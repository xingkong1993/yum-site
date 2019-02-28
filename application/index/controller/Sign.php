<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11
 * Time: 13:02
 */

namespace app\index\controller;

use app\common\controller\Pc;
use think\Db;
use think\helper\Time;

class Sign extends Pc
{
    /**
     * 注册账号
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function registers()
    {
        if (!request()->isAjax() || !request()->isPost()) $this->error("非法数据提交方式！", false, "captcha");
        $data = $this->request->param();
        if (empty($data['captcha'])) $this->error("请输入验证码！", false, "captcha");
        //验证验证码是否正确
        if (!captcha_check($data['captcha'], 1)) $this->error("验证码验证失败！", false, "captcha");
        unset($data['captcha']);
        //判断是否已注册相关账户
        $d = Db::name("user")->where(['email' => $data['email']])->find();
        if (!empty($d)) $this->error("邮箱已存在！", false, "email");
        //处理相关数据
        list($data['password'], $data['encrypt']) = get_password($data['password'], null, 2);
//        $data['captcha'] = 3;
        $data['add_time'] = time();
        Db::startTrans();
        $id = Db::name("user")->insertGetId($data);
        if ($id < 1) {
            Db::rollback();
            $this->error("注册账户失败2000！", false, "email");
        }

        $datas = [
            "accountID" => 100000 + $id,
            "uid" => $id,
            "member_level" => 1,
            "score" => 10,
            "user_name" => $data['email']
        ];
        $ids = Db::name("user_account")->insertGetId($datas);
        if ($ids === false) {
            Db::rollback();
            $this->error("注册账户失败2001！", false, "email");
        }
        $time = Time::year();
        $time = end($time);
        $time = strtotime("+4 year", $time);
        $ids = Db::name("user_score")->insertGetId([
            "uid" => $id,
            "expiry_time" => $time,
            "score" => 10,
            "balance" => 10,
            "remark" => "用户注册",
            "aTime" => time()
        ]);
        if ($ids < 1) {
            Db::rollback();
            $this->error("注册账户失败2002！", false, "email");
        }
        Db::commit();
        $this->success("注册成功", url("sign/email_login?uid=" . $id));
    }

    /**
     * 邮箱验证
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function email_login()
    {
        $id = $this->request->param("uid");
        if ($id < 1) $this->error("用户不存在！");
        $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.id' => $id])->find();
        if (empty($user)) $this->error("用户不存在！");
        $e = false;
        $sends = Db::name("send_email")->where(['account' => $user['email'], "type" => 1, "state" => 1, "add_time" => ['>', strtotime("-5 Minute")]])->find();
//        echo Db::name("send_email")->getLastSql();
//        exit;
        if (empty($sends)) {
            $base = base64_encode($id . get_encrypt(15));
            $href = "http://" . $_SERVER["HTTP_HOST"] . url("sign/register") . "?base=" . $base;

//            $e = send_email($user['email'], $content, "注册确认");
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
        } else {
            return $this->redirect("sign/register?base=$sends[base]");
        }
        session("user", $user);
        if ($e) {
//            Db::name("user")->where(['id' => $id])->update(['captcha' => 2, "mail_time" => time()]);
            $this->assign("success", "icon-success");
            $this->assign("status", "1");
        } else {
            session("user", null);
            $this->assign("success", "icon-info");
            $this->assign("status", "2");
        }

        $this->assign($user);
        return $this->fetch("/email_success");
    }

    /**
     * 邮箱验证
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function register()
    {
        $user = session("user.id");
        if (empty($user)) {
            $this->assign("is_login", false);
        } else {
            $this->assign("is_login", true);
            $base = input("base");
            $send = Db::name("send_email")->where(['base' => $base])->find();
            $user = Db::name("user")->where(['id' => $user])->find();
            if (empty($send) || empty($user) || $send['state'] < 1) {
                $this->assign("message", "验证失败，请重新获取邮件验证！");
                $this->assign("success", "icon-info");
                $this->assign("status", "2");
            } else {
                $char = (time() - $send['add_time']) / 60;
                if ($char > $send['expiry_time'] && $send['state'] != 2) {
                    $this->assign("message", "验证失败，请重新获取邮件验证！");
                    $this->assign("success", "icon-info");
                    $this->assign("status", "2");
                } else {
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
                    if ($flag) {
                        Db::commit();
                        $this->assign("message", "验证成功！");
                        $this->assign("success", "icon-success");
                        $this->assign("status", "1");
                    } else {
                        Db::rollback();
                        $this->assign("message", "验证失败，请重新获取邮件验证！");
                        $this->assign("success", "icon-info");
                        $this->assign("status", "2");
                    }
                }
            }
            $this->assign("id", $user['id']);
        }

        return $this->fetch("/register");
    }

    /**
     * 登录
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function login()
    {
        if (!$this->request->isPost() || !$this->request->isAjax()) $this->error("非法数据提交！");
        $data = $this->request->param();
        $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.email' => $data['account'], 'u.status' => 1])->find();
        if (empty($user)) $this->error("账号不存在！", false, "account");
        list($data['password'], $en) = get_password($data['password'], $user['encrypt'], 2);
        if ($data['password'] != $user['password']) $this->error("密码错误！", false, "password");
        $up = [
            "last_login" => time(),
            "times" => $user['times'] > 0 ? $user['times'] + 1 : 1
        ];
        Db::name("user")->where(['id' => $user['id']])->update($up);
        session("user", $user);
        //判断是否验证过邮件
        $sends = Db::name("send_email")->where(['account' => $user['email'], "type" => 1, "state" => 2])->count();
        if ($sends > 0) {
            $this->success("登录成功！");
        } else {
            $this->success("登录成功！", url("sign/email_login?uid=" . $user['id']), "captcha");
        }
    }

    /**
     * 退出
     */
    public function sign_out()
    {
        session("user", null);
        $this->success("退出成功！");
    }

    /**
     * 修改密码
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function change_password()
    {
        if (!$this->request->isPost() || !$this->request->isAjax()) $this->error("非法数据提交！");
        $data = $this->request->param();
        $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.id' => session("user.id"), 'u.status' => 1])->find();
        if (empty($user)) $this->error("用户不存在");
        $password = get_password($data['old_password'], $user['encrypt'], 2);
        if ($password[0] != $user['password']) $this->error("原密码错误！", false, "old_password");
        unset($data['old_password']);
        list($data['password'], $data['encrypt']) = get_password($data['password'], null, 2);
        $uid = Db::name("user")->where(['id' => session("user.id")])->update($data);
        if ($uid === false) $this->error("密码修改失败！", false, "password");
        $user['password'] = $data['password'];
        $user['encrypt'] = $data['encrypt'];
        session("user", $user);
        $this->success("密码修改成功！");
    }
}