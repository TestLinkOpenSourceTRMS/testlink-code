<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: metastring.class.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2008/01/21 20:10:54 $ $Author: schlundus $
 * @author franciscom
 */

//shorthand function for creating meta strings
function TLS($label,$params = null)
{
	$args = func_get_args();
	array_shift($args);
	return new tlMetaString($label,$args);
}

class tlMetaStringHelper
{
	public $label;
	public $params;
}

class tlMetaString extends tlObject
{
	protected $helper;
	
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
	}
	static public function unserialize($representation)
	{
		//at the moment we do this, maybe there is a more readable serialization
		$helper = unserialize($representation);
		$metaString = null;
		if ($helper)
		{
			$metaString = new tlMetaString();
			$metaString->helper = &$helper;
		}	
		return $metaString;
	}
	public function serialize()
	{
		return serialize($this->helper);
	}
	
	//if a tlMetaString is to be printed we use default localization
	public function __toString()
	{
		return $this->localize();
	}
	//localize the tlMetaString
	public function localize($locale = null)
	{
		$str = lang_get($this->helper->label,$locale);
		
		$subjects = array();
		$replacements = array();
		$params = $this->helper->params;
		for($i = 0;$i < sizeof($params);$i++)
		{
			$param = $params[$i];
			if (is_array($param))
			{
				$type = $param[0];
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
