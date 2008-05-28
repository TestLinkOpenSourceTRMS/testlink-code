{* 
   TestLink Open Source Project - http://testlink.sourceforge.net/ 
   $Id: tcTree.tpl,v 1.4 2008/05/28 20:56:58 franciscom Exp $ 
   Purpose: smarty template - show test specification tree menu 
 
   20080525 - franciscom - use only ext js tree type.
                           no change to configure a different tree menu type 
	 20070217 - franciscom - added test suite filter
                           changes to form method to allow automatic refresh
                           without browser warning
*}
{if $tlCfg->spectreemenu_type == ''}
    {include file="inc_head.tpl" jsTree="yes" openHead="yes"}
{else}
    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl"}

    {literal}
    <script type="text/javascript">
    treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",loader:""};
    </script>
    {/literal}
    
    <script type="text/javascript">
    treeCfg.loader='{$gui->ajaxTree->loader}';
    treeCfg.root_name='{$gui->ajaxTree->root_node->name}';
    treeCfg.root_id={$gui->ajaxTree->root_node->id};
    treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
    </script>
    
    <script type="text/javascript" src='gui/javascript/tcTree.js'>
    </script>
{/if}
</head>

<body>

<h1 class="title">{$treeHeader}</h1>


<div style="margin: 3px;">

{if $draw_filter}
  <form method="get" id="tree_filter_and_settings"> 
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
  </form>

{else}
  <form>
  	<table class="smallGrey" width="100%">
  		<tr>
  			<td>&nbsp;</td>
  	    <td><input type="button" value="{lang_get s='button_update_tree'}" style="font-size: 90%;"
  	       onClick="javascript: parent.treeframe.location.reload();" />
  	    </td>   
  	  </tr>  
    </table>
  </form>
{/if}		
</div>

{if $tlCfg->spectreemenu_type == ''}
    <div class="tree" id="tree">
        {if $tree eq ''}
          {lang_get s='no_tc_spec_av'}
        {/if}
        {$tree}
        <br />
    </div>
{else}
    <div id="tree" style="overflow:auto; height:300px;width:250px;border:1px solid #c3daf9;"></div>
{/if}

</body>
</html>