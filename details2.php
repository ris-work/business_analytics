<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<script>
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
var_dump($response_analytics);
$dbh = new PDO("sqlite:/saru/www-data/db.sqlite3");
$t = $dbh->beginTransaction();
$stmt_sql = $dbh->prepare("SELECT ID, avg(SIH), substring(TIME, 0, 12) FROM productsattime WHERE ID = ? GROUP BY ID, substring(TIME, 0, 12) ORDER BY substring(TIME, 0, 12)");
$stmt = $stmt_sql->execute([$response->PLU_CODE]);
$past_data=$stmt_sql->fetchAll();
$dbh->commit();
?>
<script>var past_data = JSON.parse('<?php echo json_encode($past_data); ?>')</script>
	<title>DETAILS: <?php echo $response->PLU_DESC; ?></title>
<script>
var deltaSales15 = [];
for (var i in past_data){console.log(past_data[i])}
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
</body>
</html>
