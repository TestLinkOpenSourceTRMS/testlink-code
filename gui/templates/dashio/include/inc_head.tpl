{*
Testlink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_head.tpl
Purpose: smarty template - HTML Common Header

Critic Smarty Global Variables expected

editorType: used to understand if code for tinymce need to be loaded 

*}
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$pageCharset}" />
	<meta http-equiv="Content-language" content="en" />
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="author" content="TestLink" />
	<meta name="copyright" content="GNU" />
	<meta name="robots" content="NOFOLLOW" />
	<base href="{$basehref}"/>
	<title>{$pageTitle|default:"TestLink"}</title>
	<link rel="icon" href="{$basehref}{$smarty.const.TL_THEME_IMG_DIR}favicon.ico" type="image/x-icon" />

{$css = str_replace('default','dashio',$css)}
{* ----- load CSS ----------------------------------------------- *} 
	<style media="all" type="text/css">@import "{$css}";</style>

	{if $use_custom_css}
	<style media="all" type="text/css">@import "{$custom_css}";</style>
	{/if}
	
	{if $testproject_coloring eq 'background'}
  	<style type="text/css"> body {ldelim}background: {$testprojectColor};{rdelim}</style>
	{/if}
  
	<style media="print" type="text/css">@import "{$basehref}{$smarty.const.TL_PRINT_CSS}";</style>

{* ----- load javascripts libraries --------------------------- *} 
	<script type="text/javascript" src="{$basehref}gui/javascript/testlink_library.js" language="javascript"></script>
	<script type="text/javascript" src="{$basehref}gui/javascript/test_automation.js" language="javascript"></script>
	
	{if $jsValidate == "yes"} 
	<script type="text/javascript" src="{$basehref}gui/javascript/validate.js" language="javascript"></script>
    {include file="inc_jsCfieldsValidation.tpl"}
	{/if}
   
	{if $editorType == 'tinymce'}
    <script type="text/javascript" language="javascript"
    	src="{$basehref}third_party/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
    {include file="inc_tinymce_init.tpl"}
	{/if}


  <link rel="stylesheet" href="{$basehref}third_party/chosen/chosen.css">

	<script type="text/javascript" 
    src="{$basehref}{$smarty.const.TL_JQUERY}" language="javascript"></script>

	<script type="text/javascript" src="{$basehref}third_party/chosen/chosen.jquery.js"></script>
 
  {$tproject_id = 0}
	{$tplan_id = 0}
	{if property_exists($gui,'tproject_id')}
	  {$tproject_id = $gui->tproject_id}
	{/if}

	{if property_exists($gui,'tplan_id')}
	  {$tplan_id = $gui->tplan_id}
	{/if}

	<script type="text/javascript" language="javascript">
	//<!--
  /* inc_head.tpl */
	var fRoot = '{$basehref}';
	var menuUrl = '{$menuUrl}';
	var args  = '{$args}';
	var additionalArgs  = '{$additionalArgs}';
	var printPreferences = '{$printPreferences}';
	var tproject_id = '{$tproject_id}';
	var tplan_id = '{$tplan_id}';
	
	// To solve problem diplaying help
	var SP_html_help_file  = '{$SP_html_help_file}';
	
	//attachment related JS-Stuff
	var attachmentDlg_refWindow = null;
	var attachmentDlg_refLocation = null;
	var attachmentDlg_bNoRefresh = false;
	
	// bug management (using logic similar to attachment)
	var bug_dialog = new bug_dialog();

	// for ext js
	var extjsLocation = '{$smarty.const.TL_EXTJS_RELATIVE_PATH}';
	
	//-->
	</script> 

  <link href="{$dashioHomeURL}lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <link href="{$fontawesomeHomeURL}/css/all.css" rel="stylesheet" />


  <link href="{$dashioHomeURL}css/style.css" rel="stylesheet">
  <link href="{$dashioHomeURL}css/style-responsive.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{$basehref}gui/themes/default/css/frame.css">  

{if $openHead == "no"} {* 'no' is default defined in config *}
</head>
{/if}