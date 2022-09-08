/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50726
Source Host           : localhost:3306
Source Database       : www

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2022-09-05 23:10:55
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for monitor_link
-- ----------------------------
DROP TABLE IF EXISTS `monitor_link`;
CREATE TABLE `monitor_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `monitor_link` varchar(360) NOT NULL DEFAULT '' COMMENT '生成的监控链接',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '监控链接的名称',
  `remark` varchar(360) NOT NULL DEFAULT '' COMMENT '监控链接说明',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除，0：未删除，1：已删除',
  `create_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of monitor_link
-- ----------------------------
INSERT INTO `monitor_link` VALUES ('1', 'http://local.pro.com/monitor-link', '测试1', '测试11', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('2', 'http://local.pro.com/monitor-link', '测试2', '测试22', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('3', 'http://local.pro.com/monitor-link', '测试3', '测试33', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('4', 'http://local.pro.com/monitor-link', '测试4', '测试44', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('5', 'http://local.pro.com/monitor-link', '测试5', '测试55', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('6', 'http://local.pro.com/monitor-link', '测试6', '测试66', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('7', 'http://local.pro.com/monitor-link', '测试7', '测试77', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('8', 'http://local.pro.com/monitor-link', '测试1', '测试11', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('9', 'http://local.pro.com/monitor-link', '测试1', '测试11', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('10', 'http://local.pro.com/monitor-link', '测试1', '测试11', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('11', 'http://local.pro.com/monitor-link', '测试1', '测试11', '0', '1662216283');
INSERT INTO `monitor_link` VALUES ('12', 'http://local.pro.com/monitor-link', '测试1', '测试11', '1', '1662216283');
INSERT INTO `monitor_link` VALUES ('13', 'http://local.pro.com/monitor-link?t=1&t2=t2&t3=t3', '测试1', '测试11', '1', '1662216283');
INSERT INTO `monitor_link` VALUES ('18', 'www.baidu.com', 'aa', 'bb', '0', '1662292706');
INSERT INTO `monitor_link` VALUES ('19', 'www.baidu.com', 'aa', 'cc', '0', '1662294436');
INSERT INTO `monitor_link` VALUES ('20', 'www.baidu.com', 'b1', 'b2', '0', '1662295165');
INSERT INTO `monitor_link` VALUES ('21', 'www.baidu.com', 'c22', 'c22', '0', '1662295202');
INSERT INTO `monitor_link` VALUES ('22', 'www.baidu.com', 'a', 'b', '1', '1662385089');

-- ----------------------------
-- Table structure for redirect_link
-- ----------------------------
DROP TABLE IF EXISTS `redirect_link`;
CREATE TABLE `redirect_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `monitor_id` int(11) NOT NULL DEFAULT '0' COMMENT 'monitor_link.id',
  `redirect_link` text NOT NULL COMMENT '跳转的链接',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '量级，跳转的次数',
  `rank` int(11) NOT NULL COMMENT '权重，数字越小越大',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除，0：未删除，1：已删除',
  `create_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of redirect_link
-- ----------------------------
INSERT INTO `redirect_link` VALUES ('1', '13', 'c3', '1', '2', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('2', '13', 'aa', '1', '2', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('3', '13', 'www.baidu.com1', '100', '100', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('4', '13', 'www.baidu.com1', '100', '100', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('5', '13', 'www.baidu.com1', '1009', '1003', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('6', '13', 'www.baidu.com1', '1009', '1003', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('7', '13', 'www.baidu.com1', '1009', '1003', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('8', '13', 'www.baidu.com1', '1009', '1003', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('9', '13', 'www.baidu.com1', '1009', '1003', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('10', '13', 'www.baidu.com1', '1009', '1003', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('11', '13', 'www.baidu.com1', '1009', '1003', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('12', '13', 'www.baidu.com1', '1009', '1003', '0', '1662216283');
INSERT INTO `redirect_link` VALUES ('13', '13', 'www.jd.com1', '111119', '222223', '0', '1662278403');
INSERT INTO `redirect_link` VALUES ('14', '13', 'www.mall.com', '101', '202', '1', '1662278787');
INSERT INTO `redirect_link` VALUES ('15', '13', '测试', '1', '2', '1', '1662281493');
INSERT INTO `redirect_link` VALUES ('16', '13', 'aaa', '111', '222', '1', '1662281597');
INSERT INTO `redirect_link` VALUES ('17', '13', 'a1', '11119', '22223', '1', '1662282034');
INSERT INTO `redirect_link` VALUES ('18', '13', 't1', '10', '10', '1', '1662286458');
INSERT INTO `redirect_link` VALUES ('19', '13', 't2', '10', '20', '1', '1662286467');
INSERT INTO `redirect_link` VALUES ('20', '13', 'ttt', '111', '222', '0', '1662287917');
INSERT INTO `redirect_link` VALUES ('23', '18', 'cc', '1', '2', '0', '1662292706');
INSERT INTO `redirect_link` VALUES ('24', '19', 'dd', '11', '22', '0', '1662294436');
INSERT INTO `redirect_link` VALUES ('25', '20', 'b3', '1', '1', '0', '1662295165');
INSERT INTO `redirect_link` VALUES ('26', '21', 'c3', '1', '2', '0', '1662295202');
INSERT INTO `redirect_link` VALUES ('27', '22', 'c', '1', '2', '0', '1662385089');
INSERT INTO `redirect_link` VALUES ('28', '21', 'aa', '1', '2', '0', '1662385113');

-- ----------------------------
-- Table structure for redorect_record
-- ----------------------------
DROP TABLE IF EXISTS `redorect_record`;
CREATE TABLE `redorect_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `redirect_id` int(11) NOT NULL DEFAULT '0' COMMENT 'redirect_link.id',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '曝光量',
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of redorect_record
-- ----------------------------
INSERT INTO `redorect_record` VALUES ('1', '13', '10', '2022-09-01');
INSERT INTO `redorect_record` VALUES ('2', '14', '10', '2022-09-01');
INSERT INTO `redorect_record` VALUES ('3', '15', '10', '2022-09-01');
INSERT INTO `redorect_record` VALUES ('4', '16', '10', '2022-09-01');
INSERT INTO `redorect_record` VALUES ('5', '17', '10', '2022-09-01');
INSERT INTO `redorect_record` VALUES ('6', '13', '10', '2022-09-02');
INSERT INTO `redorect_record` VALUES ('7', '14', '10', '2022-09-02');
INSERT INTO `redorect_record` VALUES ('8', '15', '10', '2022-09-02');
INSERT INTO `redorect_record` VALUES ('9', '16', '10', '2022-09-02');
INSERT INTO `redorect_record` VALUES ('10', '17', '10', '2022-09-02');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(36) NOT NULL COMMENT '用户名',
  `pass` varchar(32) NOT NULL COMMENT '密码',
  `salt` varchar(24) NOT NULL COMMENT '随机字符',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('2', 'admin', '6c0da81d45f25c2912a1f733abe0cc67', 'POP09MjhVFqAomnXZoLpuy6b');

-- ----------------------------
-- Table structure for user_token
-- ----------------------------
DROP TABLE IF EXISTS `user_token`;
CREATE TABLE `user_token` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'user.id',
  `token` varchar(64) NOT NULL DEFAULT '' COMMENT '登录token',
  `create_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '生成时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_id` (`user_id`) USING BTREE,
  KEY `token` (`token`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of user_token
-- ----------------------------
INSERT INTO `user_token` VALUES ('2', '2', '4e05a55aecf08ac8b1eed1df91160e4d', '1662384995');
