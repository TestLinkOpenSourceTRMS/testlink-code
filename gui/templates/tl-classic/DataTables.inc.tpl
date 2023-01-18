{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource DataTables.inc.tpl
Purpose: smarty template


Template Parameters
  DataTablesOID:
  DataTablesLengthMenu:

Global Coupling
  NONE
*}


<link rel="stylesheet" type="text/css" href="{$basehref}third_party/{$smarty.const.TL_DATATABLES_DIR}/datatables.min.css"/> 
<script type="text/javascript" src="{$basehref}third_party/{$smarty.const.TL_DATATABLES_DIR}/datatables.min.js"></script>

{if $DataTablesOID != ''}
  {* 
    To avoid issues due to do initialization multiple times 
    See projectView.tpl.
  *}
  <script type="text/javascript" language="javascript" class="init">
  $(document).ready(function() {
    $('#{$DataTablesOID}').DataTable({ "lengthMenu": [ {$DataTablesLengthMenu} ],stateSave: true});
  } );
  </script>
{/if}  