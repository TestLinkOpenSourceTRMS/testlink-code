{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

@filesource resultsTCFlatLauncher.tpl
@author     Francisco Mancardi (francisco.mancardi@gmail.com)

GUI to ask user for filters, due to excesive amount of data

*}

{lang_get var='labels' 
          s='active_builds,all_active_builds,release_date_start,release_date_end,date,hour,submit_query,show_calender'}
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

<script>
jQuery( document ).ready(function() {
jQuery(".chosen-select").chosen({ width: "100%" });
});
</script>
</head>

<body>
	<h1 class="title">{$gui->pageTitle}</h1>
    <div class="workBack">
      {if $gui->userFeedback != ''}
      <img src="{$tlImages.warning}"> <b>{$gui->userFeedback}</b>
      {/if}
      <form action="lib/results/resultsTCFlat.php" method="post">
        <input type="hidden" id="tproject_id" name="tproject_id" value="{$gui->tproject_id}" />
        <input type="hidden" id="tplan_id" name="tplan_id" value="{$gui->tplan_id}" />
        <input type="hidden" id="do_action" name="do_action" value="result" />
        <div>
          <table class="simple" style="margin-left: 0px;width: 80%">
            <tr>
                <th width="100%">{$labels.active_builds}</th>
            </tr>
            <tr>
            	<td>
                <select multiple class="chosen-select" name="build_set[]" id="build_set" data-placeholder="{$labels.all_active_builds}">
                  {foreach key=build_id item=buildObj from=$gui->buildInfoSet}
                    <option value="{$build_id}">{$buildObj.name|escape}</option>
                  {/foreach}
		  	        </select>
              </td>
            </tr>
          </table>
        </div>
        <div>
        	<input type="submit" value="{$labels.submit_query}"/>
        </div>
      </form>
    </div>
</body>
</html>