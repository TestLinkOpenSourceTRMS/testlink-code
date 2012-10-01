<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manage Option Transfer (double select box)
 *
 * @filesource tlOptionTransfer.class.php
 * @author franciscom
 * @since 2.0
 *
**/
class tlOptionTransfer
{
  public  $jsName = null;
  public  $jsEvents = null;
  public  $labels = null;
  public  $from = null;
  public  $to = null;
  public  $size = null;
  public  $style = null;
  private $htmlInputNames = null;
  
  public function __construct($jsName = 'ot') 
	{
	  $this->labels = new stdClass();
    $this->jsEvents = new stdClass();
    $this->to = new stdClass();
    $this->from = new stdClass();

	  $this->size = 8;
	  $this->style = "width: 300px;";
    $this->jsName = $jsName;

	  $this->labels->global_lbl = '';
	  $this->labels->additional_global_lbl = '';

    $this->htmlInputNames = new stdClass();
    $this->htmlInputNames->fromPanel = '';
    $this->htmlInputNames->toPanel = '';

    // IMPORTANT NOTICE
    // these names can not be changed because are used on JS logic.
    $suffix2add = array('removed','added','new');
    $side2go = array('Left','Right');
    foreach($side2go as $side)
    {
      foreach($suffix2add as $suffix)
      {
        $prop = $suffix . $side;
        $this->htmlInputNames->$prop = $this->jsName . '_' .$prop;
      }
    }

    $this->initJSEvents();
    $this->initFromPanel();
    $this->initToPanel();
    
    return $this;
  }

  public function initJSEvents() 
	{
	  $name = $this->jsName;
	  $this->jsEvents->all_right_click = "window.setTimeout('$name.transferAllRight()',20);";
	  $this->jsEvents->left2right_click = "window.setTimeout('$name.transferRight()',20);";
	  $this->jsEvents->right2left_click = "window.setTimeout('$name.transferLeft()',20);";
	  $this->jsEvents->all_left_click = "window.setTimeout('$name.transferAllLeft()',20);";
  }
  
  public function clearJSEvents() 
	{
    $key2set = property_get($this->cfg->js_events);
    foreach($this->cfg->js_events as $prop)
    {
      $this->jsEvents->$prop = '';
    } 
  }
  
  public function setSize($sizeInItems) 
	{
    $this->size = $sizeInItems;
  }
  
  public function setStyle($style) 
	{
    $this->style = $style;
  }
  
  public function getHtmlInputNames() 
	{
    return $this->htmlInputNames;
  }

  public function setNewRightInputName($name) 
	{
    $this->htmlInputNames->newRight = $name;
  }

  public function getNewRightInputName() 
	{
    return $this->htmlInputNames->newRight;
  }


  public function updatePanelsContent($rightPanelItemSet) 
	{
	  $rightPanel = array();
	  $leftPanel = array();
    
	  if(trim($rightPanelItemSet) == "")
	  {
	  	if(!is_null($this->to->map))
	  	{
	  		$rightPanel = $this->to->map;
	  	}
	  } 
	  else
	  {
	  	$keySet = explode(",",trim($rightPanelItemSet));
	  	foreach($keySet as $key => $code)
	  	{
	  		$rightPanel[$code] = $this->from->map[$code];
	  	}
	  }
   
	  if(!is_null($this->from->map))
	  {
	  	$leftPanel = array_diff_assoc($this->from->map,$rightPanel);
	  }
    
	  $this->from->map = $leftPanel;
	  $this->to->map = $rightPanel;
  }
  
  
  public function initFromPanel($name = 'from_select_box',$label = 'from') 
	{
	  $this->htmlInputNames->fromPanel = is_null($name) ? 'from_select_box' : $name;
    $this->from = $this->initPanel($this->htmlInputNames->fromPanel,$label) ;
  }
  
  public function initToPanel($name = 'to_select_box',$label = 'to') 
	{
	  $this->htmlInputNames->toPanel = is_null($name) ? 'to_select_box' : $name;
    $this->to = $this->initPanel($this->htmlInputNames->toPanel,$label) ;
  }
  
  public function setToPanelFieldCfg($fieldCfg)
  {
    $this->to->id_field = $fieldCfg['id'];
    $this->to->desc_field = $fieldCfg['description'];
  }
  
  public function setFromPanelFieldCfg($fieldCfg)
  {
    $this->from->id_field = $fieldCfg['id'];
    $this->from->desc_field = $fieldCfg['description'];
  }


  public function setToPanelContent($itemSet)
  {
    $this->to->map = $itemSet;
  }
  
  public function setFromPanelContent($itemSet)
  {
    $this->from->map = $itemSet;
  }
  
  
  public function initPanel($name,$label) 
	{
	  $panel = new stdClass();
	  $panel->lbl = $label;
	  $panel->name = $name;
	  $panel->map = array();
	
	  $panel->id_field = '';
	  $panel->desc_field = '';
	  $panel->desc_glue = " ";
	  $panel->desc_html_content = true;
	  $panel->required = false;
	  $panel->show_id_in_desc = true;
	  $panel->js_events = new stdClass;
	  $panel->js_events->ondblclick = "";

    return $panel;
  }
 
}  // class end