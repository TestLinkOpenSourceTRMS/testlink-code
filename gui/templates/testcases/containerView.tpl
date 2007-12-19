{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerView.tpl,v 1.3 2007/12/19 20:27:19 schlundus Exp $ *}
{* 
Purpose: smarty template - view test specification containers 

rev :
      20071102 - franciscom - added contribution
      
      20070216 - franciscom
      moved parameters from GET to hidden
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">
<h1>{$page_title}{$smarty.const.TITLE_SEP}{$container_data.name|escape}</h1>

{include file="inc_update.tpl" result=$sqlResult item=$level 
         name=$moddedItem.name refresh=$smarty.session.tcspec_refresh_on_action }
         
{assign var="bDownloadOnly" value=false}
{if $level == 'testproject'}
	{if $mgt_modify_product neq 'yes'}
		{assign var="bDownloadOnly" value=true}
	{/if}
	
	{if $modify_tc_rights == 'yes'}
		<div>
			<form method="post" action="lib/testcases/containerEdit.php">
				<input type="hidden" name="containerID" value={$container_data.id}>
				<input type="submit" name="new_testsuite" value="{lang_get s='btn_new_com'}" />
			  <input type="submit" name="reorder_testsuites" value="{lang_get s='btn_reorder_cat'}" />
			  <input type="button" onclick="location='{$basehref}lib/testcases/tcImport.php?bIntoProject=1&bRecursive=1&containerID={$container_data.id}'" value="{lang_get s='btn_import_testsuite'}" />  
			 <input type="button" onclick="location='{$basehref}lib/testcases/tcexport.php?bRecursive=1&containerID={$container_data.id}'" value="{lang_get s='btn_export_all_testsuites'}" />  			  
			 
			 {* 20071102 - franciscom *}
			 {*
			 <input type="button" name="execButton" value="{lang_get s='btn_execute_automatic_testcases'}" 
			        onclick="javascript: startExecution({$container_data.id},'testproject');" />
			 *}       
			</form>
		</div>
	{/if}

	<table width="90%" class="simple">
		<tr>
			<th>{lang_get s='th_product_name'}</th>
		</tr>
		<tr>
			<td>
	    {if $mgt_modify_product eq 'yes'}
			  <a href="lib/project/projectedit.php"  target="mainframe"
			          title="{lang_get s='edit_testproject_basic_data'}">{$container_data.name|escape}</a>
			{else}
			   {$container_data.name|escape}
			{/if}        
			</td>
		</tr>
		<tr>
			<th>{lang_get s='th_notes'}</th>
		</tr>
		<tr>
			<td>{$container_data.notes}</td>
		</tr>
		
	</table>
	{* 20071102 - franciscom *}
	{*
	<div id="inProgress"></div>
	<div id="executionResults"></div>
  *}
   
	{include file="inc_attachments.tpl" id=$id tableName="nodes_hierarchy" downloadOnly=$bDownloadOnly}
{elseif $level == 'testsuite'}

	{if $modify_tc_rights == 'yes' || $sqlResult ne ''}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php">
		  <input type="hidden" name="testsuiteID" value="{$container_data.id}">
			<input type="hidden" name="testsuiteName" value="{$container_data.name|escape}" />

			<input type="submit" name="edit_testsuite" value="{lang_get s='btn_edit_com'}"
				     alt="{lang_get s='alt_edit_com'}" />
			<input type="submit" name="delete_testsuite" value="{lang_get s='btn_del_com'}" 
				     alt="{lang_get s='alt_del_com'}" />
			<input type="submit" name="move_testsuite_viewer" value="{lang_get s='btn_move_cp_com'}" 
				     alt="{lang_get s='alt_move_cp_com'}" />
			<input type="submit" name="reorder_testsuites" value="{lang_get s='btn_reorder_cat'}" />
			<input type="button" onclick="location='{$basehref}lib/testcases/tcexport.php?bRecursive=1&containerID={$container_data.id}'" value="{lang_get s='btn_export_testsuite'}" />  
		</form>
		</div>
		<br/>	

		{* Add a new testsuite children for this parent *}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php">
			<input type="hidden" name="containerID" value={$container_data.id}>
			<input type="submit" name="new_testsuite" value="{lang_get s='btn_new_cat'}" />
			<input type="button" onclick="location='{$basehref}lib/testcases/tcImport.php?bRecursive=1&containerID={$container_data.id}'" value="{lang_get s='btn_import_testsuite'}" />  
		</form>
		</div>
		<br/>	

		{* Add a new testcase *}
		<div>
		<form method="post" action="lib/testcases/tcEdit.php">
		  <input type="hidden" name="containerID" value="{$container_data.id}">
			<input type="submit" id="create_tc" name="create_tc" value="{lang_get s='btn_new_tc'}" />  
			<input type="button" onclick="location='{$basehref}lib/testcases/tcImport.php?containerID={$container_data.id}'" value="{lang_get s='btn_import_tc'}" />  
			<input type="button" onclick="location='{$basehref}lib/testcases/tcexport.php?containerID={$container_data.id}'" value="{lang_get s='btn_export_tc'}" />  

		  {* 20071102 - franciscom *}
		  {*
			<input type="button" name="execButton" value="{lang_get s='btn_execute_automatic_testcases'}" 
			       onclick="javascript: startExecution({$container_data.id},'testsuite');" />
			*}       
		</form>
		</div>
		{* 20071102 - franciscom *}
		{*
		<div id="inProgress"></div><br />
		<div id="executionResults"></div>
		*}
	{/if}
  {assign var=this_template_dir value=$smarty.template|dirname}
	{include file="$this_template_dir/inc_testsuite_viewer_ro.tpl"}
    
	{if $modify_tc_rights neq 'yes'}
		{assign var="bDownloadOnly" value=true}
	{/if}
	{include file="inc_attachments.tpl" id=$id tableName="nodes_hierarchy" downloadOnly=$bDownloadOnly}

	{/if}

</div>
{if $refreshTree}
   {include file="inc_refreshTree.tpl"}
{/if}
</body>
</html>
