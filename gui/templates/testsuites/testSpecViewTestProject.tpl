{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	testSpecViewTestProject.tpl
@internal revisions
*}
{lang_get var='labels' 
          s='testsuite_operations,th_product_name,edit_testproject_basic_data,th_notes, 
             btn_reorder_testsuites_alpha,btn_import_testsuite,btn_new_testsuite,
             btn_export_all_testsuites'}

{include file="inc_head.tpl" openHead="yes"}
{$ext_location=$smarty.const.TL_EXTJS_RELATIVE_PATH}
<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/ext-all.css" />
</head>

<body>
<h1 class="title">{$gui->page_title|escape}</h1>
<div class="workBack">

{if $gui->grants->mgt_modify_tc == 'yes'}
	<fieldset class="groupBtn">
	<h2>{$labels.testsuite_operations}</h2>
	<form method="post" action="lib/testsuites/testSuiteEdit.php">
	  <input type="hidden" name="doAction" id="doAction" value="" />
	  <input type="hidden" name="containerType" id="containerType" value="testproject" />
	  <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
	  <input type="submit" name="new_testsuite" id="new_testsuite"
	                       onclick="doAction.value='new_testsuite'" value="{$labels.btn_new_testsuite}" />
	  <input type="submit" name="reorder_testproject_testsuites_alpha" value="{$labels.btn_reorder_testsuites_alpha}"
		  	                 title="{$labels.btn_reorder_testsuites_alpha}" />

	  <input type="button" onclick="location='{$gui->actions->importTestSuite}'" value="{$labels.btn_import_testsuite}" />
    {if $gui->canDoExport}
	    <input type="button" onclick="location='{$gui->actions->exportAllTestSuites}'" value="{$labels.btn_export_all_testsuites}" />
    {/if}
  </form>
  </fieldset>
{/if}
<table class="simple" >
	<tr>
		<th>{$labels.th_product_name}</th>
	</tr>
	<tr>
		<td>
    {if $gui->grants->mgt_modify_product == 'yes'}
		  <a href="lib/project/projectView.php"  target="mainframe"
		          title="{$labels.edit_testproject_basic_data}">{$gui->tproject.name|escape}</a>
		{else}
		   {$gui->tproject.name|escape}
		{/if}
		</td>
	</tr>
	<tr>
		<th>{$labels.th_notes}</th>
	</tr>
	<tr>
		<td>{$gui->tproject.notes}</td>
	</tr>
</table>
{include file="inc_attachments.tpl" attach=$gui->attach}
{if $gui->refreshTree}
  {include file="inc_refreshTreeWithFilters.tpl"}
{/if}
</body>
</html>