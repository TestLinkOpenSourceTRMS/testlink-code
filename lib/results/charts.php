<h3>
	Overall Metrics
</h3>
<?php
//include charts.php to access the InsertChart function
include "../../third_party/charts/charts.php";
echo InsertChart ( "../../third_party/charts/charts.swf", "../../third_party/charts/charts_library", "overallPieChart.php", 400, 250 );
?>

<h6>
Copyright © 2003-2007, maani.us
</h6>

<h3>Results by Keyword</h3>

<?php
echo InsertChart ( "../../third_party/charts/charts.swf", "../../third_party/charts/charts_library", "priorityBarChart.php", 450, 300 );
?>
<h6>
Copyright © 2003-2007, maani.us
</h6>