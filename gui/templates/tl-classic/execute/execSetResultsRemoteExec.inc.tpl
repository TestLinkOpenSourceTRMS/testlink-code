{*
TestLink Open Source Project - http://testlink.sourceforge.net/

@filesource execSetResultsRemoteExec.inc.tpl
@used-by execSetResults.inc.tpl
*}
      <b>{$labels.remoteExecFeeback}</b>
      {if $gui->remoteExecFeedback.system == ''}
        <br>{$gui->remoteExecFeedback.statusVerbose|escape}
        <br>{$gui->remoteExecFeedback.notes|escape}
        {if $gui->remoteExecFeedback.status == ''}  
          <br>{$gui->remoteExecFeedback.scheduled|escape}
          <br>{$gui->remoteExecFeedback.timestamp|escape}
        {/if} 
      {else}
        <br>{$gui->remoteExecFeedback.system.msg|escape}
      {/if}
      <p>
