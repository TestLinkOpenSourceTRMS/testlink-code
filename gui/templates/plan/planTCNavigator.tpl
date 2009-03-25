{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planTCNavigator.tpl,v 1.17 2009/03/25 20:53:12 schlundus Exp $
Scope: show test plan tree for execution

Revisions : 
	20081223 - franciscom - advanced/simple filters
	20080311 - franciscom - BUGID 1427
* ---------------------------------------------------------------------- *}

{lang_get var="labels" 
          s='btn_update_menu,btn_apply_filter,keyword,keywords_filter_help,title_navigator,
             btn_update_all_testcases_to_latest_version,
             filter_owner,TestPlan,test_plan,caption_nav_filter_settings'}

{assign var="keywordsFilterDisplayStyle" value=""}
{if $gui->keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{/if}

    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}
          
    {literal}
    <script type="text/javascript">
    treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
               loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
    </script>
    {/literal}
    
    <script type="text/javascript">
	    treeCfg.root_name = '{$gui->ajaxTree->root_node->name}';
	    treeCfg.root_id = {$gui->ajaxTree->root_node->id};
	    treeCfg.root_href = '{$gui->ajaxTree->root_node->href}';
	    treeCfg.children = {$gui->ajaxTree->children};
    </script>
    
    <script type="text/javascript" src='gui/javascript/execTree.js'>
    </script>

<script type="text/javascript">
{literal}
function pre_submit()
{
	document.getElementById('called_url').value = parent.workframe.location;
	return true;
}

/*
  function: update2latest
  args :
  returns:
*/
function update2latest(id)
{
	var action_url = fRoot+'/'+menuUrl+"?level=testplan&id="+id+args;
	parent.workframe.location = action_url;
}
</script>
{/literal}
</head>
<body>

<h1 class="title">{$labels.title_navigator} {$labels.TestPlan} {$gui->additional_string|escape}</h1>
<div style="margin: 3px;">
<form method="post" id="testSetNavigator" onSubmit="javascript:return pre_submit();">
	<input type="hidden" id="called_by_me" name="called_by_me" value="1" />
	<input type="hidden" id="called_url" name="called_url" value="" />
	<input type='hidden' id="advancedFilterMode"  name="advancedFilterMode"  value="{$gui->advancedFilterMode}" />

	<table class="smallGrey" style="width:100%;">
		<caption>
			{$labels.caption_nav_filter_settings}
			{include file="inc_help.tpl" helptopic="hlp_executeFilter"}
		</caption>
    {if $gui->map_tplans != '' }
		<tr>
			<td>{$labels.test_plan}</td>
			<td>
				<select name="tplan_id" onchange="pre_submit();this.form.submit()">
			    {html_options options=$gui->map_tplans selected=$gui->tplan_id}
				</select>
			</td>
		</tr>
		{/if}
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

    {if $gui->testers }
		<tr>
			<td>{$labels.filter_owner}</td>
			<td>
			  {if $gui->advancedFilterMode }
			  <select name="filter_assigned_to[]" multiple="multiple" size={$gui->assigneeFilterItemQty}>
			  {else}
				<select name="filter_assigned_to">
			  {/if}
					{html_options options=$gui->testers selected=$gui->filter_assigned_to}
				</select>
			</td>
		</tr>
    {/if}

		<tr>
			<td colspan="2">
			<input type="submit" value="{$labels.btn_apply_filter}" 
			       id="doUpdateTree" name="doUpdateTree" style="font-size: 90%;" />
			</td>
			{if $gui->chooseFilterModeEnabled}
			<td><input type="submit" id="toggleFilterMode"  name="toggleFilterMode" 
			     value="{$gui->toggleFilterModeLabel}"  
			     onclick="toggleInput('advancedFilterMode');"
			     style="font-size: 90%;"  /></td>
      {/if}
		</tr>
	</table>
</form>
</div>


{* 20080621 - franciscom *}
{if $gui->draw_bulk_update_button }
    	<input type="button" value="{$labels.btn_update_all_testcases_to_latest_version}" 
    	       name="doUpdateToLatest" 
    	       onclick="update2latest({$gui->tplan_id})" />
{/if}

<div id="tree" style="overflow:auto; height:400px;border:1px solid #c3daf9;"></div>

<script type="text/javascript">
{if $gui->src_workframe != ''}
	parent.workframe.location='{$gui->src_workframe}';
{/if}
</script>

</body>
</html>