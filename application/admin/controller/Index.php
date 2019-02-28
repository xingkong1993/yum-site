<?php
/**
 * Created by PhpStorm.
 * User: chenx
 * Date: 2018/2/25
 * Time: 10:34
 */

namespace app\admin\controller;


use app\common\controller\Admin;
use think\Db;

class Index extends Admin
{
    public function index(){
        if(input("webs",false,"trim") === "welcome"){
            //获取审核数据
            $this->assign("blog",Db::name("blog_item")->where(['status'=>2])->count());
            return $this->fetch("welcome");
        }
        captcha_src();
        return $this->fetch();
    }

    public function save(){
        return $this->fetch();
    }
}