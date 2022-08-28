{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource DataTables.inc.tpl
Purpose: smarty template


 each piece of path provides indication of:
 - the configuration choices
 - version of each addon/plugin
 
 jszip-2.5.0         ---> JSZip
 dt-1.10.20          ---> DataTable version
 af-2.3.4            ---> AutoFill
 b-1.6.1             ---> Buttons
 b-colvis-1.6.1      ---> Column Visibility
 b-html5-1.6.1       ---> HMTL5 export
 b-print-1.6.1       ---> Print view
 cr-1.5.2            ---> Column Reorder
 fc-3.3.0            ---> Fixed Colums
 fh-3.1.6            ---> Fixed Header
 kt-2.5.1            ---> Key Table
 r-2.2.3             ---> Responsive
 rg-1.1.1            ---> Row Grouping
 rr-1.2.6            ---> Row Reorder
 sc-2.0.1            ---> Scroller
 sl-1.3.1            ---> Select 

*}
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/jszip-2.5.0/dt-1.10.20/af-2.3.4/b-1.6.1/b-colvis-1.6.1/b-html5-1.6.1/b-print-1.6.1/cr-1.5.2/fc-3.3.0/fh-3.1.6/kt-2.5.1/r-2.2.3/rg-1.1.1/rr-1.2.6/sc-2.0.1/sl-1.3.1/datatables.min.css"/>
 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs/jszip-2.5.0/dt-1.10.20/af-2.3.4/b-1.6.1/b-colvis-1.6.1/b-html5-1.6.1/b-print-1.6.1/cr-1.5.2/fc-3.3.0/fh-3.1.6/kt-2.5.1/r-2.2.3/rg-1.1.1/rr-1.2.6/sc-2.0.1/sl-1.3.1/datatables.min.js"></script>


<script type="text/javascript" 
  src="{$basehref}third_party/DataTables.mjhasbach/dataTables.conditionalPaging.js"></script>

<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
  $('{$DataTablesSelector}').DataTable(
    { "lengthMenu": [ {$DataTableslengthMenu} ],
      "stateSave": true, 
      "conditionalPaging": true
    });
} );
</script>