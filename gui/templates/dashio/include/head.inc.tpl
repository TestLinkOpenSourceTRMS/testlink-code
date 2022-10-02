<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$pageCharset}" />
	<base href="{$basehref}"/>
	<title>{$pageTitle|default:"TestLink"}</title>


  <script type="text/javascript" language="javascript"
          src="{$basehref}{$smarty.const.TL_JQUERY}">
  </script>

	<script type="text/javascript" 
	        src="{$basehref}third_party/chosen/chosen.jquery.js">
  </script>

	<script type="text/javascript" language="javascript">
	//<!--
  /* head.inc.tpl */
	var fRoot = '{$basehref}';
	var menuUrl = '{$menuUrl}';
	var args  = '{$args}';
	var additionalArgs  = '{$additionalArgs}';
	var printPreferences = '{$printPreferences}';
	//var tproject_id = {$gui->tproject_id};
	//var tplan_id = {$gui->tplan_id};
	//-->
	</script> 
{if $openHead == "no"} {* 'no' is default defined in config *}
</head>
{/if}