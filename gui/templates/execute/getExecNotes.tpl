{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: getExecNotes.tpl,v 1.4 2009/06/10 19:35:59 franciscom Exp $
Purpose: smarty template - template for show execution notes 

rev : 20080104 - francisco.mancardi@gruppotesi.com
      added logic to display notes got using rich web editors
*}
<html>
<head></head>
<body>
{if $webeditorType == 'none'}
<textarea  name='notes' cols=70 rows=10 style="background:transparent;">
{$notes|escape}
</textarea>
{else}
{$notes}
{/if}
</body>
</html>