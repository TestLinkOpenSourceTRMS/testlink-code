<?php
/*
 * This class provides a more generic interface to getting report results
 * 
 * This is currently the only implementation of excel reporting. The class requires PHP5 
 * and it uses the Spreadsheet_Excel_Writer PEAR module.
 * To install the needed PEAR module run: "pear install --alldeps Spreadsheet_Excel_Writer"
 * 
 * @author Asiel Brumfield <asielb@users.sourceforge.net>
 */
//@TODO, schlundus, delete if not needed
 //require_once('../functions/builds.inc.php');
 require_once('Spreadsheet/Excel/Writer.php');
 
 class TestPlanResultsObj
 {
     private $testPlanName;
     private $testPlanID;
     private $buildsSelected;
     private $keyword;
     private $owner;
     private $lastStatus;
     private $xls;
     private $componentsSelected;
     private $categories;
     private $tcArray = array();
     private $buildsArray = array();
     private $buildResults;
     private $excelWorkbook;     
     // column numbers for fields returned by report
     private $columnAssignments;
     // row header information should be written to
     private $headerRow = 1;
     
	 // 20061212 - KL - For 1.7 I'd like to use this for it's excel writing purposes
	 // for now - just create the excelWorkbook object
	 // $tpName,$tpID, $buildsSelected, $keyword, $owner, $lastStatus, $xls, $componentsSelected
     function __construct()
     {
		/**
         $this->testPlanName = $tpName;
         $this->testPlanID = $tpID;
         $this->buildsSelected = $buildsSelected;
         $this->keyword = $keyword;
         $this->owner = $owner;
         $this->lastStatus = $lastStatus;
         $this->xls = $xls;
         $this->componentsSelected = $componentsSelected;
         $this->columnAssignments = array("component" => 0, "category" => 1, "tcid" => 2, "tctitle" => 3 );
         */
		 
		
		 $this->excelWorkbook = new Spreadsheet_Excel_Writer();
		 
     }     
     
	 /** 
	 * 20061212 - KL - deprecated in 1.7 (for now)
	 * 
     private function getTestCasesAndInfo()
     {
         // TODO: support keyword, owner, and last result        
         $componentsList = implode(",", $this->componentsSelected); 
         // fields will be - component, category, tctitle, tcid
         $sql = "SELECT mcomp.name AS component, mcat.name AS category, t.title AS tctitle, t.id AS tcid " .
                "FROM testcase t, mgttestcase mtc, mgtcategory mcat, mgtcomponent mcomp, project p " .
                "WHERE t.mgttcid=mtc.id AND mtc.catid=mcat.id AND mcat.compid=mcomp.id AND " .
                "mcomp.id IN($componentsList) AND p.id=$this->testPlanID ORDER BY component";
        $result = do_mysql_query($sql);
        if(!$result)
        {
            echo "<br />ERROR: " . mysql_error();
        }
        else
        {
            while($row = mysql_fetch_assoc($result))
            {
                $this->tcArray[] = $row;
            }
            
            $this->builds = $this->getBuildsForTestPlan();
            $this->buildResults = $this->getResultsForBuilds($this->tcArray, $this->builds);
        }            
     }
     
     // get all the builds for the test plan
     private function getBuildsForTestPlan()
     {
         $buildsSelected = implode(",", $this->buildsSelected);
         $sql = "SELECT id, name FROM build WHERE projid=$this->testPlanID AND id IN($buildsSelected)";
         $result = do_mysql_query($sql);
         $builds = array();
         while($row = mysql_fetch_assoc($result))
         {
             $builds[] = $row;
         }         
         return $builds;         
     }
     
     // gets results for each testcase 
     private function getResultsForBuilds($tcArray, $buildsArray)
     {
        $buildsList = array();
        for( $i=0; $i<count($buildsArray); $i++ )
        {
            $buildsList[] = $buildsArray[$i]['id'];
        }
        
        $buildsList = implode(",", $buildsList);
        $sql = "SELECT tcid,status,build_id FROM results WHERE build_id IN($buildsList)";
        $result = do_mysql_query($sql);
        $tcResults = array();
        while($row = mysql_fetch_assoc($result))
        {
            $tcResults[] = $row;
        }
        return $tcResults;                     
     }
     
     private function getResultsForTCID($tcid)
     {
         $results = array();
         foreach($this->buildResults as $item)
         {
            if($tcid == $item["tcid"])
            {
                $results[] = $item;
            }
         }
         return $results;
     }
     
     private function matchResultsWithBuilds($results)
     {      
         $fullResult = array();
         foreach($this->getBuildsForTestPlan() as $build)
         {
            foreach($results as $result)
            {
                // we have a match
                if($build["id"] == $result["build_id"])
                {
                    $fullResult[$build["id"]] = $result["status"];
                }
                else
                {
                    // it is proabably a good idea to eventually allow the user to 
                    // specify what they want to show for not run
                    //$fullResult[$build["id"]] = "n";
                }
            }
         }
         return $fullResult;
     }
     
	 */
	 // last thing that gets called
     public function createExcelFile($fileName)
     {                  
         $this->excelWorkbook->send($fileName);
         $worksheet =& $this->excelWorkbook->addWorksheet('testlink report');                  
         $this->inputSheetData($worksheet);         
         $this->excelWorkbook->close();
     }
     
	 // insert data into worksheet - this is where more work is done
     private function inputSheetData($worksheet)
     {
         $this->getTestCasesAndInfo();
         
         foreach($this->tcArray as $key => $item)
         {     
            // add an additional 1 because of 0 start in excelWriter
            $headerOffsetRow = $key + $this->headerRow +1;              
            $this->formatRow($headerOffsetRow, $worksheet, $item["component"], $item["category"], $item["tcid"], $item["tctitle"]);
         }         
     }         
     
     private function formatHeader($worksheet)
     {         
        $formatHeader =& $this->excelWorkbook->addFormat();
        $formatHeader->setBold();
        $formatHeader->setFgColor('yellow');
        
         foreach($this->columnAssignments as $key => $val)
         {
             // somewhat confusing since the key is really the value in this case within $this->columnAssignments 
             $worksheet->write($this->headerRow, $val, $key, $formatHeader);
         }
         
         $this->formatBuildHeader($worksheet, $formatHeader);
     }
     
     private function formatBuildHeader($worksheet, $format)
     {
         $column = sizeof($this->columnAssignments);
         foreach($this->getBuildsForTestPlan() as $build)
         {
             $buildStr = $build["id"] . ": " . $build["name"]; 
             $worksheet->write($this->headerRow, $column, $buildStr, $format);
             ++$column;
         }
     }     
     
     // should also overload the function for...
     //private function formatRow($rowNum, $worksheet, $itemArray)
     
     private function formatRow($rowNum, $worksheet, $component, $category, $tcid, $tctitle)
     {
        $this->formatHeader($worksheet);
        $this->formatComponent($worksheet, $component, $rowNum, $this->columnAssignments["component"]);
        $this->formatCategory($worksheet, $category, $rowNum, $this->columnAssignments["category"]);
        $this->formatTCID($worksheet, $tcid, $rowNum, $this->columnAssignments["tcid"]);
        $this->formatTCTitle($worksheet, $tctitle, $rowNum, $this->columnAssignments["tctitle"]);
        $this->formatBuildResults($worksheet, $rowNum, $tcid);
     }
     
     private function formatComponent($worksheet, $compName, $rowNum, $colNum)
     {
         $worksheet->write($rowNum, $colNum, $compName);
     }
     
     private function formatCategory($worksheet, $catName, $rowNum, $colNum)
     {
         $worksheet->write($rowNum, $colNum, $catName);
     }
     
     private function formatTCID($worksheet, $tcid, $rowNum, $colNum)
     {
         $worksheet->write($rowNum, $colNum, $tcid);
     }
     
     private function formatTCTitle($worksheet, $tcTitle, $rowNum, $colNum)
     {
         $worksheet->write($rowNum, $colNum, $tcTitle);
     }
     
     private function formatBuildResults($worksheet, $rowNum, $tcid)
     {         
         $results = $this->matchResultsWithBuilds($this->getResultsForTCID($tcid));
         
         $column = sizeof($this->columnAssignments);
         foreach($this->getBuildsForTestPlan() as $build)
         {
             $build = $build["id"];              
             $worksheet->write($rowNum, $column, $results[$build]);
             ++$column;
         }         
     }     
 }
 
?>
