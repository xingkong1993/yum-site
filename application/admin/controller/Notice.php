<?php
/**
 * Created by PhpStorm.
 * User: 11131
 * Date: 2019-2-28
 * Time: 10:00
 */

namespace app\admin\controller;


use app\common\controller\Admin;

class Notice extends Admin
{
    public function index()
    {
        return $this->fetch();
    }

    public function add(){
        return $this->fetch("save");
    }
}