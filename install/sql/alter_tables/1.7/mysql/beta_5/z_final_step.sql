/* 
$Revision: 1.1 $
$Date: 2007/02/05 08:06:54 $
$Author: franciscom $
$Name:  $
*/

UPDATE priorities
SET risk=SUBSTRING(risk_importance,1,1),
    importance=SUBSTRING(risk_importance,2,1);

ALTER TABLE priorities DROP COLUMN risk_importance;

INSERT INTO db_version VALUES('1.7.0 Beta 5', CURRENT_TIMESTAMP());