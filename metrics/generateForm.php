<? 
Header("Content-Disposition: inline; filename=test.xls");
Header("Content-Description: PHP Generated Data");
Header("Content-type: application/vnd.ms-excel; name='My_Excel'");
flush;
include "formTemplate.php";
?>
