<html>

<head>

<title>Metrics</title>

</head>

<body>

<? include("helpHeader.php"); ?>

<table width=100% class=class=tctable>

<tr><td class=navtable><b>View Project Status Across All Builds:</b></td></tr>

<tr><td>This page displays the entire status of the project across all builds. <br><br>This means that if a test case passes in build 1 and then fails in build 2 it will be counted as a failure on this page.</td></tr>

</table><br>

<table width=100% class=class=tctable>

<tr><td class=navtable><b><b>View Status by an Individual Build:</b></td></tr>

<tr><td>This page displays the results of testing against the selected build.</td></tr>

</table><br>


<table width=100% class=class=tctable>

<tr><td class=navtable><b>View The Overall Build Status:</b></td></tr>

<tr><td>This page displays overall project status for each build.</td></tr>

</table><br>

<table width=100% class=class=tctable>

<tr><td class=navtable><b>View Status By Individual Test Cases:</b></td></tr>

<tr><td>This page displays each test case and its result against every build</td></tr>

</table><br>

<table width=100% class=class=tctable>

<tr><td class=navtable><b>Blocked Test Cases:</b></td></tr>

<tr><td>This page will show a rollup of all of the currently blocked test cases.

<br>EX: A test case will show up if it passed in build 1 and then was blocked in build 2

<br><br>EX: If a test case is blocked in build 1 and then passes in build 2 it will not show up</td></tr>

</table><br>

<table width=100% class=class=tctable>

<tr><td class=navtable><b>Failed Test Cases:</b></td></tr>

<tr><td>This page is the same as the blocked page but for failed test cases</td></tr>

</table><br>

<table width=100% class=class=tctable>

<tr><td class=navtable><b>Total Bugs For Each Test Case:</b></td></tr>

<tr><td>This page displays every test case in a project and all of the bugs that were filed against it</td></tr>

</table><br>

<table width=100% class=class=tctable>

<tr><td class=navtable><b>Email Test Plan Info:</b></td></tr>

<tr><td>This page allows users to email the results of an entire project or a particular build to anyone they'd like</td></tr>

</table>

</body>

</html>