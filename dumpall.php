<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<script>
dump="";
var json_p="";
function goBack(){window.location.assign("/scan/")}
function displaySelected(e){
	var regex=false;
	console.log(e.target.value);
	input = e.target.value;
	if(input.length>=3){
		if(regex){
		console.log(r = new RegExp(input));
		json_p.filter();
		}
		else{
			json_filtered = json_p.filter(a => a.PLU_DESC.toLowerCase().includes(input.toLowerCase()));
			dump=json_filtered;
		}
		pretty_print_filtered(json_filtered);
	}
}
function pretty_print_filtered(filtered){
	document.getElementById("printer");
	var table = document.createElement("table");
	filtered.forEach((v) => {table.appendChild(generate_table_row(v));});
	printer.replaceChildren(table);
}
function generate_table_row(v){
	row = document.createElement("tr");
	row.appendChild(generate_data_element(v.PLU_DESC));
	row.appendChild(generate_data_element(v.PLU_CODE));
	row.appendChild(generate_data_element(v.PLU_SELL));
	row.appendChild(generate_data_element(v.SIH));
	return row;
}
function generate_data_element(text){
	var de = document.createElement('td');
	de.innerText = text;
	return de;
}
function loaded(){
document.getElementById("back").addEventListener("click", goBack)
document.getElementById("search").addEventListener("input", displaySelected)
json_p=list_p;
}
window.onload=loaded;
</script>
<?php
//ini_set('display_errors', '1');
//error_reporting(E_ALL);
require_once("/etc/auth.php");
$ID = "";
$BASEURL = "http://127.0.0.1:9090/api/Items2";
$req = curl_init();
curl_setopt($req, CURLOPT_URL, "$BASEURL");
curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
curl_setopt($req, CURLOPT_HTTPHEADER, ["Authorization: Basic $ENCODED_AUTH"]);
$response = curl_exec($req);
$response_dec = json_decode($response);
echo "<script>var list = ".json_encode($response)."; list_p = JSON.parse((list));</script>";
//var_dump($response);
?>
	<title>DETAILS: <?php echo $response->PLU_DESC; ?></title>
</head>
<body>
<button onclick="goback()" class="navigation-button" id="back">🔙 Go back!</button><br />
<input type="text" placeholder="Search (enter at least 3 letters)... 🔍" id="search" /><br />
<div id="printer"></div>
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
<td><?php var_dump($value); ?></td>
</tr>
<?php } ?>
</table>
<?php
echo "<pre>".json_encode($response, JSON_PRETTY_PRINT)."</pre>";
?>
</details>
</body>
</html>
