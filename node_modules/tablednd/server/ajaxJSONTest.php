The server says: your row order was<br/>
<?php
$result = json_decode(file_get_contents('php://input'), true);
show_results($result, "table-7");
function show_results($result, $id, $indent = null) {
    foreach($result[$id] as $value) {
        echo "$indent$value<br/>";
        if (isset($result["$value"]))
            show_results($result, $value, $indent.implode('&nbsp;', array_fill(0, 12, '')));
    }
}
?>
See the <a href="server/ajaxJSONTest_php.html" target="_BLANK">PHP Source</a><br/>
