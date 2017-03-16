/* mysql */
ALTER TABLE /*prefix*/executions ADD KEY /*prefix*/executions_idx3 (tcversion_id);
ALTER TABLE /*prefix*/attachments ADD KEY /*prefix*/attachments_idx1 (fk_id);