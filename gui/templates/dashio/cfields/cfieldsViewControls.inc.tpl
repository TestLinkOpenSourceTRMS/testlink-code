{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfieldsViewControl.inc.tpl
*}
  <div class="page-content-{$suffix}">
    <form method="post" id="f{$suffix}" action="#">
      <a class="{#BUTTON_CLASS#}" role="button" href="{$cfCreateAction}">{$labels.btn_cfields_create}</a>  

      <a class="{#BUTTON_CLASS#}" role="button" href="{$exportCfieldsAction}">{$labels.btn_export}</a>  

      <a class="{#BUTTON_CLASS#}" role="button" href="{$importCfieldsAction}">{$labels.btn_import}</a>  
    </form>
  </div>