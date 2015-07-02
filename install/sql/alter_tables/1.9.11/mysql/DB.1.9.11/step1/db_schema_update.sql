CREATE TABLE /*prefix*/execution_tcsteps (
   execution_id int(10) unsigned NOT NULL default '0',
   tcstep_id int(10) unsigned NOT NULL default '0',
   notes text,
   status char(1) default NULL,
  PRIMARY KEY  (`execution_id`,`tcstep_id`)
) DEFAULT CHARSET=utf8;
