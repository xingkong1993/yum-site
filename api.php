<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
//$time = date("ymd.His");
//if(floatval($time)>= 180123){
//    die;
//}
// [ 应用入口文件 ]
header("Access-Control-Allow-Origin:*");
header("Access-Control-Request-Method:POST");
header('Access-Control-Allow-Headers:x-requested-with,Authorization,Content-Type');
header("Content-Type: text/html;charset=utf-8");
// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE', 'api');
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';