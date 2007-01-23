{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_help.tpl,v 1.4 2007/01/23 18:26:41 franciscom Exp $ *}
{* Purpose: smarty template - help link/icon *}
	<img title="Help"
	     alt="Help" style="vertical-align: top;" 
			 src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
			 onclick="javascript:open_popup('{$helphref}{$filename}');" />
