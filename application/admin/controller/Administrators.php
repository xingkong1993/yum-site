<?php
/**
 * Created by PhpStorm.
 * User: chenx
 * Date: 2018/7/14
 * Time: 20:50
 */

namespace app\admin\controller;


use app\common\controller\Admin;
use think\Db;

class Administrators extends Admin
{
    public function index()
    {
        $condition = [];
        $param = $this->request->param();
        $param = array_filter($param);
        if(!empty($param['realname'])) $condition['realname'] = ['like',"%{$param["realname"]}%"];
        if(!empty($param['account'])) $condition['account'] = ['like',"%{$param["account"]}%"];
        if(!empty($param['is_admin'])) $condition['is_admin'] = $param['is_admin'];
        $data = model("AdminBaseModel")->get_pages("admin", $condition, 30, ["query" => $param]);
        if (!empty($data['result'])) {
            foreach ($data['result'] as &$value) {
                $value['old'] = '';
                if (!empty($value['birthday'])) {
                    $olday = time() - $value['birthday'];
                    $value['old'] = floor($olday / (24 * 3600 * 365));
                    $value['birthday'] = date("m月d日", $value['birthday']);
                }
                if ($value['is_admin'] < 1) $value['role'] = "超级管理员";
                else {
                    $value['role'] = Db::name("role")->where(["id" => $value['is_admin']])->value("name");
                }
            }
        }
        $con = [
            "thisHref" => "administrators/index",
            "special" => ['delete' => ["administrators/delete"]],
            "%name" => "account",
            "query" => [
                "id" => "%id"
            ]
        ];
        $option = get_menu_string($con, ['administrators/add']);
        $this->assign("option", json_encode($option));
        $this->assign($data);
        $this->assign("roles",Db::name("role")->where(['status'=>1])->column("id,name"));
        $this->assign($param);
        return $this->fetch();
    }

    public function add()
    {
        $role = Db::name("role")->where(['status' => 1])->column("id,name");
        $this->assign("roles", $role);
        return $this->fetch("save");
    }

    public function edit()
    {
        $id = $this->request->param("id");
        if ($id < 1) $this->error("参数ID不存在！");
        $role = Db::name("role")->where(['status' => 1])->column("id,name");
        $this->assign("roles", $role);
        $this->assign(Db::name("admin")->where(['admin_id' => $id])->find());
        return $this->fetch("save");
    }

    public function save()
    {
        if (!$this->request->isAjax() || !$this->request->isPost()) $this->error("非法数据提交方式");
        $data = $this->request->param();
        $condition = [];
        $data['add_time'] = time();
        if (!empty($data['password'])) {
            list($data['password'], $data['encrypt']) = get_password($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_activation'] = isset($data['is_activation']) ? 1 : 2;
        if (!empty($data['admin_id'])) {
            $condition['admin_id'] = $data['admin_id'];
            unset($data['add_time']);
            unset($data['admin_id']);
        }
        if ($condition) {
            $id = Db::name("admin")->where($condition)->update($data);
        } else {
            $id = Db::name("admin")->insertGetId($data);
        }

        if ($id === false) {
            $this->error("保存数据失败");
        }
        $this->success("保存成功！", url("index"));
    }

    public function delete()
    {
        $id = $this->request->param("id");
        if ($id < 1) $this->error("参数ID不能为空");
        if ($id == session("admin_id")) $this->error("操作错误");
        //判断用户是否为超级管理员
        $is_admin = Db::name("admin")->where(['admin_id' => $id])->value("is_admin");
        if ($is_admin < 1) $this->error("超级管理员禁止删除");
        if (Db::name("admin")->where(['admin_id' => $id])->delete() === false) {
            $this->error("删除管理失败");
        }
        $this->success("管理删除成功");
    }
}