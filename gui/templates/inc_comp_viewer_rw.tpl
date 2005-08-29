{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_comp_viewer_rw.tpl,v 1.1 2005/08/29 06:45:54 franciscom Exp $ *}
{* *}

		<p>{lang_get s='comp_name'}<br />
			<input type="text" name="name" alt="{lang_get s='comp_alt_name'}"
			value="{$name|escape}" size="50" /></p>
		<p>{lang_get s='comp_intro'}<br />
			{$intro}
		</p>
		
		<div style="margin: 3px;">{lang_get s='comp_scope'}<br />
		{$scope}
		</div>
		<p>{lang_get s='comp_ref'}<br />
			{$ref}
		</p>
		<p>{lang_get s='comp_method'}<br />
			{$method}
		</p>
		<p>{lang_get s='comp_lim'}<br />
			{$lim}
		</p>
