<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: metastring.class.php,v $
 * @version $Revision: 1.12 $
 * @modified $Date: 2009/06/09 19:21:09 $ $Author: schlundus $
 * @author schlundus
 */
 

//some shorthand function for creating meta strings
/**
 * Creates an TLS object by giving a label and maybe some params, if the string is not localized
 * a "Not-localized" Event is generated
 * 
 * @param $label the string to localize
 * @param $params params which should be inserted into the localized label
 * @return tlMetaString returns the created tlMetaString Object 
 */
function TLS($label,$params = null)
{
	$args = func_get_args();
	array_shift($args);
	return new tlMetaString($label,$args);
}

/**
 * Creates an TLS object by giving a label and maybe some params, but doesn't fire any
 * "Not-localized" events
 * 
 * @param $label the string to localize
 * @param $params params which should be inserted into the localized label
 * @return tlMetaString returns the created tlMetaString Object 
 */
function _TLS($label,$params = null)
{
	$args = func_get_args();
	$mString = call_user_func_array("TLS",$args);
	$mString->helper->bDontFireEvent = true;
	return $mString;
}

/**
 * Helper class for the tlMetaString object, just a container to hold some infos
 *
 */
class tlMetaStringHelper
{
	/**
	 * @var string the label to localize
	 */
	public $label;
	/**
	 * @var array array of parameters which should be inserted into the localized String
	 */
	public $params;
	/**
	 * @var boolean set this to true if the label should be localized
	 */
	public $bDontLocalize;
	/**
	 * @var boolean set this to true, if not-localized strings should not generate events 
	 */
	public $bDontFireEvent;
}

/**
 * Class for localize-able String with parameters
 *
 */
class tlMetaString extends tlObject
{
	/**
	 * @var tlMetaStringHelper the helper object
	 */
	public $helper;

	/** class constructor
	 * @param $label string the label to localize, use {%1} to {%n} for parameters inserted into the localized string 
	 * @param $args array the array of parameters
	 */
	public function __construct($label = null,$args = null) {
		parent::__construct();
		$this->helper = new tlMetaStringHelper();
		if ($label) {
			$this->initialize($label,$args);
		}
	}

	/**
	 * Initializes the object
	 * 
	 * @param $label @see __construct
	 * @param $args  @see __construct
	 */
	public function initialize($label,$args = null) {
		$this->helper->label = $label;
		$this->helper->params = $args;
		$this->helper->bDontLocalize = false;
		$this->helper->bDontFireEvent = false;
	}

	/**
	  * Creates a serialized representation of the object, which can be stored
	  * and later unserialized again
	  *
	  * @return string the serialized tlMetaString object
	  */
	public function serialize() {
		return @serialize($this->helper);
	}
	
	/**
	 * Creates an tlMetaString object from a serialized representation
	 * 
	 * @param $representation string the serialized representation of the object
	 * @return tlMetaString the recreated tlMetaString object
	 */
	static public function unserialize($representation) {
		//at the moment we do this, maybe there is a more readable serialization
		$helper = @unserialize($representation);
		$metaString = new tlMetaString();
		if (!$helper) {
			$helper = new tlMetaStringHelper();
			$helper->label = $representation;
			$helper->params = null;
			$helper->bDontLocalize = true;
		}

		$metaString->helper = &$helper;
		return $metaString;
	}


	/* magic method, if a tlMetaString is to be printed we use default localization
	* @return string the localized tlMetaString
	*/
	public function __toString() {
		return $this->localize();
	}
	
	/**
	 * localizes the tlMetaString
	 * 
	 * @param $locale string any valid locale (which is supported by TestLink)
	 * @return string returns the localized tlMetaString
	 */
	public function localize($locale = null) {

		if ($this->helper->bDontLocalize) {
			$str = $this->helper->label;
		} else {
			$str = lang_get($this->helper->label,$locale,
				              $this->helper->bDontFireEvent);
		}

		$subjects = array();
		$replacements = array();
		$params = (array)$this->helper->params;
		for($i = 0;$i < sizeof($params); $i++) {
			$param = $params[$i];
			if (is_array($param)) {
				$item = null;
				if ($param[0]) {
					$type = $param[0];
				}
				if ($param[1]) {
					$item = $param[1];
				}

				//at the moment we ignore the type, if needed we can add types to eG localize a date or
				//something else
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