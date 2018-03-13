{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource tcBulkOp.tpl

@internal revisions
@since 1.9.14

*}

{lang_get var="labels" 
          s='status,importance,execution_type,force_frozen_testcases_versions,btn_apply,btn_cancel'} 

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}
</head>

<body>
<h1 class="title">{$gui->page_title|escape}</h1>

<div class="workBack">
  <form method="post" id="asgard" action="{$basehref}lib/testcases/tcBulkOp.php">

<table>
  <tr>
    <td>
    <label for="status">{$labels.status}
    </td>
    <td>
    <select name="status" id="status" >
      {html_options options=$gui->domainTCStatus selected=$gui->uchoice.status}
    </select>
    </td>
  </tr>
  <tr>
    <td>
    <label for="importance">{$labels.importance}
    </td>
    <td>
      <select name="importance" id="importance">
        {html_options options=$gui->domainTCImportance selected=$gui->uchoice.importance}
      </select>
    </td>
  </tr>
  <tr>
    <td>
    <label for="execution_type">{$labels.execution_type}
    </td>
    <td>
      <select name="execution_type" id="execution_type">
        {html_options options=$gui->domainTCExecType selected=$gui->uchoice.execution_type}
      </select>
    </td>
  </tr>
  <tr>
	<td>{$labels.force_frozen_testcases_versions}</td>
	<td><input type="checkbox" name="forceFrozenTestcasesVersions" value="1" /></td>
  </tr>

</table>

  	<div class="groupBtn">
  		<input type="hidden" name="tcase_id" id="tcase_id" value="{$gui->tcase_id}" />
  		<input type="hidden" name="goback_url" id="goback_url" value="{$gui->goback_url}" />
      <input type="hidden" name="doAction" id="doAction" value="apply" />
      <input type="submit" name="nike" id="nike" value="{$labels.btn_apply}" />

  		<input type="button" name="cancel" value="{$labels.btn_cancel}"
    		     {if $gui->goback_url != ''}  onclick="location='{$gui->goback_url}'"
             {elseif $gui->cancelActionJS != ''} onclick="javascript:{$gui->cancelActionJS};"
    		     {else}  onclick="javascript:history.back();" {/if} />
  	</div>
  </form>
</div>

</body>
</html>