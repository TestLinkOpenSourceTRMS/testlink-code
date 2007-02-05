/* 
$Revision: 1.1 $
$Date: 2007/02/05 08:06:54 $
$Author: franciscom $
$Name:  $
*/

ALTER TABLE priorities ADD COLUMN `risk` CHAR(1) NOT NULL DEFAULT '2' AFTER `priority`;
ALTER TABLE priorities ADD COLUMN `importance` CHAR(1) NOT NULL DEFAULT 'M' AFTER `risk`;
ALTER TABLE priorities ADD UNIQUE KEY `tplan_prio_risk_imp`(`testplan_id`,`priority`, `risk`, `importance`);
ALTER TABLE priorities COMMENT = 'Updated to TL 1.7.0 Beta 5';