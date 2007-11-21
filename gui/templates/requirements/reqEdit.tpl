{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqEdit.tpl,v 1.2 2007/11/21 13:10:19 franciscom Exp $
Purpose: smarty template - create / edit a req  
*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{lang_get s='warning'}";
var warning_empty_req_docid = "{lang_get s='warning_empty_reqdoc_id'}";
var warning_empty_req_title = "{lang_get s='warning_empty_req_title'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.reqDocId.value)) 
  {
      alert_message(alert_box_title,warning_empty_req_docid);
      selectField(f, 'reqDocId');
      return false;
  }
  
  if (isWhitespace(f.req_title.value)) 
  {
      alert_message(alert_box_title,warning_empty_req_title);
      selectField(f, 'req_title');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>


<body onload="document.forms[0].elements[0].focus()">
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

  <p>
  <div class="labelHolder"> <label for="reqDocId">{lang_get s='req_doc_id'}</label></div> 
	<div><input type="text" name="reqDocId" 
  		        size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}" 
  		        value="{$req.req_doc_id}" />
  				{include file="error_icon.tpl" field="reqDocId"}
  </div>
  </p>
  <p>
  <div class="labelHolder"> <label for="req_title">{lang_get s='title'}</label></div>
  <div><input type="text" name="req_title" 
  		        size="{#REQ_TITLE_SIZE#}" maxlength="{#REQ_TITLE_MAXLEN#}" 
  		        value="{$req.title}" />
  		    {include file="error_icon.tpl" field="req_title"}
  </div>
  </p>
  <p>
  <div class="labelHolder"> <label for="scope">{lang_get s='scope'}</label></div>
	<div>{$scope}</div>
  </p>
  <p>
  <div class="labelHolder"> <label for="reqStatus">{lang_get s='status'}</label></div>
  <div><select name="reqStatus">
  			{html_options options=$selectReqStatus selected=$req.status}
  		</select>
  </div>
  </p>
  {if $cf != ''}
  <p>	
  {$cf}
  </p>
  {/if}
  
<p>
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