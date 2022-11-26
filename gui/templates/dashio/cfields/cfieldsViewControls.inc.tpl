{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfieldsViewControl.inc.tpl
*}
  <div class="page-content-{$suffix}">
    <form method="post" id="f{$suffix}" action="#">
      <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
      <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">

      <a class="{#BUTTON_CLASS#}" role="button" href="{$cfCreateAction}">{$labels.btn_cfields_create}</a>  

      <a class="{#BUTTON_CLASS#}" role="button" href="{$exportCfieldsAction}">{$labels.btn_export}</a>  

      <a class="{#BUTTON_CLASS#}" role="button" href="{$importCfieldsAction}">{$labels.btn_import}</a>  
    </form>
  </div>