{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 @filesource  mainPage.tpl 
 Purpose: smarty template - main page / site map                 
*}
{$cfg_section=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" popup="yes" openHead="yes"}

{include file="inc_ext_js.tpl"}
<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
<script type="text/javascript">
window.onload=function()
{
  // Nifty("div.menu_bubble");
  if( typeof display_left_block_1 != 'undefined')
  {
    display_left_block_1();
  }

  if( typeof display_left_block_2 != 'undefined')
  {
    display_left_block_2();
  }

  if( typeof display_left_block_3 != 'undefined')
  {
    display_left_block_3();
  }
    
  if( typeof display_left_block_4 != 'undefined')
  {
    display_left_block_4();
  }

  display_left_block_5();

  if( typeof display_right_block_1 != 'undefined')
  {
    display_right_block_1();
  }

  if( typeof display_right_block_2 != 'undefined')
  {
    display_right_block_2();
  }

  if( typeof display_right_block_3 != 'undefined')
  {
    display_right_block_3();
  }
   
}
</script>
</head>

<body>
{if $gui->securityNotes}
  {include file="inc_msg_from_array.tpl" array_of_msg=$gui->securityNotes arg_css_class="warning"}
{/if}

{* ----- Right Column ------------- *}
{include file="mainPageRight.tpl"}

{* ----- Left Column -------------- *}
{include file="mainPageLeft.tpl"}
</body>
</html>