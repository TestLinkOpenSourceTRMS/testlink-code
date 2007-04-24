/* 
$Revision: 1.1 $
$Date: 2007/04/24 14:27:19 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE requirements ADD COLUMN node_order BIGINT DEFAULT 0;
COMMENT ON TABLE requirements IS 'Updated to TL 1.7.0 RC 2';