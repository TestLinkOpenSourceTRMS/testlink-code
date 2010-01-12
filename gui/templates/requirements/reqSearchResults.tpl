{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
Purpose: show results for requirement search.
*}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if $smarty.const.USE_EXT_JS_LIBRARY}
  {include file="inc_ext_js.tpl" css_only=1}
{/if}

</head>

{assign var=this_template_dir value=$smarty.template|dirname}
{lang_get var='labels' 
          s='no_records_found,other_versions,version'}

<body onLoad="viewElement(document.getElementById('other_versions'),false)">
<h1 class="title">{$gui->pageTitle}</h1>

<div class="workBack">
{if $gui->warning_msg == ''}
    {if $gui->resultSet}
        <table class="simple">
            {foreach from=$gui->resultSet item=req}
	            {assign var="id" value=$req.id}
	            <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">       
	            <td>
	        	      {foreach from=$gui->path_info[$id] item=path_part}
	        	          {$path_part|escape} /
	        	      {/foreach}
	        	  <a href="lib/requirements/reqView.php?item=requirement&requirement_id={$id}">
	        	  {$req.name|escape}</a>
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
