{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_msg_from_array.tpl,v 1.2 2006/04/24 10:35:36 franciscom Exp $
Author Francisco Mnacardi
*}
	<div class="bold" style="background-color:#990000; color:white;">
	  <ul>
		{foreach from=$array_of_msg item=msg}
			<li>{$msg}</li>
		{/foreach}
		</ul>
	</div>