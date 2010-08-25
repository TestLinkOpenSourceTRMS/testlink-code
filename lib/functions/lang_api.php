<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: lang_api.php,v 1.29 2010/08/25 18:05:50 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 
 *	20100823 - franciscom - forcing loading of en_GB, for displaying missing localization
 *							on english (if available), instead of LOCALIZED
 *
 *	20100820 - franciscom - new feature on init_labels()
 *	20070501 - franciscom - lang_get_smarty() now accept a list of
 *	                         strings to translate.
 *	20070501 - franciscom - enabled logic to manage a custom_strings.txt file
 *	20050508 - fm - changes to lang_get_smarty()
 *	200800606 - havlatm - added description.php resource
 *
 *
 * Thanks:
 * 		The functionality is based on Mantis BTS project code 
 * 		Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * 		Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 *
 **/


// lang_load call
$g_lang_strings = array();


# stack for language overrides
$g_lang_overrides = array();


/**
 * Retrieves an internationalized string
 * This function will return one of (in order of preference):
 *   1. The string in the current user's preferred language (if defined)
 *   2. The string in English
 * 
 * @param mixed $p_string string or array of string with term keys
 * 
 * @internal Revisions:
 *      20070501 - franciscom - added TL_LOCALIZE_TAG in order to
 *                             improve label management for custom fields
 */
function lang_get( $p_string, $p_lang = null, $bDontFireEvents = false)
{
	if ($p_string == '' || is_null($p_string))
	{
		return $p_string;
	}
	
	$t_lang = $p_lang;
	if (null === $t_lang)
	{
		$t_lang = isset($_SESSION['locale']) ? $_SESSION['locale'] : TL_DEFAULT_LOCALE;
	}
	
	lang_ensure_loaded($t_lang);
	global $g_lang_strings;
	
	$loc_str = null;
	$missingL18N = false;
	$englishSolutionFound = false;
	
	if (isset($g_lang_strings[$t_lang][$p_string]))
	{
		$loc_str = $g_lang_strings[$t_lang][$p_string];
	}
	else
	{
		if( $t_lang != 'en_GB' )
		{
			// force load of english strings
			lang_ensure_loaded('en_GB');
		}
		if(isset($g_lang_strings['en_GB'][$p_string]))
		{
			$missingL18N = true;
			$englishSolutionFound = true;
			$loc_str = $g_lang_strings['en_GB'][$p_string];
		}
	}
	
	
	$the_str = $loc_str;
	$missingL18N = is_null($loc_str) || $missingL18N;
	
	if (!is_null($loc_str))
	{
		$stringFileCharset = "ISO-8859-1";
		if (isset($g_lang_strings[$t_lang]['STRINGFILE_CHARSET']))
			$stringFileCharset = $g_lang_strings[$t_lang]['STRINGFILE_CHARSET'];
			
		if ($stringFileCharset != TL_TPL_CHARSET)
			$the_str = iconv($stringFileCharset,TL_TPL_CHARSET,$loc_str);
	}
	
	if( $missingL18N ) 
	{
		// if( $t_lang != 'en_GB' )
		// {
		// 	// force load of english strings
		// 	lang_ensure_loaded('en_GB');
		// }
		
		if(!$bDontFireEvents)
		{
			// 20100823 - franciscom
			// When testing with a user with locale = italian, found
			// 1. missing localized string was replaced with version present on english strings
			// 2. no log written to event viewer
			// 3. detected a call to lang_get() with language en_GB
			//
			$msg = sprintf("string '%s' is not localized for locale '%s'",$p_string,$t_lang);
			logWarningEvent($msg,"LOCALIZATION");
		}
		if( !$englishSolutionFound )
		{
			$the_str = TL_LOCALIZE_TAG .$p_string;
		}
	}
	return $the_str;
}


/**
 * Retrieves an internationalized string and insert text into
 * 
 * @param string $base string to be localized
 * @param string $modifier something to be inserted in the first string
 * @return string localized with inserted name or something
 */
function langGetFormated( $text_key, $modifier )
{
	$text_localized = lang_get($text_key);
	if (strpos($text_localized, TL_LOCALIZE_TAG) == 0)
	{
		$text_localized = sprintf($text_localized, $modifier);
	}
	
	return $text_localized;
}


/**
 * Get localized string on key
 * 
 * When you choose to have translation results assigned to a smarty variable
 * now you can send a list (string with ',' as element separator) of labels
 * to be translated.
 * In this situation you will get as result an associative array that uses
 * as key the string to be translated.
 * 
 * Example:
 * <code>
 * {lang_get s='th_testsuite,details' var='labels'}
 * </code>
 * labels will be : labels['th_testsuite']
 *                  labels['details']
 * 
 * and on smarty template you will access in this way: $labels.details
 * 
 * @internal Revisions:
 * 20050708 - fm
 * Modified to cope with situation where you need
 * to assign a Smarty Template variable instead
 * of generate output.
 * Now you can use this function in both situatuons.
 * 
 * if the key 'var' is found in the associative array
 * instead of return a value, this value is assigned
 * to $params['var`]
 */
function lang_get_smarty($params, &$smarty)
	{
	$myLocale=isset($params['locale']) ? $params['locale'] : null;
	if(	isset($params['var']) )
	{
		$labels2translate=explode(',',$params['s']);
		if( count($labels2translate) == 1)
		{
			$myLabels=lang_get($params['s'], $myLocale);
		}
		else
		{
			$myLabels=array();
			foreach($labels2translate as $str)
			{
				$str2search=trim($str);
				$myLabels[$str2search]=lang_get($str2search, $myLocale);
			}
		}
		$smarty->assign($params['var'], $myLabels);
	}
	else
	{
		$the_ret = lang_get($params['s'], $myLocale);
		return $the_ret;
	}
}


/** 
 * Loads the specified language and stores it in $g_lang_strings,
 * to be used by lang_get
 */
function lang_load( $p_lang ) {
	global $g_lang_strings, $g_active_language;
	global $TLS_STRINGFILE_CHARSET;

	$g_active_language  = $p_lang;

	if ( isset( $g_lang_strings[ $p_lang ] ) ) {
		return;
	}

	$t_lang_dir_base = TL_ABS_PATH . 'locale' . DIRECTORY_SEPARATOR;
	$lang_resource_path = $t_lang_dir_base . $p_lang . DIRECTORY_SEPARATOR . 'strings.txt';
	if (file_exists($lang_resource_path))
	{
		require($lang_resource_path);
	}
	else
	{
		require($t_lang_dir_base . 'en_GB' . DIRECTORY_SEPARATOR . 'strings.txt');
    }
    
	$lang_resource_path = $t_lang_dir_base . $p_lang . DIRECTORY_SEPARATOR . 'description.php';
	if (file_exists($lang_resource_path))
	{
		require($lang_resource_path );
	}
	else
	{
		require($t_lang_dir_base . 'en_GB' . DIRECTORY_SEPARATOR . 'description.php');
    }
    
	// Allow overriding strings declared in the language file.
	// custom_strings_inc.php can use $g_active_language
	$lang_resource_path = $t_lang_dir_base . $p_lang . DIRECTORY_SEPARATOR . 'custom_strings.txt';
	if (file_exists( $lang_resource_path ) ) {
	     require_once( $lang_resource_path );
	}

	$t_vars = get_defined_vars();
	foreach( array_keys($t_vars) as $t_var ) 
	{
		$t_lang_var = preg_replace( '/^TLS_/', '', $t_var );
		if ( $t_lang_var != $t_var) 
		{
			$g_lang_strings[$p_lang][$t_lang_var] = $$t_var;
		}
	}
}


/** 
 * Ensures that a language file has been loaded
 */
function lang_ensure_loaded( $p_lang ) {
	global $g_lang_strings;

	if ( !isset( $g_lang_strings[ $p_lang ] ) ) {
		lang_load( $p_lang );
	}
}


/** 
 * localize strings in array (used for example in html options element in form)
 * 
 * @param array $input_array list of localization string keys
 * @return array list of localized strings
 **/
function localize_array( $input_array ) {

	foreach ($input_array as &$value) {
    	$value = lang_get($value);
	}

	return $input_array;
}


/**
 * Translate array of TLS keys to array of localized labels
 * 
 * @param array $map_code_label map 
 *			 key=code
 *           value: string_to_translate, that can be found in strings.txt
 *			 if is_null(value), then key will be used as string_to_translate 
 *
 * @return array  map key=code
 *             value: lang_get(string_to_translate)
 * 
 * @internal revision:
 * 20100820 - franciscom - support for null as string_to_translate
 */
function init_labels($map_code_label)
{
	foreach($map_code_label as $key => $label)
	{
		$map_code_label[$key] = is_null($label) ? lang_get($key) : lang_get($label);
	}
	return $map_code_label;
}


/**
 * Add a date in smarty template (registered to Smarty class)
 * 
 * @tutorial usage: if registered as localize_date()
 *        {localize_date d='the date to localize'}
 * @uses localize_dateOrTimeStamp()
 * @internal Revisions:
 * 20050708 - fm - Modified to cope with situation where you need to assign 
 * a Smarty Template variable instead of generate output.
 * Now you can use this function in both situatuons.
 * 
 * if the key 'var' is found in the associative array instead of return a value,
 * this value is assigned to $params['var`]
 */
function localize_date_smarty($params, &$smarty)
{
	return localize_dateOrTimeStamp($params,$smarty,'date_format',$params['d']);
}


/**
 * Add a time in smarty template (registered to Smarty class)
 * @uses localize_dateOrTimeStamp()
 */
function localize_timestamp_smarty($params, &$smarty)
{
	return localize_dateOrTimeStamp($params,$smarty,'timestamp_format',$params['ts']);
}


/**
 * @param array $params used only if you call this from an smarty template
 *                or a wrapper in an smarty function.
 * @param integer $smarty: when not used in an smarty template, pass NULL.
 * @param $what: give info about what kind of value is contained in value.
 *              possible values: timestamp_format || date_format
 * @param $value: must be a date or time stamp in ISO format
 * 
 * @return string localized date or time
 */
function localize_dateOrTimeStamp($params,&$smarty,$what,$value)
{
	// to supress E_STRICT messages
	setlocale(LC_ALL, TL_DEFAULT_LOCALE);

	$format = config_get($what);
	if (!is_numeric($value))
	{
		$value = strtotime($value);
	}
	
	$retVal = strftime($format, $value);
	if(isset($params['var']))
	{
		$smarty->assign($params['var'],$retVal);
	}
	return $retVal;
}

?>
