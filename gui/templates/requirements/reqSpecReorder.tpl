{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecReorder.tpl,v 1.1 2007/11/27 08:11:12 franciscom Exp $
*}

{assign var="req_module" value=$smarty.const.REQ_MODULE}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{assign var="tree_id" value="req_tree"}
{lang_get s="drag_and_drop_to_reorder" var="hint_drag_and_drop"}

{assign var="req_module" value=$smarty.const.REQ_MODULE}
{assign var="url_args" value="reqSpecView.php"}
{assign var="action_url" value="$basehref$req_module$url_args"}


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

function saveMyTree_byForm(form_id,elem_name)
{
  var f=document.getElementById(form_id);
	f.elements['nodes_order'].value = treeObj.getNodeOrders();
	f.elements[elem_name].disabled = '';
	f.elements[elem_name].value = 1;
	f.submit();		

}
</script>
{/literal}
</head>


<body onload="init_drag_drop('{$basehref}','{$tree_id}');">
<h1>{lang_get s="requirement_spec"}{$smarty.const.TITLE_SEP}{$srs_title|escape}</h1>

<div class="workBack">
<h1>{lang_get s='title_change_req_order'}</h1>

<div>	
 	<ul id="{$tree_id}" class="dhtmlgoodies_tree">
		<li id="{$tproject_id}" noDrag="true" noSiblings="true" noDelete="true" noRename="true">
		    <a href="dummy#" onclick="return false;">{$tproject_name|escape}</a>
    <ul>
		{section name=idx loop=$arrReqSpecs}
			<li id="{$arrReqSpecs[idx].id}" isLeaf="true" 
			    noRename="true" noDelete="true" noChildren="true">
  				<a href="dummy#" onclick="return false;" title="{$hint_drag_and_drop}">
 					  {$arrReqSpecs[idx].title|escape}</a></li>
		{/section}
    </ul>
   </li>
  </ul>
	<form method="post" name="req_spec_order" id="req_spec_order"
	      action="{$action_url}">
	      
	  <input type="hidden" name="nodes_order">
	  <input type="hidden" name="do_reorder" disabled="disabled">
	  
		<div style="padding: 3px;">
			<input type="button" id="btn_do_testsuite_reorder" 
			       name="btn_do_testsuite_reorder" 
			       onclick='saveMyTree_byForm("req_spec_order",'do_reorder');'
			       value="{lang_get s='btn_upd'}" />
		       
			<input type="button" name="goback" 
		                     onclick='javascript:history.go(-1);'
		                     value="{lang_get s='btn_cancel'}" />
       
		</div>	
	</form>


</div>

</body>
</html>