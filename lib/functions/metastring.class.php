<?php
//shorthand function for creating meta strings
function TLS($label,$params)
{
	return new tlMetaString($label,$params);
}

class tlMetaString extends tlObject
{
	protected $label = null;
	protected $params = null;
	
	public function __construct($label,$args)
	{
		parent::__construct();
		$args = func_get_args();
		$this->label = array_shift($args);
		$this->params = $args;
	}
	static public function unserialize($representation)
	{
		//at the moment we do this, maybe there is a more readable serialization
		return unserialize($representation);
	}
	public function serialize()
	{
		return serialize($this);
	}
	
	//if a tlMetaString is to be printed we use default localization
	public function __toString()
	{
		return $this->localize();
	}
	//localize the tlMetaString
	public function localize($locale = null)
	{
		$str = lang_get($this->label,$locale);
		
		$subjects = array();
		$replacements = array();
		for($i = 0;$i < sizeof($this->params);$i++)
		{
			$param = $this->params[$i];
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
			$replacements = $param;
		}
		$str = str_replace($subjects,$replacements,$str);
				
		return $str;
	}
}
?>
