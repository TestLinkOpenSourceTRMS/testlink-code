{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: opt_transfer.inc.tpl,v 1.4 2006/12/24 11:48:18 franciscom Exp $
Purpose: manage the OptionTransfer.js created by Matt Kruse
         http://www.JavascriptToolbox.com/
         JavaScript Toolbox - Option Transfer - Move Select Box Options Back And Forth

Author: Francisco Mancardi
        Based on Cold Fusion code by Alessandro Lia (alessandro.lia@gruppotesi.com
      
        20061223 - franciscom - $title -> $option_transfer->additional_global_lbl  
        20060423 - franciscom - improved label management 
                                added double-click management
*}
  
  <table cellspacing="0" cellpadding="0" border="0" width="100%">
  		<caption style="font-weight:bold;">
  	  {$option_transfer->global_lbl}
    	&nbsp;{$option_transfer->additional_global_lbl|escape}
		  </caption>

    <tr>
      <td align="center">
         {$option_transfer->from->lbl}
         {html_options name=$option_transfer->from->name 
                       id=$option_transfer->from->name
                       size=$option_transfer->size 
                       style=$option_transfer->style 
                       multiple="yes"
                       ondblclick=$opt_cfg->js_events->left2right_click  
                       options=$option_transfer->from->map}
      </td>
      <td align="center" width="10%">
        <img src="#images["ico_all_r.gif"]#" 
              onclick="{$opt_cfg->js_events->all_right_click}"
              alt=">>" style="cursor: pointer;" /><br />
        <img src="#images["ico_l2r.gif"]#" 
              onclick="{$opt_cfg->js_events->left2right_click}"
              alt=">" style="cursor: pointer;" /><br />
        <img src="#images["ico_r2l.gif"]#" 
              onclick="{$opt_cfg->js_events->right2left_click}"
              alt="<" style="cursor: pointer;" /><br />
        <img src="#images["ico_all_l.gif"]#" 
              onclick="{$opt_cfg->js_events->all_left_click}"
              alt="<<" style="cursor: pointer;" />
      </td>
      <td align="center">
         {$option_transfer->to->lbl}
         {html_options name=$option_transfer->to->name 
                       id=$option_transfer->to->name
                       size=$option_transfer->size 
                       style=$option_transfer->style 
                       multiple="yes"
                       ondblclick=$opt_cfg->js_events->right2left_click  
                       options=$option_transfer->to->map}
      </td>
      
      
    </tr>
  </table>
  <input type="hidden" name="{$opt_cfg->js_ot_name}_removedLeft"  value="" />
  <input type="hidden" name="{$opt_cfg->js_ot_name}_removedRight"  value="" />
  <input type="hidden" name="{$opt_cfg->js_ot_name}_addedLeft"  value="" />
  <input type="hidden" name="{$opt_cfg->js_ot_name}_addedRight"  value="" />
  <input type="hidden" name="{$opt_cfg->js_ot_name}_newLeft"  value="" />
  <input type="hidden" name="{$opt_cfg->js_ot_name}_newRight"  value="" />