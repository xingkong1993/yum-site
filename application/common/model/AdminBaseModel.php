<?php
/**
 * Created by PhpStorm.
 * User: chenx
 * Date: 2018/6/16
 * Time: 18:00
 */

namespace app\common\model;

use think\Db;
use think\Model;

class AdminBaseModel extends Model
{
    /**
     * 保存数据
     * @param $db
     * @param $data
     * @param array $where
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function saves($db, $data, $where = [])
    {
        if (empty($where)) {
            return Db::name($db)->insertGetId($data);
        }

        return Db::name($db)->where($where)->update($data);
    }

    /**
     * 层级菜单
     * @param int $levels
     * @param array $condition
     * @param bool $attr
     * @return mixed
     */
    public function get_level_cat($levels = 2, $condition = [], $attr = false)
    {
        $condition = array_merge($condition, ['level' => ['<=', $levels]]);
        $result = $this->get_level_all($condition, $attr);
        $this->get_levels($result, 0, $res);
        return $res;
    }

    private function get_levels($data, $id = 0, &$result)
    {
        if (isset($data[$id])) {
            foreach ($data[$id] as $value) {
                $value['parents'] = "顶级分类";
                if ($value['level'] !== 1 && $value['pid'] > 0) {
                    $pid = Db::name("admin_menu")->field("name,level")->find($value['pid']);
                    $value['parents'] = $pid['level'] == 2 ? "|--|-- " . $pid['name'] : "|-- " . $pid['name'];
                }
                $result[] = $value;
                $this->get_levels($data, $value['id'], $result);
            }
        }
    }

    public function get_level_all($condition = [], $attr = false)
    {
        $data = Db::name("admin_menu")->where($condition)->order("sort asc")->select();
        $result = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $value['parents'] = $value['pid'] > 0 ? Db::name("admin_menu")->find($value['pid'])['name'] : '顶级分类';
                $option = "";
                if ($value['level'] < 3) {
                    $option .= '<a href="' . url("add?pid={$value['id']}") . '" class="option-btn">新增分类</a>';
                }
                $dele_fun = "options_confirm({icon:3,obj:this});return false;";
                $option .= '<a href="' . url("edit?id={$value['id']}") . '" class="option-btn">编辑</a><a href="' . url("delete?id={$value['id']}") . '" class="option-btn" onclick="' . $dele_fun . '" data-name="是否删除【' . $value['name'] . '】？" data-type="delete">删除</a>';
                $value['option'] = $option;
                $result[$value['pid']][] = $value;
            }
        }
        return $result;
    }

    /**
     * 删除菜单
     * @param $id
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete_menu($id)
    {
        //判读是否有子节点
        $child = Db::name("admin_menu")->where(['pid' => $id])->count();
        if ($child > 0) return "请删除或移除子分类";
        return Db::name("admin_menu")->where(['id' => $id])->delete();
    }

    /**
     * 菜单树
     * @param array $condition
     * @param $result
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_menu_level_tree(array $condition = [], &$result)
    {
        $result = Db::name("admin_menu")->order("sort")->where($condition)->select();
        if (!$result) return false;
        foreach ($result as &$value) {
//            $condition = [
//                "pid" => $value['id'],
//                "status" => 1
//            ];
            $condition['pid'] = $value['id'];
            $condition['status'] = 1;
            $value['href'] = !preg_match("/^(javascript)$/", $value['href']) ? url($value['href']) : "javascript:;";
            $this->get_menu_level_tree($condition, $value['children']);
        }
    }

    /**
     * 保存白名单信息
     * @param $data
     * @param array $required
     * @param array $condition
     * @return bool|int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function saves_write($data, $required = [], $condition = [])
    {
        //判断是否有重复的白名单数据
        if ($required) {
            $count = Db::name("write")->where($required)->count();
            if ($count > 0) return false;
        }
        if (empty($condition)) return Db::name("write")->insertGetId($data);
        return Db::name("write")->where($condition)->update($data);
    }

    /**
     * 分页数据获取
     * @param $db
     * @param $condition
     * @param int $page
     * @param array $param
     * @return array
     * @throws \think\exception\DbException
     */
    public function get_pages($db, $condition, $page = 30, $param = [])
    {
        $data = Db::name($db)->where($condition)->paginate($page, false, $param);
        return ["data" => $data, "result" => $data->toArray()['data']];
    }

    public function get_page_join($db1, $db2, $join, $condition = [], $page = 30, $param = [])
    {
        $data = Db::name($db1)->alias("db1")->join($db2 . " db2", $join)->where($condition)->paginate($page, false, $param);
        return ["data" => $data, "result" => $data->toArray()['data']];
    }
}