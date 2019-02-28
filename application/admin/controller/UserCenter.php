<?php
/**
 * Created by PhpStorm.
 * User: chenx
 * Date: 2018/2/25
 * Time: 12:18
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;

class UserCenter extends Controller
{
    public function sign()
    {
//        echo strtoupper(md5("ljb123123-OAgti8xFGX"));
        if ($this->request->isMobile()) {
            $this->redirect("mobile");
        }
        if ($this->request->isPost()) {
            $data = $this->request->param();
            //获取到管理员信息
            $admin = Db::name("admin")->where(['account' => $data['account']])->find();
            if (empty($admin)) {
                $this->error("管理员不存在！", "", ["error" => "account"]);
            }
            list($password, $enty) = get_password($data['pwd'], $admin['encrypt'], 1);
            if ($admin['password'] != $password) {
                $this->error("密码错误！", "", ["error" => "pwd"]);
            }
            session("admin_id", $admin['admin_id']);
            session("admin_group", $admin['is_admin']);//管理组
            $this->success("登陆成功！", url("index/index"));
            exit;
        }
        return $this->fetch();
    }

    public function logout()
    {
        if (!$this->request->isPost()) $this->error("非法提交！");
        session("admin_id", null);
        $this->success("退出成功！");
    }

    public function mobile()
    {
        if (!$this->request->isMobile()) {
            header("location:" . getenv("HTTP_REFERER"));
        }
        return $this->fetch();
    }
}