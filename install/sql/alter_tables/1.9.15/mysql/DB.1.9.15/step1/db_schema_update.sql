/* mysql */
INSERT INTO /*prefix*/rights  (id,description) VALUES (47,'testcase_freeze');

# Rights for Administrator role
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,47);

ALTER TABLE /*prefix*/cfield_testprojects ADD COLUMN monitorable tinyint(1) NOT NULL default '0';

ALTER TABLE /*prefix*/users MODIFY COLUMN login VARCHAR(100);
ALTER TABLE /*prefix*/users MODIFY COLUMN first VARCHAR(50);
ALTER TABLE /*prefix*/users MODIFY COLUMN last VARCHAR(50);