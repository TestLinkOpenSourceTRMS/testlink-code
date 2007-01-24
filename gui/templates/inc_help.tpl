{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_help.tpl,v 1.6 2007/01/24 18:56:10 franciscom Exp $ *}
{* 

Purpose: smarty template - help link/icon 

rev:
     20070124 - franciscom
     adding new optional arguments
*}

  {assign var="img_title" value=$title|default:"Help"}
  {assign var="img_alt" value=$alt|default:"Help"}
  

	<img title="{img_title}"
	     alt="{img_alt}" style="vertical-align: top;" 
			 src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
       {if $help eq ''}
			   onclick="javascript:open_popup('{$helphref}{$filename}');"
       {else}
			   onclick="javascript:open_help_window('{$help}','{$locale}');"
       {/if}
  />