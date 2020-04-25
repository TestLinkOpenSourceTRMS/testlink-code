{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource platformsViewControls.inc.tpl
*}
  <div class="page-content">
    <form method="post" id="f{$suffix}" action="#">
      <a class="{#BUTTON_CLASS#}" role="button" 
         href="{$createAction}">{{$labels.btn_create}}</a>  

      <a class="{#BUTTON_CLASS#}" role="button" 
         href="{$exportAction}">{$labels.btn_export}</a>  

      {if '' != $gui->canManage}      
        <a class="{#BUTTON_CLASS#}" role="button" 
           href="{$importAction}">{$labels.btn_import}</a>  
      {/if}   
    </form>
  </div>