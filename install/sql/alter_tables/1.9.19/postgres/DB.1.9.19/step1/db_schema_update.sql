-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres

-- 
CREATE UNIQUE INDEX /*prefix*/keywords_keyword_testproject_id ON /*prefix*/keywords (testproject_id,keyword);
-- END