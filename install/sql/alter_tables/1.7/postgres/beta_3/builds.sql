/* 
$Revision: 1.2 $
$Date: 2007/01/31 14:13:33 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE builds ADD COLUMN active INT2 NOT NULL DEFAULT 1;
ALTER TABLE builds ADD COLUMN open INT2 NOT NULL DEFAULT 1;
COMMENT ON TABLE builds IS 'Updated to TL 1.7.0 Beta 3';

