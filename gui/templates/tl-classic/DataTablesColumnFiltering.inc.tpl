{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource DataTablesColumnFiltering.inc.tpl

@see https://datatables.net/extensions/fixedheader/examples/options/columnFiltering.html


Template Parameters
  DataTablesLengthMenu:

Global Coupling
  the table id MUST BE item_view

*}
<script>

$(document).ready(function() {
    var pimpedTable = $('#item_view').DataTable( {
        orderCellsTop: true,
        fixedHeader: true,
        lengthMenu: [{$DataTablesLengthMenu}],

        // 20210530 stateSave: true produces weird behaivour when using filter on individual columns
        // stateSave: true,

        // https://datatables.net/reference/option/dom
        "dom": 'lrtip'
    } );

    var state = pimpedTable.state.loaded();

    // Setup - add a text input to each footer cell
    // Clone & append the whole header row
    // clone(false) -> is the solution to avoid sort action when clicking
    $('#item_view thead tr').clone(false).prop("id","column_filters").appendTo( '#item_view thead' );
    $('#item_view thead tr:eq(1) th').each( function (idx) {
        if (typeof  $(this).data('draw-filter') != 'undefined') {
          var title = '';
          var dst = $(this).data('draw-filter');
          switch (dst) {
            case 'regexp':
              title += "regexp";
            break;

            default:
            break;
          }

          var html = '<input type="text" data-search-type="%dst%" placeholder="Filter %title%" %value% style="color: #000000;" />';
          var value='';
          // --------------------------------------------------------------------------------
          // Restore state
          if (state) {
            var colSearchSavedValue = state.columns[idx].search.search;
            if (colSearchSavedValue) {
              value=' value="' + colSearchSavedValue + '" ';
            }
          }
          // -------------------------------------------------------------------------------- 
          $(this).html(html.replace('%dst%',dst).replace('%title%',title).replace('%value%',value));

          $( 'input', this ).on( 'keyup change', function () {
              var use_regexp = false;
              var use_smartsearch = true;
              if ($(this).data('search-type') == "regexp") {
                use_regexp = true;
                use_smartsearch = false;
              }

              if ( pimpedTable.column(idx).search() !== this.value ) {
                  pimpedTable.column(idx)
                             .search( this.value, use_regexp, use_smartsearch )
                             .draw();
              }
          } );
        } else {
          $(this).html( '' );
        }
    } );
} );
</script>
