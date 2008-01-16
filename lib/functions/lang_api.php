<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: lang_api.php,v $
 * @version $Revision: 1.9 $
 * @modified $Date: 2008/01/16 22:47:58 $ - $Author: havlat $
 *
 * rev :
 *       20070501 - franciscom - lang_get_smarty() now accept a list of
 *                               strings to translate.
 * 
 *       20070501 - franciscom - enabled logic to manage a custom_strings.txt file
 *       
*/

# Mantis - a php based bugtracking system
# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
# This program is distributed under the terms and conditions of the GPL
# See the README and LICENSE files for details
# Cache of localization strings in the language specified by the last                
# 20050508 - fm - changes to lang_get_smarty()
#
# lang_load call                                                                     
$g_lang_strings = array();

                                                                                     
# stack for language overrides                                                       
$g_lang_overrides = array();                                                         

# ------------------
# Retrieves an internationalized string
#  This function will return one of (in order of preference):
#    1. The string in the current user's preferred language (if defined)
#    2. The string in English
#
# rev: 
#      20070501 - franciscom - added TL_LOCALIZE_TAG in order to 
#                              improve label management for custom fields
#
function lang_get( $p_string, $p_lang = null)
{
	global $TLS_STRINGFILE_CHARSET;
	$t_lang = $p_lang;

	if (null === $p_lang)
		$t_lang = isset($_SESSION['locale']) ? $_SESSION['locale'] : null;
	if (null === $t_lang)
		$t_lang = TL_DEFAULT_LOCALE;

	lang_ensure_loaded($t_lang);
	global $g_lang_strings;
	//if lang_get is used to localize a string from the DB (like custom field label) we must simply passthrough the string
	if (isset($g_lang_strings[$t_lang][$p_string]))
	{
		$the_str = $g_lang_strings[$t_lang][$p_string];
		if (!isset($TLS_STRINGFILE_CHARSET))
			$TLS_STRINGFILE_CHARSET = "ISO-8859-1";
		$the_str = iconv($TLS_STRINGFILE_CHARSET,TL_TPL_CHARSET,$the_str);	
	}
	else
		$the_str = TL_LOCALIZE_TAG .$p_string;
	
	return $the_str;
}

/* 
----------------------------------------------------------------------
20071225 - franciscom
When you choose to have translation results assigned to a smarty variable
now you can send a list (string with ',' as element separator) of labels
to be translated.
In this situation you will get as result an associative array that uses
as key the string to be translated.

Example:

{lang_get s='th_testsuite,details' var='labels'}

labels will be : labels['th_testsuite']
                 labels['details']

and on smarty template you will access in this way: $labels.details


20050708 - fm
Modified to cope with situation where you need
to assign a Smarty Template variable instead
of generate output.
Now you can use this function in both situatuons.

if the key 'var' is found in the associative array
instead of return a value, this value is assigned
to $params['var`]

----------------------------------------------------------------------*/
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
// -----------------------------------------------


# ---------------------------------------------------------------
# Loads the specified language and stores it in $g_lang_strings,
# to be used by lang_get
function lang_load( $p_lang ) {
	global $g_lang_strings, $g_active_language;
	global $TLS_STRINGFILE_CHARSET;
	$g_active_language  = $p_lang;
	if ( isset( $g_lang_strings[ $p_lang ] ) ) {
		return;
	}

	$t_lang_dir = dirname(dirname ( dirname ( __FILE__ ) )) . DIRECTORY_SEPARATOR . 
	              'locale' . DIRECTORY_SEPARATOR . $p_lang . DIRECTORY_SEPARATOR;

				  
	if (file_exists($t_lang_dir . 'strings.txt'))
		require_once( $t_lang_dir . 'strings.txt' );
	else
	{
		$t_lang_dir = dirname(dirname ( dirname ( __FILE__ ) )) . DIRECTORY_SEPARATOR . 
	              'locale' . DIRECTORY_SEPARATOR . 'en_GB' . DIRECTORY_SEPARATOR;
		require_once( $t_lang_dir . 'strings.txt' );
	}
	# Allow overriding strings declared in the language file.
	# custom_strings_inc.php can use $g_active_language
	#
	# 20070501 - franciscom
	$t_custom_strings = $t_lang_dir . 'custom_strings.txt';
	if ( file_exists( $t_custom_strings ) ) {
	     require_once( $t_custom_strings ); 
	}
	$t_vars = get_defined_vars();

	foreach ( array_keys( $t_vars ) as $t_var ) {
		$t_lang_var = ereg_replace( '^TLS_', '', $t_var );
		if ( $t_lang_var != $t_var) {
			$g_lang_strings[ $p_lang ][ $t_lang_var ] = $$t_var;
		}
	}
}


# Ensures that a language file has been loaded
function lang_ensure_loaded( $p_lang ) {
	global $g_lang_strings;

	if ( ! isset( $g_lang_strings[ $p_lang ] ) ) {
		lang_load( $p_lang );
	}
}

/** localize strings in array (used for example in html_options)*/
function localize_array( $input_array ) {

	foreach ($input_array as &$value) {
    	$value = lang_get($value);
	}
	
	return $input_array;
}
?>
