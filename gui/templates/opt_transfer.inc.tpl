{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: opt_transfer.inc.tpl,v 1.1 2006/04/10 09:07:13 franciscom Exp $
Purpose: manage the OptionTransfer.js created by Matt Kruse
         http://www.JavascriptToolbox.com/
         JavaScript Toolbox - Option Transfer - Move Select Box Options Back And Forth

Author: Francisco Mancardi
        Based on Cold Fusion code by Alessandro Lia (alessandro.lia@gruppotesi.com
*}
  
  <table cellspacing="0" cellpadding="0" border="0" width="100%">
  		<caption>
  	  {lang_get s='title_assign_kw_to_tc'}
    	&nbsp;{$title|escape}
		  </caption>

    <tr>
      <td align="center">
         {html_options name=$option_transfer->from->name 
                       id=$option_transfer->from->name
                       size=$option_transfer->size 
                       style=$option_transfer->style 
                       multiple="yes"
                       ondblclick="dd"  
                       options=$option_transfer->from->map}
      </td>
      <td align="center" width="10%">
        <img  src="#images["ico_all_r.gif"]#" 
              onclick="{$opt_cfg->js_events->all_right_click}"
              alt=">>" style="cursor: pointer;"><br>
        <img  src="#images["ico_l2r.gif"]#" 
              onclick="{$opt_cfg->js_events->left2right_click}"
              alt=">" style="cursor: pointer;"><br>
        <img  src="#images["ico_r2l.gif"]#" 
              onclick="{$opt_cfg->js_events->right2left_click}"
              alt="<" style="cursor: pointer;"><br>
        <img  src="#images["ico_all_l.gif"]#" 
              onclick="{$opt_cfg->js_events->all_left_click}"
              alt="<<" style="cursor: pointer;">
      </td>
      <td align="center">
         {html_options name=$option_transfer->to->name 
                       id=$option_transfer->to->name
                       size=$option_transfer->size 
                       style=$option_transfer->style 
                       multiple="yes"
                       ondblclick="dd"  
                       options=$option_transfer->to->map}
      </td>
      
      
    </tr>
  </table>
  <input type="hidden" name="{$opt_cfg->js_ot_name}_removedLeft"  value="">
  <input type="hidden" name="{$opt_cfg->js_ot_name}_removedRight"  value="">
  <input type="hidden" name="{$opt_cfg->js_ot_name}_addedLeft"  value="">
  <input type="hidden" name="{$opt_cfg->js_ot_name}_addedRight"  value="">
  <input type="hidden" name="{$opt_cfg->js_ot_name}_newLeft"  value="">
  <input type="hidden" name="{$opt_cfg->js_ot_name}_newRight"  value="">