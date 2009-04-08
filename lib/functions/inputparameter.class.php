<?php
/*
- Please add CVS header
- comment on function on php documentator style
- remove m_ prefix that is not used in any other class
- add some blank line between fuctions to let code breath

*/
class tlInputParameter extends tlObject
{

    // why dis kind of coding to do things like
    // INT_N || STRING to create the 6 constant ?
    // Please explain
    //
	const INT = 1;
	const INT_N = 2;   // Please add comments wht _N means? documentation must be here
	const STRING = 4;
	const STRING_N = 8;

	//@TODO: schlundus, add support for arrays
		
	protected $m_taintValue = null;
	protected $m_normalizedValue = null;
	
	private $m_parameterInfo = null;
	private $m_bFetched = false;
	
	protected $m_charset = "UTF-8";
	protected $m_validationInfo = null;
	protected $m_bValid = false;
	
	
	function __construct($parameterInfo,$validationInfo = null)
	{
		parent::__construct();

		$this->m_charset = config_get('charset');
		
		$this->m_validationInfo = $validationInfo;
		$this->m_parameterInfo = $parameterInfo;
		
		$this->fetchParameter();
		$this->normalize();
		$this->validate();
	}
	
	protected function isFetched()
	{
		return $this->m_bFetched;
	}
	protected function isValid()
	{
		return $this->m_bValid;
	}
	protected function _clean()
	{
		$this->m_taintValue = null;
		$this->m_normalizedValue = null;
	
		$this->m_parameterInfo = null;
		$this->m_bFetched = false;
		
		$this->m_validationInfo = null;
		$this->m_bValid = false;
	}
	private function fetchParameter()
	{
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
	
	protected function normalize()
	{
		if ($this->isFetched())
			$this->m_normalizedValue = $this->m_taintValue;
	}	
	protected function validate()
	{
		if ($this->isFetched())
			$this->m_bValid = true;
	}
	public function value()
	{
		if ($this->isFetched() && $this->isValid())
			return $this->m_normalizedValue;
			
		return null;
	}
}

class tlParameterInfo 
{
	public $m_source = null;
	public $m_name = null;
}

class tlStringValidationInfo 
{
	const TRIM_NONE = 0;
	const TRIM_LEFT = 1;
	const TRIM_RIGHT = 2;
	const TRIM_BOTH = 3;
	
	public $m_maxLen;
	public $m_minLen;
	public $m_trim = self::TRIM_NONE;
	public $m_bStripSlashes = false;
	public $m_regExp = null;
	public $m_pfnValidation = null;
	public $m_pfnNormalization = null;
}

class tlStringInputParameter extends tlInputParameter
{
	function __construct($parameterInfo,$validationInfo)
	{
		parent::__construct($parameterInfo,$validationInfo);
	}

	protected function normalize()
	{
		if (!$this->isFetched())
			return;
		$value = $this->m_taintValue;
		$pfnNormalization = $this->m_validationInfo->m_pfnNormalization;
		if ($pfnNormalization)
			$value = $pfnNormalization($value);
		else
		{
			$value = $this->trim($value);
			$value = stripslashes($value);
		}
		
		$this->m_normalizedValue = $value;	
	}
	
	protected function validate()
	{
		if (!$this->isFetched())
			return;
			
		$value = $this->m_normalizedValue;	
		$minLen = $this->m_validationInfo->m_minLen;
		if ($minLen && tlStringLen($value) < $minLen)
			throw new Exception("Input parameter validation failed [minLen]");
		
		$regExp = $this->m_validationInfo->m_regExp; 
		if ($regExp)
		{
			$dummy = null;
			if (!preg_match($regExp,$value,$dummy))
				throw new Exception("Input parameter validation failed [regExp]");
		}	
		$pfnValidation = $this->m_validationInfo->m_pfnValidation;
		if ($pfnValidation)
		{
			if (!$pfnValidation($value))
				throw new Exception("Input parameter validation failed [external function]");
		}	
			
		$this->m_bValid = true;
	}

	private function stripslashes($value)
	{
		$validationInfo = $this->m_validationInfo;
		if ($validationInfo->m_bStripSlashes)	
			$value = strings_stripSlashes($value);	
		return $value;
	}
	
	private function trim($value)
	{
		$validationInfo = $this->m_validationInfo;
		$trim = $validationInfo->m_trim;
		
		// what about a switch ???
		if ($trim == tlStringValidationInfo::TRIM_LEFT)
		{
			$value = ltrim($value);
		}
		elseif ($trim == tlStringValidationInfo::TRIM_RIGHT)
		{
			$value = rtrim($value);
		}
		elseif ($trim == tlStringValidationInfo::TRIM_BOTH)
		{
			$value = trim($value);
        }
        
		if ($validationInfo->m_maxLen)	
		{
			$value = iconv_substr($value,0,$validationInfo->m_maxLen,$this->m_charset);
		} 	
		return $value;
	}
	
}

class tlIntegerValidationInfo
{
	public $m_maxVal = PHP_INT_MAX;
	public $m_minVal = -2147483648;
	public $m_pfnValidation = null;
}

class tlIntegerInputParameter extends tlInputParameter
{
	function __construct($parameterInfo,$validationInfo)
	{
		parent::__construct($parameterInfo,$validationInfo);
	}
	
	protected function normalize()
	{
		if ($this->isFetched())
			$this->m_normalizedValue = intval(trim($this->m_taintValue));
	}
	
	protected function validate()
	{
		if (!$this->isFetched())
		{
			return;
        }
        
		$value = $this->m_normalizedValue;	
		if (!is_numeric($value))
		{
			throw new Exception("Input parameter validation failed [numeric]");
		}
		
		$value = intval($value);
		$minVal = $this->m_validationInfo->m_minVal;
		if ($value < $minVal)
			throw new Exception("Input parameter validation failed [minVal]");
				
		$maxVal = $this->m_validationInfo->m_maxVal;
		if ($value > $maxVal)
			throw new Exception("Input parameter validation failed [maxVal]");
		
		$pfnValidation = $this->m_validationInfo->m_pfnValidation;
		if ($pfnValidation && !$pfnValidation($value))
		{
			throw new Exception("Input parameter validation failed [external function]");
		}
			
		$this->m_bValid = true;
	}
}
?>