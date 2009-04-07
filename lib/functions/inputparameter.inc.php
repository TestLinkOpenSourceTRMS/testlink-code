<?php
require_once("object.class.php");
require_once("inputparameter.class.php");

function P_PARAMS($paramInfo)
{
	foreach($paramInfo as $pName => &$info)
	{
		array_unshift($info,"POST");
	}
	return I_PARAMS($paramInfo);
}

function G_PARAMS($paramInfo)
{
	foreach($paramInfo as $pName => &$info)
	{
		array_unshift($info,"GET");
	}
	return I_PARAMS($paramInfo);
}

function R_PARAMS($paramInfo)
{
	foreach($paramInfo as $pName => &$info)
	{
		array_unshift($info,"REQUEST");
	}
	return I_PARAMS($paramInfo);
}

function P_PARAM_STRING_N($name,$minLen = null,$maxLen = null,$regExp = null,$pfnValidation = null,$pfnNormalization = null)
{
	return GPR_PARAM_STRING_N("POST",$name,$minLen,$maxLen,$regExp,$pfnValidation,$pfnNormalization);
}
function P_PARAM_INT($name,$minVal = null,$maxVal = null,$pfnValidation = null)
{
	return GPR_PARAM_INT("POST",$name,$minVal,$maxVal,$pfnValidation);
}
function P_PARAM_INT_N($name,$maxVal = null,$pfnValidation = null)
{
	return GPR_PARAM_INT_N("POST",$name,$maxVal,$pfnValidation);
}
function G_PARAM_STRING_N($name,$minLen = null,$maxLen = null,$regExp = null,$pfnValidation = null,$pfnNormalization = null)
{
	return GPR_PARAM_STRING_N("GET",$name,$minLen,$maxLen,$regExp,$pfnValidation,$pfnNormalization);
}
function G_PARAM_INT($name,$minVal = null,$maxVal = null,$pfnValidation = null)
{
	return GPR_PARAM_INT("GET",$name,$minVal,$maxVal,$pfnValidation);
}
function G_PARAM_INT_N($name,$maxVal = null,$pfnValidation = null)
{
	return GPR_PARAM_INT_N("GET",$name,0,$maxVal,$pfnValidation);
}
function GPR_PARAM_INT_N($gpr,$name,$maxVal = null,$pfnValidation = null)
{
	return GPR_PARAM_INT($gpr,$name,0,$maxVal,$pfnValidation);
}

function I_PARAMS($paramInfo)
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
				$minLen= $p1;
				$maxLen = $p2;
				$regExp = $p3;
				$pfnValidation = $p4;
				$pfnNormalization = $p5;
				$value = GPR_PARAM_STRING_N($source,$pName,$minLen,$maxLen,$regExp,$pfnValidation,$pfnNormalization);
				break;
		}
		$params[$pName] = $value;
	}
	return $params;
}


function GPR_PARAM_STRING_N($gpr,$name,$minLen = null,$maxLen = null,$regExp = null,$pfnValidation = null,$pfnNormalization = null)
{
	$vInfo = new tlStringValidationInfo();
	if (!is_null($minLen))
		$vInfo->m_minLen = $minLen;
	if (!is_null($maxLen))
		$vInfo->m_maxLen = $maxLen;
	$vInfo->m_trim = tlStringValidationInfo::TRIM_BOTH;
	$vInfo->m_bStripSlashes = true;
	$vInfo->m_regExp = $regExp;
	$vInfo->m_pfnValidation = $pfnValidation;
	$vInfo->m_pfnNormalization = $pfnNormalization;
	
	$pInfo = new tlParameterInfo();
	$pInfo->m_source = $gpr;
	$pInfo->m_name = $name;
	
	$iParam = new tlStringInputParameter($pInfo,$vInfo);
	return $iParam->value();
}

function GPR_PARAM_INT($gpr,$name,$minVal = null,$maxVal = null,$pfnValidation = null)
{
	$vInfo = new tlIntegerValidationInfo();
	if (!is_null($minVal))
		$vInfo->m_minVal = $minVal;
	if (!is_null($maxVal))
		$vInfo->m_maxVal = $maxVal;
	$vInfo->m_pfnValidation = $pfnValidation;
		
	$pInfo = new tlParameterInfo();
	$pInfo->m_source = $gpr;
	$pInfo->m_name = $name;
	
	$iParam = new tlIntegerInputParameter($pInfo,$vInfo);
	return $iParam->value();
}

/*

function check($value)
{
	if (strlen($value) != 4)
		return false;
	return true;
}
function norm($value)
{
	return str_replace("b","",$value);
}
$_POST["HelloInt"] = "5";
$_POST["Hello"] = utf8_encode("abbababa");
//$iParam = P_PARAM_INT("HelloInt",null,null,"check");
//$iParam = P_PARAM_INT_N("HelloInt",null,"check");
$_POST["Hello"] = utf8_encode("abbababa");
$iParam = P_PARAM_STRING_N("Hello",1,15,null,"check","norm");
$_POST["Hello"] = utf8_encode("aaaa");
$iParam = P_PARAM_STRING_N("Hello",1,15,'/^aaaa$/');
//$iParam = P_PARAM_INT("HelloInt");

$params = array(
	"Hello" => array("POST",tlInputParameter::STRING_N,1,15,null,"check","norm"),
	"HelloInt" => array("POST",tlInputParameter::INT_N),
);
var_dump(I_PARAMS($params));
*/
?>