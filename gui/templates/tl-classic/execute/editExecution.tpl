{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource editExecution.tpl
@author   francisco.mancardi@gmail.com


*}
{include file="inc_head.tpl" openHead='yes' editorType=$gui->editorType}
{include file="inc_ext_js.tpl"}
</head>

<body onUnload="storeWindowSize('ExecEditPopup')">
<h1 class="title">{lang_get s='title_execution_notes'}</h1>
<div class="workBack">
  <form method="post">
    {* memory *}
    <input type="hidden" name="tplan_id" value="{$gui->tplan_id}">
    <input type="hidden" name="tproject_id" value="{$gui->tproject_id}">
    <input type="hidden" name="exec_id" value="{$gui->exec_id}">
    <input type="hidden" name="tcversion_id" value="{$gui->tcversion_id}">
    
    <table width="100%">
    <tr>
      <td>
        {$gui->notes}
      </td>
    </tr> 
      {if $gui->cfields_exec neq ''}
      <tr>
          <td colspan="2">
            <div id="cfields_exec" class="custom_field_container" 
              style="background-color:#dddddd;">{$gui->cfields_exec}
            </div>
          </td>
      </tr>
      {/if}
    
    </table>
    <div class="groupBtn">
      <input type="hidden" name="doAction" value="doUpdate" />
      <input type="submit" value="{lang_get s='btn_save'}" />
      <input type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
    </div>
  </form>
</div>
</body>
</html>