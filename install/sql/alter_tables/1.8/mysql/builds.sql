/* 
$Revision: 1.1 $
$Date: 2007/10/19 06:53:14 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE `builds` CHANGE COLUMN `open` `is_open` TINYINT(1) NOT NULL DEFAULT 1;