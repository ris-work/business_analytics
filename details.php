<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<script>
function goback(){window.location.assign("/scan/")}
</script>
<?php
//ini_set('display_errors', '1');
//error_reporting(E_ALL);
require_once("/etc/auth.php");
$ID = $_GET["id"];
$BASEURL = "http://127.0.0.1:9090/api/Items2/";
$req = curl_init();
curl_setopt($req, CURLOPT_URL, "$BASEURL/$ID");
curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
curl_setopt($req, CURLOPT_HTTPHEADER, ["Authorization: Basic $ENCODED_AUTH"]);
$response = json_decode(curl_exec($req));
//var_dump($response);
?>
	<title>DETAILS: <?php echo $response->PLU_DESC; ?></title>
</head>
<body>
<button onclick="goback()">🔙 Go back!</button>
<table class="named">
<tr>
<td>Name</td>
<td><?php echo $response->PLU_DESC; ?></td>
</tr>
<tr>
<td>Code</td>
<td><?php echo $response->PLU_CODE; ?></td>
</tr>
<tr>
<td>Selling price</td>
<td><?php echo $response->PLU_SELL; ?></td>
</tr>
<tr>
<td>SIH</td>
<td><?php echo $response->SIH; ?></td>
</tr>
</table>
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
</table>
<?php
echo "<pre>".json_encode($response, JSON_PRETTY_PRINT)."</pre>";
?>
</body>
</html>
