{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: req_spec_order.tpl,v 1.1 2007/04/15 10:59:18 franciscom Exp $
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

function saveMyTree_byForm(form_id)
{
  var f=document.getElementById(form_id);
	f.elements['nodes_order'].value = treeObj.getNodeOrders();
	f.elements['do_req_reorder'].disabled = '';
	f.elements['do_req_reorder'].value = 1;
	f.submit();		

}
</script>
{/literal}
</head>

{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{assign var="tree_id" value="req_tree"}
{lang_get s="drag_and_drop_to_reorder" var="hint_drag_and_drop"}

<body onload="init_drag_drop('{$basehref}','{$tree_id}');">
<h1>{lang_get s="requirement_spec"}{$smarty.const.TITLE_SEP}{$srs_title|escape}</h1>

<div class="workBack">
<h1>{lang_get s='title_change_req_order'}</h1>

<div>	
 	<ul id="{$tree_id}" class="dhtmlgoodies_tree">
		<li id="{$idSRS}" noDrag="true" noSiblings="true" noDelete="true" noRename="true">
		    <a href="dummy#" onclick="return false;">{$srs_title|escape}</a>
    <ul>
		{section name=idx loop=$arrReq}
			<li id="{$arrReq[idx].id}" isLeaf="true" 
			    noRename="true" noDelete="true" noChildren="true">
  				<a href="dummy#" onclick="return false;" title="{$hint_drag_and_drop}">
 					  {$arrReq[idx].req_doc_id|escape} / {$arrReq[idx].title|escape}</a></li>
		{/section}
    </ul>
   </li>
  </ul>
	<form method="post" name="req_spec_order" id="req_spec_order"
	      action="lib/req/reqSpecView.php?idSRS={$idSRS}">
	      
	  <input type="hidden" name="nodes_order">
	  <input type="hidden" name="do_req_reorder" disabled="disabled">
	  
		<div style="padding: 3px;">
			<input type="button" id="btn_do_testsuite_reorder" 
			       name="btn_do_testsuite_reorder" 
			       onclick='saveMyTree_byForm("req_spec_order");'
			       value="{lang_get s='btn_upd'}" />
		       
			<input type="button" name="goback" 
		                     onclick='javascript:history.go(-1);'
		                     value="{lang_get s='btn_cancel'}" />
       
		</div>	
	</form>


</div>

</body>
</html>