{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_msg_from_array.tpl,v 1.3 2006/08/21 13:19:15 franciscom Exp $
Author franciscom
*}
	<div class="{$arg_css_class}">
	  <ul>
		{foreach from=$array_of_msg item=msg}
			<li>{$msg}</li>
		{/foreach}
		</ul>
	</div>