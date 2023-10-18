<?php

$str = file_get_contents('gilbarco.json');
$json = json_decode($str, true);

//echo '<pre>' . print_r($json, true) . '</pre>';

//$json é um array associativo


$retorno = new StdClass();
$retorno->results = [];

foreach($json['results'] as $keyJ => $valueJ){

    //aloca objeto padrão do php
    $objJ = new StdClass();
    $objJ->id = $valueJ['id'];
    $objJ->name = $valueJ['name'];
    $objJ->notes = $valueJ['notes'];    
    
    $retorno->results[] = $objJ;   
}

/*
echo "\n";
echo "\n";
echo "\n";
echo '--------------------------';
echo '<pre>';
print_r($retorno);
echo '</pre>';
*/

/*  
echo '<pre>';
echo json_encode($retorno);    
echo '</pre>';
*/

$fp = fopen('resultsELTON.json', 'w');
fwrite($fp, json_encode($retorno, JSON_UNESCAPED_SLASHES));
fclose($fp); 

die('rodou 1111');  


?>