{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_help.tpl,v 1.8 2008/05/02 07:09:23 franciscom Exp $ *}
{* 

Purpose: smarty template - help link/icon 

rev:
     20070124 - franciscom
     adding new optional arguments
*}

  {assign var="img_title" value=$inc_help_title|default:"Help"}
  {assign var="img_alt" value=$inc_help_alt|default:"Help"}
  {assign var="img_style" value=$inc_help_style|default:"vertical-align: top;"}
  
 	<img title="{$img_title}"
	     alt="{$img_alt}" style="{$img_style}" 
			 src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
       {if $help eq ''}
			   onclick="javascript:open_popup('{$helphref}{$filename}');"
       {else}
			   onclick="javascript:open_help_window('{$help}','{$locale}');"
       {/if}
  />