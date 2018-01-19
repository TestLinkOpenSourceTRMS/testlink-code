{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	reqMonitors.tpl
Purpose: 
@internal revisions
@since 1.9.15
*}
{lang_get var='labels' 
          s='monitor_set, login'}

<script type="text/javascript">
uyu = fRoot + 'lib/ajax/requirements/getreqmonitors.php';
uyu = uyu + '?item_id=' + {$gui->req_id};

$(document).ready(function() {
    $('#monitors').DataTable( {
        "paging":   false,
        "searching": false,
        "ajax": {
            "url": uyu
        },
        
        "columns": [
            { "data": "login" },
        ]
    } );
	$('#monitors').DataTable.ajax.load();	
} );


</script>

<table class="simple" id="monitors">
  <thead><tr><th>{$labels.monitor_set}</th></tr></thead>
</table>
