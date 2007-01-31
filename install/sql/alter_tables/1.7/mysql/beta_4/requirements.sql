/* 
$Revision: 1.1 $
$Date: 2007/01/31 14:14:56 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE requirements MODIFY req_doc_id varchar(32) default NULL;
ALTER TABLE requirements DROP INDEX req_doc_id; 
ALTER TABLE requirements ADD UNIQUE KEY `req_doc_id` (`srs_id`,`req_doc_id`);
ALTER TABLE requirements COMMENT = 'Updated to TL 1.7.0 Beta 4';
