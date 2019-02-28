<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/18
 * Time: 14:20
 */

namespace app\admin\controller;


use app\common\controller\Admin;
use think\Db;

class EmailTpl extends Admin
{
    public function index()
    {
        $data = model("AdminBaseModel")->get_pages("email_template", [], 30, ["query" => []]);
        if (!empty($data['result'])) {
            foreach ($data['result'] as &$value) {
                $value['type'] = config("email_template.{$value['type']}");
                $value['tpl_time'] = date("Y-m-d H:i:s", $value['tpl_time']);
            }
        }
        $this->assign($data);
        $con = [
            "thisHref" => "email_tpl/index",
            "special" => ['delete' => ["email_tpl/delete"]],
            "%name" => "type",
            "query" => [
                "id" => "%id"
            ]
        ];
        $option = get_menu_string($con, ['email_tpl/add']);
        $this->assign("option", json_encode($option));
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch("save");
    }

    public function edit()
    {
        $id = $this->request->param("id");
        if ($id < 1) $this->error("参数ID不能为空！");
        $this->assign(Db::name("email_template")->where(['id' => $id])->find());
        return $this->fetch("save");
    }

    public function save()
    {
        if (!$this->request->isAjax() || !$this->request->isPost()) $this->error("非法数据提交");
        $data = $this->request->param();
        $data['state'] = isset($data['state']) && $data['state'] == 'on' ? 1 : 2;
        $data['tpl_time'] = time();
        if (empty($data['content'])) $this->error("请输入模板内容");
        $data['content'] = htmlspecialchars($data['content']);
        $condition = [];
        if ($data['id']) {
            unset($data['tpl_time']);
            $condition['id'] = $data['id'];
        }
        unset($data['id']);
        if ($condition) {
            $id = Db::name("email_template")->where($condition)->update($data);
        } else {
            $id = Db::name("email_template")->insertGetId($data);
        }

        if ($id === false) {
            $this->error("保存失败");
        }
        $this->success("保存成功", url("index"));
    }

    public function details()
    {
        $id = $this->request->param("id");
        if ($id < 1) $this->error("参数ID不能为空！");
        $this->assign(Db::name("email_template")->where(['id' => $id])->find());
        return $this->fetch();
    }
}