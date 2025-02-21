<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "./env.php";
$arg_dbname = escapeshellarg($dbname);
$arg_scriptname = escapeshellarg(".read " . __FILE__ . ".sql");
//echo shell_exec("/usr/bin/sqlite3 $arg_dbname $arg_scriptname");
$input = file_get_contents("php://input");
$data = json_decode($input, false);
//var_dump($input);

if(json_last_error() !== JSON_ERROR_NONE){
	echo "Invalid JSON data";
	exit();
}

print_r($data->itemcode);
$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$itemcode = $data->itemcode;

if(property_exists($data, "label_i18n_ta")){
$stmt_ta = $dbh->prepare("INSERT INTO label_i18n(itemcode, label_i18n_ta) VALUES (:itemcode, :ta) ON CONFLICT DO UPDATE SET label_i18n_ta = excluded.label_i18n_ta");
echo "Tamil label is present";
$stmt_ta->bindParam(':itemcode', $itemcode);
$stmt_ta->bindParam(':ta', $data->label_i18n_ta);
print_r($data->label_i18n_ta);
$stmt_ta->execute();
}
echo "Att2";

if(property_exists($data, "label_i18n_si")){
$stmt_si = $dbh->prepare("INSERT INTO label_i18n(itemcode, label_i18n_si) VALUES (:itemcode, :si) ON CONFLICT DO UPDATE SET label_i18n_si = excluded.label_i18n_si");
echo "Sinhala label is present";
$stmt_si->bindParam(':itemcode', $itemcode);
$stmt_si->bindParam(':si', $data->label_i18n_si);
print_r($data->label_i18n_si);
$stmt_si->execute();
}

?>
