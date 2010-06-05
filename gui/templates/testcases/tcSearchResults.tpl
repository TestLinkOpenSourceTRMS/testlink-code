{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcSearchResults.tpl,v 1.2 2010/06/05 07:30:17 franciscom Exp $
Purpose: smarty template - view test case in test specification
rev: 20080322 - franciscom - php errors clean up
*}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
{include file="inc_ext_js.tpl" css_only=1}

</head>

{assign var=this_template_dir value=$smarty.template|dirname}
{lang_get var='labels' 
          s='no_records_found,other_versions,version,title_test_case'}

<body onLoad="viewElement(document.getElementById('other_versions'),false)">
<h1 class="title">{$gui->pageTitle}</h1>

<div class="workBack">
{if $gui->warning_msg == ''}
    {if $gui->resultSet}
        <table class="simple">
        {foreach from=$gui->resultSet item=tcase}
            {assign var="tcase_id" value=$tcase.testcase_id}
            {assign var="tcversion_id" value=$tcase.tcversion_id}
           <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">       
            <td>
        	      {foreach from=$gui->path_info[$tcase_id] item=path_part}
        	          {$path_part|escape} /
        	      {/foreach}
        	  <a href="lib/testcases/archiveData.php?edit=testcase&id={$tcase_id}">
        	  {$gui->tcasePrefix}{$tcase.tc_external_id|escape}:{$tcase.name|escape}</a>
            </td>
        	  </tr>
        {/foreach}
        </table>
    {else}
        	{$labels.no_records_found}
    {/if}
{else}
    {$gui->warning_msg}
{/if}   
</div>
</body>
</html>
