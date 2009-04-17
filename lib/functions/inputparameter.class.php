<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: inputparameter.class.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2009/04/17 19:57:32 $ by $Author: schlundus $
 * 
**/

/**
 * Helper class for Input parameter
 *
 */
class tlInputParameter extends tlObject
{
	//the supported types of Input parameters
	//int
	const INT = 1;
	//non-negative integer
	const INT_N = 2;
	//normalized (trimmed) string
	const STRING_N = 3;
	//array of integers
	const ARRAY_INT = 4;
	//array of normalized strings
	const ARRAY_STRING_N = 5;
	
	/**
	 * @var tlParameterInfo Information about the parameter
	 */
	private $parameterInfo = null;
	/**
	 * @var bool was the parameter fetched?
	 */
	private $bFetched = false;
	
	/**
	 * @var unknown_type tainted value, fetched but not validated
	 */
	protected $taintValue = null;
	/**
	 * @var unknown_type normalized and maybe validated value
	 */
	protected $normalizedValue = null;
	
	/**
	 * @var tl<TYPE>ValidationInfo Info how the value of the parameter should be validated
	 */
	protected $validationInfo = null;
	/**
	 * @var bool is the parameter valid?
	 */
	protected $bValid = false;
	
	/**
	 * constructor
	 * @param tlInputParameter $parameterInfo Infos about the parameter source
	 * @param tl<TYPE>ValidationInfo $validationInfo Info about the validation of the parameter
	 * @return unknown_type
	 */
	function __construct($parameterInfo,$validationInfo = null)
	{
		parent::__construct();
	
		$this->validationInfo = $validationInfo;
		$this->parameterInfo = $parameterInfo;
		
		$this->fetchParameter();
		$this->normalize();
		$this->validate();
	}
	
	/**
	 * Returns the FETCH state
	 * @return bool returns true if the parameter is fetched, else false 
	 */
	protected function isFetched()
	{
		return $this->bFetched;
	}
	
	/**
	 * Return the VALID state
	 * @return bool returns true if the parameter was validated, else false
	 */
	protected function isValid()
	{
		return $this->bValid;
	}
	
	/* Cleans up the object
	 */
	protected function _clean()
	{
		$this->taintValue = null;
		$this->normalizedValue = null;
	
		$this->parameterInfo = null;
		$this->bFetched = false;
		
		$this->validationInfo = null;
		$this->bValid = false;
	}
	
	/**
	 * Fetches the parameter from the source
	 */
	private function fetchParameter()
	{
		//@TODO schlundus, move fetch inside the parameterInfo class
		$parameterSource = $this->parameterInfo->source;
		$parameterName = $this->parameterInfo->name;
		
		$src = null;
		switch($parameterSource)
		{
			case "POST":
				$src = $_POST;
				break;
			case "GET":
				$src = $_GET;
				break;
			case "REQUEST":
				$src = $_REQUEST;
				break;
		}
		$value = null;
		$bFetched = false;
		if ($src)
		{	
			if (isset($src[$parameterName]))
			{
				$value = $src[$parameterName];
				$bFetched = true;
			}
		}
		$this->bFetched = $bFetched;
		$this->taintValue = $value;
	}

	/**
	 * Normalizes the value of the parameter, means gives the value the right type
	 */
	protected function normalize()
	{
		if ($this->isFetched())
		{
			if ($this->validationInfo)
			{
				$this->normalizedValue = $this->validationInfo->normalize($this->taintValue);
			}
			else
			{
				$this->normalizedValue = $this->taintValue;
			}	
		}
	}
		
	/**
	 * validated the value of the parameter
	 */
	protected function validate()
	{
		if (!$this->isFetched())
		{
			return;
        }
        
		if ($this->validationInfo)
		{
			$this->validationInfo->validate($this->normalizedValue);
		}
		$this->bValid = true;
	}	
	
	/**
	 * Returns the value of the parameter, after it was fetched and validated
	 * @return <ANYTYPE> return the value if it was fetched AND validated, null else
	 */
	public function value()
	{
		if ($this->isFetched() && $this->isValid())
		{
			return $this->normalizedValue;
		}	
		return null;
	}
}

/**
 * Helper class which holds information about the InputParameter
 *
 */
class tlParameterInfo 
{
	/**
	 * @var string the source of the parameter (eG POST,GET,...)
	 */
	public $source = null;
	
	/**
	 * @var string the name of the parameter
	 */
	public $name = null;
}

/**
 * Helper class for validating Strings
 *
 */
class tlStringValidationInfo 
{
	//some TRIM related constants
	const TRIM_NONE = 0;
	const TRIM_LEFT = 1;
	const TRIM_RIGHT = 2;
	const TRIM_BOTH = 3;
	
	/**
	 * @var int maxLen of the string
	 */
	public $maxLen;
	
	/**
	 * @var int minLen of the string
	 */
	public $minLen;
	
	/**
	 * @var int should we trim?
	 */
	public $trim = self::TRIM_NONE;
	
	/**
	 * @var bool should we strip slashes?
	 */
	public $bStripSlashes = false;
	
	/**
	 * @var string regular expression which can be used for validation
	 */
	public $regExp = null;
	
	/**
	 * @var function callback function which can be used for validation
	 */
	public $pfnValidation = null;
	
	/**
	 * @var function callback function which can be used for normalization
	 */
	public $pfnNormalization = null;
		
	/**
	 * @param unknown_type $value the value to be normalized
	 * @return string returns the normalized string-typed value
	 */
	public function normalize($value)
	{
		$pfnNormalization = $this->pfnNormalization;
		if ($pfnNormalization)
		{
			$value = $pfnNormalization($value);
		}
		else
		{
			$value = $this->trim($value);
			if ($this->bStripSlashes)	
			{
				$value = $this->stripslashes($value);
			}	
		}
		return $value;
	}
	
	/**
	 * @param string $value
	 * @return string return 
	 */
	public function stripslashes($value)
	{
		return strings_stripSlashes($value);	
	}
	
	/**
	 * @param string $value the string which should be trimmed
	 * @return string the trimmed value
	 */
	public function trim($value)
	{
		switch($this->trim)
		{
			case tlStringValidationInfo::TRIM_LEFT:
				$value = ltrim($value);
				break;

			case tlStringValidationInfo::TRIM_RIGHT:
				$value = rtrim($value);
				break;

			case tlStringValidationInfo::TRIM_BOTH:
				$value = trim($value);
				break;
		}
		if ($this->maxLen)	
		{
			$value = tlSubStr($value,0,$this->maxLen);
		}	
		return $value;
	}
	
	/**
	 * @param string $value the string which should be validated
	 * @return bool return true if the value was successfully validated, else throws an Exception
	 */
	public function validate($value)
	{
		$minLen = $this->minLen;
		if ($minLen && tlStringLen($value) < $minLen)
		{
			throw new Exception("Input parameter validation failed [minLen: " . tlStringLen($value)." {$minLen}]");
		}
		
		$regExp = $this->regExp; 
		if ($regExp)
		{
			$dummy = null;
			if (!preg_match($regExp,$value,$dummy))
			{
				throw new Exception("Input parameter validation failed [regExp: " . 
				                    htmlspecialchars($value)." ".htmlspecialchars($regExp)."]");
			}	                    
		}	
		
		$pfnValidation = $this->pfnValidation;
		if ($pfnValidation)
		{
			if (!$pfnValidation($value))
			{
				throw new Exception("Input parameter validation failed [external function]");
			}	
		}	
			
		return true;
	}
}


/**
 * Helper class for validating Integers
 *
 */
class tlIntegerValidationInfo
{
	/**
	 * @var int the maxVal of the parameter
	 */
	public $maxVal = PHP_INT_MAX;
	
	/**
	 * @var int the minVal of the parameter
	 */
	public $minVal = -2147483648;
	/**
	 * @var function callback function which can be used for validation
	 */
	public $pfnValidation = null;
	
	/**
	 * @param unknown_type $value the value which should normalized
	 * @return int return the normalized integer-typed value
	 */
	public function normalize($value)
	{
		return intval(trim($value));
	}
	
	/**
	 * @param integer $value the value which should be validated
	 * @return bool return true if the value was successfully validated, else throws an Exception
	 */
	public function validate($value)
	{
		if (!is_numeric($value))
			throw new Exception("Input parameter validation failed [numeric: " . htmlspecialchars($value)."]");
		
		$value = intval($value);
		$minVal = $this->minVal;
		if ($value < $minVal)
			throw new Exception("Input parameter validation failed [minVal: " . htmlspecialchars($value) . " = {$minVal}]");
				
		$maxVal = $this->maxVal;
		if ($value > $maxVal)
			throw new Exception("Input parameter validation failed [maxVal: " . htmlspecialchars($value) . " = {$maxVal}]");
		
		$pfnValidation = $this->pfnValidation;
		if ($pfnValidation && !$pfnValidation($value))
			throw new Exception("Input parameter validation failed [external function]");
		
		return true;
	}
}

/**
 * Helper class for validating Arrays
 *
 */
class tlArrayValidationInfo
{
	/**
	 * @var tl<TYPE>ValidationInfo the validationb info which should be use to validated
	 * 							    the member of the array
	 */
	public $validationInfo = null;
	//@TODO schlundus, future purposes
	/**
	 * @var function callback function which can be used for validation
	 */
	public $pfnValidation = null;
	
	/**
	 * @param array $valueArray the array which should be normalized
	 * @return array returns the normalized array-typed value
	 */
	public function normalize($valueArray)
	{
		$valueArray = (array) $valueArray;
		foreach($valueArray as $key => $value)
		{
			$valueArray[$key] = $this->validationInfo->normalize($value);
		}
		
		return $valueArray;
	}
	
	/**
	 * @param array $valueArray the array of values which should be validated
	 * @return bool return true if the array was successfully validated, else throws an Exception
	 */
	public function validate($valueArray)
	{
		$valueArray = (array) $valueArray;
		foreach($valueArray as $key => $value)
		{
			$this->validationInfo->validate($value);
		}
		
		return true;
	}
}
?>