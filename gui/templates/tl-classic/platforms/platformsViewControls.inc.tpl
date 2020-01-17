{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfieldsViewControl.inc.tpl
*}
  <div class="page-content">
    <form style="float:left" name="platform_view" id="platform_view" 
          method="post" action="lib/platforms/platformsEdit.php">
      <input type="hidden" name="doAction" value="" />
      <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
      {if '' != $gui->canManage}
        <input class="btn btn-primary" role="button"
          type="submit" 
          id="create_platform" name="create_platform" value="{$labels.btn_create}" 
          onclick="doAction.value='create'"/>
      {/if} 
    </form>
    <form name="platformsExport" id="platformsExport" method="post" 
      action="lib/platforms/platformsExport.php">

      <input type="hidden" name="goback_url" value="{$basehref|escape}{$viewAction|escape}"/>

      <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />

      <input type="submit" name="export_platforms" id="export_platforms"
        class="btn btn-primary" role="button"
        style="margin-left: 3px;" value="{$labels.btn_export}" />

      {if '' != $gui->canManage}      
          <input class="btn btn-primary" role="button" type="button" 
            name="import_platforms" id="import_platforms" 
            onclick="location='{$importAction}'" value="{$labels.btn_import}" />
      {/if}
    </form>
    </div>