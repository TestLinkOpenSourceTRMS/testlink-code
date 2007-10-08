{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_del_onclick.tpl,v 1.1 2007/10/08 07:25:42 franciscom Exp $
Purpose: include files for:


rev :
*}
{if $smarty.const.USE_EXT_JS_LIBRARY}
  {include file="inc_ext_js.tpl"}
  {lang_get s='Yes' var="yes_b"}
  {lang_get s='No' var="no_b"}
  {assign var="body_onload" 
          value="onload=\"init_yes_no_buttons('$yes_b','$no_b');\""}

  <script type="text/javascript">
   {literal}
  function delete_confirmation(o_id,o_name,msg)
  {
    Ext.Msg.confirm(o_label + ' ' + o_name , msg,
  			            function(btn, text)
  			            { 
  					         do_action(btn,text,o_id);
  			            });
  }
 
  function init_yes_no_buttons(yes_btn,no_btn)
  {
    Ext.MessageBox.buttonText.yes=yes_btn;
    Ext.MessageBox.buttonText.no=no_btn;
  }
  
  function do_action(btn, text, o_id)
  { 
  	var my_action='';
    
    if( btn == 'yes' )
    {
      my_action=del_action+o_id;
  	  window.location=my_action;
  	}
  }					           
  {/literal}
  </script>
{else}
  {assign var="body_onload" value=''}

  <script type="text/javascript">
  {literal}
  function delete_confirmation(o_id,o_name,msg) 
  {
  	if (confirm(msg + o_name))
  	{
  		window.location = del_action+o_id;
  	}
  }
  {/literal}
 </script>
{/if}
