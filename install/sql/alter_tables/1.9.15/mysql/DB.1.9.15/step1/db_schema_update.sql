/* mysql */
INSERT INTO /*prefix*/rights  (id,description) VALUES (47,'testcase_freeze');

# Rights for Administrator role
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,47);

ALTER TABLE /*prefix*/cfield_testprojects ADD COLUMN monitorable tinyint(1) NOT NULL default '0';

ALTER TABLE /*prefix*/users MODIFY COLUMN login VARCHAR(100);
ALTER TABLE /*prefix*/users MODIFY COLUMN first VARCHAR(50);
ALTER TABLE /*prefix*/users MODIFY COLUMN last VARCHAR(50);

CREATE TABLE /*prefix*/req_notify_assignments (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `testproject_id` int(11) NOT NULL,
  `field_id` int(10) NOT NULL,
  `field_value` varchar(64) NOT NULL,
  `assigned_user_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/req_monitor (
  `req_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `testproject_id` int(11) NOT NULL,
  PRIMARY KEY (`req_id`,`user_id`,`testproject_id`)
) DEFAULT CHARSET=utf8;