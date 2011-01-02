<?php
This code is based on test_smtl_gmail_basic.php provided
by PHPMAILER.
Has been adapted to read TestLink mail configuration

Author: franciscom - 20110102
?>

<html>
<head>
<title>PHPMailer - SMTP basic test (Adapted for TestLink)</title>
</head>
<body>
<h1>PHPMailer - SMTP basic test (Adapted for TestLink)</h1>
<?php

require_once("../../config.inc.php");
require_once("common.php");

error_reporting(E_STRICT);

date_default_timezone_set('America/Argentina/Buenos_Aires');

define( 'PHPMAILER_PATH', dirname(__FILE__). '/../../third_party/phpmailer' . DIRECTORY_SEPARATOR );
require_once( PHPMAILER_PATH . 'class.phpmailer.php' );

$mail = new PHPMailer();
$body = "TestLink Mail Test";

$mail->IsSMTP(); // telling the class to use SMTP
$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only


$key2debug = array('SMTPAuth','SMTPSecure','Host','Port','Username','Password','From','To');

$mail->SMTPAuth = true;                  // enable SMTP authentication
if (trim(config_get('smtp_connection_mode') != '' ) ) 
{
	$mail->SMTPSecure = config_get( 'smtp_connection_mode' );
}

# Mail subsystem configuration
$mail->Host = config_get( 'smtp_host' );
$mail->Port = config_get( 'smtp_port' );
$mail->Username = config_get('smtp_username');
$mail->Password = config_get('smtp_password');

# Mail contents
$mail->CharSet = config_get( 'charset');
$mail->From = config_get( 'from_email' );
$mail->Subject = "PHPMailer Test Subject via smtp (Gmail), basic";
$mail->MsgHTML($body);

$ToAddress = "francisco.mancardi@gruppotesi.com";
$mail->AddAddress($ToAddress, "John Doe");

echo '<h2>Mail Settings</h2><br>';
// echo '<pre>';
foreach($key2debug as $pty)
{
	echo '<b>' . $pty . ':</b>' . $mail->$pty . '<br>';
}	
echo '<b>' . 'To' . ':</b>' . $ToAddress . '<br>';
echo '<p>';


if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}
?>
</body>
</html>
