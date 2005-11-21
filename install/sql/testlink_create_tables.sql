# TestLink Open Source Project - http://testlink.sourceforge.net/
# $Id: testlink_create_tables.sql,v 1.10 2005/11/21 04:19:51 havlat Exp $
# SQL script - create db tables for TL 1.6.0  
#
# default rights & admin account are created via testlink_create_default_data.sql
#
# Rev :
#       20050925 - fm
#       build: removed build.build
#       category: removed category.name
#       component: removed component.name
#       bugs: build -> build_id
#
#       20050808 - fm
#       every occurence of active field converted to boolean
#
#       20050806 - fm
#       1. equalized the dimension and type of field 'NAME'
#       2. Corrected dimension of ID fields (11 -> 10) in requirement tables
#       3. Table Comments clean-up   
#
#
# --------------------------------------------------------

#
# to trace the db upgrade history
DROP TABLE IF EXISTS `db_version`;
CREATE TABLE `db_version` (
  version varchar(50) NOT NULL default '1.6 BETA 1',
  upgrade_date datetime NOT NULL default '0000-00-00 00:00'
);

#
# Table structure for table `bugs`
#
#
DROP TABLE IF EXISTS `bugs`;
CREATE TABLE `bugs` (
  `tcid` int(10) unsigned NOT NULL default '0',
  `build_id` int(10) NOT NULL default '0',
  `bug` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tcid`,`build_id`,`bug`),
  KEY `tcid` (`tcid`),
  KEY `build_id` (`build_id`),
  KEY `bug` (`bug`)
) TYPE=MyISAM COMMENT='Bugs filed for each result';

# --------------------------------------------------------

#
# Table structure for table `build`
#

DROP TABLE IF EXISTS `build`;
CREATE TABLE `build` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `projid` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default 'undefined',
  `note` text,
  PRIMARY KEY  (`id`),
  KEY `projid` (`projid`)
) TYPE=MyISAM COMMENT='Available builds';

# --------------------------------------------------------

#
# Table structure for table `category`
#

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `compid` int(10) unsigned default NULL,
  `importance` enum('L','M','H') NOT NULL default 'M',
  `risk` enum('1','2','3') NOT NULL default '2',
  `owner` varchar(30) default 'none',
  `mgtcatid` int(10) unsigned NOT NULL default '0',
  `CATorder` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `compid` (`compid`)
) TYPE=MyISAM COMMENT='Category of TC assigned to a Test Plan';

# --------------------------------------------------------

#
# Table structure for table `component`
#

DROP TABLE IF EXISTS `component`;
CREATE TABLE `component` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `projid` int(10) unsigned default NULL,
  `mgtcompid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `projid` (`projid`)
) TYPE=MyISAM ;

# --------------------------------------------------------

#
# Table structure for table `keywords`
#

DROP TABLE IF EXISTS `keywords`;
CREATE TABLE `keywords` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keyword` varchar(100) NOT NULL default '',
  `prodid` int(10) unsigned NOT NULL default '0',
  `notes` text,
  PRIMARY KEY  (`id`),
  KEY `prodid` (`prodid`),
  KEY `keyword` (`keyword`)
) TYPE=MyISAM COMMENT='All of the keywords for each product';

# --------------------------------------------------------

#
# Table structure for table `mgtcategory`
#

DROP TABLE IF EXISTS `mgtcategory`;
CREATE TABLE `mgtcategory` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default 'undefined',
  `objective` text NOT NULL,
  `config` text NOT NULL,
  `data` text NOT NULL,
  `tools` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `compid` int(10) unsigned NOT NULL default '0',
  `CATorder` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `compid` (`compid`)
) TYPE=MyISAM COMMENT='Categories of the Test Specification';

# --------------------------------------------------------

#
# Table structure for table `mgtcomponent`
#

DROP TABLE IF EXISTS `mgtcomponent`;
CREATE TABLE `mgtcomponent` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default 'undefined',
  `intro` text,
  `scope` text,
  `ref` text,
  `method` text,
  `lim` text,
  `prodid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `prodid` (`prodid`)
) TYPE=MyISAM COMMENT='Components of the Test Specification';

# --------------------------------------------------------

#
# Table structure for table `mgtproduct`
#

DROP TABLE IF EXISTS `mgtproduct`;
CREATE TABLE `mgtproduct` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default 'undefined',
  `color` varchar(12) NOT NULL default '#9BD',
  `active` bool NOT NULL default 1,
  `option_reqs` bool NOT NULL default 0,
  `option_priority` bool NOT NULL default 1,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `ActiveId` (`id`,`active`)
) TYPE=MyISAM COMMENT='Products of the TC management';

# --------------------------------------------------------

#
# Table structure for table `mgttestcase`
#

DROP TABLE IF EXISTS `mgttestcase`;
CREATE TABLE `mgttestcase` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `steps` text,
  `exresult` text,
  `keywords` text,
  `catid` int(10) unsigned NOT NULL default '0',
  `version` smallint(5) unsigned NOT NULL default '1',
  `summary` text,
  `author` varchar(30) default NULL,
  `create_date` date NOT NULL default '0000-00-00',
  `reviewer` varchar(30) default NULL,
  `modified_date` date NOT NULL default '0000-00-00',
  `TCorder` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`)
) TYPE=MyISAM COMMENT='The test cases within Test Specification';

# --------------------------------------------------------

#
# Table structure for table `milestone`
#

DROP TABLE IF EXISTS `milestone`;
CREATE TABLE `milestone` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `projid` int(10) unsigned NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  `A` tinyint(3) unsigned zerofill NOT NULL default '000',
  `B` tinyint(3) unsigned zerofill NOT NULL default '000',
  `C` tinyint(3) unsigned zerofill NOT NULL default '000',
  `name` varchar(100) NOT NULL default 'undefined',
  PRIMARY KEY  (`id`),
  KEY `projid` (`projid`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `priority`
#

DROP TABLE IF EXISTS `priority`;
CREATE TABLE `priority` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `projid` int(10) unsigned NOT NULL default '0',
  `riskImp` char(2) NOT NULL default '',
  `priority` enum('a','b','c') NOT NULL default 'b',
  PRIMARY KEY  (`id`),
  KEY `projid` (`projid`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `project`
#
#
# 20050808 - fm
# from:
#       `active` enum('y','n') NOT NULL default 'y',
# to:
#       `active` bool NOT NULL default 1,
#
DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(10) NOT NULL auto_increment,
  `prodid` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default 'unknown',
  `notes` text,
  `active` bool NOT NULL default 1,
  PRIMARY KEY  (`id`),
  KEY `product` (`prodid`,`active`)
) TYPE=MyISAM COMMENT='Test Plan information';

# --------------------------------------------------------

#
# Table structure for table `projrights`
#

DROP TABLE IF EXISTS `projrights`;
CREATE TABLE `projrights` (
  `userid` int(10) unsigned NOT NULL default '0',
  `projid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`userid`,`projid`)
) TYPE=MyISAM COMMENT='User''s project permissions';

# --------------------------------------------------------

#
# Table structure for table `requirement_doc`
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

#
# Table structure for table `requirements`
#

DROP TABLE IF EXISTS `requirements`;
CREATE TABLE `requirements` (
  `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `id_srs` INT( 10 ) UNSIGNED NOT NULL ,
  `req_doc_id` varchar(16) default NULL,
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

#
# Table structure for table `requirements_coverage`
#

DROP TABLE IF EXISTS `req_coverage`;
CREATE TABLE `req_coverage` (
`id_req` INT( 10 ) NOT NULL ,
`id_tc` INT( 10 ) NOT NULL ,
INDEX ( `id_req` , `id_tc` )
) TYPE=MyISAM COMMENT = 'relation test case ** requirements';

# --------------------------------------------------------

#
# Table structure for table `results`
#

DROP TABLE IF EXISTS `results`;
CREATE TABLE `results` (
  `build_id` int(10) NOT NULL default '0',
  `runby` varchar(30) default NULL,
  `daterun` date default NULL,
  `status` char(1) default NULL,
  `bugs` varchar(100) default NULL,
  `tcid` int(10) unsigned NOT NULL default '0',
  `notes` text,
  PRIMARY KEY  (`tcid`,`build_id`),
  KEY `tcid` (`tcid`),
  KEY `status` (`status`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `rights`
#

DROP TABLE IF EXISTS `rights`;
CREATE TABLE `rights` (
  `id` tinyint(5) unsigned NOT NULL auto_increment,
  `role` varchar(20) NOT NULL default '',
  `rights` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `role` (`role`)
) TYPE=MyISAM COMMENT='rights (permissions)';

# --------------------------------------------------------

#
# Table structure for table `testcase`
#
# 20050808 - fm
# from:
#       `active` enum('on','off') NOT NULL default 'on',
# to:
#       `active` bool NOT NULL default 1,
#

DROP TABLE IF EXISTS `testcase`;
CREATE TABLE `testcase` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `summary` text,
  `steps` text,
  `exresult` text,
  `catid` int(10) unsigned NOT NULL default '0',
  `active` bool NOT NULL default 1,
  `version` smallint(5) unsigned NOT NULL default '0',
  `mgttcid` int(10) unsigned NOT NULL default '0',
  `keywords` text,
  `TCorder` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `mgttcid` (`mgttcid`),
  KEY `catid` (`catid`)
) TYPE=MyISAM COMMENT='Test case information';

# --------------------------------------------------------

#
# Table structure for table `user`
#

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `password` varchar(32) NOT NULL default '',
  `login` varchar(30) NOT NULL default '',
  `id` int(10) unsigned NOT NULL auto_increment,
  `rightsid` tinyint(3) unsigned NOT NULL default '0',
  `email` varchar(100) NOT NULL default '',
  `first` varchar(30) NOT NULL default '',
  `last` varchar(30) NOT NULL default '',
  `locale` varchar(10) NOT NULL default 'en_GB',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) TYPE=MyISAM COMMENT='User information' AUTO_INCREMENT=20;

    
# ---- END ----------------------------------------------------

