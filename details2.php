<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<script>
"use strict";
function goback(){window.location.assign("/scan/")}
</script>
<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once("/etc/auth.php");
$ID = $_GET["id"];
$BASEURL = "http://127.0.0.1:9090/api/Items2/";
$req = curl_init();
curl_setopt($req, CURLOPT_URL, "$BASEURL/$ID");
curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
curl_setopt($req, CURLOPT_HTTPHEADER, ["Authorization: Basic $ENCODED_AUTH"]);
$response = json_decode(curl_exec($req));
$BASEURL_ANALYTICS = "http://127.0.0.1:9090/api/Items2/GetSalesDataForAnalysis";
$req_analytics = curl_init();
curl_setopt($req_analytics, CURLOPT_URL, "$BASEURL_ANALYTICS?PLU_CODE=$response->PLU_CODE");
curl_setopt($req_analytics, CURLOPT_RETURNTRANSFER, true);
curl_setopt($req_analytics, CURLOPT_HTTPHEADER, ["Authorization: Basic $ENCODED_AUTH"]);
$response_analytics = json_decode(curl_exec($req_analytics));
$SIH = $response_analytics->SIH;
$state_of_things="too-much";
//var_dump($response);
//var_dump($response_analytics);
$dbh = new PDO("sqlite:/saru/www-data/db.sqlite3");
$t = $dbh->beginTransaction();
$stmt_sql = $dbh->prepare("SELECT productsattime.TIME, s15, s30, s60, date as date, * FROM productsattime INNER JOIN productsattime_dailylatest ON productsattime_dailylatest.ID=productsattime.ID AND productsattime_dailylatest.latest=productsattime.TIME WHERE productsattime.ID=?");
$stmt = $stmt_sql->execute([$response->PLU_CODE]);
$past_data=$stmt_sql->fetchAll();
$dbh->commit();
?>
<script>"use strict"; var past_data = JSON.parse('<?php echo json_encode($past_data); ?>')</script>
	<title>DETAILS: <?php echo $response->PLU_DESC; ?></title>
<script>
"use strict";
var avgSales15 = [];
var avgSales30 = [];
var avgSales60 = [];
var deltaSales15 = [];
var deltaSales30 = [];
var deltaSales60 = [];
var dates=[];
for (var i=0; i<past_data.length-1; i++){avgSales15.push(past_data[i]['s15']/15)}
for (var i=0; i<past_data.length-1; i++){avgSales30.push(past_data[i]['s30']/30)}
for (var i=0; i<past_data.length-1; i++){avgSales60.push(past_data[i]['s60']/60)}
for (var i=0; i<past_data.length-1; i++){deltaSales15.push(past_data[i]['s15']- past_data[i+1]['s15'])}
for (var i=0; i<past_data.length-1; i++){deltaSales30.push(past_data[i]['s30']- past_data[i+1]['s30'])}
for (var i=0; i<past_data.length-1; i++){deltaSales60.push(past_data[i]['s60']- past_data[i+1]['s60'])}
for (var i=0; i<past_data.length; i++){dates.push(past_data[i]['date'])}
var averageDailySalesOnDay0_15=past_data[0]['s15']/15;
var averageDailySalesOnDay0_30=past_data[0]['s30']/30;
var averageDailySalesOnDay0_60=past_data[0]['s60']/60;
var averageDailySales_15_d = [averageDailySalesOnDay0_15];
var averageDailySales_30_d = [averageDailySalesOnDay0_30];
var averageDailySales_60_d = [averageDailySalesOnDay0_60];
var c_15 = averageDailySalesOnDay0_15;
var c_30 = averageDailySalesOnDay0_30;
var c_60 = averageDailySalesOnDay0_60;
for(var i=0; i<deltaSales15.length; i++){
	c_15+=deltaSales15[i];
	averageDailySales_15_d.push(c_15);
}
for(var i=0; i<deltaSales30.length; i++){
	c_30+=deltaSales30[i];
	averageDailySales_30_d.push(c_30);
}
for(var i=0; i<deltaSales60.length; i++){
	c_60+=deltaSales60[i];
	averageDailySales_60_d.push(c_60);
}
console.log(averageDailySales_60_d);
</script>
<script type="text/javascript" src="chart.umd.min.js">
</script>
<script>
document.addEventListener('DOMContentLoaded', displayChart);
function displayChart(){
	var daily_sales = new Chart(document.getElementById('chart_sales'),
{
	type: 'line',
		data: {
		labels: dates,
			datasets: [{label: "Daily Average Sales (15d Average)", data: avgSales15},
				{label: "Daily Average Sales (30d Average)", data: avgSales30},
				{label: "Daily Average Sales (60d Average)", data: avgSales60}],
			options: {scale: {y: {beginAtZero: true}}}
}
}
)
}
</script>
</head>
<body>
<div class ="button-container">
<button onclick="goback()" class="btn goback" > <img  src="icons/backButton.png" height= "70px" width="70px"></button>
</div>
<table class="named">
<tr>
<th>Name</th>
<td><?php echo $response->PLU_DESC; ?></td>
</tr>
<tr>
<th>Code</th>
<td><?php echo $response->PLU_CODE; ?></td>
</tr>
<tr>
<th>Selling price</th>
<td><?php echo $response->PLU_SELL; ?></td>
</tr>
<tr class="<?php echo $state_of_things; ?>">
<th>SIH</th>
<td><?php echo $response->SIH; ?></td>
</tr>
<tr>
<th>Sold (15 days)</th>
<td><?php echo $response_analytics->S_D15; ?></td>
</tr>
<tr>
<th>Sold (30 days)</th>
<td><?php echo $response_analytics->S_D30; ?></td>
</tr>
<tr>
<th>Sold (60 days)</th>
<td><?php echo $response_analytics->S_D60; ?></td>
</tr>
</table>
<details>
<summary>More details</summary>
<table class="others">
<tr>
<th scope="col">Field</th>
<th scope="col">Value</th>
</tr>
<?php foreach($response as $field=>$value) { ?>
<tr>
<td><?php echo $field; ?></td>
<td><?php echo $value; ?></td>
</tr>
<?php } ?>
<?php foreach($response_analytics as $field=>$value) { ?>
<tr>
<td><?php echo $field; ?></td>
<td><?php echo $value; ?></td>
</tr>
<?php } ?>
</table>
<?php
echo "<pre>".json_encode($response, JSON_PRETTY_PRINT)."</pre>";
echo "<pre>".json_encode($response_analytics, JSON_PRETTY_PRINT)."</pre>";
?>
</details>
<button class ="goBackButton" onclick="goback()" style={background-color: blue}> <img  src="icons/backButton.png"> </button>
<canvas id="chart_sales" width="400" height="400"></canvas>
</body>
</html>
