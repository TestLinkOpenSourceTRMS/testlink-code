/* 
$Revision: 1.1 $
$Date: 2007/02/05 08:06:54 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE risk_assignments ALTER COLUMN risk TYPE CHAR(1);
ALTER TABLE risk_assignments ALTER COLUMN risk SET NOT NULL;
ALTER TABLE risk_assignments ALTER COLUMN risk SET DEFAULT '2';
COMMENT ON TABLE risk_assignments IS 'Updated to TL 1.7.0 Beta 5';