<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/28
 * Time: 16:23
 */

namespace app\index\controller;


use think\Controller;

class Error extends Controller
{
    public function close_site(){
        return $this->fetch("/site_close");
    }
}