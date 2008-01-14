/* 
$Revision: 1.3 $
$Date: 2008/01/14 02:02:58 $
$Author: havlat $
$RCSfile: db_schema_update.sql,v $
*/

DROP TABLE IF EXISTS `priorities`;
DROP TABLE IF EXISTS `risk_assignments`;

ALTER TABLE tcversions ADD COLUMN `importance` smallint(5) unsigned NOT NULL default '2';
ALTER TABLE testprojects ADD COLUMN `prefix` varchar(30) NULL;
/* ALTER TABLE testprojects ADD COLUMN `tc_counter` int(10) unsigned NULL default '0'; */
ALTER TABLE users ADD COLUMN `script_key` varchar(32) NULL;

/* mht - 0000774: Global Project Template */
CREATE TABLE `text_templates` (
  `id` int(10) unsigned NOT NULL,
  `tpl_type` smallint(5) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `template_data` text,
  `author_id` int(10) unsigned default NULL,
  `create_ts` datetime NOT NULL default '1900-00-00 01:00:00',
  `is_public` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `idx_text_templates` (`tpl_type`,`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Global Project Templates';

/* mht - 0000537: Dsicussion: Priority = Urgency + Importance */
CREATE TABLE `test_urgency` (
  `node_id` int(10) unsigned NOT NULL,
  `testplan_id` int(10) unsigned NOT NULL,
  `urgency` smallint(5) unsigned NOT NULL default '2',
  UNIQUE KEY `idx_test_urgency` (`node_id`,`testplan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Urgence of testing test suite in a Test Plan';

/* mht - group users for large companies */
CREATE TABLE `user_group` (
  `usergroup_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  UNIQUE KEY (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `user_group_assign` (
  `usergroup_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
