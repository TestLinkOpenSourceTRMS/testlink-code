/* 
$Revision: 1.1 $
$Date: 2007/02/05 08:06:54 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE risk_assignments MODIFY COLUMN `risk` CHAR(1) NOT NULL DEFAULT '2';
ALTER TABLE risk_assignments COMMENT = 'Updated to TL 1.7.0 Beta 5';