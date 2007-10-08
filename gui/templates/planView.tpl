{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planView.tpl,v 1.4 2007/10/08 07:25:42 franciscom Exp $ 
Purpose: smarty template - edit / delete Test Plan 

Development hint:
     some variables smarty and javascript are created on the inc_*.tpl files.
     
Rev :
     20071006 - franciscom - added logic to use ext js confirm widget
     
*}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get s='testplan_alt_delete_tp' var="warning_msg"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
var o_label ="{lang_get s='testplan'}";
var del_action=fRoot+'lib/plan/planEdit.php?do_action=do_delete&tplan_id=';
</script>

</head>

<body {$body_onload}>

<h1>{lang_get s='testplan_title_tp_management'}</h1>
{if $editResult ne ""}
	<div>
		<p class="info">{$editResult}</p>
	</div>
{/if}

<div class="workBack">
<div id="testplan_management_list">
{if $tplans eq ''}
	{lang_get s='testplan_txt_empty_list'}

{else}
	{* <h2>{lang_get s='testplan_title_list'}</h2> *}
	<table class="simple" width="95%">
		<tr>
			<th>{lang_get s='testplan_th_name'}</th>
			<th>{lang_get s='testplan_th_notes'}</th>
			<th>{lang_get s='testplan_th_active'}</th>
			<th>{lang_get s='testplan_th_delete'}</th>
		</tr>
		{foreach item=testplan from=$tplans}
		<tr>
			<td><a href="lib/plan/planEdit.php?tplan_id={$testplan.id}&do_action=edit"> 
				     {$testplan.name|escape} 
				     {if $gsmarty_gui->show_icon_edit}
 				         <img title="{lang_get s='testplan_alt_edit_tp'}" 
 				              alt="{lang_get s='testplan_alt_edit_tp'}" 
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
  				            title="{lang_get s='alt_active_testplan'}" 
  				            alt="{lang_get s='alt_active_testplan'}" 
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png"/>
  				{else}
  					&nbsp;        
  				{/if}
			</td>
			<td class="clickable_icon">
				  <img style="border:none" 
				       alt="{lang_get s='testplan_alt_delete_tp'}"
					   title="{lang_get s='testplan_alt_delete_tp'}" 
					   onclick='delete_confirmation({$testplan.id},"{$testplan.name|escape:'javascript'}","{$warning_msg}");'
				     src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/>
			</td>
		</tr>
		{/foreach}

	</table>

{/if}
</div>

 {if $testplan_create}
 <div class="groupBtn">
    <form method="post" action="lib/plan/planEdit.php?do_action=create">
      <input type="submit" name="create_testplan" value="{lang_get s='btn_testplan_create'}" />
    </form>
  </div>
 {/if}
</div>



</body>
</html>
