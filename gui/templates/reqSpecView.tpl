{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecView.tpl,v 1.23 2007/01/23 18:26:41 franciscom Exp $ *}
{* 
   Purpose: smarty template - view a requirement specification
   Author: Martin Havlat 

20070102 - franciscom - added javascript validation of checked requirements 
*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var warning_delete_requirements = "{lang_get s='warning_delete_requirements'}";
var please_select_a_req="{lang_get s='cant_delete_req_nothing_sel'}";
{literal}

/* 20070102 - franciscom */
function check_action_precondition(form_id,action)
{
 if( checkbox_count_checked(form_id) > 0) 
 {
    if( action=='delete')
    {
      return confirm(warning_delete_requirements);
    }
    if( action=='create')
    {
      return true;
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
	<img title="{lang_get s='help'}: {lang_get s='req_spec'}"
	     alt="{lang_get s='help'}: {lang_get s='req_spec'}" class="help" 
	     src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
	     onclick="javascript:open_popup('{$helphref}requirementsCoverage.html');" />
  {lang_get s='req_spec'}{$smarty.const.TITLE_SEP}{$arrSpec[0].title|escape}   
</h1>



<div class="workBack">
  {include file="inc_update.tpl" result=$sqlResult item=$sqlItem name=$name action=$action}

  {* ----------------------------------------------------------------------------------------- *}
  <div id="srs_container" style="width: {#SRS_CONTAINER_WIDTH#}">

    
    {* ----------------------------------------------------------------------------------------- *}
    <div class="workBack">
    <table class="common" style="width: 100%">
      <tr>
      	<th width="120px">{lang_get s='title'}</th>
      	<td>{$arrSpec[0].title|escape}</td>
      </tr>
      <tr>
      	<th>{lang_get s='scope'}</th>
      	<td>{$arrSpec[0].scope}</td>
      </tr>
      <tr>
      	<th>{lang_get s='req_total'}</th>
      	<td>{$arrSpec[0].total_req}</td>
      </tr>
      <tr class="time_stamp_creation">
        <td colspan="2">&nbsp; </td>
	    </tr>
      <tr class="time_stamp_creation">
        <td colspan="2">
        {lang_get s='title_created'}&nbsp;{localize_timestamp ts=$arrSpec[0].creation_ts}&nbsp;
        {lang_get s='by'}&nbsp;{$arrSpec[0].author|escape}     
        </td>
    	</tr>
      {if $arrSpec[0].modifier neq ""}
        <tr class="time_stamp_creation">
          <td colspan="2">
          {lang_get s='title_last_mod'}&nbsp;{localize_timestamp ts=$arrSpec[0].modification_ts}&nbsp;
          {lang_get s='by'}&nbsp;{$arrSpec[0].modifier|escape}     
          </td>
    	  </tr>
      {/if}
    </table>
    {* ----------------------------------------------------------------------------------------- *}

  {* ----------------------------------------------------------------------------------------- *}
  <div class="groupBtn">
    <form id="SRS" name="SRS" method="post">
    	<input type="hidden" name="idSRS" value="{$arrSpec[0].id}" />
    	{if $modify_req_rights == "yes"}
    	<input type="submit" name="editSRS" value="{lang_get s='btn_edit_spec'}" />
    	<input type="button" name="deleteSRS" value="{lang_get s='btn_delete_spec'}"
    		onclick="javascript:; 
    		if (confirm('{lang_get s="popup_sure_delete"}')){ldelim} 
    		location.href=fRoot+'lib/req/reqSpecList.php?deleteSRS=1&amp;idSRS={$arrSpec[0].id}';{rdelim};"/>
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
  <p>
  <p>
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
     <table class="simple">
    	 <tr>
    		{if $modify_req_rights == "yes"}<th style="width: 15px;"></th>{/if}
    		<th>{lang_get s="req_doc_id"}</th>
    		<th>{lang_get s="title"}</th>
    		<th>{lang_get s="scope"}</th>
    	 </tr>
    	{section name=row loop=$arrReq}
    	<tr>
    	  {* 20060110 - fm - managing checkboxes as array and added value *}
    		{if $modify_req_rights == "yes"}<td><input type="checkbox" name="req_id_cbox[{$arrReq[row].id}]" 
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
     {* ------------------------------------------------------------------------------------------ *}
    
     {* ------------------------------------------------------------------------------------------ *}
     {if $modify_req_rights == "yes"}
      <div class="groupBtn">
      	<input type="button" name="checkAll" value="{lang_get s='btn_check_all'}" 
      		onclick="javascript: box('frmReqList', true);" />
      	<input type="button" name="clearAll" value="{lang_get s='btn_uncheck_all'}" 
      		onclick="javascript: box('frmReqList', false);" />
      
       <input type="submit" name="create_tc_from_req" value="{lang_get s='req_select_create_tc'}" 
              onclick="return check_action_precondition('frmReqList','create');"/>
              
       <input type="submit" name="req_select_delete" value="{lang_get s='req_select_delete'}"
              onclick="return check_action_precondition('frmReqList','delete');"/>
      </div>
     {/if}
     {* ------------------------------------------------------------------------------------------ *}
    
  {/if}  
  {* ------------------------------------------------------------------------------------------ *}
  </div>
</form>
</div>


{if $js_msg neq ""}
<script type="text/javascript">
alert("{$js_msg}");
</script>
{/if}
</body>
</html>