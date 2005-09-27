/* Migration from 1.5 to 1.6 */
CREATE TABLE `db_version` (
  version varchar(50) NOT NULL default '1.6 BETA 1',
  upgrade_date datetime NOT NULL default '0000-00-00 00:00'
);
INSERT INTO db_version VALUES('1.6 POST RC1', CURRENT_TIMESTAMP());