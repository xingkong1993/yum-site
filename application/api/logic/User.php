<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 11:48
 */

namespace app\api\logic;


use app\api\model\PagesUrl;
use think\Db;
use think\helper\Time;

/**
 * 用户管理
 * Class User
 * @package app\api\logic
 */
class User
{
    /**
     * 会员登陆
     * @param $fields
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function sign($fields)
    {
        //判断用户是否存在？
        $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.email|ua.accountID' => $fields['account']])->find();
        if (empty($user)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10001, $fields['sign'], $fields['method'], $fields['format'], "", false, $fields['account']));
        }

        //验证密码是否输入正确
        list($pwd, $ent) = get_password($fields['password'], $user['encrypt'], 2);
        if ($pwd !== $user['password']) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10002, $fields['sign'], $fields['method'], $fields['format'], "", false, $fields['account']));
        }
        $user['send_email'] = false;
        //判断是否验证过邮件
        $sends = Db::name("send_email")->where(['account' => $user['email'], "type" => 1, "state" => 2])->count();
        if ($sends < 1) $user['send_email'] = true;
        $icon = $user['icon'];
        $user['icon'] = preg_match("/^(http:\/\/)|(https:\/\/)/",$icon)?$icon:config("icon_src") . $user['icon'];
        $up = [
            "last_login" => time(),
            "times" => $user['times'] > 0 ? $user['times'] + 1 : 1
        ];
        if(!empty($fields['bind_id'])){
            Db::name("third_{$fields['from']}_user")->where(['id'=>$fields['bind_id']])->update(['uid'=>$user['id']]);
            $user['from'] = $fields['from'];
            if(empty($icon)){
                $user['icon'] = Db::name("third_{$fields['from']}_user")->where(['id'=>$fields['bind_id']])->value("avatar_large");
                Db::name("user_account")->where(['uid'=>$user['id']])->update(['icon'=>$user['icon']]);
            }
        }else{
            //判断是否绑定第三方账号
            $c = Db::name("third_weibo_user")->where(['uid'=>$user['id']])->find();
            if(!empty($c)){
                $user['from'] = "weibo";
                if(empty($icon)){
                    $user['icon'] = $c['avatar_large'];
                    Db::name("user_account")->where(['uid'=>$user['id']])->update(['icon'=>$user['icon']]);
                }
            }
        }
        Db::name("user")->where(['id' => $user['id']])->update($up);
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "登录成功", $user));
    }

    /**
     * 新用户注册
     * @param $fields
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register($fields)
    {
        //验证验证码是否正确
        if (!src_code($fields['captcha'], "2" . $fields['code_sign'])) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(4, $fields['sign'], $fields['method'], $fields['format']));
        }
        //判断用户是否存在？
        $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.email' => $fields['account']])->find();
        if (!empty($user)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10003, $fields['sign'], $fields['method'], $fields['format'], "", false, $fields['account']));
        }
        $data = [];
        $data['email'] = $fields['account'];
        list($data['password'], $data['encrypt']) = get_password($fields['password'], null, 2);
        $data['add_time'] = time();
        Db::startTrans();
        $uid = Db::name("user")->insertGetId($data);
        if ($uid < 1) {
            Db::rollback();
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10004, $fields['sign'], $fields['method'], $fields['format'], "", false, $fields['account']));
        }

        $datas = [
            "accountID" => 100000 + $uid,
            "uid" => $uid,
            "member_level" => 1,
            "score" => 10,
            "user_name" => $data['email']
        ];
        $ac_id = Db::name("user_account")->insertGetId($datas);
        if ($ac_id === false) {
            Db::rollback();
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10004, $fields['sign'], $fields['method'], $fields['format'], "", false, $fields['account']));
        }
        $time = Time::year();
        $time = end($time);
        $time = strtotime("+4 year", $time);
        $ids = Db::name("user_score")->insertGetId([
            "uid" => $uid,
            "expiry_time" => $time,
            "score" => 10,
            "balance" => 10,
            "remark" => "用户注册",
            "aTime" => time()
        ]);

        if ($ids < 1) {
            Db::rollback();
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10004, $fields['sign'], $fields['method'], $fields['format'], "3", false, $fields['account']));
        }
        $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.id' => $uid])->find();
        Db::commit();
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "注册账号成功！", $user));
    }

    /**
     * 发送注册邮件
     * @param $fields
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function email($fields)
    {
        //获取用户信息
        $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.id' => $fields['uid']])->find();

        if (empty($user)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10001, $fields['sign'], $fields['method'], $fields['format'], "用户不存在，邮件发送失败！", false));
        }

        $sends = Db::name("send_email")->where(['account' => $user['email'], "type" => 1, "state" => 1, "add_time" => ['>', strtotime("-5 Minute")]])->find();
        if (!empty($sends) && $fields['types'] != "repert") {
            exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "系统发送邮件成功！", $user));
        }

        $base = base64_encode($fields['uid'] . get_encrypt(15));
        $href = config("send_src")."/user/send_email_recode.html?base=" . $base;
        $tpl = Db::name("email_template")->where(['type' => 1, "state" => 1])->order("tpl_time desc")->value("content");
        if (empty($tpl)) {
            $tpl = '感谢注册YUM社区，<p><a href="' . $href . '">点击我</a>完成邮箱验证。</p><p>如无法点击请复制下方链接完成验证：</p><p>' . $href . '</p><p style="text-align: right">YUM社区<br>' . date("Y-m-d") . '</p>';
        } else {
            $tpl = htmlspecialchars_decode($tpl);
            $tpl = str_replace('${email}', $user['email'], $tpl);
            $tpl = str_replace('${site_name}', site_config("site_name"), $tpl);
            $tpl = str_replace('${link}', '<a href="' . $href . '">点击确认</a>', $tpl);
            $tpl = str_replace('${copy}', $href, $tpl);
            $tpl = str_replace('${date}', date("Y-m-d"), $tpl);
        }

        $e = send_email($user['email'], $tpl, config("email_template.1"));
        $email = [
            "account" => $user['email'],
            "type" => 1,
            "content" => htmlspecialchars($tpl),
            "expiry_time" => 5,
            "state" => $e ? 1 : 0,
            "base" => $base,
            "add_time" => time()
        ];
        Db::name("send_email")->insertGetId($email);

        if (!$e) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(20001, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "邮件发送成功！", $user));
    }

    /**
     * 邮件验证
     * @param $fields
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function codeEmail($fields)
    {
        $send = Db::name("send_email")->where(['base' => $fields['base']])->order("add_time desc")->find();
        $user = Db::name("user")->where(['id' => $fields['uid']])->find();
        if (empty($send) || empty($user) || $send['state'] < 1) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(20002, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        $char = (time() - $send['add_time']) / 60;
        if ($char > $send['expiry_time'] && $send['state'] != 2) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(20003, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        $flag = true;
        Db::startTrans();
        if ($send['state'] == 1) {
            $time = Time::year();
            $time = end($time);
            $time = strtotime("+4 year", $time);
            $id = Db::name("user_score")->insertGetId([
                "uid" => session("user.id"),
                "expiry_time" => $time,
                "score" => 10,
                "balance" => 10,
                "remark" => "邮箱验证",
                "aTime" => time()
            ]);
            if ($id < 1) {
                $flag = false;
            }

            $users = Db::name("user_account")->where(['uid' => session("user.id")])->setInc("score", 10);
            if ($users === false) $flag = false;
        }

        $up = Db::name("send_email")->where(['id' => $send['id']])->update(['state' => 2]);
        if ($up === false) {
            $flag = false;
        }
        if (!$flag) {
            Db::rollback();
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(20003, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        Db::commit();
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "验证成功！", $user));
    }

    /**
     * 积分查询
     * @param $fields
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function score($fields)
    {
        $data = Db::name("user_score")->where(['uid' => $fields['uid'], "expiry_time" => ['>=', time()]])->paginate(10);
        $data = $data->toArray();
        $data['current_page'] = isset($fields['page']) ? $fields['page'] : 1;
        $data['data'] = Db::name("user_score")->where(['uid' => $fields['uid']])->page($data['current_page'], 10)->select();
        //生成分页代码
        $data['pages'] = get_page_code($data['last_page'], $data['current_page']);
        if (!empty($data['data'])) {
            foreach ($data['data'] as &$value) {
                $value['aTime'] = date("Y-m-d H:i:s", $value['aTime']);
                $value['expiry_time'] = date("Y-m-d H:i:s", $value['expiry_time']);
            }
        }
        $data = ["data" => $data['data'], "pages" => $data['pages']];
        $data['total_score'] = Db::name("user_score")->where(['uid' => $fields['uid'], "expiry_time" => ['>=', time()]])->sum("balance");
        $times = Time::month();
        $times = end($times);
        $data['expiry_score'] = Db::name("user_score")->where(['uid' => $fields['uid'], "expiry_time" => ['<=', $times]])->sum("balance");
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "", $data));
    }

    /**
     * 资料编辑
     * @param $fields
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($fields)
    {
        $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.id' => $fields['uid']])->find();

        if (empty($user)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10001, $fields['sign'], $fields['method'], $fields['format'], "用户不存在！", false));
        }

        $user_data = [
            "user_name" => $fields['user_name'] ?: $user['user_name'],
            "sex" => $fields["sex"],
            "birthday" => $fields['birthday'] ? strtotime($fields['birthday']) : $user['birthday'],
            "jingle" => $fields["jingle"] ?: ""
        ];

        if (Db::name("user_account")->where(['uid' => $fields['uid']])->update($user_data) === false) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10006, $fields['sign'], $fields['method'], $fields['format'], "", false, $user['accountID']));
        }
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "用户【%s】资料更新成功！", false, $user['accountID']));
    }

    /**
     * 会员文章列表
     * @param $fields
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function blog_list($fields)
    {
        $data = Db::name("blog_item")->where(['author' => $fields['uid'], "from" => 1])->paginate(30);
        $data = $data->toArray();
        $data['current_page'] = isset($fields['page']) ? $fields['page'] : 1;
        $data['data'] = Db::name("blog_item")->where(['author' => $fields['uid'], "from" => 1])->page($data['current_page'], 30)->select();
        //生成分页代码
        $data['pages'] = get_page_code($data['last_page'], $data['current_page']);
        $count = ($data['current_page'] - 1) * 30 + 1;
        if (!empty($data['data'])) {
            foreach ($data['data'] as &$value) {
                $value['number'] = $count;
                $count++;
                $value['image'] = "//" . $_SERVER['HTTP_HOST'] . $value['blog_icon'];
                $value['content'] = htmlspecialchars_decode($value['content']);
                $value['add_time'] = date("Y-m-d H:i:s", $value['add_time']);
                $value['update_time'] = date("Y-m-d H:i:s", $value['update_time']);
            }
        }
        $data = ["data" => $data['data'], "pages" => $data['pages']];
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "", $data));
    }

    /**
     * 会员回复/评论列表
     * @param $fields
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user_account_message($fields)
    {
        $users = Db::name("user")->find($fields['uid']);
        if (empty($users)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10001, $fields['sign'], $fields['method'], $fields['format'], "用户不存在！", false));
        }

        $blog_number = Db::name("blog_item")->where(['author' => $fields['uid'], "from" => 1, "status" => 1])->count();
        $data = [
            "blog" => $blog_number,
            "fans" => 0,
            "powers" => 0
        ];
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "", $data));
    }
}