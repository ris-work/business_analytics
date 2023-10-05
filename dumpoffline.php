<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<script>
dump="";
var json_p="";
var DataCache = new Map();
var AnalyticsCache = new Map();
var ToDisplay = "";
var Displaying = "";
var Clock = null;
function updateSelected(e){
	ToDisplay=e.target.value;
}
function goBack(){window.location.assign("/scan/")}
var Blocked=false;
async function displaySelected(){
	if(Displaying==ToDisplay) return;
	if(Blocked) return;
	var regex=false;
	//console.log(e.target.value);
	var input = ToDisplay;
	if(input.length>=3){
		Blocked = true;
		if(regex){
			console.log(r = new RegExp(input));
			json_p.filter();
		}
		else{
			il = input.toLowerCase();
			if(starts_with){
				json_filtered = json_pl.filter(a => a.PLU_DESC.startsWith(il));
			}
			else if(abjad){
				iln = normalize(il);
				if (iln < 3) {Blocked=false; return;}
				json_filtered = json_pa.filter(a => a.PLU_DESC.includes(iln));
			}else{
				json_filtered = json_pl.filter(a => a.PLU_DESC.includes(il));
			}
			not_in_cache = json_filtered.filter(a => {return !DataCache.has(a.PLU_CODE) || !AnalyticsCache.has(a.PLU_CODE)});
			in_cache = json_filtered.filter(a => {return !(!DataCache.has(a.PLU_CODE) || !AnalyticsCache.has(a.PLU_CODE))});
			console.log(not_in_cache);
			console.log(in_cache);
			var dump=json_filtered;
			json_data = await fetch_data(not_in_cache);
			json_analytics = await fetch_analytics(not_in_cache);
			var analytics_fetched = JSON.parse(json_analytics);
			analytics=Array.from(analytics_fetched);
			in_cache.forEach(x => analytics.push(AnalyticsCache.get(x.PLU_CODE)));
			var data_fetched = JSON.parse(json_data);
			data=Array.from(data_fetched);
			in_cache.forEach(x => data.push(DataCache.get(x.PLU_CODE)));
			var data_with_analytics=[];
			//console.log(data);
			for(var i1 in analytics_fetched){
				try{
					AnalyticsCache.set(analytics[i1].CODE, analytics[i1]);
				}catch(e){}
			}
			for(var i1 in data_fetched){
				try{
					DataCache.set(data[i1].PLU_CODE, data[i1]);
				}catch(e){}
			}
			for(var i1 in data){
				try{
					var dwa = Object.create({});
					Object.assign(dwa, DataCache.get(data[i1].PLU_CODE));
					Object.assign(dwa, AnalyticsCache.get(data[i1].PLU_CODE));
					data_with_analytics.push(dwa);
				}catch(e){}
			}
			//console.log(analytics);
			console.log(data_with_analytics);
		}
		pretty_print_filtered((data_with_analytics));
		Blocked=false;
		Displaying=ToDisplay;
	}
}
async function loadAllAtOnce(){
	var not_in_cache = json_pl;
	//console.log(data);
	json_data = await fetch_data(not_in_cache);
	json_analytics = await fetch_analytics(not_in_cache);
	var analytics_fetched = JSON.parse(json_analytics);
	var data_fetched = JSON.parse(json_data);
	var data_with_analytics=[];
	console.log(data_fetched);
	//console.log(data);
	for(var i1 in analytics_fetched){
		try{
			AnalyticsCache.set(analytics[i1].CODE, analytics[i1]);
		}catch(e){}
	}
	for(var i1 in data_fetched){
		try{
			DataCache.set(data[i1].PLU_CODE, data[i1]);
		}catch(e){}
	}
	//console.log(analytics);
	console.log(DataCache);
}
async function fetch_data(entries){
	data = new FormData();
	ids = [];
	entries.forEach((v) => ids.push(v.PLU_CODE));
	data.append('ids', JSON.stringify(ids));
	dump=ids;
	res = await fetch("agg_get_info.php",
	{method: "POST", body: data});
	const buf=await res.arrayBuffer();
	return (new TextDecoder).decode(buf);
};
async function fetch_analytics(entries){
	data = new FormData();
	ids = [];
	entries.forEach((v) => ids.push(v.PLU_CODE));
	data.append('ids', JSON.stringify(ids));
	dump=ids;
	res = await fetch("agg_get_salesdata.php",
	{method: "POST", body: data});
	const buf=await res.arrayBuffer();
	return (new TextDecoder).decode(buf);
};
function pretty_print_filtered(filtered){
	document.getElementById("printer");
	var table = document.createElement("table");
	table.style.marginLeft="auto";
	table.style.marginRight="auto";
	table.style.borderCollapse="collapse";
	heading_row = document.createElement("tr");
	heading_row.appendChild(generate_data_heading("Code"));
	heading_row.appendChild(generate_data_heading("Description"));
	heading_row.appendChild(generate_data_heading("Sell"));
	heading_row.appendChild(generate_data_heading("SIH"));
	heading_row.appendChild(generate_data_heading("Sales (15)"));
	heading_row.appendChild(generate_data_heading("Sales (30)"));
	heading_row.appendChild(generate_data_heading("Sales (60)"));
	table.appendChild(heading_row);
	filtered.forEach((v) => {table.appendChild(generate_table_row(v));});
	printer.replaceChildren(table);
}
function generate_table_row(v){
	row = document.createElement("tr");
	//console.log(v);
	if(v){
		row.appendChild(generate_data_element(v.PLU_CODE));
		row.appendChild(generate_data_element(v.PLU_DESC));
		row.appendChild(generate_data_element(v.PLU_SELL));
		row.appendChild(generate_data_element(v.SIH));
		row.appendChild(generate_data_element(v.S_D15));
		row.appendChild(generate_data_element(v.S_D30));
		row.appendChild(generate_data_element(v.S_D60));
		if(!v.PLU_ACTIVE) {row.style.backgroundColor="darkcyan"};
		if(parseInt(v.SIH)==0 && parseInt(v.S_D15)==0 && parseInt(v.S_D30)==0 && parseInt(v.S_D60)==0) row.className = "always-empty";
		if(parseInt(v.SIH) < parseInt(v.S_D15)){
		}
	}
	return row;
}
function generate_data_element(text){
	var de = document.createElement('td');
	de.innerText = text;
	return de;
}
function generate_data_heading(text){
	var de = document.createElement('th');
	de.innerText = text;
	de.style.border="1px solid black";
	return de;
}
var abjad = false;
var starts_with=false;
function updateOptions(){
	starts_with = document.getElementById("starts-with").checked;
	abjad = document.getElementById("abjad").checked;
}
var json_p, json_pa;
function loaded(){
	document.getElementById("back").addEventListener("click", goBack)
		document.getElementById("search").addEventListener("input", updateSelected)
		document.getElementById("abjad").addEventListener("change", updateOptions)
		document.getElementById("starts-with").addEventListener("change", updateOptions)
		json_p=list_p;
	json_pa=json_p.map(v => {var copy = Object.assign({}, v); copy.PLU_DESC = normalize(copy.PLU_DESC); return copy});
	json_pl=json_p.map(v => {var copy = Object.assign({}, v); copy.PLU_DESC = (copy.PLU_DESC.toLowerCase()); return copy});
	Clock = window.setInterval(displaySelected, 400);
}
function normalize(string) {
	string = string.toLowerCase()
		var vowels=/[aeiou]/g
		string = string.replaceAll(vowels, "");
	string = string.replaceAll("y", "i");
	string = string.replaceAll("k", "c");
	return string;
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
<details>
<summary>More options...</summary>
<fieldset id="search-options">
<legend>Search options:</legend>
<div>
<input type="checkbox" id="abjad"></input>
<label for="abjad">Abjad and other normalizations</label>
</div>
<br />
<div>
<input type="checkbox" id="starts-with"></input>
<label for="starts-with">Starts with ... (disables abjad)</label>
</div>
</fieldset>
</details>
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
//echo "<pre>".json_encode($response, JSON_PRETTY_PRINT)."</pre>";
?>
</details>
</body>
</html>
