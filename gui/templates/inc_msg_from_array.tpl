{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_msg_from_array.tpl
Author franciscom
*}
<div class="{$arg_css_class}">
  <ul>
	{foreach from=$array_of_msg item=msg}
	  <li>{$msg|escape}</li>
	{/foreach}
	</ul>
</div>