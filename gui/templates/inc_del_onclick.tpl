{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_del_onclick.tpl,v 1.3 2007/11/21 13:10:16 franciscom Exp $
Purpose: include files for:


rev :
     20071008 - franciscom - added prototype.js method escapeHTML()
*}
{if $smarty.const.USE_EXT_JS_LIBRARY}
  {include file="inc_ext_js.tpl"}
  {lang_get s='Yes' var="yes_b"}
  {lang_get s='No' var="no_b"}
  {assign var="body_onload" 
          value="onload=\"init_yes_no_buttons('$yes_b','$no_b');\""}

  <script type="text/javascript">
   {literal}
  /*
    function: 

    args:
    
    returns: 

  */
  function delete_confirmation(o_id,o_name,msg)
  {
    Ext.Msg.confirm(o_label + ' ' + o_name.escapeHTML() , msg,
  			            function(btn, text)
  			            { 
  					         do_action(btn,text,o_id);
  			            });
  }
 
  /*
    function: 

    args:
    
    returns: 

  */
  function init_yes_no_buttons(yes_btn,no_btn)
  {
    Ext.MessageBox.buttonText.yes=yes_btn;
    Ext.MessageBox.buttonText.no=no_btn;
  }
  
  /*
    function: 

    args:
    
    returns: 

  */
  function do_action(btn, text, o_id)
  { 
  	var my_action='';
    
    if( btn == 'yes' )
    {
      my_action=del_action+o_id;
  	  window.location=my_action;
  	}
  }					
  
  /*
    function: 

    args:
    
    returns: 

  */
  function alert_message(title,msg)
  {
    Ext.MessageBox.alert(title.escapeHTML(), msg.escapeHTML());
  }
  {/literal}
  </script>
{else}
  {assign var="body_onload" value=''}

  <script type="text/javascript">
  {literal}
  /*
    function: 

    args:
    
    returns: 

  */
  function delete_confirmation(o_id,o_name,msg) 
  {
  	if (confirm(msg + o_name))
  	{
  		window.location = del_action+o_id;
  	}
  }


  /*
    function: 

    args:
    
    returns: 

  */
  function alert_message(title,msg)
  {
    alert(msg);
  }
  {/literal}
 </script>
{/if}
