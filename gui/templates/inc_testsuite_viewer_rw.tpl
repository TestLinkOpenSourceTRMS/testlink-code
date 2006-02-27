{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_testsuite_viewer_rw.tpl,v 1.1 2006/02/27 07:45:14 franciscom Exp $ 
*}
		<p>{lang_get s='comp_name'}<br />
			<input type="text" name="name" alt="{lang_get s='comp_alt_name'}"
			value="{$name|escape}" size="50" /></p>
		<div style="margin: 3px;">{lang_get s='details'}<br />
		{$details}
		</div>
