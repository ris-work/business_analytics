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
//var_dump($ID);
//$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t = $dbh->beginTransaction();
$stmt_sql = $dbh->prepare("SELECT *, S60T.itemcode AS CODE FROM (SELECT total(quantity) as S_D60, itemcode FROM hourly WHERE daydate BETWEEN date('now', '-61 days') AND date('now', '-1 day') GROUP BY itemcode) S60T JOIN (SELECT total(quantity) AS S_D30, itemcode FROM hourly WHERE daydate BETWEEN date('now', '-31 days') AND date('now', '-1 day') GROUP BY itemcode) S30T ON S30T.itemcode = S60T.itemcode JOIN (SELECT total(quantity) AS S_D15, itemcode FROM hourly WHERE daydate BETWEEN date('now', '-16 days') AND date('now', '-1 day') GROUP BY itemcode) AS S15T ON S15T.itemcode = S30T.itemcode");
$stmt = $stmt_sql->execute();
$past_data=$stmt_sql->fetchAll();
$dbh->commit();
//var_dump($past_data);
if($past_data == null) {$past_data = array(0 => array("CODE"=>0, "S_D60"=>0, "S_D30"=>0, "S_D15"=>0));}
//array_push($RESPONSES, $past_data[0]);
$RESPONSES = $past_data;
//}
//echo json_encode(json_encode($RESPONSES));
echo json_encode(($RESPONSES));
//$response = json_decode(curl_exec($req));
//var_dump($response);
?>
