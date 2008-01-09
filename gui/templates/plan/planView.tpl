{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planView.tpl,v 1.5 2008/01/09 15:21:20 franciscom Exp $ 
Purpose: smarty template - edit / delete Test Plan 

Development hint:
     some variables smarty and javascript are created on the inc_*.tpl files.
     
Rev:
    20080109 - franciscom - added sort table by JS
    20071006 - franciscom - added logic to use ext js confirm widget
     
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{assign var="theme_dir" value=$smarty.const.TL_THEME_IMG_DIR}
{assign var="sort_img" value="$theme_dir/sort_hint.png"}
{assign var="checked_img" value="$theme_dir/apply_f2_16.png"}
{assign var="delete_img" value="$theme_dir/trash.png"}


{lang_get var="labels" 
          s='testplan_title_tp_management,testplan_txt_empty_list,sort_table_by_column,
          testplan_th_name,testplan_th_notes,testplan_th_active,testplan_th_delete,
          testplan_alt_edit_tp,alt_active_testplan,testplan_alt_delete_tp,
          btn_testplan_create'}


{lang_get s='warning_delete_testplan' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}
<script type="text/javascript" src="{$basehref}gui/javascript/sorttable.js" language="javascript"></script>

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/plan/planEdit.php?do_action=do_delete&tplan_id=';
</script>

</head>

<body {$body_onload}>

<h1>{$labels.testplan_title_tp_management}</h1>
{if $editResult ne ""}
	<div>
		<p class="info">{$editResult}</p>
	</div>
{/if}

<div class="workBack">
<div id="testplan_management_list">
{if $tplans eq ''}
	{$labels.testplan_txt_empty_list}

{else}
	<table class="simple sortable" width="95%">
		<tr>
			<th><img title="{$labels.sort_table_by_column}"  alt="{$labels.sort_table_by_column}" 
 				       src="{$sort_img}" align="left"/>{$labels.testplan_th_name}
 			</th>
			<th class="sorttable_nosort">{$labels.testplan_th_notes}</th>
			<th class="sorttable_nosort">{$labels.testplan_th_active}</th>
			<th class="sorttable_nosort">{$labels.testplan_th_delete}</th>
		</tr>
		{foreach item=testplan from=$tplans}
		<tr>
			<td><a href="lib/plan/planEdit.php?tplan_id={$testplan.id}&amp;do_action=edit"> 
				     {$testplan.name|escape} 
				     {if $gsmarty_gui->show_icon_edit}
 				         <img title="{$labels.testplan_alt_edit_tp}" 
 				              alt="{$labels.testplan_alt_edit_tp}" 
 				              src="{$smarty.const.TL_THEME_IMG_DIR}/icon_edit.png"/>
 				     {/if}  
 				  </a>
			</td>
			<td>
				{$testplan.notes|strip_tags|strip|truncate:#TESTPLAN_NOTES_TRUNCATE#}
			</td>
			<td class="clickable_icon">
				{if $testplan.active eq 1} 
  					<img style="border:none" 
  				            title="{$labels.alt_active_testplan}" 
  				            alt="{$labels.alt_active_testplan}" 
  				            src="{$checked_img}"/>
  				{else}
  					&nbsp;        
  				{/if}
			</td>
			<td class="clickable_icon">
				  <img style="border:none;cursor: pointer;" 
				       alt="{$labels.testplan_alt_delete_tp}"
					   title="{$labels.testplan_alt_delete_tp}" 
					   onclick="delete_confirmation({$testplan.id},'{$testplan.name|escape:'javascript'}',
					                                '{$del_msgbox_title}','{$warning_msg}');"
				     src="{$delete_img}"/>
			</td>
		</tr>
		{/foreach}

	</table>

{/if}
</div>

 {if $testplan_create}
 <div class="groupBtn">
    <form method="post" action="lib/plan/planEdit.php?do_action=create">
      <input type="submit" name="create_testplan" value="{$labels.btn_testplan_create}" />
    </form>
  </div>
 {/if}
</div>



</body>
</html>
