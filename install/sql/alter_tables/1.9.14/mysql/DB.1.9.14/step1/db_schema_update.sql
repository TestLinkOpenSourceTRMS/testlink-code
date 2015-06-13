/* mysql */
ALTER TABLE /*prefix*/executions ADD UNIQUE KEY /*prefix*/executions_idx3 (tcversion_id);
ALTER TABLE /*prefix*/attachments ADD UNIQUE KEY /*prefix*/attachments_idx1 (fk_id);