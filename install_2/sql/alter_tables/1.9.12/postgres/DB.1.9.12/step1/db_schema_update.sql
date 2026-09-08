-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- $Id: testlink_create_tables.sql,v 1.63.2.2 2010/12/11 17:25:21 franciscom Exp $
--
-- SQL script - Postgres   
-- 
-- IMPORTANT NOTE:
-- each NEW TABLE added here NEED TO BE DEFINED in object.class.php getDBTables()
--

--
-- Table structure for table testcase_relations
--
CREATE TABLE /*prefix*/testcase_relations (
  id BIGSERIAL NOT NULL,
  source_id INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id) ON DELETE CASCADE,
  destination_id  INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id) ON DELETE CASCADE,
  relation_type INT2 NOT NULL DEFAULT '1',
  author_id BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  creation_ts TIMESTAMP NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);