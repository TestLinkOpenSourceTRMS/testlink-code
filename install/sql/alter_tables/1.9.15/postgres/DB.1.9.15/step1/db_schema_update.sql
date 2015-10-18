-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres   
-- 
--
INSERT INTO /*prefix*/rights (id,description) VALUES (47,'testcase_freeze');

--  Rights for Administrator (admin role)
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,47);