/* 
$Revision: 1.1 $
$Date: 2007/02/05 08:06:54 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE priorities ADD COLUMN risk CHAR(1) NOT NULL DEFAULT '2';
ALTER TABLE priorities ADD COLUMN importance CHAR(1) NOT NULL DEFAULT 'M';
ALTER TABLE priorities ALTER COLUMN priority SET DEFAULT 'B';
CREATE UNIQUE INDEX tplan_prio_risk_imp ON priorities ("testplan_id","priority", "risk", "importance");
COMMENT ON TABLE priorities IS 'Updated to TL 1.7.0 Beta 5';