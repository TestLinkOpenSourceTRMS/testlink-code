{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecView.tpl,v 1.3 2007/11/22 07:33:04 franciscom Exp $ *}
{* 
   Purpose: smarty template - view a requirement specification
   Author: Martin Havlat 

   rev: 20071106 - franciscom - added ext js library
        20070102 - franciscom - added javascript validation of checked requirements 
*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}
{lang_get s='warning_delete_requirements' var="warning_msg" }

{assign var="req_module" value=$smarty.const.REQ_MODULE}
{assign var="url_args" value="reqEdit.php?do_action=create&req_spec_id="}
{assign var="req_edit_url" value="$basehref$req_module$url_args$req_spec_id"}

{assign var="url_args" value="reqImport.php?req_spec_id="}
{assign var="req_import_url"  value="$basehref$req_module$url_args$req_spec_id"}

{assign var="url_args" value="reqExport.php?req_spec_id="}
{assign var="req_export_url"  value="$basehref$req_module$url_args$req_spec_id"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var o_label ="{lang_get s='requirement_spec'}";
var del_action=fRoot+'{$smarty.const.REQ_MODULE}reqSpecEdit.php?do_action=do_delete&req_spec_id=';
</script>



{literal}
<script type="text/javascript">
{/literal}
var warning_delete_requirements = "{lang_get s='warning_delete_requirements'}";
var please_select_a_req="{lang_get s='cant_delete_req_nothing_sel'}";
{literal}


/*
  function: check_action_precondition

  args :
  
  returns: 

*/
function check_action_precondition(form_id,action)
{
 if( checkbox_count_checked(form_id) > 0) 
 {
    switch(action)
    {
      case 'delete':
      return confirm(warning_delete_requirements);
      break;
    
      case 'create':
      return true;
      break;
      
      default:
      return true;
      break
    
    }
 }
 else
 {
    confirm(please_select_a_req);
    return false; 
 }  
}
</script>
{/literal}
</head>

<body>
<h1>
 {lang_get s='help' var='common_prefix'}
 {lang_get s='req_spec' var="xx_alt"}
 {assign var="text_hint" value="$common_prefix: $xx_alt"}
 {include file="inc_help.tpl" help="requirementsCoverage" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
 {lang_get s='req_spec'}{$smarty.const.TITLE_SEP}{$req_spec.title|escape}   
</h1>



<div class="workBack">
  {include file="inc_update.tpl" result=$sqlResult item=$sqlItem name=$name action=$action}

  {* ----------------------------------------------------------------------------------------- *}
  <div id="srs_container" style="width: {#SRS_CONTAINER_WIDTH#}">

    
    {* ----------------------------------------------------------------------------------------- *}
    <div class="workBack">
    <table class="common" style="width:100%">
      <tr>
      	<th style="width:15%">{lang_get s='title'}</th>
      	<td>XXXXX {$req_spec.title|escape}</td>
      </tr>
      <tr>
      	<th>{lang_get s='scope'}</th>
      	<td>{$req_spec.scope}</td>
      </tr>
      {if $req_spec.total_req neq "0"}
      <tr>
      	<th>{lang_get s='req_total'}</th>
      	<td>{$req_spec.total_req}</td>
      </tr>
      {/if}

      {if $cf!=''}
        {$cf}
      {/if}

    </table>
    <div class="time_stamp_creation">
        {lang_get s='title_created'}&nbsp;{localize_timestamp ts=$req_spec.creation_ts}&nbsp;
        {lang_get s='by'}&nbsp;{$req_spec.author|escape}
      {if $req_spec.modifier neq ""}
		<br />     
          {lang_get s='title_last_mod'}&nbsp;{localize_timestamp ts=$req_spec.modification_ts}&nbsp;
          {lang_get s='by'}&nbsp;{$req_spec.modifier|escape}     
      {/if}
    </div>
	<br />
    {* ----------------------------------------------------------------------------------------- *}

  {* ----------------------------------------------------------------------------------------- *}
  <div class="groupBtn">
    <form id="req_spec" name="req_spec" action="{$smarty.const.REQ_MODULE}reqSpecEdit.php" method="post">
    	<input type="hidden" name="req_spec_id" value="{$req_spec_id}" />
    	<input type="hidden" name="do_action" value="" />
    	
    	{if $modify_req_rights == "yes"}
    	<input type="submit" name="edit_req_spec" 
    	       value="{lang_get s='btn_edit_spec'}" 
    	       onclick="do_action.value='edit'"/>
    	
    	
    	<input type="button" name="deleteSRS" value="{lang_get s='btn_delete_spec'}"
    	       onclick="delete_confirmation({$req_spec.id},
 					                                 '{$req_spec.title|escape:'javascript'}',
 					                                 '{$warning_msg}');"	/>


    	<input type="button" name="print_req_spec" value="{lang_get s='btn_print'}"
    		onclick="javascript: window.open('{$basehref}lib/req/reqSpecPrint.php?req_spec_id={$req_spec.id}', 
    		        '_blank','left=100,top=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=600,height=650');" />
    	<input type="button" name="analyse" value="{lang_get s='btn_analyse'}"
    		onclick="javascript: location.href=fRoot+{$smarty.const.REQ_MODULE}'reqSpecAnalyse.php?req_spec_id={$req_spec.id}';" />
    	{/if}

    	
    	{if $modify_req_rights == "yes"}
    	<br />
    	&nbsp;
    	<br />
    	<input type="button" name="create_req" 
    	       value="{lang_get s='btn_req_create'}"
 			       onclick="location='{$req_edit_url}'" />  
    	
    	
  	  <input type="button" name="importReq" value="{lang_get s='btn_import'}"
  		       onclick="location='{$req_import_url}'" />

    	<input type="button" name="exportReq" value="{lang_get s='btn_export_reqs'}"
  		       onclick="location='{$req_export_url}'" />

    	{/if}
    	
    		
    		
    </form>
  </div>
  </div>
  <br />
  <br />
  {* ----------------------------------------------------------------------------------------- *}

 
</div>
</div>

{if $js_msg neq ""}
<script type="text/javascript">
alert("{$js_msg}");
</script>
{/if}
</body>
</html>
