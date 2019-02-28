<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/20
 * Time: 15:34
 */

namespace app\common\controller;


use think\Controller;

class ApiBase extends Controller
{
    protected $param = [];
    const token = "rT9TZRaGbKBfuAu";
    protected $field = [
        "format", "time", "sign", "method", "base"
    ];
    protected $format = ["json", "xml", "base64", "serialize"];
    protected $method = "";
    protected $fun = "";

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        //判断是否符合提交数据规则
        if (!$this->request->isAjax() || !$this->request->isPost()) {
            $header = $this->request->header();
            if (!isset($header['cookie'])) {
                header("HTTP/1.0 203 OK");
            } else {
                header("HTTP/1.0 201 OK");
            }
//
            exit(get_api_return_message(-1, get_encrypt(15), "", "json"));
        }

        $this->param = $this->request->param();
        //验证返回参数是否合法
        $format = $this->param['header']['format'];
        if (!in_array($format, $this->format)) $this->param['header']['format'] = "json";

        //签名验证
        $body = $this->param['body'];
        $base = $this->param["header"]['base'];
        $sign = $this->get_sign($body, $base);
        if (!file_exists("./runtime/logs/sign/" . date("Ymd") . "/")) {
            mkdir("./runtime/logs/sign/" . date("Ymd") . "/", 0777, true);
        }
        @file_put_contents("./runtime/logs/sign/" . date("Ymd") . "/sign.log", date(DATE_ATOM) . "\r\n", FILE_APPEND);
        @file_put_contents("./runtime/logs/sign/" . date("Ymd") . "/sign.log", "系统sign:" . $sign . "\r\n", FILE_APPEND);
        @file_put_contents("./runtime/logs/sign/" . date("Ymd") . "/sign.log", "客户端sign:" . $this->param['header']['sign'] . "\r\n", FILE_APPEND);
        if ($sign !== $this->param["header"]['sign']) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(2, get_encrypt(15), "", $this->param['header']['format']));
        }

        $method = explode("/", $this->param['header']['method']);
        $field = $this->getFields($method);
        $this->method = array_shift($method);
        $this->fun = end($method);
        $this->field = array_merge($field, $this->field);
    }

    private function get_sign($body, $base = "md5")
    {
        ksort($body);
        $k = "";
        foreach ($body as $key => $value) {
            $value = (string)$value;
            $value = strip_tags($value);
            if (preg_match("/[\x{4e00}-\x{9fa5}]+/u", $value)) {
                $value = urlencode($value);
                $value = preg_replace("/\+/", "%20", $value);
            }
            if (strlen($value) > 1 && strlen($value) < 180)
                $k .= $key . $value;
        }
        return $base($k . self::token);
    }

    private function getFields($method)
    {
        foreach ($method as &$value) {
            $value = ucfirst($value);
        }
        $method = implode($method);
        switch ($method) {
            case "UserSign":
                return ["account", "password"];
                break;
            case "UserRegister":
                return ["account", "password", "captcha"];
                break;
            case "UserEmail":
            case "UserEdit":
            case "UserScore":
            case "UserBlog_list":
            case "UserUser_account_message":
                return ["uid"];
                break;
            case "UserCodeEmail":
                return ["uid", "base"];
                break;
            case "UploadUser":
                return ["uid", "files"];
                break;
            case "BlogEdit":
                return ["uid", "content", "title", "type"];
                break;
            case "BlogFinds":
                return ["uid", "id"];
                break;
            case "BlogDetails":
            case "BlogComment":
                return ["id"];
                break;
            case "ThirdCodes":
                return ['types'];
                break;
            case "ThirdLogin":
                return ['id', "from"];
                break;
            case "BlogDels":
                return ["uid", "id", "status"];
                break;
            case "BlogApply":
                return ["uid", "id"];
                break;
            default:
                return [];
                break;
        }
    }

}