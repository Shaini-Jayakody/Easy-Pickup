
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tbl_cars
-- ----------------------------
DROP TABLE IF EXISTS `tbl_cars`;
CREATE TABLE `tbl_cars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ref_no` varchar(13) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `engine_number` varchar(50) DEFAULT NULL,
  `chassis_number` varchar(255) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `transmition` varchar(50) DEFAULT NULL,
  `model_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tbl_cars
-- ----------------------------
INSERT INTO `tbl_cars` VALUES ('1', 'CAR1633626936', 'Seadan', 'EN123', 'CHA234', 'Black', 'Auto', '1');
INSERT INTO `tbl_cars` VALUES ('2', 'CAR1633629434', 'Seadan2', 'sdsd', 'sds', 'green', 'Auto', '1');

-- ----------------------------
-- Table structure for tbl_car_brands
-- ----------------------------
DROP TABLE IF EXISTS `tbl_car_brands`;
CREATE TABLE `tbl_car_brands` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tbl_car_brands
-- ----------------------------
INSERT INTO `tbl_car_brands` VALUES ('1', 'BMW');

-- ----------------------------
-- Table structure for tbl_car_models
-- ----------------------------
DROP TABLE IF EXISTS `tbl_car_models`;
CREATE TABLE `tbl_car_models` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `brand_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tbl_car_models
-- ----------------------------
INSERT INTO `tbl_car_models` VALUES ('1', 'BMW 5 Series Sedan', '2020', '1');
