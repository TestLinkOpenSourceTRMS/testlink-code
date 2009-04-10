<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: inputparameter.class.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/04/10 21:07:27 $ by $Author: schlundus $
 *
**/

/**
 * Helper class for Input parameter
 *
 */
class tlInputParameter extends tlObject
{
	//the supported types of Input parameters
	//normal int
	const INT = 1;
	//non-negative int
	const INT_N = 2;
	//normal string
	const STRING_N = 3;
	//noral array
	const ARRAY_INT = 4;
	//@TODO: schlundus, add support for stringarrays	

	/**
	 * @var tlParameterInfo Information about the parameter
	 */
	private $m_parameterInfo = null;
	/**
	 * @var bool was the parameter fetched?
	 */
	private $m_bFetched = false;
	
	/**
	 * @var unknown_type tainted value, fetched but not validated
	 */
	protected $m_taintValue = null;
	/**
	 * @var unknown_type normalized and maybe validated value
	 */
	protected $m_normalizedValue = null;
	
	/**
	 * @var tl<TYPE>ValidationInfo Info how the value of the parameter should be validated
	 */
	protected $m_validationInfo = null;
	/**
	 * @var bool is the parameter valid?
	 */
	protected $m_bValid = false;
	
	/**
	 * constructor
	 * @param tlInputParameter $parameterInfo Infos about the parameter source
	 * @param tl<TYPE>ValidationInfo $validationInfo Info about the validation of the parameter
	 * @return unknown_type
	 */
	function __construct($parameterInfo,$validationInfo = null)
	{
		parent::__construct();
	
		$this->m_validationInfo = $validationInfo;
		$this->m_parameterInfo = $parameterInfo;
		
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
		return $this->m_bFetched;
	}
	
	/**
	 * Return the VALID state
	 * @return bool returns true if the parameter was validated, else false
	 */
	protected function isValid()
	{
		return $this->m_bValid;
	}
	
	/* Cleans up the object
	 */
	protected function _clean()
	{
		$this->m_taintValue = null;
		$this->m_normalizedValue = null;
	
		$this->m_parameterInfo = null;
		$this->m_bFetched = false;
		
		$this->m_validationInfo = null;
		$this->m_bValid = false;
	}
	
	/**
	 * Fetches the parameter from the source
	 */
	private function fetchParameter()
	{
		//@TODO schlundus, move fetch inside the parameterInfo class
		$parameterSource = $this->m_parameterInfo->m_source;
		$parameterName = $this->m_parameterInfo->m_name;
		
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
		$this->m_bFetched = $bFetched;
		$this->m_taintValue = $value;
	}

	/**
	 * Normalizes the value of the parameter, means gives the value the right type
	 */
	protected function normalize()
	{
		if ($this->isFetched())
		{
			if ($this->m_validationInfo)
				$this->m_normalizedValue = $this->m_validationInfo->normalize($this->m_taintValue);
			else
				$this->m_normalizedValue = $this->m_taintValue;
		}
	}
		
	/**
	 * validated the value of the parameter
	 */
	protected function validate()
	{
		if (!$this->isFetched())
			return;

		if ($this->m_validationInfo)
			$this->m_validationInfo->validate($this->m_normalizedValue);
		$this->m_bValid = true;
	}	
	
	/**
	 * Returns the value of the parameter, after it was fetched and validated
	 * @return <ANYTYPE> return the value if it was fetched AND validated, null else
	 */
	public function value()
	{
		if ($this->isFetched() && $this->isValid())
			return $this->m_normalizedValue;
			
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
	public $m_source = null;
	/**
	 * @var string the name of the parameter
	 */
	public $m_name = null;
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
	public $m_maxLen;
	/**
	 * @var int minLen of the string
	 */
	public $m_minLen;
	/**
	 * @var int should we trim?
	 */
	public $m_trim = self::TRIM_NONE;
	/**
	 * @var bool should we strip slashes?
	 */
	public $m_bStripSlashes = false;
	/**
	 * @var string regular expression which can be used for validation
	 */
	public $m_regExp = null;
	/**
	 * @var function callback function which can be used for validation
	 */
	public $m_pfnValidation = null;
	/**
	 * @var function callback function which can be used for normalization
	 */
	public $m_pfnNormalization = null;
		
	/**
	 * @param unknown_type $value the value to be normalized
	 * @return string returns the normalized string-typed value
	 */
	public function normalize($value)
	{
		$pfnNormalization = $this->m_pfnNormalization;
		if ($pfnNormalization)
			$value = $pfnNormalization($value);
		else
		{
			$value = $this->trim($value);
			if ($this->m_bStripSlashes)	
				$value = $this->stripslashes($value);
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
		switch($this->m_trim)
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
		if ($this->m_maxLen)	
			$value = tlSubStr($value,0,$this->m_maxLen);
			
		return $value;
	}
	
	/**
	 * @param string $value the string which should be validated
	 * @return bool return true if the value was successfully validated, else throws an Exception
	 */
	public function validate($value)
	{
		$minLen = $this->m_minLen;
		if ($minLen && tlStringLen($value) < $minLen)
			throw new Exception("Input parameter validation failed [minLen: " . tlStringLen($value)." {$minLen}]");
		
		$regExp = $this->m_regExp; 
		if ($regExp)
		{
			$dummy = null;
			if (!preg_match($regExp,$value,$dummy))
				throw new Exception("Input parameter validation failed [regExp: " . htmlspecialchars($value)." ".htmlspecialchars($regExp)."]");
		}	
		$pfnValidation = $this->m_pfnValidation;
		if ($pfnValidation)
		{
			if (!$pfnValidation($value))
				throw new Exception("Input parameter validation failed [external function]");
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
	public $m_maxVal = PHP_INT_MAX;
	/**
	 * @var int the minVal of the parameter
	 */
	public $m_minVal = -2147483648;
	/**
	 * @var function callback function which can be used for validation
	 */
	public $m_pfnValidation = null;
	
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
		$minVal = $this->m_minVal;
		if ($value < $minVal)
			throw new Exception("Input parameter validation failed [minVal: " . htmlspecialchars($value) . " = {$minVal}]");
				
		$maxVal = $this->m_maxVal;
		if ($value > $maxVal)
			throw new Exception("Input parameter validation failed [maxVal: " . htmlspecialchars($value) . " = {$maxVal}]");
		
		$pfnValidation = $this->m_pfnValidation;
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
	public $m_validationInfo = null;
	//@TODO schlundus, future purposes
	/**
	 * @var function callback function which can be used for validation
	 */
	public $m_pfnValidation = null;
	
	/**
	 * @param array $valueArray the array which should be normalized
	 * @return array returns the normalized array-typed value
	 */
	public function normalize($valueArray)
	{
		$valueArray = (array) $valueArray;
		foreach($valueArray as $key => $value)
		{
			$valueArray[$key] = $this->m_validationInfo->normalize($value);
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
			$this->m_validationInfo->validate($value);
		}
		
		return true;
	}
}
?>