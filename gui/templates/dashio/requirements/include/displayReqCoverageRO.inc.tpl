{*
displayReqCoverageRO.inc.tpl
*}  
    <fieldset class="x-fieldset x-form-label-left">
       <legend class="legend_container">{$labels.coverage}</legend>
 
    {section name=rowCov loop=$argsReqCoverage}
        {$reqCovItem = $argsReqCoverage[rowCov]}
        <span>
        &nbsp;&nbsp; 
        {if $reqCovItem.is_obsolete ==1}
        <img class="clickable" src="{$tlImages.heads_up}"
             title="{$labels.obsolete}" />
        {else}
          &nbsp;&nbsp;&nbsp; 
        {/if}
        <img class="clickable" src="{$tlImages.history_small}"
             onclick="javascript:openExecHistoryWindow({$reqCovItem.id});"
             title="{$labels.execution_history}" />
        <img class="clickable" src="{$tlImages.edit_icon}"
             onclick="javascript:openTCaseWindow({$reqCovItem.id});"
             title="{$labels.design}" />
        {$args_gui->tcasePrefix|escape}{$args_gui->glueChar}
        {$reqCovItem.tc_external_id}{$args_gui->pieceSep}
        {$reqCovItem.tcase_name|escape} [{$labels.version} 
        {$reqCovItem.version}]
        </span><br />
      {/section}
    </fieldset>