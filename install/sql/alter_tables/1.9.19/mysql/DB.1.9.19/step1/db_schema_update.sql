
CREATE TABLE /*prefix*/execution_tcsteps_wip (
   id int(10) unsigned NOT NULL auto_increment,
   tcstep_id int(10) unsigned NOT NULL default '0',
   testplan_id int(10) unsigned NOT NULL default '0',
   platform_id int(10) unsigned NOT NULL default '0',
   build_id int(10) unsigned NOT NULL default '0',
   tester_id int(10) unsigned default NULL,
   backup_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   notes text,
   status char(1) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY /*prefix*/execution_tcsteps_backup_idx1(`tcstep_id`,`testplan_id`,`platform_id`,`build_id`)
) DEFAULT CHARSET=utf8;