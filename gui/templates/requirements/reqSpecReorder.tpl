{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecReorder.tpl,v 1.3 2008/03/09 18:38:18 franciscom Exp $
*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get s="drag_and_drop_to_reorder" var="hint_drag_and_drop"}

{assign var="req_module" value='lib/requirements/'}
{assign var="url_args" value="reqSpecEdit.php?do_action=do_reorder"}
{assign var="action_url" value="$basehref$req_module$url_args"}

{assign var="tree_id" value="req_tree"}
{include file="inc_head.tpl" openHead="Yes"}
{include file="drag_drop.inc.tpl"}
</head>


<body onload="init_drag_and_drop('{$basehref}','{$tree_id}');">
<h1>{lang_get s="testproject"}{$smarty.const.TITLE_SEP}{$tproject_name|escape}</h1>

<div class="workBack">
<h1>{lang_get s='title_change_req_spec_order'}</h1>

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
  {assign var="form_id" value="items_order_mgmt"}
	<form method="post" name="{$form_id}" id="{$form_id}" action="{$action_url}">
	      
	  <input type="hidden" name="nodes_order">
	  <input type="hidden" name="do_reorder" disabled="disabled">
	  
		<div style="padding: 3px;">
			<input type="button" id="btn_save" 
			       name="btn_save" 
			       onclick="dnd_save_tree('{$form_id}','nodes_order','do_reorder');"
			       value="{lang_get s='btn_save'}" />
		</div>	
	</form>


</div>

</body>
</html>