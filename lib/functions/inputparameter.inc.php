<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: inputparameter.inc.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2009/05/14 18:39:53 $ by $Author: schlundus $
 * 
**/
require_once("object.class.php");
require_once("inputparameter.class.php");

function P_PARAMS($paramInfo,&$args = null)
{
	return GPR_PARAMS("POST",$paramInfo,$args);
}

function G_PARAMS($paramInfo,&$args = null)
{
	return GPR_PARAMS("GET",$paramInfo,$args);
}

function R_PARAMS($paramInfo,&$args = null)
{
	return GPR_PARAMS("REQUEST",$paramInfo,$args);
}

function GPR_PARAMS($source,$paramInfo,&$args = null)
{
	foreach($paramInfo as $pName => &$info)
	{
		array_unshift($info,$source);
	}
	return I_PARAMS($paramInfo,$args);
}

function I_PARAMS($paramInfo,&$args = null)
{
	$params = null;
	foreach($paramInfo as $pName => $info)
	{
		$source = $info[0];
		$type = $info[1];
		for($i = 1;$i <= 5;$i++)
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


function GPR_PARAM_STRING_N($gpr,$name,$minLen = null,$maxLen = null,$regExp = null,
                            $pfnValidation = null,$pfnNormalization = null)
{
	$vInfo = new tlStringValidationInfo();
	$vInfo->trim = tlStringValidationInfo::TRIM_BOTH;
	$vInfo->bStripSlashes = true;

    $items2check = array("minLen","maxLen","regExp","pfnValidation","pfnNormalization");
    foreach($items2check as $item)
    {
        if (!is_null($$item))
        {
            $vInfo->$item=$$item;
        }
    }
   
	$pInfo = new tlParameterInfo();
	$pInfo->source = $gpr;
	$pInfo->name = $name;
	
	$iParam = new tlInputParameter($pInfo,$vInfo);
	return $iParam->value();
}

function GPR_PARAM_INT($gpr,$name,$minVal = null,$maxVal = null,$pfnValidation = null)
{
	$vInfo = new tlIntegerValidationInfo();

    $items2check = array("minVal","maxVal","pfnValidation");
    foreach($items2check as $item)
    {
        if (!is_null($$item))
        {
            $vInfo->$item=$$item;
        }
    }
		
	$pInfo = new tlParameterInfo();
	$pInfo->source = $gpr;
	$pInfo->name = $name;
	
	$iParam = new tlInputParameter($pInfo,$vInfo);
	return $iParam->value();
}

function GPR_PARAM_INT_N($gpr,$name,$maxVal = null,$pfnValidation = null)
{
	return GPR_PARAM_INT($gpr,$name,0,$maxVal,$pfnValidation);
}

function GPR_PARAM_ARRAY_INT($gpr,$name,$pfnValidation = null)
{
	return GPR_PARAM_ARRAY($gpr,tlInputParameter::INT,$name,$pfnValidation);
}

function GPR_PARAM_ARRAY_STRING_N($gpr,$name,$pfnValidation = null)
{
	return GPR_PARAM_ARRAY($gpr,tlInputParameter::STRING_N,$name,$pfnValidation);
}

function GPR_PARAM_ARRAY($gpr,$type,$name,$pfnValidation)
{
	$vInfo = new tlArrayValidationInfo();
	if (!is_null($pfnValidation))
	{
		$vInfo->pfnValidation = $pfnValidation;
    }
    if ($type == tlInputParameter::STRING_N) 
    {
		$vInfo->validationInfo = new tlStringValidationInfo();
	}
	else
	{
		$vInfo->validationInfo = new tlIntegerValidationInfo();
	}
	$pInfo = new tlParameterInfo();
	$pInfo->source = $gpr;
	$pInfo->name = $name;
	
	$iParam = new tlInputParameter($pInfo,$vInfo);
	
	return $iParam->value();
}

function GPR_PARAM_CB_BOOL($gpr,$name)
{
	$vInfo = new tlCheckBoxValidationInfo();
		
	$pInfo = new tlParameterInfo();
	$pInfo->source = $gpr;
	$pInfo->name = $name;
	
	$iParam = new tlInputParameter($pInfo,$vInfo);
	return $iParam->value();
}
?>