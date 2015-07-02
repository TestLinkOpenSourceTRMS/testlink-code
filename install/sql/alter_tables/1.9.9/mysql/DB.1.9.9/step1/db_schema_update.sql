/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema MySQL database for TestLink 1.9 from version 1.8 
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * @filesource	db_schema_update.sql
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * @internal revisions
 * @since 1.9.9
 */

# ==============================================================================
# ATTENTION PLEASE - replace /*prefix*/ with your table prefix if you have any. 
# ==============================================================================

CREATE TABLE /*prefix*/cfield_build_design_values (
  `field_id` int(10) NOT NULL default '0',
  `node_id` int(10) NOT NULL default '0',
  `value` varchar(4000) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`node_id`),
  KEY /*prefix*/idx_cfield_build_design_values (`node_id`)
) DEFAULT CHARSET=utf8;

/* users */
ALTER TABLE /*prefix*/users ADD COLUMN auth_method varchar(10) NULL default '' AFTER cookie_string;

/* testprojects */
/* Need to do process in two steps */
ALTER TABLE /*prefix*/testprojects ADD COLUMN api_key varchar(64) NOT NULL default '0d8ab81dfa2c77e8235bc829a2ded3edfa2c78235bc829a27eded3ed0d8ab81d';
update /*prefix*/testprojects SET api_key = CONCAT(MD5(RAND()),MD5(RAND()));
ALTER TABLE /*prefix*/testprojects ADD UNIQUE INDEX /*prefix*/testprojects_api_key (`api_key`);

/* Need to do process in two steps */
ALTER TABLE /*prefix*/testplans ADD COLUMN api_key varchar(64) NOT NULL default '829a2ded3ed0829a2dedd8ab81dfa2c77e8235bc3ed0d8ab81dfa2c77e8235bc';
update /*prefix*/testplans SET api_key = CONCAT(MD5(RAND()),MD5(RAND()));
ALTER TABLE /*prefix*/testplans ADD UNIQUE INDEX /*prefix*/testplans_api_key (`api_key`);
/* ----- END ----- */