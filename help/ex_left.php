<html>

<head>

<title>Filtering Test Cases</title>

</head>

<body>

<b>Filtering Test Cases</b><br>

This table allows the user to filter test cases before they are executed. 

<br><br><b>Ownership:</b><br>

Users can filter test cases by their owner. Ownership is determined at the category level, is determined by leads, and can be changed at the Assign Risk and Ownership page under metrics. 

<br><br>Note: Users outside of Good will only be able to see the test cases delegated to them by their project lead. 


<br><br><b>Keyword:</b><br>

Users can filter test cases by keyword. Keywords are set either using the Create/Edit/Delete Test Cases or by the Assign Keywords To Multiple Cases. Keywords can only be created, edited, or deleted by leads but may be assigned to test cases by testers. 

<br><br><b>Build:</b><br>

Users can filter test cases by builds. Builds are the basic component for how test cases are tracked. Each test case may be run once and only once per build. 

<br><br>Builds can be created by leads using the Create New Build page.


<br><br><b>Result:</b><br>

Users can filter test cases by results. Results are what happened to that test case during a particular build. Test cases can pass, fail, be blocked, or not be run.

<br><br><b>Most Current Result:</b><br>

By default or if the "most current" checkbox is unchecked, the tree will be sorted by the build that is chosen from the dropdown box. In this state the tree will display the test cases status. 

<br><br>Ex: User selects build 2 from the dropdown box and doesn't check the "most current" checkbox. All test cases will be shown with their status from build 2. So, if test case 1 passed in build 2 it will be colored green.

<br><br>If the user decideds to check the "most current" checkbox the tree will be colored by the test cases most recent result.

<br><br>Ex: User selects build 2 from the dropdown box and this time checks the "most current" checkbox. All test cases will be shown with most current status. So, if test case 1 passed in build 3, even though the user has also selected build 2, it will be colored green.


</body>

</html>