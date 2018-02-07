-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- ---------------------------------------------------------------------------------------
-- @filesource testlink_create_udf0.sql
-- 
-- 
CREATE OR REPLACE FUNCTION public.udfstriphtmltags(text)
  RETURNS text AS
$BODY$
     SELECT regexp_replace(
        regexp_replace($1, E'(?x)<[^>]*?(\s alt \s* = \s* ([\'"]) ([^>]*?) \2) [^>]*? >', E'\3'), 
       E'(?x)(< [^>]*? >)', '', 'g')
 $BODY$
  LANGUAGE sql VOLATILE;