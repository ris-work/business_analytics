<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<script>
"use strict";
function goback(){window.location.assign("/scan/")}
function graph(){}
</script>
<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
require_once "/etc/auth.php";
$ID = $_GET["id"];
$BASEURL = "http://127.0.0.1:9090/api/Items2/";
$req = curl_init();
curl_setopt($req, CURLOPT_URL, "$BASEURL/$ID");
curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
curl_setopt($req, CURLOPT_HTTPHEADER, ["Authorization: Basic $ENCODED_AUTH"]);
//$response = json_decode(curl_exec($req));
//var_dump($response);
if (true || ($response && !property_exists($response, "Message"))) {

	$BASEURL_ANALYTICS =
		"http://127.0.0.1:9090/api/Items2/GetSalesDataForAnalysis";
	$req_analytics = curl_init();
	//curl_setopt($req_analytics, CURLOPT_URL, "$BASEURL_ANALYTICS?PLU_CODE=$response->PLU_CODE");
	curl_setopt($req_analytics, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($req_analytics, CURLOPT_HTTPHEADER, [
		"Authorization: Basic $ENCODED_AUTH",
	]);
	$response = null;
	if ($response == null) {
		$response = new class {
			public $PLU_CODE = "";
		};
		$response->PLU_CODE = $_GET["id"];
	}
	//$response_analytics = json_decode(curl_exec($req_analytics));
	//$SIH = $response_analytics->SIH;
	$state_of_things = "too-much";
	//var_dump($response);
	//var_dump($response_analytics);
	$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
	$dbh->query("pragma mmap_size=2000000000");
	$t_lookup = $dbh->beginTransaction();
	$stmt_lookup = $dbh->prepare(
			"SELECT itemcode FROM (SELECT itemcode AS barcode, itemcode FROM sih_current UNION ALL SELECT barcode, itemcode FROM barcodes WHERE barcode NOT IN (SELECT itemcode FROM sih_current)) WHERE barcode=CAST(? AS int)"
	);
	$stmt_lookup->execute([$response->PLU_CODE]);
	$response->PLU_CODE = ($stmt_lookup->fetchAll())[0][0];
	$dbh->commit();
	$t = $dbh->beginTransaction();
	//$stmt_sql = $dbh->prepare("SELECT productsattime.TIME, s15, s30, s60, date as date, * FROM productsattime INNER JOIN productsattime_dailylatest ON productsattime_dailylatest.ID=productsattime.ID AND productsattime_dailylatest.latest=productsattime.TIME WHERE productsattime.ID=?");
	$stmt_sql = $dbh->prepare(
			"WITH closestfuturedatesfordate_prices AS NOT MATERIALIZED (
					SELECT itemcode, x AS date, max(daydate) AS closestfuturedate FROM dates JOIN hourly ON dates.x > hourly.daydate WHERE itemcode=? GROUP BY date -- This table contains the closest future date for a given date for a given itemcode
		) SELECT trendsfrommindate.date, closestsihbydate.sih AS SIH, 
		total(dailyqty) OVER (ORDER BY trendsfrommindate.date ROWS BETWEEN 14 PRECEDING AND CURRENT ROW) AS s15,  -- Window query for D15 and so on
		total(dailyqty) OVER (ORDER BY trendsfrommindate.date ROWS BETWEEN 29 PRECEDING AND CURRENT ROW) AS s30, 
		total(dailyqty) OVER (ORDER BY trendsfrommindate.date ROWS BETWEEN 59 PRECEDING AND CURRENT ROW) AS s60, 
		avg(dailyqty) OVER (ORDER BY trendsfrommindate.date ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS avgrun, -- Averaged cumulative up to the given date
		avgcost, avgsell 
	FROM (
		SELECT total(quantity) as dailyqty, x as date, itemcode FROM dates LEFT JOIN (SELECT daydate, total(quantity) AS quantity, itemcode AS itemcode FROM hourly WHERE itemcode=? GROUP BY daydate) daily ON dates.x = daily.daydate WHERE x > (SELECT min(daydate) FROM hourly WHERE itemcode=?) GROUP BY x ORDER BY x -- Choose trends greater than min date
) trendsfrommindate 
		LEFT JOIN (
			SELECT x AS date, sih FROM (
				SELECT itemcode, x, min(datetime) AS closestpointinfuture FROM (
					SELECT itemcode, futures.x, datetime FROM (
						SELECT dates.x AS x, datetime, itemcode, sih FROM(SELECT datetime, itemcode, sih FROM (
							SELECT datetime, itemcode, sih FROM sih_history -- History 
							UNION ALL SELECT substr(datetime('now'), 1, 16) as datetime, CAST(itemcode AS int) AS itemcode, CAST(sih AS int) FROM sih_current -- Add current to the set
						) WHERE itemcode=? ORDER BY datetime) 
						RIGHT JOIN dates ON datetime > dates.x ORDER BY dates.x DESC
					) futures JOIN dates ON dates.x = futures.x ORDER BY dates.x
				) GROUP BY x
			) nearestfuture 
				JOIN (
					SELECT * FROM (
						SELECT datetime, itemcode, sih FROM sih_history 
						UNION ALL SELECT substr(datetime('now'), 1, 16) as datetime, CAST(itemcode AS int) AS itemcode, CAST(sih AS int) FROM sih_current
					) WHERE itemcode=? ORDER BY datetime
				) history ON history.datetime = nearestfuture.closestpointinfuture ORDER BY datetime
		) closestsihbydate ON trendsfrommindate.date = closestsihbydate.date 
		LEFT JOIN (
			SELECT closestfuturedatesfordate_prices.itemcode, date, 
				closestfuturedatesfordate_prices.closestfuturedate, 
				sum(sumcost)/sum(quantity) AS avgcost, sum(sumsell)/sum(quantity) AS avgsell 
			FROM closestfuturedatesfordate_prices 
			JOIN hourly ON closestfuturedatesfordate_prices.itemcode = hourly.itemcode AND closestfuturedatesfordate_prices.closestfuturedate = hourly.daydate GROUP BY date
		) pricechanges ON pricechanges.date=trendsfrommindate.date"
	);
	$stmt = $stmt_sql->execute([
		$response->PLU_CODE,
		$response->PLU_CODE,
		$response->PLU_CODE,
		$response->PLU_CODE,
		$response->PLU_CODE,
	]);
	$past_data = $stmt_sql->fetchAll();
	$dbh->commit();
	$dbh_cost = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
	$t_cost = $dbh_cost->beginTransaction();
	$stmt_sql_cost = $dbh_cost->prepare(
		"SELECT itemcode, daydate, cost FROM cost WHERE itemcode=? AND daydate = (SELECT max(daydate) FROM cost WHERE itemcode = ?)"
	);
	$stmt_cost = $stmt_sql_cost->execute([
		$response->PLU_CODE,
		$response->PLU_CODE,
	]);
	$data_cost = $stmt_sql_cost->fetchAll();
	$dbh_cost->commit();
	$dbh_cost_grn = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
	$t_cost_grn = $dbh_cost_grn->beginTransaction();
	$stmt_sql_cost_grn = $dbh_cost_grn->prepare(
		"SELECT cost_purchase.itemcode, date, cost_purchase.cost, runno, desc, sih FROM cost_purchase LEFT JOIN sih_current ON sih_current.itemcode=cost_purchase.itemcode WHERE cost_purchase.itemcode=?"
	);
	$stmt_cost_grn = $stmt_sql_cost_grn->execute([$response->PLU_CODE]);
	$data_cost_grn = $stmt_sql_cost_grn->fetchAll();
	$dbh_cost_grn->commit();
	$dbh_cost_supl = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
	$t_cost_supl = $dbh_cost_supl->beginTransaction();
	$stmt_sql_cost_supl = $dbh_cost_supl->prepare(
		"SELECT vendors.vendorname, product_vendors.cost, vendors.vendorcode FROM product_vendors JOIN vendors ON vendors.vendorcode = product_vendors.vendorcode WHERE itemcode=?"
	);
	$stmt_cost_supl = $stmt_sql_cost_supl->execute([$response->PLU_CODE]);
	$data_cost_supl = $stmt_sql_cost_supl->fetchAll();
	$dbh_cost_supl->commit();
	$last = $past_data[count($past_data) - 1];

	function getsalesbyhour($itemcode)
	{
		$dbhm = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
		$t = $dbhm->beginTransaction();
		$stmtm_sql = $dbhm->prepare(
			"SELECT 100*hsq/sq AS psh, c.x as timehour FROM ((select sum(quantity) AS sq, * FROM hourly WHERE itemcode=?) a CROSS JOIN (SELECT itemcode, timehour, sum(quantity) AS hsq FROM hourly WHERE itemcode=? GROUP BY timehour) b) RIGHT JOIN (SELECT x FROM cnt LIMIT 17 OFFSET 6) c ON b.timehour = c.x ORDER BY c.x"
		);
		$stmtm = $stmtm_sql->execute([$itemcode, $itemcode]);
		$past_datam = $stmtm_sql->fetchAll();
		$dbhm->commit();
		return $past_datam;
	}
	function getsalesbyday($itemcode)
	{
		$dbhm = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
		$t = $dbhm->beginTransaction();
		$stmtm_sql = $dbhm->prepare(
			"SELECT a.x AS daydate_full, ifnull(b.sq, 0) AS quantity, b.sq as rawsq, sum(b.sq/15) FILTER (WHERE b.sq IS NOT null) OVER (ORDER BY a.x ROWS BETWEEN 14 PRECEDING AND CURRENT ROW) as da15, sum(b.sq/60) FILTER (WHERE b.sq IS NOT null) OVER (ORDER BY a.x ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) as da60, * FROM dates a LEFT JOIN (SELECT sum(quantity) AS sq, daydate FROM hourly WHERE itemcode=? GROUP BY daydate) b ON a.x=b.daydate WHERE a.x < date('now') AND a.x > (SELECT min(daydate) FROM hourly WHERE itemcode=?) AND a.x < (SELECT * FROM last_imported) ORDER BY a.x"
		);
		$stmtm = $stmtm_sql->execute([$itemcode, $itemcode]);
		$past_datam = $stmtm_sql->fetchAll();
		$dbhm->commit();
		return $past_datam;
	}
	function lastimportedday()
	{
		$dbhm = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
		$t = $dbhm->beginTransaction();
		$stmtm_sql = $dbhm->prepare(
			"SELECT max(daydate) AS lastupdated FROM hourly"
		);
		$stmtm = $stmtm_sql->execute([]);
		$past_datam = $stmtm_sql->fetchAll();
		$dbhm->commit();
		return $past_datam;
	}
	//var_dump($last);
	$id = $response->PLU_CODE;
	$salesdatabyhour = getsalesbyhour($id);
	$salesdatabyday = getsalesbyday($id);
	$salesdatalastimported = lastimportedday();

	$daily_data_only = array_map(fn($x) => $x["quantity"], $salesdatabyday);

	$desspec = [
		0 => ["pipe", "r"],
		1 => ["pipe", "w"],
		2 => ["pipe", "r"],
	];
	$cwd = "/tmp";
	//var_dump(json_encode($daily_data_only));

	$process = proc_open("python3 analysis.py", $desspec, $pipes);
	if (is_resource($process)) {
		fwrite($pipes[0], json_encode($daily_data_only));
		fclose($pipes[0]);
		$spectrum = trim(stream_get_contents($pipes[1]));
		//var_dump(stream_get_contents($pipes[2]));
		fclose($pipes[1]);
		proc_close($process);
	} else {
		echo "Process creation failed.";
	}
	?>
<script>"use strict"; var past_data = JSON.parse('<?php echo json_encode(
	$past_data
); ?>')</script>
<script>"use strict"; var data_cost = JSON.parse('<?php echo json_encode(
	$data_cost
); ?>')</script>
<script>"use strict"; var sales_data_by_hour = JSON.parse('<?php echo json_encode(
	$salesdatabyhour
); ?>')</script>
<script>"use strict"; var sales_data_by_day = JSON.parse('<?php echo json_encode(
	$salesdatabyday
); ?>')</script>
<script>"use strict"; var sales_data_updated = JSON.parse('<?php echo json_encode(
	$salesdatalastimported
); ?>')</script>
<script>"use strict"; var sales_data_by_day_spectrum = JSON.parse('<?php echo $spectrum; ?>')
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
	<title>DETAILS: <?php echo $data_cost_grn[0]["desc"]; ?></title>
<script>
"use strict";
var avgSales15 = [];
var avgSales30 = [];
var avgSales60 = [];
var avgrun = [];
var avgCost = [];
var avgSell = [];
var SIH = [];
var SIH_dates = [];
var deltaSales15 = [];
var deltaSales30 = [];
var deltaSales60 = [];
var sih_beginning = '2024-06-01';
var dates=[];
var sales_hours = sales_data_by_hour.map(x => x['timehour']);
var sales_hours_data = sales_data_by_hour.map(x => x['psh']);
for (var i=0; i<past_data.length-1; i++){avgSales15.push(past_data[i]['s15']/15)}
for (var i=0; i<past_data.length-1; i++){avgSales30.push(past_data[i]['s30']/30)}
for (var i=0; i<past_data.length-1; i++){avgSales60.push(past_data[i]['s60']/60)}
for (var i=0; i<past_data.length-1; i++){avgrun.push(past_data[i]['avgrun'])}
for (var i=0; i<past_data.length-1; i++){avgCost.push(past_data[i]['avgcost'] ?? 0)}
for (var i=0; i<past_data.length-1; i++){avgSell.push(past_data[i]['avgsell'] ?? 0)}
for (var i=0; i<past_data.length-1; i++){past_data[i]['date'] > sih_beginning ? SIH.push(past_data[i]['SIH']) : null}
for (var i=0; i<past_data.length-1; i++){past_data[i]['date'] > sih_beginning ? SIH_dates.push(past_data[i]['date']): 0}
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
		Chart.defaults.color='#112';
	var daily_sales = new Chart(document.getElementById('chart_sales'),
{
	type: 'line',
	data: {
		labels: dates,
		datasets: [{label: "Daily Average Sales (15d Average)", data: avgSales15, tenstion: 0.8, cubicInterpolationMode: 'monotone', borderWidth: 1, order: 11},
			{label: "Daily Average Sales (30d Average)", data: avgSales30, tension: 0.4, cubicInterpolationMode: 'monotone', borderWidth: 1, order: 12},
			{label: "Daily Average Sales (60d Average)", data: avgSales60, tension: 0.4, cubicInterpolationMode: 'monotone', borderWidth: 1, order: 13},
			{label: "Average since starting", data: avgrun, tension: 1, cubicInterpolationMode: 'monotone', borderWidth: 0.5, order: 20},
			]
	},
			options: {scales: {y: {beginAtZero: true, grid: {color: "#449944"}, y1: {beginAtZero: true}, animation: false}, 
		y1: {type: 'linear', display: false, position: 'right', grid: {drawOnChartArea: false}, beginAtZero: true},
				x: {grid: {color: "#077"}}},
			responsive: false
		}
}
)
	var daily_sales = new Chart(document.getElementById('chart_prices'),
{
	type: 'line',
	data: {
		labels: dates,
		datasets: [
			{label: "Closest past average cost [360days+ only]", data: avgCost, tension: 0.1, cubicInterpolationMode: 'monotone', yAxisID: 'y', pointRadius: 0.5, borderWidth: 2},
			{label: "Closest past average selling price [360d+ only]", data: avgSell, tension: 0.1, cubicInterpolationMode: 'monotone', yAxisID: 'y', pointRadius: 0.5, borderWidth: 2}]
	},
			options: {scales: {y: {beginAtZero: true, grid: {color: "#449944"}, y1: {beginAtZero: true}, animation: false}, 
		y1: {type: 'linear', display: false, position: 'right', grid: {drawOnChartArea: false}, beginAtZero: true},
				x: {grid: {color: "#077"}}},
			responsive: false
		}
}
)
	var daily_SIH = new Chart(document.getElementById('chart_sih'),
{
	type: 'line',
	data: {
		labels: SIH_dates,
		datasets: [{label: "SIH", data: SIH, tenstion: 0.8, cubicInterpolationMode: 'monotone', borderColor: "#000", backgroundColor: "#000", pointRadius: 0.5, borderRadius: 1}]
	},
			options: {scales: {y: {beginAtZero: true, grid: {color: "#000"}, ticks: {color: "#000"}, animation: false}, 
				x: {grid: {color: "#000"}, ticks: {color: "#000"}}},
			responsive: false
		}
}
)
	var sales_by_hour = new Chart(document.getElementById('sales_by_hour'),
{
	type: 'bar',
			color: "#fff",
	data: {
		labels: sales_hours,
		color: "#fff",
		datasets: [{label: "Sales %", data: sales_hours_data, tenstion: 0.8, cubicInterpolationMode: 'monotone', borderColor: "#eee", backgroundColor: "#ff9", color: "#fff", background: "linear-gradient(to right, rgb(30, 77, 125) 0%, rgb(19, 108, 132) 27%, rgb(140, 154, 81) 47%, rgb(125, 136, 102) 61%, rgb(26, 103, 141) 73%, rgb(128, 60, 60) 99%);;"}]
	},
		options: {scales: {y: {beginAtZero: true, max: 100, grid: {color: "#ccc"}, ticks: {color: "#ccc"}}, 
				x: {grid: {color: "#ccc"}, ticks: {color: "#ccc"}}},
			responsive: false
		}
}
)
		document.getElementById("sells_every").innerHTML = `${(1/avgrun[avgrun.length-1]).toFixed(2)} workday(s)`;
}
</script>
</head>
<body class="cached-details">
<table class="named">
<tr>
<td colspan=2 style="color: #008500; width: 20em;">This page loads cached data. You pressed the (O) or v2 link. You might want to go back to go to an up-to-date version.</td>
</tr>
<tr>
<th>Name</th>
<td><?php echo $data_cost_grn[0]["desc"]; ?></td>
</tr>
<tr>
<th>Code</th>
<td><?php echo $response->PLU_CODE; ?></td>
</tr>
<tr>
<th>Sold price</th>
<td><?php echo $last["avgsell"]; ?></td>
</tr>
<tr class="<?php echo $state_of_things; ?>">
<th>SIH</th>
<!--<td><?php echo $response->SIH; ?></td>-->
<td><?php echo $data_cost_grn[0]["sih"]; ?></td>
</tr>
<tr>
<th>Fill sold (15 days)</th>
<td><?php echo $last["s15"] - $last["SIH"]; ?></td>
</tr>
<tr>
<th>Fill sold (30 days)</th>
<td><?php echo $last["s30"] - $last["SIH"]; ?></td>
</tr>
<tr>
<th>Fill sold (60 days)</th>
<td><?php echo $last["s60"] - $last["SIH"]; ?></td>
</tr>
<tr>
<th>Cost<sup> (<?php echo $data_cost[0][
	"daydate"
]; ?>)</sup><sup style="font-size: 0.25em">Average of entries</sup></th>
<td><?php echo number_format(
	$data_cost[0]["cost"],
	2
); ?><sup> gross <?php echo number_format(
	(100 * $last["avgsell"]) / $data_cost[0]["cost"] - 100,
	2
); ?>%</sup></td>
</tr>
<tr>
<th>Cost<sup> (<?php echo substr(
	$data_cost_grn[0]["date"],
	0,
	10
); ?>)</sup><sup style="font-size: 0.25em">GRN (<?php echo $data_cost_grn[0][
	"runno"
]; ?>)</sup></th>
<td><?php echo number_format(
	$data_cost_grn[0]["cost"],
	2
); ?><sup> gross <?php echo number_format(
	(100 * $last["avgsell"]) / $data_cost_grn[0]["cost"] - 100,
	2
); ?>%</sup></td>
</tr>
<tr>
<td>Sells every:</td>
<td>
<span id="sells_every"></span>
</td>
</tr>
<tr>
<td>Vendors</td>
<td>
<table class="inner-table">
<tr>
<th>Vendor</th>
<th>Cost</th>
</tr>
<?php foreach($data_cost_supl as $vendor){?>
<tr>
<td><a href="ql_sup.php?vendorcode=<?php echo $vendor["vendorcode"] ?>"><?php echo $vendor["vendorname"] ?></a></td>
<td class="numeric-data"><?php echo number_format($vendor["cost"], 2) ?></td>
</tr>
<?php } ?>
</table>
</td>
</tr>
</table>
<details>
<summary><button class="btn goback" > <img  src="icons/down_button.png" style= "height:2em; width:2em;"> </button></summary>
<summary><button class="btn goback" > <img  src="icons/down_button.png" style= "height:2em; width:2em;"> </button></summary>
<table class="others">
<tr>
<th scope="col">Field</th>
<th scope="col">Value</th>
</tr>
<?php foreach ($response as $field => $value) { ?>
<tr>
<td><?php echo $field; ?></td>
<td><?php echo $value; ?></td>
</tr>
<?php } ?>
<?php foreach ($last as $field => $value) { ?>
<tr>
<td><?php echo $field; ?></td>
<td><?php echo $value; ?></td>
</tr>
<?php } ?>
</table>
<?php
echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
echo "<pre>" .
	json_encode(
		$last,

		JSON_PRETTY_PRINT
	) .
	"</pre>";

} elseif ($response == null) { ?>
	<h1 style="font-family: Monospace; text-align: center; color: darkgoldenrod; font-size: 5em; border: 0.2em double goldenrod; margin: 2em;">Server down or could not be reached.</h1>
<?php } else { ?>
	<h1 style="font-family: Monospace; text-align: center; color: darkred; font-size: 5em; border: 0.2em double red; margin: 2em;">Does not exist. <?php echo $response->Message; ?></h1>
<?php }
?>
</details>
<div class="centered-container">
<div style="background: linear-gradient(180deg, lightseagreen, red);">
<!--<span style="mix-blend-mode: color-burn; color: white; background: black">Hello! Chart area...</span>-->
<div style="background: white; mix-blend-mode: lighten;">
<canvas id="chart_sih" style="" width="795" height="650"></canvas>
</div>
</div>
</div>
<br />
<div class="centered-container">
<canvas id="chart_sales" width="795" height="650"></canvas>
</div>
<br />
<div class="centered-container">
<canvas id="chart_prices" width="795" height="650"></canvas>
</div>
<br />
<div class="centered-container">
<canvas id="sales_by_hour" width="795" height="650"></canvas>
</div>
<div id ="bottom"> <button onclick="goback()" class="btn goback" > <img  src="icons/back_button.png" style="height:55%; width:55%;"> </button>
<a href="<?php echo "moredetails.php?id=$ID"; ?>"><button class="moredetails"> <img  src="icons/clock.svg" style="filter: grayscale(100%) opacity(50%);height: max(6vh, 6vw); width:max(6vh, 6vw);;"></button></a>
<button class="graph" onclick="(function(){let e = document.getElementById('chart_sales'); e.style.visibility='visible'; let f = document.getElementById('chart_prices'); f.style.visibility='visible'; let g = document.getElementById('sales_by_hour'); g.style.visibility='visible'; e.scrollIntoView({behavior: 'smooth'});})()"> <img  src="icons/graph.png" style="height: 55%; width:55%; left:33%;"> </button> </div>
</body>
</html>
