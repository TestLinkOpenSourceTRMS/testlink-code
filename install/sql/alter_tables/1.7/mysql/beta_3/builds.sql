/* 
$Revision: 1.1 $
$Date: 2007/01/22 08:31:14 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE builds ADD COLUMN active TINYINT NOT NULL DEFAULT 1 AFTER notes;
ALTER TABLE builds ADD COLUMN open TINYINT NOT NULL DEFAULT 1 AFTER active;
ALTER TABLE builds COMMENT = 'Updated to TL 1.7.0 Beta 3';
