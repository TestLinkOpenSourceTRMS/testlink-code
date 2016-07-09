/* tested OK on POSTGRESQL                                           */
/* Pay attention if you have used table prefix                       */
/* CRITIC!!!!                                                        */
/* BACKUP YOUR DB BEFORE running this update                         */
/* After you have checked everything is OK, remove the custom fields */

/* Custom Field ESTIMATED EXECUTION TIME */
/* http://stackoverflow.com/questions/7869592/how-to-do-an-update-join-in-postgresql */
UPDATE tcversions TCV
SET estimated_exec_duration = CAST(TPZ.value AS numeric) 
FROM (
select id, field_id,node_id,value
from custom_fields CFDEF
JOIN cfield_design_values CFDV ON CFDV.field_id = id
where name ='CF_ESTIMATED_EXEC_TIME' ) TPZ
WHERE TPZ.node_id = TCV.id



/* tested OK on POSTGRESQL                                           */
/* Pay attention if you have used table prefix                       */
/* CRITIC!!!!                                                        */
/* BACKUP YOUR DB BEFORE running this update                         */
/* After you have checked everything is OK, remove the custom fields */

/* Custom Field EXECUTION TIME */
UPDATE executions EX
SET execution_duration = CAST(TPZ.value AS numeric)
FROM (
select id, field_id,execution_id,value
from custom_fields CFDEF
JOIN cfield_execution_values CFEV ON CFEV.field_id = id
where name ='CF_EXEC_TIME') TPZ
WHERE TPZ.execution_id = EX.id



