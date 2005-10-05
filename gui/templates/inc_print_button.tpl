{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_print_button.tpl,v 1.1 2005/10/05 06:14:26 franciscom Exp $
Purpose: print button
*}

{literal}
<style type="text/css" media="print">
	#print_button {
	  display: none;
	}
  #submit_tc_results {
	  display: none;
	}
</style>
{/literal}
<div id="print_button" align="center">
<input type="button" name="print" value="{lang_get s='btn_print'}" 
		onclick="javascript:window.print();" />
</div>
