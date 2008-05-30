{*
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_head.tpl,v 1.23 2008/05/30 09:31:06 franciscom Exp $
Purpose: smarty template - HTML Common Header

rev :
     20080326 - franciscom - restored $testproject_coloring logic
     20080109 - franciscom - added sort table engine management
     20071201 - franciscom - tinymce support
*}
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
	<title>{$pageTitle|default:"TestLink"}</title>
	<link rel="TL shortcut icon" href="{$basehref}/{$smarty.const.TL_THEME_IMG_DIR}/favicon.ico" />
	<style media="all" type="text/css">@import "{$css}";</style>
	{if $testproject_coloring eq 'background'}
  	<style type="text/css"> body {ldelim}background: {$testprojectColor};{rdelim}</style>
  {/if}
  
	<style media="print" type="text/css">@import "{$basehref}{$smarty.const.TL_PRINT_CSS}";</style>

	<script type="text/javascript" src="{$basehref}gui/javascript/testlink_library.js" language="javascript"></script>
	<script type="text/javascript" src="{$basehref}gui/javascript/test_automation.js" language="javascript"></script>
	
	
  {if $jsValidate == "yes"} 
	  <script type="text/javascript" src="{$basehref}gui/javascript/validate.js" language="javascript"></script>
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
	
  {if $smarty.const.WEBEDITOR eq 'tinymce'}
    <script type="text/javascript" src="{$basehref}third_party/tinymce/jscripts/tiny_mce/tiny_mce.js" language="javascript" ></script>
    {include file="inc_tinymce_init.tpl"}
	{/if}

  {* 20080109 - franciscom *}
      {if $smarty.const.TL_SORT_TABLE_ENGINE == 'kryogenix.org'}
          <script type="text/javascript" src="{$basehref}gui/javascript/sorttable.js" language="javascript"></script>
      {/if}


{if $openHead == "no"} {* 'no' is default defined in config *}
</head>
{/if}