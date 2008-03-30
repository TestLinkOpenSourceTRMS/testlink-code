{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerView.tpl,v 1.6 2008/03/30 17:16:26 franciscom Exp $ *}
{* 
Purpose: smarty template - view test specification containers 

rev :
      20080329 - franciscom - added contribution by Eugenia Drosdezki
                              choose testcases to move/copy inside a testsuite 
      20071102 - franciscom - added contribution
      
      20070216 - franciscom
      moved parameters from GET to hidden
*}
{lang_get var='labels'
          s='btn_new_com,btn_reorder_cat,btn_import_testsuite,
             btn_export_all_testsuites,btn_execute_automatic_testcases,
             th_product_name,edit_testproject_basic_data,th_notes,
             btn_edit_com,alt_edit_com,btn_del_com,alt_del_com,btn_move_cp_com,
             alt_move_cp_com,btn_move_cp_testcases,
             alt_move_cp_tc_com,btn_reorder_cat,btn_export_testsuite,btn_new_cat,
             btn_import_testsuite,btn_new_tc,btn_import_tc,btn_export_tc,btn_execute_automatic_testcases'}

{assign var="container_id" value=$container_data.id}

{assign var="tcImportAction" 
        value="lib/testcases/tcImport.php?containerID=$container_id"}

{assign var="importToTProjectAction"  value="$basehref$tcImportAction&bIntoProject=1&bRecursive=1&"}
{assign var="importToTSuiteAction"  value="$basehref$tcImportAction&bRecursive=1&"}

{assign var="tcExportAction" 
        value="lib/testcases/tcExport.php?containerID=$container_id"}

{assign var="tsuiteExportAction" value="$basehref$tcExportAction&bRecursive=1"}

		 

{include file="inc_head.tpl" openHead="yes"}
{assign var="ext_version" value="-2.0"}
<link rel="stylesheet" type="text/css" href="{$basehref}third_party/ext{$ext_version}/css/ext-all.css" />
</head>

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
				<input type="hidden" name="containerID" value="{$container_data.id}">
				<input type="submit" name="new_testsuite" value="{$labels.btn_new_com}" />
			  <input type="submit" name="reorder_testsuites" value="{$labels.btn_reorder_cat}" />
			  <input type="button" onclick="location='{$importToTProjectAction}'" 
			                       value="{$labels.btn_import_testsuite}" />  
			  <input type="button" onclick="location='{$tsuiteExportAction}'" value="{$labels.btn_export_all_testsuites}" />  			  
			 
			 {* 20071102 - franciscom *}
			 {*
			 <input type="button" name="execButton" value="{$labels.btn_execute_automatic_testcases}" 
			        onclick="javascript: startExecution({$container_data.id},'testproject');" />
			 *}       
			</form>
		</div>
	{/if}

	<table width="90%" class="simple">
		<tr>
			<th>{$labels.th_product_name}</th>
		</tr>
		<tr>
			<td>
	    {if $mgt_modify_product eq 'yes'}
			  <a href="lib/project/projectedit.php"  target="mainframe"
			          title="{$labels.edit_testproject_basic_data}">{$container_data.name|escape}</a>
			{else}
			   {$container_data.name|escape}
			{/if}        
			</td>
		</tr>
		<tr>
			<th>{$labels.th_notes}</th>
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

	{if $modify_tc_rights == 'yes' || $sqlResult neq ''}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php">
		  <input type="hidden" name="testsuiteID" value="{$container_data.id}">
			<input type="hidden" name="testsuiteName" value="{$container_data.name|escape}" />

			<input type="submit" name="edit_testsuite" value="{$labels.btn_edit_com}"
				     title="{$labels.alt_edit_com}" />

			<input type="submit" name="delete_testsuite" value="{$labels.btn_del_com}" 
				     title="{$labels.alt_del_com}" />

			<input type="submit" name="move_testsuite_viewer" value="{$labels.btn_move_cp_com}" 
				     title="{$labels.alt_move_cp_com}" />

      <input type="submit" name="move_testcases_viewer" value="{$labels.btn_move_cp_testcases}"
             title="{$labels.move_cp_testcases}" />

			<input type="submit" name="reorder_testsuites" value="{$labels.btn_reorder_cat}" />
			<input type="button" onclick="location='{$tsuiteExportAction}'" value="{$labels.btn_export_testsuite}" />  
		</form>
		</div>
		<br/>	

		{* Add a new testsuite children for this parent *}
		<div>
		<form method="post" action="lib/testcases/containerEdit.php">
			<input type="hidden" name="containerID" value={$container_data.id}>
			<input type="submit" name="new_testsuite" value="{$labels.btn_new_cat}" />
			<input type="button" onclick="location='{$importToTSuiteAction}'" value="{$labels.btn_import_testsuite}" />  
		</form>
		</div>
		<br/>	

		{* Add a new testcase *}
		<div>
		<form method="post" action="lib/testcases/tcEdit.php">
		  <input type="hidden" name="containerID" value="{$container_data.id}">
			<input type="submit" id="create_tc" name="create_tc" value="{$labels.btn_new_tc}" />  
			<input type="button" onclick="location='{$tcImportAction}'" value="{$labels.btn_import_tc}" />  
			<input type="button" onclick="location='{$tcExportAction}'" value="{$labels.btn_export_tc}" />  

		  {* 20071102 - franciscom *}
		  {*
			<input type="button" name="execButton" value="{$labels.btn_execute_automatic_testcases}" 
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
