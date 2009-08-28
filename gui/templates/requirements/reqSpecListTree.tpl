{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecListTree.tpl,v 1.8 2009/08/28 20:37:03 schlundus Exp $ 
show requirement specifications tree menu

rev: 20080831 - franciscom - treeCfg
                             manage testlink_node_type, useBeforeMoveNode
                             
*}
    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}

    {literal}
    <script type="text/javascript">
    treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
               root_testlink_node_type:'',useBeforeMoveNode:false,
               loader:"", enableDD:false, dragDropBackEndUrl:''};
    </script>
    {/literal}
    
    <script type="text/javascript" language="javascript">
	    treeCfg.loader='{$gui->ajaxTree->loader}';
	    treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape}';
	    treeCfg.root_id={$gui->ajaxTree->root_node->id};
	    treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
	    treeCfg.root_testlink_node_type='{$gui->ajaxTree->root_node->testlink_node_type}';
	    treeCfg.enableDD='{$gui->ajaxTree->dragDrop->enabled}';
	    treeCfg.dragDropBackEndUrl='{$gui->ajaxTree->dragDrop->BackEndUrl}';
	    treeCfg.cookiePrefix='{$gui->ajaxTree->cookiePrefix}';
	    treeCfg.useBeforeMoveNode='{$gui->ajaxTree->dragDrop->useBeforeMoveNode}';
    </script>
    
    <script type="text/javascript" src='gui/javascript/treebyloader.js'></script>

	<script type="text/javascript" language="javascript">
		var req_spec_manager_url = '{$gui->req_spec_manager_url}';
		var req_manager_url = '{$gui->req_manager_url}';
	</script>
</head>

<body>
<h1 class="title">{$gui->tree_title}</h1>
<div style="margin: 3px;">
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
</div>

<div id="tree" style="overflow:auto; height:400px;border:1px solid #c3daf9;"></div>
</body>
</html>