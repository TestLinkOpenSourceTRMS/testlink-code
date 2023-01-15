{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource DataTablesColumnFiltering.inc.tpl
@param DataTablesSelector
@param DataTablesLengthMenu

@see https://datatables.net/extensions/fixedheader/examples/options/columnFiltering.html


@since 1.9.20
*}
<script>
$(document).ready(function() {


    // 20210530 
    // stateSave: true produces weird behaivour when using filter on individual columns
    var pimpedTable = $('{$DataTablesSelector}').DataTable( {
        orderCellsTop: true,
        fixedHeader: true,
        lengthMenu: [{$DataTablesLengthMenu}],
        stateSave: true,

        // https://datatables.net/reference/option/dom
        "dom": 'lrtip'
    } );

    var state = pimpedTable.state.loaded();

    // Setup - add a text input to each footer cell
    // Clone & append the whole header row
    // clone(false) -> is the solution to avoid sort action when clicking
    $('{$DataTablesSelector} thead tr').clone(false).prop("id","column_filters").appendTo( '{$DataTablesSelector} thead' );
    $('{$DataTablesSelector} thead tr:eq(1) th').each( function (idx) {

        // Remove class from cloned <th>, to remove sort icons!!
         $(this).removeClass(['sorting','sorting_desc','sorting_asc']);

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
