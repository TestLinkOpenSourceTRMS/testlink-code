/* 
$Revision: 1.1 $
$Date: 2007/01/22 08:31:14 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE builds ADD COLUMN active INT2 NOT NULL DEFAULT 1;
ALTER TABLE builds ADD COLUMN open INT2 NOT NULL DEFAULT 1;

