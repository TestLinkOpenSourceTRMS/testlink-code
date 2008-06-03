{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_login_title.tpl,v 1.1 2008/06/03 08:40:49 havlat Exp $
Purpose: smarty template - login page title
*}
<div class="fullpage_head">
<p><img alt="Company logo" title="logo" style="width: 115px; height: 53px;" 
	src="{$smarty.const.TL_THEME_IMG_DIR}{$tlCfg->company_logo}" />
	<br />TestLink {$tlVersion|escape}</p>
</div>