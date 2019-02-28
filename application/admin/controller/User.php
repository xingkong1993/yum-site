<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/18
 * Time: 9:09
 */

namespace app\admin\controller;


use app\common\controller\Admin;
use think\Db;

class User extends Admin
{
    public function index()
    {
        $data = model("AdminBaseModel")->get_page_join("user_account", "user", "db1.uid=db2.id", [], 30, ["query" => []]);
        if (!empty($data['result'])) {
            foreach ($data['result'] as &$value) {
                if ($value['birthday'] > 0) {
                    $value['age'] = get_age($value['birthday']) . "岁";
                    $value['birthday'] = date("n月d日", $value['birthday']);
                } else {
                    $value['age'] = "保密";
                    $value['birthday'] = "保密";
                }
            }
        }
        $con = [
            "thisHref" => "user/index",
            "special" => [
                'delete' => ["user/disable_user", "user/cancellation"],
                'open' => ["user/email_push"]
            ],
            "%name" => "user_name",
            "query" => [
                "id" => "%id"
            ]
        ];
        $option = get_menu_string($con, ['role/add']);
        $this->assign("option", json_encode($option));
        $this->assign($data);
        return $this->fetch();
    }

    public function email_push()
    {
        if ($this->request->isPost()) {
//            dump($this->request->param());
            $data = $this->request->param();
//            dump($data);
            $send = send_email($data['email'], $data['email_content'], config("email_template.{$data['email_type']}"));
            $email = [
                "account" => $data['email'],
                "type" => $data['email_type'],
                "content"=>htmlspecialchars($data['email_content']),
                "expiry_time"=>5,
                "state"=> $send?1:0,
                "base"=>$data['base'],
                "add_time"=>time()
            ];
            $id = Db::name("send_email")->insertGetId($email);
            if($send && $id){
                $this->success("邮件推送成功！");
            }else{
                $this->error("邮件推送失败");
            }
            exit;
        }
        $this->assign(Db::name("user")->where(['id' => input("id")])->find());
        return $this->fetch("email");
    }

    public function email_template()
    {
        if (!$this->request->isPost() || !$this->request->isAjax()) $this->error("非法数据提交");
        $id = $this->request->param("type");
        $account = $this->request->param("account");
        $uri = $this->request->param("uri");
        $data = Db::name("email_template")->where(['type' => $id, "state" => 1])->order("tpl_time desc")->value("content");
        if (empty($data)) $this->error("没有模板数据");
        $data = htmlspecialchars_decode($data);
        $e = function ($account, $data, $uri) {
            $data = str_replace('${email}', $account, $data);
            $data = str_replace('${site_name}', site_config("site_name"), $data);
            $data = str_replace('${link}', '<a href="' . $uri . '">点击确认</a>', $data);
            $data = str_replace('${copy}', $uri, $data);
            $data = str_replace('${date}', date("Y-m-d"), $data);
            return $data;
        };
        $data = $e($account, $data, $uri);
        $this->success("成功", false, ['html' => $data, "text" => htmlspecialchars($data)]);
    }
}