/* 
$Revision: 1.1 $
$Date: 2007/02/05 08:06:54 $
$Author: franciscom $
$Name:  $
*/

UPDATE priorities
SET risk=SUBSTRING(risk_importance FROM 1 FOR 1),
    importance=SUBSTRING(risk_importance FROM 2 FOR 1);

ALTER TABLE priorities DROP COLUMN risk_importance;

INSERT INTO db_version VALUES('1.7.0 Beta 5', now());