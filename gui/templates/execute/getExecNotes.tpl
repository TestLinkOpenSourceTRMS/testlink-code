{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource getExecNotes.tpl
smarty template - used to show execution notes on execution feature

*}
<html>
<head>
</head>
<body>
{if $webeditorType == 'none'}
<textarea {$readonly} name="notes" cols="70" rows="10" style="background:transparent;">
{$notes|escape}
</textarea>
{else}
{$notes}
{/if}
</body>
</html>