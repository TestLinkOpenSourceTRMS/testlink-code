/* mysql */
INSERT INTO /*prefix*/rights  (id,description) VALUES (47,'testcase_freeze');
INSERT INTO /*prefix*/rights  (id,description) VALUES (48,'mgt_plugins');

# Rights for Administrator role
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,47);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,48);

ALTER TABLE /*prefix*/cfield_testprojects ADD COLUMN monitorable tinyint(1) NOT NULL default '0';

ALTER TABLE /*prefix*/users MODIFY COLUMN login VARCHAR(100);
ALTER TABLE /*prefix*/users MODIFY COLUMN first VARCHAR(50);
ALTER TABLE /*prefix*/users MODIFY COLUMN last VARCHAR(50);

CREATE TABLE /*prefix*/req_monitor (
  `req_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `testproject_id` int(11) NOT NULL,
  PRIMARY KEY (`req_id`,`user_id`,`testproject_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/plugins (
   `id` int(11) NOT NULL auto_increment,
   `basename`  varchar(100) NOT NULL,
   `enabled` tinyint(1) NOT NULL default '0',
   `author_id` int(10) unsigned default NULL,
   `creation_ts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/plugins_configuration (
  `id` int(11) NOT NULL auto_increment,
  `testproject_id` int(11) NOT NULL,
  `config_key` varchar(255) NOT NULL,
  `config_type` int(11) NOT NULL,
  `config_value` varchar(255) NOT NULL,
  `author_id` int(10) unsigned default NULL,
  `creation_ts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
