<?php
// vim: set expandtab tabstop=4 shiftwidth=4:
// This code that was derived from the original PHPLIB Template class
// is copyright by Kristian Koehntopp, NetUSE AG and was released
// under the LGPL.
//
// Authors: Kristian Koehntopp <kris@koehntopp.de> (original from PHPLIB)
//          Bjoern Schotte <bjoern@rent-a-phpwizard.de> (PEARification)                
//          Martin Jansen <mj@php.net> (PEAR conformance)            
//
// $Id: PHPLIB.php,v 1.1 2004/10/27 20:36:40 chadrosen Exp $
//

//require_once "PEAR.php";

/**
 * Converted PHPLIB Template class
 *
 * For those who want to use PHPLIB's fine template class,
 * here's a PEAR conforming class with the original PHPLIB
 * template code from phplib-stable CVS. Original author
 * was Kristian Koehntopp <kris@koehntopp.de>
 *
 * @author  Bjoern Schotte <bjoern@rent-a-phpwizard.de>
 * @author  Martin Jansen <mj@php.net> (PEAR conformance)
 * @version 1.0
 */
class Template_PHPLIB
{
    /**
     * If set, echo assignments
     * @var bool
     */
    var $debug     = false;

    /**
     * $file[handle] = "filename";
     * @var array
     */
    var $file  = array();

    /**
     * fallback paths that should be defined in a child class
     * @var array
     */
    var $file_fallbacks = array();

    /**
     * Relative filenames are relative to this pathname
     * @var string
     */
    var $root   = "";

    /*
     * $_varKeys[key] = "key"
     * @var array
     */
    var $_varKeys = array();
    
    /**
     * $_varVals[key] = "value";
     * @var array
     */
    var $_varVals = array();

    /**
     * "remove"  => remove undefined variables
     * "comment" => replace undefined variables with comments
     * "keep"    => keep undefined variables
     * @var string
     */
    var $unknowns = "remove";
  
    /**
     * "yes" => halt, "report" => report error, continue, "no" => ignore error quietly
     * @var string
     */
    var $haltOnError  = "report";
  
    /**
     * The last error message is retained here
     * @var string
     * @see halt
     */
    var $_lastError     = "";


    /**
     * Constructor
     *
     * @access public
     * @param  string template root directory
     * @param  string how to handle unknown variables
     * @param  array fallback paths
     */
    function Template_PHPLIB($root = ".", $unknowns = "remove", $fallback="")
    {
        $this->setRoot($root);
        $this->setUnknowns($unknowns);
        if (is_array($fallback)) $this->file_fallbacks = $fallback;
    }

    /**
     * Sets the template directory
     *
     * @access public
     * @param  string new template directory
     * @return bool
     */
    function setRoot($root)
    {
        if (!is_dir($root)) {
            $this->halt("setRoot: $root is not a directory.");
            return false;
        }
    
        $this->root = $root;
    
        return true;
    }

    /**
     * What to do with unknown variables
     *
     * three possible values:
     *
     * - "remove" will remove unknown variables
     *   (don't use this if you define CSS in your page)
     * - "comment" will replace undefined variables with comments
     * - "keep" will keep undefined variables as-is
     *
     * @access public
     * @param  string unknowns
     */
    function setUnknowns($unknowns = "keep")
    {
        $this->unknowns = $unknowns;
    }

    /**
     * Set appropriate template files
     *
     * With this method you set the template files you want to use.
     * Either you supply an associative array with key/value pairs
     * where the key is the handle for the filname and the value
     * is the filename itself, or you define $handle as the file name
     * handle and $filename as the filename if you want to define only
     * one template.
     *
     * @access public
     * @param  mixed handle for a filename or array with handle/name value pairs
     * @param  string name of template file
     * @return bool
     */
    function setFile($handle, $filename = "")
    {
        if (!is_array($handle)) {
    
            if ($filename == "") {
                $this->halt("setFile: For handle $handle filename is empty.");
                return false;
            }
      
            $this->file[$handle] = $this->_filename($filename);
      
        } else {
    
            reset($handle);
            while (list($h, $f) = each($handle)) {
                $this->file[$h] = $this->_filename($f);
            }
        }
    }

    /**
     * Set a block in the appropriate template handle
     *
     * By setting a block like that:
     *
     * &lt;!-- BEGIN blockname --&gt;
     * html code
     * &lt;!-- END blockname --&gt;
     *
     * you can easily do repeating HTML code, i.e. output
     * database data nice formatted into a HTML table where
     * each DB row is placed into a HTML table row which is
     * defined in this block.
     * It extracts the template $handle from $parent and places
     * variable {$name} instead.
     *
     * @access public
     * @param  string parent handle
     * @param  string block name handle
     * @param  string variable substitution name
     */
    function setBlock($parent, $handle, $name = "")
    {
        if (!$this->_loadFile($parent)) {
            $this->halt("setBlock: unable to load $parent.");
            return false;
        }
    
        if ($name == "") {
            $name = $handle;
        }

        $str = $this->getVar($parent);
        $reg = "/[ \t]*<!--\s+BEGIN $handle\s+-->\s*?\n?(\s*.*?\n?)\s*<!--\s+END $handle\s+-->\s*?\n?/sm";
        preg_match_all($reg, $str, $m);
        $str = preg_replace($reg, "{" . "$name}", $str);

        if (isset($m[1][0])) $this->setVar($handle, $m[1][0]);
        $this->setVar($parent, $str);
    }

    /**
     * Set corresponding substitutions for placeholders
     *
     * @access public
     * @param  string name of a variable that is to be defined or an array of variables with value substitution as key/value pairs
     * @param  string value of that variable
     * @param  boolean if true, the value is appended to the variable's existing value
     */
    function setVar($varname, $value = "", $append = false)
    {
        if (!is_array($varname)) {

            if (!empty($varname))
                if ($this->debug) print "scalar: set *$varname* to *$value*<br>\n";

            $this->_varKeys[$varname] = $this->_varname($varname);
            ($append) ? $this->_varVals[$varname] .= $value : $this->_varVals[$varname] = $value;

        } else {
            reset($varname);

            while (list($k, $v) = each($varname)) {
                if (!empty($k))
                    if ($this->debug) print "array: set *$k* to *$v*<br>\n";

                $this->_varKeys[$k] = $this->_varname($k);
                ($append) ? $this->_varVals[$k] .= $v : $this->_varVals[$k] = $v;
            }
        }
    }

    /**
     * Substitute variables in handle $handle
     *
     * @access public
     * @param  string name of handle
     * @return mixed string substituted content of handle
     */
    function subst($handle)
    {
        if (!$this->_loadFile($handle)) {
            $this->halt("subst: unable to load $handle.");
            return false;
        }

        return @str_replace($this->_varKeys, $this->_varVals, $this->getVar($handle));
    }
  
    /**
     * Same as subst but printing the result
     *
     * @access  public
     * @brother subst
     * @param   string handle of template
     * @return  bool always false
     */
    function pSubst($handle)
    {
        print $this->subst($handle);
        return false;
    }

    /**
     * Parse handle into target
     *
     * Parses handle $handle into $target, eventually
     * appending handle at $target if $append is defined
     * as TRUE.
     *
     * @access public
     * @param  string target handle to parse into
     * @param  string which handle should be parsed
     * @param  boolean append it to $target or not?
     * @return string parsed handle
     */
    function parse($target, $handle, $append = false)
    {
        if (!is_array($handle)) {
            $str = $this->subst($handle);

            ($append) ? $this->setVar($target, $this->getVar($target) . $str) : $this->setVar($target, $str);
        } else {
            reset($handle);

            while (list(, $h) = each($handle)) {
                $str = $this->subst($h);
                $this->setVar($target, $str);
            }
        }

        return $str;
    }

    /**
     * Same as parse, but printing it.
     *
     * @access  public
     * @brother parse
     * @param   string target to parse into
     * @param   string handle which should be parsed
     * @param   should $handle be appended to $target?
     * @return  bool
     */
    function pParse($target, $handle, $append = false)
    {
        print $this->finish($this->parse($target, $handle, $append));
        return false;
    }
  
    /**
     * Return all defined variables and their values
     *
     * @access public
     * @return array with all defined variables and their values
     */
    function getVars()
    {
        reset($this->_varKeys);

        while (list($k, ) = each($this->_varKeys)) {
            $result[$k] = $this->getVar($k);
        }

        return $result;
    }

    /**
     * Return one or more specific variable(s) with their values.
     *
     * @access public    
     * @param  mixed array with variable names or one variable name as a string
     * @return mixed array of variable names with their values or value of one specific variable
     */
    function getVar($varname)
    {
        if (!is_array($varname)) {
            if (isset($this->_varVals[$varname])) {
                return $this->_varVals[$varname];
            } else {
                return "";
            }
        } else {
            reset($varname);
    
            while (list($k, ) = each($varname)) {
                $result[$k] = (isset($this->_varVals[$k])) ? $this->_varVals[$k] : "";
            }

            return $result;
        }
    }
  
    /**
     * Get undefined values of a handle
     *
     * @access public
     * @param  string handle name
     * @return mixed  false if an error occured or the undefined values
     */
    function getUndefined($handle)
    {
        if (!$this->_loadFile($handle)) {
            $this->halt("getUndefined: unable to load $handle.");
            return false;
        }
    
        preg_match_all("/{([^ \t\r\n}]+)}/", $this->getVar($handle), $m);
        $m = $m[1];
        if (!is_array($m)) {
            return false;
        }

        reset($m);
        while (list(, $v) = each($m)) {
            if (!isset($this->_varKeys[$v])) {
                $result[$v] = $v;
            }
        }
    
        if (isset($result) && count($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Finish string
     *
     * @access public
     * @param  string string to finish
     * @return finished, i.e. substituted string
     */
    function finish($str)
    {
        switch ($this->unknowns) {
            case "remove":
                $str = preg_replace('/{[^ \t\r\n}]+}/', "", $str);
                break;

            case "comment":
                $str = preg_replace('/{([^ \t\r\n}]+)}/', "<!-- Template $handle: Variable \\1 undefined -->", $str);
                break;
        }

        return $str;
    }

    /**
     * Print variable to the browser
     *
     * @access public
     * @param  string name of variable to print
     */
    function p($varname)
    {
        print $this->finish($this->getVar($varname));
    }

    /**
     * Get finished variable
     *
     * @access public public
     * @param  string variable to get
     * @return string string with finished variable
     */
    function get($varname)
    {
        return $this->finish($this->getVar($varname));
    }

    /**
     * Complete filename
     *
     * Complete filename, i.e. testing it for slashes
     *
     * @access private
     * @param  string filename to be completed
     * @return string completed filename
     */
    function _filename($filename)
    {
//        if (substr($filename, 0, 1) != "/") {
//            $filename = $this->root."/".$filename;
//        }

        if (file_exists($filename)) return $filename;
        if (is_array($this->file_fallbacks) && count($this->file_fallbacks) > 0) {
            reset($this->file_fallbacks);
            while (list(,$v) = each($this->file_fallbacks)) {
                if (file_exists($v.basename($filename))) return $v.basename($filename);
            }
            $this->halt(sprintf("filename: file %s does not exist in the fallback paths %s.",$filename,implode(",",$this->file_fallbacks)));
            return false;
        } else {
            $this->halt(sprintf("filename: file %s does not exist.",$filename));
            return false;
        }

        return $filename;
    }

    /**
     * Protect a replacement variable
     *
     * @access private
     * @param  string name of replacement variable
     * @return string replaced variable
     */
    function _varname($varname)
    {
        return "{".$varname."}";
    }

    /**
     * load file defined by handle if it is not loaded yet
     *
     * @access private
     * @param  string handle
     * @return bool   FALSE if error, true if all is ok
     */
    function _loadFile($handle)
    {
        if (isset($this->_varKeys[$handle]) and !empty($this->_varVals[$handle])) {
            return true;
        }

        if (!isset($this->file[$handle])) {
            $this->halt("loadfile: $handle is not a valid handle.");
            return false;
        }

        $filename = $this->file[$handle];
        if (function_exists("file_get_contents")) {
            $str = file_get_contents($filename);
        } else {
            if (!$fp = @fopen($filename,"r")) {
                $this->halt("loadfile: couldn't open $filename");
                return false;
            }

            $str = fread($fp,filesize($filename));
            fclose($fp);
        }

        if ($str=='') {
            $this->halt("loadfile: While loading $handle, $filename does not exist or is empty.");
            return false;
        }

        $this->setVar($handle, $str);

        return true;
    }

    /**
     * Error function. Halt template system with message to show
     *
     * @access public
     * @param  string message to show
     * @return bool
     */
    function halt($msg)
    {
        $this->_lastError = $msg;

        if ($this->haltOnError != "no") {
//            return $this->haltMsg($msg);
            $this->haltMsg($msg);
        }

        if ($this->haltOnError == "yes") {
            die("<b>Halted.</b>");
        }

        return false;
    }
  
    /**
     * printf error message to show
     *
     * @access public
     * @param  string message to show
     * @return object PEAR error object
     */
    function haltMsg($msg)
    {
//        PEAR::raiseError(sprintf("<b>Template Error:</b> %s<br>\n", $msg));
        printf("<b>Template Error:</b> %s<br>\n", $msg);
    }
}
?>
