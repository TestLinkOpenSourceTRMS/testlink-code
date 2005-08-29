{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_cat_viewer_rw.tpl,v 1.1 2005/08/29 06:45:54 franciscom Exp $ *}
{* *}

		<p>{lang_get s='cat_name'}<br />
			<input type="text" name="name" alt="{lang_get s='cat_alt_name'}"
			value="{$name|escape}" size="50" /></p>
		
		<p>{lang_get s='cat_scope'}<br />
			{$objective}
		</p>
		
		<p>{lang_get s='cat_config'}<br />
			{$config}
		</p>
		
		<p>{lang_get s='cat_data'}<br />
			{$data}
		</p>
		
		<p>{lang_get s='cat_tools'}<br />
			{$tools}
		</p>
		