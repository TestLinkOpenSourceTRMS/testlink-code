#!/usr/bin/php
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * command line script: update localization files strings.txt, texts.php and
 * description.php according to the current english file by adding new variables
 * 
 * Usage: 
 * 	1. correct the first line to point php binary
 *  2. Modify a path to master file (en_GB) - $file_eng
 *  3. Linux: Allow execute - #chmod u+x tl_lang_parser.php
 *  4. Run the file with to-be-updated file as argument
 * 		#tl_lang_parser.php /home/havlatm/www/tl_head/locale/cs_CZ/strings.txt
 *     if you run from default location (locale) use e.g.:
 *      #tl_lang_parser.php de_DE/strings.txt
 * 
 *  Note: to have report about parsing redirect script to file; for example
 * 		#tl_lang_parser.php strings.txt > report.txt (RECOMMENDED)
 * 
 * @package 	TestLink
 * @author 		Martin Havlat, Julian Krien
 * @copyright 	2003, TestLink community 
 * @version    	CVS: $Id: tl_lang_parser.php,v 1.2.6.3 2010/12/12 10:34:14 mx-julian Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
 * 20100517 - Julian - major changes: improved robustness, script can now also be
 *                     used to update texts.php and description.php
 * 
 **/

/** Set path to your en_GB english file */
$file_eng = 'en_GB/strings.txt';
//$file_eng = 'en_GB/texts.php';
//$file_eng = 'en_GB/description.php';

/** Set true if you would like to have original file with 'bck' extension */
$do_backup_file = FALSE;




// ---------------------------------------------------------------------------
if ($argc < 1)
{
	echo 'Usage: #tl_lang_parser.php <localization_file_to_be_updated>';
	exit;
}
else
	$file_lang_old = $argv[1];

$out = ''; // data for output file
$var_counter = 0;
$var_counter_new = 0;
$new_vars = array();

echo "===== Start TestLink lang_parser =====\n";

// read english file
if (file_exists($file_eng) && is_readable ($file_eng))
{ 
	echo "Master file ($file_eng) is readable OK.\n";
	$lines_eng = file( $file_eng );
}
else
{
	echo "Master File ($file_eng) is not readable. Exit.\n";
	exit;
}
// read language file
if (file_exists($file_lang_old) && is_readable ($file_lang_old))
{ 
	echo "File to be updated ({$file_lang_old}) is readable OK.\n";
	$lines_lang_old = file( $file_lang_old );
}
else
{
	echo "File to be updated ({$file_lang_old}) is not readable. Exit.\n";
	exit;
}

$lines_eng_count = sizeof($lines_eng);
$lines_old_count = sizeof($lines_lang_old);
echo "Master file lines = ".($lines_eng_count+1)."\n";
echo "File to update lines = ".($lines_old_count+1)."\n";

// find end of english header:
for( $i = 0; $i < $lines_eng_count; $i++ )
{
	// parse revision of master file
    if (preg_match('/\$Id.+v\s(\S+)\s.*/', $lines_eng[$i], $eng_revision) )
    {
        $revision_comment = $eng_revision[1];
        echo "Master file revision: ".$revision_comment."\n";
    }
    // search for "*/" at the end of a line
    if (preg_match("/\*\//", $lines_eng[$i]) )
    {
        echo "Master file: End of header is line = ".($i+1)."\n";
        $begin_line = $i + 1;
        $i = $lines_eng_count;
    }
}

// copy existing localization file header
for( $i = 0; $i < $lines_old_count; $i++ )
{
    if (preg_match("/\*\//", $lines_lang_old[$i]) )
    {
        echo "File to be updated: End of header is line = ".($i+1)."\n";
        $begin_line_old = $i + 1;
        $i = $lines_old_count;
		$out .= " * Scripted update according en_GB string file (version: ".$revision_comment.") \n";
		$out .= " *\n **/\n";
    }
    else
		$out .= $lines_lang_old[$i];
}


// compile output array based on english file
for( $i = $begin_line; $i < $lines_eng_count; $i++ )
{
//    echo "$i >> {$lines_eng[$i]}\n";

	// copy comments:
    if (preg_match("/^\/\//", $lines_eng[$i]) )
    {
        echo "\n\n=line ".($i+1)."=\nCopy comment to file to be updated:\n".
			"-----------------------------------------\n".
			trim($lines_eng[$i]). "\n".
			"-----------------------------------------\n";
        $out .= $lines_eng[$i];
    }

	// copy empty line
    elseif (preg_match('/^([\s\t]*)$/', $lines_eng[$i]))
    {
        echo "\n\n=line ".($i+1)."=\nCopy empty line to file to be updated\n";
        $out .= "\r\n";
    }

	// parse a line with variable definition
    elseif (preg_match('/^\$TLS_(\w+\[?\'?\w*\'?\]?)[\s]*=[\s]*(.*)$/', $lines_eng[$i], $parsed_line))
    {
        $var_counter++;
        $var_name = '$TLS_'.$parsed_line[1];
        $bLocalized = FALSE;
        $localizedLine = '';
//        print_r($parsed_line);
        echo "\n\n=line ".($i+1)."=\nFound variable '$var_name' on master file\n";
        
        // get localized value if defined - parse old localized strings
		for( $k = $begin_line_old; $k < $lines_old_count; $k++ )
		{
			if (preg_match('/^\\'.addcslashes($var_name,'\'\[\]').'[\s]*=[\s]*.+$/', $lines_lang_old[$k]))
			{
		        echo "Found localization for variable '$var_name' on file to be updated (line ".($k+1).")\n";
				$bLocalized = TRUE;
		        $localizedLine = $lines_lang_old[$k];
				
				// check if localized value exceed to more lines - semicolon is not found
				while (!(preg_match('/;[\s]*$/', $lines_lang_old[$k])
				|| preg_match('/;[\s]*[\/]{2}/', $lines_lang_old[$k])))
				{
			        $k++;
			        //echo "\t(line $k)Found localization for variable $var_name extends to this line\n";
				    $localizedLine .= $lines_lang_old[$k];
				}
				$k = $lines_old_count; // exit more parsing old file
			}
		}
		
		if ($bLocalized)
		{
	        echo "Keep existing localization on file to be updated:\n".
				"-----------------------------------------\n".
				trim($localizedLine). "\n".
				"-----------------------------------------\n";
        	$out .= $localizedLine;
		} 
		else 
		{
	        echo "Localization doesn't exists. Copy from master file to file to be updated:\n".
			"-----------------------------------------\n".
			trim($lines_eng[$i]). "\n";
			//add a todo on newly added lines
		    //$out .= trim($lines_eng[$i]). " //TODO: localize\r\n";
			$out .= $lines_eng[$i];
		    $var_counter_new++;
		    $new_vars[$i] = $var_name;

        	// check multiline value (check semicolon or semicolon with comment)
			while (!(preg_match('/^(.*);[\s]*$/', $lines_eng[$i])
				|| preg_match('/^(.*);[\s]*[\/]{2}/', $lines_eng[$i])))
			{
				$i++;
				echo trim($lines_eng[$i]). "\n";
				//add a todo on newly added lines
				//$out .= trim($lines_eng[$i]). " //TODO: localize\r\n";
				$out .= $lines_eng[$i];
			}
			echo "-----------------------------------------\n";

		}
    }

	// end of file    
    elseif (preg_match('/^\?\>/', $lines_eng[$i]))
    {
        $out .= "?>";
    }

	// skip unused multiline values
	// must be a multiline value if it is no variable/comment/empty line/end of file
    elseif (preg_match('/^.*/', $lines_eng[$i]))
    	echo "\n\n=line ".($i+1)."=\nSkipped line (expected unused multiline value on master file)\n";

	// something wrong?
    else
    {
    	echo "\n\n=line ".($i+1)."=\nERROR: please fix this line\n" . $lines_eng[$i];
    	exit;
    }
}


// create backup if defined
if ($do_backup_file)
	rename($file_lang_old, $file_lang_old.'.bck');

	
// save output
$fp = fopen($file_lang_old, "w");
fwrite($fp, $out);
fclose($fp);

echo "\n\nUpdated file: ".$file_lang_old;
echo "\nCompleted! The script has parsed $var_counter strings and add $var_counter_new new variables.\n";
echo implode("\n", $new_vars);
echo "\n\n===== Bye =====\n";

?>
