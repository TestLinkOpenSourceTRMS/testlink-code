{* Testlink Open Source Project - http://testlink.sourceforge.net/
 * $Id: inc_refreshTreeWithFilters.tpl,v 1.2 2010/06/24 17:25:52 asimon83 Exp $
 *
 * Purpose: smarty include - refresh tree frame after update
 *
 * Does essentially the same as inc_refreshTree.tpl, 
 * but by submitting the form instead of just reloading the frame
 * (this avoids browser popups annoying the user).
 *}

{literal}
<script type="text/javascript">
	parent.frames['treeframe'].document.forms['filter_panel_form'].submit();
</script>
{/literal}
