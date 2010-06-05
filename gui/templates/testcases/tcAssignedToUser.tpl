{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcAssignedToUser.tpl,v 1.3 2010/06/05 07:30:17 franciscom Exp $
Purpose: smarty template - view test case in test specification
rev: 
20100326 - amitkhullar - BUGID 3345
20080322 - franciscom - php errors clean up
*}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{include file="inc_ext_js.tpl" css_only=1}

</head>

{assign var=this_template_dir value=$smarty.template|dirname}
{lang_get var='labels' 
          s='no_records_found,testplan,testcase,version,assigned_on,due_since'}

<body>
<h1 class="title">{$gui->pageTitle}</h1>
<div class="workBack">
{if $gui->warning_msg == ''}
    {if $gui->resultSet}
        {foreach from=$gui->resultSet key=tplan_id item=tcaseSet}
           <h1 align="left">{$labels.testplan}:&nbsp;{$gui->tplanNames[$tplan_id].name|escape}</h1>
            <table class="simple">
            <th align="left">{$labels.testcase}</th>
            <th>{$labels.assigned_on}</th>
            <th>{$labels.due_since}</th>
            {foreach from=$tcaseSet item=tcase}
                {assign var="tcase_id" value=$tcase.testcase_id}
                {assign var="tcversion_id" value=$tcase.tcversion_id}
               <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">       
                <td>
            	  <a href="lib/testcases/archiveData.php?edit=testcase&id={$tcase_id}">
            	  {$tcase.tcase_full_path|escape}{$tcase.prefix|escape}
            	  {$gui->glueChar}{$tcase.tc_external_id|escape}:{$tcase.name|escape}&nbsp({$labels.version}:{$tcase.version})</a>
                </td>
                <td >
            	  {localize_timestamp ts=$tcase.creation_ts}
                </td>
                <td>
                 {date_diff date1=$tcase.creation_ts date2=$smarty.now interval="days"}
                </td>
            	  </tr>
            {/foreach}
            </table>
            <br>
        {/foreach}
    {else}
        	{$labels.no_records_found}
    {/if}
{else}
    {$gui->warning_msg}
{/if}   
</div>
</body>
</html>
