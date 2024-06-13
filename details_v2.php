<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<script>
"use strict";
function goback(){window.location.assign("/scan/")}
function graph(){}
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
//var_dump($response);
if (true || $response && !property_exists($response, "Message")){
$BASEURL_ANALYTICS = "http://127.0.0.1:9090/api/Items2/GetSalesDataForAnalysis";
$req_analytics = curl_init();
curl_setopt($req_analytics, CURLOPT_URL, "$BASEURL_ANALYTICS?PLU_CODE=$response->PLU_CODE");
curl_setopt($req_analytics, CURLOPT_RETURNTRANSFER, true);
curl_setopt($req_analytics, CURLOPT_HTTPHEADER, ["Authorization: Basic $ENCODED_AUTH"]);
if($response==null){$response = new class{public $PLU_CODE = "";}; $response->PLU_CODE=$_GET["id"];};
$response_analytics = json_decode(curl_exec($req_analytics));
$SIH = $response_analytics->SIH;
$state_of_things="too-much";
//var_dump($response);
//var_dump($response_analytics);
$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$dbh->query("pragma mmap_size=2000000000");
$t = $dbh->beginTransaction();
//$stmt_sql = $dbh->prepare("SELECT productsattime.TIME, s15, s30, s60, date as date, * FROM productsattime INNER JOIN productsattime_dailylatest ON productsattime_dailylatest.ID=productsattime.ID AND productsattime_dailylatest.latest=productsattime.TIME WHERE productsattime.ID=?");
$stmt_sql = $dbh->prepare("WITH closestfuturedatesfordate_prices AS NOT MATERIALIZED (SELECT itemcode, x AS date, max(daydate) AS closestfuturedate FROM dates JOIN hourly ON dates.x > hourly.daydate WHERE itemcode=? GROUP BY date) SELECT trendsfrommindate.date, closestsihbydate.sih AS SIH, total(dailyqty) OVER (ORDER BY trendsfrommindate.date ROWS BETWEEN 14 PRECEDING AND CURRENT ROW) AS s15, total(dailyqty) OVER (ORDER BY trendsfrommindate.date ROWS BETWEEN 29 PRECEDING AND CURRENT ROW) AS s30, total(dailyqty) OVER (ORDER BY trendsfrommindate.date ROWS BETWEEN 59 PRECEDING AND CURRENT ROW) AS s60, avgcost, avgsell FROM (SELECT total(quantity) as dailyqty, x as date, itemcode FROM dates LEFT JOIN (SELECT daydate, total(quantity) AS quantity, itemcode AS itemcode FROM hourly WHERE itemcode=? GROUP BY daydate) daily ON dates.x = daily.daydate WHERE x > (SELECT min(daydate) FROM hourly WHERE itemcode=?) GROUP BY x ORDER BY x) trendsfrommindate LEFT JOIN (SELECT x AS date, sih FROM (SELECT itemcode, x, min(datetime) AS closestpointinfuture FROM (SELECT itemcode, futures.x, datetime FROM (SELECT dates.x AS x, datetime, itemcode, sih FROM(SELECT datetime, itemcode, sih FROM (SELECT datetime, itemcode, sih FROM sih_history UNION SELECT substr(datetime('now'), 1, 16) as datetime, CAST(itemcode AS int) AS itemcode, CAST(sih AS int) FROM sih_current) WHERE itemcode=? ORDER BY datetime) RIGHT JOIN dates ON datetime > dates.x ORDER BY dates.x DESC) futures JOIN dates ON dates.x = futures.x ORDER BY dates.x) GROUP BY x) nearestfuture JOIN (SELECT * FROM (SELECT datetime, itemcode, sih FROM sih_history UNION SELECT substr(datetime('now'), 1, 16) as datetime, CAST(itemcode AS int) AS itemcode, CAST(sih AS int) FROM sih_current) WHERE itemcode=? ORDER BY datetime) history ON history.datetime = nearestfuture.closestpointinfuture ORDER BY datetime) closestsihbydate ON trendsfrommindate.date = closestsihbydate.date LEFT JOIN (SELECT closestfuturedatesfordate_prices.itemcode, date, closestfuturedatesfordate_prices.closestfuturedate, sum(sumcost)/sum(quantity) AS avgcost, sum(sumsell)/sum(quantity) AS avgsell FROM closestfuturedatesfordate_prices JOIN hourly ON closestfuturedatesfordate_prices.itemcode = hourly.itemcode AND closestfuturedatesfordate_prices.closestfuturedate = hourly.daydate GROUP BY date) pricechanges ON pricechanges.date=trendsfrommindate.date");
$stmt = $stmt_sql->execute([$response->PLU_CODE, $response->PLU_CODE, $response->PLU_CODE, $response->PLU_CODE, $response->PLU_CODE]);
$past_data=$stmt_sql->fetchAll();
$dbh->commit();
$dbh_cost = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t_cost = $dbh_cost->beginTransaction();
$stmt_sql_cost = $dbh_cost->prepare("SELECT itemcode, daydate, cost FROM cost WHERE itemcode=? AND daydate = (SELECT max(daydate) FROM cost WHERE itemcode = ?)");
$stmt_cost = $stmt_sql_cost->execute([$response->PLU_CODE, $response->PLU_CODE]);
$data_cost=$stmt_sql_cost->fetchAll();
$dbh_cost->commit();
$dbh_cost_grn = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t_cost_grn = $dbh_cost_grn->beginTransaction();
$stmt_sql_cost_grn = $dbh_cost_grn->prepare("SELECT itemcode, date, cost, runno FROM cost_purchase WHERE itemcode=?");
$stmt_cost_grn = $stmt_sql_cost_grn->execute([$response->PLU_CODE]);
$data_cost_grn=$stmt_sql_cost_grn->fetchAll();
$dbh_cost_grn->commit();
?>
<script>"use strict"; var past_data = JSON.parse('<?php echo json_encode($past_data); ?>')</script>
<script>"use strict"; var data_cost = JSON.parse('<?php echo json_encode($data_cost); ?>')</script>
	<title>DETAILS: <?php echo $response->PLU_DESC; ?></title>
<script>
"use strict";
var avgSales15 = [];
var avgSales30 = [];
var avgSales60 = [];
var avgCost = [];
var avgSell = [];
var SIH = [];
var deltaSales15 = [];
var deltaSales30 = [];
var deltaSales60 = [];
var dates=[];
for (var i=0; i<past_data.length-1; i++){avgSales15.push(past_data[i]['s15']/15)}
for (var i=0; i<past_data.length-1; i++){avgSales30.push(past_data[i]['s30']/30)}
for (var i=0; i<past_data.length-1; i++){avgSales60.push(past_data[i]['s60']/60)}
for (var i=0; i<past_data.length-1; i++){avgCost.push(past_data[i]['avgcost'] ?? 0)}
for (var i=0; i<past_data.length-1; i++){avgSell.push(past_data[i]['avgsell'] ?? 0)}
for (var i=0; i<past_data.length-1; i++){SIH.push(past_data[i]['SIH'])}
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
		datasets: [{label: "Daily Average Sales (15d Average)", data: avgSales15, tenstion: 0.8, cubicInterpolationMode: 'monotone'},
			{label: "Daily Average Sales (30d Average)", data: avgSales30, tension: 0.4, cubicInterpolationMode: 'monotone'},
			{label: "Daily Average Sales (60d Average)", data: avgSales60, tension: 0.4, cubicInterpolationMode: 'monotone'},
			{label: "Closest past average cost [360days+ only]", data: avgCost, tension: 0.4, cubicInterpolationMode: 'monotone', yAxisID: 'y1', pointRadius: 0.5},
			{label: "Closest past average selling price [360d+ only]", data: avgSell, tension: 0.4, cubicInterpolationMode: 'monotone', yAxisID: 'y1', pointRadius: 0.5}]
	},
			options: {scales: {y: {beginAtZero: true, grid: {color: "#449944"}, y1: {beginAtZero: true}}, 
		y1: {type: 'linear', display: true, position: 'right', grid: {drawOnChartArea: false}},
				x: {grid: {color: "#077"}}},
			responsive: false
		}
}
)
	var daily_SIH = new Chart(document.getElementById('chart_sih'),
{
	type: 'line',
	data: {
		labels: dates,
		datasets: [{label: "SIH", data: SIH, tenstion: 0.8, cubicInterpolationMode: 'monotone', borderColor: "#000", backgroundColor: "#000"}]
	},
		options: {scales: {y: {beginAtZero: true, grid: {color: "#000"}, ticks: {color: "#000"}}, 
				x: {grid: {color: "#000"}, ticks: {color: "#000"}}},
			responsive: false
		}
}
)
}
</script>
</head>
<body>
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
<th>Fill sold (15 days)</th>
<td><?php echo $response_analytics->S_D15 - $response->SIH; ?></td>
</tr>
<tr>
<th>Fill sold (30 days)</th>
<td><?php echo $response_analytics->S_D30 - $response->SIH; ?></td>
</tr>
<tr>
<th>Fill sold (60 days)</th>
<td><?php echo $response_analytics->S_D60 - $response->SIH; ?></td>
</tr>
<tr>
<th>Cost<sup> (<?php echo $data_cost[0]["daydate"]; ?>)</sup><sup style="font-size: 0.25em">Average of entries</sup></th>
<td><?php echo number_format($data_cost[0]["cost"], 2); ?><sup> gross <?php echo number_format(100*$response->PLU_SELL/$data_cost[0]["cost"] - 100, 2); ?>%</sup></td>
</tr>
<tr>
<th>Cost<sup> (<?php echo substr($data_cost_grn[0]["date"], 0, 10); ?>)</sup><sup style="font-size: 0.25em">GRN (<?php echo $data_cost_grn[0]["runno"]; ?>)</sup></th>
<td><?php echo number_format($data_cost_grn[0]["cost"], 2); ?><sup> gross <?php echo number_format(100*$response->PLU_SELL/$data_cost_grn[0]["cost"] - 100, 2); ?>%</sup></td>
</tr>
</table>
<details>
<summary><button class="btn goback" > <img  src="icons/down_button.png" style= "height:42%; width:42%;"> </button></summary>
<summary><button class="btn goback" > <img  src="icons/down_button.png" style= "height:42%; width:42%;"> </button></summary>
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
}
else if($response==null){
?>
	<h1 style="font-family: Monospace; text-align: center; color: darkgoldenrod; font-size: 5em; border: 0.2em double goldenrod; margin: 2em;">Server down or could not be reached.</h1>
<?php
}else {
?>
	<h1 style="font-family: Monospace; text-align: center; color: darkred; font-size: 5em; border: 0.2em double red; margin: 2em;">Does not exist. <?php echo $response->Message ?></h1>
<?php
}
?>
</details>
<div class="centered-container">
<div style="background: linear-gradient(180deg, lightseagreen, red)">
<!--<span style="mix-blend-mode: color-burn; color: white; background: black">Hello! Chart area...</span>-->
<div style="background: white; mix-blend-mode: lighten;">
<canvas id="chart_sih" style="" width="795" height="650"></canvas>
</div>
</div>
</div>
<div class="centered-container">
<canvas id="chart_sales" width="795" height="650"></canvas>
</div>
<div id ="bottom"> <button onclick="goback()" class="btn goback" > <img  src="icons/back_button.png" style="height:55%; width:55%;"> </button>
<a href="<?php echo "moredetails.php?id=$ID" ?>"><button class="moredetails"> <img  src="icons/clock.svg" style="filter: grayscale(100%) opacity(50%);height: max(6vh, 6vw); width:max(6vh, 6vw);"></button></a>
<button class="graph" onclick="document.getElementById('chart_sales').style.visibility='visible'"> <img  src="icons/graph.png" style="height: 55%; width:55%; left:33%;"> </button> </div>
</body>
</html>
