ALTER TABLE /*prefix*/execution_tcsteps DROP PRIMARY KEY;
ALTER TABLE /*prefix*/execution_tcsteps ADD id INT PRIMARY KEY AUTO_INCREMENT;
ALTER TABLE /*prefix*/execution_tcsteps ADD UNIQUE KEY /*prefix*/execution_tcsteps_idx1 (execution_id, tcstep_id);