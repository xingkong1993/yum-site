<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/7
 * Time: 13:32
 */

namespace app\api\logic;

/**
 * 网站信息
 * Class Site
 * @package app\api\logic
 */
class Site
{
    /**
     * 网站配置信息
     * @param $fields
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function config($fields){
        $data = site_config();
        exit(get_api_return_message(0,$fields['sign'],$fields['method'],$fields['format'],"",$data));
    }
}