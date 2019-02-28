<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/26
 * Time: 12:47
 */

namespace app\api\logic;


use think\Db;

/**
 * 文章管理
 * Class Blog
 * @package app\api\logic
 */
class Blog
{
    /**
     * 编辑文章
     * @param $fileds
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function edit($fileds)
    {
        $data = [
            "title" => $fileds['title'],
            "tag" => $fileds['tag'],
            "blog_icon" => get_upload_image($fileds['blog_icon'], "blog", $fileds['uid']),
            "type" => $fileds['type'],
            "author" => $fileds['uid'],
            "from" => 1,
            "is_comment" => isset($fileds['is_comment']) && $fileds['is_comment'] == "on" ? 1 : 2,
            "content" => htmlspecialchars($fileds['content']),
            "status" => 2,
            "update_time" => time()
        ];
        $condition = [];
        if (!empty($fileds['items_id'])) {
            $condition['id'] = $fileds['items_id'];
        } else {
            $data['add_time'] = time();
        }

        if (empty($condition)) {
            $id = Db::name("blog_item")->insertGetId($data);
        } else {
            $id = Db::name("blog_item")->where($condition)->update($data);
        }

        if ($id === false) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(30001, $fileds['sign'], $fileds['method'], $fileds['format'], "", false, $data['title']));
        }
        exit(get_api_return_message(0, $fileds['sign'], $fileds['method'], $fileds['format'], "文章发布成功！", false));
    }

    /**
     * 查看文章
     * @param $fields
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function finds($fields)
    {
        //判断用户是否是同一用户
        $blog = Db::name("blog_item")->where(['id' => $fields['id'], "author" => $fields['uid'], "from" => 1, "status" => ['in', [2, 4]]])->find();
        if (empty($blog)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(30002, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        $blog['blog_icon_src'] = $blog['blog_icon'] ? config("icon_src") . $blog['blog_icon'] : "";
        $blog['content'] = htmlspecialchars_decode($blog['content']);
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "", $blog));
    }

    /**
     * 文章详情
     * @param $fields
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function details($fields)
    {
        $blog = Db::name("blog_item")->where(['id' => $fields['id']])->find();
        if (empty($blog)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(30002, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        if (!empty($fields["uid"]) && $fields['uid'] != $blog['author'] && $blog['status'] != 1) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(30002, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        $blog['image'] = !empty($blog['blog_icon']) ? config("icon_src") . $blog['blog_icon'] : "";
        $author = Db::name("user_account")->where(['uid' => $blog['author']])->find();
        if (empty($author)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(30002, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }
        $author['tou'] = "";
        if (!empty($author['icon'])) {
            $author['tou'] = preg_match("/^(http:\/\/)|(https:\/\/)/", $author['icon']) ? $author['icon'] : config("icon_src") . $author['icon'];
        }
        $blog['author'] = $author;
        $blog['content'] = htmlspecialchars_decode($blog['content']);
        $blog['add_time'] = date("m-d H:i:s", $blog['add_time']);
        $blog['tag'] = explode(" ", $blog['tag']);
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "", $blog));
    }

    public function comment($fields)
    {
        $comment = Db::name("blog_comment")->where(['id' => $fields['id'], "type" => 1])->paginate(15);
    }

    /**
     * 删除文章
     * @param $fields
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function dels($fields)
    {
        //判断是物理删除还是逻辑删除
        $is_del = false;
        $condition = [
            'id' => $fields['id'],
            "author" => $fields['uid'],
            "status" => ['<>', 5]
        ];
        if ($fields['status'] != 5) {
            $is_del = true;
            $condition['status'] = 5;
        }
        $data = Db::name("blog_item")->where($condition)->find();
        if (empty($data)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(30002, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        if ($is_del) {
            $del = Db::name("blog_item")->where(['id' => $data['id']])->delete();
        } else {
            $del = Db::name("blog_item")->where(['id' => $data['id']])->update(['status' => 5]);
        }

        if ($del === false) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(30002, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "删除文章成功！", false));
    }

    /**
     * 恢复删除文章
     * @param $fields
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function apply($fields)
    {
        $condition = [
            'id' => $fields['id'],
            "author" => $fields['uid'],
            "status" => 5
        ];
        $blog = Db::name("blog_item")->where($condition)->find();
        if (empty($blog)) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(30002, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }

        if (Db::name("blog_item")->where(['id' => $blog['id']])->update(['status' => 1]) === false) {
            header("HTTP/1.0 201 OK");
            exit(get_api_return_message(30002, $fields['sign'], $fields['method'], $fields['format'], "", false));
        }
        exit(get_api_return_message(0, $fields['sign'], $fields['method'], $fields['format'], "恢复成功！", false));
    }
}