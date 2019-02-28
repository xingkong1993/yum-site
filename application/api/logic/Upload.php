<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/14
 * Time: 16:04
 */

namespace app\api\logic;


use think\Db;

/**
 * 文件上传
 * Class Upload
 * @package app\api\logic
 */
class Upload
{
    /**
     * 用户头像上传
     * @param $fields
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function user($fields)
    {
        //获取用户信息
        $user = Db::name("user")->alias("u")->join("user_account ua", "ua.uid=u.id")->where(['u.id' => $fields['uid'], "u.status" => 1])->find();
        if (empty($user)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10001, $fields['sign'], $fields['method'], $fields['format'], "", false, ""));
        }

        //存储头像信息
        $url = upload_user_icon($fields['files'], $user['id']);

        if (empty($url)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10004, $fields['sign'], $fields['method'], $fields['format'], "", false, $user['accountID']));
        }

        $da = Db::name("user_account")->where(['uid' => $fields['uid']])->update(['icon' => $url]);
        if ($da === false) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(10004, $fields['sign'], $fields['method'], $fields['format'], "", false, $user['accountID']));
        }

        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "账号【%s】头像更改成功", ['icon' => "//{$_SERVER['HTTP_HOST']}{$url}"], $user['accountID']));
    }
}