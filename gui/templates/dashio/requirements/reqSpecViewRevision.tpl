{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource reqSpecViewRevision.tpl
Purpose: view requirement spec revision READ ONLY

@internal revisions
@since 1.9.4
20110816 - franciscom - TICKET 4703: Req. Spec. View - display log message 

*}
{config_load file="input_dimensions.conf"}
{assign var="my_style" value=""}
{if $gui->hilite_item_name}
    {assign var="my_style" value="background:#059; color:white; margin:0px 0px 4px 0px;padding:3px;"}
{/if}
{assign var=this_template_dir value=$smarty.template|dirname}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
{include file="inc_ext_js.tpl"}

<script type="text/javascript">
{literal}
Ext.onReady(function(){ 
{/literal}
tip4log({$gui->item.id});
{literal}
});

function tip4log(itemID)
{
	var fUrl = fRoot+'lib/ajax/getreqspeclog.php?item_id=';
	new Ext.ToolTip({
        target: 'tooltip-'+itemID,
        width: 500,
        autoLoad:{url: fUrl+itemID},
        dismissDelay: 0,
        trackMouse: true
    });
}
{/literal}
</script>	
</head>

<body>
<h1 class="title">{$gui->main_descr|escape}</h1>
<div class="workBack">

{lang_get var="labels"
          s="requirement_spec,Requirements,scope,status,type,expected_coverage,  
             coverage,btn_delete,btn_cp,btn_edit,btn_del_this_version,btn_new_version,
             btn_del_this_version, btn_freeze_this_version, version, can_not_edit_req,
             testproject,title_last_mod,title_created,by,btn_compare_versions,showing_version,
             btn_revisions,revision,btn_print_view,req_spec,log_message"}

             
{if $gui->showContextInfo}
    {if $gui->tproject_name != ''}
     <h2>{$labels.testproject} {$gui->tproject_name|escape} </h2>
    {/if}
	<h2>{$labels.req_spec} {$gui->item.name|escape} </h2>
{/if}
{assign var="warning_edit_msg" value=""}

<div>
	<form method="post" action="" name="reqSpecPrinterFriendly">
		<input type="button" name="printerFriendly" value="{$labels.btn_print_view}"
		       onclick="javascript:openPrintPreview('reqSpec',{$gui->item.parent_id},{$gui->item.id},-1,
		                                            'lib/requirements/reqSpecPrint.php');"/>
	</form>
</div>
<table class="simple">
	<tr>
    <th>{$gui->item.doc_id|escape}{$tlCfg->gui_title_separator_1}{$gui->item.name|escape}</th>
	</tr>
	  <tr>
	  	<td class="bold" colspan="2" id="tooltip-{$gui->item.id}">{$labels.revision} {$gui->item.revision}
	  		      			    <img src="{$tlImages.log_message_small}" style="border:none" /></a>

	  	</td>
	  </tr>

	{* to be enabled on 2.x
    <tr>
	 {assign var="dummy" value=$gui->item.status}
	  <td>{$labels.status}{$smarty.const.TITLE_SEP}{$gui->itemStatusDomain[$gui->item.status]}</td>
	</tr>
	*}
	<tr>
		{assign var="dummy" value=$gui->item.type}
	  <td>{$labels.type}{$smarty.const.TITLE_SEP}{$gui->itemTypeDomain[$dummy]}</td>
	</tr>
	<tr>
		<td>
			<fieldset class="x-fieldset x-form-label-left"><legend class="legend_container">{$labels.scope}</legend>
			{$gui->item.scope}
			</fieldset>
		</td>
	</tr>
	<tr>
			<td>&nbsp;</td>
	</tr>

	<tr class="time_stamp_creation">
  		<td >
      		{$labels.title_created}&nbsp;{localize_timestamp ts=$gui->item.creation_ts }&nbsp;
      		{$labels.by}&nbsp;{$gui->item.author|escape}
  		</td>
  </tr>
	{if $gui->item.modifier != ""}
  <tr class="time_stamp_creation">
  		<td >
    		{$labels.title_last_mod}&nbsp;{localize_timestamp ts=$gui->item.modification_ts}
		  	&nbsp;{$labels.by}&nbsp;{$gui->item.modifier|escape}
    	</td>
  </tr>
	{/if}
	<tr>
	</tr>
	<tr>
	</tr>
</table>

{if $gui->cfields != ''}
<div>
      <div id="cfields_design_time" class="custom_field_container">{$gui->cfields}</div>
</div>
{/if}
</div>
</body>
</html>
