<?php
/**
 * Created by PhpStorm.
 * User: chenx
 * Date: 2018/2/25
 * Time: 10:36
 */

namespace app\common\controller;


use think\Controller;
use think\Db;
use think\Request;

class Admin extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if (!$this->is_login()) {
            $this->redirect("user_center/sign");
        }

        if($this->request->isMobile()){
            $this->redirect("user_center/mobile");
        }
        $this->adminstor();
    }

    /**
     * 判断当前用户是否有效登陆
     * @return bool
     */
    private function is_login()
    {
        $admin = session("admin_id");
        if (empty($admin)) return false;
        $admin_account = Db::name("admin")->where(['admin_id' => $admin])->find();
        if (empty($admin_account)) {
            session("admin_id", null);
            return false;
        }
        return true;
    }

    private function adminstor()
    {
        $this->assign(site_config());
        $this->assign("login_user", Db::name("admin")->where(['admin_id' => session("admin_id")])->find());
        $path = $this->request->path();
        $path = strtolower($path);
        preg_match("/^[a-z1-9_]+\/([a-z1-9_]+\/|[a-z1-9_]+$){1,2}/m", $path, $path);
        if (count($path) > 0) {
            $path = explode("/", $path[0]);
            $path = count($path) >= 2 ? $path[0] . "/" . $path[1] :$path[0]. "/index";
            $menu = Db::name("admin_menu")->field("href,name,jingle,level,pid")->where(['href' => ['like', "$path%"]])->find();
            if ($menu['level'] < 3) {
                $this->assign("menu_title", $menu);
                $this->assign("path",true);
            } else {
                $menu['last'] = true;
                $parent = Db::name("admin_menu")->where(['id' => $menu['pid']])->field("href,name,jingle,level,pid")->find();
                if ($parent) $parent['last'] = false;
                $menu = [$parent, $menu];
                $this->assign("menu_title", array_values($menu));
            }
        }
    }
}