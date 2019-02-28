<?php
/**
 * Created by PhpStorm.
 * User: chenx
 * Date: 2018/7/7
 * Time: 17:50
 */

namespace app\admin\controller;


use app\common\controller\Admin;
use think\Db;

class OpenWrite extends Admin
{
    public function index(){
        $param = $this->request->param();
        $param = array_filter($param);
        $condition = [];
        if(!empty($param['name'])) $condition['name'] = ['like',"%$param[name]%"];
        if(!empty($param['href'])) $condition['href'] = ['like',"%$param[href]%"];
        $data = model("AdminBaseModel")->get_pages("write",$condition,10,['query'=>$param]);
        $this->assign($data);
        $this->assign($param);
        $con = [
            "thisHref" => "open_write/index",
            "special" => ['delete' => ["open_write/delete"]],
            "%name"=>"name",
            "query"=>[
                "id"=>"%id"
            ]
        ];
        $option = get_menu_string($con, ['open_write/add']);
        $this->assign("option",json_encode($option));
        return $this->fetch();
    }

    public function add(){
        return $this->fetch("save");
    }
    public function edit(){
        $id = $this->request->param("id");
        if(!$id) $this->error("参数id不能为空！");
        $data = Db::name("write")->find($id);
        $this->assign($data);
        return $this->fetch("save");
    }
    public function save(){
        if(!$this->request->isAjax() || !$this->request->isPost()) $this->error("非法数据提交");
        $data = $this->request->param();
        $required = ["href"=>$data['href']];
        $data['status'] = isset($data['status'])?1:2;
        $condition = [];
        if(!empty($data['id'])){
            $condition['id'] = $data['id'];
            $required['id'] = ["<>",$data['id']];
        }
        unset($data['id']);

        $d = model("AdminBaseModel")->saves_write($data,$required,$condition);
        if($d === false) $this->error("保存失败");
        $this->success("保存成功",url("index"));
    }
    public function delete(){
        if(!$this->request->isPost() || !$this->request->isAjax()) $this->error("非法数据提交方式！");
        $id = $this->request->param("id");
        if(!$id) $this->error("参数ID不能为空！");
        $d = Db::name("write")->where(['id'=>$id])->delete();
        if($d === false) $this->error("删除失败");
        $this->success("删除成功");
    }
}