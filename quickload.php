<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="icon" type="image/svg+xml" href="/srm-icons/broken.svg">
		<link rel="apple-touch-icon" size="180x180" type="image/png" href="/srm-icons/broken_180.png" />
		<link rel="apple-touch-icon" size="120x120" type="image/png" href="/srm-icons/broken_120.png" />
		<link rel="apple-touch-icon" size="152x152" type="image/png" href="/srm-icons/broken_152.png" />
		<link rel="apple-touch-icon" size="any" type="image/svg+xml" href="/srm-icons/broken.svg" />
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
			json_data = "[]";//await fetch_data(not_in_cache);
			json_analytics = "[]"; //await fetch_analytics(not_in_cache);
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
			var datacount=0;
			for(var i1 in data){
				if (datacount < 100){
				try{
					var dwa = Object.create({});
					Object.assign(dwa, DataCache.get(data[i1].PLU_CODE));
					Object.assign(dwa, AnalyticsCache.get(data[i1].PLU_CODE));
					data_with_analytics.push(dwa);
					datacount++;
				}catch(e){}
				}else{break;}
			}
			//console.log(analytics);
			console.log(data_with_analytics);
		}
		pretty_print_filtered((data_with_analytics));
		Blocked=false;
		Displaying=input;
	}
}
async function fetch_data(entries){
	if(entries.length == 0) return JSON.stringify([]);
	data = new FormData();
	ids = [];
	entries.forEach((v) => ids.push(v.PLU_CODE));
	data.append('ids', JSON.stringify(ids));
	dump=ids;
	res = await fetch("ql_info.php",
	{method: "POST", body: data});
	const buf=await res.arrayBuffer();
	return (new TextDecoder).decode(buf);
};
async function fetch_analytics(entries){
	if(entries.length == 0) return JSON.stringify([]);
	data = new FormData();
	ids = [];
	entries.forEach((v) => ids.push(v.PLU_CODE));
	data.append('ids', JSON.stringify(ids));
	dump=ids;
	res = await fetch("ql_get_salesdata.php",
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
	heading_row.appendChild(generate_data_heading("Cost"));
	heading_row.appendChild(generate_data_heading("SIH"));
	heading_row.appendChild(generate_data_heading("Fill sold (15)"));
	heading_row.appendChild(generate_data_heading("Fill sold (30)"));
	heading_row.appendChild(generate_data_heading("Fill sold (60)"));
	heading_row.appendChild(generate_data_heading("MORE"));
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
		row.appendChild(generate_numeric_data_element(v.PLU_SELL));
		row.appendChild(generate_numeric_data_element(v.cost));
		row.appendChild(generate_numeric_data_element(v.SIH));
		row.appendChild(generate_numeric_data_element((v.S_D15-v.SIH) < 0 ? 0 : v.S_D15 - v.SIH));
		row.appendChild(generate_numeric_data_element((v.S_D30-v.SIH) < 0 ? 0 : v.S_D30 - v.SIH));
		row.appendChild(generate_numeric_data_element((v.S_D60-v.SIH) <0 ? 0 : v.S_D60 - v.SIH));
		row.appendChild(generate_link_element(`details.php?id=${v.PLU_CODE}`, "More"));
		//if(!v.PLU_ACTIVE) {row.style.backgroundColor="darkcyan"};
		if(parseInt(v.SIH)==0 && parseInt(v.S_D15)==0 && parseInt(v.S_D30)==0 && parseInt(v.S_D60)==0) row.className = "always-empty";
		if(parseInt(v.SIH) <=parseInt(v.S_D15))row.className="dangerous";
		if(parseInt(v.SIH) <=parseInt((v.S_D15)*7/15))row.className="very-dangerous";
		if(parseInt(v.SIH) >= parseInt(v.S_D15)&&parseInt(v.SIH)<parseInt(v.S_D30))row.className="good";
		if(parseInt(v.SIH) >=parseInt(v.S_D30)&&parseInt(v.SIH)<parseInt(v.S_D60))row.className="very-good";
		if(parseInt(v.SIH) >=parseInt(v.S_D60))row.className="too-much";
		
	}
	return row;
}
function generate_data_element(text){
	var de = document.createElement('td');
	de.innerText = text;
	return de;
}
function generate_numeric_data_element(text){
	var de = document.createElement('td');
	de.innerText = Number.parseFloat(text).toFixed(2);
	de.className += "numeric-data";
	return de;
}
function generate_link_element(href, text){
	var de=document.createElement('td');
	var le = document.createElement('a');
	le.href = href;
	le.innerText = text;
	de.appendChild(le);
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
	Displaying="";
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
	Clock = window.setInterval(displaySelected, 250);
}
function normalize(string) {
	string = string.toLowerCase()
		var vowels=/[aeiou]/g
		string = string.replaceAll(vowels, "");
	string = string.replaceAll("y", "i");
	string = string.replaceAll("k", "c");
	string = string.replaceAll("pc", "c");
	string = string.replaceAll(" ", "");
	return string;
}
window.onload=loaded;
</script>
<?php
ini_set('display_errors', '1');
//error_reporting(E_ALL);
require_once("/etc/auth.php");
$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
//var_dump($IDs);
//$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t = $dbh->beginTransaction();
$stmt_sql = $dbh->prepare("SELECT selling.itemcode AS PLU_CODE, desc AS PLU_DESC, sih_current.sell/iif(sih=0,1,sih) AS PLU_SELL_AV, selling.sell AS PLU_SELL, sih_current.sih AS SIH, cost_purchase.cost AS cost FROM sih_current JOIN selling ON selling.itemcode = sih_current.itemcode JOIN cost_purchase ON cost_purchase.itemcode = selling.itemcode ");
$stmt = $stmt_sql->execute();
$cur_data=$stmt_sql->fetchAll();
$dbh->commit();
//var_dump($past_data);
$response = $cur_data;
$response_dec = $response ;// json_decode($response);
$tlat = $dbh->beginTransaction();
$stmt_sql_tlat = $dbh->prepare("SELECT max(daydate) AS last_updated FROM hourly");
$stmt_tlat = $stmt_sql_tlat->execute();
$lastu=$stmt_sql_tlat->fetchAll();
$dbh->commit();
$ta = $dbh->beginTransaction();
$stmta_sql = $dbh->prepare("SELECT ifnull(S_D60, 0) AS S_D60, ifnull(S_D30, 0) AS S_D30, ifnull(S_D15, 0) AS S_D15, everything.itemcode AS CODE FROM everything_itemcode_in_hourly AS everything LEFT JOIN (SELECT total(quantity) as S_D60, itemcode FROM hourly WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-61 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) S60T ON everything.itemcode = S60T.itemcode LEFT JOIN (SELECT total(quantity) AS S_D30, itemcode FROM hourly WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-31 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) S30T ON S30T.itemcode = S60T.itemcode LEFT JOIN (SELECT total(quantity) AS S_D15, itemcode FROM hourly WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-16 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) AS S15T ON S15T.itemcode = S30T.itemcode ");
$stmta = $stmta_sql->execute();
$past_data=$stmta_sql->fetchAll();
$dbh->commit();
echo "<script>var list = ".json_encode(json_encode($response))."; list_p = JSON.parse((list));</script>";
echo "<script>var lista = ".json_encode(json_encode($past_data))."; list_a = JSON.parse((lista));</script>";
//var_dump($response);
?>
	<title>DETAILS: <?php echo $lastu[0][0]; ?></title>
<script>
list_p.forEach((v) => {DataCache.set(v.PLU_CODE, v)});
list_a.forEach((v) => {AnalyticsCache.set(v.CODE, v)});
</script>
</head>
<body class="cached">
<button onclick="goback()" class="navigation-button" style="position: fixed; bottom: 0; left: 0; font-size: 4vh;" id="back">🔙 Go back!</button><br />
<div class="centered-container">
<?php echo ($lastu[0][0]); ?> <br />
<input type="text" placeholder="Search (enter at least 3 letters)... 🔍" id="search" /><br />
</div>
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
