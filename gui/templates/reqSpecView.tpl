{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecView.tpl,v 1.31 2007/11/09 21:42:52 franciscom Exp $ *}
{* 
   Purpose: smarty template - view a requirement specification
   Author: Martin Havlat 

   rev: 20071106 - franciscom - added ext js library
        20070102 - franciscom - added javascript validation of checked requirements 
*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}
{lang_get s='warning_delete_requirements' var="warning_msg" }

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var o_label ="{lang_get s='requirement_spec'}";
var del_action=fRoot+'lib/req/reqSpecList.php?deleteSRS=1&idSRS=';
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


{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1>
 {lang_get s='help' var='common_prefix'}
 {lang_get s='req_spec' var="xx_alt"}
 {assign var="text_hint" value="$common_prefix: $xx_alt"}
 {include file="inc_help.tpl" help="requirementsCoverage" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
 {lang_get s='req_spec'}{$smarty.const.TITLE_SEP}{$arrSpec[0].title|escape}   
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
      	<td>{$arrSpec[0].title|escape}</td>
      </tr>
      <tr>
      	<th>{lang_get s='scope'}</th>
      	<td>{$arrSpec[0].scope}</td>
      </tr>
      {if $arrSpec[0].total_req neq "0"}
      <tr>
      	<th>{lang_get s='req_total'}</th>
      	<td>{$arrSpec[0].total_req}</td>
      </tr>
      {/if}

      {if $cf!=''}
        {$cf}
      {/if}

    </table>
    <div class="time_stamp_creation">
        {lang_get s='title_created'}&nbsp;{localize_timestamp ts=$arrSpec[0].creation_ts}&nbsp;
        {lang_get s='by'}&nbsp;{$arrSpec[0].author|escape}
      {if $arrSpec[0].modifier neq ""}
		<br />     
          {lang_get s='title_last_mod'}&nbsp;{localize_timestamp ts=$arrSpec[0].modification_ts}&nbsp;
          {lang_get s='by'}&nbsp;{$arrSpec[0].modifier|escape}     
      {/if}
    </div>
	<br />
    {* ----------------------------------------------------------------------------------------- *}

  {* ----------------------------------------------------------------------------------------- *}
  <div class="groupBtn">
    <form id="SRS" name="SRS" method="post">
    	<input type="hidden" name="idSRS" value="{$arrSpec[0].id}" />
    	{if $modify_req_rights == "yes"}
    	<input type="submit" name="editSRS" value="{lang_get s='btn_edit_spec'}" />
    	<input type="button" name="deleteSRS" value="{lang_get s='btn_delete_spec'}"
    	       onclick="delete_confirmation({$arrSpec[0].id},
 					                                 '{$arrSpec[0].title|escape:'javascript'}',
 					                                 '{$warning_msg}');"	/>
    	{/if}
    	<input type="button" name="printSRS" value="{lang_get s='btn_print'}"
    		onclick="javascript: window.open('{$basehref}lib/req/reqSpecPrint.php?idSRS={$arrSpec[0].id}', 
    		        '_blank','left=100,top=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=600,height=650');" />
    	<input type="button" name="analyse" value="{lang_get s='btn_analyse'}"
    		onclick="javascript: location.href=fRoot+'lib/req/reqSpecAnalyse.php?idSRS={$arrSpec[0].id}';" />
    	<input type="button" name="backToSRSList" value="{lang_get s='btn_spec_list'}" 
    		onclick="javascript: location.href=fRoot+'lib/req/reqSpecList.php';" />
    </form>
  </div>
  </div>
  <br />
  <br />
  {* ----------------------------------------------------------------------------------------- *}




  
  {* existing REQs *}	
  <div class="workBack">
  <h2>{lang_get s="req_title_list"}</h2>
  
  <form id="frmReqList" enctype="multipart/form-data" method="post">
  
    {if $modify_req_rights == "yes"}
  	  <input type="submit" name="createReq" value="{lang_get s='btn_req_create'}" />
  	  <input type="button" name="importReq" value="{lang_get s='btn_import'}"
  		       onclick="javascript: location.href=fRoot+'lib/req/reqImport.php?idSRS={$arrSpec[0].id}';" />
      &nbsp;
    {/if}	
  
  {* ------------------------------------------------------------------------------------------ *}
  {if $arrReq ne ''}  {* There are requirements in the SRS *}
    	<input type="submit" name="exportAll" value="{lang_get s='btn_export_reqs'}"> 
    	<select name="exportType">
    		{html_options options=$exportTypes}
    	</select>
    
     {* ------------------------------------------------------------------------------------------ *} 
     <div id="req_div"  style="margin:0px 0px 0px 0px;">
        {* used as memory for the check/uncheck all checkbox javascript logic *}
        <input type="hidden" name="toogle_req"  id="toogle_req"  value="0" />
     

     <table class="simple">
    	 <tr>
    		{if $modify_req_rights == "yes"}
    		<th style="width: 15px;">
    						    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" 
                         onclick='cs_all_checkbox_in_div("req_div","req_id_cbox","toogle_req");'
                 title="{lang_get s='check_uncheck_all_checkboxes'}" /></th>
        {/if}
    		
    		<th>{lang_get s="req_doc_id"}</th>
    		<th>{lang_get s="title"}</th>
    		<th>{lang_get s="scope"}</th>
    	 </tr>
    	{section name=row loop=$arrReq}


    	<tr>
    	  {* 20060110 - fm - managing checkboxes as array and added value *}
    		{if $modify_req_rights == "yes"}
    		<td><input type="checkbox" id="req_id_cbox{$arrReq[row].id}"
    		           name="req_id_cbox[{$arrReq[row].id}]" 
    		                                           value="{$arrReq[row].id}"/></td>{/if}
    		<td><span class="bold">{$arrReq[row].req_doc_id|escape}</span></td>
    		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrReq[row].id}&amp;idSRS={$arrSpec[0].id}">
    		{$arrReq[row].title|escape}</a></span></td>
    		<td>{$arrReq[row].scope|strip_tags|strip|truncate:100}</td>
    	</tr>
    	{sectionelse}
    	<tr><td></td><td><span class="bold">{lang_get s='req_msg_norequirement'}</span></td></tr>
    	{/section}
     </table>
     </div>
     {* ------------------------------------------------------------------------------------------ *}
    
     {* ------------------------------------------------------------------------------------------ *}
     {if $modify_req_rights == "yes"}
      <div class="groupBtn">
       <input type="submit" name="create_tc_from_req" value="{lang_get s='req_select_create_tc'}" 
              onclick="return check_action_precondition('frmReqList','create');"/>
              
       <input type="submit" name="req_select_delete" value="{lang_get s='req_select_delete'}"
              onclick="return check_action_precondition('frmReqList','delete');"/>
              
       <input type="submit" name="req_reorder" value="{lang_get s='req_reorder'}">

              
      </div>
     {/if}
     {* ------------------------------------------------------------------------------------------ *}
    
  {/if}  
  {* ------------------------------------------------------------------------------------------ *}
</form>
  </div>
</div>
</div>

{if $js_msg neq ""}
<script type="text/javascript">
alert("{$js_msg}");
</script>
{/if}
</body>
</html>
