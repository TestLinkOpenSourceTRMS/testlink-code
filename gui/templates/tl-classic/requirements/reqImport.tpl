{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource reqImport.tpl
*}

{lang_get var="labels" 
          s='note_keyword_filter,check_uncheck_all_checkboxes_for_add,
             th_id,th_test_case,version,scope,check_status,type,doc_id_short,
             btn_save_custom_fields,title_req_import,expected_coverage,
             check_req_file_structure,req_msg_norequirement,status,
             req_import_option_skip,req_import_option_overwrite,
             title_req_import_check_input,req_import_check_note,
             req_import_dont_empty,btn_import,btn_cancel,Result,
             req_doc_id,title,req_import_option_header,warning,
             check_uncheck_all_checkboxes,remove_tc,show_tcase_spec,
             check_uncheck_all_checkboxes_for_rm'}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}
</head>
<body>
<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
{if  $gui->doAction == 'askFileName' || $gui->file_check.status_ok eq 0}
  <form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}?req_spec_id={$gui->req_spec_id}">
  	<input type="hidden" name="scope" id="scope" value="{$gui->scope}" />
    {include file="inc_gui_import_file.tpl" args=$gui->importFileGui}
  </form>
{else}
  {if $gui->importResult != '' && $gui->file_check.status_ok }
  	<p class="info">{$gui->importResult}</p>
  	<table class="simple">
  	<tr>
  		<th>{$labels.doc_id_short}</th>
  		<th>{$labels.title}</th>
  		<th style="width: 20%;">{$labels.Result}</th>
  	</tr>
  	{if $gui->items != ''}
 	    {foreach from=$gui->items key=idx item=import_feedback}
  	  <tr>
  	    <td>{$import_feedback.doc_id|escape}</td>
  	    <td>{$import_feedback.title|escape}</td>
  	    <td>{$import_feedback.import_status|escape}</td>
  	  </tr>
  	  {/foreach}
  	{else}
  	  <tr><td>{$labels.req_msg_norequirement}</td></tr>
  	{/if}
  	</table>
  {/if}
{/if}

{if $gui->refreshTree}
	{include file="inc_refreshTree.tpl"}
{/if}

{if $gui->file_check.status_ok eq 0}
  <script type="text/javascript">
  alert_message("{$labels.warning|escape:'javascript'}","{$gui->file_check.msg|escape:'javascript'}");
  </script>
{/if}  


</div>

</body>
</html>