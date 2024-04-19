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
$stmt_sql = $dbh->prepare("SELECT * FROM (SELECT sum(quantity) as S90, itemcode FROM hourly WHERE itemcode IN (?) AND daydate BETWEEN date('now', '-91 days') AND date('now', '-1 day')) S90T NATURAL INNER JOIN (SELECT sum(quantity) AS S45 FROM hourly WHERE itemcode IN (?) AND daydate BETWEEN date('now', '-46 days') AND date('now', '-1 day')) S45T NATURAL INNER JOIN (SELECT sum(quantity) AS S30 FROM hourly WHERE itemcode IN (?) AND daydate BETWEEN date('now', '-31 days') AND date('now', '-1 day')) AS S30T");
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
