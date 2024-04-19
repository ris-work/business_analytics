<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
//var_dump($_POST["ids"]);
//var_dump($_POST);
//$IDs = json_decode($_POST["ids"]);
$IDs = json_decode($_GET["ids"]);
//var_dump($IDs);
$REQUESTS = [];
$RESPONSES=[];
$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
foreach($IDs as $ID){
//var_dump($IDs);
//$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t = $dbh->beginTransaction();
$stmt_sql = $dbh->prepare("SELECT * FROM (SELECT sum(quantity) as S_D60, itemcode FROM hourly WHERE itemcode IN (?) AND daydate BETWEEN date('now', '-61 days') AND date('now', '-1 day')) S60T NATURAL INNER JOIN (SELECT sum(quantity) AS S_D30 FROM hourly WHERE itemcode IN (?) AND daydate BETWEEN date('now', '-31 days') AND date('now', '-1 day')) S30T NATURAL INNER JOIN (SELECT sum(quantity) AS S_D15 FROM hourly WHERE itemcode IN (?) AND daydate BETWEEN date('now', '-16 days') AND date('now', '-1 day')) AS S15T");
$stmt = $stmt_sql->execute([$ID, $ID, $ID]);
$past_data=$stmt_sql->fetchAll();
$dbh->commit();
//var_dump($past_data);
array_push($RESPONSES, $past_data[0]);
}
echo json_encode($RESPONSES);
//$response = json_decode(curl_exec($req));
//var_dump($response);
?>
