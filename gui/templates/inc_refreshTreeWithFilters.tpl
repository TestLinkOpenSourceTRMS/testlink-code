{* Testlink Open Source Project - http://testlink.sourceforge.net/
 * $Id: inc_refreshTreeWithFilters.tpl,v 1.3 2010/11/13 11:24:25 franciscom Exp $
 *
 * Purpose: smarty include - refresh tree frame after update
 *
 * Does essentially the same as inc_refreshTree.tpl, 
 * but by submitting the form instead of just reloading the frame
 * (this avoids browser popups annoying the user).
 *}
<script type="text/javascript">
	parent.frames['treeframe'].document.forms['filter_panel_form'].submit();
</script>