{lang_get var='labels' 
          s='assign_table_header_fieldvals,assign_table_header_users,btn_cancel,btn_ok'}

<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="../../gui/themes/default/css/testlink.css"/>
	</head>
	<body>
		<div class="workBack">
			<form id="assignmentForm" method="post" action="notificationAssignmentConfig.php" >
				<input hidden type="text" name="fieldName" value="{$gui->fieldName}"/>
				<table class="common sortable dataTable no-footer">
					<th>{$labels.assign_table_header_fieldvals}</th><th>{$labels.assign_table_header_users}</th>
					{foreach key=fieldValNr item=fieldVal from=$gui->fieldVals}
						<tr>
							<td>{$fieldVal}</td>
							<td>
								<select name="select_{$fieldVal}">
									<option id="empty"/>
									{$addSelectedUser = false}
									{foreach key=userIndex item=userName from=$gui->users}
										{for $i=0 to sizeof($gui->fieldAssignments[$gui->fieldName]["field_value"])-1}
											{if strcmp($fieldVal,$gui->fieldAssignments[$gui->fieldName]["field_value"][$i]) === 0
											 and strcmp($userName,$gui->fieldAssignments[$gui->fieldName]["user_name"][$i]) === 0}
												{$addSelectedUser = true}
												{break}
											{/if}
										{/for}
										{if $addSelectedUser}
											<option	selected="selected" id={$userIndex}>{$userName}</option>
											{$addSelectedUser = false}
										{else}
											<option	id={$userIndex}>{$userName}</option>
										{/if}
									{/foreach}
								</select>
							</td>
						</tr>
					{/foreach}
				</table>
				<input id="assignmentFormSubmit" name="submit" type="submit" value="{$labels.btn_ok}" />
				<input id="assignmentFormCancel" name="cancel" type="submit" value="{$labels.btn_cancel}" />
			</form>
		</div>
	</body>
</html>