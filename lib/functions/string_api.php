<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * String Processing functions
 * 
 * @package 	TestLink
 * @copyright 	2007-2016, TestLink community
 * @copyright 	Copyright (C) 2002 - 2004  Mantis Team
 * 				The base for certain code was adapted from Mantis - a php based bugtracking system
 *     
 **/


/** 
 * Preserve spaces at beginning of lines. 
 * Lines must be separated by \n rather than < br / > 
 **/
function string_preserve_spaces_at_bol( $p_string ) 
{
	$lines = explode( "\n", $p_string );
	$line_count = count( $lines );
	for ( $i = 0; $i < $line_count; $i++ ) {
		$count	= 0;
		$prefix	= '';
		
		$t_char = substr( $lines[$i], $count, 1 );
		$spaces = 0;
		while ( ( $t_char  == ' ' ) || ( $t_char == "\t" ) ) {
			if ( $t_char == ' ' )
				$spaces++;
			else
				$spaces += 4; // 1 tab = 4 spaces, can be configurable.
			
			$count++;
			$t_char = substr( $lines[$i], $count, 1 );
		}
		
		for ( $j = 0; $j < $spaces; $j++ ) {
			$prefix .= '&nbsp;';
		}
		
		$lines[$i] = $prefix . substr( $lines[$i], $count );
	}
	return implode( "\n", $lines );
}


/** 
 * Prepare a string to be printed without being broken into multiple lines 
 **/
function string_no_break( $p_string ) {
	if ( strpos( $p_string, ' ' ) !== false ) {
		return '<span class="nowrap">' . $p_string . "</span>";
	} else {
		return $p_string;
	}
}

/** 
 * Similar to nl2br, but fixes up a problem where new lines are doubled between < pre > tags.
 * additionally, wrap the text an $p_wrap character intervals if the config is set
 * 
 * @author Mantis BT team
 */
function string_nl2br( $p_string, $p_wrap = 100 ) 
{
		$p_string = nl2br( $p_string );

		// fix up eols within <pre> tags
		$pre2 = array();
		preg_match_all("/<pre[^>]*?>(.|\n)*?<\/pre>/", $p_string, $pre1);
		for ( $x = 0; $x < count($pre1[0]); $x++ ) 
		{
			$pre2[$x] = preg_replace("/<br[^>]*?>/", "", $pre1[0][$x]);
			// this may want to be replaced by html_entity_decode (or equivalent)
			//     if other encoded characters are a problem
			$pre2[$x] = preg_replace("/&nbsp;/", " ", $pre2[$x]);
			if ( ON == config_get( 'wrap_in_preformatted_text' ) ) 
			{
				$pre2[$x] = preg_replace("/([^\n]{".$p_wrap."})(?!<\/pre>)/", "$1\n", $pre2[$x]);
			}
			$pre1[0][$x] = "/" . preg_quote($pre1[0][$x], "/") . "/";
		}

		return preg_replace( $pre1[0], $pre2, $p_string );
}


/** 
 * Prepare a multiple line string for display to HTML 
 **/
function string_display( $p_string ) 
{	
	$p_string = string_strip_hrefs( $p_string );
	$p_string = string_html_specialchars( $p_string );
	$p_string = string_restore_valid_html_tags( $p_string, /* multiline = */ true );
	$p_string = string_preserve_spaces_at_bol( $p_string );
	$p_string = string_nl2br( $p_string );

	return $p_string;
}


/** Prepare a single line string for display to HTML */
function string_display_line( $p_string ) 
{
	$p_string = string_strip_hrefs( $p_string );
	$p_string = string_html_specialchars( $p_string );
	$p_string = string_restore_valid_html_tags( $p_string, /* multiline = */ false );
	
	return $p_string;
}


/** 
 * Prepare a string for display to HTML and add href anchors for URLs, emails,
 * bug references, and cvs references
 */
function string_display_links( $p_string ) 
{
	$p_string = string_display( $p_string );
	$p_string = string_insert_hrefs( $p_string );
	return $p_string;
}


/** 
 * Prepare a single line string for display to HTML and add href anchors for
 * URLs, emails, bug references, and cvs references
 */ 
function string_display_line_links( $p_string ) 
{
	$p_string = string_display_line( $p_string );
	$p_string = string_insert_hrefs( $p_string );

	return $p_string;
}


/** Prepare a string for display in rss */
function string_rss_links( $p_string ) 
{
	// rss can not start with &nbsp; which spaces will be replaced into by string_display().
	$t_string = trim( $p_string );

	// same steps as string_display_links() without the preservation of spaces since &nbsp; is undefined in XML.
	$t_string = string_strip_hrefs( $t_string );
	$t_string = string_html_specialchars( $t_string );
	$t_string = string_restore_valid_html_tags( $t_string );
	$t_string = string_nl2br( $t_string );
	$t_string = string_insert_hrefs( $t_string );
	$t_string = string_process_bug_link( $t_string, /* anchor */ true, /* detailInfo */ false, /* fqdn */ true );
	$t_string = string_process_bugnote_link( $t_string, /* anchor */ true, /* detailInfo */ false, /* fqdn */ true );
	$t_string = string_process_cvs_link( $t_string );
	# another escaping to escape the special characters created by the generated links
	$t_string = string_html_specialchars( $t_string );

	return $t_string;
}

   
/** 
 * Prepare a string for plain text display in email 
 **/
function string_email( $p_string ) 
{
	$p_string = string_strip_hrefs( $p_string );
	return $p_string;
}
 
  
/**  
 * Prepare a string for plain text display in email and add URLs for bug
 * links and cvs links
 */     
function string_email_links( $p_string ) {
	$p_string = string_email( $p_string );
  return $p_string;
}


/** 
 * Process a string for display in a textarea box 
 **/
function string_textarea( $p_string ) 
{
	$p_string = string_html_specialchars( $p_string );
	return $p_string;
}


/** 
 * Process a string for display in a text box
 */
function string_attribute( $p_string ) 
{
	$p_string = string_html_specialchars( $p_string );

	return $p_string;
}


/** 
 * Process a string for inclusion in a URL as a GET parameter 
 */
function string_url( $p_string ) 
{
	$p_string = rawurlencode( $p_string );

	return $p_string;
}


/** 
 * validate the url as part of this site before continuing 
 **/
function string_sanitize_url( $p_url ) {

	$t_url = strip_tags( urldecode( $p_url ) );
	if ( preg_match( '?http(s)*://?', $t_url ) > 0 ) { 
		// no embedded addresses
		if ( preg_match( '?^' . config_get( 'path' ) . '?', $t_url ) == 0 ) { 
			// url is ok if it begins with our path, if not, replace it
			$t_url = 'index.php';
		}
	}
	if ( $t_url == '' ) {
		$t_url = 'index.php';
	}
	
	// split and encode parameters
	if ( strpos( $t_url, '?' ) !== FALSE ) {
		list( $t_path, $t_param ) = split( '\?', $t_url, 2 );
		if ( $t_param !== "" ) {
			$t_vals = array();
			parse_str( $t_param, $t_vals );
			$t_param = '';
			foreach($t_vals as $k => $v) {
				if ($t_param != '') {
					$t_param .= '&'; 
				}
				$t_param .= "$k=" . urlencode( strip_tags( urldecode( $v ) ) );
			}
			return $t_path . '?' . $t_param;
		} else {
			return $t_path;
		}
	} else {
		return $t_url;
	}
}
	

// ----- Tag Processing -------------------------------------------------------

/**
 * Search email addresses and URLs for a few common protocols in the given
 * string, and replace occurences with href anchors.
 * @param string $p_string
 * @return string
 */
function string_insert_hrefs( $p_string ) {
	static $s_url_regex = null;
	static $s_email_regex = null;
	static $s_anchor_regex = '/(<a[^>]*>.*?<\/a>)/is';

	if( !config_get( 'html_make_links' ) ) {
		return $p_string;
	}

	$t_change_quotes = false;
	if( ini_get_bool( 'magic_quotes_sybase' ) && function_exists( 'ini_set' ) ) {
		$t_change_quotes = true;
		ini_set( 'magic_quotes_sybase', false );
	}

	# Initialize static variables
	if ( is_null( $s_url_regex ) ) {
		# URL protocol. The regex accepts a small subset from the list of valid
		# IANA permanent and provisional schemes defined in
		# http://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
		$t_url_protocol = '(?:https?|s?ftp|file|irc[6s]?|ssh|telnet|nntp|git|svn(?:\+ssh)?|cvs):\/\/';

		# %2A notation in url's
		$t_url_hex = '%[[:digit:]A-Fa-f]{2}';

		# valid set of characters that may occur in url scheme. Note: - should be first (A-F != -AF).
		$t_url_valid_chars       = '-_.,!~*\';\/?%^\\\\:@&={\|}+$#[:alnum:]\pL';
		$t_url_chars             = "(?:${t_url_hex}|[${t_url_valid_chars}\(\)\[\]])";
		$t_url_chars2            = "(?:${t_url_hex}|[${t_url_valid_chars}])";
		$t_url_chars_in_brackets = "(?:${t_url_hex}|[${t_url_valid_chars}\(\)])";
		$t_url_chars_in_parens   = "(?:${t_url_hex}|[${t_url_valid_chars}\[\]])";

		$t_url_part1 = "${t_url_chars}";
		$t_url_part2 = "(?:\(${t_url_chars_in_parens}*\)|\[${t_url_chars_in_brackets}*\]|${t_url_chars2})";

		$s_url_regex = "/(${t_url_protocol}(${t_url_part1}*?${t_url_part2}+))/su";

		# e-mail regex
		$s_email_regex = substr_replace( email_regex_simple(), '(?:mailto:)?', 1, 0 );
	}

	# Find any URL in a string and replace it by a clickable link
	$t_function = create_function( '$p_match', '
		$t_url_href = \'href="\' . rtrim( $p_match[1], \'.\' ) . \'"\';
		return "<a ${t_url_href}>${p_match[1]}</a> [<a ${t_url_href} target=\"_blank\">^</a>]";
	' );
	$p_string = preg_replace_callback( $s_url_regex, $t_function, $p_string );
	if( $t_change_quotes ) {
		ini_set( 'magic_quotes_sybase', true );
	}

	# Find any email addresses in the string and replace them with a clickable
	# mailto: link, making sure that we skip processing of any existing anchor
	# tags, to avoid parts of URLs such as https://user@example.com/ or
	# http://user:password@example.com/ to be not treated as an email.
	$t_pieces = preg_split( $s_anchor_regex, $p_string, null, PREG_SPLIT_DELIM_CAPTURE );
	$p_string = '';
	foreach( $t_pieces as $piece ) {
		if( preg_match( $s_anchor_regex, $piece ) ) {
			$p_string .= $piece;
		} else {
			$p_string .= preg_replace( $s_email_regex, '<a href="mailto:\0">\0</a>', $piece );
		}
	}

	return $p_string;
}


/** 
 * Detect href anchors in the string and replace them with URLs and email addresses 
 **/
function string_strip_hrefs( $p_string ) 
{
	# First grab mailto: hrefs.  We don't care whether the URL is actually
	# correct - just that it's inside an href attribute.
	$p_string = preg_replace( '/<a\s[^\>]*href="mailto:([^\"]+)"[^\>]*>[^\<]*<\/a>/si',
								'\1', $p_string);

	# Then grab any other href
	$p_string = preg_replace( '/<a\s[^\>]*href="([^\"]+)"[^\>]*>[^\<]*<\/a>/si',
								'\1', $p_string);
	return $p_string;
}


/**
 * This function looks for text with htmlentities
 * like &lt;b&gt; and converts is into corresponding
 * html &lt;b&gt; based on the configuration presets
 */
function string_restore_valid_html_tags( $p_string, $p_multiline = true ) 
{
	$t_html_valid_tags = config_get( $p_multiline ? 'html_valid_tags' : 'html_valid_tags_single_line' );

	if ( OFF === $t_html_valid_tags || is_blank( $t_html_valid_tags ) ) {
		return $p_string;
	}

	$tags = explode( ',', $t_html_valid_tags );
	foreach ($tags as $key => $value) 
	{ 
    	if ( !is_blank( $value ) ) {
        	$tags[$key] = trim($value); 
        }
    }
    $tags = implode( '|', $tags);

	$p_string = preg_replace( '/&lt;(' . $tags . ')\s*&gt;/ui', '<\\1>', $p_string );
	$p_string = preg_replace( '/&lt;\/(' . $tags . ')\s*&gt;/ui', '</\\1>', $p_string );
	$p_string = preg_replace( '/&lt;(' . $tags . ')\s*\/&gt;/ui', '<\\1 />', $p_string );


	return $p_string;
}


/**	
 * Return a string with the $p_character pattern repeated N times.
 * 
 * @param string $p_character - pattern to repeat
 * @param integer $p_repeats - number of times to repeat.
 */
function string_repeat_char( $p_character, $p_repeats ) {
	return str_pad( '', $p_repeats, $p_character );
}


/**
 * Format date for display
 */ 
function string_format_complete_date( $p_date ) {
	$t_timestamp = db_unixtimestamp( $p_date );
	return date( config_get( 'complete_date_format' ), $t_timestamp );
}


/** 
 * Shorten a string for display on a dropdown to prevent the page rendering too wide
 */
function string_shorten( $p_string ) {
	$t_max = config_get( 'max_dropdown_length' );
	if ( ( tlStrLen($p_string ) > $t_max ) && ( $t_max > 0 ) ){
		$t_pattern = '/([\s|.|,|\-|_|\/|\?]+)/';
		$t_bits = preg_split( $t_pattern, $p_string, -1, PREG_SPLIT_DELIM_CAPTURE );

		$t_string = '';
		$t_last = $t_bits[ count( $t_bits ) - 1 ];
		$t_last_len = tlStrLen( $t_last );

		foreach ( $t_bits as $t_bit ) {
			if ( ( tlStrLen( $t_string ) + tlStrLen( $t_bit ) + $t_last_len + 3 <= $t_max )
				|| ( strpos( $t_bit, '.,-/?' ) > 0 ) ) {
				$t_string .= $t_bit;
			} else {
				break;
			}
		}
		$t_string .= '...' . $t_last;
		return $t_string;
	} else {
		return $p_string;
	}
}


/**
 * remap a field name to a string name (for sort filter)
 */
function string_get_field_name( $p_string ) {

	$t_map = array(
			'last_updated' => 'last_update',
			'id' => 'email_bug'
			);

	$t_string = $p_string;
	if ( isset( $t_map[ $p_string ] ) ) {
		$t_string = $t_map[ $p_string ];
	}
	return lang_get_defaulted( $t_string );
}


/** 
 * Calls htmlentities on the specified string, passing along
 * the current charset.
 */
function string_html_entities( $p_string ) {
	return htmlentities( $p_string, ENT_COMPAT, config_get('charset') );
}


/** 
 * Calls htmlspecialchars on the specified string, passing along
 * the current charset, if the current PHP version supports it.
 */
function string_html_specialchars( $p_string ) {
	# achumakov: @ added to avoid warning output in unsupported codepages
	# e.g. 8859-2, windows-1257, Korean, which are treated as 8859-1.
	# This is VERY important for Eastern European, Baltic and Korean languages
	return preg_replace("/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", @htmlspecialchars( $p_string, ENT_COMPAT, config_get('charset') ) );
}


/** 
 * Prepares a string to be used as part of header().
 */
function string_prepare_header( $p_string ) {
	$t_string = $p_string;

	$t_truncate_pos = strpos($p_string, "\n");
	if ($t_truncate_pos !== false ) {
		$t_string = substr($t_string, 0, $t_truncate_pos);
	}

	$t_truncate_pos = strpos($p_string, "\r");
	if ($t_truncate_pos !== false ) {
		$t_string = substr($t_string, 0, $t_truncate_pos);
	}

	return $t_string;
}


/** 
 * Checks the supplied string for scripting characters, if it contains any, then return true, otherwise return false.
 * 
 * @param string $p_string
 * @return boolean
 */
function string_contains_scripting_chars( $p_string ) {
	if ( ( strstr( $p_string, '<' ) !== false ) || ( strstr( $p_string, '>' ) !== false ) ) {
		return true;
	}

	return false;
}

/**
 * Use a simple perl regex for valid email addresses.  This is not a complete regex,
 * as it does not cover quoted addresses or domain literals, but it is simple and
 * covers the vast majority of all email addresses without being overly complex.
 * @return string
 */
function email_regex_simple() {
	static $s_email_regex = null;

	if( is_null( $s_email_regex ) ) {
		$t_recipient = "([a-z0-9!#*+\/=?^_{|}~-]+(?:\.[a-z0-9!#*+\/=?^_{|}~-]+)*)";

		# a domain is one or more subdomains
		$t_subdomain = "(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)";
		$t_domain    = "(${t_subdomain}(?:\.${t_subdomain})*)";

		$s_email_regex = "/${t_recipient}\@${t_domain}/i";
	}
	return $s_email_regex;
}
