<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
//var_dump($_POST["ids"]);
//var_dump($_POST);
$IDs = json_decode($_POST["ids"]);
//$IDs = json_decode($_GET["ids"]);
//var_dump($IDs);
$REQUESTS = [];
$RESPONSES=[];
$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
//foreach($IDs as $ID){
//var_dump($IDs);
//$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t = $dbh->beginTransaction();
//$stmt_sql = $dbh->prepare("SELECT itemcode AS PLU_CODE, desc AS PLU_DESC, sih AS SIH, sell/iif(sih=0, 1, sih) AS PLU_SELL FROM sih_current");
$stmt_sql = $dbh->prepare("SELECT selling.itemcode AS PLU_CODE, desc AS PLU_DESC, sih_current.sell/iif(sih=0,1,sih) AS PLU_SELL_AV, selling.sell AS PLU_SELL, sih AS SIH FROM sih_current JOIN selling ON selling.itemcode = sih_current.itemcode");
$stmt = $stmt_sql->execute();
$past_data=$stmt_sql->fetchAll();
$dbh->commit();
//var_dump($past_data);
//array_push($RESPONSES, $past_data[0]);
$RESPONSES = $past_data;
//}
echo json_encode($RESPONSES);
//$response = json_decode(curl_exec($req));
//var_dump($response);
?>
