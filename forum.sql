/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : forum

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-07-22 15:54:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for forum_admin
-- ----------------------------
DROP TABLE IF EXISTS `forum_admin`;
CREATE TABLE `forum_admin` (
  `admin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` char(32) NOT NULL COMMENT '管理员账号',
  `password` char(64) NOT NULL COMMENT '密码',
  `ip` char(32) NOT NULL COMMENT '登陆ip',
  `is_activation` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '是否激活？1--已激活 2--未激活',
  `nick_name` char(32) DEFAULT NULL COMMENT '昵称',
  `login_time` int(10) unsigned NOT NULL COMMENT '最近登陆时间',
  `is_admin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理组',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '3' COMMENT '性别 1--男 2--女 3--保密',
  `encrypt` char(32) DEFAULT NULL COMMENT '登录短密码',
  `icon` text COMMENT '头像',
  `mobile` char(18) DEFAULT NULL COMMENT '手机号码',
  `email` varchar(255) DEFAULT NULL COMMENT '邮箱',
  `realname` varchar(255) DEFAULT NULL COMMENT '真实名称',
  `birthday` int(10) unsigned DEFAULT NULL COMMENT '出生日期',
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员';

-- ----------------------------
-- Records of forum_admin
-- ----------------------------
INSERT INTO `forum_admin` VALUES ('1', 'admin', 'aff476b60fd1b6c46232a3eea08da366', '171.88.42.146', '1', '搬砖的小白', '1506320037', '0', '1498192797', '3', 'OSNpeNqXSg', null, null, null, null, '727113600');

-- ----------------------------
-- Table structure for forum_admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `forum_admin_menu`;
CREATE TABLE `forum_admin_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT '0' COMMENT '上级id',
  `name` char(32) DEFAULT NULL COMMENT '名称',
  `jingle` char(32) DEFAULT NULL COMMENT '别名',
  `href` text COMMENT '跳转链接',
  `level` tinyint(1) unsigned DEFAULT '1' COMMENT '等级',
  `sort` int(4) unsigned DEFAULT NULL COMMENT '排序',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 1--启用 2--禁用',
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='系统后台菜单管理';

-- ----------------------------
-- Records of forum_admin_menu
-- ----------------------------
INSERT INTO `forum_admin_menu` VALUES ('1', '0', '首页', 'index', 'javascript', '1', '1', 'fa fa-home', '1', '1529147711', '1531021790');
INSERT INTO `forum_admin_menu` VALUES ('4', '1', '欢迎页', 'welcome', 'index/index?webs=welcome', '2', '1', '', '1', '1529292656', '1529292656');
INSERT INTO `forum_admin_menu` VALUES ('6', '0', '权限中心', 'power', 'javascript', '1', '3', 'fa fa-lock', '1', '1530934634', '1531020289');
INSERT INTO `forum_admin_menu` VALUES ('7', '6', '菜单管理', 'menu', 'menu/index', '2', '1', '', '1', '1530934662', '1531020115');
INSERT INTO `forum_admin_menu` VALUES ('8', '7', '新增菜单', 'menu add', 'menu/add', '3', '1', '', '1', '1530934854', '1530934854');
INSERT INTO `forum_admin_menu` VALUES ('9', '7', '编辑菜单', 'menu edit', 'menu/edit', '3', '1', '', '1', '1530934943', '1530934943');
INSERT INTO `forum_admin_menu` VALUES ('10', '7', '删除菜单', 'menu delete', 'menu/delete', '3', '1', '', '1', '1530934977', '1530934977');
INSERT INTO `forum_admin_menu` VALUES ('11', '6', '白名单管理', 'write', 'open_write/index', '2', '1', '', '1', '1530956796', '1530956796');
INSERT INTO `forum_admin_menu` VALUES ('12', '11', '新增名单', 'write add', 'open_write/add', '3', '1', '', '1', '1530963832', '1530963832');
INSERT INTO `forum_admin_menu` VALUES ('13', '11', '编辑名单', 'write edit', 'open_write/edit', '3', '1', '', '1', '1530963880', '1530963880');
INSERT INTO `forum_admin_menu` VALUES ('14', '11', '删除名单', 'write delete', 'open_write/delete', '3', '1', '', '1', '1530970686', '1530970686');
INSERT INTO `forum_admin_menu` VALUES ('15', '6', '角色管理', 'role', 'role/index', '2', '1', '', '1', '1530971574', '1530971574');
INSERT INTO `forum_admin_menu` VALUES ('16', '0', '系统管理', 'system', 'javascript', '1', '2', 'fa fa-gear', '1', '1531020243', '1531020299');
INSERT INTO `forum_admin_menu` VALUES ('17', '0', '博客中心', 'blog', 'javascript', '1', '4', 'fa fa-file-text', '1', '1531020432', '1531020442');
INSERT INTO `forum_admin_menu` VALUES ('18', '0', '下载中心', 'download', 'javascript', '1', '5', 'fa fa-download', '1', '1531020491', '1531020491');
INSERT INTO `forum_admin_menu` VALUES ('19', '0', '用户中心', 'user center', 'javascript', '1', '2', 'fa fa-user', '1', '1531020536', '1531020536');
INSERT INTO `forum_admin_menu` VALUES ('20', '15', '新增角色', 'role add', 'role/add', '3', '1', '', '1', '1531021094', '1531021094');
INSERT INTO `forum_admin_menu` VALUES ('21', '15', '编辑角色', 'role edit', 'role/edit', '3', '1', '', '1', '1531021136', '1531021136');
INSERT INTO `forum_admin_menu` VALUES ('22', '15', '查看角色', 'role details', 'role/details', '3', '1', '', '1', '1531021332', '1531021332');
INSERT INTO `forum_admin_menu` VALUES ('23', '15', '删除角色', 'role delete', 'role/delete', '3', '1', '', '1', '1531021373', '1531021373');
INSERT INTO `forum_admin_menu` VALUES ('24', '15', '角色授权', 'applies', 'role/apply', '3', '1', '', '1', '1531021416', '1531021416');
INSERT INTO `forum_admin_menu` VALUES ('25', '16', '管理员', 'admin', 'administrators/index', '2', '1', '', '1', '1531021892', '1531021892');

-- ----------------------------
-- Table structure for forum_role
-- ----------------------------
DROP TABLE IF EXISTS `forum_role`;
CREATE TABLE `forum_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(32) DEFAULT NULL COMMENT '角色名称',
  `power` longtext COMMENT '权限组',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 1--有效 2--无效',
  `remarks` text COMMENT '说明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='角色列表';

-- ----------------------------
-- Records of forum_role
-- ----------------------------
INSERT INTO `forum_role` VALUES ('1', '管理员', 'a:4:{s:6:\"level1\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"6\";}s:6:\"level2\";a:4:{i:0;s:1:\"4\";i:1;s:1:\"7\";i:2;s:2:\"11\";i:3;s:2:\"15\";}s:6:\"level3\";a:7:{i:0;s:1:\"8\";i:1;s:1:\"9\";i:2;s:2:\"12\";i:3;s:2:\"13\";i:4;s:2:\"20\";i:5;s:2:\"21\";i:6;s:2:\"24\";}s:3:\"all\";a:13:{i:0;s:1:\"1\";i:1;s:1:\"4\";i:2;s:1:\"6\";i:3;s:1:\"7\";i:4;s:1:\"8\";i:5;s:1:\"9\";i:6;s:2:\"11\";i:7;s:2:\"12\";i:8;s:2:\"13\";i:9;s:2:\"15\";i:10;s:2:\"20\";i:11;s:2:\"21\";i:12;s:2:\"24\";}}', '1', '');

-- ----------------------------
-- Table structure for forum_write
-- ----------------------------
DROP TABLE IF EXISTS `forum_write`;
CREATE TABLE `forum_write` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(32) DEFAULT NULL COMMENT '名称',
  `href` char(255) DEFAULT NULL COMMENT '路径',
  `jingle` varchar(255) DEFAULT NULL COMMENT '说明',
  `status` tinyint(3) unsigned DEFAULT NULL COMMENT '状态 1--有效 2--无效',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='白名单';

-- ----------------------------
-- Records of forum_write
-- ----------------------------
INSERT INTO `forum_write` VALUES ('1', '保存菜单', 'menu/save', null, '1');
INSERT INTO `forum_write` VALUES ('2', '保存白名单', 'open_write/save', null, '1');
INSERT INTO `forum_write` VALUES ('3', '保存角色', 'role/save', null, '1');
INSERT INTO `forum_write` VALUES ('4', '保存授权信息', 'role/apply_save', '', '1');
