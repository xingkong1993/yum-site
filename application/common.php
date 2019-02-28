<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 获取fa-icon图标文件
 * @return mixed
 */
function get_fa_icon()
{
    $clien = fopen("./static/css/iconfont.css", "r");
    $content = fread($clien, filesize("./static/css/iconfont.css"));
    fclose($clien);
    preg_match_all("/\.(icon-.+)\:before/", $content, $data);
    return $data[1];
}

/**
 * 加密字符串生成器
 * @param int $char_length 生成长度
 * @return string 加密字符串
 */
function get_encrypt($char_length = 10)
{
    $rands = '';
    $chart = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    for ($i = 0; $i < $char_length; $i++) {
        $rands .= $chart[mt_rand(0, (strlen($chart) - 1))];
    }

    return $rands;
}

/**
 * 密码生成规则
 * @param $pwd
 * @param string $encrypt
 * @param int $login_stat 登录平台 1--后台 2--前端
 * @return array
 */
function get_password($pwd, $encrypt = '', $login_stat = 1)
{
    if (empty($encrypt)) $encrypt = get_encrypt();
    switch ($login_stat) {
        case 1:
            //系统后台登陆验证
            $pwd = sha1(base64_encode($pwd));
            $pwd = md5($pwd . $encrypt);
            break;
        case 2:
            //前端登陆验证
            $pwd = md5($pwd . $encrypt);
            $pwd = base64_encode(substr($pwd, 0, 12));
            $pwd = md5($pwd . $encrypt);
            break;
    }
    return [$pwd, $encrypt];
}

/**
 * 新浪微博第三方登录获取授权链接生成
 * @return array
 */
function get_weibo_uri()
{
    vendor("weibo.saetv2_ex_class");
    header('Content-Type: text/html; charset=UTF-8');
    $key = "2102737632";
    $sert = "67e5a688defdebf3715a28900f54b883";
    $uri = "https://www.yum-blog.cn/weibo.php/weibo/callback";
    $SaeTOAuthV2 = new \SaeTOAuthV2($key, $sert);
    return $SaeTOAuthV2->getAuthorizeURL($uri);
}

/**
 * 菜单按钮
 * @param array $options 相关参数
 * @param array $con 跳过的菜单按钮
 * @return array
 */
function get_menu_string(array $options, array $con = [])
{
    $menu = \think\Db::name("admin_menu")->alias("m1")->join("admin_menu m2", "m2.id=m1.pid")->where(['m2.href' => $options['thisHref'], "m1.status" => 1])->order("m1.sort asc")->column("m1.id,m1.name,m1.href");
    $delete = [];
    $open = [];
    if (isset($options['special']) && !empty($options['special'])) {
        $delete = isset($options['special']['delete']) ? $options['special']['delete'] : [];
        $open = isset($options['special']['open']) ? $options['special']['open'] : [];
    }
    if (!empty($menu)) {
        foreach ($menu as $key => &$value) {
            if ($con && in_array($value['href'], $con)) {
                unset($menu[$key]);
            } elseif ($delete && in_array($value['href'], $delete)) {
                $value = [
                    "name" => $value['name'],
                    "uri" => url($value['href'], $options['query']),
                    "clickFunc" => "options_confirm({icon:3,obj:this});return false;",
                    "data" => [
                        "type" => "delete",
                        "name" => "是否{$value['name']}【%name】"
                    ],
                    "dataRule" => [
                        "%name" => $options['%name']
                    ]
                ];
            } elseif ($open && in_array($value['href'], $open)) {
                $value = [
                    "name" => $value['name'],
                    "uri" => url($value['href'], $options['query']),
                    "clickFunc" => "options_open(this);return false;",
                    "data" => [
                        "type" => "open",
                    ],
                    "dataRule" => [
                        "%name" => $options['%name']
                    ]
                ];
            } else {
                $value = [
                    "name" => $value['name'],
                    "uri" => url($value['href'], $options['query']),
                    "clickFunc" => false
                ];
            }
        }
    }
    return array_values($menu);
}

/**
 *  获取网站配置信息
 * @return array|false|mixed|PDOStatement|string|\think\Model
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function site_config($name = null)
{
    $site = \think\Cache::get("site_config");
    if (empty($site)) {
        $site = \think\Db::name("site_config")->find();
        \think\Cache::set("site_config", $site);
    }
    if (!$name)
        return $site;
    else
        return $site[$name];
}

function upload_site($logo, $dir)
{
//匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $logo, $result)) {
        $logo = str_replace(" ", "+", $logo);
        $type = $result[2];
        $new_file = "./static/image/site";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0777, true);
        }
        $name = "{$dir}.png";
        $new_file = $new_file . "/" . $name;
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $logo)))) {
            return "/static/image/site/{$dir}.png";
        } else {
            return false;
        }
    } else {
        return $logo;
    }
}

function upload_user_icon($icon, $id)
{
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $icon, $result)) {
        $icon = str_replace(" ", "+", $icon);
        $type = $result[2];
        $new_file = "./upload/user/{$id}/icon";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0777, true);
        } else {
            $dir = @scandir($new_file);
            if (count($dir) > 2) {
                foreach ($dir as $value) {
                    if ($value != "." && $value != "..") {
                        @unlink($new_file . "/" . $value);
                    }
                }
            }
        }
        $name = $id . "-" . get_encrypt(5);
        $new_file = $new_file . "/" . $name . ".png";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $icon)))) {
            return trim($new_file, ".");
        } else {
            return false;
        }
    } else {
        return $icon;
    }
}
function get_upload_image($images,$item,$uid){
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $images, $result)) {
        $icon = str_replace(" ", "+", $images);
        $type = $result[2];
        $new_file = "./upload/".date("Ymd")."/{$item}/{$uid}";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0777, true);
        }
        $name = $uid . "-" . get_encrypt(5);
        $new_file = $new_file . "/" . $name . ".png";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $icon)))) {
            return trim($new_file, ".");
        } else {
            return false;
        }
    } else {
        return $images;
    }
}
function send_email($email, $content = "测试邮件", $title = "测试")
{
    vendor("phpmailer.PHPMailer");
    $sendmail = 'server@yum-blog.cn'; //发件人邮箱
    $sendmailpswd = "4zK48iGAPCk3TGtZ"; //客户端授权密码,而不是邮箱的登录密码！
    $send_name = 'YUM社区';// 设置发件人信息，如邮件格式说明中的发件人，
    $toemail = $email;//定义收件人的邮箱
    $to_name = $email;//设置收件人信息，如邮件格式说明中的收件人
    $mail = new PHPMailer();
    $mail->isSMTP();// 使用SMTP服务
    $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
    $mail->Host = "smtp.exmail.qq.com";// 发送方的SMTP服务器地址
    $mail->SMTPAuth = true;// 是否使用身份验证
    $mail->Username = $sendmail;//// 发送方的
    $mail->Password = $sendmailpswd;//客户端授权密码,而不是邮箱的登录密码！
    $mail->SMTPSecure = "ssl";// 使用ssl协议方式
    $mail->Port = 465;
    $mail->From = $sendmail;
    $mail->FromName = $send_name;
    $mail->addAddress($toemail, $to_name);
    $mail->IsHTML(true);
    $mail->Subject = $title;
    $mail->Body = $content;
//    $mail->SMTPDebug = true;
    return $mail->Send();
}

function get_email_encryption($email)
{
    if (empty($email)) return false;
    $email = explode("@", $email);
    $end = end($email);
    $email = array_shift($email);
    $len = strlen($email);
    if ($len < 3) return $email[0] . "***@" . $end;
    for ($i = 0; $i < $len; $i++) {
        if ($i >= 3) {
            $email[$i] = "*";
        }
    }

    return $email . "@" . $end;
}

function get_age($birthday)
{
    if (empty($birthday)) return false;
    list($birth_year, $birth_month, $birth_day) = explode("-", date("Y-m-d", $birthday));
    list($year, $month, $day) = explode("-", date("Y-m-d"));
    $age = $year - $birth_year;
    if ((int)($birth_month . $birth_day) > (int)($month . $day)) $age -= 1;
    return $age;
}