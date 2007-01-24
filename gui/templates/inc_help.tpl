{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_help.tpl,v 1.5 2007/01/24 08:10:24 franciscom Exp $ *}
{* Purpose: smarty template - help link/icon *}

	<img title="Help"
	     alt="Help" style="vertical-align: top;" 
			 src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
       {if $help eq ''}
			   onclick="javascript:open_popup('{$helphref}{$filename}');"
       {else}
			   onclick="javascript:open_help_window('{$help}','{$locale}');"
       {/if}
  />