<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/17
 * Time: 11:13
 */

namespace app\common\controller;


use think\Controller;
use think\Db;

class Pc extends Controller
{
    public function _initialize()
    {
        parent::_initialize();
        $site = site_config();
        if ($site['site_status'] != 1) {
            $this->redirect("error/close_site");
        }
        $this->assign($site);
        if (session("user")) {
            $capchas = Db::name("send_email")->where(['account' => session("user.email"), "state" => 2, "type" => 1])->count();
            $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.id' => session("user.id")])->find();
            $user['captcha'] = 1;
            if ($capchas < 1) {
                $user['captcha'] = 2;
            }
            $this->assign("session_user", $user);
            $this->myNotices();
        }
    }

    private function myNotices(){
        $id = session("user.id");
        $blog_num = Db::name("blog_item")->where(['author'=>$id])->count();
        $this->assign(["blog_number"=>$blog_num]);
    }
}