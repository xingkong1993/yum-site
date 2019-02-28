<?php
/**
 * 返回报文信息
 * @param $code
 * @param $sign
 * @param string $method
 * @param string $message
 * @param array $data
 * @param string $format
 * @param string $rep
 * @return bool|false|string
 */
function get_api_return_message($code, $sign, $method = "", $format = "json", $message = "", $data = [], $rep = "")
{
    $arr = [
        0 => "success",
        -1 => "非法数据提交方式！",
        1 => "必填参数%s缺失",
        2 => "签名验签失败！",
        3 => "接口%s不存在！",
        4 => "验证码错误！",
        10001 => "账号【%s】不存在！",
        10002 => "账号【%s】密码错误！",
        10003 => "邮箱【%s】已注册！",
        10004 => "邮箱【%s】注册失败！",
        10005 => "用户【%s】头像上传失败！",
        10006 => "用户【%s】更新信息失败！",
        20001 => "邮件发送失败！",
        20002 => "验证邮件不存在，验证失败！",
        20003 => "验证邮件已过期，请重新获取！",
        30001 => "文章【%s】发布失败！",
        30002 => "文章不存在！",
        40001 => "用户信息不存在！",
    ];

    if (!isset($arr[$code])) {
        return false;
    }

    $message = empty($message) ? $arr[$code] : $message;

    if (!empty($rep)) {
        $message = str_replace("%s", $rep, $message);
    }

    //组装回传数据
    $return['header'] = [
        "method" => $method,
        "times" => date("Y-m-d H:i:s"),
        "sign" => $sign,
        "format" => $format
    ];

    if ($code !== 0) {
        $return['header']['method'] = "ErrorMethod";
    }

    $return['message'] = ['code' => $code, "message" => $message];

    if (!empty($data)) $return['data'] = $data;
    switch ($format) {
        case "xml":
            return xml_encode($return, "resultData");
            break;
        case "base64":
            return base64_encode(serialize($return));
            break;
        case "serialize":
            return serialize($return);
            break;
        default:
            return json_encode($return, JSON_UNESCAPED_UNICODE);
            break;
    }
}

function logic($name)
{
    return model($name, "logic");
}

/**
 * XML报文
 * @param $data
 * @param string $root
 * @param string $item
 * @param string $attr
 * @param string $id
 * @param string $encoding
 * @return string
 */
function xml_encode($data, $root = 'think', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')
{
    if (is_array($attr)) {
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }
        $attr = implode(' ', $_attr);
    }
    $attr = trim($attr);
    $attr = empty($attr) ? '' : " {$attr}";
    $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
    $xml .= "<{$root}{$attr}>";
    $xml .= data_to_xml($data, $item, $id);
    $xml .= "</{$root}>";
    return $xml;
}

/**
 * 数据XML编码
 * @param mixed $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id 数字索引key转换为的属性名
 * @return string
 */
function data_to_xml($data, $item = 'item', $id = 'id')
{
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if (is_numeric($key)) {
            $id && $attr = " {$id}=\"{$key}\"";
            $key = $item;
        }
        $xml .= "<{$key}{$attr}>";
        $xml .= (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml .= "</{$key}>";
    }
    return $xml;
}

/**
 * 图形验证码
 * @param $code
 * @param $id
 * @return bool
 */
function src_code($code, $id)
{
    //绑定请求ip防止验证码冲突
    $ip = $_SERVER['REMOTE_ADDR'];
    $ip = explode(".", $ip);
    $id = $id . implode("", $ip);
    return (captcha_check($code, $id));
}

/**
 * 分页代码
 * @param $last
 * @param $cur
 * @return string
 */
function get_page_code($last, $cur)
{
    $block = [
        'first' => null,
        'slider' => null,
        'last' => null
    ];

    $side = 2;
    $window = $side * 2;

    if ($last < ($window + 1)) {
        $block['slider'] = get_pages(1, $last, $cur);
    } elseif ($cur <= ($window - 1)) {
        $block['slider'] = get_pages(1, $window + 1, $cur);
    } elseif ($cur > ($last - $window + 1)) {
        $block['slider'] = get_pages($last - ($window), $last, $cur);
    } else {
        $block['slider'] = get_pages($cur - $side, $cur + $side, $cur);
    }
    $html = "";
    if (is_array($block['slider'])) {
        $html .= getLinks($block['slider'], $cur, $last);
    }

    return $html;
}

function get_pages($start, $end, $cur = 1)
{
    $pages = [];
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $cur) {
            $pages[$i] = "<li class='page_active'><span>{$i}</span></li>";
        } else {
            $pages[$i] = "<li class='get_page' data-page='{$i}'><a href='javascript:;'>{$i}</a></li>";
        }
    }

    return $pages;
}

function getLinks($page, $cur, $last)
{
    $data = "<ul class='paginator'>";
    if ($cur == 1) {
        $data .= "<li class='page_active'><span>首页</span></li>";
        $data .= "<li class='page_active'><span>上一页</span></li>";
    } else {
        $data .= "<li class='get_page' data-page='1'><a href='javascript:;'>首页</a></li>";
        $data .= "<li class='get_page' data-page='".($cur-1)."'><a href='javascript:;'>上一页</a></li>";
    }
    foreach ($page as $value) {
        $data .= $value;
    }

    if ($cur == $last) {
        $data .= "<li class='page_active'><span>下一页</span></li>";
        $data .= "<li class='page_active'><span>尾页</span></li>";
    } else {
        $data .= "<li class='get_page' data-page='".($cur+1)."'><a href='javascript:;'>下一页</a></li>";
        $data .= "<li class='get_page' data-page='{$last}'><a href='javascript:;'>尾页</a></li>";
    }

    return $data;
}