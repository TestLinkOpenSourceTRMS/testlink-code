# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
# ---------------------------------------------------------------------------------------
# @filesource db_schema_update.sql
#
# SQL script - updates DB schema for MySQL - From TestLink 1.9.18 to 1.9.19
# 
#
#
ALTER TABLE /*prefix*/keywords ADD UNIQUE KEY /*prefix*/keyword_testproject_id (`keyword`,`testproject_id`);
# END
