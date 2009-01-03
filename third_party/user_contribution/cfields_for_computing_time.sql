/*Data for the table `custom_fields` */
insert into custom_fields 
(name,label,type,possible_values,default_value,valid_regexp,length_min,length_max,
 show_on_design,enable_on_design,show_on_execution,enable_on_execution,show_on_testplan_design,enable_on_testplan_design) 
values ('CF_ESTIMATED_EXEC_TIME','Estimated Exec time (minutes)',2,'','','',0,0,1,1,1,0,0,0);

insert into custom_fields 
(name,label,type,possible_values,default_value,valid_regexp,length_min,length_max,
 show_on_design,enable_on_design,show_on_execution,enable_on_execution,show_on_testplan_design,enable_on_testplan_design) 
 values ('CF_EXEC_TIME','Time used to execute test (min)',2,'','','',0,0,0,0,1,1,0,0);

/*Data for the table `cfield_node_types` */
insert into cfield_node_types (field_id,node_type_id) 
values ( (SELECT id FROM custom_fields WHERE name='CF_ESTIMATED_EXEC_TIME') ,3);

insert into cfield_node_types (field_id,node_type_id) 
values ( (SELECT id FROM custom_fields WHERE name='CF_EXEC_TIME') ,3);
