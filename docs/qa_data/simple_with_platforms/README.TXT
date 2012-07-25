How to use - updated 20120725

Important notice:
if you use suggested names, you will be able to use all support files provided
without editing them.

1. create test project (suggested name 'Sample One', prefix 'TR') 
2. import testproject-2ts-4tc.xml
3. import platforms => platforms-formulaone-few-cars.xml
4. create test plan (suggested name 'TPLAN WITH PLAT')
5. import testplan-linked-items.xml
	you have to get this feedback:
	Platform Ferrari has been linked to test plan. : OK
	Platform Mc Laren has been linked to test plan. : OK
	Platform Red Bull has been linked to test plan. : OK
	Test Case with external id 1 version 1 has been linked to Test Plan for Platform Ferrari : OK
	Test Case with external id 2 version 1 has been linked to Test Plan for Platform Mc Laren : OK
	Test Case with external id 2 version 1 has been linked to Test Plan for Platform Red Bull : OK
	Test Case with external id 3 version 1 has been linked to Test Plan for Platform Mc Laren : OK	
	Test Case with external id 4 version 1 has been linked to Test Plan for Platform Ferrari : OK
	Test Case with external id 4 version 1 has been linked to Test Plan for Platform Mc Laren : OK

6. Create MANUALE a build (suggested name '1.0')
7. Now go to execute feature and import results from files, in following order:
   results-step-01-redbull.xml
   results-step-02-mclaren.xml
   results-step-03-ferrari.xml

You can use this enviroment, to check info provided on pdf files present on this folder.