<?php
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

function lang_get( $p_string, $p_lang = null )
{
	$t_lang = $p_lang;

	if (null === $p_lang)
		$t_lang = isset($_SESSION['locale']) ? $_SESSION['locale'] : null;
	if (null === $t_lang)
		$t_lang = TL_DEFAULT_LOCALE;

	lang_ensure_loaded( $t_lang );

	global $g_lang_strings;
	$the_str = isset($g_lang_strings[$t_lang][$p_string]) ? $g_lang_strings[$t_lang][$p_string] : "LOCALIZE: " .$p_string;
		
	if (TL_TPL_CHARSET == 'UTF-8')	
		return utf8_encode($the_str);
	else
		return $the_str;
}

/* 
-----------------------------------------------
20050708 - fm
Modified to cope with situation where you need
to assign a Smarty Template variable instead
of generate output.
Now you can use this function in both situatuons.

if the key 'var' is found in the associative array
instead of return a value, this value is assigned
to $params['var`]

-------------------------------------------------
*/
function lang_get_smarty($params, &$smarty)
{
	if (isset($params['locale']))
	{
		$the_ret = lang_get($params['s'], $params['locale']);    
	}	
	else
	{
		$the_ret = lang_get($params['s']);  
	}
	
	// 20050508 - fm
	if(	isset($params['var']) )
	{
	  $smarty->assign($params['var'], $the_ret);
	}
	else
	{
	  return $the_ret;
	}
}
// -----------------------------------------------


# ---------------------------------------------------------------
# Loads the specified language and stores it in $g_lang_strings,
# to be used by lang_get
function lang_load( $p_lang ) {
	global $g_lang_strings, $g_active_language;

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
	//$t_custom_strings = dirname ( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'custom_strings_inc.php';
	//if ( file_exists( $t_custom_strings ) ) {
	//	require( $t_custom_strings ); # this may be loaded multiple times, once per language
	//}

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
?>
