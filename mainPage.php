<?php

////////////////////////////////////////////////////////////////////////////////
//File:     mainPage.php
//Author:   Chad Rosen
//Purpose:  This file is the first page that the user sees when they log in.
//	    Most of the code in it is html but there is some logic that displays
//	    based upon the login. There is also some javascript that handles the
//	    form information.
////////////////////////////////////////////////////////////////////////////////

//Starting a session

require_once("functions/header.php");
testLinkPageStart();

//print_r($_SESSION);

require_once("functions/getRights.php");

?>

<script language='javascript' src='functions/popupHelp.js'></script>

<?

$planLinkTag = "FAKE"; //initializing planLinkTag variable

if(has_rights("mgt_view_product"))//if user can view products
{

echo <<<END
<table width="100%" border="0" align="center">
<form NAME="productForm" ACTION="">
<tr>
<td height="124" valign="top"><table width="100%" class="mainTable" align="center">
<tr>
<td width="34%" class="mainHeader myproduct"><img align=top src=icons/sym_question.gif onclick=javascript:open_popup('help/mainProduct.php');>Product
END;
 

		//This is the code that displays the select box with all the available projects

		

		$queryString = "select distinct id,name from mgtproduct";

		$result = mysql_query($queryString,$db);

		if ($result) {
			$productCount = mysql_num_rows($result);
		} else {
			$productCount = 0;
		}

		if($productCount > 0)
		{

			//echo "<SELECT NAME='product' onchange=\"this.form.submit();\">;";

			$cProductsDisplayed = 0;
				
			while ($myrow = mysql_fetch_row($result))
			
			{
				// If no session project has been set yet in the session,
				// then set it.
				if ($cProductsDisplayed == 0 && !$_SESSION['product']) {
					$_SESSION['product'] = $myrow[0];
				}
				
				//This code block checks to see if the user has already selected a project once before
				//and sets the default to that.. Bug 11453
				
				if($myrow[0] == $_SESSION['product'])
				{

					$optionProd .= "<OPTION VALUE='" . $myrow[0] ."' SELECTED>" . $myrow[1] . "</option>";

				}
				else
				{
				
					$optionProd .= "<OPTION VALUE='" . $myrow[0] ."'>" . $myrow[1] . "</option>";
				
				}

				$cProductsDisplayed ++;

			}//END WHILE
			
			//I changed the way this works so that now if there are no options I display nothing
			//I think this makes the UI more clear
			
			if($optionProd != "")
			{

			echo "<SELECT NAME='product' onchange=\"this.form.submit();\">";
			echo $optionProd;
			echo "</select>";

			}


		}

		//check to see how many products are available.. If there are no products then only want to show certain links. This is the same way the test plans are handled

			if ($cProductsDisplayed == 0) {
				$prodLinkTag = "FAKE";
			} else {
				$prodLinkTag = "A";
			}






echo "</td>";
echo "</form>";
echo "</table>";
 
      

if(has_rights("mgt_view_tc") || has_rights("mgt_modify_tc")) //user can view tcs or modify them
{

echo <<<END

		<table width="34%" align="left" class=subTable>
        
		<tr>
          <td class="mainSubHeader">Test Case Management</td>
        </tr>
		
		<tr bordercolor="#000000">
          <td class='mainMenu'><img src="icons/arrow_org.gif" width="16" height="9"><$prodLinkTag href="manage/archiveFrameSet.php?nav= > Test Case Management">Create/Edit/Delete
              Test Cases</a></td>
        </tr>
        <tr bordercolor="#000000">
          <td class='mainMenu'><img src="icons/arrow_org.gif" width="16" height="9"><$prodLinkTag href="manage/search/searchFrameSet.php?nav= > Search Test Cases">Search
              Test Cases</a></td>
        </tr>


END;

	if(has_rights("mgt_modify_tc")) //users can modify tcs
	{	

echo <<<END

	    <tr bordercolor="#000000">
         <td height="18" class='mainMenu'><img src="icons/arrow_org.gif" width="16" height="9"><$prodLinkTag href="print/printFrameSet.php?type=product&nav= > Print Product Test Cases">Print Product Test Cases</a> </td>
        </tr>
END;

	}else
	{

		echo "<tr><td class='mainMenu'>&nbsp;</td></tr>";


	}


echo "</table>";

}

//Keyword Management Section			

if(has_rights("mgt_view_key") || has_rights("mgt_modify_key"))//user can view keys or modify them
{

echo <<<END

	     <table width='33%' align='left' class=subTable>
        <tr>
          <td class="mainSubHeader">Keyword Management</td>
       </tr>

	<tr><td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'><$prodLinkTag href="manage/keyword/viewKeywords.php?type=product&nav= > View Keywords">View Keywords</a></td></tr>



END;

if(has_rights("mgt_modify_key"))//user can modify keys
	{

echo <<<END

	<tr><td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'><$prodLinkTag href='manage/newEditKeywords.php?type=product&nav= > Create/Edit/Delete Keywords'>Create/Edit/Delete Keywords</a></td></tr>
				
	<tr><td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'><$prodLinkTag href="manage/keyword/keywordFrameSet.php?type=product&nav= > Batch Keyword Assign">Assign Keywords To Multiple Cases</a></td></tr>
	
END;




	}else //if they cant modify keys show two blank rows to fill out the whole table
	{

		echo "<tr><td class='mainMenu'>&nbsp</td></tr>";
		echo "<tr><td class='mainMenu'>&nbsp;</td></tr>";



	}

echo "</table>";


}
			 

			
//Product Mangement Section

if(has_rights("mgt_modify_product"))//can the user modify products
{

echo <<<END

<table width='33%' class=subTable align='left'>
<tr>
<td class='mainSubHeader'>Product Management</td>
</tr>
<tr> 
<td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'>
<a href='admin/product/newProduct.php?nav= > New Products'>Create new Products</a></td>
</td>
</tr>
<tr> 
<td class='mainMenu'> <img src='icons/arrow_org.gif' width='16' height='9'>
<$prodLinkTag href='admin/product/editProduct.php?type=product&nav= > Edit/Delete Product'>Edit/Delete Product</a></td>
</td>
</tr>
<tr>
<td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'> 
		  
<$prodLinkTag href='admin/product/importProduct.php?nav= > Import Products'>Import Product Data from CSV</a>
			
</td>
</tr>
			  
</table>

END;

/*			  echo "<table width='33%' class=subTable align='left'>";
              echo "<tr>"; 
              echo "<td class='mainSubHeader'>Product Management</td>";
              echo "</tr>";
              echo "<tr> ";
              echo "<td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'>";
		echo "<a href='admin/product/newProduct.php?nav= > New Products'>Create new Products</a></td>";
              echo "</td>";
              echo "</tr>";
              echo "<tr> ";
              echo "<td class='mainMenu'> <img src='icons/arrow_org.gif' width='16' height='9'>";
		echo "<a href='admin/product/editProduct.php?type=product&nav= > Edit/Delete Product'>Edit/Delete Product</a></td>";
              echo "</td>";
              echo "</tr>";
              echo "<tr>"; 
              echo "<td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'>"; 
		  
			  echo "<a href='admin/product/importProduct.php?nav= > Import Products'>Import Product Data from CSV</a>";
			
              echo "</td>";
              echo "</tr>";
			  
              echo "</table>";*/
			
			
			}else //if not show a blank table
			{
				
				echo "<table width='33%' align='left' class=subTable>";

				echo "<tr><td class='mainSubHeader'>&nbsp</td></tr>";
				
				echo "<tr><td class='mainMenu'>&nbsp;</td></tr>";
				echo "<tr><td class='mainMenu'>&nbsp;</td></tr>";
				echo "<tr><td class='mainMenu'>&nbsp;</td></tr>";

				
				
				echo "</table>";




			}
			

}

?>


<!--Begin the Test Plan Section-->



      </table>
    </td>
  </tr>
</table>
<table width="100%" border="0" align="center">
<FORM NAME='projectForm' ACTION=''>
  <tr>
    <td height="103" valign="top"><table width="100%" class='mainTable'>
      <tr>
        <td height="20" class="mainHeader myproject">
            <img align=top src=icons/sym_question.gif onclick=javascript:open_popup('help/mainTestPlan.php');>Test Plan
                <?php
			
		//This is the code that displays the select box with all the available projects

		
		$queryString = "select distinct id,name from project,projrights where active='y'";

		$result = mysql_query($queryString,$db);

		if ($result) {
			$testplanCount = mysql_num_rows($result);
		} else {
			$testplanCount = 0;
		}

		if($testplanCount > 0)
		{

			//echo "<SELECT NAME='project' onchange=\"this.form.submit();\">;";

			$cAvailablePlans = 0;  // count the available plans
			
			while ($myrow = mysql_fetch_row($result))
			
			{
				

				//Block of code will determines if the user has the appropriate rights to view available projects

				$sqlProjRights = "select projid from projrights where userid=" . $_SESSION['userID'] . " and projid=" . $myrow[0];
				$projRightsResult = mysql_query($sqlProjRights);
				$myrowProjRights = mysql_fetch_row($projRightsResult);
				
				//If the user has the rights to the project show it

				if($myrowProjRights[0] == $myrow[0])
				{
					
					//This code block checks to see if the user has already selected a project once before
					//and sets the default to that.. Bug 11453

					// If this is the first plan we're displaying,
					// and no session project has been set yet,
					// then set it.
					if ($cAvailablePlans == 0 && !$_SESSION['project']) {
						$_SESSION['project'] = $myrow[0];

						//echo "set";
					}
					
					$cAvailablePlans++;
					
					if($myrow[0] == $_SESSION['project']) //did I choose this selection last
					{
						$optionProj .= "<OPTION VALUE='" . $myrow[0] ."' SELECTED>" . $myrow[1] . "</option>";
					}

					else //Else just display the value
					{
						$optionProj .= "<OPTION VALUE='" . $myrow[0] ."'>" . $myrow[1] . "</option>";
				
					}
				}
			}//END WHILE

			//I changed the way this works so that now if there are no options I display nothing
			//I think this makes the UI more clear

			if($optionProj != "")
			{

			echo "<SELECT NAME='project' onchange=\"this.form.submit();\">;";
			echo $optionProj;
			echo "</select>";

			}

			//I only want the hyperlinks to show up if the user has the propper rights or if there
			//are available projects

			if ($cAvailablePlans == 0) {
				$planLinkTag = "FAKE";
			} else {
				$planLinkTag = "A";
			}

		}//end testplan count

		
        echo "</td>";
        echo "</form>";
        echo "</table>";

if(has_rights("tp_execute") || has_rights("tp_create_build")) //if the user has either execute or create build rights
{

echo <<<END
	  
	  <table width="34%" align="left" class=subTable>
        <tr>
          <td class="mainSubHeader">Test Case Execution</td>
        </tr>
        <tr bordercolor="#000000">
          <td class='mainMenu'><img src="icons/arrow_org.gif" width="16" height="9"><$planLinkTag href="execution/frameSet.php?nav= > Execution&page=detailed">Execute Test Cases</a></td>
        </tr>
        <tr bordercolor="#000000">
          <td bordercolor="#000000" class='mainMenu'><img src="icons/arrow_org.gif" width="16" height="9"><$planLinkTag href="print/printFrameSet.php?type=project&nav= > Print Test Cases&page=detailed");>Print
              Test Plan Test Cases</a></td>
        </tr>

END;

	if(has_rights("tp_create_build"))//if the user has create build rights
	{

echo <<<END
		  
<td bordercolor='#000000' class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'><$planLinkTag href="admin/build/createBuild.php?type=project&nav= > Create Build">Create New Build</a>

<tr><td class='mainMenu'>&nbsp;</td></tr>
<tr><td class='mainMenu'>&nbsp;</td></tr>
<tr><td class='mainMenu'>&nbsp;</td></tr>

		
</td>
</tr>

END;



	}


echo "</table>";

}else //If the user does not have permissions to see any of the execution pages then show a blank table
{

echo <<<END
	  
	  <table width="34%" align="left" class=subTable>
        <tr>
          <td class="mainSubHeader">&nbsp;</td>
        </tr>
		  <tr><td class='mainMenu'>&nbsp;</td></tr>
<tr><td class='mainMenu'>&nbsp;</td></tr>
		  </table>

END;


}



if(has_rights("tp_metrics") || has_rights("tp_planning"))//user has metrics and planning rights
{

echo <<<END

<table width="33%" class=subTable align="left">
<tr>
<td class="mainSubHeader">Execution Status</td>
</tr>

<tr bordercolor='#000000'>
		        
<td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'><$planLinkTag href="metrics/metricsFrameSet.php?nav= > Test Plan Metrics">View Metrics</a></td></tr>


END;

	if(has_rights("tp_planning"))//user has planning rights
	{

echo <<<END

	  
	 <tr bordercolor='#000000'><td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'><$planLinkTag href="admin/category/frameSet.php?nav= > Risk/Priority">Assign 
            Risk and Ownership</a></td></tr>
			
	<tr><td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'><$planLinkTag href="metrics/newEditMilestone.php?type=project&nav= > Manage Milestones">Create/Edit/Delete 
			Milestones</a></td</tr>
	
	<tr><td class='mainMenu'>&nbsp;</td></tr>
	<tr><td class='mainMenu'>&nbsp;</td></tr>
		
	<tr><td class='mainMenu'>&nbsp;</td></tr>
	
	</table>

	<table class=subTable width='33%' align='left'>
            <tr>
  			<td class='mainSubHeader'>Test Plan Management</td>
            </tr>
            <tr bordercolor='#000000'>
            <td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'>
          
		  
		    <$planLinkTag href="manage/importFrameSet.php?nav= > Import Data Into Test Plan">Import (SmartLink) Into a Test Plan</a>
			
			</td>
            </tr>
            <tr bordercolor='#000000'>
            <td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'>
			
			<$planLinkTag href="admin/TC/editFrameSet.php?nav= > Delete Test Cases">Delete Test Cases</a>
			
			</td>
            </tr>
			
			<tr bordercolor='#000000'>
            <td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'>
			
			<$planLinkTag href="admin/TC/viewModified.php?nav= > View Modified">View Modified Test Cases</a>
			
			</td>
            </tr>

			<tr bordercolor='#000000'>
            <td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'>
		  
		  	<a href="admin/project/newProject.php?nav= > Create Test Plans">Create Test Plans</a>
			
			</td>
            </tr>

			<tr bordercolor='#000000'>
            <td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'>
		  
		  	<a href="admin/project/editDeleteProject.php?nav= > Edit/Delete Test Plans">Edit/Delete 
            Test Plans</a>
			
			</td>
            </tr>

			
			<tr bordercolor='#000000'>
            <td class='mainMenu'><img src='icons/arrow_org.gif' width='16' height='9'>
		  
		  	<a href="admin/user/projectRights.php?nav= > Define User/Project Rights">Define User/Project Rights</a>
			
			</td>
            </tr>
END;


}else

{

//if the user doesnt have planning rights show this blank table


echo <<<END
		<tr><td class='mainMenu'>&nbsp</td></tr>
		</table>		

	     <table width='33%' align='left' class=subTable>
        <tr>
       <td class="mainSubHeader">&nbsp</td>
       </tr>

	<tr><td class='mainMenu'>&nbsp</td></tr>

	<tr><td class='mainMenu'>&nbsp</td></tr>


END;


}//end else

echo "</table>";



}//end metrics and tp planning section


?>

<!--Code that cleans up the big table all of the smaller tables reside in-->

</td>
</tr>
</table>

</body>
</html>

<?php

?>
