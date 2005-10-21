 ALTER TABLE `requirements` ADD `req_doc_id` VARCHAR( 16 ) AFTER `id_srs`;
 ALTER TABLE `requirements` ADD INDEX `req_doc_id`;