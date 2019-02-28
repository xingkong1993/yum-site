<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/30
 * Time: 11:28
 */

namespace app\admin\controller;


use app\common\controller\Admin;

class FriendLink extends Admin
{
    public function index()
    {
        return $this->fetch();
    }

    public function add(){
        return $this->fetch("save");
    }

    public function save(){
        if(!$this->request->isAjax() || !$this->request->isPost()) $this->error("非法数据提交方式");
    }
}