{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_help.tpl,v 1.9 2008/05/15 14:44:38 havlat Exp $ *}
{* 

Purpose: smarty template - help link/icon component

Revisions:

	20080515 - havlatm - refactorization: use <div> tag instead of extra window
    20070124 - franciscom - adding new optional arguments
    
******************************************************************* *}

{assign var="img_title" value=$inc_help_title|default:"Help"}
{assign var="img_alt" value=$inc_help_alt|default:"Help"}
{assign var="img_style" value=$inc_help_style|default:"vertical-align: top;"}
{* get localized text and remove harm characters *}
{lang_get var="help_text_raw" s=$helptopic}
{assign var="help_text" value=$help_text_raw|regex_replace:"/[\r\t\n]/":" "|default:"Help: Localization/Text is missing." }
  
<img title="{$img_title}"
	alt="{$img_alt}" style="{$img_style}" 
	src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
	onclick="javascript: ;var myText = '{$help_text}'; show_help(myText);"
/>