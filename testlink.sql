# phpMyAdmin SQL Dump
# version 2.5.3
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Oct 14, 2003 at 02:53 PM
# Server version: 4.0.15
# PHP Version: 4.3.3
# 
# Database : `TestLink`
# 

# --------------------------------------------------------

#
# Table structure for table `bugs`
#

CREATE TABLE `bugs` (
  `tcid` int(11) NOT NULL default '0',
  `build` varchar(30) NOT NULL default '',
  `bug` int(11) NOT NULL default '0',
  PRIMARY KEY  (`tcid`,`build`,`bug`),
  KEY `tcid` (`tcid`),
  KEY `build` (`build`),
  KEY `bug` (`bug`)
) TYPE=MyISAM COMMENT='This table holds the bugs filed for each result';
    
#
# Table structure for table `build`
#

CREATE TABLE `build` (
  `build` int(11) NOT NULL default '0',
  `projid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`build`,`projid`)
) TYPE=MyISAM COMMENT='This table holds all of the available builds';

# --------------------------------------------------------

#
# Table structure for table `category`
#

CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` text,
  `compid` int(10) unsigned default NULL,
  `importance` enum('L','M','H') NOT NULL default 'M',
  `risk` enum('1','2','3') NOT NULL default '2',
  `owner` varchar(20) default 'none',
  `mgtcatid` int(11) NOT NULL default '0',
  `CATorder` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `compid` (`compid`)
) TYPE=MyISAM COMMENT='This table holds all of the category information' AUTO_INCREMENT=121 ;

# --------------------------------------------------------

#
# Table structure for table `component`
#

CREATE TABLE `component` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` text,
  `projid` int(10) unsigned default NULL,
  `mgtcompid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `projid` (`projid`)
) TYPE=MyISAM AUTO_INCREMENT=34 ;

# --------------------------------------------------------

#
# Table structure for table `keywords`
#

CREATE TABLE `keywords` (
  `id` int(11) NOT NULL auto_increment,
  `keyword` varchar(30) NOT NULL default '',
  `prodid` int(11) NOT NULL default '0',
  `notes` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='this table holds all of the keywords for each product' AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `mgtcategory`
#

CREATE TABLE `mgtcategory` (
  `id` int(11) NOT NULL auto_increment,
  `name` text,
  `objective` text NOT NULL,
  `config` text NOT NULL,
  `data` text NOT NULL,
  `tools` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `compid` int(11) default NULL,
  `CATorder` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `compid` (`compid`)
) TYPE=MyISAM COMMENT='The different categories of the TC management DB' AUTO_INCREMENT=71 ;

#
# Table structure for table `mgtcomponent`
#

CREATE TABLE `mgtcomponent` (
  `id` int(11) NOT NULL auto_increment,
  `name` text,
  `intro` text,
  `scope` text,
  `ref` text,
  `method` text,
  `lim` text,
  `prodid` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `prodid` (`prodid`)
) TYPE=MyISAM COMMENT='The different components of the TC management DB' AUTO_INCREMENT=1 ;


#
# Table structure for table `mgtproduct`
#

CREATE TABLE `mgtproduct` (
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `color` varchar(12) NOT NULL default '#0066CC',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='The different products of the TC management DB' AUTO_INCREMENT=3 ;

# --------------------------------------------------------

#
# Table structure for table `mgttcarchive`
#

CREATE TABLE `mgttcarchive` (
  `id` int(11) NOT NULL default '0',
  `title` text,
  `steps` text,
  `exresult` text,
  `keywords` text,
  `version` int(11) NOT NULL default '0',
  `summary` text,
  `author` varchar(30) default NULL,
  PRIMARY KEY  (`id`,`version`),
  KEY `id` (`id`)
) TYPE=MyISAM COMMENT='The test case archive for the TC management DB';


#
# Table structure for table `mgttestcase`
#

CREATE TABLE `mgttestcase` (
  `id` int(11) NOT NULL auto_increment,
  `title` text,
  `steps` text,
  `exresult` text,
  `keywords` text,
  `catid` int(11) default NULL,
  `version` int(11) NOT NULL default '1',
  `summary` text,
  `author` varchar(30) default NULL,
  `TCorder` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`)
) TYPE=MyISAM COMMENT='The different test cases of the TC management DB' AUTO_INCREMENT=146 ;

#
# Table structure for table `milestone`
#

CREATE TABLE `milestone` (
  `id` int(11) NOT NULL auto_increment,
  `projid` int(11) NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  `A` int(11) NOT NULL default '0',
  `B` int(11) NOT NULL default '0',
  `C` int(11) NOT NULL default '0',
  `name` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `milestone`
#


# --------------------------------------------------------

#
# Table structure for table `priority`
#

CREATE TABLE `priority` (
  `id` int(11) NOT NULL auto_increment,
  `projid` int(11) NOT NULL default '0',
  `riskImp` char(2) NOT NULL default '',
  `priority` enum('a','b','c') NOT NULL default 'b',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=325 ;

#
# Dumping data for table `priority`
#

# --------------------------------------------------------

#
# Table structure for table `project`
#

CREATE TABLE `project` (
  `id` int(10) NOT NULL auto_increment,
  `name` text,
  `notes` text,
  `active` enum('y','n') NOT NULL default 'y',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) TYPE=MyISAM COMMENT='This table holds all of the project information' AUTO_INCREMENT=113 ;


#
# Table structure for table `projrights`
#

CREATE TABLE `projrights` (
  `userid` int(11) NOT NULL default '0',
  `projid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`userid`,`projid`)
) TYPE=MyISAM COMMENT='User''s project permissions';


#
# Table structure for table `results`
#

CREATE TABLE `results` (
  `build` int(11) NOT NULL default '0',
  `runby` varchar(30) default NULL,
  `daterun` date default NULL,
  `status` char(1) default NULL,
  `bugs` varchar(30) default NULL,
  `tcid` int(10) unsigned NOT NULL default '0',
  `notes` text,
  PRIMARY KEY  (`tcid`,`build`),
  KEY `tcid` (`tcid`),
  KEY `status` (`status`)
) TYPE=MyISAM;


#
# Table structure for table `rights`
#

CREATE TABLE `rights` (
  `id` int(11) NOT NULL auto_increment,
  `role` varchar(20) NOT NULL default '',
  `rights` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='This table holds all of the rights (permissions)' AUTO_INCREMENT=10 ;

#
# Dumping data for table `rights`
#

INSERT INTO `rights` (`id`, `role`, `rights`) VALUES (5, 'guest', 'tp_metrics,mgt_view_product,mgt_view_tc,mgt_view_key');
INSERT INTO `rights` (`id`, `role`, `rights`) VALUES (6, 'tester', 'tp_execute,tp_metrics,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_view_product');
INSERT INTO `rights` (`id`, `role`, `rights`) VALUES (7, 'otester', 'tp_execute,tp_metrics');
INSERT INTO `rights` (`id`, `role`, `rights`) VALUES (8, 'admin', 'tp_execute,tp_create_build,tp_metrics,tp_planning,tp_assign_rights,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_modify_key,mgt_view_product,mgt_modify_product,mgt_users');
INSERT INTO `rights` (`id`, `role`, `rights`) VALUES (9, 'lead', 'tp_execute,tp_create_build,tp_metrics,tp_planning,tp_assign_rights,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_modify_key,mgt_view_product');

# --------------------------------------------------------

#
# Table structure for table `testcase`
#

CREATE TABLE `testcase` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` text,
  `summary` text,
  `steps` text,
  `exresult` text,
  `catid` int(10) NOT NULL default '0',
  `active` enum('on','off') NOT NULL default 'on',
  `version` int(11) NOT NULL default '0',
  `mgttcid` int(11) NOT NULL default '0',
  `keywords` text,
  `TCorder` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `mgttcid` (`mgttcid`),
  KEY `catid` (`catid`)
) TYPE=MyISAM COMMENT='This table holds all of the test case information' AUTO_INCREMENT=9 ;


#
# Table structure for table `user`
#

CREATE TABLE `user` (
  `password` varchar(15) default NULL,
  `login` varchar(15) default NULL,
  `id` int(10) unsigned NOT NULL auto_increment,
  `rightsid` int(11) NOT NULL default '0',
  `email` varchar(35) NOT NULL default '',
  `first` varchar(20) NOT NULL default '',
  `last` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='This table holds all of the user information' AUTO_INCREMENT=45 ;

#
# Dumping data for table `user`
#

INSERT INTO `user` (`password`, `login`, `id`, `rightsid`, `email`, `first`, `last`) VALUES ('admin', 'admin', 1, 8, '', 'ad', 'min');
