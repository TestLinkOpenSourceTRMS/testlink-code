{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_show_hide_mgmt.tpl,v 1.5 2009/09/07 06:46:42 franciscom Exp $
Purpose: manage show/hide contanier logics
Author : franciscom

Rev:
*}
{$show_hide_container_draw=$show_hide_container_draw|default:false}
{$show_hide_container_class=$show_hide_container_class|default:"exec_additional_info"}

{*  franciscom - implementation note -
	1. save the status when user saves executiosn.
	2. value is setted via javascript using the body onload event
*}

<input type='hidden' id="{$show_hide_container_view_status_id}"
         name="{$show_hide_container_view_status_id}"  value="0" />

<div class="x-panel-header x-unselectable">
	<div class="x-tool x-tool-toggle" style="background-position:0 -75px; float:left;"
		onclick="show_hide('{$show_hide_container_id}',
	              '{$show_hide_container_view_status_id}',
	              document.getElementById('{$show_hide_container_id}').style.display=='none')">
	</div>
	<span style="padding:2px;">{$show_hide_container_title}</span>
</div>

{if $show_hide_container_draw}
	<div id="{$show_hide_container_id}" class="{$show_hide_container_class}">
		{$show_hide_container_html}
	</div>
{/if}