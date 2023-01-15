{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource DataTables.inc.tpl
@param DataTablesSelector
@param DataTablesLengthMenu



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

{* 
   this links has been generated using 
   https://datatables.net/download/index

   - styling framework
   DataTable

*}

{*
 @20220828 
 each piece of path provides indication of:
 - the configuration choices
 - version of each addon/plugin
 
 dt-1.10.20          ---> DataTable version
 
 af-2.3.4            ---> AutoFill
 b-1.6.1             ---> Buttons
 b-colvis-1.6.1      ---> Column Visibility
 b-html5-1.6.1       ---> HMTL5 export
 b-print-1.6.1       ---> Print view
 cr-1.5.2            ---> Column Reorder
 fc-3.3.0            ---> Fixed Colums
 fh-3.1.6            ---> Fixed Header
 jszip-2.5.0         ---> JSZip
 kt-2.5.1            ---> Key Table
 r-2.2.3             ---> Responsive
 rg-1.1.1            ---> Row Grouping
 rr-1.2.6            ---> Row Reorder
 sc-2.0.1            ---> Scroller
 sl-1.3.1            ---> Select 


Examples of styling

styling framework -> Bootstrap 3.3.7
https://cdn.datatables.net/v/bs/dt-1.12.1/datatables.min.css -> /v/bs in url means BootStrap styling

styling framework -> Bootstrap 4.6.0
https://cdn.datatables.net/v/bs4/dt-1.12.1/datatables.min.css -> /v/bs4 in url means BootStrap styling

styling framework -> Bootstrap 5.1.3
https://cdn.datatables.net/v/bs5/dt-1.12.1/datatables.min.css -> /v/bs5 in url means BootStrap styling

styling framework -> DataTables' default styling -> v1.12.1
https://cdn.datatables.net/v/dt/dt-1.12.1/datatables.min.css -> /v/dt in url means DataTable  styling


ATTENTION changes in sort icons on 1.12.0 and +
Sorting icons now are UTF-8 character based rather than using external images and are consistent across the different styling libraries

*}

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/fc-4.1.0/fh-3.2.4/kt-2.7.0/r-2.3.0/rg-1.2.0/rr-1.2.8/sc-2.0.7/sl-1.4.0/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/fc-4.1.0/fh-3.2.4/kt-2.7.0/r-2.3.0/rg-1.2.0/rr-1.2.8/sc-2.0.7/sl-1.4.0/datatables.min.js"></script>

<script type="text/javascript" 
  src="{$basehref}third_party/DataTables.mjhasbach/dataTables.conditionalPaging.js"></script>


{if $DataTablesSelector != ''}
  {* 
    To avoid issues due to do initialization multiple times 
    See projectView.tpl.
  *}

  <script type="text/javascript" language="javascript" class="init">
  $(document).ready(function() {
    $('{$DataTablesSelector}').DataTable(
      { "lengthMenu": [ {$DataTablesLengthMenu} ],
        "stateSave": true, 
        "conditionalPaging": true
      });
  } );
  </script>
{/if}  