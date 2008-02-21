{*
TestLink Open Source Project - http://testlink.sourceforge.net/
Id: reqAssign.tpl,v 1.6 2006/07/15 19:55:30 schlundus Exp $
Purpose: smarty template - assign REQ to one test case

20070617 - franciscom - manage checkboxes as arrays
                        added js logic to toogle/untoggle all

20070104 - franciscom -
1. added feedback message when there are not requirements
2. added control via javascrit on quantity of checked checkboxes

*}
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var please_select_a_req="{lang_get s='please_select_a_req'}";
{literal}

function check_action_precondition(form_id,action)
{
	if(checkbox_count_checked(form_id) <= 0)
	{
		alert(please_select_a_req);
		return false;
	}
	return true;
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
 {lang_get s='test_case'}{$smarty.const.TITLE_SEP}{$tcTitle|escape}
</h1>


<div class="workBack">
<h1>{lang_get s='req_title_assign'}</h1>

{include file="inc_update.tpl" result=$sqlResult action=$action item="requirement"}

{if $arrReqSpec eq "" }

   {lang_get s='warning_req_tc_assignment_impossible'}


{else}

  <form id="SRS_switch" name="SRS_switch" method="post">
    <p><span class="labelHolder">{lang_get s='req_spec'}</span>
  	<select name="idSRS" onchange="form.submit()">
  	{html_options options=$arrReqSpec selected=$selectedReqSpec}</select>
  </form>
  </div>

  <div class="workBack">
  <h2>{lang_get s='req_title_assigned'}</h2>
  {if $arrAssignedReq ne ""}
    <form id="reqList" method="post">
    <div id="div_assigned_req">
 	    {* used as memory for the check/uncheck all checkbox javascript logic *}
       <input type="hidden" name="memory_assigned_req"
                            id="memory_assigned_req"  value="0" />

    <input type="hidden" name="idSRS" value="{$selectedReqSpec}" />
    <table class="simple">
    	<tr>
      		<th align="center"  style="width: 5px;background-color:#005498;">
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
      		             onclick='cs_all_checkbox_in_div("div_assigned_req","assigned_req","memory_assigned_req");'
      		             title="{lang_get s='check_uncheck_all_checkboxes'}" />
      		</th>
    		<th>{lang_get s="req_doc_id"}</th>
    		<th>{lang_get s="req"}</th>
    		<th>{lang_get s="scope"}</th>
    	</tr>
    	{section name=row loop=$arrAssignedReq}
    	<tr>
    		<td><input type="checkbox" id="assigned_req{$arrAssignedReq[row].id}"
    		                           name="req_id[{$arrAssignedReq[row].id}]" /></td>
    		<td><span class="bold">{$arrAssignedReq[row].req_doc_id|escape}</span></td>
    		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrAssignedReq[row].id}&amp;idSRS={$selectedReqSpec}">
    			{$arrAssignedReq[row].title|escape}</a></span></td>
    		<td>{$arrAssignedReq[row].scope|strip_tags|strip|truncate:30}</td>
    	</tr>
    	{sectionelse}
    	<tr><td></td><td><span class="bold">{lang_get s='req_msg_norequirement'}</span></td></tr>
    	{/section}
    </table>
   	</div>

    {if $smarty.section.row.total > 0}
    	<div class="groupBtn">
    		<input type="submit" name="unassign" value="{lang_get s='btn_unassign'}"
    		       onclick="return check_action_precondition('reqList','unassign');"/>
    	</div>
    {/if}
  </form>
  {/if}

  </div>


    {if $arrUnassignedReq ne ""}
      <div class="workBack">
      <h2>{lang_get s='req_title_unassigned'}</h2>
      <form id="reqList2" method="post">

       <div id="div_free_req">
 	     {* used as memory for the check/uncheck all checkbox javascript logic *}
       <input type="hidden" name="memory_free_req"
                            id="memory_free_req"  value="0" />

      <input type="hidden" name="idSRS" value="{$selectedReqSpec}" />
      <table class="simple">
      	<tr>
      		<th align="center"  style="width: 5px;background-color:#005498;">
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
      		             onclick='cs_all_checkbox_in_div("div_free_req",
      		                                             "free_req","memory_free_req");'
      		             title="{lang_get s='check_uncheck_all_checkboxes'}" />
      		</th>
      		<th>{lang_get s="req_doc_id"}</th>
      		<th>{lang_get s="req"}</th>
      		<th>{lang_get s="scope"}</th>
      	</tr>
      	{section name=row2 loop=$arrUnassignedReq}
      	<tr>
      		<td><input type="checkbox"
      		           id="free_req{$arrUnassignedReq[row2].id}"
      		           name="req_id[{$arrUnassignedReq[row2].id}]" /></td>

      		<td><span class="bold">{$arrUnassignedReq[row2].req_doc_id|escape}</span></td>
      		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrUnassignedReq[row2].id}&amp;idSRS={$selectedReqSpec}">
      			{$arrUnassignedReq[row2].title|escape}</a></span></td>
      		<td>{$arrUnassignedReq[row2].scope|strip_tags|strip|truncate:30}</td>
      	</tr>
      	{sectionelse}
      	<tr><td></td><td><span class="bold">{lang_get s='req_msg_norequirement66'}</span></td></tr>
      	{/section}
      </table>
	  </div>
      <div class="groupBtn">
      	<input type="submit" name="assign" value="{lang_get s='btn_assign'}"
     		       onclick="return check_action_precondition('reqList2','assign');"/>
      </div>
      </form>
      </div>
    {/if}
{/if}

</body>
</html>
