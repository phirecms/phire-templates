--
-- Templates Module MySQL Database for Phire CMS 2.0
--

-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]templates` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `name` varchar(255) NOT NULL,
  `device` varchar(255),
  `template` mediumtext,
  `history` mediumtext,
  `visible` int(1),
  PRIMARY KEY (`id`),
  INDEX `template_parent_id` (`parent_id`),
  INDEX `template_name` (`name`),
  CONSTRAINT `fk_template_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `[{prefix}]templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9001;

-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;
