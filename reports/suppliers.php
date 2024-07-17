<html>
<head>
<link rel="stylesheet" type="text/css" href="../style.css">
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
var data_with_analytics=[];
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
	if(input.length>=0){
		Blocked = true;
		if(regex){
			console.log(r = new RegExp(input));
			json_p.filter();
		}
		else{
			let ctr=0;
			il = input.toLowerCase();
			if(starts_with){
				json_filtered = json_pl.filter(a => (a.vendorname.startsWith(il)));
			}
			else if(abjad){
				iln = normalize(il);
				if (iln < 3) {Blocked=false; return;}
				json_filtered = json_pa.filter(a => a.vendorname.includes(iln));
			}else{
				json_filtered = json_pl.filter(a => a.vendorname.includes(il));
			}
			not_in_cache = json_filtered.filter(a => {return !DataCache.has(a.vendorcode)});
			in_cache = json_filtered.filter(a => {return true; !(!DataCache.has(a.vendorcode) )});
			console.log(not_in_cache);
			console.log(in_cache);
			var dump=json_filtered;
			json_data = "[]";//await fetch_data(not_in_cache);
			json_analytics = "[]"; //await fetch_analytics(not_in_cache);
			var analytics_fetched = JSON.parse(json_analytics);
			analytics=Array.from(analytics_fetched);
			in_cache.forEach(x => analytics.push(AnalyticsCache.get(x.vendorcode)));
			var data_fetched = JSON.parse(json_data);
			data=Array.from(data_fetched);
			in_cache.forEach(x => data.push(DataCache.get(x.vendorcode)));
			//var data_with_analytics=[];
			data_with_analytics=[];
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
				if (datacount < 1000){
				try{
					var dwa = Object.create({});
					Object.assign(dwa, DataCache.get(data[i1].vendorcode));
					data_with_analytics.push(dwa);
					datacount++;
				}catch(e){}
				}else{break;}
			}
			//console.log(analytics);
			data_with_analytics.sort((a,b) => {var cmp = (a.SIH/a.S_D60) - (b.SIH/b.S_D60); if(!isNaN(cmp)) return cmp; else if(isNaN(a.SIH/a.S_D60)) return 1; else return -1;});
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
	heading_row.appendChild(generate_data_heading("Name"));
	heading_row_bottom = heading_row.cloneNode(true);
	heading_row.className += " theader";
	heading_row_bottom.className += " theader_b";
	table.appendChild(heading_row);
	filtered.forEach((v) => {table.appendChild(generate_table_row(v));});
	table.appendChild(heading_row_bottom);
	printer.replaceChildren(table);
}
function generate_table_row(v){
	row = document.createElement("tr");
	//console.log(v);
	if(v){
		row.appendChild(generate_code_data_element(v.vendorcode));
		row.appendChild(generate_link_element("../ql_sup.php?vendorcode="+v.vendorcode, v.vendorname));
		
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
	de.className += " numeric-data";
	return de;
}
function generate_code_data_element(text){
	var de = document.createElement('td');
	de.innerText = Number.parseInt(text);
	de.className += " numeric-data";
	return de;
}
function generate_stock_data_element(text){
	var de = document.createElement('td');
	if(!(text == "NaN") && !isNaN(text)){
	de.innerText = text != Infinity ? Number.parseFloat(text).toFixed(1) : "I";
	de.className += " numeric-data";
	de.className += " stock-data";
	}
	else {de.innerText="E";}
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
	json_pa=json_p.map(v => {var copy = Object.assign({}, v); copy.vendorname = normalize(copy.vendorname); return copy});
	json_pl=json_p.map(v => {var copy = Object.assign({}, v); copy.vendorname = (copy.vendorname.toLowerCase()); return copy});
	Clock = window.setInterval(displaySelected, 250);
	ToDisplay="";
	Displaying="dummy";
	updateSelected();
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
ini_set("display_errors", "1");
//error_reporting(E_ALL);
require_once "/etc/auth.php";
$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t_q1_start = microtime(true);
$dbh->query("pragma mmap_size=2000000000");
$t = $dbh->beginTransaction();
$stmt_sql = $dbh->prepare(
		"
SELECT vendorname, vendorcode
FROM vendors
"
);
$stmt = $stmt_sql->execute();
$cur_data = $stmt_sql->fetchAll();
$dbh->commit();
$response = $cur_data;
$response_dec = $response; // json_decode($response);
$t_q1_end = microtime(true);
$t_q2_start = microtime(true);
$tlat = $dbh->beginTransaction();
$stmt_sql_tlat = $dbh->prepare(
	"SELECT max(daydate) AS last_updated FROM hourly"
);
$stmt_tlat = $stmt_sql_tlat->execute();
$lastu = $stmt_sql_tlat->fetchAll();
$dbh->commit();
$t_q2_end = microtime(true);
echo "<script>var list = " .
	json_encode(json_encode($response)) .
	"; list_p = JSON.parse((list));</script>";

//var_dump($response);
?>
	<title>DETAILS: <?php echo $lastu[0][0] ?></title>
<script>
list_p.forEach((v) => {DataCache.set(v.vendorcode, v)});
</script>
</head>
<body class="cached">
<button onclick="goback()" class="navigation-button" style="position: fixed; bottom: 0; left: 0; font-size: 4vh; z-index: 2;" id="back">🔙 Go back!</button><br />
<div class="centered-container">
<span class="notice">Data loaded on (please check today's date): <?php echo $lastu[0][0]; ?></span> <br />
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
 /*foreach($response as $field=>$value) { ?>
<tr>
<td><?php echo $field; ?></td>
<td><?php var_dump($value); ?></td>
</tr>
<?php /*}*/ ?>
</table>
<?php
//echo "<pre>".json_encode($response, JSON_PRETTY_PRINT)."</pre>";


$t_q1_diff_ms = ($t_q1_end - $t_q1_start) * 1000;
$t_q2_diff_ms = ($t_q2_end - $t_q2_start) * 1000;
echo "Took $t_q1_diff_ms milliseconds for query 1, $t_q2_diff_ms for query 2";
?>
</details>
</body>
</html>
