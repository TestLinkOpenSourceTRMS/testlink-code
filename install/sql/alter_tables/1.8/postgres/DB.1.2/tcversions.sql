/* 
tcversions.sql - postgres
$File:$
$Revision: 1.1 $
$Date: 2008/01/02 18:56:17 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE tcversions ADD COLUMN execution_type INT2 NOT NULL default '1';
COMMENT ON COLUMN tcversions.execution_type IS '1 -> manual, 2 -> automated';