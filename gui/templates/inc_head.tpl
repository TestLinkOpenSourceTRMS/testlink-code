{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_head.tpl,v 1.9 2007/03/12 07:04:49 franciscom Exp $ *}
{* Purpose: smarty template - HTML Common Header *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$pageCharset}" />
	<meta http-equiv="Content-language" content="en" />
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="author" content="Martin Havlat" />
	<meta name="copyright" content="GNU" />
	<meta name="robots" content="NOFOLLOW" />
	<base href="{$basehref}"/>
	<title>{$title|default:"TestLink"}</title>
	<style media="all" type="text/css">@import "{$css}";</style>
	
	{if $smarty.const.TL_JOMLA_1_CSS neq ''}
  	<style media="all" type="text/css">@import "{$smarty.const.TL_JOMLA_1_CSS}";</style>
	{/if}
	{*
	http://localhost/w3/tl/tl-head-20070127/gui/themes/theme_m1/css/jos_template_css.css";</style>
  *}
  
	{if $smarty.const.TL_TESTPROJECT_COLORING eq 'background'}
	  <style type="text/css"> body {ldelim}background: {$testprojectColor};{rdelim}</style>
	{/if}
	<style media="print" type="text/css">@import "{$smarty.const.TL_PRINT_CSS}";</style>
	<script type="text/javascript" src="gui/javascript/testlink_library.js" language="javascript"></script>
{if $jsValidate == "yes"} 
	<script type="text/javascript" src="gui/javascript/validate.js" language="javascript"></script>
{/if}
{if $jsTree == "yes"} {* 'no' is default defined in config *}
	{include file="inc_jsTree.tpl"}
{/if}
	<script type="text/javascript" language="javascript">
	var fRoot = '{$basehref}';
	var menuUrl = '{$menuUrl}';
	var args  = '{$args}';
	
	// 20050528 - fm
	// To solve problem diplaying help
	var SP_html_help_file  = '{$SP_html_help_file}';
	
	//attachment related JS-Stuff
	var attachmentDlg_refWindow = null;
	var attachmentDlg_refLocation = null;
	var attachmentDlg_bNoRefresh = false;
	
	// 20060916 - franciscom
	// bug management (using logic similar to attachment)
	var bug_dialog=new bug_dialog();
  
	</script> 
{if $openHead == "no"} {* 'no' is default defined in config *}
</head>
{/if}
