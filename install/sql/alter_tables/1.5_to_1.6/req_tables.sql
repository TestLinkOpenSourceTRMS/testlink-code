/* Update From 1.5.x to 1.6 */
DROP TABLE IF EXISTS `requirement_doc`;
DROP TABLE IF EXISTS `requirements`;
DROP TABLE IF EXISTS `requirements_coverage`; req_coverage

# 
DROP TABLE IF EXISTS `req_spec`;
CREATE TABLE `req_spec` (
  `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `id_product` INT( 10 ) UNSIGNED NOT NULL ,
  `title` VARCHAR( 100 ) NOT NULL ,
  `scope` TEXT,
  `total_req` VARCHAR( 5 ) DEFAULT 'n/a' NOT NULL ,
  `type` char(1) default 'n',
  `id_author` INT( 10 ) UNSIGNED NULL,
  `create_date` date NOT NULL default '0000-00-00',
  `id_modifier` INT( 10 ) UNSIGNED NULL,
  `modified_date` date NOT NULL default '0000-00-00',
PRIMARY KEY ( `id` ) ,
INDEX ( `id_product` )
) TYPE=MyISAM COMMENT='Dev. Documents (e.g. System Requirements Specification)';
# --------------------------------------------------------

# --------------------------------------------------------
DROP TABLE IF EXISTS `requirements`;
CREATE TABLE `requirements` (
  `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `id_srs` INT( 10 ) UNSIGNED NOT NULL ,
  `req_doc_id` varchar(16) default NULL ,
  `title` VARCHAR( 100 ) NOT NULL ,
  `scope` TEXT,
  `status` char(1) default 'v' NOT NULL,
  `type` char(1) default NULL,
  `id_author` INT( 10 ) UNSIGNED NULL,
  `create_date` date NOT NULL default '0000-00-00',
  `id_modifier` INT( 10 ) UNSIGNED NULL,
  `modified_date` date NOT NULL default '0000-00-00',
PRIMARY KEY ( `id` ) ,
INDEX ( `id_srs` , `status` ),
KEY `req_doc_id` (`req_doc_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

# --------------------------------------------------------
CREATE TABLE `req_coverage` (
`id_req` INT( 10 ) NOT NULL ,
`id_tc` INT( 10 ) NOT NULL ,
INDEX ( `id_req` , `id_tc` )
) TYPE=MyISAM COMMENT = 'relation test case ** requirements';

# --------------------------------------------------------
