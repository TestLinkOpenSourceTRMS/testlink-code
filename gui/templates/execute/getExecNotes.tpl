{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: getExecNotes.tpl,v 1.6 2010/01/29 20:50:01 franciscom Exp $
Purpose: smarty template - template for show execution notes 

rev : 20100129 - BUGID 3113 - franciscom
      solved ONLY for  $webeditorType == 'none'
      
      20080104 - franciscom
      added logic to display notes got using rich web editors
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