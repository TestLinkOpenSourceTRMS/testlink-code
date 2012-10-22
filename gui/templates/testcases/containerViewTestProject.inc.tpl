{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	containerViewTestProject.inc.tpl
@internal revisions
*}

{if $gui->grants->mgt_modify_tc == 'yes'}
	{* IS REALLY NEEDED ??? {$bDownloadOnly=false} 20121014 *}
	<fieldset class="groupBtn">
	<h2>{$labels.testsuite_operations}</h2>
	<form method="post" action="lib/testcases/testSuiteEdit.php">
	  <input type="hidden" name="doAction" id="doAction" value="" />
	  <input type="hidden" name="containerType" id="containerType" value="{$gui->level}" />
	  <input type="hidden" name="testsuiteID" id="testsuiteID" value="{$gui->tsuite.id}" />
	  <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
	
	  <input type="submit" name="new_testsuite" id="new_testsuite"
	                       onclick="doAction.value='new_testsuite'" value="{$labels.btn_new_testsuite}" />
	  <input type="submit" name="reorder_testproject_testsuites_alpha" value="{$labels.btn_reorder_testsuites_alpha}"
		  	                 title="{$labels.btn_reorder_testsuites_alpha}" />

	  <input type="button" onclick="location='{$importToTProjectAction}'" value="{$labels.btn_import_testsuite}" />
    {if $gui->canDoExport}
	    <input type="button" onclick="location='{$tsuiteExportAction}'" value="{$labels.btn_export_all_testsuites}" />
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
    {if $gui->mgt_modify_product == 'yes'}
		  <a href="lib/project/projectView.php"  target="mainframe"
		          title="{$labels.edit_testproject_basic_data}">{$gui->container_data.name|escape}</a>
		{else}
		   {$gui->container_data.name|escape}
		{/if}
		</td>
	</tr>
	<tr>
		<th>{$labels.th_notes}</th>
	</tr>
	<tr>
		<td>{$gui->container_data.notes}</td>
	</tr>
</table>
{include file="inc_attachments.tpl" attach=$gui->attach}