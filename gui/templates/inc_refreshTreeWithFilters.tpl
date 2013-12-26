{* Testlink Open Source Project - http://testlink.sourceforge.net/
 * @filesource inc_refreshTreeWithFilters.tpl
 *
 * Does essentially the same as inc_refreshTree.tpl, 
 * but by submitting the form instead of just reloading the frame
 * (this avoids browser popups annoying the user).
 *}

<script type="text/javascript">
parent.frames['treeframe'].document.forms['filter_panel_form'].submit();
</script>
