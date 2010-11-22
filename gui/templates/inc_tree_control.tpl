{*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: inc_tree_control.tpl,v 1.1 2010/11/22 09:15:57 asimon83 Exp $
 *
 * Shows some buttons which perform actions on the displayed tree.
 * Is included from filter panel template.
 *
 * @author Andreas Simon
 * @internal revision
 *}

{lang_get var=labels s='expand_tree, collapse_tree'}

<div>

<input type="button"
       value="{$labels.expand_tree}" 
       id="expand_tree" 
       name="expand_tree"
       onclick="tree.expandAll();"
       style="font-size: 90%;" />

<input type="button"
       value="{$labels.collapse_tree}"
       id="collapse_tree"
       name="collapse_tree"
       onclick="tree.collapseAll();"
       style="font-size: 90%;" />

</div>
