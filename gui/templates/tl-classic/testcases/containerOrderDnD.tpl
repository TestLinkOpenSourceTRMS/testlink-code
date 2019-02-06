{* TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: containerOrderDnD.tpl,v 1.2 2008/05/06 06:26:12 franciscom Exp $ 
Purpose: smarty template - reorder container contents using JS Drag and drop

rev :
     
*}
{include file="inc_head.tpl" openHead="Yes"}
{include file="drag_drop.inc.tpl"}

{literal}
<script type="text/javascript">
/*
  function: 

  args : 
         basehref: needed to create absolute URL to images
         tree_id : html of ul used to implement tree with drag and drop 
  
  returns: 

*/
function init_drag_drop(basehref,tree_id)
{
	treeObj = new JSDragDropTree(basehref);
	treeObj.setTreeId(tree_id);
	treeObj.setMaximumDepth(7);
	
	// If you want to show a message when maximum depth is reached, i.e. on drop.
	treeObj.setMessageMaximumDepthReached('Maximum depth reached'); 
	treeObj.initTree();
	treeObj.expandAll();
}


function saveMyTree_byForm()
{
	document.containerOrder.elements['nodes_order'].value = treeObj.getNodeOrders();
	document.containerOrder.elements['do_testsuite_reorder'].disabled = '';
	document.containerOrder.submit();		
}
</script>
{/literal}
</head>

{config_load file="input_dimensions.conf" section="containerOrder"} {* Constant definitions *}
{assign var="tree_id" value="tproject_tree"}
{lang_get s="drag_and_drop_to_reorder" var="hint_drag_and_drop"}

<body onload="init_drag_drop('{$basehref}','{$tree_id}');">
<h1 class="title">{lang_get s=$level}{$smarty.const.TITLE_SEP}{$object_name|escape}</h1>

<div class="workBack">
<h1 class="title">{lang_get s='title_change_node_order'}</h1>

<div>	
	{if $arraySelect eq ''}
		{lang_get s='no_nodes_to_reorder'}
	{else}
	
  	<ul id="{$tree_id}" class="dhtmlgoodies_tree">
   		
   		<li id="{$objectID}" noDrag="true" noSiblings="true" noDelete="true" noRename="true">
   		    <a href="#" onclick="return false;">{$object_name|escape}</a>
      <ul>
			{section name=idx loop=$arraySelect}
 	   		{assign var="node_table" value=$arraySelect[idx].node_table}
 					<li id="{$arraySelect[idx].id}"
 					    {if $node_table == 'testcases'} isLeaf="true" {/if}
 					    noRename="true" noDelete="true" noChildren="true">
 					<a href="#" onclick="return false;" title="{$hint_drag_and_drop}">{$arraySelect[idx].name|escape}</a></li>
			{/section}
			</ul>
			
	  </ul>

	<form method="post" name="containerOrder"
	      action="{$basehref}lib/testcases/containerEdit.php?containerID={$objectID}">
	      
	  <input type="hidden" name="nodes_order">
	  <input type="hidden" name="do_testsuite_reorder" disabled="disabled">
	  
		<div style="padding: 3px;">
			<input type="button" id="btn_do_testsuite_reorder" 
			       name="btn_do_testsuite_reorder" 
			       onclick='saveMyTree_byForm();'
			       value="{lang_get s='btn_save'}" />
		       
			<input type="button" name="goback" 
		                     onclick='javascript:history.go(-1);'
		                     value="{lang_get s='btn_cancel'}" />
       
		</div>	
	</form>
	{/if}
</div>

</div>
</body>
</html>