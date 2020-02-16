{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqReorder.tpl,v 1.6 2008/09/25 10:33:11 franciscom Exp $

rev: 20080924 - franciscom
     BUGID 1728
*}

{lang_get var="labels"
          s="req_spec,title_change_req_order,btn_save"}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get s="drag_and_drop_to_reorder" var="hint_drag_and_drop"}

{assign var="req_module" value='lib/requirements/'}
{assign var="url_args" value="reqEdit.php?doAction=doReorder"}
{assign var="action_url" value="$basehref$req_module$url_args"}

{assign var="tree_id" value="req_tree"}
{include file="inc_head.tpl" openHead="Yes"}
{include file="drag_drop.inc.tpl"}
</head>


<body onload="init_drag_and_drop('{$basehref}','{$tree_id}');">
<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
<h1 class="title">{$labels.title_change_req_order}</h1>

<div>
 	<ul id="{$tree_id}" class="dhtmlgoodies_tree">
		<li id="{$gui->req_spec_id}" noDrag="true" noSiblings="true" noDelete="true" noRename="true">
		    <a href="dummy#" onclick="return false;">{$gui->req_spec_name|escape}</a>
	   		<ul>
			{section name=idx loop=$gui->all_reqs}
				<li id="{$gui->all_reqs[idx].id}" isLeaf="true"
				    noRename="true" noDelete="true" noChildren="true">
	  				<a href="dummy#" onclick="return false;" title="{$hint_drag_and_drop}">
	 					  {$gui->all_reqs[idx].title|escape}</a></li>
			{/section}
	    	</ul>
  		 </li>
 	</ul>
  {assign var="form_id" value="items_order_mgmt"}
	<form method="post" name="{$form_id}" id="{$form_id}" action="{$action_url}">

    {* BUGID 1728 *}
    <input type="hidden" name="req_spec_id" id="req_spec_id" value="{$gui->req_spec_id}">
		<input type="hidden" name="nodes_order" />
		<input type="hidden" name="doReorder" disabled="disabled" />
	
		<div style="padding: 3px;">
			<input type="button" id="btn_save"
				       name="btn_save"
				       onclick="dnd_save_tree('{$form_id}','nodes_order','doReorder');"
				       value="{$labels.btn_save}" />
		</div>
	</form>

</div>
</div>
</body>
</html>