<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: installStart.php,v 1.2 2005/08/16 17:59:48 franciscom Exp $ */
$installationType = $_GET['installationType'];
if ($installationType=='new') {
    header("Location: newInstallStart_TL.php");
    exit;
}

if ($installationType=='upgrade') {
    header("Location: upgradeStart.php");
    exit;
}
echo "No installationType found in \$_GET.";

?>
