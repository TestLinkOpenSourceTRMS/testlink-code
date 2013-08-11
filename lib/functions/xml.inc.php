<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * support for XML
 * 
 * @package   TestLink
 * @copyright   2004-2013, TestLink community 
 * @filesource  xml.inc.php
 * @link    http://www.teamst.org/index.php
 *
 * @internal revisions
 *
 */

/**
 * 
 *
 */
function exportDataToXML($items,$rootTpl,$elemTpl,$elemInfo,$bNoXMLHeader = false)
{
  // echo __FUNCTION__; echo 'items <br>'; new dBug($items);
  if (!$items)
  {
    return;
  }

  $xmlCode = '';
  reset($items);
  while($item = each($items))
  {
    $item = $item[1];
    $xmlElemCode = $elemTpl;
    
    // REMEMBER YOU NEED TO USE XMP TO DEBUG
    // echo '$xmlElemCode'; echo "<xmp>$xmlElemCode)</xmp>";
    // echo '$elemInfo'; new dBug($elemInfo);
    
    foreach($elemInfo as $subject => $replacement)
    {
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
      
      // echo 'what to search => $subject:' . $subject . '<br>';
      // echo 'replace with:' . $content . '<br>';
      // echo '$replacement key:' . $replacement . '<br>';
      // echo "BEFORE Replace: <xmp>$xmlElemCode</xmp>";

      $howMany = 0;
      $xmlElemCode = str_replace($subject,$content,$xmlElemCode,$howMany);
      // echo "How many?: $howMany<br>";
      // echo "After Replace: <xmp>$xmlElemCode</xmp>";
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
  // new dBug(__FUNCTION__);
  // new dBug($simpleXMLItems);
  // new dBug($itemStructure);

  $items = null;
  if($simpleXMLItems)
  {
      $items_counter=0;
      $loop_qty = count($simpleXMLItems);

        // new dBug($loop_qty);
      for($idx=0; $idx < $loop_qty; $idx++)
      {
        // echo "DEBUG - " . __FUNCTION__ . " \$idx:$idx<br>";
      foreach($itemStructure['elements'] as $castType => $keyValues)
        {
          // new dBug($castType); new dBug($keyValues); 
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
?>