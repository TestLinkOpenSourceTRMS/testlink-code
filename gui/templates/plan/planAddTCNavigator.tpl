{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planAddTCNavigator.tpl,v 1.16 2009/03/25 20:53:12 schlundus Exp $
f
Scope: show test specification tree for Test Plan related features
		(the name of scripts is not correct; used more)

Revisions:
	20080629 - franciscom - fixed missed variable bug
    20080622 - franciscom - ext js tree support
    20080429 - franciscom - keyword filter multiselect
* ------------------------------------------------------------------------ *}

{lang_get var="labels" 
          s='keywords_filter_help,btn_apply_filter,
             btn_update_menu,title_navigator,keyword,test_plan,keyword,caption_nav_filter_settings'}

{assign var="keywordsFilterDisplayStyle" value=""}
{if $gui->keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{/if}

    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}

    {if $gui->ajaxTree->loader == '' }
        {literal}
        <script type="text/javascript">
        treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
                   loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
        </script>
        {/literal}

        <script type="text/javascript">
        treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape:'javascript'}';
        treeCfg.root_id={$gui->ajaxTree->root_node->id};
        treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
        treeCfg.children={$gui->ajaxTree->children};
        </script>
        <script type="text/javascript" src='gui/javascript/execTree.js'></script>
    {else}
        {literal}
        <script type="text/javascript">
        treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
                   root_testlink_node_type:'',useBeforeMoveNode:false,
                   loader:"", enableDD:false, dragDropBackEndUrl:''};
        </script>
        {/literal}
        
        <script type="text/javascript">
	        treeCfg.loader = "{$gui->ajaxTree->loader}";
	        treeCfg.root_name = "{$gui->ajaxTree->root_node->name|escape}";
	        treeCfg.root_id = {$gui->ajaxTree->root_node->id};
	        treeCfg.root_href = "{$gui->ajaxTree->root_node->href}";
	        treeCfg.cookiePrefix = "{$gui->ajaxTree->cookiePrefix}";
	    </script>
        
        <script type="text/javascript" src="gui/javascript/treebyloader.js">
        </script>
   {/if}

<script type="text/javascript">
{literal}
function pre_submit()
{
	document.getElementById('called_url').value=parent.workframe.location;
	return true;
}
</script>
</head>

<body>

<h1 class="title">{$labels.title_navigator}</h1>
<div style="margin: 3px;">
<form method="post" id="planAddTCNavigator" onSubmit="javascript:return pre_submit();">
	<input type="hidden" id="called_by_me" name="called_by_me" value="1" />
 	<input type="hidden" id="called_url" name="called_url" value="" />

	<table class="smallGrey" width="100%">
		<caption>
			{$labels.caption_nav_filter_settings}
			{* include file="inc_help.tpl" helptopic="hlp_executeFilter" *}
		</caption>
		<tr>
			<td>{$labels.test_plan}</td>
			<td>
				<select name="tplan_id" onchange="pre_submit();this.form.submit()">
			    {html_options options=$gui->map_tplans selected=$gui->tplan_id}
				</select>
			</td>
		</tr>
		<tr style="{$keywordsFilterDisplayStyle}">
			<td>{$labels.keyword}</td>
			<td><select name="keyword_id[]" title="{$labels.keywords_filter_help}"
			            multiple="multiple" size={$gui->keywordsFilterItemQty}>
			    {html_options options=$gui->keywords_map selected=$gui->keyword_id}
				</select>
			</td>
			<td>
     			 {html_radios name='keywordsFilterType' 
                   	options=$gui->keywordsFilterType->options
                   	selected=$gui->keywordsFilterType->selected }
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" value="{$labels.btn_apply_filter}" name="doUpdateTree" />
			</td>
		</tr>
	</table>
</form>
</div>

<div id="tree" style="overflow:auto; height:400px;border:1px solid #c3daf9;"></div>

{* 20061030 - update the right pane *}
<script type="text/javascript">
{if $gui->src_workframe != ''}
	parent.workframe.location='{$gui->src_workframe}';
{else}
	{if $gui->do_reload}
		  parent.workframe.location.reload();
	{/if}
{/if}
</script>
</body>
</html>