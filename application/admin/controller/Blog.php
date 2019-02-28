<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 10:46
 */

namespace app\admin\controller;


use app\common\controller\Admin;
use think\Db;

class Blog extends Admin
{
    static $BLOG_TYPE = [
        1 => "技术",
        2 => "灌水",
        3 => "讨论",
        4 => "求助",
    ];

    public function index()
    {
        $data = Db::name("blog_item")->order("update_time desc")->paginate(30, false, []);
        $result = $data->toArray()['data'];
        if (!empty($result)) {
            foreach ($result as &$value) {
                $value['add_time'] = date("Y-m-d", $value['add_time']);
                $tag = explode(" ", $value['tag']);
                if (!empty($tag)) {
                    foreach ($tag as &$v) {
                        $v = '<span class="blog-tag">' . $v . '</span>';
                    }
                }

                $value['tag'] = implode(" ", $tag);
                $value['author'] = Db::name("user_account")->where(['uid' => $value['author']])->value("user_name");
                $value['type'] = self::$BLOG_TYPE[$value['type']];
                $value['status_tag'] = $value['status'];
            }
        }
        $data = [
            "data" => $data,
            "result" => $result
        ];
        $con = [
            "thisHref" => "blog/index",
            "%name" => "title",
            "query" => [
                "id" => "%id"
            ]
        ];
        $option = get_menu_string($con, ['blog/add']);
        $this->assign("option", json_encode($option));
        $this->assign($data);
        return $this->fetch();
    }

    public function examine()
    {
        $id = $this->request->param("id");
        if ($id < 1) $this->error("参数id不能为空");
        $blog = Db::name("blog_item")->where(['id' => $id, 'status' => ['in', [2, 3]]])->find();
        if (empty($blog)) $this->error("文章不存在！");
        $this->assign($blog);
        return $this->fetch();
    }

    public function save(){
        if(!$this->request->isPost() || !$this->request->isAjax()) $this->error("非法数据提交方式！");
        $data = $this->request->param();
        if($data['status'] == 4 && empty($data['remarks'])) $this->error("审核建议不能为空！");

        $condition['id'] = $data['id'];
        unset($data['id']);
        $data['update_time'] = time();
        $d = Db::name("blog_item")->where($condition)->update($data);
        if($d === false) $this->error("文章审核失败！");
        $this->success("文章审核成功",url("index"));
    }
}