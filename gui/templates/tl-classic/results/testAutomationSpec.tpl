{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 


@filesource testAutomationSpec.tpl
*}

{lang_get var="labels" 
  s="th_id,platform,no_testcase_available,th_test_case,version"}

{include file="inc_head.tpl" openHead="yes"}
</head>

<body>
<form id='testAutomationSpec' name='testAutomationSpec' method='post'>
	<h1 class="title">{$gui->main_descr|escape}</h1>

  <p>&nbsp;<p>&nbsp;<p>
  {if $gui->has_tc}
    <div class="workBack">
	    {$table_counter=0}
      {* Loop over TEST SUITES *}
	    {foreach from=$gui->items item=ts key=idx name="div_drawing"}
  	    {$ts_id=$ts.testsuite.id}
  	    {$div_id="div_$ts_id"}
  	    {if $ts_id != ''}
	        <div id="{$div_id}" style="margin-left:0px; border:1;">
            <br />
            {if $ts.testcase_qty gt 0}
	            {$table_counter=$table_counter+1}
              <table cellspacing="0" style="font-size:small;" width="100%"
                id="the-table-{$table_counter}" class="tableruler">
                <caption 
                  style="text-align:left;font-size:x-small;background-color:#059; font-weight:bold; color:white">{$ts.testsuite.name|escape}</caption>
  			        <thead>
    			        <tr style="background-color:#059; font-weight:bold; color:white">
                    <th>{$labels.th_test_case}&nbsp;{$gsmarty_gui->role_separator_open}
                  	{$labels.version}{$gsmarty_gui->role_separator_close}</th>
                  </tr>
  			        </thead>
                <tbody>  
                  {foreach from=$ts.testcases item=tcase}
                    <tr>
                      {$version = current($tcase.tcversions)}
                      <td>{$tcase.name|escape}&nbsp;[{$version}]
                      </td>
                    </tr>
                  {/foreach}   
                </tbody>
              </table>
            {/if}
          </div>
        {/if} {* $ts_id != '' *}
	    {/foreach}
	  </div>
  {/if} {* $gui->has_tc *}  
</form>
</body>
</html>