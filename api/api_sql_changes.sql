-- create the api_developer_keys table 
CREATE TABLE `api_developer_keys` (
  `id` int(10) NOT NULL auto_increment,
  `developer_key` varchar(32) NOT NULL,
  `user_id` int(10) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='authentication keys for using the api';


-- add automated flag to executions table
ALTER TABLE `executions` ADD `automated` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `executions` ADD INDEX ( `automated` ) ;

-- TODO: need to add sql for flagging test cases as being automated