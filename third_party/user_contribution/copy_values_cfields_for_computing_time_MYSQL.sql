/* tested OK on MySQL                                                */
/* Pay attention if you have used table prefix                       */
/* CRITIC!!!!                                                        */
/* BACKUP YOUR DB BEFORE running this update                         */
/* After you have checked everything is OK, remove the custom fields */

/* Custom Field ESTIMATED EXECUTION TIME */
UPDATE tcversions TCV
JOIN (
select id, field_id,node_id,value
from custom_fields CFDEF
JOIN cfield_design_values CFDV ON CFDV.field_id = id
where name ='CF_ESTIMATED_EXEC_TIME' ) TPZ
ON TPZ.node_id = TCV.id
SET estimated_exec_duration = TPZ.value 

/* tested OK on MySQL                                                */
/* Pay attention if you have used table prefix                       */
/* CRITIC!!!!                                                        */
/* BACKUP YOUR DB BEFORE running this update                         */
/* After you have checked everything is OK, remove the custom fields */

/* Custom Field EXECUTION TIME */
UPDATE executions EX
JOIN (
select id, field_id,execution_id,value
from custom_fields CFDEF
JOIN cfield_execution_values CFEV ON CFEV.field_id = id
where name ='CF_EXEC_TIME') TPZ
ON TPZ.execution_id = EX.id
SET execution_duration = TPZ.value