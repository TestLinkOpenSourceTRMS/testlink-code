{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: getExecNotes.tpl,v 1.1 2007/12/31 16:24:34 franciscom Exp $
Purpose: smarty template - template for show execution notes 
*}
<html>
<head></head>
<body>
<textarea readonly name='notes' cols=80 rows=10 style="background:transparent;">
{$notes|escape}
</textarea>			
</body>
</html>