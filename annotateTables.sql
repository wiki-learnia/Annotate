SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `annotate_annotations`
-- ----------------------------
CREATE TABLE IF NOT EXISTS annotate_annotations (
  `user_id` int(10) NOT NULL,
  `page_id` int(10) NOT NULL,
  `revision_id` int(10) NOT NULL,
  `annotations` text,
  `annotation_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`annotation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS annotate_shared (
  `user_id` int(10) NOT NULL,
  `page_id` int(10) NOT NULL,
  `shared` boolean NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
