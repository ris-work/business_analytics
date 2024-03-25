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
if (strlen($ID) == 6 || ($response && !property_exists($response, "Message"))){
$BASEURL_ANALYTICS = "http://127.0.0.1:9090/api/Items2/GetSalesDataForAnalysis";
$req_analytics = curl_init();
curl_setopt($req_analytics, CURLOPT_URL, "$BASEURL_ANALYTICS?PLU_CODE=$response->PLU_CODE");
curl_setopt($req_analytics, CURLOPT_RETURNTRANSFER, true);
curl_setopt($req_analytics, CURLOPT_HTTPHEADER, ["Authorization: Basic $ENCODED_AUTH"]);
$response_analytics = json_decode(curl_exec($req_analytics));
$SIH = $response_analytics->SIH;
$state_of_things="too-much";
if($response){$id = $response->PLU_CODE;} else{$id = $_GET["id"];}
//var_dump($response);
//var_dump($response_analytics);
$dbh = new PDO("sqlite:/saru/www-data/db.sqlite3");
$t = $dbh->beginTransaction();
$stmt_sql = $dbh->prepare("SELECT productsattime.TIME, s15, s30, s60, date as date, * FROM productsattime INNER JOIN productsattime_dailylatest ON productsattime_dailylatest.ID=productsattime.ID AND productsattime_dailylatest.latest=productsattime.TIME WHERE productsattime.ID=?");
$stmt = $stmt_sql->execute([$response->PLU_CODE]);
$past_data=$stmt_sql->fetchAll();
$dbh->commit();
function getsalesbyhour($itemcode){
$dbhm = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t = $dbhm->beginTransaction();
$stmtm_sql = $dbhm->prepare("SELECT 100*hsq/sq AS psh, c.x as timehour FROM ((select sum(quantity) AS sq, * FROM hourly WHERE itemcode=?) a CROSS JOIN (SELECT itemcode, timehour, sum(quantity) AS hsq FROM hourly WHERE itemcode=? GROUP BY timehour) b) RIGHT JOIN (SELECT x FROM cnt LIMIT 17 OFFSET 6) c ON b.timehour = c.x ORDER BY c.x");
$stmtm = $stmtm_sql->execute([$itemcode, $itemcode]);
$past_datam=$stmtm_sql->fetchAll();
$dbhm->commit();
return $past_datam;
}
function getsalesbyday($itemcode){
$dbhm = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t = $dbhm->beginTransaction();
$stmtm_sql = $dbhm->prepare("SELECT a.x AS daydate_full, ifnull(b.sq, 0) AS quantity, b.sq as rawsq, sum(b.sq/15) FILTER (WHERE b.sq IS NOT null) OVER (ORDER BY a.x ROWS BETWEEN 14 PRECEDING AND CURRENT ROW) as da15, sum(b.sq/60) FILTER (WHERE b.sq IS NOT null) OVER (ORDER BY a.x ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) as da60, * FROM dates a LEFT JOIN (SELECT sum(quantity) AS sq, daydate FROM hourly WHERE itemcode=? GROUP BY daydate) b ON a.x=b.daydate WHERE a.x < date('now') AND a.x > (SELECT min(daydate) FROM hourly WHERE itemcode=?) AND a.x < (SELECT * FROM last_imported) ORDER BY a.x");
$stmtm = $stmtm_sql->execute([$itemcode, $itemcode]);
$past_datam=$stmtm_sql->fetchAll();
$dbhm->commit();
return $past_datam;
}
function lastimportedday(){
$dbhm = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t = $dbhm->beginTransaction();
$stmtm_sql = $dbhm->prepare("SELECT max(daydate) AS lastupdated FROM hourly");
$stmtm = $stmtm_sql->execute([]);
$past_datam=$stmtm_sql->fetchAll();
$dbhm->commit();
return $past_datam;
}
$salesdatabyhour=getsalesbyhour($id);
$salesdatabyday=getsalesbyday($id);
$salesdatalastimported=lastimportedday();

$daily_data_only = array_map(fn($x) => $x['quantity'], $salesdatabyday);

$desspec = array(
	0 => array("pipe", "r"),
	1 => array("pipe", "w"),
	2 => array("pipe", "r")
);
$cwd = '/tmp';
//var_dump(json_encode($daily_data_only));

$process = proc_open('python3 analysis.py', $desspec, $pipes);
if(is_resource($process)){
	fwrite($pipes[0], json_encode($daily_data_only));
	fclose($pipes[0]);
	$spectrum = trim(stream_get_contents($pipes[1]));
	//var_dump(stream_get_contents($pipes[2]));
	fclose($pipes[1]);
	proc_close($process);
}
else{
	echo "Process creation failed.";
}
?>
<script>"use strict"; var past_data = JSON.parse('<?php echo json_encode($past_data); ?>')</script>
<script>"use strict"; var sales_data_by_hour = JSON.parse('<?php echo json_encode($salesdatabyhour); ?>')</script>
<script>"use strict"; var sales_data_by_day = JSON.parse('<?php echo json_encode($salesdatabyday); ?>')</script>
<script>"use strict"; var sales_data_updated = JSON.parse('<?php echo json_encode($salesdatalastimported); ?>')</script>
<script>"use strict"; var sales_data_by_day_spectrum = JSON.parse('<?php echo ($spectrum); ?>')
var A = sales_data_by_day_spectrum[0];
var w = sales_data_by_day_spectrum[1];
var sarr=[];
A.forEach((v,k)=>{sarr.push({A: v, w: w[k]})});
var sarr_dup = sarr.map(x=>x);
sarr_dup = sarr_dup.filter(x=>x.w<366);
sarr_dup.sort((a,b)=>a.A<b.A?1:-1);
//var sarr_dup = sarr.map(x=>x);
if(sarr_dup[0].w==0) {sarr_dup.shift()}
var most_varied=sarr_dup.slice(0,10);
var avgsincefirstsale=A[0].toLocaleString();
var variation_int =  `The daily average since first sold is ${A[0].toLocaleString()}. Sales commonly varies every ${sarr_dup[0].w.toLocaleString()} days by \u00b1${sarr_dup[0].A.toLocaleString()}, every ${sarr_dup[1].w.toLocaleString()} days by \u00b1${(sarr_dup[1].A).toLocaleString()} units.`;
</script>
	<title>DETAILS: <?php echo $response->PLU_DESC; ?></title>
<script>
"use strict";
var avgSales15 = [];
var avgSales30 = [];
var avgSales60 = [];
var SIH = [];
var deltaSales15 = [];
var deltaSales30 = [];
var deltaSales60 = [];
var dates=[];
var dailysales_dates = sales_data_by_day.map(x => x['daydate_full']);
var dailysales_data = sales_data_by_day.map(x => x['quantity']);
var sales_hours = sales_data_by_hour.map(x => x['timehour']);
var sales_hours_data = sales_data_by_hour.map(x => x['psh']);
for (var i=0; i<past_data.length-1; i++){avgSales15.push(past_data[i]['s15']/15)}
for (var i=0; i<past_data.length-1; i++){avgSales30.push(past_data[i]['s30']/30)}
for (var i=0; i<past_data.length-1; i++){avgSales60.push(past_data[i]['s60']/60)}
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
	document.getElementById("avgsincefirst").innerText=avgsincefirstsale;
	document.getElementById("patterns").innerText=variation_int;
	var daily_sales = new Chart(document.getElementById('chart_sales'),
{
	type: 'line',
	data: {
		labels: dates,
		datasets: [{label: "Daily Average Sales (15d Average)", data: avgSales15, tenstion: 0.8, cubicInterpolationMode: 'monotone'},
			{label: "Daily Average Sales (30d Average)", data: avgSales30, tension: 0.4, cubicInterpolationMode: 'monotone'},
			{label: "Daily Average Sales (60d Average)", data: avgSales60, tension: 0.4, cubicInterpolationMode: 'monotone'}]
	},
		options: {scales: {y: {beginAtZero: true, grid: {color: "#449944"}}, 
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
	var daily_sales_by_day = new Chart(document.getElementById('chart_salesbyday'),
{
	type: 'line',
	data: {
		labels: dailysales_dates,
		datasets: [{label: "Sales", data: dailysales_data, tenstion: 0.8, cubicInterpolationMode: 'monotone', borderColor: "#000", backgroundColor: "#000"}]
	},
		options: {scales: {y: {beginAtZero: true, grid: {color: "#000"}, ticks: {color: "#000"}}, 
				x: {grid: {color: "#000"}, ticks: {color: "#000"}}},
			responsive: false
		}
}
)
	var daily_sales_by_hour = new Chart(document.getElementById('chart_salesbyhour'),
{
	type: 'bar',
	data: {
		labels: sales_hours,
		datasets: [{label: "Sales %", data: sales_hours_data, tenstion: 0.8, cubicInterpolationMode: 'monotone', borderColor: "#000", backgroundColor: "#000"}]
	},
		options: {scales: {y: {beginAtZero: true, max: 100, grid: {color: "#000"}, ticks: {color: "#000"}}, 
				x: {grid: {color: "#000"}, ticks: {color: "#000"}}},
			responsive: false
		}
}
)
}
</script>
</head>
<body>
<span><?php echo "Last updated (full sales data): {$salesdatalastimported[0]['lastupdated']}"; ?></span>
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
<tr style="border: 9px double black">
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
<tr>
<th>Average (since first sold): </th>
<td id="avgsincefirst"></td>
</tr>
<tr>
<td colspan=2><span id="patterns" style="max-width: 20em"></span></th>
</tr>
</table>
<details>
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
<canvas id="chart_salesbyday" width="795" height="650"></canvas>
<canvas id="chart_salesbyhour" width="795" height="650"></canvas>
<canvas id="chart_salesspectrum" width="795" height="650"></canvas>
<canvas id="chart_sales" width="795" height="650"></canvas>
</div>
<div id ="bottom"> <button onclick="goback()" class="btn goback" > <img  src="icons/back_button.png" style="height:55%; width:55%;"> </button>
<button class="graph" onclick="document.getElementById('chart_sales').style.visibility='visible'"> <img  src="icons/graph.png" style="height: 55%; width:55%; left:33%;"> </button> <div>
</body>
</html>
