<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 14:38
 */

namespace app\third\controller;

use think\Db;

class Weibo
{
    public function callback()
    {
        vendor("weibo.saetv2_ex_class");
        header('Content-Type: text/html; charset=UTF-8');
        $key = "2102737632";
        $sert = "67e5a688defdebf3715a28900f54b883";
        $uri = "https://www.yum-blog.cn/weibo.php/weibo/callback";
        $SaeTOAuthV2 = new \SaeTOAuthV2($key, $sert);
        $code = request()->param("code");
        if ($code) {
            $keys = array();
            $keys['code'] = $code;
            $keys['redirect_uri'] = $uri;
            $token = $SaeTOAuthV2->getAccessToken('code', $keys);
        }
        if (isset($token)) {
            $sae = new \SaeTClientV2($key, $sert, $token['access_token']);
            $uid_get = $sae->get_uid();
            $uid = $uid_get['uid'];
            $usr_info = $sae->show_user_by_id($uid);
            if ($usr_info) {
                //判断是否存在用户信息
                $users = Db::name("third_weibo_user")->where(['weibo_id' => $usr_info['idstr']])->find();
                if (empty($users)) {
                    $datas = [
                        "weibo_id" => $usr_info['idstr'],
                        "screen_name" => $usr_info['screen_name'],
                        "location" => $usr_info['location'],
                        "description" => $usr_info['description'],
                        "profile_image_url" => $usr_info['profile_image_url'],
                        "avatar_large" => $usr_info['avatar_large'],
                        "gender" => $usr_info["gender"],
                        "jsons" => json_encode($usr_info),
                        "add_times" => time()
                    ];

                    $id = Db::name("third_weibo_user")->insertGetId($datas);
                    if ($id < 1) {
                        exit("用户数据错误！");
                    }
                } else {
                    $id = $users['id'];
                }
                @header("location:https://www.xincheng-blog.cn/user/sign.html?id={$id}&from=weibo");
            }
        }
    }
}