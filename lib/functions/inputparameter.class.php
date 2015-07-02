<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package    TestLink
 * @author     Andreas Morsing
 * @copyright  2009, TestLink community 
 * @filesource inputparameter.class.php
 * @link       http://www.teamst.org
 * @since      1.9
 * 
 **/

/**
 * Helper class for Input parameters (parameters fetched from POST/GET/REQUEST
 * 
 * @package TestLink
 * @author Andreas Morsing
 */
class tlInputParameter extends tlObject
{
	/** the supported parameter type: integer */
	const INT = 1;
	
	/** the supported parameter type: non-negative integer */
	const INT_N = 2;

	/** the supported parameter type: normalized (trimmed) string */
	const STRING_N = 3;

	/** the supported parameter type: array of integers */
	const ARRAY_INT = 4;

	/** the supported parameter type: array of normalized strings */
	const ARRAY_STRING_N = 5;

	/** the supported parameter type: checkbox boolean */
	const CB_BOOL = 6;
	
	/**
	 * @var object tlParameterInfo Information about the parameter
	 * @see class tlParameterInfo
	 */
	private $parameterInfo = null;

	/**
	 * @var boolean was the parameter fetched?
	 */
	private $bFetched = false;
	
	/**
	 * @var mixed tainted value, fetched but not validated
	 */
	protected $taintValue = null;
	
	/**
	 * @var mixed normalized and maybe validated value
	 */
	protected $normalizedValue = null;
	
	/**
	 * @var tl<TYPE>ValidationInfo Info how the value of the parameter should be validated
	 */
	protected $validationInfo = null;
	
	/**
	 * @var boolean is the parameter valid?
	 */
	protected $isValid = false;
	
	/**
	 * constructor
	 * @param tlInputParameter $parameterInfo Infos about the parameter source
	 * @param tl<TYPE>ValidationInfo $validationInfo Info about the validation of the parameter
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
	 * @return boolean returns true if the parameter is fetched, else false 
	 */
	protected function isFetched()
	{
		return $this->bFetched;
	}
	
	/**
	 * Return the VALID state
	 * @return boolean returns true if the parameter was validated, else false
	 */
	protected function isValid()
	{
		return $this->isValid;
	}
	
	/** 
	 * Cleans up the object
	 */
	protected function _clean()
	{
		$this->taintValue = null;
		$this->normalizedValue = null;
	
		$this->parameterInfo = null;
		$this->bFetched = false;
		
		$this->validationInfo = null;
		$this->isValid = false;
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
		$fetched = false;
		if ($src)
		{	
			if (isset($src[$parameterName]))
			{
				$value = $src[$parameterName];
				$fetched = true;
			}
		}
		$this->bFetched = $fetched;
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
	 * validates the value of the parameter
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
		$this->isValid = true;
	}	
	
	/**
	 * Returns the value of the parameter, after it was fetched and validated
	 * @return mixed return the value if it was fetched AND validated, null else
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
 * Helper class which holds some information like source and name about the InputParameter
 * @package TestLink
 */
class tlParameterInfo
{
	/**
	 * @var string source of the parameter input value (eG POST,GET,...)
	 */
	public $source = null;
	
	/**
	 * @var string name of the parameter
	 */
	public $name = null;
	
	function __construct($source = null,$name = null)
	{
	    $this->source = $source;
	    $this->name = $name;    
	}
	
}

/**
 * Helper class for validating strings
 * @package TestLink
 */
class tlStringValidationInfo 
{
	//some trimming related constants
	const TRIM_NONE = 0;
	const TRIM_LEFT = 1;
	const TRIM_RIGHT = 2;
	const TRIM_BOTH = 3;
	
	/**
	 * @var int maximum length of the string
	 */
	public $maxLen;
	
	/**
	 * @var int mininum length of the string
	 */
	public $minLen;
	
	/**
	 * @var int should we trim?
	 */
	public $trim = self::TRIM_NONE;
	
	/**
	 * @var bool should we strip slashes?
	 */
	public $doStripSlashes = false;
	
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
	 * @param string $value the value to be normalized
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
			if ($this->doStripSlashes)	
			{
				$value = $this->stripslashes($value);
			}	
		}
		return $value;
	}
	
	/**
	 * @param string $value the string to strip the slashes of
	 * @return string returns the stripped value 
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
	 * @return bool returns true if the value was successfully validated, else throws an Exception
	 */
	public function validate($value)
	{
		$minLen = $this->minLen;
		if ($minLen && tlStringLen($value) < $minLen)
		{
			$msg = "Input parameter validation failed [minLen - target: {$minLen} - actual: " . tlStringLen($value) . "]";
			tLog($msg,'ERROR');
			throw new Exception($msg);
		}
		
		$regExp = $this->regExp; 
		if ($regExp)
		{
			$dummy = null;
			if (!preg_match($regExp,$value,$dummy))
			{
				$msg = "Input parameter validation failed " .
				       "[regExp: " . htmlspecialchars($value) . " " . htmlspecialchars($regExp) . "]";
				tLog($msg,'ERROR');
				throw new Exception($msg);
			}	                    
		}	
		
		$pfnValidation = $this->pfnValidation;
		if ($pfnValidation)
		{
			if (!$pfnValidation($value))
			{
				$msg = "Input parameter validation failed [external function" .
						" - $pfnValidation]";
				tLog($msg,'ERROR');
				throw new Exception($msg);
			}	
		}	
			
		return true;
	}
}


/**
 * Helper class for validating Integers
 * @package TestLink
 */
class tlIntegerValidationInfo
{
	/**
	 * @var integer the maximum value of the parameter
	 */
	public $maxVal = PHP_INT_MAX;
	
	/**
	 * @var integer the minimum value of the parameter
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
	  $msg = 'Input parameter validation failed';
		if (!is_numeric($value))
		{
			$msg = "{$msg} [numeric: " . htmlspecialchars($value)."]";
			tLog($msg,'ERROR');
			throw new Exception($msg);
		}
		$value = intval($value);
		$minVal = $this->minVal;
		if ($value < $minVal)
		{
			$msg = "{$msg} [minVal: " . htmlspecialchars($value) . " = {$minVal}]";
			tLog($msg,'ERROR');
			throw new Exception($msg);
		}
		$maxVal = $this->maxVal;
		if ($value > $maxVal)
		{
			$msg = "{$msg} [maxVal: " . htmlspecialchars($value) . " = {$maxVal}]";
			tLog($msg,'ERROR');
			throw new Exception($msg);
		}
		$pfnValidation = $this->pfnValidation;
		if ($pfnValidation && !$pfnValidation($value))
		{
			$msg = "{$msg} [external function]";
			tLog($msg,'ERROR');
			throw new Exception($msg);
		}
		return true;
	}
}

/**
 * Helper class for validating Arrays
 * @package TestLink
 */
class tlArrayValidationInfo
{
	/**
	 * @var tl<TYPE>ValidationInfo the validation info which should be use to validated
	 * 		the member of the array
	 */
	public $validationInfo = null;
	/**
	 * @var function callback function which can be used for validation
	 */
	//@TODO schlundus, future purposes
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


/**
 * Helper class for validating checkboxex submitted via POST/GET 
 * @package TestLink
 */
class tlCheckBoxValidationInfo
{
	/**
	 * @param string $value the value which should be normalized
	 * @return array returns the normalized bool-typed value, true if value == "on", false else
	 */
	public function normalize($value)
	{
		if (!is_null($value))
		{
			$value = strtolower(trim($value));
			if ($value == "on")
			{
				$value = true;
			}
		}
		else
		{
			$value = false;
		}
		return $value;
	}
	
	/**
	 * @param boolean $value the value which should be validated
	 * @return bool returns true if the valie was successfully validated, else throws an Exception
	 */
	public function validate($value)
	{
	    return ($value === true || $value === false);
	}
}
?>