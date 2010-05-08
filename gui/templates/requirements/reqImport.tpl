{* ----------------------------------------------------------------- *
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqImport.tpl,v 1.16 2010/05/08 15:39:40 franciscom Exp $
Purpose: smarty template - requirements import initial page
Author: Martin Havlat

Revision:
20050830 - MHT - result presentation updated
20051015 - scs - fixed back button
20051202 - scs - fixed 211
20061014 - franciscom - added alert due to:
                        no text file ($ftype_ok)
                        bad syntax ($fsyntax_ok)
* ----------------------------------------------------------------- *}
{lang_get var="labels" 
          s='note_keyword_filter,check_uncheck_all_checkboxes_for_add,
             th_id,th_test_case,version,scope,check_status,type,
             btn_save_custom_fields,title_req_import,
             check_req_file_structure,req_msg_norequirement,
             req_import_option_skip,req_import_option_overwrite,
             title_req_import_check_input,req_import_check_note,
             req_import_dont_empty,btn_import,btn_cancel,Result,
             req_doc_id,title,req_import_option_header,
             check_uncheck_all_checkboxes,remove_tc,show_tcase_spec,
             check_uncheck_all_checkboxes_for_rm'}

{assign var="bn" value=$smarty.template|basename}
{assign var="viewer_template" value=$smarty.template|replace:"$bn":"inc_req_import_viewer.tpl"}
{assign var="req_module" value='lib/requirements/'}
{assign var="url_args" value="reqSpecView.php?req_spec_id="}
{assign var="req_spec_view_url" value="$basehref$req_module$url_args$gui->req_spec_id"}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}
</head>

<body>
<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">

  {if  $gui->doAction == 'askFileName'}
    <form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}?req_spec_id={$gui->req_spec_id}">
	  	<input type="hidden" name="scope" id="scope" value="{$gui->scope}" />
      {include file="inc_gui_import_file.tpl" args=$gui->importFileGui}
    </form>
    
    {if $gui->file_check.status_ok eq 0}
      <script>
      alert("{$gui->file_check.msg}");
      </script>
    {elseif $gui->try_upload  && ($gui->arrImport == "") }
      <script>
      alert("{$labels.check_req_file_structure}");
      </script>
    {/if}
  
  {elseif $gui->doAction == 'uploadFile'}

    {if !is_null($gui->items)}
      {if $gui->importType == 'XML'}
  	    <form method='post' action='{$SCRIPT_NAME}?req_spec_id={$gui->req_spec_id}'>
 		    <input type='hidden' value="{$gui->importType}" name='importType' />
		    <input type="hidden" name="scope" id="scope" value="{$gui->scope}" />
        {include file="$viewer_template" }
  	    	<div class="groupBtn">
  	    		<input type='submit' name='executeImport' value="{$labels.btn_import}" />
  	    		<input type="button" name="cancel" value="{$labels.btn_cancel}"
  	    			onclick="javascript: location.href='{$req_spec_view_url}';" />
  	    	</div>
  	    </form>
  	  {/if}
  	  
      {if $gui->importType != 'XML'}
        {* NEED TO BE DEVELOPED *}  	
  	    <h2>{$labels.title_req_import_check_input}</h2>
  	    <p>{$labels.req_import_check_note}</p>
  	    <div>
  	    <form method='post' action='{$SCRIPT_NAME}?req_spec_id={$gui->req_spec_id}'>
  	    	<p>{$labels.req_import_option_header}
  	    	<select name="conflicts">
  	    		<option value ="skip">{$labels.req_import_option_skip}</option>
  	    		<option value ="overwrite" selected="selected">{$labels.req_import_option_overwrite}</option>
  	    	</select></p>
        
  	    	<p><input type="checkbox" name="noEmpty" checked="checked" />{$labels.req_import_dont_empty}</p>
        
  	    	<input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
  	    	<input type='hidden' value='{$gui->fileName}' name='uploadedFile' />
  	    	<input type='hidden' value='{$gui->importType}' name='importType' />
        
  	    	<div class="groupBtn">
  	    		<input type='submit' name='executeImport' value="{$labels.btn_import}" />
  	    		<input type="button" name="cancel" value="{$labels.btn_cancel}"
  	    			onclick="javascript: location.href='{$req_spec_view_url}';" />
  	    	</div>
  	    </form>
  	    <div>
  	    <table class="simple">
  	    	<tr>
  	    		<th>{$labels.req_doc_id}</th>
  	    		<th>{$labels.title}</th>
  	    		<th>{$labels.scope}</th>
  	    		<th>{$labels.type}</th>
  	    		<th>{$labels.check_status}</th>
  	    	</tr>
  	      {if $gui->items != ''}
 	          {foreach from=$gui->items key=idx item=import_feedback}
  	    	  <tr>
  	    	  	<td>{$import_feedback.req_doc_id|escape}</td>
  	    	  	<td>{$import_feedback.title|escape}</td>
  	    	  	<td>{$import_feedback.scope|strip_tags|strip|truncate:#SCOPE_TRUNCATE#}</td>
  	    	  	<td>{$import_feedback.type}</td>
  	    	  	<td>{$import_feedback.check_status|escape}</td>
  	    	  </tr>
  	    	  {/foreach}
  	      {else}
  	        <tr><td>{$labels.req_msg_norequirement}</td></tr>
  	    	{/if}  
  	    </table>
  	    </div>


  	    </div>
  	  {/if}
  	{/if}

  {/if}
  
  {if $gui->importResult != '' && $gui->file_check.status_ok }
  	<p class="info">{$importResult}</p>

  	<table class="simple">
  	<tr>
  		<th>{$labels.req_doc_id}</th>
  		<th>{$labels.title}</th>
  		<th style="width: 20%;">{$labels.Result}</th>
  	</tr>
  	{if $gui->items != ''}
 	    {foreach from=$gui->items key=idx item=import_feedback}
  	  <tr>
  	    <td>{$import_feedback.req_doc_id|escape}</td>
  	    <td>{$import_feedback.title|escape}</td>
  	    <td>{$import_feedback.import_status|escape}</td>
  	  </tr>
  	  {/foreach}
  	{else}
  	  <tr><td>{$labels.req_msg_norequirement}</td></tr>
  	{/if}
  	</table>
 {/if}

</div>

</body>
</html>