{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_show_hide_mgmt.tpl,v 1.1 2007/12/24 17:10:21 franciscom Exp $
Purpose: manage show/hide contanier logics
Author : franciscom

Rev:
*}	
    {assign var="args_container_draw" value=$args_container_draw|default:false}
    {assign var="args_container_class" value=$args_container_class|default:"exec_additional_info"}

    {*  franciscom - implementation note - 
     1. save the status when user saves executiosn.
     2. value is setted via javascript using the body onload event  
    *}   

		<input type='hidden' id="{$args_container_view_status_id}" 
		                     name="{$args_container_view_status_id}"  value="0" />
		
		<div class="x-panel-header x-unselectable">
    <div class="x-tool x-tool-toggle" style="background-position:0 -75px; float:left;"
         onclick="show_hide('{$args_container_id}',
                            '{$args_container_view_status_id}',
                            document.getElementById('{$args_container_id}').style.display=='none')"/></div>
    <span style="padding:2px;">{$args_container_title|escape}</span>
	  </div>
	  
	  {if $args_container_draw}
    <div id="{$args_container_id}" class="{$args_container_class}">
     {$args_container_html}
    </div>
	  {/if}
