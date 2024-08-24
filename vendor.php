<head>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<div class="printer">
<table style="margin-left: auto; margin-right: auto;">
<tr style="position: sticky; top: 0; z-index: 1000;">
<th>itemcode</th>
<th>desc</th>
<th>cost</th>
<th>sell</th>
</tr>
<?php
	error_reporting(E_ALL);
if($_GET["id"]){
	$CODE = $_GET["id"];
	$dbh_v = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
	$t_cost = $dbh_v->beginTransaction();
	$stmt_sql_cost = $dbh_v->prepare(
		"SELECT sih_current.itemcode AS itemcode, desc, sih_current.cost/sih AS cost, sih_current.sell/sih AS sell FROM product_vendors JOIN sih_current ON sih_current.itemcode = product_vendors.itemcode WHERE vendorcode=?"
	);
	$stmt_v = $stmt_sql_cost->execute([
		$CODE
	]);
	$data_v = $stmt_sql_cost->fetchAll();
	$dbh_v->commit();
	foreach($data_v as $product){
		echo "<tr>";
		echo "<td>" . json_encode($product["itemcode"]) . "</td>";
		echo "<td>" . json_encode($product["desc"]) . "</td>";
		echo "<td>" . json_encode($product["cost"]) . "</td>";
		echo "<td>" . json_encode($product["sell"]) . "</td>";
		echo "</tr>";
	}
} else {
		die("ID missing");
}
?>
</table>
</div>
