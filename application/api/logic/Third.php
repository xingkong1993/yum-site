<?php
/**
 * Created by PhpStorm.
 * User: 11131
 * Date: 2019/1/23
 * Time: 15:12
 */

namespace app\api\logic;


use think\Db;

/**
 * 第三方登录信息获取
 * Class Third
 * @package app\api\logic
 */
class Third
{
    /**
     * 第三方登录配置
     * @param $files
     */
    public function codes($files)
    {
        switch ($files['types']) {
            case "weibo":
                exit(get_api_return_message(0, $files['sign'], $files['method'], $files['format'], "", ["code" => get_weibo_uri()]));
                break;
        }
    }

    /**
     * 第三方登录验权
     * @param $files
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function login($files)
    {
        //获取用户是否存在
        $users = Db::name("third_{$files['from']}_user")->where(['id' => $files['id']])->find();
        if (empty($users)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(40001, $files['sign'], $files['method'], $files['format'], "", false));
        }
        $users['from'] = $files['from'];
        if(!empty($users['uid'])){
            $thirds = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.id'=>$users['uid'],'u.status'=>1])->find();
            if(empty($thirds)){
                header("HTTP/1.0 201 OK");
                exit(get_api_return_message(40001, $files['sign'], $files['method'], $files['format'], "", false));
            }
            $thirds['from'] = $files['from'];
            if($thirds['icon'] && $thirds['icon'] != $users['profile_image_url'] && !preg_match("/^(http:\/\/)/",$thirds['icon'])){
                $thirds['icon'] = config("icon_src").$thirds['icon'];
            }
            $users['user_accounts'] = $thirds;
            $up = [
                "last_login" => time(),
                "times" => $thirds['times'] > 0 ? $thirds['times'] + 1 : 1
            ];
            Db::name("user")->where(['id' => $thirds['id']])->update($up);
        }
        exit(get_api_return_message(0, $files['sign'], $files['method'], $files['format'], "", $users));
    }
}