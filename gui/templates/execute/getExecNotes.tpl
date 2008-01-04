{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: getExecNotes.tpl,v 1.2 2008/01/04 16:17:49 franciscom Exp $
Purpose: smarty template - template for show execution notes 

rev : 20080104 - francisco.mancardi@gruppotesi.com
      added logic to display notes got using rich web editors
*}
<html>
<head></head>
<body>
{if $webeditorType == 'none'}
<textarea readonly name='notes' cols=80 rows=10 style="background:transparent;">
{$notes|escape}
{else}
{$notes}
{/if}
</textarea>			
</body>
</html>