-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres   
-- 
--
INSERT INTO /*prefix*/rights (id,description) VALUES (47,'testcase_freeze');
INSERT INTO /*prefix*/rights (id,description) VALUES (48,'mgt_plugins');

--  Rights for Administrator (admin role)
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,47);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,48);

ALTER TABLE /*prefix*/cfield_testprojects ADD COLUMN "monitorable" INT2 NOT NULL default '0';

ALTER TABLE /*prefix*/users ALTER COLUMN "login" SET DATA TYPE VARCHAR(100);
ALTER TABLE /*prefix*/users ALTER COLUMN "first" SET DATA TYPE VARCHAR(50);
ALTER TABLE /*prefix*/users ALTER COLUMN "last" SET DATA TYPE VARCHAR(50);

CREATE TABLE /*prefix*/req_monitor (
  req_id INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/requirements (id) ON DELETE CASCADE,
  user_id BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  testproject_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  PRIMARY KEY (req_id,user_id,testproject_id)
);

CREATE TABLE /*prefix*/plugins (
   plugin_id BIGSERIAL NOT NULL,
   basename  VARCHAR(100) NOT NULL,
   enabled INT2 NOT NULL DEFAULT '0',
   PRIMARY KEY (plugin_id)
);

CREATE TABLE /*prefix*/plugins_configuration (
   plugin_config_id BIGSERIAL NOT NULL,
   testproject_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
   config_key VARCHAR(255) NOT NULL,
   config_type INTEGER NOT NULL,
   config_value varchar(255) NOT NULL,
   author_id BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
   creation_ts TIMESTAMP NOT NULL DEFAULT now(),
   PRIMARY KEY (plugin_config_id)
);
