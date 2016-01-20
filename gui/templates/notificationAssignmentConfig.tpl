{lang_get var='labels' 
          s='test_project_names,assign_button_label,assign_table_header_fieldvals,assign_table_header_users,assign_btn_change,assign_btn_delete'}

<html>
 <head>
 </head>
 <body>
	{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
	<div class="workBack">
		<form method="post" action="lib/notificationassignments/notificationAssignmentCreate.php">
			<script>
			function submitBtnEnablePolicy() {
				var select = document.getElementsByName("fieldName")[0];
				var changeBtn = document.getElementsByName("change")[0];
				if(select.options[select.selectedIndex].text.localeCompare("")==0) {
					changeBtn.disabled = true;
				}else {
					changeBtn.disabled = false;
				}
			}
			</script>
			<label for="testProject">{$labels.test_project_names}</label>
				<select name="fieldName" onchange="submitBtnEnablePolicy()">
					{html_options options=$gui->fieldNames}
				</select>
			<input type="submit" name="change" value="{$labels.assign_button_label}" disabled=true; />
		</form>
	</div>
	{if isset($gui->assignments)}
	<div class="workBack">
		{foreach key=fieldName item=fieldVal from=$gui->assignments}
		<div class="workBack">
			<form method="POST" >
				<h3>{$fieldName}</h3>
				<table class="common sortable dataTable no-footer">
					<th>{$labels.assign_table_header_fieldvals}</th><th>{$labels.assign_table_header_users}</th>
					{for $i=0 to sizeof($fieldVal["field_value"])}
						<tr>
							<td>{$fieldVal["field_value"][$i]}</td>
							<td>{$fieldVal["user_name"][$i]}</td>
						</tr>
					{/for}
				</table>
				<input type="text" name="fieldName" style="display:none" value="{$fieldName}"/>
				<input type="submit" name="change" value="{$labels.assign_btn_change}" formaction="lib/notificationassignments/notificationAssignmentCreate.php" />
				<input type="submit" name="delete" value="{$labels.assign_btn_delete}" formaction="lib/notificationassignments/notificationAssignmentConfig.php" />
			</form>
		</div>
		{/foreach}
	</div>
	{/if}
 </body>
</html>