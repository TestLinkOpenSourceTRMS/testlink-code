{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: mainPage.tpl,v 1.34 2007/05/24 06:49:18 franciscom Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
 rev :                                                 
       20070523 - franciscom - nifty corners
       20070113 - franciscom - truncate on test plan name combo box
       20060908 - franciscom - removed assign risk and ownership
                               added define priority
                               added tc exec assignment
                                   
       20060819 - franciscom - changed css classes name
                               removed old comments
       
*}
{include file="inc_head.tpl" popup="yes" openHead="yes"}
<script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
{literal}
<script type="text/javascript">
window.onload=function(){
 Nifty("div.menu_bubble");
}
</script>
{/literal}

</head>

<body>
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{if $securityNotes}
    {include file="inc_msg_from_array.tpl" array_of_msg=$securityNotes arg_css_class="warning_message"}
{/if}

{* Right Column                  *}
{include file="mainPage_right.tpl"}

{*   left column                 *}
{include file="mainPage_left.tpl"}

{*      ** middle table ************}
{if $metricsEnabled == 'TRUE'}
    <div style="width: 45%; padding: 5px">
	    <table class="mainTable" style="width: 100%">
		<tr>
			<td colspan="3"><h2>{lang_get s='title_your_tp_metrics'}</h2></td>
		</tr>
		<tr>
			<th>{lang_get s='th_name'}</th>
			<th>{lang_get s='th_perc_completed'}</th>
			<th>{lang_get s='th_my_perc_completed'}</th>
		</tr>
	       {$myTPdata}
	    </table>
    </div>
{/if}

{*
<script type="text/javascript">
Rounded('vertical_menu', 8, 8);
</script>
*}

</body>
</html>