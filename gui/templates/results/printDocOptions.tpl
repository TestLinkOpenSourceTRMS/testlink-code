{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: printDocOptions.tpl,v 1.20.2.2 2011/02/11 10:54:40 mx-julian Exp $ 
Purpose: show tree on print feature

rev:
	20110211 - Julian - BUGID 4199 - tree div has to be named different than the tree variable
	20101106 - amitkhullar - BUGID 2738: Contribution: option to include TC Exec notes in test report
	20101027 - asimon - BUGID 3946: reqirement specification tree size
	20080820 - franciscom - added code to manage EXTJS tree component

*}
{lang_get var="labels"
          s='doc_opt_title,doc_opt_guide,tr_td_show_as,check_uncheck_all_options'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}
{include file="inc_jsCheckboxes.tpl"}

{if $gui->ajaxTree->loadFromChildren}
    {literal}
    <script type="text/javascript">
    treeCfg = {tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
               loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
    </script>
    {/literal}
    
    <script type="text/javascript">
	    treeCfg.root_name = '{$gui->ajaxTree->root_node->name|escape:'javascript'}';
	    treeCfg.root_id = {$gui->ajaxTree->root_node->id};
	    treeCfg.root_href = '{$gui->ajaxTree->root_node->href}';
	    treeCfg.children = {$gui->ajaxTree->children}
	    treeCfg.cookiePrefix = '{$gui->ajaxTree->cookiePrefix}';
    </script>

    <script type="text/javascript" src='gui/javascript/execTree.js'></script>
{else}
    {literal}
    <script type="text/javascript">
    	treeCfg = {tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
               loader:"", enableDD:false, dragDropBackEndUrl:''};
    </script>
    {/literal}
    
    <script type="text/javascript">
		treeCfg.loader = '{$gui->ajaxTree->loader}';
		treeCfg.root_name = '{$gui->ajaxTree->root_node->name|escape:'javascript'}';
		treeCfg.root_id = {$gui->ajaxTree->root_node->id};
		treeCfg.root_href = '{$gui->ajaxTree->root_node->href}';
		treeCfg.enableDD = '{$gui->ajaxTree->dragDrop->enabled}';
		treeCfg.dragDropBackEndUrl = '{$gui->ajaxTree->dragDrop->BackEndUrl}';
		treeCfg.cookiePrefix = '{$gui->ajaxTree->cookiePrefix}';
    </script>
    <script type="text/javascript" src='gui/javascript/treebyloader.js'></script>
{/if} 
</head>

<body>
<h1 class="title">{$gui->mainTitle}{include file="inc_help.tpl" helptopic="hlp_generateDocOptions" show_help_icon=true}</h1>

<div style="margin: 10px;">
<p>{$labels.doc_opt_guide}<br /></p>
<form method="GET" id="printDocOptions" name="printDocOptions"
      action="lib/results/printDocument.php?type={$gui->doc_type}">

	<input type="hidden" name="docTestPlanId" value="{$docTestPlanId}" />
  	<input type="hidden" name="toggle_memory" id="toggle_memory"  value="0" />

	<table class="smallGrey" id="optionsContainer" name="optionsContainer">
		{section name=number loop=$arrCheckboxes}
		<tr>
			<td>{$arrCheckboxes[number].description}</td>
			<td>
				<input type="checkbox" name="{$arrCheckboxes[number].value}" id="cb{$arrCheckboxes[number].value}"
				{if $arrCheckboxes[number].checked == 'y'}checked="checked"{/if}/>
			</td>
		</tr>
		{/section}
		<tr>
		{if $docType == 'testspec' || $docType == 'reqspec'}
			<td>{$labels.tr_td_show_as}</td>
			<td>
				<select id="format" name="format">
					{html_options options=$gui->outputFormat selected=$selFormat}
				</select>
			</td>
		{else}
		    <td><input type="hidden" id="format" name="format" value="{$selFormat}" /></td>
		{/if}
		</tr>
		<tr>
		 <td><input type="button" id="toogleOptions" name="toogleOptions"
		            onclick='cs_all_checkbox_in_div("optionsContainer","cb","toggle_memory");'
		            value="{$labels.check_uncheck_all_options}" /> </td>
		</tr>
	</table>
</form>
</div>

<div id="tree_div" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>

</body>
</html>