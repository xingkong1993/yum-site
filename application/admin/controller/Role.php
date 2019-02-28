<?php
/**
 * Created by PhpStorm.
 * User: chenx
 * Date: 2018/7/7
 * Time: 21:53
 */

namespace app\admin\controller;


use app\common\controller\Admin;
use think\Db;

class Role extends Admin
{
    public function index()
    {
        $condition = [];
        $param = $this->request->param();
        $param = array_filter($param);
        if (isset($param['name']) && !empty($param['name'])) $condition['name'] = ['like', "%{$param['name']}%"];
        $data = model("AdminBaseModel")->get_pages("role", $condition, 30, ["query" => $param]);
        if (!empty($data['result'])) {
            foreach ($data['result'] as &$value) {
                $power = unserialize($value['power'])['level1'];
                $value['power'] = Db::name("admin_menu")->where(['id' => ['in', $power]])->column("name");
                foreach ($value['power'] as &$v) {
                    $v = "<span style='display: inline-block;margin-right: 5px;'>{$v}</span>";
                }
                $value['power'] = implode("", $value['power']);
            }
        }
        $con = [
            "thisHref" => "role/index",
            "special" => ['delete' => ["role/delete"]],
            "%name"=>"name",
            "query"=>[
                "id"=>"%id"
            ]
        ];
        $option = get_menu_string($con, ['role/add']);
        $this->assign("option",json_encode($option));
        $this->assign($data);
        $this->assign($param);
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch("save");
    }

    public function edit()
    {
        $id = $this->request->param("id");
        if (empty($id)) $this->error("参数ID不能为空！");
        $this->assign(Db::name("role")->find($id));
        return $this->fetch("save");
    }

    public function save()
    {
        if (!$this->request->isAjax() || !$this->request->isPost()) $this->error("非法数据提交方式！");
        $data = $this->request->param();
        $condition = [];
        $data['status'] = isset($data['status'])? 1 : 2;
        if (!empty($data['id'])) {
            $condition['id'] = $data['id'];
        }
        unset($data['id']);
        $d = model("AdminBaseModel")->saves("role", $data, $condition);
        if ($d === false) $this->error("保存失败！");
        if (!empty($condition)) $d = $condition['id'];
        $this->success("保存成功", url("apply?id={$d}"));
    }

    public function apply()
    {
        $id = $this->request->param("id");
        if (!$id) $this->error("参数id不能为空");
        $data = Db::name("role")->find($id);
        $data['power'] = unserialize($data['power']);
        $data['powers'] = $data["power"]?json_encode($data['power']['all']):json_encode([]);
        $this->assign($data);
        model("AdminBaseModel")->get_menu_level_tree(['status' => 1, "pid" => 0], $power);
        $this->assign("level_tree", ($power));
        return $this->fetch();
    }

    public function apply_save()
    {
        if (!$this->request->isAjax() || !$this->request->isPost()) $this->error("非法数据提交方式");
        $data = $this->request->param();
        if (!isset($data['tree'])) $this->error("请选择权限");
        $level1 = isset($data['tree'][1]) ? $data['tree'][1] : [];
        $level2 = isset($data['tree'][2]) ? $data['tree'][2] : [];
        $level3 = isset($data['tree'][3]) ? $data['tree'][3] : [];
        unset($data['tree']);
        $all = array_merge($level1, array_merge($level2, $level3));
        sort($all);
        $datas['power'] = [
            "level1" => $level1,
            "level2" => $level2,
            "level3" => $level3,
            "all" => $all
        ];
        $datas['power'] = serialize($datas['power']);
        $d = model("AdminBaseModel")->saves("role", $datas, $data);
        if ($d === false) $this->error("保存失败！");
        $this->success("保存成功！", url("index"));
    }

    public function details()
    {
        $id = $this->request->param("id");
        if (!$id) $this->error("参数id不能为空");
        $data = Db::name("role")->find($id);
        $data['power'] = unserialize($data['power']);
        $data['powers'] = json_encode($data['power']['all']);
        $this->assign($data);
        model("AdminBaseModel")->get_menu_level_tree(['status' => 1, "pid" => 0, "id" => ['in', $data['power']['all']]], $power);
        $this->assign("level_tree", ($power));
        return $this->fetch();
    }

    public function delete()
    {
        if (!$this->request->isAjax() || !$this->request->isPost()) $this->error("非法数据提交！");
        $id = $this->request->param("id");
        if (empty($id)) $this->error("数据ID不能为空！");
        //判断是否存在管理员
        $user = Db::name("admin")->where(['is_admin' => $id])->count();
        if ($user > 0) $this->error("请删除或移除管理员后再操作！");
        if (Db::name("role")->where(['id' => $id])->delete() === false) {
            $this->error("删除失败！");
        }
        $this->success("删除成功！");
    }
}