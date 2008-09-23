<?php
# XMLFile -- Initial version June 1, 2000 
# May 29, 2002
#  Fixed to work with later versions of PHP that have deprecated the call-time
#  pass by reference syntax.  This may break versions of PHP older than 3, so
#  if you are using this on those versions, go with an earlier version.
#  Also added some more comments on how to use it to create XML files.  Some
#  people (a surprising number of them, in fact) were having difficulty
#  understanding the recursive nature of XML files, and that each tag has
#  subtags, each of which can have subtags, each of which....
# July 12, 2001
#  Fixed an oops in the find_subtags_by_name function.
# July 11, 2001
#  Incorporated Kevin Howe's read_xml_string function.
#  Incorporated Mike Konkov's find_subtags_by_name function (with some changes).
#  Fixed an errant documentation comment (open instead of fopen -- oops).
#
# September 29, 2000
# by Chris Monson -- e408345b17be3ce90059d01d96be0599@orangatango.com This PHP module is licensed under the GNU LGPL (www.gnu.org)
# Please become familiar with this license before sending this thing all over
# the place!  I would like to have any changes submitted back to me, and this
# comment must be included with any distribution of this component.
#
# The purpose of this module was to provide a simple interface for dealing with
# small to medium sized XML files.  It is probably not suitable for very large
# files since it reads the entire file into a structure in memory.  For very
# large files, the XML parsing functions should be used more or less directly
# so that pieces of the file can be dealt with as they are read in.
#
# The basic idea is this: Read the XML file into an internal tree structure
# that is easy to traverse.  This module also allows you to create such a
# structure in memory and then write it out to disk.  The output is formatted
# in a nice, readable way, using whitespace to delimit tag containment, etc.
# It makes it very easy to write nice-looking XML files.
#
# I have included some usage comments.  They are almost certainly incomplete.
# If you get stumped, first use the source, then email me.
#
# July 22, 2004
# by Olavo Alexandrino <oalexandrino@yahoo.com.br>
# Small adaptation so that it is configured the version and codification of the file xml
# Usage:
# $xmlFile = new XMLFile($version, $encoding);
#------------------------------------------------------------------------------
### USAGE ###
#------------------------------------------------------------------------------
# Reading an XML file:
#------------------------------------------------------------------------------
#
# $xml = new XMLFile();
# $fh = fopen( 'myxmlfile.xml', 'r' );
# $xml->read_file_handle( $fh );
# close( $fh );
#
# Now the tags can be accessed via the root node of $xml:
#
# $root = &$xml->roottag;
# $tagname = $root->name;
# $tagdata = $root->cdata;
#
# Note that a tag is of the following form:
# <NAME attribute=value>CDATA</NAME>
# Each tag contains an attributes array, which is associative by nature.  In
# other words, you would access the value of "attribute" as follows:
#
# $value = $root->attributes['attribute'];
#
# Also, each tag has a 'tags' proprerty, which is an ordered array (integer
# indices, not associative!) which has the tags that were contained within
# this tag in their order of appearance.  The reason that this is not
# associative is that there can be multiple tags of the same name.  There is
# nothing in the XML spec (barring a DTD) that declares the uniqueness of tag
# names.  For example:
#
# <OUTER>
#     <INNER>CDATA</INNER>
#     <INNER name="hello"/>
# </OUTER>
#
# In the above example, the outer tag would have a tags array that has two
# entries, each of which has a tag name of "INNER".  The one with CDATA wrapped
# inside would be at index 0, and the other would be at index 1.
#
# Once you have finished with the XMLFile object, you need to call the cleanup
# method.  If you don't, you will get a memory leak, since PHP is reference
# counted and each element in the tree refers to its parent.  'cleanup' simply
# traverses the tree and disconnects the parent pointers.  The PHP cleans up
# everything else.
#
# $xml->cleanup();
# 
# Note that you can change the elements, delete tags, and do other things
# to the tree once it has been read from the file.  After it has been changed,
# you can write it back out and the file will reflect your changes.
#------------------------------------------------------------------------------
# Writing a new file:
# 
# $xml = new XMLFile();
# $xml->create_root(); # necessary -- no root is created until requested
# $xml->roottag->name = 'ROOT';
# $xml->roottag->add_subtag( 'INNER', array() );
# $innertag = &$xml->roottag->curtag;
# $innertag->add_subtag( 'REALLYINNER', array() );
# # Or, you can do this:
# $xml->roottag->curtag->add_subtag( 'INNER2', array() );
# # The point is that each tag can have subtags.  The most recently added
# # subtag is always the curtag of its parent.
# $xml->roottag->add_subtag( 'INNER', array( 'name' => 'value' ) );
# $xml->roottag->curtag->cdata = "Hello!"; # curtag is the most recent addition
# $fh = fopen( 'myxmlfile.xml', 'w' );
# $xml->write_file_handle( $fh );
# close( $fh );
#
# The file will look like this: (no space between ? and >)
#
# <?xml version="1.0" encoding="UTF-8" ? >
# <ROOT>
#     <INNER>
#           <REALLYINNER/>
#           <INNER2/>
#     </INNER>
#     <INNER name="value">Hello!</INNER>
# </ROOT>
#
#------------------------------------------------------------------------------
#------------------------------------------------------------------------------
#
if (!isset($XMLFile_Included) || !$XMLFile_Included) {
$XMLFile_Included = 1;

###############################################################################
class XMLTag
{
    var $cdata;
    var $attributes;
    var $name;
    var $tags;
    var $parent;
    var $curtag;

    function XMLTag(&$parent)
    {
        if (is_object( $parent ))
        {
            $this->parent = &$parent;
        }
        $this->_init();
    }

	/**
	* @description 		It initiates the attributes of the XML
	* @author     		Olavo Alexandrino <oalexandrino@yahoo.com.br>
	* @copyright  		oalexandrino.com
	* @access			private
	* @since			july / 2004
	*/
    function _init()
    {
        $this->attributes = array();
        $this->cdata = '';
        $this->name = '';
        $this->tags = array();
    }

    function add_subtag($name, $attributes=0)
    {
        $tag = new XMLTag( $this );
        $tag->set_name( $name );
        if (is_array($attributes)) {
            $tag->set_attributes( $attributes );
        }
        $this->tags[] = &$tag;
        $this->curtag = &$tag;
    }

    function find_subtags_by_name( $name )
    {
        $result = array();
        $found=false;
        for($i=0;$i<$this->num_subtags();$i++) {
            if(strtoupper($this->tags[$i]->name)==strtoupper($name)) {
                $found=true;
                $array2return[]=&$this->tags[$i];
            }
        }
        if($found) {
            return $array2return;
        }
        else {
            return false;
        }
    }

    function clear_subtags()
    {
        # Traverse the structure, removing the parent pointers
        $numtags = sizeof($this->tags);
        $keys = array_keys( $this->tags );
        foreach( $keys as $k ) {
            $this->tags[$k]->clear_subtags();
            unset($this->tags[$k]->parent);
        }

        # Clear the tags array
        $this->tags = array();
        unset( $this->curtag );
    }

    function remove_subtag($index)
    {
        if (is_object($this->tags[$index])) {
            unset($this->tags[$index]->parent);
            unset($this->tags[$index]);
        }
    }

    function num_subtags()
    {
        return sizeof( $this->tags );
    }

    function add_attribute( $name, $val )
    {
        //$this->attributes[strtoupper($name)] = $val;
        $this->attributes[$name] = $val;		
    }

    function clear_attributes()
    {
        $this->attributes = array();
    }

    function set_name( $name )
    {
        //$this->name = strtoupper($name);
        $this->name = $name;		
    }

    function set_attributes( $attributes )
    {
        $this->attributes = (is_array($attributes)) ? $attributes : array();
    }

    function add_cdata( $data )
    {
        $this->cdata .= $data;
    }

    function clear_cdata()
    {
        $this->cdata = "";
    }

    function write_file_handle( $fh, $prepend_str='' )
    {
       
        # Get the attribute string
        $attrs = array();
        $attr_str = '';
        foreach( $this->attributes as $key => $val )
        {
            $attrs[] = strtoupper($key) . "=\"$val\"";
        }
        if ($attrs) {
            $attr_str = join( " ", $attrs );
        }
        # Write out the start element
        $tagstr = "$prepend_str<{$this->name}";
        if ($attr_str) {
            $tagstr .= " $attr_str";
        }

        $keys = array_keys( $this->tags );
        $numtags = sizeof( $keys );
        #
        # If there are subtags and no data (only whitespace), 
        # then go ahead and add a carriage
        # return.  Otherwise the tag should be of this form:
        # <tag>val</tag>
        # If there are no subtags and no data, then the tag should be
        # closed: <tag attrib="val"/>
        #
        # 20080921 - francisco.mancardi@gmail.com
        # $trimmeddata = "![CDATA[" . trim( $this->cdata ) . "]]";
        $trimmeddata = trim( $this->cdata );
        
        if ($numtags && ($trimmeddata == "")) {
            $tagstr .= ">\n";
        }
        elseif (!$numtags && ($trimmeddata == "")) {
            $tagstr .= "/>\n";
        }
        else {
            $tagstr .= ">";
        }

        fwrite( $fh, $tagstr );

        # Write out the data if it is not purely whitespace
        if ($trimmeddata != "") {
            fwrite( $fh, $trimmeddata );
        }

        # Write out each subtag
        foreach( $keys as $k ) {
            $this->tags[$k]->write_file_handle( $fh, "$prepend_str\t" );
        }

        # Write out the end element if necessary
        if ($numtags || ($trimmeddata != "")) {
            $tagstr = "</{$this->name}>\n";
            if ($numtags) {
                $tagstr = "$prepend_str$tagstr";
            }
            fwrite( $fh, $tagstr );
        }
    }

}
###############################################################################
class XMLFile
{
    var $parser;
    var $roottag;
    var $curtag;
	var $encoding;
	var $version;	

	/**
	* @description 		Constructor
	* @author     		Olavo Alexandrino <oalexandrino@yahoo.com.br>
	* @copyright  		oalexandrino.com
	* @access			public
	* @since			july / 2004
	*/
    function XMLFile($version = "1.0", $encoding = "UTF-8")
    {
		    $this->version  = $version;
		    $this->encoding = $encoding;	
        $this->init();
    }

    # Until there is a suitable destructor mechanism, this needs to be
    # called when the file is no longer needed.  This calls the clear_subtags
    # method of the root node, which eliminates all circular references
    # in the xml tree.
    function cleanup()
    {
        if (is_object( $this->roottag )) {
            $this->roottag->clear_subtags();
        }
    }

    function init()
    {
        $this->roottag  = "";
        $this->curtag   = &$this->roottag;
    }

    function create_root()
    {
        $null = 0;
        $this->roottag = new XMLTag($null);
        $this->curtag = &$this->roottag;
    }

    # read_xml_string
    # Same as read_file_handle, but you pass it a string.  Note that
    # depending on the size of the XML, this could be rather memory intensive.
    # Contributed July 06, 2001 by Kevin Howe
    function read_xml_string( $str )
    {
        $this->init();
        $this->parser = xml_parser_create($this->encoding);
        xml_set_object( $this->parser, $this );
        xml_set_element_handler( $this->parser, "_tag_open", "_tag_close" );
        xml_set_character_data_handler( $this->parser, "_cdata" );
        xml_parse( $this->parser, $str );
        xml_parser_free( $this->parser );
    }

    function read_file_handle( $fh )
    {
        $this->init();
        $this->parser = xml_parser_create($this->encoding);
        xml_set_object( $this->parser, $this );
        xml_set_element_handler( $this->parser, "_tag_open", "_tag_close" );
        xml_set_character_data_handler( $this->parser, "_cdata" );

        while( $data = fread( $fh, 4096 )) {
            if (!xml_parse( $this->parser, $data, feof( $fh ) )) {
                die(sprintf("XML error: %s at line %d",
                    xml_error_string(xml_get_error_code($this->parser)),
                    xml_get_current_line_number($this->parser)));
            }
        }

        xml_parser_free( $this->parser );
    }

    function write_file_handle( $fh, $write_header=1 )
    {
        if ($write_header) {
            fwrite( $fh, "<?xml version='". $this->version . "' encoding='". $this->encoding . "'?>\n" );
        }

        # Start at the root and write out all of the tags
        $this->roottag->write_file_handle( $fh );
    }

    ###### UTIL #######

    function _tag_open( $parser, $tag, $attributes )
    {
        #print "tag_open: $parser, $tag, $attributes\n";
        # If the current tag is not set, then we are at the root
        if (!is_object($this->curtag)) {
            $null = 0;
            $this->curtag = new XMLTag($null);
            $this->curtag->set_name( $tag );
            $this->curtag->set_attributes( $attributes );
        }
        else { # otherwise, add it to the tag list and move curtag
            $this->curtag->add_subtag( $tag, $attributes );
            $this->curtag = &$this->curtag->curtag;
        }
    }

    function _tag_close( $parser, $tag )
    {
        # Move the current pointer up a level
        $this->curtag = &$this->curtag->parent;
    }

    function _cdata( $parser, $data )
    {
        $this->curtag->add_cdata( $data );
    }
}
###############################################################################
} // included
###############################################################################
