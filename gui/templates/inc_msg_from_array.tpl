{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_msg_from_array.tpl,v 1.4 2008/09/22 19:14:07 schlundus Exp $
Author franciscom
*}
	<div class="{$arg_css_class}">
	  <ul>
		{foreach from=$array_of_msg item=msg}
			<li>{$msg|escape}</li>
		{/foreach}
		</ul>
	</div>