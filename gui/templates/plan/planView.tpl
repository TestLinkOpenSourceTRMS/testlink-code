{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
Filename: planView.tpl
smarty template - edit / delete Test Plan 

Development hint:
     some smarty and javascript variables are created on the inc_*.tpl files.
     
Rev:
    20110220 - franciscom - use of thead abd tbody
    						BUGID 4246 - add simple table ruler via events
    20101017 - franciscom - image access refactored (tlImages)
    20100925 - franciscom - BUGID 3649 - test plan export/import -> EXPORT
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{assign var="managerURL" value="lib/plan/planEdit.php"}
{assign var="editAction" value="$managerURL?do_action=edit&amp;tplan_id="}
{assign var="deleteAction" value="$managerURL?do_action=do_delete&tplan_id="}
{assign var="createAction" value="$managerURL?do_action=create"}
{assign var="exportAction" value="lib/plan/planExport.php?tplan_id="}
{assign var="importAction" value="lib/plan/planImport.php?tplan_id="}


{lang_get var="labels" 
          s='testplan_title_tp_management,testplan_txt_empty_list,sort_table_by_column,
          testplan_th_name,testplan_th_notes,testplan_th_active,testplan_th_delete,
          testplan_alt_edit_tp,alt_active_testplan,testplan_alt_delete_tp,public,
          btn_testplan_create,th_id,error_no_testprojects_present,btn_export_import,
          export_import,export,import,export_testplan_links,import_testplan_links'}


{lang_get s='warning_delete_testplan' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{include file="inc_head.tpl" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$deleteAction}';
</script>

</head>

<body {$body_onload}>

<h1 class="title">{$gui->main_descr|escape}</h1>
{if $gui->user_feedback ne ""}
	<div>
		<p class="info">{$gui->user_feedback}</p>
	</div>
{/if}

<div class="workBack">
<div id="testplan_management_list">
{if $gui->tproject_id <= 0}
	{$labels.error_no_testprojects_present}
{elseif $gui->tplans eq ''}
	{$labels.testplan_txt_empty_list}
{else}
	<table id='item_view'class="simple_tableruler sortable">
		<thead>
		<tr>
			<th>{$tlImages.toggle_api_info}{$tlImages.sort_hint}{$labels.testplan_th_name}</th> 			
			<th class="{$noSortableColumnClass}">{$labels.testplan_th_notes}</th>
			<th class="{$noSortableColumnClass}">{$labels.testplan_th_active}</th>
			<th class="{$noSortableColumnClass}">{$labels.public}</th>
			<th class="{$noSortableColumnClass}">{$labels.testplan_th_delete}</th>
			<th class="{$noSortableColumnClass}">{$labels.export}</th>
			<th class="{$noSortableColumnClass}">{$labels.import}</th>
		</tr>
		</thead>
		<tbody>
		{foreach item=testplan from=$gui->tplans}
		<tr>
			<td><span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$testplan.id}</span>
			    <a href="{$editAction}{$testplan.id}"> 
				     {$testplan.name|escape} 
				     {if $gsmarty_gui->show_icon_edit}
 				         <img title="{$labels.testplan_alt_edit_tp}"  alt="{$labels.testplan_alt_edit_tp}" 
 				              src="{$tlImages.edit}"/>
 				     {/if}  
 				  </a>
			</td>
			<td>
				{$testplan.notes|strip_tags|strip|truncate:#TESTPLAN_NOTES_TRUNCATE#}
			</td>
			<td class="clickable_icon">
				{if $testplan.active eq 1} 
  					<img style="border:none" title="{$labels.alt_active_testplan}" alt="{$labels.alt_active_testplan}" 
  				       src="{$tlImages.checked}"/>
  				{else}
  					&nbsp;        
  				{/if}
			</td>
			<td class="clickable_icon">
				{if $testplan.is_public eq 1} 
  					<img style="border:none" title="{$labels.public}"  alt="{$labels.public}" src="{$tlImages.checked}"/>
  				{else}
  					&nbsp;        
  				{/if}
			</td>
			<td class="clickable_icon">
				  <img style="border:none;cursor: pointer;" 
				       alt="{$labels.testplan_alt_delete_tp}"
					   title="{$labels.testplan_alt_delete_tp}" 
					   onclick="delete_confirmation({$testplan.id},'{$testplan.name|escape:'javascript'|escape}',
					                                '{$del_msgbox_title}','{$warning_msg}');"
				     src="{$tlImages.delete}"/>
			</td>
			<td class="clickable_icon">
			    <a href="{$exportAction}{$testplan.id}"> 
				  <img style="border:none;cursor: pointer;" alt="{$labels.export_testplan_links}" 
				       title="{$labels.export_testplan_links}" src="{$tlImages.export}"/>
				  </a>     
			</td>
			<td class="clickable_icon">
			    <a href="{$importAction}{$testplan.id}"> 
				  <img style="border:none;cursor: pointer;" alt="{$labels.import_testplan_links}" 
				       title="{$labels.import_testplan_links}"  src="{$tlImages.import}"/>
				  </a>     
			</td>
		</tr>
		{/foreach}
		</tbody>
	</table>

{/if}
</div>

 {if $gui->grants->testplan_create && $gui->tproject_id > 0}
 <div class="groupBtn">
    <form method="post" action="{$createAction}">
      <input type="submit" name="create_testplan" value="{$labels.btn_testplan_create}" />
    </form>
  </div>
 {/if}
</div>

</body>
</html>