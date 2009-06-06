<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: metastring.class.php,v $
 * @version $Revision: 1.11 $
 * @modified $Date: 2009/06/06 14:54:57 $ $Author: franciscom $
 * @author schlundus
 */
 
//@TODO COMMENT ALL FUNCTIONS 

//shorthand function for creating meta strings
function TLS($label,$params = null)
{
	$args = func_get_args();
	array_shift($args);
	return new tlMetaString($label,$args);
}

function _TLS($label,$params = null)
{
	$args = func_get_args();
	$mString = call_user_func_array("TLS",$args);
	$mString->helper->bDontFireEvent = true;
	return $mString;
}

class tlMetaStringHelper
{
	public $label;
	public $params;
	public $bDontLocalize;
	public $bDontFireEvent;
}

class tlMetaString extends tlObject
{
	public $helper;

	public function __construct($label = null,$args = null)
	{
		parent::__construct();
		$this->helper = new tlMetaStringHelper();
		if ($label)
			$this->initialize($label,$args);
	}
	public function initialize($label,$args = null)
	{
		$this->helper->label = $label;
		$this->helper->params = $args;
		$this->helper->bDontLocalize = false;
		$this->helper->bDontFireEvent = false;
	}
	static public function unserialize($representation)
	{
		//at the moment we do this, maybe there is a more readable serialization
		$helper = @unserialize($representation);
		$metaString = new tlMetaString();
		if (!$helper)
		{
			$helper = new tlMetaStringHelper();
			$helper->label = $representation;
			$helper->params = null;
			$helper->bDontLocalize = true;
		}

		$metaString->helper = &$helper;
		return $metaString;
	}
	public function serialize()
	{
		return @serialize($this->helper);
	}

	//if a tlMetaString is to be printed we use default localization
	public function __toString()
	{
		return $this->localize();
	}
	//localize the tlMetaString
	public function localize($locale = null)
	{
		if ($this->helper->bDontLocalize)
			$str = $this->helper->label;
		else
			$str = lang_get($this->helper->label,$locale,$this->helper->bDontFireEvent);

		$subjects = array();
		$replacements = array();
		$params = $this->helper->params;
		for($i = 0;$i < sizeof($params);$i++)
		{
			$param = $params[$i];
			if (is_array($param))
			{
				$item = null;
				if ($param[0])
					$type = $param[0];
				if ($param[1])
					$item = $param[1];

				//at the moment we ignore the type
				$param = $item;
			}
			else
			{
				$subjects[] = "{%".($i+1)."}";
			}
			$replacements[] = $param;
		}
		$str = str_replace($subjects,$replacements,$str);

		return $str;
	}
}
?>
