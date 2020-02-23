{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_msg_from_array.tpl,v 1.5 2009/08/19 19:56:24 schlundus Exp $
Author franciscom
*}
	<div class="{$arg_css_class}">
		<ul>
		{foreach from=$array_of_msg item=msg}
			<li>{$msg|escape}</li>
		{/foreach}
		</ul>
	</div>