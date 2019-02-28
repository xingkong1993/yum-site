<?php
/**
 * Created by PhpStorm.
 * User: chenx
 * Date: 2018/5/26
 * Time: 10:41
 */

namespace app\admin\controller;


use app\common\controller\Admin;
use think\Db;

class Menu extends Admin
{
    public function index()
    {
        $data = model("adminBaseModel")->get_level_all();
        $this->assign("data", json_encode($data));
        return $this->fetch();
    }

    public function add()
    {
        $menu = model("adminBaseModel")->get_level_cat(2, ['status' => 1]);
        $this->assign("menu", $menu);
        return $this->fetch("save");
    }

    public function edit()
    {
        $id = $this->request->param("id");
        if (!$id) $this->error("参数ID不能为空！");
        $menu = model("adminBaseModel")->get_level_cat(2, ['status' => 1]);
        $this->assign("menu", $menu);
        $this->assign(Db::name("admin_menu")->find($id));
        return $this->fetch("save");
    }

    public function save()
    {
        if (!$this->request->isAjax() || !$this->request->isPost()) $this->error("非法数据提交方式！");
        $data = $this->request->param();
        $data['create_time'] = $data['update_time'] = time();
        $data['status'] = isset($data['status'])? 1 : 2;
        $condition = [];
        if (!empty($data['id'])) {
            $condition['id'] = $data['id'];
            unset($data['create_time']);
        }
        unset($data['id']);
        $d = model("adminBaseModel")->saves("admin_menu",$data, $condition);

        if ($d === false) {
            $this->error("保存失败！");
        }
        $this->success("保存成功！", url("index"));
    }

    public function delete(){
        if(!$this->request->isAjax() || !$this->request->isPost()) $this->error("非法数据提交方式");
        $id = $this->request->param("id");
        if(empty($id)) $this->error("参数ID不能为空");
        $d = model("adminBaseModel")->delete_menu($id);
        if($d === false || is_string($d)) {
            $d = $d === false?"分类删除失败":$d;
            $this->error($d);
        }
        $this->success("删除成功");
    }

    public function menu_list(){
        $admin_group = session("admin_group");
        if($admin_group){
            //获取管理组菜单
        }
        //获取到菜单
        model("adminBaseModel")->get_menu_level_tree(['pid'=>0,"status"=>1],$result);
        return json($result);
    }
}