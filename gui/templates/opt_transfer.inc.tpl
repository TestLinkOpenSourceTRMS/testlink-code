{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesoruce opt_transfer.inc.tpl,v 1.10 2010/05/01 19:06:26 franciscom Exp $
Purpose: manage the OptionTransfer.js created by Matt Kruse
         http://www.JavascriptToolbox.com/
         JavaScript Toolbox - Option Transfer - Move Select Box Options Back And Forth

Author: Francisco Mancardi
        Based on Cold Fusion code by Alessandro Lia (alessandro.lia@gruppotesi.com
*}
  
   <div class="option_transfer_container">
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
      {if $option_transfer->labels->global_lbl != ''}
  		<caption style="font-weight:bold;">
  	  {$option_transfer->labels->global_lbl}
    	&nbsp;{$option_transfer->labels->additional_global_lbl|escape}
		  </caption>
		  {/if}

    <tr>
      <td align="center" width="50%">
         <div class="labelHolder">{$option_transfer->from->lbl}</div>
         {html_options name=$option_transfer->from->name 
                       id=$option_transfer->from->name
                       size=$option_transfer->size 
                       style=$option_transfer->style 
                       multiple="yes"
                       ondblclick=$option_transfer->jsEvents->left2right_click  
                       options=$option_transfer->from->map}
      </td>
      <td align="center" width="40">
        <img src="{$tlImages.allToRight}" 
              onclick="{$option_transfer->jsEvents->all_right_click}"
              alt=">>" style="cursor: pointer;" /><br />
        <img src="{$tlImages.leftToRight}" 
              onclick="{$option_transfer->jsEvents->left2right_click}"
              alt=">" style="cursor: pointer;" /><br />
        <img src="{$tlImages.rightToLeft}" 
              onclick="{$option_transfer->jsEvents->right2left_click}"
              alt="<" style="cursor: pointer;" /><br />
        <img src="{$tlImages.allToLeft}" 
              onclick="{$option_transfer->jsEvents->all_left_click}"
              alt="<<" style="cursor: pointer;" />
      </td>
      <td align="center" width="50%">
         <div class="labelHolder">{$option_transfer->to->lbl}</div>
         {html_options name=$option_transfer->to->name 
                       id=$option_transfer->to->name
                       size=$option_transfer->size 
                       style=$option_transfer->style 
                       multiple="yes"
                       ondblclick=$option_transfer->jsEvents->right2left_click  
                       options=$option_transfer->to->map}
      </td>
    </tr>
  </table>
  </div>
  <input type="hidden" id="{$option_transfer->htmlInputNames->removedLeft}" name="{$option_transfer->htmlInputNames->removedLeft}"  value="" />
  <input type="hidden" id="{$option_transfer->htmlInputNames->removedRight}" name="{$option_transfer->htmlInputNames->removedRight}"  value="" />
  <input type="hidden" id="{$option_transfer->htmlInputNames->addedLeft}" name="{$option_transfer->htmlInputNames->addedLeft}"  value="" />
  <input type="hidden" id="{$option_transfer->htmlInputNames->addedRight}" name="{$option_transfer->htmlInputNames->addedRight}"  value="" />
  <input type="hidden" id="{$option_transfer->htmlInputNames->newLeft}" name="{$option_transfer->htmlInputNames->newLeft}"  value="" />
  <input type="hidden" id="{$option_transfer->htmlInputNames->newRight}" name="{$option_transfer->htmlInputNames->newRight}"  value="" />