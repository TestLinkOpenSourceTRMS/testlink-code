{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_help.tpl,v 1.7 2007/01/25 14:13:26 franciscom Exp $ *}
{* 

Purpose: smarty template - help link/icon 

rev:
     20070124 - franciscom
     adding new optional arguments
*}

  {assign var="img_title" value=$title|default:"Help"}
  {assign var="img_alt" value=$alt|default:"Help"}
  {assign var="img_style" value=$style|default:"vertical-align: top;"}
  
 	<img title="{$img_title}"
	     alt="{$img_alt}" style="{$img_style}" 
			 src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
       {if $help eq ''}
			   onclick="javascript:open_popup('{$helphref}{$filename}');"
       {else}
			   onclick="javascript:open_help_window('{$help}','{$locale}');"
       {/if}
  />