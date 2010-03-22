{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqViewVersions.tpl,v 1.15 2010/03/22 17:53:09 asimon83 Exp $
Purpose: view requirement with version management
         Based on work tcViewer.tpl

rev:
  20100319 - asimon - BUGID 1748, added requirement relations display
*}

{lang_get s='warning_delete_requirement' var="warning_msg" }
{lang_get s='warning_freeze_requirement' var="freeze_warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }
{lang_get s='freeze' var="freeze_msgbox_title" }

{lang_get s='delete_rel_msgbox_msg' var='delete_rel_msgbox_msg'}
{lang_get s='delete_rel_msgbox_title' var='delete_rel_msgbox_title'}
{lang_get s='warning_empty_reqdoc_id' var='warning_empty_reqdoc_id'}

{lang_get var='labels' 
          s='relation_id, relation_type, relation_document, relation_status, relation_project,
             relation_set_by, relation_delete, relations, new_relation, by, title_created,
             relation_destination_doc_id, in, btn_add, img_title_delete_relation, current_req,
             no_records_found,other_versions,version,title_test_case,match_count, warning'}


{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{config_load file="input_dimensions.conf"}

<script type="text/javascript">
{literal}
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
function delete_req(btn, text, o_id)
{ 
	var my_action=fRoot+'lib/requirements/reqEdit.php?doAction=doDelete&requirement_id=';
  if( btn == 'yes' )
  {
    my_action = my_action+o_id;
	  window.location=my_action;
	}
}					

function delete_req_version(btn, text, o_id)
{ 
	var my_action=fRoot+'lib/requirements/reqEdit.php?doAction=doDeleteVersion&req_version_id=';
  if( btn == 'yes' )
  {
    my_action = my_action+o_id;
	  window.location=my_action;
	}
}					

function freeze_req_version(btn, text, o_id)
{
	var my_action=fRoot+'lib/requirements/reqEdit.php?doAction=doFreezeVersion&req_version_id=';
	if( btn == 'yes' )
	{
		my_action = my_action+o_id;
		window.location=my_action;
	}
}

// BUGID 1748
{/literal}
var alert_box_title = "{$labels.warning}";
var delete_rel_msgbox_msg = '{$delete_rel_msgbox_msg}';
var delete_rel_msgbox_title = '{$delete_rel_msgbox_title}';
var warning_empty_reqdoc_id = '{$warning_empty_reqdoc_id}';
{literal}

function validate_req_docid_input(input_id, original_value) {

	var input = document.getElementById(input_id);

	if (isWhitespace(input.value) || input.value == original_value) {
    	alert_message(alert_box_title,warning_empty_reqdoc_id);
		return false;
	}

	return true;
}

function delete_req_relation(btn, text, req_id, relation_id) {
	var my_action=fRoot + 'lib/requirements/reqEdit.php?doAction=doDeleteRelation&requirement_id='
	                   + req_id + '&relation_id=' + relation_id;
	if( btn == 'yes' ) {
		window.location=my_action;
	}
}

function relation_delete_confirmation(requirement_id, relation_id, title, msg, pFunction) {
	var my_msg = msg.replace('%i',relation_id);
	var safe_title = title.escapeHTML();
	Ext.Msg.confirm(safe_title, my_msg,
	                function(btn, text) { 
	                	pFunction(btn,text,requirement_id, relation_id);
	                });
}


// VERY IMPORTANT:
// needed to make delete_confirmation() understand we are using a function.
// if I pass delete_req as argument javascript complains.
var pF_delete_req = delete_req;
var pF_delete_req_version = delete_req_version; 
var pF_freeze_req_version = freeze_req_version;
var pF_delete_req_relation = delete_req_relation;
{/literal}
</script>

<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{* need by refresh on upload logic used when this template is called while executing *}
{if $gui->bodyOnLoad != '' }
<script language="JavaScript">
  var {$gui->dialogName} = new std_dialog('&refreshTree');
</script>  
{/if}
</head>

{assign var="my_style" value=""}
{if $gui->hilite_item_name}
    {assign var="my_style" value="background:#059; color:white; margin:0px 0px 4px 0px;padding:3px;"}
{/if}

{assign var=this_template_dir value=$smarty.template|dirname}

<body onLoad="viewElement(document.getElementById('other_versions'),false);{$gui->bodyOnLoad}" onUnload="{$gui->bodyOnUnload}">
{* fixed a little bug, here $gui->pageTitle was called instead of $gui->main_descr *}
<h1 class="title">{$gui->main_descr|escape}{if $gui->show_match_count} - {$labels.match_count}: {$gui->match_count}{/if}
</h1>
{if !isset($refresh_tree) }
  {assign var="refresh_tree" value=false}
{/if}
{include file="inc_update.tpl" user_feedback=$user_feedback refresh=$refresh_tree}

<div class="workBack">

{if $gui->current_version}
{section name=idx loop=$gui->current_version}

	{assign var="reqID" value=$gui->current_version[idx][0].id}
    {* Current active version *}
    {if $gui->other_versions[idx] neq null}
        {assign var="my_delete_version" value=true}
    {else}
        {assign var="my_delete_version" value=false}
    {/if}
  
  	{* is it frozen? *}
    {if $gui->current_version[idx][0].is_open}
        {assign var="frozen_version" value=false}
    {else}
        {assign var="frozen_version" value=true}
    {/if}
  
    <h2 style="{$my_style}">
	  {$toggle_direct_link_img} &nbsp;
	  {if $gui->display_path}
	      {foreach from=$gui->path_info[$reqID] item=path_part}
	          {$path_part|escape} /
	      {/foreach}
	  {/if}
    {if !$gui->show_title }
	    {$gui->current_version[idx][0].req_doc_id|escape}:{$gui->current_version[idx][0].title|escape}</h2>
    {/if}
    <div class="direct_link" style='display:none'><a href="{$gui->direct_link}" target="_blank">{$gui->direct_link}</a></div>
    
		{include file="$this_template_dir/reqViewVersionsViewer.tpl" 
		         args_req_coverage=$gui->req_coverage
		         args_req=$gui->current_version[idx][0] 
		         args_gui=$gui
		         args_grants=$gui->grants 
		         args_can_copy=true
		         args_can_delete_req=true
		         args_can_delete_version=$my_delete_version
		         args_frozen_version=$frozen_version
		         args_show_version=true
		         args_show_title=$gui->show_title
		         args_cf=$gui->cfields[idx] 
		         args_tproject_name=$gui->tproject_name
		         args_reqspec_name=$gui->current_version[idx][0]['req_spec_title']}
		
		
		{assign var="downloadOnly" value=false}
		{if $gui->grants->req_mgmt != 'yes'}
			{assign var="downloadOnly" value=true}
		{/if}
		
		{if !isset($loadOnCancelURL)}
 	      {assign var="loadOnCancelURL" value=""}
    {/if} 
		         
	{* BUGID 1748 - req relations *}
	
	{if $gui->req_cfg->relations->enable} {* show this part only if relation feature is enabled *}
	
		{* form to enter a new relation *}
		<form method="post" action="lib/requirements/reqEdit.php" 
				onSubmit="javascript:return validate_req_docid_input('relation_destination_req_doc_id', 
				                                                     '{$labels.relation_destination_doc_id}');">
		
		<table class="simple" id="relations">
		
			<tr><th colspan="7">{$labels.relations}</th></tr>
		
			{if $gui->req_add_result_msg}
				<tr style="height:40px; vertical-align: middle;"><td style="height:40px; vertical-align: middle;" colspan="7">
					{$gui->req_add_result_msg}
				</td></tr>
			{/if}
		
			<tr style="height:40px; vertical-align: middle;"><td style="height:40px; vertical-align: middle;" colspan="7">
			
				<span class="bold">{$labels.new_relation}:</span> {$labels.current_req}
					
				<select name="relation_type">
				{html_options options=$gui->req_relation_select.items selected=$gui->req_relation_select.selected}
				</select>
		
				<input type="text" name="relation_destination_req_doc_id" id="relation_destination_req_doc_id"
						value="{$labels.relation_destination_doc_id}" 
				size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}" 
				onclick="javascript:this.value=''" />
			
				{* show input for testproject only if cross-project linking is enabled *}
				{if $gui->req_cfg->relations->interproject_linking}
						{$labels.relation_project} <select name="relation_destination_testproject_id">
						{html_options options=$gui->testproject_select.items selected=$gui->testproject_select.selected}
						</select>
				{/if}	
				
				<input type="hidden" name="doAction" value="doAddRelation" />
				<input type="hidden" name="relation_source_req_id" value="{$gui->req_id}" />
				<input type="submit" name="relation_submit_btn" value="{$labels.btn_add}" />
				
				</td></tr>
			
		{if $gui->req_relations.num_relations}
			
			<tr>
				<th>{$labels.relation_id}</th>
				<th>{$labels.relation_type}</th>
				
				{if $gui->req_cfg->relations->interproject_linking}
				  {assign var=colspan value=1}
				{else}
				  {assign var=colspan value=2}
				{/if}
				
				<th colspan="{$colspan}">{$labels.relation_document}</th>
				<th>{$labels.relation_status}</th>
				
				{if $gui->req_cfg->relations->interproject_linking}
					<th>{$labels.relation_project}</th>
				{/if}
				
				<th>{$labels.relation_set_by}</th>
				<th>{$labels.relation_delete}</th>
			</tr>
			
			{foreach item=relation from=$gui->req_relations.relations}
			{assign var=status value=$relation.related_req.status}
				<tr>
					<td>{$relation.id}</td>
					<td class="bold">{$relation.type_localized|escape}</td>
					<td colspan="{$colspan}"><a href="javascript:openLinkedReqWindow({$relation.related_req.id})">
						{$relation.related_req.req_doc_id|escape|truncate:#REQ_DOCID_SIZE#}:
						{$relation.related_req.title|escape|truncate:#REQ_DOCID_SIZE#}</a></td>
					<td>{$gui->reqStatus.$status|escape}</td>
					
					{* show related testproject name only if cross-project linking is enabled *}
					{if $gui->req_cfg->relations->interproject_linking}
						<td>{$relation.related_req.testproject_name|escape}</td>
					{/if}
					
					<td><span title="{$labels.title_created} {$relation.creation_ts} {$labels.by} {$relation.author|escape}">
						{$relation.author|escape}</span></td>

					<td align="center">
	             	<a href="javascript:relation_delete_confirmation({$gui->req_relations.req.id}, {$relation.id}, 
	             	                                                 delete_rel_msgbox_title, delete_rel_msgbox_msg, 
	             	                                                 pF_delete_req_relation);">
	      			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png" 
	      			    	   title="{$labels.img_title_delete_relation}"  style="border:none" /></a>
	              </td>
				</tr>
			{/foreach}
						
		{/if}
		
		</table>
		</form>
	
	{/if}
	
	{* end req relations *}
		     
	{include file="inc_attachments.tpl" 
		         attach_id=$reqID  
		         attach_tableName=$gui->attachmentTableName
		         attach_attachmentInfos=$gui->attachments[$reqID]  
		         attach_downloadOnly=$downloadOnly
		         attach_loadOnCancelURL=$loadOnCancelURL}
		         
	{* Other Versions *}
    {if $gui->other_versions[idx] neq null}
        {assign var="vid" value=$gui->current_version[idx][0].id}
        {assign var="div_id" value=vers_$vid}
        {assign var="memstatus_id" value=mem_$div_id}
  
        {include file="inc_show_hide_mgmt.tpl" 
                 show_hide_container_title=$labels.other_versions
                 show_hide_container_id=$div_id
                 show_hide_container_draw=false
                 show_hide_container_class='exec_additional_info'
                 show_hide_container_view_status_id=$memstatus_id}
               
        <div id="vers_{$vid}" class="workBack">
        
  	    {foreach from=$gui->other_versions[idx] item=my_req }
            {assign var="version_num" value=$my_req.version}
            {assign var="title" value="$labels.version}
            {assign var="title" value="$title $version_num"}
            
            {assign var="div_id" value=v_$vid}
            {assign var="sep" value="_"}
            {assign var="div_id" value=$div_id$sep$version_num}
            {assign var="memstatus_id" value=mem_$div_id}
           
           	{* is this version frozen? *}
    		{if $my_req.is_open}
        		{assign var="frozen_version" value=false}
    		{else}
        		{assign var="frozen_version" value=true}
    		{/if}
           
            {include file="inc_show_hide_mgmt.tpl" 
                     show_hide_container_title=$title
                     show_hide_container_id=$div_id
                     show_hide_container_draw=false
                     show_hide_container_class='exec_additional_info'
                     show_hide_container_view_status_id=$memstatus_id}
  	          <div id="{$div_id}" class="workBack">
           		
		          {include file="$this_template_dir/reqViewVersionsViewer.tpl" 
		                   args_req_coverage=$gui->req_coverage
		                   args_req=$my_req 
           		         args_gui=$gui
		                   args_grants=$gui->grants 
		                   args_can_copy=false
                       args_can_delete_req=false
                       args_can_delete_version=true
                       args_frozen_version=$frozen_version
                       args_show_version=false 
                       args_show_title=false
                       args_cf=$gui->cfields[idx]}
  	         </div>
  	         <br />
  	         
		    {/foreach}
		    </div>
  
      	{* ---------------------------------------------------------------- *}
      	{* Force the div of every old version to show closed as first state *}
      	{literal}
      	<script type="text/javascript">
      	{/literal}
 	  	      viewElement(document.getElementById('vers_{$vid}'),false);
    	  		{foreach from=$gui->other_versions[idx] item=my_req}
  	  	      viewElement(document.getElementById('v_{$vid}_{$my_req.version}'),false);
			      {/foreach}
      	{literal}
      	</script>
      	{/literal}
      	{* ---------------------------------------------------------------- *}
    {/if}
    <br>
{/section}
{else}
	{$labels.no_records_found}
{/if}

</div>
</body>
</html>
