SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `bots`
-- ----------------------------
DROP TABLE IF EXISTS `bots`;
CREATE TABLE `bots` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `bot_name` varchar(255) NOT NULL,
  `token` text NOT NULL,
  `last_update_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bots
-- ----------------------------

-- ----------------------------
-- Table structure for `resources`
-- ----------------------------
DROP TABLE IF EXISTS `resources`;
CREATE TABLE `resources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subscribe_type_id` tinyint(4) NOT NULL,
  `subscribe_name` varchar(255) NOT NULL,
  `subscribe_id` varchar(255) DEFAULT NULL,
  `time_last_monitoring` timestamp NULL DEFAULT NULL,
  `last_monitoring_info` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of resources
-- ----------------------------
INSERT INTO `resources` VALUES ('1', '1', 'Lenta4', '29534144', null, '');

-- ----------------------------
-- Table structure for `subscribe_types`
-- ----------------------------
DROP TABLE IF EXISTS `subscribe_types`;
CREATE TABLE `subscribe_types` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of subscribe_types
-- ----------------------------
INSERT INTO `subscribe_types` VALUES ('1', 'VK Wall Group', 'vk,vk_wall,vk_group');

-- ----------------------------
-- Table structure for `subscribers`
-- ----------------------------
DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE `subscribers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` varchar(255) NOT NULL,
  `resource_id` bigint(20) unsigned NOT NULL,
  `is_display_preview` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of subscribers
-- ----------------------------

ALTER TABLE `resources`
ADD COLUMN `bot_id`  bigint UNSIGNED NOT NULL DEFAULT 0 AFTER `id`;

UPDATE `resources` SET `bot_id`='1' WHERE (`id`='1');