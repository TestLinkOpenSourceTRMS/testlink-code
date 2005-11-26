<?
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @version $Id: keywords.inc.php,v 1.10 2005/11/26 13:27:25 schlundus Exp $
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author	Chad Rosen
* 
* Purpose:  Functions for support keywords management. 
* Precondition: require init db 
*
* @author: francisco mancardi - 20051011
* refactoring - new function check_for_keyword_existence()
*
* @author: francisco mancardi - 20051004 
* addNewKeyword() refactoring and improvements
*
* @author: francisco mancardi - 20050810
* deprecated $_SESSION['product'] removed
*/

/** collect all keywords for the product and return as associative array */
function selectKeywords($prodID, $selectedKey = '')
{
	$arrKeywords = null;
	
	if ($prodID)
	{	
	  	//20050827 - scs - added sorting of keyword
	  	$sql = "SELECT id,keyword,notes FROM keywords WHERE prodid = " . $prodID . " ORDER BY keyword ASC";
	  	$result = do_mysql_query($sql);
	  	
	  	if ($result)
	  	{
	  		while ($myrow = mysql_fetch_assoc($result)) 
	  		{
	  			// add selected string for an appropriate row
	  			$selData = '';
	  			if (!is_null($selectedKey) && ($selectedKey == $myrow['id']))
	  				$selData = 'selected="selected"';
	  			$arrKeywords[] = array( 'id' => $myrow['id'],
	  									'keyword' => $myrow['keyword'], 
	  									'notes' => $myrow['notes'], 
	  				   					'selected' => $selData,
	  								   );
	  		}
	  	}
	}
	return $arrKeywords;
}

function updateTCKeywords ($id, $arrKeywords)
{
	$keywords = null;
	if ($arrKeywords)
		$keywords = implode(",",$arrKeywords).",";
	
	$sqlUpdate = "UPDATE mgttestcase SET keywords='" . mysql_escape_string($keywords)."' where id=".$id;
	$resultUpdate = do_mysql_query($sqlUpdate);
	
	return $resultUpdate ? 'ok' : mysql_error();
}

function updateCategoryKeywords ($id, $newKey)
{
	$sqlTC = "SELECT id,title FROM mgttestcase WHERE catid=" . $id;
	$resultTC = do_mysql_query($sqlTC);
	
	$resultUpdate = null;
	if ($resultTC)
	{
		// execute for all test cases of the category
		while($rowTC = mysql_fetch_assoc($resultTC))
		{ 
			$resultAdd = addTCKeyword ($rowTC['id'], $newKey);
			if ($resultAdd != 'ok')
				$resultUpdate .= lang_get('tc_kw_update_fails1'). htmlspecialchars($rowTC['title']) . 
				                 lang_get('tc_kw_update_fails2').': ' . $resultAdd . '<br />';
		}
	}
	else
	{
		$resultUpdate = mysql_error();
	}
	return $resultUpdate ? $resultUpdate : 'ok';
}


function updateComponentKeywords ($id, $newKey)
{
	$sqlCat = "SELECT id AS cat_id FROM mgtcategory WHERE compid=" . $id;
	$resultCat = do_mysql_query($sqlCat);
	
	$resultUpdate = null;
	if ($resultCat)
	{
		// execute for all test cases of the category
		while($rowCat = mysql_fetch_assoc($resultCat))
		{ 
			$resultAdd = updateCategoryKeywords($rowCat['cat_id'], $newKey);
			if ($resultAdd != 'ok')
			{
				$resultUpdate .= $resultAdd . '<br />';
			}	
		}
	}
	else
	{
		$resultUpdate = mysql_error();
	}
  
	return $resultUpdate ? $resultUpdate : 'ok';
}

function addTCKeyword($tcID, $newKey)
{
	$sqlTC = "SELECT keywords FROM mgttestcase where id=" . $tcID;
	$resultUpdate = do_mysql_query($sqlTC);
	if ($resultUpdate)
	{
		$oldKeys = mysql_fetch_assoc($resultUpdate);
		$TCKeys = $oldKeys['keywords'];
		// add newKey if is not included
		$keys = explode(",",$TCKeys);
		if (!in_array($newKey,$keys))
		{
			$TCKeys .= $newKey.",";
			$TCKeys = mysql_escape_string($TCKeys);
			$sqlUpdate = "UPDATE mgttestcase SET keywords='".$TCKeys."' WHERE id=". $tcID;
			$resultUpdate = do_mysql_query($sqlUpdate);
		}
	}
	
	return $resultUpdate ? 'ok' : mysql_error();
}

/**
* multi update or delete keywords; Input is $_POST
*
* @author Andreas Morsing - added check for empty keywords
* @return array of Array of keyword + result
*
* @author Francisco Mancardi - 20051011 - interface changes
*
*/
function multiUpdateKeywords($prodID)
{
	$arrUpdate = null;
	$APPLY_STRIP_SLASHES=true;
	$newArray = extractInput($APPLY_STRIP_SLASHES);

	$i = 0;
	$arrLimit = count($newArray) - 1; // -1 because of button

	while ($i < $arrLimit)
	{ 
		$id = ($newArray[$i++]);
		$keyword = ($newArray[$i++]);
		$notes = ($newArray[$i++]);
		if (isset($newArray[$i]) && $newArray[$i] == 'on')
		{
			$i = $i + 1;

      $errorResult = lang_get('kw_deleted');
			if ( !deleteKeyword($id) )
			{
				$errorResult = lang_get('kw_delete_fails'). ' : ' . mysql_error();
			}	
		}
		else
		{
			$errorResult = lang_get('empty_keyword_no');		
			if (strlen($keyword))
			{
				//we shouldnt allow " and , any longer
				if (!preg_match("/(\"|,)/",$keyword,$m))
				{
				  $check = updateKeyword($prodID,$id,$keyword,$notes);
					if ($check['status_ok'])
					{
						$errorResult = lang_get('kw_updated');
					}	
		   		else
		   		{
						$errorResult = lang_get('kw_update_fails') . ': ' . $check['msg'];
					}	
				}
				else
				{
					$errorResult = lang_get('kw_invalid_chars');
				}	
			}
		}
		$arrUpdate[] =  array( 
								'keyword' => $keyword,
								'result' => $errorResult
							 );
	}
	return $arrUpdate;
}

function updateKeyword($prodID,$id,$keyword,$notes)
{
	global $g_allow_duplicate_keywords;

	$ret = array("msg" => "ok", "status_ok" => 0);
	$do_action = 1;
	$my_kw = trim($keyword);

	if (!$g_allow_duplicate_keywords)
	{
		$check = check_for_keyword_existence($prodID, $my_kw,$id);
		$do_action = !$check['keyword_exists'];

		$ret['msg'] = $check['msg'];
		$ret['status_ok'] = $do_action;
	}

  if( $do_action )
  {
		$sql = "UPDATE keywords SET notes='" . mysql_escape_string($notes) . "', keyword='" 
			     . mysql_escape_string($my_kw) . "' where id=" . $id;
	  $result = do_mysql_query($sql);
	  
	  if (!$result)
	  {
			$ret['msg'] = mysql_error();
			$ret['status_ok'] = 0;
	  }
  }

  return($ret);
}


function deleteKeyword($id)
{
	$sql = "DELETE FROM keywords WHERE id=" . $id;
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

/**
* Function insert a new Keyword to database
*
* @param int  $prodID
* @param string $keyword
* @param string $notes
* @return string SQL result
*
* 20051011 - fm - use of check_for_keyword_existence()
* 20051004 - fm - refactoring
*/
function addNewKeyword($prodID,$keyword,$notes)
{
	global $g_allow_duplicate_keywords;
	
	$ret = 'ok';
	$do_action = 1;
	$my_kw = trim($keyword);
	if (!$g_allow_duplicate_keywords)
	{
		$check = check_for_keyword_existence($prodID, $my_kw);
		$ret = $check['msg'];
		$do_action = !$check['keyword_exists'];
	}
	
	if ($do_action)
	{
		$sql =  " INSERT INTO keywords (keyword,prodid,notes) " .
				" VALUES ('" . mysql_escape_string($my_kw) .	"'," . 
				$prodID . ",'" . mysql_escape_string($notes) . "')";
		
		$result = do_mysql_query($sql);
		$ret = trim(mysql_error());
		if(!strlen($ret))
		{
			$ret = 'ok';
		}
	}
  
	return $ret;
}
/*
20051004 - fm - return type changed
*/
function getTCKeywords($tcID)
{
	$sql = "SELECT keywords FROM mgttestcase WHERE id=" . $tcID;
	$result = do_mysql_query($sql);
	$keywords = array();
	if ($result)
	{
		if ($row = mysql_fetch_assoc($result))
		{
			$keywords = explode(",",$row['keywords']);
		}	
	}
	return($keywords);
}

/*
20051004 - fm return type changed
*/
function getProductKeywords($prodID,$searchKW = null)
{
	// grab all of the available keywords
	$sql = "SELECT keyword FROM keywords WHERE prodid=" . $prodID;
	
	if (!is_null($searchKW))
	{
		$sql .= " AND keyword = '".mysql_escape_string($searchKW)."'";
	}
	$sql .= " ORDER BY keyword ASC";
	
	$result = do_mysql_query($sql);
	$keywords = array();
	if ($result)
	{
		while($row = mysql_fetch_assoc($result))
		{
			$keywords[] = $row['keyword'];
		}	
	}
	return $keywords;
}


/* 20051011 - fm 

$prodID: product ID
$kw    : keyword 
*/
function check_for_keyword_existence($prodID, $kw, $kwID=0)
{
	$ret = array('msg' => 'ok', 'keyword_exists' => 0);
  
	$sql = 	" SELECT * FROM keywords " .
			" WHERE UPPER(keyword) ='" . strtoupper(mysql_escape_string($kw))
			."' AND prodid=" . $prodID ;
	
	if ($kwID)
		$sql .= " AND id <> " . $kwID;
	
	$result = do_mysql_query($sql);       
	if(mysql_num_rows($result))
	{
		$ret['keyword_exists'] = 1;
		$ret['msg'] = lang_get('keyword_already_exists');
	}
	
	return $ret;
}
?>