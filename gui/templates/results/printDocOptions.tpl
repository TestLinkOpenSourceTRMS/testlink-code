{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: printDocOptions.tpl,v 1.13 2009/03/12 22:15:13 havlat Exp $ 
Purpose: show tree on print feature

rev: 20080820 - franciscom - added code to manage EXTJS tree component

*}

{if $tlCfg->treemenu_type == 'EXTJS'}
    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}

    {if $gui->ajaxTree->loadFromChildren}
        {literal}
        <script type="text/javascript">
        treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
                   loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
        </script>
        {/literal}
        
        <script type="text/javascript">
        // On execNavigator.tpl I've used
        // escape:'javascript'
        treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape:'javascript'}';
        treeCfg.root_id={$gui->ajaxTree->root_node->id};
        treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
        treeCfg.children={$gui->ajaxTree->children}
        treeCfg.cookiePrefix='{$gui->ajaxTree->cookiePrefix}';
        </script>

        <script type="text/javascript" src='gui/javascript/execTree.js'>
        </script>
    {else}
        {literal}
        <script type="text/javascript">
        treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
                   loader:"", enableDD:false, dragDropBackEndUrl:''};
        </script>
        {/literal}
        
        <script type="text/javascript">
        treeCfg.loader='{$gui->ajaxTree->loader}';
        // 20081116 - franciscom
        // On execNavigator.tpl I've used
        // escape:'javascript'
        treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape:'javascript'}';
        treeCfg.root_id={$gui->ajaxTree->root_node->id};
        treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
        treeCfg.enableDD='{$gui->ajaxTree->dragDrop->enabled}';
        treeCfg.dragDropBackEndUrl='{$gui->ajaxTree->dragDrop->BackEndUrl}';
        </script>
        <script type="text/javascript" src='gui/javascript/treebyloader.js'>
        </script>
    {/if} 

{else}
    {include file="inc_head.tpl" jsTree="yes" openHead="yes"}
{/if}
</head>

<body>
<h1 class="title">{lang_get s='doc_opt_title'}{include file="inc_help.tpl" helptopic="hlp_generateDocOptions"}</h1>

<div style="margin: 10px;">
<p>{lang_get s='doc_opt_guide'}<br /></p>
<form method="GET" action="lib/results/printDocument.php?type={$gui->doc_type}">

	<input type="hidden" name="docTestPlanId" value="{$docTestPlanId}" />
	<table class="smallGrey" >
		{section name=number loop=$arrCheckboxes}
		<tr>
			<td>{$arrCheckboxes[number].description}</td>
			<td><input type="checkbox" name="{$arrCheckboxes[number].value}" id="cb{$arrCheckboxes[number].value}"
			{if $arrCheckboxes[number].checked == 'y'}checked="checked"{/if} 
			/></td>
		</tr>
		{/section}
		<tr>
		{if $docType == 'testspec'}
			<td>{lang_get s='tr_td_show_as'}</td>
			<td><select id="format" name="format">
			{html_options options=$arrFormat selected=$selFormat}
			</select></td>
		{else}
		    <td><input type="hidden" id="format" name="format" value="{$selFormat}" /></td>
		{/if}
		</tr>
	</table>
</form>
</div>

{if $tlCfg->treemenu_type == 'EXTJS'}
    <div id="tree" style="overflow:auto; height:400px;border:1px solid #c3daf9;"></div>
{else}
    <div class="tree" name="treeMenu"  id="tree">
        {$tree}
        <br />
    </div>
{/if}

</body>
</html>
