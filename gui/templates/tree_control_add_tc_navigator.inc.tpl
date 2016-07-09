{*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: inc_tree_control.tpl,v 1.1.2.3 2010/11/22 09:46:26 asimon83 Exp $
 *
 * Shows some buttons which perform actions on the displayed tree.
 * Is included from filter panel template.
 *
 * @author Andreas Simon
 * @internal revisions
 *}

{lang_get var=labels s='expand_tree, collapse_tree, show_whole_spec_on_right_panel'}

<div class="x-panel-body exec_additional_info" style="padding:3px; padding-left: 9px;border:1px solid #99BBE8;">

<input type="button" value="{$labels.expand_tree}" id="expand_tree" name="expand_tree" 
       onclick="tree.expandAll();" style="font-size: 90%;" />

<input type="button" value="{$labels.collapse_tree}" id="collapse_tree" name="collapse_tree" 
       onclick="tree.collapseAll();" style="font-size: 90%;" />

<input type="button" value="{$labels.show_whole_spec_on_right_panel}" id="show_whole_test_spec" name="show_whole_test_spec" 
       onclick="javascript:ETS({$gui->ajaxTree->root_node->id});" style="font-size: 90%;" />

</div>
