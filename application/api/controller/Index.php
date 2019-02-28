<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/20
 * Time: 15:33
 */

namespace app\api\controller;


use app\common\controller\ApiBase;

class Index extends ApiBase
{
    public function index()
    {
        //验证参数是否存在
        $fields = array_merge($this->param['header'], $this->param['body']);
        $f = $this->fields($fields);
        if ($f !== true) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(1, $fields['sign'], $fields['method'], $fields['format'], "", false, $f));
        }

        $logic = logic($this->method);
        $fun = $this->fun;
        try{
            $logic->$fun($fields);
        }catch (\Exception $e){
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(-1, $fields['sign'], $fields['method'], $fields['format'], "数据错误！", $e->getMessage()));
        }

    }

    private function fields($fields)
    {
        $key = array_keys($fields);
        $flag = true;
        foreach ($this->field as $value) {
            if (!in_array($value, $key)) {
                $flag = $value;
                break;
            }
            if (!$fields[$value]) {
                $flag = $value;
                break;
            }
        }

        return $flag;
    }
}