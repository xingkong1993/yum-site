<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/5
 * Time: 15:51
 */

namespace app\api\controller;


use think\Controller;

class ImageCode extends Controller
{
    public function src()
    {
        $id = $this->request->param("id");
        if (empty($id)) $id = "1" . get_encrypt(5);
        //绑定请求ip防止验证码冲突
        $ip = $_SERVER['REMOTE_ADDR'];
        $ip = explode(".", $ip);
        $id = $id . implode("", $ip);
        return "//" . $_SERVER["HTTP_HOST"] . captcha_src($id);
    }
}