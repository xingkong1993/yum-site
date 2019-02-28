<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/27
 * Time: 16:52
 */

namespace app\admin\controller;


use app\common\controller\Admin;
use think\Cache;
use think\Db;

class WebSiteSetting extends Admin
{
    public function index()
    {
        return $this->fetch();
    }

    public function edit()
    {
        return $this->fetch("save");
    }

    public function save()
    {
        if (!$this->request->isPost() || !$this->request->isAjax()) $this->error("非法数据提交方式！");
        $data = $this->request->param();
        $data = array_filter($data);
        $datas = site_config();
        if (!empty($data['site_status'])) $data['site_status'] = 1;
        else $data['site_status'] = 2;
        //处理图片信息
        if (!empty($data['site_logo'])) {
            $logo = upload_site($data['site_logo'], "site_logo");
            if ($logo) $data['site_logo'] = $logo;
            else $data['site_logo'] = "";
        } else {
            @unlink("./static/image/site/site_logo.png");
            $data['site_logo'] = "";
        }

        if (!empty($data['site_sina_weibo'])) {
            $site_sina_weibo = upload_site($data['site_sina_weibo'], "site_sina_weibo");
            if ($site_sina_weibo) $data['site_sina_weibo'] = $site_sina_weibo;
            else $data['site_sina_weibo'] = "";
        } else {
            @unlink("./static/image/site/site_sina_weibo.png");
            $data['site_sina_weibo'] = "";
        }

        if (!empty($data['site_wechat'])) {
            $site_wechat = upload_site($data['site_wechat'], "site_wechat");
            if ($site_wechat) $data['site_wechat'] = $site_wechat;
            else $data['site_wechat'] = "";
        } else {
            @unlink("./static/image/site/site_wechat.png");
            $data['site_wechat'] = "";
        }

        if (!empty($data['site_mini_app'])) {
            $site_mini_app = upload_site($data['site_mini_app'], "site_mini_app");
            if ($site_mini_app) $data['site_mini_app'] = $site_mini_app;
            else $data['site_mini_app'] = "";
        } else {
            @unlink("./static/image/site/site_mini_app.png");
            $data['site_mini_app'] = "";
        }

        if (Db::name("site_config")->where($datas)->update($data) === false) {
            $this->error("配置保存失败");
        }
        Cache::rm("site_config");
        $this->success("配置保存成功", url("index"));
    }
}