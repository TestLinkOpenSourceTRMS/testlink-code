<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Fetch and process input data
 * 
 * Examples of using tlInputParameter related functions
 *
 *	@interal revisions:
 *  20100109 - franciscom - fixed errors on documentation          
 *
 * <code>
 *  
 * 
 * 	$params = array( 
 *
 *		// input from GET['HelloString3'], 
 *    // type: string,  minLen: 1, maxLen: 15,
 *    // regexp: null 
 *    // checkFunction: applys checks via checkFooOrBar() to ensure its either 'foo' or 'bar' 
 *    // normalization: done via  normFunction() which replaces ',' with '.' 
 *		"HelloString3" => array("GET",tlInputParameter::STRING_N,1,15,null,'checkFooOrBar','normFunction'),
 *
 *		// string, from POST['HelloString'], minLen 1, maxLen 15
 *		"HelloString1" => array("POST",tlInputParameter::STRING_N,1,15),
 *
 *		//non negative integer, from POST['HelloInt1']
 *		"HelloInt1" =>  array("POST",tlInputParameter::INT_N),
 *
 *		//string, from POST['HelloString2'], minLen 1, maxLen 15, checked with a regExp 
 *		"HelloString2" => array("POST",tlInputParameter::STRING_N,1,15,'/^aaaa$/'),
 *
 *		// non negativ integer, from POST['HelloInt2'], minValue = 20, maxValue = 40. checked to 	
 *		// ensure it's odd by using a checkFunction
 *		"HelloInt2" =>  array("POST",tlInputParameter::INT,20,40,'checkOdd'),
 * 	);
 * 
 * 	$pageParams = I_PARAMS($params)
 * 
 * 
 * 	$params = array( 
 * 		"HelloString1" => array(tlInputParameter::STRING_N,1,15),
 * 		"HelloInt1" =>  array(tlInputParameter::INT_N),
 * 		"HelloString2" => array(tlInputParameter::STRING_N,1,15,'/^aaaa$/'),
 * 		"HelloString3" => array(,tlInputParameter::STRING_N,1,15,null,'checkFunction','normFunction'),
 * 		"HelloInt2" =>  array(tlInputParameter::INT,20,40,'checkOdd'),
 * 	);
 * 
 * $pageParams = P_PARAMS($params);
 * </code> 
 *
 * @package 	TestLink
 * @copyright 2005-2012, TestLink community 
 * @version   inputparameter.inc.php
 * @link 		  http://www.teamst.org/index.php
 * 
 * 
 **/

/** include logic */
require_once("object.class.php");
require_once("inputparameter.class.php");


/**
 * Fetches the input parameters from POST
 * 
 * @param array $paramInfo generic array about the parameter see examples below of usage
 * @param object $args an optional object to which each parameter is added as a property
 * 
 * @return array returns the array with the fetched parameter, keys are the same as in $paramInfo
 */
function P_PARAMS($paramInfo,&$args = null)
{
	return GPR_PARAMS("POST",$paramInfo,$args);
}


/**
 * Fetches the input parameters from GET
 * 
 * @param array $paramInfo generic array about the parameter see examples below of usage
 * @param object $args an optional object to which each parameter is added as a property
 * 
 * @return array returns the array with the fetched parameter, keys are the same as in $paramInfo
 */
function G_PARAMS($paramInfo,&$args = null)
{
	return GPR_PARAMS("GET",$paramInfo,$args);
}


/**
 * Fetches the input parameters from REQUEST
 * 
 * @param array $paramInfo generic array about the parameter see examples below of usage
 * @param object $args an optional object to which each parameter is added as a property
 * 
 * @return array returns the array with the fetched parameter, keys are the same as in $paramInfo
 */
function R_PARAMS($paramInfo,&$args = null)
{
	return GPR_PARAMS("REQUEST",$paramInfo,$args);
}


/**
 * Fetches the input parameters from POST
 * 
 * @param string $source name of the source to fetch could be "POST", "GET", "REQUEST"
 * @param array $paramInfo generic array about the parameter see examples below of usage
 * @param object $args an optional object to which each parameter is added as a property
 * 
 * @return array returns the array with the fetched parameter, keys are the same as in $paramInfo
 */
function GPR_PARAMS($source,$paramInfo,&$args = null)
{
	foreach($paramInfo as $pName => &$info)
	{
		array_unshift($info,$source);
	}
	return I_PARAMS($paramInfo,$args);
}


/**
 * Fetches the input parameters from the sources specified in $paramInfo
 * 
 * @param array $paramInfo generic array about the parameter see examples below of usage
 * @param object $args an optional object to which each parameter is added as a property
 * 
 * @return array returns the array with the fetched parameter, keys are the same as in $paramInfo
 */
function I_PARAMS($paramInfo,&$args = null)
{
	static $MAX_NUM_OF_PARAMS = 5;
	$params = null;
	foreach($paramInfo as $pName => $info)
	{
		$source = $info[0];
		$type = $info[1];
		for($i = 1;$i <= $MAX_NUM_OF_PARAMS;$i++)  
		{
			$varName = "p{$i}";
			$value = isset($info[$i+1]) ? $info[$i+1] : null;
			$$varName = $value;
		}
		
		switch($type)
		{
			case tlInputParameter::ARRAY_INT:
				$pfnValidation = $p1;
				$value = GPR_PARAM_ARRAY_INT($source,$pName,$pfnValidation);
				break;
		
			case tlInputParameter::ARRAY_STRING_N:
				$pfnValidation = $p1;
				$value = GPR_PARAM_ARRAY_STRING_N($source,$pName,$pfnValidation);
				break;
		
			case tlInputParameter::INT_N:
				$maxVal = $p1;
				$pfnValidation = $p2;
				$value = GPR_PARAM_INT_N($source,$pName,$maxVal,$pfnValidation);
				break;
		
			case tlInputParameter::INT:
				$minVal = $p1;
				$maxVal = $p2;
				$pfnValidation = $p3;
				$value = GPR_PARAM_INT($source,$pName,$minVal,$maxVal,$pfnValidation);
				break;
		
			case tlInputParameter::STRING_N:
				$minLen = $p1;
				$maxLen = $p2;
				$regExp = $p3;
				$pfnValidation = $p4;
				$pfnNormalization = $p5;
				$value = GPR_PARAM_STRING_N($source,$pName,$minLen,$maxLen,$regExp,
				                            $pfnValidation,$pfnNormalization);
				break;
		
			case tlInputParameter::CB_BOOL:
				$value = GPR_PARAM_CB_BOOL($source,$pName);
				break;
		}
		$params[$pName] = $value;
		if ($args)
		{
			$args->$pName = $value;
		}
	}
	return $params;
}


/**
 * Process a string type value from GET/POST/REQUEST 
 * 
 * @param string $inputSource the name of the source, "GET","POST","REQUEST"
 * @param string $name the name of the parameter
 * @param integer $minLen the minimum length of the string
 * @param integer $maxLen the maximum length of the string
 * @param string $regExp a regular Expression for preg_ functions used the validate
 * @param string $pfnValidation a callback function used to validate
 * @param string $pfnNormalization a callback function used to normalize
 
 * @return string the value of the parameter
 */
function GPR_PARAM_STRING_N($inputSource,$name,$minLen = null,$maxLen = null,$regExp = null,
                            $pfnValidation = null,$pfnNormalization = null)
{
	$vInfo = new tlStringValidationInfo();
	$vInfo->trim = tlStringValidationInfo::TRIM_BOTH;
	$vInfo->doStripSlashes = true;

  $parameters = array("minLen","maxLen","regExp","pfnValidation","pfnNormalization");
  foreach($parameters as $parameter)
  {
    if (!is_null($$parameter))
    {
      $vInfo->$parameter = $$parameter;
    }    
  }
  
  try   
  {
    $pInfo = new tlParameterInfo($inputSource,$name);
	  $iParam = new tlInputParameter($pInfo,$vInfo);
	}
  catch (Exception $e)  
  {  
    echo 'Input name: ' . $name . ' :: Exception ' . $e->getMessage();
    exit();
  }
	
	return $iParam->value();
}


/**
 * Process a integer type value from GET/POST/REQUEST 
 * 
 * @param string $inputSource the name of the source, "GET","POST","REQUEST"
 * @param string $name the name of the parameter
 * @param integer $minVal the minimum value 
 * @param integer $maxVal the maximum value
 * @param string $pfnValidation a callback function used to validate
 
 * @return integer the value of the parameter
 */
function GPR_PARAM_INT($inputSource,$name,$minVal = null,$maxVal = null,$pfnValidation = null)
{
	$vInfo = new tlIntegerValidationInfo();

  $parameters = array("minVal","maxVal","pfnValidation");
	foreach($parameters as $parameter)
  {
    if (!is_null($$parameter))
    {
      $vInfo->$parameter = $$parameter;
    }    
  }
	$pInfo = new tlParameterInfo($inputSource,$name);
	$iParam = new tlInputParameter($pInfo,$vInfo);

	return $iParam->value();
}


/**
 * Process a non-negative integer type value from GET/POST/REQUEST 
 * 
 * @param string $inputSource the name of the source, "GET","POST","REQUEST"
 * @param string $name the name of the parameter
 * @param integer $maxVal the maximum value
 * @param string $pfnValidation a callback function used to validate
 
 * @return integer the value of the parameter
 */
function GPR_PARAM_INT_N($inputSource,$name,$maxVal = null,$pfnValidation = null)
{
	return GPR_PARAM_INT($inputSource,$name,0,$maxVal,$pfnValidation);
}


/**
 * Process an array of integer type values from GET/POST/REQUEST 
 * 
 * @param string $inputSource the name of the source, "GET","POST","REQUEST"
 * @param string $name the name of the parameter
 * @param string $pfnValidation a callback function used to validate
 
 * @return array the array of integer values from the parameter
 */
function GPR_PARAM_ARRAY_INT($inputSource,$name,$pfnValidation = null)
{
	return GPR_PARAM_ARRAY($inputSource,tlInputParameter::INT,$name,$pfnValidation);
}

/**
 * Process an array of string_n type values from GET/POST/REQUEST 
 * 
 * @param string $inputSource the name of the source, "GET","POST","REQUEST"
 * @param string $name the name of the parameter
 * @param string $pfnValidation a callback function used to validate
 
 * @return array the array of string values from the parameter
 */
function GPR_PARAM_ARRAY_STRING_N($inputSource,$name,$pfnValidation = null)
{
	return GPR_PARAM_ARRAY($inputSource,tlInputParameter::STRING_N,$name,$pfnValidation);
}


/**
 * Process an array of string_n type values from GET/POST/REQUEST 
 * 
 * @param string $inputSource the name of the source, "GET","POST","REQUEST"
 * @param string $name the name of the parameter
 * @param string $pfnValidation a callback function used to validate
 
 * @return array the array of string values from the parameter
 */
function GPR_PARAM_ARRAY($inputSource,$type,$name,$pfnValidation)
{
	$vInfo = new tlArrayValidationInfo();
	if (!is_null($pfnValidation))
		$vInfo->pfnValidation = $pfnValidation;
    
	if ($type == tlInputParameter::STRING_N)
    	$vInfo->validationInfo = new tlStringValidationInfo();
	else
		$vInfo->validationInfo = new tlIntegerValidationInfo();
	
	$pInfo = new tlParameterInfo($inputSource,$name);
	$iParam = new tlInputParameter($pInfo,$vInfo);
	
	return $iParam->value();
}


/**
 * Process an array of "checkbox" (string equal to "on") type values from GET/POST/REQUEST 
 * 
 * @param string $inputSource the name of the source, "GET","POST","REQUEST"
 * @param string $name the name of the parameter
 
 * @return array the array of boolean values from the parameter
 */
function GPR_PARAM_CB_BOOL($inputSource,$name)
{
	$vInfo = new tlCheckBoxValidationInfo();
	$pInfo = new tlParameterInfo($inputSource,$name);
	$iParam = new tlInputParameter($pInfo,$vInfo);
	return $iParam->value();
}
?>