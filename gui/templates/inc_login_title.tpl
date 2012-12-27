{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_login_title.tpl,v 1.1 2008/06/03 08:40:49 havlat Exp $
Purpose: smarty template - login page title
*}
<div class="fullpage_head">
{* style="width: 462px; height: 112px;" style="width: 231px; height: 56px;" *}
<p><img alt="Company logo" title="logo" src="{$smarty.const.TL_THEME_IMG_DIR}{$tlCfg->logo_login}" />
	<br />TestLink {$tlVersion|escape}</p>
</div>