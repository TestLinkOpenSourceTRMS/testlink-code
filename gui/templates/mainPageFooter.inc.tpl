{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 @filesource  mainPage.tpl 
 Purpose: smarty template - main page footer               
*}

<link rel="stylesheet" href="{$basehref}{$smarty.const.TL_THEME_CSS_DIR}/footer.css">

<div class="footerFoo">	
<address>
{lang_get var="lbl_f" s="poweredBy,system_descr"}

<strong><h6>{$lbl_f.poweredBy|escape} <a href="{$tlCfg->testlinkdotorg}" title="{$lbl_f.system_descr|escape}">TestLink {$tlVersion|escape}</a></h6></strong> <br>
</address>
</div>
