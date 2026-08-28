SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for vt_system_area
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_area`;
CREATE TABLE `vt_system_area` (
  `areaid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '地区ID',
  `areaname` varchar(50) NOT NULL DEFAULT '' COMMENT '地区名',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级地区ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `childs` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '直接子级数',
  `listorder` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序ID',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT ''COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`areaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='地区数据';

-- ----------------------------
-- Records of vt_system_area
-- ----------------------------
INSERT INTO `vt_system_area` VALUES ('1', '北京', '0', '', '0', '1', '', '', '0', '0', '0');

-- ----------------------------
-- Table structure for vt_system_category
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_category`;
CREATE TABLE `vt_system_category` (
  `catid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '类别标题',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '图标',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `sign` varchar(10) NOT NULL DEFAULT '' COMMENT '扩展标识',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '区分',
  `listorder` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(默认1显示)',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT ''COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`catid`),
  KEY `type` (`type`) USING BTREE,
  KEY `sign` (`sign`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='公用类别';

-- ----------------------------
-- Records of vt_system_category
-- ----------------------------
INSERT INTO `vt_system_category` VALUES ('1', '首页', 'layui-icon-home', '0', '', '', '01', '1', '0', '', '', '0', '0', '0');
INSERT INTO `vt_system_category` VALUES ('2', '首页', 'layui-icon-home', '0', '', '', '02', '1', '0', '', '', '0', '0', '0');

-- ----------------------------
-- Table structure for vt_system_dict
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_dict`;
CREATE TABLE `vt_system_dict` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '项名',
  `value` varchar(100) NOT NULL DEFAULT '' COMMENT '项值',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `listorder` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0禁用,1启用',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `parentid` (`parentid`) USING BTREE,
  KEY `groupid` (`groupid`) USING BTREE,
  KEY `state` (`state`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='字典项目';

-- ----------------------------
-- Records of vt_system_dict
-- ----------------------------
INSERT INTO `vt_system_dict` VALUES ('1', '支付宝支付', '支付宝支付', '4', '0', '', '100', '1', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict` VALUES ('2', '微信支付', '微信支付', '4', '0', '', '100', '1', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict` VALUES ('3', '银联支付', '银联支付', '4', '0', '', '100', '1', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict` VALUES ('4', '企业支付', '企业支付', '4', '1', '1', '100', '1', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict` VALUES ('5', '个体支付', '个体支付', '4', '1', '1', '100', '1', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict` VALUES ('6', '个', '个', '5', '0', '', '100', '1', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict` VALUES ('7', '件', '件', '5', '0', '', '100', '1', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict` VALUES ('8', '部', '部', '5', '0', '', '100', '1', '', '', '0', '0', '0');

-- ----------------------------
-- Table structure for vt_system_dict_group
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_dict_group`;
CREATE TABLE `vt_system_dict_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `code` varchar(30) NOT NULL DEFAULT '' COMMENT '字典编码',
  `sql` varchar(500) NOT NULL DEFAULT '' COMMENT 'SQL查表语句',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '字典类型0:类型1:列表2:树形n其他',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '为字典类时的父级ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `note` varchar(200) NOT NULL DEFAULT '' COMMENT '备注',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='字典分组';

-- ----------------------------
-- Records of vt_system_dict_group
-- ----------------------------
INSERT INTO `vt_system_dict_group` VALUES ('1', '字典类型', '', '', '0', '0', '', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict_group` VALUES ('2', '列表', '', '', '0', '1', '1', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict_group` VALUES ('3', '树形', '', '', '0', '1', '1', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict_group` VALUES ('4', '支付类型', 'PAY_TYPE', '', '3', '0', '', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict_group` VALUES ('5', '常用单位', 'DAN_TYPE', '', '2', '0', '', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_dict_group` VALUES ('6', '组织机构', 'ORGAN', 'SELECT id,title as name,id as value,parentid as pid,arrparentid as pids FROM vt_system_organ WHERE del_time = 0', '3', '0', '', 'SQL调用其他表演示', '', '', '0', '0', '0');

-- ----------------------------
-- Table structure for vt_system_login_log
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_login_log`;
CREATE TABLE `vt_system_login_log` (
  `logid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '登录帐号',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `passsalt` varchar(8) NOT NULL DEFAULT '' COMMENT '秘钥',
  `admin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '登录类型0:后台1:会员',
  `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `logintime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `message` varchar(255) NOT NULL DEFAULT '' COMMENT '状态信息',
  `agent` varchar(255) NOT NULL DEFAULT '' COMMENT '登录端设备信息',
  PRIMARY KEY (`logid`),
  KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='登录日志';

-- ----------------------------
-- Table structure for vt_system_manager
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_manager`;
CREATE TABLE `vt_system_manager` (
  `userid` mediumint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '帐号',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `passsalt` varchar(8) NOT NULL COMMENT '秘钥',
  `roleid` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '角色ID',
  `roleids` varchar(100) NOT NULL DEFAULT '' COMMENT '角色ID集',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `truename` varchar(30) NOT NULL DEFAULT '' COMMENT '姓名',
  `nickname` varchar(30) NOT NULL DEFAULT '' COMMENT '昵称',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别:1男2女',
  `face` varchar(100) NOT NULL DEFAULT '' COMMENT '头像',
  `mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '手机',
  `points` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '积分余额',
  `total_points` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '累计充值积分',
  `email` varchar(30) NOT NULL DEFAULT '' COMMENT '邮箱',
  `areaid` varchar(30) NOT NULL DEFAULT '0' COMMENT '地区ID串',
  `address` varchar(100) NOT NULL DEFAULT '' COMMENT '详细地址',
  `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '最近登录IP',
  `logins` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `logintime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最近登录时间',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='管理用户';

-- Records of vt_system_manager
-- ----------------------------
INSERT INTO `vt_system_manager` VALUES ('1', 'admin', '5948e769e5b5fb287d7e76b4203aec69', '25abcde1', '1', '1', '1', '超管员', '超管', '1', '', '15900000001', '99940.00', '0.00', '26843818@qq.com', '20', '广州', '127.0.0.1', '20', '1787835524', '1', '', '', '1749630366', '1787835524', '0');


-- ----------------------------
-- Table structure for vt_system_manager_log
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_manager_log`;
CREATE TABLE `vt_system_manager_log` (
  `logid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '用户',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `logtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`logid`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='管理日志';

-- ----------------------------
-- Table structure for vt_system_menus
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_menus`;
CREATE TABLE `vt_system_menus` (
  `menuid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `catid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '插件标识名称',
  `menu_name` varchar(50) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `role_name` varchar(50) NOT NULL DEFAULT '' COMMENT '权限名称',
  `link_url` varchar(255) NOT NULL DEFAULT '' COMMENT '外链',
  `menu_url` varchar(255) NOT NULL DEFAULT '' COMMENT '控制路径',
  `role_url` varchar(255) NOT NULL DEFAULT '' COMMENT '权限路径多个逗号隔开',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `listorder` int(10) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态:0菜单不显示',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:后台菜单2:会员菜单',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`menuid`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='菜单权限';

-- Records of vt_system_menus
-- ----------------------------
INSERT INTO `vt_system_menus` VALUES ('1', '1', '', '系统面板', '系统面板', '', '', 'index/index,index/json,index/clear,index/ip', 'layui-icon-home', '0', '1', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('2', '1', '', '系统管理', '系统管理', '', '', '', 'layui-icon-set', '0', '2', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('3', '1', '', '首页面板', '首页面板', '', 'index/main', 'index/main', '', '1', '1', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('4', '1', '', '插件管理', '插件管理', '', 'addon/index', '', '', '1', '2', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('5', '1', '', '插件列表', '插件列表', '', '', 'addon/index', '', '4', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('6', '1', '', '插件安装', '插件安装', '', '', 'addon/install', '', '4', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('7', '1', '', '插件配置', '插件配置', '', '', 'addon/set', '', '4', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('8', '1', '', '插件卸载', '插件卸载', '', '', 'addon/unstall', '', '4', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('9', '1', '', '配置管理', '配置管理', '', '', 'addon/setting', '', '4', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('10', '1', '', '配置更新', '配置更新', '', '', 'addon/setup', '', '4', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('11', '1', '', '设配置项', '设配置项', '', 'system.setting/build', '', '', '2', '1', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('12', '1', '', '管理配置', '管理配置', '', 'system.setting/index', '', '', '2', '2', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('13', '1', '', '后台菜单', '后台菜单', '', 'system.menus/index', '', '', '2', '3', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('14', '1', '', '用户角色', '用户角色', '', 'system.roles/index', '', '', '2', '4', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('15', '1', '', '用户管理', '用户管理', '', 'system.manager/index', '', '', '1', '3', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('16', '1', '', '上传管理', '上传管理', '', 'system.upload/image', '', '', '2', '6', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('17', '1', '', '文件管理', '文件管理', '', 'system.filemanage/index', '', '', '2', '6', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('18', '1', '', '日志管理', '登录日志', '', 'system.log/index', 'system.log/index', '', '2', '7', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('19', '1', '', '地区管理', '地区管理', '', 'system.area/index', '', '', '2', '8', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('20', '1', '', '短信管理', '短信管理', '', 'system.sms/index', '', '', '2', '9', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('21', '1', '', '数据维护', '数据维护', '', 'system.database/index', '', '', '2', '10', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('22', '1', '', '在线用户', '在线用户', '', 'system.online/index', '', '', '2', '11', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('23', '1', '', '数据字典', '数据字典', '', 'system.dict/index', '', '', '2', '12', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('24', '1', '', '单据设序', '单据设序', '', 'system.sequence/index', '', '', '2', '13', '1', '1', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('25', '1', '', '配置项列表', '配置项列表', '', '', 'system.setting/build', '', '11', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('26', '1', '', '配置项添加', '配置项添加', '', '', 'system.setting/badd', '', '11', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('27', '1', '', '配置项编辑', '配置项编辑', '', '', 'system.setting/bedit', '', '11', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('28', '1', '', '配置项删除', '配置项删除', '', '', 'system.setting/bdel', '', '11', '4', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('29', '1', '', '配置项导出', '配置项导出', '', '', 'system.setting/bout', '', '11', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('30', '1', '', '配置项导入', '配置项导入', '', '', 'system.setting/bup', '', '11', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('31', '1', '', '查看配置', '查看配置', '', '', 'system.setting/index', '', '12', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('32', '1', '', '修改配置', '修改配置', '', '', 'system.setting/edit', '', '12', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('33', '1', '', '查看菜单', '查看菜单', '', '', 'system.menus/index', '', '13', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('34', '1', '', '添加菜单', '添加菜单', '', '', 'system.menus/add', '', '13', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('35', '1', '', '添加菜单批量', '添加菜单批量', '', '', 'system.menus/adds', '', '13', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('36', '1', '', '编辑菜单', '编辑菜单', '', '', 'system.menus/edit', '', '13', '4', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('37', '1', '', '删除菜单', '删除菜单', '', '', 'system.menus/del', '', '13', '5', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('38', '1', '', '菜单重构', '菜单重构', '', '', 'system.menus/reset', '', '13', '6', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('39', '1', '', '菜单类别', '菜单类别', '', '', 'system.menus/category', '', '13', '7', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('40', '1', '', '类别添加', '类别添加', '', '', 'system.menus/catadd', '', '13', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('41', '1', '', '类别编辑', '类别编辑', '', '', 'system.menus/catedit', '', '13', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('42', '1', '', '类别删除', '类别删除', '', '', 'system.menus/catdel', '', '13', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('43', '1', '', '菜单导出', '菜单导出', '', '', 'system.menus/out', '', '13', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('44', '1', '', '菜单导入', '菜单导入', '', '', 'system.menus/up', '', '13', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('45', '1', '', '菜单重构', '菜单重构', '', '', 'system.menus/reset', '', '13', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('46', '1', '', '角色列表', '角色列表', '', '', 'system.roles/index', '', '14', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('47', '1', '', '角色添加', '角色添加', '', '', 'system.roles/add', '', '14', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('48', '1', '', '角色编辑', '角色编辑', '', '', 'system.roles/edit', '', '14', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('49', '1', '', '角色删除', '角色删除', '', '', 'system.roles/del', '', '14', '4', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('50', '1', '', '用户查看', '用户查看', '', '', 'system.manager/index', '', '15', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('51', '1', '', '用户添加', '用户添加', '', '', 'system.manager/add', '', '15', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('52', '1', '', '用户编辑', '用户编辑', '', '', 'system.manager/edit', '', '15', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('53', '1', '', '用户删除', '用户删除', '', '', 'system.manager/del', '', '15', '4', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('54', '1', '', '修改密码', '修改密码', '', '', 'system.manager/changpwd', '', '15', '5', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('55', '1', '', '重置密码', '重置密码', '', '', 'system.manager/resetpwd', '', '15', '6', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('56', '1', '', '用户中心', '用户中心', '', '', 'system.manager/index/info', '', '15', '7', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('57', '1', '', '个人修改', '个人修改', '', '', 'system.manager/edits', '', '15', '8', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('58', '1', '', '机构添加', '机构添加', '', '', 'system.manager/oadd', '', '15', '9', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('59', '1', '', '机构编辑', '机构编辑', '', '', 'system.manager/oedit', '', '15', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('60', '1', '', '机构删除', '机构删除', '', '', 'system.manager/odel', '', '15', '11', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('61', '1', '', '角色切换', '角色切换', '', '', 'system.manager/index/role', '', '15', '12', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('62', '1', '', '上传入口', '上传入口', '', '', '', '', '16', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('63', '1', '', '弹出文件管理', '弹出文件管理', '', '', '', '', '16', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('64', '1', '', '百度编辑器', '百度编辑器', '', '', '', '', '16', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('65', '1', '', '上传图片', '上传图片', '', '', 'system.upload/upfile/image', '', '62', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('66', '1', '', '上传视频', '上传视频', '', '', 'system.upload/upfile/video', '', '62', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('67', '1', '', '上传文件', '上传文件', '', '', 'system.upload/upfile/file', '', '62', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('68', '1', '', '上传音频', '上传音频', '', '', 'system.upload/upfile/audio', '', '62', '4', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('69', '1', '', '文件列表', '文件列表', '', '', 'system.upload/files', '', '63', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('70', '1', '', '文件移动', '文件移动', '', '', 'system.upload/files/move', '', '63', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('71', '1', '', '文件删除', '文件删除', '', '', 'system.upload/files/del', '', '63', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('72', '1', '', '分组添加', '分组添加', '', '', 'system.upload/group/add', '', '63', '4', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('73', '1', '', '分组编辑', '分组编辑', '', '', 'system.upload/group/edit', '', '63', '5', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('74', '1', '', '分组删除', '分组删除', '', '', 'system.upload/group/del', '', '63', '6', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('75', '1', '', '接口配置', '接口配置', '', '', 'system.upload/ueditor/config', '', '64', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('76', '1', '', '上传图片', '上传图片', '', '', 'system.upload/ueditor/image', '', '64', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('77', '1', '', '上传视频', '上传视频', '', '', 'system.upload/ueditor/video', '', '64', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('78', '1', '', '上传附件', '上传附件', '', '', 'system.upload/ueditor/file', '', '64', '4', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('79', '1', '', '图片列表', '图片列表', '', '', 'system.upload/ueditor/listimage', '', '64', '5', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('80', '1', '', '附件列表', '附件列表', '', '', 'system.upload/ueditor/listfile', '', '64', '6', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('81', '1', '', '文件列表', '文件列表', '', '', 'system.filemanage/index', '', '17', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('82', '1', '', '文件名编辑', '文件名编辑', '', '', 'system.filemanage/edit', '', '17', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('83', '1', '', '文件软删除', '文件软删除', '', '', 'system.filemanage/del', '', '17', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('84', '1', '', '文件恢复', '文件恢复', '', '', 'system.filemanage/reset', '', '17', '4', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('85', '1', '', '文件清理', '文件清理', '', '', 'system.filemanage/clear', '', '17', '5', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('86', '1', '', '登录日志', '日志查看', '', '', 'system.log/login', '', '18', '1', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('87', '1', '', '登录日志清理', '日志删除', '', '', 'system.log/ldel', '', '18', '2', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('88', '1', '', '后台日志', '后台日志', '', '', 'system.log/manager', '', '18', '3', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('89', '1', '', '后台日志清理', '日志列表', '', '', 'system.log/mdel', '', '18', '4', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('90', '1', '', '访问日志', '访问日志', '', '', 'system.log/web', '', '18', '5', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('91', '1', '', '访问日志清理', '访问日志清理', '', '', 'system.log/wdel', '', '18', '6', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('92', '1', '', '地区列表', '地区列表', '', '', 'system.area/index', '', '19', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('93', '1', '', '地区添加', '地区添加', '', '', 'system.area/add', '', '19', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('94', '1', '', '地区编辑', '地区编辑', '', '', 'system.area/edit', '', '19', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('95', '1', '', '地区删除', '地区删除', '', '', 'system.area/del', '', '19', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('96', '1', '', '内置导入', '内置导入', '', '', 'system.area/import', '', '19', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('97', '1', '', '发送记录', '发送记录', '', '', 'system.sms/index', '', '20', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('98', '1', '', '发送短信', '发送短信', '', '', 'system.sms/send', '', '20', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('99', '1', '', '记录删除', '记录删除', '', '', 'system.sms/del', '', '20', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('100', '1', '', '数据列表', '数据列表', '', '', 'system.database/index', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('101', '1', '', '数据备份', '数据备份', '', '', 'system.database/backup', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('102', '1', '', '备份列表', '备份列表', '', '', 'system.database/imports', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('103', '1', '', '备份恢复', '备份恢复', '', '', 'system.database/import', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('104', '1', '', '备份删除', '备份删除', '', '', 'system.database/del', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('105', '1', '', '注释修改', '注释修改', '', '', 'system.database/edit', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('106', '1', '', '查看字典', '查看字典', '', '', 'system.database/dict', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('107', '1', '', '备份下载', '备份下载', '', '', 'system.database/download', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('108', '1', '', '数据表修复', '数据表修复', '', '', 'system.database/xiufu', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('109', '1', '', '数据表优化', '数据表优化', '', '', 'system.database/youhua', '', '21', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('110', '1', '', '用户列表', '用户列表', '', '', 'system.online/index', '', '22', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('111', '1', '', '字典列表', '字典列表', '', '', 'system.dict/index', '', '23', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('112', '1', '', '字典添加', '字典添加', '', '', 'system.dict/add', '', '23', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('113', '1', '', '字典编辑', '字典编辑', '', '', 'system.dict/edit', '', '23', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('114', '1', '', '字典删除', '字典删除', '', '', 'system.dict/del', '', '23', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('115', '1', '', '字典组添加', '字典组添加', '', '', 'system.dict/gadd', '', '23', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('116', '1', '', '字典组编辑', '字典组编辑', '', '', 'system.dict/gedit', '', '23', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('117', '1', '', '字典组删除', '字典组删除', '', '', 'system.dict/gdel', '', '23', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('118', '1', '', '字典项列表', '字典项列表', '', '', 'system.dict/items', '', '23', '11', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('119', '1', '', '字典项添加', '字典项添加', '', '', 'system.dict/iadd', '', '23', '12', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('120', '1', '', '字典项批量', '字典项批量', '', '', 'system.dict/iadds', '', '23', '13', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('121', '1', '', '字典项编辑', '字典项编辑', '', '', 'system.dict/iedit', '', '23', '14', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('122', '1', '', '字典项删除', '字典项删除', '', '', 'system.dict/idel', '', '23', '15', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('123', '1', '', '字典项接口', '字典项接口', '', '', 'system.dict/json', '', '23', '16', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('124', '1', '', '设序列表', '设序列表', '', '', 'system.sequence/index', '', '24', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('125', '1', '', '设序添加', '设序添加', '', '', 'system.sequence/add', '', '24', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('126', '1', '', '设序编辑', '设序编辑', '', '', 'system.sequence/edit', '', '24', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('127', '1', '', '设序删除', '设序删除', '', '', 'system.sequence/del', '', '24', '10', '0', '0', '1', '', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('130', '1', '', '蜘蛛池管理', '蜘蛛池管理', '', '', '', 'layui-icon-flag', '0', '5', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('131', '1', '', '站群站点', '站群站点', '', 'pool.site/index', 'pool.site/index,pool.site/json,pool.site/add,pool.site/edit,pool.site/del,pool.site/up', 'layui-icon-website', '130', '1', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('132', '1', '', '模板管理', '模板管理', '', 'pool.template/index', 'pool.template/index,pool.template/json,pool.template/add,pool.template/edit,pool.template/del', 'layui-icon-template', '130', '2', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('133', '1', '', '引擎与计费', '引擎与计费', '', 'pool.engine/index', 'pool.engine/index,pool.engine/json,pool.engine/add,pool.engine/edit,pool.engine/del', 'layui-icon-search', '130', '3', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('134', '1', '', '全部链接', '全部链接', '', 'pool.link/index', 'pool.link/index,pool.link/json,pool.link/del,pool.link/up', 'layui-icon-links', '130', '4', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('135', '1', '', '引导记录', '引导记录', '', 'pool.billing/index', 'pool.billing/index,pool.billing/json', 'layui-icon-chart', '130', '5', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('136', '1', '', '充值订单', '充值订单', '', 'pool.recharge/index', 'pool.recharge/index,pool.recharge/json,pool.recharge/mark', 'layui-icon-rmb', '130', '6', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('137', '1', '', '积分调整', '积分调整', '', '', 'pool.recharge/points', '', '136', '7', '0', '0', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('138', '1', '', '订单删除', '订单删除', '', '', 'pool.recharge/del', '', '136', '8', '0', '0', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('139', '1', '', '链接导出', '链接导出', '', '', 'pool.link/out', '', '134', '9', '0', '0', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('140', '1', '', '我的账户', '我的账户', '', '', '', 'layui-icon-username', '0', '6', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('141', '1', '', '我的概览', '我的概览', '', 'my.index/index', 'my.index/index', 'layui-icon-home', '140', '1', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('142', '1', '', '添加链接', '添加链接', '', 'my.link/add', 'my.link/add,my.link/preview,my.link/submit', 'layui-icon-add-circle', '140', '2', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('143', '1', '', '我的链接', '我的链接', '', 'my.link/index', 'my.link/index,my.link/json,my.link/del,my.link/up,my.link/pause', 'layui-icon-list', '140', '3', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('144', '1', '', '在线充值', '在线充值', '', 'my.recharge/index', 'my.recharge/index,my.recharge/order,my.recharge/query', 'layui-icon-rmb', '140', '4', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('145', '1', '', '财务记录', '财务记录', '', 'my.pointslog/index', 'my.pointslog/index,my.pointslog/json', 'layui-icon-log', '140', '5', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('146', '1', '', '个人中心', '个人中心', '', 'system.manager/index?action=info', 'system.manager/index/info,system.manager/changpwd,system.manager/edits', 'layui-icon-vercode', '140', '6', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('147', '1', '', '内容管理', '内容管理', '', '', '', 'layui-icon-read', '0', '7', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('148', '1', '', '文章分组', '文章分组', '', 'cms.group/index', 'cms.group/index,cms.group/json,cms.group/save,cms.group/del,cms.group/crawl', 'layui-icon-cols', '147', '1', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('149', '1', '', '文章管理', '文章管理', '', 'cms.article/index', 'cms.article/index,cms.article/json,cms.article/save,cms.article/del,cms.article/view,cms.ai/rewrite', 'layui-icon-list', '147', '2', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('150', '1', '', 'AI内容生成', 'AI内容生成', '', 'cms.ai/index', 'cms.ai/index,cms.ai/gen,cms.ai/rewrite', 'layui-icon-engine', '147', '3', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('152', '1', '', 'AI模板', 'AI模板', '', 'pool.aitpl/index', 'pool.aitpl/index,pool.aitpl/gen', 'layui-icon-engine', '130', '7', '1', '1', '1', 'admin', '', '1700289715', '0', '0');
INSERT INTO `vt_system_menus` VALUES ('153', '1', '', 'AI配置', 'AI配置', '', 'system.ai/index', 'system.ai/index,system.ai/save', 'layui-icon-engine', '2', '14', '1', '1', '1', '', '', '1700289715', '0', '0');


-- ----------------------------
-- Table structure for vt_system_online
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_online`;
CREATE TABLE `vt_system_online` (
  `uid` varchar(30) NOT NULL DEFAULT '' COMMENT '编号',
  `userid` varchar(20) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '会员帐号',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '所在路径',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `online` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否在线',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:后台1:会员',
  `last_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后时间',
  UNIQUE KEY `uid` (`uid`,`userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='在线用户';

-- ----------------------------
-- Table structure for vt_system_organ
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_organ`;
CREATE TABLE `vt_system_organ` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '机构简称',
  `titles` varchar(200) NOT NULL DEFAULT '' COMMENT '机构全称',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '所有上级ID串',
  `listorder` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT ''COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='组织机构';

-- ----------------------------
-- Records of vt_system_organ
-- ----------------------------
INSERT INTO `vt_system_organ` VALUES ('1', 'Veitool', 'Veitool总部', '0', '', '1', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_organ` VALUES ('2', '市场部', '市场部', '1', '1', '1', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_organ` VALUES ('3', '售前组', '售前组', '2', '1,2', '1', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_organ` VALUES ('4', '售后组', '售后组', '2', '1,2', '1', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_organ` VALUES ('5', '研发部', '研发部', '1', '1', '1', '', '', '', '0', '0', '0');
INSERT INTO `vt_system_organ` VALUES ('6', '设计部', '设计部', '1', '1', '1', '', '', '', '0', '0', '0');

-- ----------------------------
-- Table structure for vt_system_roles
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_roles`;
CREATE TABLE `vt_system_roles` (
  `roleid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色id',
  `role_name` varchar(50) NOT NULL DEFAULT '' COMMENT '角色名称',
  `role_menuid` text NOT NULL COMMENT '权限菜单项ID串',
  `role_ext` text NOT NULL COMMENT '控制器内扩展权限控制',
  `listorder` int(10) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0禁用',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='管理角色';

-- Records of vt_system_roles
-- ----------------------------
INSERT INTO `vt_system_roles` VALUES ('1', '超级管理员', '', '', '1', '1', '', '', '1552297670', '0', '0');
INSERT INTO `vt_system_roles` VALUES ('2', '管理员', ',1,3,130,131,132,133,134,135,136,137,138,139,147,148,149,', '', '2', '1', '', '', '1552297670', '0', '0');
INSERT INTO `vt_system_roles` VALUES ('3', '注册用户', ',140,141,142,143,144,145,146,', '', '3', '1', 'admin', '', '1475240646', '0', '0');


-- ----------------------------
-- Table structure for vt_system_sequence
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_sequence`;
CREATE TABLE `vt_system_sequence` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '单据类型',
  `code` varchar(30) NOT NULL DEFAULT '' COMMENT '单据标识',
  `prefix` varchar(30) NOT NULL DEFAULT '' COMMENT '单据前缀',
  `day` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当前日期',
  `seq` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '当前号',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT ''COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `code` (`code`) USING BTREE,
  KEY `day` (`day`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='单据编序';

-- ----------------------------
-- Table structure for vt_system_setting
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_setting`;
CREATE TABLE `vt_system_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '名称',
  `title` varchar(32) NOT NULL DEFAULT '' COMMENT '标题',
  `group` varchar(32) NOT NULL DEFAULT '' COMMENT '配置分组',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '类型',
  `value` text NOT NULL COMMENT '配置值',
  `options` text NOT NULL COMMENT '配置项',
  `tips` varchar(200) NOT NULL DEFAULT '' COMMENT '配置提示',
  `relation` varchar(100) NOT NULL DEFAULT '' COMMENT '关联',
  `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐私',
  `listorder` int(10) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `addon` varchar(30) NOT NULL DEFAULT '' COMMENT '插件标识',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0禁用,1启用',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `addon` (`addon`) USING BTREE,
  KEY `group` (`group`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='系统配置';

-- Records of vt_system_setting
-- ----------------------------
INSERT INTO `vt_system_setting` VALUES ('1', 'sys_group', '配置分组', 'system', 'array', 'system:系统
sms:短信
upload:上传', '', '配置分组，每行为一组如：标识:组名', '', '0', '1', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('2', 'sys_type', '配置类型', 'system', 'array', 'text:单行文本
textarea:多行文本
static:静态文本
password:密码
checkbox:复选框
radio:单选按钮
year:年选择器
month:年月选择器
date:日期选择器
time:时间选择器
datetime:日期+时间选择器
switch:开关
array:数组
keyval:键值对
select:下拉框
tags:标签
image:单张图片
images:多张图片
number:数字
upfile:文件上传
colorpicker:取色器
ueditor:百度编辑器
cherrymd:CherryMarkdown
editormd:Editor.md
tinymce:TinyMCE编辑器', '', '配置类型，每行为一组如：标识:说明', '', '0', '2', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('3', 'sys_title', '面板名称', 'system', 'text', 'YanyvSEO', '', '后台管理面板显示的名称', '', '0', '2', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('4', 'site_title', '网站名称', 'system', 'text', 'YanyvSEO', '', '网站标题名称', '', '0', '2', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('5', 'admin_captcha', '后台验证', 'system', 'switch', '0', '', '后台管理员登录是否开启图形验证码', '', '0', '3', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('6', 'admin_log', '后台日志', 'system', 'switch', '1', '', '是否开启后台操作日志', '', '0', '4', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('7', 'home_log', '前台日志', 'system', 'switch', '0', '', '是否开启前台访问日志', '', '0', '5', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('8', 'online_on', '在线状态', 'system', 'radio', '1', '0:全部关闭
1:后台开启
2:会员开启
3:全部开启', '控制是否开启用户在线状态记录', '', '0', '10', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('9', 'ip_login', '异地登录', 'system', 'radio', '0', '0:全部允许
1:后台允许
2:前台允许
3:全部禁止', '控制是否允许同帐号同时异地登录', '', '0', '10', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('10', 'sys_filter', '过滤字符', 'system', 'textarea', '', '', '多个用以英文逗号,隔开', '', '0', '10', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('11', 'sms_state', '短信开关', 'sms', 'switch', '1', '', '', '', '0', '1', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('12', 'sms_type', '发送方式', 'sms', 'radio', 'qiniu', 'qiniu:七牛短信
smsbao:短信宝', '发送短信的方式', 'sm', '0', '1', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('13', 'sms_user', '接口ID/KEY', 'sms', 'text', '', '', '七牛 access_key', 'sm_qiniu', '1', '2', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('14', 'sms_pass', '短信秘钥', 'sms', 'text', '', '', '七牛 secret_key', 'sm_qiniu', '1', '3', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('15', 'sms_temp', '默认短信模板', 'sms', 'text', '', '', '七牛短信模板号', 'sm_qiniu', '0', '4', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('16', 'sms_baouser', '短信宝帐号', 'sms', 'text', '', '', '短信宝接口帐号', 'sm_smsbao', '0', '10', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('17', 'sms_baopass', '短信宝接口密码', 'sms', 'text', '', '', '短信宝接口密码', 'sm_smsbao', '1', '10', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('18', 'sms_times', '发送时间间隔', 'sms', 'number', '', '', '发送短信的时间间隔，单位秒', '', '0', '11', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('19', 'upload_image_type', '可传图片类型', 'upload', 'tags', 'jpg,png,gif,jpeg', '', '本地允许上传的图片类型', '', '0', '1', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('20', 'upload_file_type', '可传文件类型', 'upload', 'tags', 'rar,zip,pdf,docx,doc,xlsx,xls', '', '本地允许上传的文件类型', '', '0', '2', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('21', 'upload_video_type', '可传视频类型', 'upload', 'tags', 'mp4,flv,wmv,avi,mov,mpeg', '', '本地允许上传的视频类型', '', '0', '3', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('22', 'upload_audio_type', '可传音频类型', 'upload', 'tags', 'mp3', '', '本地允许上传的音频类型', '', '0', '4', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('23', 'upload_image_size', '上传图片大小上限', 'upload', 'number', '2', '', '允许上传图片大小上限（Mb）', '', '0', '5', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('24', 'upload_file_size', '上传文件大小上限', 'upload', 'number', '10', '', '允许上传文件大小上限（Mb）', '', '0', '6', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('25', 'upload_video_size', '上传视频大小上限', 'upload', 'number', '20', '', '允许上传视频大小上限（Mb）', '', '0', '7', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('26', 'upload_audio_size', '上传音频大小上限', 'upload', 'number', '20', '', '允许上传音频大小上限（Mb）', '', '0', '8', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('27', 'upload_engine', '上传方式', 'upload', 'radio', 'local', 'local:本地
qiniu:七牛云存储
aliyun:阿里云OSS
qcloud:腾讯云COS', '上传文件所保存的位置', 'up', '0', '9', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('28', 'qiniu_bucket', '空间名称 Bucket', 'upload', 'text', '', '', '七牛云存储 Bucket', 'up_qiniu', '0', '10', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('29', 'access_key', 'ACCESS_KEY AK', 'upload', 'text', '', '', '七牛云存储 ACCESS_KEY', 'up_qiniu', '1', '11', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('30', 'qiniu_secret_key', 'SECRET_KEY SK', 'upload', 'text', '', '', '七牛云存储 SECRET_KEY', 'up_qiniu', '1', '12', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('31', 'qiniu_domain', '空间域名 Domain', 'upload', 'text', '', '', '七牛云存储 请补全http:// 或 https://，例如：http://v.abc.com', 'up_qiniu', '0', '13', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('32', 'aliyun_bucket', '空间名称 Bucket', 'upload', 'text', '', '', '阿里云OSS Bucket', 'up_aliyun', '0', '14', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('33', 'access_key_id', 'AccessKeyId', 'upload', 'text', '', '', '阿里云OSS AccessKeyId', 'up_aliyun', '1', '15', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('34', 'access_key_secret', 'AccessKeySecret', 'upload', 'text', '', '', '阿里云OSS AccessKeySecret', 'up_aliyun', '1', '16', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('35', 'aliyun_domain', '空间域名 Domain', 'upload', 'text', '', '', '阿里云OSS 请补全http:// 或 https://，例如：http://v.abc.com', 'up_aliyun', '0', '17', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('36', 'qcloud_bucket', '空间名称 Bucket', 'upload', 'text', '', '', '腾讯云COS Bucket', 'up_qcloud', '0', '18', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('37', 'region', '所属地域 Region', 'upload', 'text', '', '', '腾讯云COS Region', 'up_qcloud', '0', '19', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('38', 'secret_id', 'SecretId', 'upload', 'text', '', '', '腾讯云COS SecretId', 'up_qcloud', '1', '20', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('39', 'qcloud_secret_key', 'SecretKey', 'upload', 'text', '', '', '腾讯云COS SecretKey', 'up_qcloud', '1', '21', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('40', 'qcloud_domain', '空间域名 Domain', 'upload', 'text', '', '', '腾讯云COS 请补全http:// 或 https://，例如：http://v.abc.com', 'up_qcloud', '0', '22', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('41', 'aliyun_endpoint', '上传节点 Endpoint', 'upload', 'text', '', '', '如：https://oss-cn-beijing.aliyuncs.com，不设则按空间域名配置，也可以用内网域名（如：ECS同地域时）', 'up_aliyun', '0', '17', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('42', 'sys_author', '作者', 'system', 'text', '嗷呜awa', '', '系统作者', '', '0', '2', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('43', 'sys_site', '官网地址', 'system', 'text', '', '', '系统官网地址', '', '0', '2', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('44', 'sys_source', '开源地址', 'system', 'text', '', '', '系统开源仓库地址', '', '0', '2', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('45', 'sys_copyright', '页脚版权', 'system', 'text', 'Copyright (C) 2026 YanyvSEO All Rights Reserved.', '', '前台/后台页脚显示的版权信息', '', '0', '2', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('47', 'pool_pay_api', '易支付接口', 'system', 'text', '', '', '易支付兼容API提交地址,如 https://pay.example.com/', '', '0', '21', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('48', 'pool_pay_pid', '商户PID', 'system', 'text', '', '', '易支付商户ID', '', '0', '22', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('49', 'pool_pay_key', '商户密钥', 'system', 'password', '', '', '易支付商户密钥', '', '0', '23', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('50', 'pool_pay_mode', '充值金额模式', 'system', 'radio', 'mixed', 'fixed:仅固定金额
custom:仅自定义金额
mixed:混合模式', '控制在线充值页面的金额输入方式', '', '0', '24', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('51', 'pool_exchange_rate', '积分兑换比例', 'system', 'number', '100', '', '自定义金额模式下1元可兑换的积分数量', '', '0', '25', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('52', 'pool_fixed_amounts', '固定金额档位', 'system', 'textarea', '10:1000
30:3000
50:5200
100:11000', '', '每行为一档: 金额:到账积分', '', '0', '26', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('53', 'pool_pay_min', '最低充值金额', 'system', 'number', '1', '', '元,自定义金额模式的下限', '', '0', '27', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('54', 'pool_pay_max', '最高充值金额', 'system', 'number', '10000', '', '元,自定义金额模式的上限', '', '0', '28', '', '1', '', '', '1475240646', '0', '0');
INSERT INTO `vt_system_setting` VALUES ('55', 'pool_user_site', '用户自选站群', 'system', 'switch', '1', '', '是否允许用户提交链接时自行选择投放站群（关闭则强制全站轮询）', '', '0', '29', '', '1', 'admin', '', '1787830514', '0', '0');


-- ----------------------------
-- Table structure for vt_system_sms
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_sms`;
CREATE TABLE `vt_system_sms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '手机号',
  `message` text NOT NULL COMMENT '短信内容',
  `word` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '短信字数',
  `code` varchar(200) NOT NULL DEFAULT '' COMMENT '错误提示',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='短信记录';

-- ----------------------------
-- Table structure for vt_system_upload_file
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_upload_file`;
CREATE TABLE `vt_system_upload_file` (
  `fileid` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `storage` varchar(20) NOT NULL DEFAULT '' COMMENT '存储方式',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件分组id',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '所属会员账户',
  `admin` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '帐号平台1:后台2:会员',
  `fileurl` varchar(255) NOT NULL DEFAULT '' COMMENT '存储路径',
  `filename` varchar(200) NOT NULL DEFAULT '' COMMENT '文件名称',
  `filesize` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '文件大小',
  `filetype` varchar(20) NOT NULL DEFAULT '' COMMENT '文件类型',
  `fileext` varchar(20) NOT NULL DEFAULT '' COMMENT '文件扩展名',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '软删除',
  `editor` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`fileid`),
  KEY `groupid` (`groupid`) USING BTREE,
  KEY `isdel` (`isdel`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `admin` (`admin`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='上传文件';

-- ----------------------------
-- Table structure for vt_system_upload_group
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_upload_group`;
CREATE TABLE `vt_system_upload_group` (
  `groupid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `grouptype` varchar(10) NOT NULL DEFAULT '' COMMENT '文件类型',
  `groupname` varchar(30) NOT NULL DEFAULT '' COMMENT '分类名称',
  `listorder` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类排序',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `editor` varchar(30) NOT NULL DEFAULT ''COMMENT '更新者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='上传分组';

-- ----------------------------
-- Records of vt_system_upload_group
-- ----------------------------
INSERT INTO `vt_system_upload_group` VALUES ('1', 'image', '系统配置', '1', '0', '', '', '0', '0');

-- ----------------------------
-- Table structure for vt_system_web_log
-- ----------------------------
DROP TABLE IF EXISTS `vt_system_web_log`;
CREATE TABLE `vt_system_web_log` (
  `logid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '会员帐号',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '访问地址',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `logtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '访问时间',
  `agent` varchar(255) NOT NULL DEFAULT '' COMMENT '设备信息',
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='访问日志';

-- ----------------------------
-- Table structure for vt_pool_engine
-- ----------------------------
DROP TABLE IF EXISTS `vt_pool_engine`;
CREATE TABLE `vt_pool_engine` (
  `engine_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '引擎ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '引擎名称',
  `mark` varchar(30) NOT NULL DEFAULT '' COMMENT '引擎标识',
  `match_mode` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '匹配模式:1仅UA 2仅IP 3IP+UA',
  `ua_keywords` varchar(500) NOT NULL DEFAULT '' COMMENT 'UA关键词多个逗号隔开',
  `ip_rules` text NOT NULL COMMENT 'IP规则每行一条,支持CIDR如baiduspiderip.txt',
  `price_normal` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '普通引导单价(积分/次)',
  `price_301` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '301强引单价(积分/次)',
  `guide_options` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义引导数选项逗号隔开',
  `listorder` int(10) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态:0禁用',
  `creator` varchar(30) NOT NULL DEFAULT '',
  `editor` varchar(30) NOT NULL DEFAULT '',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`engine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='SEO引擎规则';

-- ----------------------------
-- Table structure for vt_pool_template
-- ----------------------------
DROP TABLE IF EXISTS `vt_pool_template`;
CREATE TABLE `vt_pool_template` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '目录名(public/{name}/)',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '模板标题',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态:0停用',
  `creator` varchar(30) NOT NULL DEFAULT '',
  `editor` varchar(30) NOT NULL DEFAULT '',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='站群模板';

-- ----------------------------
-- Table structure for vt_pool_site
-- ----------------------------
DROP TABLE IF EXISTS `vt_pool_site`;
CREATE TABLE `vt_pool_site` (
  `siteid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '站点ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '站点名称',
  `domain` varchar(100) NOT NULL DEFAULT '' COMMENT '泛解析域名如*.abc.com或abc.com',
  `template_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模板ID',
  `ratio_301` tinyint(3) unsigned NOT NULL DEFAULT '50' COMMENT '301强引占比%',
  `weight` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '轮询权重',
  `real_ip_head` varchar(30) NOT NULL DEFAULT 'X-Forwarded-For' COMMENT '真实IP识别头',
  `cms_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'CMS内容源地址',
  `cms_rules` text NOT NULL COMMENT '正则抓取规则,每行:key|正则',
  `cache_hours` tinyint(3) unsigned NOT NULL DEFAULT '6' COMMENT '内容缓存时间(小时)',
  `spider_show` smallint(5) unsigned NOT NULL DEFAULT '10' COMMENT '单次展示链接数',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态:0停用',
  `creator` varchar(30) NOT NULL DEFAULT '',
  `editor` varchar(30) NOT NULL DEFAULT '',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`siteid`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='站群站点';

-- ----------------------------
-- Table structure for vt_pool_link
-- ----------------------------
DROP TABLE IF EXISTS `vt_pool_link`;
CREATE TABLE `vt_pool_link` (
  `lid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '链接ID',
  `userid` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户ID',
  `url` varchar(500) NOT NULL DEFAULT '' COMMENT '目标URL',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投放站点ID 0全站',
  `engine_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投放引擎ID',
  `guide_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '引导类型:1普通 2-301强引',
  `total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总引导次数',
  `used` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已引导次数',
  `cost_points` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实付积分',
  `price_point` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价快照',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态:1进行中2已完成3已暂停4额度不足',
  `source_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '提交来源IP',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`lid`),
  UNIQUE KEY `url` (`url`(191)),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='链接投放';

-- ----------------------------
-- Table structure for vt_pool_billing_log
-- ----------------------------
DROP TABLE IF EXISTS `vt_pool_billing_log`;
CREATE TABLE `vt_pool_billing_log` (
  `bid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `link_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '链接ID',
  `userid` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `engine_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '引擎ID',
  `guide_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '引导类型:1普通2-301',
  `points` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '扣除积分',
  `spider_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '蜘蛛IP',
  `spider_ua` varchar(255) NOT NULL DEFAULT '' COMMENT '蜘蛛UA',
  `referer_url` varchar(255) NOT NULL DEFAULT '' COMMENT '命中页面URL',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`bid`),
  KEY `link_id` (`link_id`),
  KEY `userid` (`userid`,`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='引导计费流水';

-- ----------------------------
-- Table structure for vt_pool_recharge
-- ----------------------------
DROP TABLE IF EXISTS `vt_pool_recharge`;
CREATE TABLE `vt_pool_recharge` (
  `rid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `orderid` varchar(40) NOT NULL DEFAULT '' COMMENT '订单号',
  `userid` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `points` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '到账积分',
  `paytype` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式:alipay/wxpay等',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态:0待支付1已支付2已失效',
  `trade_no` varchar(60) NOT NULL DEFAULT '' COMMENT '第三方交易号',
  `callback` varchar(500) NOT NULL DEFAULT '' COMMENT '回调原文',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `orderid` (`orderid`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='充值订单';

-- ----------------------------
-- Table structure for vt_pool_points_log
-- ----------------------------
DROP TABLE IF EXISTS `vt_pool_points_log`;
CREATE TABLE `vt_pool_points_log` (
  `pid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `userid` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` varchar(15) NOT NULL DEFAULT '' COMMENT '类型:recharge充值/consume消费/admin调整/refund退还',
  `points` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '变动积分正加负减',
  `before_points` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '变动前余额',
  `after_points` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '变动后余额',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `rel_order` varchar(40) NOT NULL DEFAULT '' COMMENT '关联单号',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`),
  KEY `userid` (`userid`,`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='积分明细';

-- ----------------------------
-- Table structure for vt_cms_group
-- ----------------------------
CREATE TABLE `vt_cms_group` (
  `groupid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分组ID',
  `title` varchar(30) NOT NULL DEFAULT '' COMMENT '分组名称',
  `list_url` varchar(255) NOT NULL DEFAULT '' COMMENT '抓取列表页URL',
  `list_rule` text COMMENT '链接正则(每行一条)',
  `title_rule` varchar(255) NOT NULL DEFAULT '' COMMENT '详情标题正则',
  `content_rule` text COMMENT '详情内容正则',
  `charset` varchar(15) NOT NULL DEFAULT 'utf-8' COMMENT '来源编码',
  `pseudo_lib` mediumtext COMMENT '伪原创词库(每行 原词=替换1|替换2)',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态1启用0停用',
  `listorder` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `creator` varchar(30) NOT NULL DEFAULT '' COMMENT '创建者',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='CMS文章分组';

-- ----------------------------
-- Table structure for vt_cms_article
-- ----------------------------
CREATE TABLE `vt_cms_article` (
  `articleid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属分组ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '文章标题',
  `content` mediumtext COMMENT '文章内容',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态1启用0停用',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `del_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`articleid`),
  KEY `groupid` (`groupid`,`state`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='CMS文章';

-- ----------------------------
-- Records of vt_pool_engine
-- ----------------------------
INSERT INTO `vt_pool_engine` VALUES ('1', '百度', 'baidu', '3', 'Baiduspider', '180.76.15.
116.179.32.
220.181.108.', '0.03', '0.06', '1000,3000,5000,10000', '1', '1', 'admin', 'admin', '1475240646', '1787834147', '0');
INSERT INTO `vt_pool_engine` VALUES ('2', '谷歌', 'google', '1', 'Googlebot', '', '0.04', '0.08', '1000,3000,5000,10000', '2', '1', 'admin', '', '1475240646', '0', '0');
INSERT INTO `vt_pool_engine` VALUES ('3', '必应', 'bing', '1', 'bingbot,BingPreview', '', '0.04', '0.08', '1000,3000,5000,10000', '3', '1', 'admin', '', '1475240646', '0', '0');
INSERT INTO `vt_pool_engine` VALUES ('4', '360搜索', 'so', '3', '360Spider,HaoSouSpider', '101.226.61.
103.21.163.', '0.02', '0.05', '1000,3000,5000,10000', '4', '1', 'admin', '', '1475240646', '0', '0');
INSERT INTO `vt_pool_engine` VALUES ('5', '搜狗', 'sogou', '1', 'Sogou web spider,Sogou inst spider', '', '0.02', '0.05', '1000,3000,5000,10000', '5', '1', 'admin', '', '1475240646', '0', '0');
INSERT INTO `vt_pool_engine` VALUES ('6', '神马', 'sm', '1', 'YisouSpider,HaosouSpider', '', '0.02', '0.04', '1000,3000,5000,10000', '6', '1', 'admin', '', '1475240646', '0', '0');

-- ----------------------------
-- Records of vt_pool_template
-- ----------------------------
INSERT INTO `vt_pool_template` VALUES ('1', 'default', '默认单页模板', '简单文章站样式，支持路由规则与蜘蛛引导链接列表', '1', 'admin', 'admin', '1475240646', '1787835312', '0');
