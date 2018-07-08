<?php
include("class/conector.php");
include("class/proposicoes.php");

$cd = new CamaraDeputados();
$cd->getDataAll();

echo $cd->getStatistica();
?>