<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * support for XML
 * 
 * @package   TestLink
 * @copyright   2004-2021, TestLink community 
 * @filesource  xml.inc.php
 * @link    http://www.testlink.org
 *
 */


/**
 * Use this sample file to test attack (user contribution)
 * <?xml version='1.0' encoding='UTF-8'?><!DOCTYPE foo [<!ENTITY xxe SYSTEM "file:///etc/passwd"> ]>
 * <platforms>
 *  <platform>
 *   <name>&xxe;</name>
 *  </platform>
 * </platforms>
 *
 * User contribution regarding XML External Entity (XXE) Processing Attacks
 *
 */
function simplexml_load_file_wrapper($filename)
{
  // http://websec.io/2012/08/27/Preventing-XXE-in-PHP.html
  libxml_disable_entity_loader(true);  
  $zebra = file_get_contents($filename);
  $xml = @simplexml_load_string($zebra);
  if ($xml === false) {
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($zebra);
    echo lang_get("simplexml_load_file_wrapper_error");
    foreach (libxml_get_errors() as $error) {
      echo "<br>", $error->message;
    }
    die();
  }
  return $xml;
}


/**
 * 
 *
 */
function exportDataToXML($items,$rootTpl,$elemTpl,$elemInfo,$bNoXMLHeader = false)
{
  if (!$items)
  {
    return;
  }


  $xmlCode = '';
  reset($items);
  foreach ($items as $item) {

    $xmlElemCode = $elemTpl;
    
    // REMEMBER YOU NEED TO USE XMP TO DEBUG
    // echo '$xmlElemCode'; echo "<xmp>$xmlElemCode)</xmp>";
    foreach($elemInfo as $subject => $replacement) {
      $fm = substr($subject,0,2);
      $content = isset($item[$replacement]) ? $item[$replacement] : null;
      
      switch($fm)
      {
        case '||':
        break;

        case '{{':
        default:
          $content = htmlspecialchars($content);
        break;
      }
      
      $howMany = 0;
      $xmlElemCode = str_replace($subject,$content,$xmlElemCode,$howMany);
    }
  

    $xmlCode .= $xmlElemCode;
  }
  reset($items);
  
  $result = null;
  if (!$bNoXMLHeader)
  {
    $result .= TL_XMLEXPORT_HEADER."\n";
  }
  
  if($rootTpl != '' && !is_null($rootTpl))
  {  
    $result .= str_replace("{{XMLCODE}}",$xmlCode,$rootTpl);
    return $result;
  } 
  else
  {
    return $xmlCode;
  } 
}


/**
 * $simpleXMLItems
 * $itemStructure: keys elements, attributes
 *
 *           both keys are maps:
 *           key: element/attribute type
 *           value: map 
 *              key: attribute name 
 *              value: options used to request special
 *                   processing like trim(), intval(),etc.
 *                
 *           Example:
 *           $tcXML['elements'] = array('string' => array("summary" => null,
 *                                "preconditions" => 'trim'),
 *                        'integer' => array("node_order" => 'intval',
 *                                 "externalid" => null,
 *                                                     "execution_type" => null,
 *                                                     "importance" => null));
 *
 *           $tcXML['attributes'] = array('string' => array("name" => 'trim'), 
 *                                              'integer' => array('internalid' => null));
 *  
 *                            
 */
function getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure)
{
  $items = null;
  if($simpleXMLItems)
  {
    $items_counter=0;
    $loop_qty = count($simpleXMLItems);

    // new dBug($loop_qty);
    for($idx=0; $idx < $loop_qty; $idx++)
    {
      foreach($itemStructure['elements'] as $castType => $keyValues)
      {
        foreach($keyValues as $key => $fn2apply)
        {
          $dummy[$key] = null;
          if( property_exists($simpleXMLItems[$idx],$key) )
          {
            $dummy[$key] = $simpleXMLItems[$idx]->$key;
            settype($dummy[$key],$castType);
            if(!is_null($fn2apply))
            {
              $dummy[$key] = $fn2apply($dummy[$key]);
            }   
          }
        }
      } 

      if( isset($itemStructure['attributes']) && !is_null($itemStructure['attributes']) )
      {
        foreach($itemStructure['attributes'] as $castType => $keyValues)
          {
          foreach($keyValues as $key => $fn2apply)
            {
              $dummy[$key] = null;
              if( isset($simpleXMLItems[$idx],$key) )
              {
                $dummy[$key] = $simpleXMLItems[$idx][$key];
                  settype($dummy[$key],$castType);
                  if(!is_null($fn2apply))
                  {
                    $dummy[$key] = $fn2apply($dummy[$key]);
                  }   
                }
            }
          } 
      }
      $items[$items_counter++] = $dummy;
      }
    }

  return $items;
}