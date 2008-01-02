/* 
$Revision: 1.1 $
$Date: 2008/01/02 18:56:05 $
$Author: franciscom $
$Name:  $
*/
CREATE TABLE `api_developer_keys` (
  `id` int(10) NOT NULL auto_increment,
  `developer_key` varchar(32) NOT NULL,
  `user_id` int(10) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='authentication keys for using the api';
