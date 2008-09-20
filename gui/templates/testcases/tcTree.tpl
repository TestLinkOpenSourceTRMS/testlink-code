{* 
   TestLink Open Source Project - http://testlink.sourceforge.net/ 
   $Id: tcTree.tpl,v 1.10 2008/09/20 21:02:54 schlundus Exp $ 
   Purpose: smarty template - show test specification tree menu 

rev: 
     20080831 - franciscom - treeCfg
                             manage testlink_node_type, useBeforeMoveNode
     20080805 - franciscom - BUGID 1656
     20080525 - franciscom - use only ext js tree type.
                             no change to configure a different tree menu type 
	   20070217 - franciscom - added test suite filter
                             changes to form method to allow automatic refresh
                             without browser warning
*}

{if $tlCfg->treemenu_type == 'EXTJS'}
    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl"}

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

{else}
    {include file="inc_head.tpl" jsTree="yes" openHead="yes"}
{/if}
</head>

<body>

<h1 class="title">{$treeHeader}</h1>


<div style="margin: 3px;">

  <form method="get" id="tree_filter_and_settings"> 
	{if $draw_filter}
	    <input type="hidden" name="feature" value="{$smarty.get.feature}" />
	  	<table class="smallGrey" width="100%">
	    		<caption>
	    			{lang_get s='caption_nav_filter_settings'}
	    		</caption>
	    		<tr>
	    			<td>{lang_get s='testsuite'}</td>
	    			<td>
	    			{html_options name="tsuites_to_show" options=$tsuites_combo selected=$gui->tsuite_choice}
	    			</td>
	    		</tr>
	 
	  		<tr>
	   			<td>{lang_get s='do_auto_update'}</td>
	  			<td>
	  			   <input type="hidden" id="hidden_tcspec_refresh_on_action"   
	  			           name="hidden_tcspec_refresh_on_action" />
	  			
	  			   <input type="checkbox" 
	  			           id="cbtcspec_refresh_on_action"   name="tcspec_refresh_on_action"
	  			           value="1"
	  			           {if $tcspec_refresh_on_action eq "yes"} checked {/if}
	  			           style="font-size: 90%;" onclick="submit()"/>
	  			</td>
	  		</tr>
	  
	  		<tr>
	  			<td>&nbsp;</td>
	  			<td><input type="submit" name="refresh_view" id="refresh_view" 
	  			           value="{lang_get s='button_update_tree'}" style="font-size: 90%;" /></td>
	  		</tr>
	  	</table>
	
	{else}
	  	<table class="smallGrey" width="100%">
	  		<tr>
	  			<td>&nbsp;</td>
	  	    <td><input type="button" value="{lang_get s='button_update_tree'}" style="font-size: 90%;"
	  	       onClick="javascript: parent.treeframe.location.reload();" />
	  	    </td>   
	  	  </tr>  
	    </table>
	{/if}	 
  </form>	
</div>

{if $tlCfg->treemenu_type == 'EXTJS'}
    {* 20080805 - franciscom - BUGID 1656 *}
    <div id="tree" style="overflow:auto; height:400px;border:1px solid #c3daf9;"></div>
{else}
    <div class="tree" id="tree">
        {if $tree eq ''}
          {lang_get s='no_tc_spec_av'}
        {/if}
        {$tree}
        <br />
    </div>
{/if}
 

</body>
</html>