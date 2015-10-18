/* mysql */
INSERT INTO /*prefix*/rights  (id,description) VALUES (47,'testcase_freeze');

# Rights for Administrator role
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,47);
