{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqViewVersions.tpl,v 1.10 2009/12/31 10:24:38 franciscom Exp $
Purpose: view requirement with version management
         Based on work tcViewer.tpl

rev:
*}

{lang_get s='warning_delete_requirement' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{include file="inc_head.tpl" openHead='yes'}
{include file="inc_del_onclick.tpl"}


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

// VERY IMPORTANT:
// needed to make delete_confirmation() understand we are using a function.
// if I pass delete_req as argument javascript complains.
var pF_delete_req = delete_req;
var pF_delete_req_version = delete_req_version; 
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
{lang_get var='labels' 
          s='no_records_found,other_versions,version,title_test_case,match_count'}

<body onLoad="viewElement(document.getElementById('other_versions'),false);{$gui->bodyOnLoad}" onUnload="{$gui->bodyOnUnload}">
<h1 class="title">{$gui->pageTitle}{if $gui->show_match_count} - {$labels.match_count}:{$gui->match_count}{/if}
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
