CREATE TABLE `req_spec` (
  `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `id_product` INT( 10 ) UNSIGNED NOT NULL ,
  `title` VARCHAR( 100 ) NOT NULL default 'undefined',
  `scope` TEXT,
  `total_req` VARCHAR( 5 ) DEFAULT 'n/a' NOT NULL ,
  `edit_by` varchar(30) default NULL,
  `edit_date` date NOT NULL default '0000-00-00',
PRIMARY KEY ( `id` ) ,
INDEX ( `id_product` )
) TYPE=MyISAM COMMENT='Dev. Documents (e.g. System Requirements Specification)';
# --------------------------------------------------------

# --------------------------------------------------------
CREATE TABLE `requirements` (
  `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `id_srs` INT( 10 ) UNSIGNED NOT NULL ,
  `title` VARCHAR( 100 ) NOT NULL default 'undefined',
  `scope` TEXT,
  `status` ENUM( 'Normal', 'Not testable' ) DEFAULT 'Normal' NOT NULL ,
  `edit_by` varchar(30) default NULL,
  `edit_date` date NOT NULL default '0000-00-00',
PRIMARY KEY ( `id` ) ,
INDEX ( `id_srs` , `status` )
) TYPE=MyISAM;

# --------------------------------------------------------

# --------------------------------------------------------
CREATE TABLE `req_coverage` (
`id_req` INT( 10 ) NOT NULL ,
`id_tc` INT( 10 ) NOT NULL ,
INDEX ( `id_req` , `id_tc` )
) TYPE=MyISAM COMMENT = 'relation test case ** requirements';

# --------------------------------------------------------
