{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqEdit.tpl,v 1.1 2007/11/19 21:01:05 franciscom Exp $
Purpose: smarty template - create / edit a req  
*}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{literal}
<script type="text/javascript">
{/literal}
var warning_empty_req_docid = "{lang_get s='warning_empty_reqdoc_id'}";
var warning_empty_req_title = "{lang_get s='warning_empty_req_title'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.reqDocId.value)) 
  {
      alert(warning_empty_req_docid);
      selectField(f, 'reqDocId');
      return false;
  }
  
  if (isWhitespace(f.title.value)) 
  {
      alert(warning_empty_req_title);
      selectField(f, 'title');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>


<body onload="document.forms[0].elements[0].focus()">
{config_load file="input_dimensions.conf" section="reqEdit"}

<h1>
 {lang_get s='help' var='common_prefix'}
 {lang_get s='reqs' var="xx_alt"}
 {assign var="text_hint" value="$common_prefix: $xx_alt"}
 
 {include file="inc_help.tpl" help="requirementsCoverage" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}

	{lang_get s='req_spec'}{$smarty.const.TITLE_SEP}{$srs_title|escape} 
</h1>



<div class="workBack">
{include file="inc_update.tpl" result=$sqlResult item="Requirement" name=$name action=$action}
{if $page_descr != ''}
<h1>{$page_descr}</h1>
{/if}

<form name="reqEdit" id="reqEdit" method="post" onSubmit="javascript:return validateForm(this);">
  <input type="hidden" name="req_spec_id" value="{$req_spec_id}">
  <input type="hidden" name="requirement_id" value="{$req_id}">

  <table class="common" style="width: 90%">
  	<tr>
  		<th>{lang_get s='req_doc_id'}</th>
  		<td><input type="text" name="reqDocId" 
  		           size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}" 
  		           value="{$req.req_doc_id}" />
  				{include file="error_icon.tpl" field="reqDocId"}
  		</td>
  	</tr>
  	<tr>
  		<th>{lang_get s='title'}</th>
  		<td><input type="text" name="title" 
  		           size="{#REQ_TITLE_SIZE#}" maxlength="{#REQ_TITLE_MAXLEN#}" 
  		           value="{$req.title}" />
  		    {include file="error_icon.tpl" field="title"}
  		</td>
  	</tr>
  	<tr>
  		<th>{lang_get s='scope'}</th>
  		<td>{$scope}</td>
  	</tr>
  	<tr>
  		<th>{lang_get s='status'}</th>
  		<td><select name="reqStatus">
  			{html_options options=$selectReqStatus selected=$req.status}
  		</select></td>
  	</tr>
  	
  	{if $cf != ''}
  	  <tr class="time_stamp_creation">
       <td colspan="2">&nbsp; </td>
    	</tr>

  	<tr>
  	  <td colspan="2">
  	  {$cf}
  	  </td>
  	</tr>
  	{/if}
  	
  </table>
  
<div class="groupBtn">
<input type="hidden" name="do_action" value="">

<input type="submit" name="create_req" value="{$submit_button_label}" 
	       onclick="do_action.value='{$submit_button_action}'"/>
</div>
</form>

</div>

</div>

</body>
</html>