<?php
header('Content-Type: application/javascript');

/*$rutaAPIenergy   = "http://provanemon.infinityfreeapp.com/api/prices/energy.php";
$rutaAPIpower   = "http://provanemon.infinityfreeapp.com/api/prices/power.php";
$rutaAPIgeneratebill  = "http://provanemon.infinityfreeapp.com/api/generatebill.php";
*/

$rutaAPIenergy = "http://localhost/provanemon/api/prices/energy.php";
$rutaAPIpower = "http://localhost/provanemon/api/prices/power.php";
$rutaAPIgeneratebill = "http://localhost/provanemon/api/generatebill.php";

echo "const rutaAPIenergy = '$rutaAPIenergy';\n";
echo "const rutaAPIpower = '$rutaAPIpower';\n";
echo "const rutaAPIgeneratebill = '$rutaAPIgeneratebill';\n";
?>