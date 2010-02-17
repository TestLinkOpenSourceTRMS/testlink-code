{* 
   TestLink Open Source Project - http://testlink.sourceforge.net/ 
   $Id: tcTree.tpl,v 1.19 2010/02/17 21:39:56 franciscom Exp $ 
   Purpose: smarty template - show test specification tree menu 

rev: 
     20091210 - franciscom -  exec type filter 
     20080831 - franciscom - treeCfg
                             manage testlink_node_type, useBeforeMoveNode
     20080805 - franciscom - BUGID 1656
     20080525 - franciscom - use only ext js tree type.
                             no change to configure a different tree menu type 
	   20070217 - franciscom - added test suite filter
                             changes to form method to allow automatic refresh
                             without browser warning
*}
{lang_get var="labels"
          s="caption_nav_filter_settings,testsuite,do_auto_update,keywords_filter_help,
             button_update_tree,no_tc_spec_av,keyword,execution_type"}


    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}

    {if $gui->ajaxTree->loader == ''}
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
	        treeCfg.loader='{$gui->ajaxTree->loader}';
	        treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape}';
	        treeCfg.root_id={$gui->ajaxTree->root_node->id};
	        treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
	        treeCfg.enableDD='{$gui->ajaxTree->dragDrop->enabled}';
	        treeCfg.dragDropBackEndUrl='{$gui->ajaxTree->dragDrop->BackEndUrl}';
	        treeCfg.cookiePrefix='{$gui->ajaxTree->cookiePrefix}';
	        treeCfg.root_testlink_node_type='{$gui->ajaxTree->root_node->testlink_node_type}';
	        treeCfg.useBeforeMoveNode='{$gui->ajaxTree->dragDrop->useBeforeMoveNode}';
	        </script>
        <script type="text/javascript" src='gui/javascript/treebyloader.js'>
        </script>
    {/if}
 </head>

<body>
{assign var="keywordsFilterDisplayStyle" value=""}
{if $gui->keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{/if}

<h1 class="title">{$gui->treeHeader}</h1>
<div style="margin: 3px;">

  <form method="get" id="tree_filter_and_settings"> 
	{if $gui->draw_filter}
	    <input type="hidden" name="feature" value="{$smarty.get.feature}" />
	  	<table class="smallGrey" width="100%">
    		<caption>
    			{$labels.caption_nav_filter_settings}
    		</caption>
    		<tr>
    			<td>{$labels.testsuite}</td>
    			<td>
    				<select name="tsuites_to_show" style="width:100%">
    					{html_options options=$gui->tsuites_combo selected=$gui->tsuite_choice}
    				</select>
    			</td>
    		</tr>
		    <tr style="{$keywordsFilterDisplayStyle}">
		      	<td>{$labels.keyword}</td>
		      	<td>
		      		<select name="keyword_id[]" title="{$labels.keywords_filter_help}"
		      	            multiple="multiple" size="{$gui->keywordsFilterItemQty+1}" style="width:100%">
		      	    	{html_options options=$gui->keywords_map selected=$gui->keyword_id}
		      		</select>
		      	</td>
		     </tr>
			<tr style="{$keywordsFilterDisplayStyle}">
				<td>&nbsp;</td>
	  			<td>
					{html_radios name='keywordsFilterType' 
                         	options=$gui->keywordsFilterType->options
                         	selected=$gui->keywordsFilterType->selected }
				</td>	
			</tr>
		  {if $session['testprojectOptions']->automationEnabled}
			<tr>
				<td>{$labels.execution_type}</td>
	  			<td>
			    <select name="exec_type">
    	  	  {html_options options=$gui->exec_type_map selected=$gui->exec_type}
	    	  </select>
				</td>	
			</tr>
			{/if}
			
			<tr>
	   			<td>{$labels.do_auto_update}</td>
	  			<td>
	  			   <input type="hidden" id="hidden_tcspec_refresh_on_action"   
	  			           name="hidden_tcspec_refresh_on_action" />
	  			
	  			   <input type="checkbox" 
	  			           id="cbtcspec_refresh_on_action"   name="tcspec_refresh_on_action"
	  			           value="1"
	  			           {if $gui->tcspec_refresh_on_action eq "yes"} checked {/if}
	  			           style="font-size: 90%;" onclick="submit()"/>
	  			</td>
	  		</tr>
	  
	  		<tr>
	  			<td>&nbsp;</td>
	  			<td>
	  				<input type="submit" name="refresh_view" id="refresh_view" 
	  			           value="{$labels.button_update_tree}" style="font-size: 90%;" />
	  			</td>
	  		</tr>
	  	</table>
	{else}
	  	<table class="smallGrey" width="100%">
	  		<tr>
	  			<td>&nbsp;</td>
	  	    	<td><input type="button" value="{$labels.button_update_tree}" style="font-size: 90%;"
	  	       			onClick="javascript: parent.treeframe.location.reload();" />
	  	    	</td>   
	  	  </tr>  
	    </table>
	{/if}	 
  </form>	
</div>

<div id="tree" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>

</body>
</html>