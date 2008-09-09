{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_help.tpl,v 1.12 2008/09/09 10:22:49 franciscom Exp $ *}
{* 

Purpose: smarty template - help link/icon component

Revisions:

	20080515 - havlatm - refactorization: use <div> tag instead of extra window
    20070124 - franciscom - adding new optional arguments
    
******************************************************************* *}

{lang_get s='help' var='img_alt'}
{assign var="img_style" value=$inc_help_style|default:"vertical-align: top;"}
{* get localized text and remove harm characters *}
{lang_get var="help_text_raw" s=$helptopic}
{assign var="help_text" 
        value=$help_text_raw|regex_replace:"/[\r\t\n]/":" "|replace:"'":"&#39;"|replace:"\"":"&quot;"|default:"Help: Localization/Text is missing." }

<script type="text/javascript">
	var help_localized_text = "<img style='float: right' " +
		"src='{$smarty.const.TL_THEME_IMG_DIR}/x-icon.gif' " +
		"onclick='javascript: close_help();' /> {$help_text}";
</script>  
<img alt="{$img_alt}" style="{$img_style}" 
	src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
	onclick='javascript: show_help(help_localized_text);'
/>