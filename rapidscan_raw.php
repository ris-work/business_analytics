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
var json_exp, ilnseg;
function updateSelected(e){
	ToDisplay=e.target.value;
}
function goBack(){window.location.assign("/scan/")}
var Blocked=false;
var json_filtered = [];
function lookupAndAdd(barcode){
		console.log(`Called to add: ${barcode}`);
		if(barcode && barcode.toString().length < 6 && barcode.toString().length >= 1) return;
		if(barcode){
			var looked_up_from_existing_or_current = json_pa.filter(a => a.PLU_DESC.includes(barcode));
			if(looked_up_from_existing_or_current.length == 0)
					looked_up_from_existing_or_current = [barcode];
			json_filtered.push(...looked_up_from_existing_or_current);
		}
		data = json_filtered.map(x => x).reverse();
		localStorage.setItem("looked_up", JSON.stringify(json_filtered));
		var data_with_analytics=[];
			var datacount=0;
			for(var i1 in data){
				if (datacount < 100){
				try{
					var dwa = Object.create({});
					if(DataCache.get(data[i1].PLU_CODE) != undefined){
						Object.assign(dwa, DataCache.get(data[i1].PLU_CODE));
						Object.assign(dwa, AnalyticsCache.get(data[i1].PLU_CODE));
					}
					else {
						dwa = {
							PLU_CODE: -1,
							PLU_DESC: data[i1],
							SIH: 0,
							S_D15: 0,
							S_D30: 0,
							S_D60: 0,
						};
					}
					data_with_analytics.push(dwa);
					datacount++;
				}catch(e){}
				}else{break;}
			}
			//console.log(analytics);
			console.log(data_with_analytics);
		pretty_print_filtered((data_with_analytics));
}
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
				iln = normalize_keep_spaces(il);
				if (iln < 3) {Blocked=false; return;}
				json_filtered = json_pa;
				ilnseg = iln.split(" ");;
				ilnseg.forEach((v)=>json_filtered = json_filtered.filter(a => a.PLU_DESC.includes(v)));
			}else{
				json_filtered = json_pl.filter(a => a.PLU_DESC.includes(il));
			}
			not_in_cache = json_filtered.filter(a => {return !DataCache.has(a.PLU_CODE) || !AnalyticsCache.has(a.PLU_CODE)});
			in_cache = json_filtered.filter(a => {return true; !(!DataCache.has(a.PLU_CODE) || !AnalyticsCache.has(a.PLU_CODE))});
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
	heading_row.appendChild(generate_data_heading("S"));
	heading_row.appendChild(generate_data_heading_noprint("Code"));
	heading_row.appendChild(generate_data_heading("Description"));
	heading_row.appendChild(generate_data_heading_noprint("Sell"));
	heading_row.appendChild(generate_data_heading_noprint("Cost"));
	heading_row.appendChild(generate_data_heading("SIH"));
	heading_row.appendChild(generate_data_heading("Want"));
	heading_row.appendChild(generate_data_heading_noprint("Sold (15)"));
	heading_row.appendChild(generate_data_heading_noprint("S (30)"));
	heading_row.appendChild(generate_data_heading_noprint("S (60)"));
	heading_row.appendChild(generate_data_heading_noprint("DL"));
	heading_row.appendChild(generate_data_heading_noprint("MORE"));
	heading_row.appendChild(generate_data_heading_noprint("(O)"));
	heading_row_bottom = heading_row.cloneNode(true);
	heading_row.className += " theader";
	heading_row_bottom.className += " theader_b";
	table.appendChild(heading_row);
	filtered.forEach((v,k) => {table.appendChild(generate_table_row(v,k));});
	table.appendChild(heading_row_bottom);
	printer.replaceChildren(table);
}
function generate_table_row(v,k){
	row = document.createElement("tr");
	//console.log(v);
	if(v){
		var seq_no = generate_right_serial_element(k);
		seq_no.id="seq_no";
		row.appendChild(seq_no);
		row.appendChild(generate_right_data_element(v.PLU_CODE));
		row.appendChild(generate_data_element(v.PLU_DESC));
		row.appendChild(generate_numeric_data_element(v.PLU_SELL));
		row.appendChild(generate_numeric_data_element(v.cost));
		row.appendChild(generate_stock_data_element(v.SIH));
		row.appendChild(generate_textbox_element((v.S_D15 - v.SIH) < 0 ? 0 : v.S_D15 - v.SIH));
		row.appendChild(generate_stock_data_element_noprint((v.S_D15) < 0 ? 0 : v.S_D15));
		row.appendChild(generate_stock_data_element_noprint((v.S_D30) < 0 ? 0 : v.S_D30));
		row.appendChild(generate_stock_data_element_noprint((v.S_D60) <0 ? 0 : v.S_D60));
		row.appendChild(generate_stock_data_element_noprint((v.SIH/v.S_D60)*60));
		row.appendChild(generate_link_element(`details.php?id=${v.PLU_CODE.toString().padStart(6, '0')}`, "More"));
		row.appendChild(generate_link_element(`details_v2.php?id=${v.PLU_CODE}`, "(O)"));
		row.appendChild(generate_button_close_element());
		//if(!v.PLU_ACTIVE) {row.style.backgroundColor="darkcyan"};
		if(parseInt(v.SIH)==0 && parseInt(v.S_D15)==0 && parseInt(v.S_D30)==0 && parseInt(v.S_D60)==0) row.className = "always-empty";
		if(parseInt(v.SIH) <=parseInt(v.S_D15))row.className="dangerous";
		if(parseInt(v.SIH) <=parseInt((v.S_D15)*7/15))row.className="very-dangerous";
		if(parseInt(v.SIH) >= parseInt(v.S_D15)&&parseInt(v.SIH)<parseInt(v.S_D30))row.className="good";
		if(parseInt(v.SIH) >=parseInt(v.S_D30)&&parseInt(v.SIH)<parseInt(v.S_D60))row.className="very-good";
		if(parseInt(v.SIH) >=parseInt(v.S_D60))row.className="too-much";
		if(parseInt(v.PLU_CODE) < 0)row.className="not-found";
		
	}
	return row;
}
function generate_textbox_element(text){
	var de = document.createElement('td');
	var tb = document.createElement('input');
	tb.type = "text";
	tb.value = text;
	tb.maxLength = 3;
	tb.style.width = "3.6em";
	tb.style.fontSize = "1.2em";
	tb.style.textAlign = "right";
	tb.style.background = "rgba(255,255,255,0)";
	tb.style.fontFamily = "Courier New, MONOSPACE";
	de.appendChild(tb);
	return de;
}
function generate_data_element(text){
	var de = document.createElement('td');
	de.innerText = text;
	return de;
}
function generate_numeric_data_element(text){
	var de = document.createElement('td');
	de.innerText = Number.parseFloat(text).toFixed(2);
	de.className += " no-print";
	de.className += " numeric-data";
	return de;
}
function generate_right_data_element(text){
	var de = document.createElement('td');
	de.innerText = Number.parseFloat(text).toFixed(0);
	de.className += " numeric-data";
	de.className += " no-print";
	return de;
}
function generate_right_serial_element(text){
	var de = document.createElement('td');
	de.innerText = Number.parseFloat(text).toFixed(0);
	de.className += " numeric-data";
	return de;
}
function generate_stock_data_element(text){
	var de = document.createElement('td');
	if(!(text == "NaN") && !isNaN(text)){
	//de.innerText = Number.parseFloat(text).toFixed(1);
	de.innerText = text != Infinity ? Number.parseFloat(text).toFixed(1) : "I";
	de.className += " numeric-data";
	de.className += " stock-data";
	}
	else {de.innerText="E"}
	return de;
}
function generate_stock_data_element_noprint(text){
	var de = document.createElement('td');
	if(!(text == "NaN") && !isNaN(text)){
	//de.innerText = Number.parseFloat(text).toFixed(1);
	de.innerText = text != Infinity ? Number.parseFloat(text).toFixed(1) : "I";
	de.className += " numeric-data";
	de.className += " stock-data";
	}
	else {de.innerText="E"}
	de.className += " no-print";
	return de;
}
var removal_locked = false;
function remove_current_row(e){
		if (removal_locked == false) {
		//removal_locked = true; we use {once: true} instead
		var seq_no=parseInt(e.target.parentElement.parentElement.querySelectorAll("#seq_no")[0].innerText); 
		json_filtered.splice(seq_no, 1);
		lookupAndAdd();
		removal_locked=false;
		}
}
function generate_button_close_element(){
		var de = document.createElement("td");
		var close_button = document.createElement('button');
		close_button.addEventListener("click", remove_current_row, {once: true});
		close_button.style.fontFamily = "COURIER NEW, Monospace";
		close_button.innerText = "X";
		close_button.style.fontSize="1.2em";
		close_button.style.verticalAlign="center";
		de.className = "no-print";
		de.appendChild(close_button);
		return de;
}
function generate_link_element(href, text){
	var de=document.createElement('td');
	var le = document.createElement('a');
	de.className = "no-print";
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
function generate_data_heading_noprint(text){
	var de = document.createElement('th');
	de.innerText = text;
	de.className = "no-print";
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
		//document.getElementById("search").addEventListener("input", updateSelected)
		document.getElementById("abjad").addEventListener("change", updateOptions)
		document.getElementById("starts-with").addEventListener("change", updateOptions)
		json_p=list_p;
	json_pa=json_p.map(v => {var copy = Object.assign({}, v); copy.PLU_DESC = normalize(copy.possible_barcodes); return copy});
	json_pl=json_p.map(v => {var copy = Object.assign({}, v); copy.PLU_DESC = (copy.PLU_DESC.toLowerCase()); return copy});
	Clock = window.setInterval(displaySelected, 250);
	if(localStorage.getItem("looked_up")){
			json_filtered = JSON.parse(localStorage.getItem("looked_up"));
			lookupAndAdd();
	}
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
function normalize_keep_spaces(string) {
	string = string.toLowerCase()
		var vowels=/[aeiou]/g
		string = string.replaceAll(vowels, "");
	string = string.replaceAll("y", "i");
	string = string.replaceAll("k", "c");
	string = string.replaceAll("pc", "c");
	//string = string.replaceAll(" ", "");
	return string;
}
window.onload=loaded;
</script>
<?php
ini_set("display_errors", "1");
//error_reporting(E_ALL);
require_once "/etc/auth.php";
$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
//var_dump($IDs);
//$dbh = new PDO("sqlite:/saru/www-data/hourly.sqlite3");
$t_q1_start = microtime(true);
$dbh->query("pragma mmap_size=2000000000");
$t = $dbh->beginTransaction();
$stmt_sql = $dbh->prepare(
		"
WITH maxdates AS MATERIALIZED (SELECT max(daydate) AS maxdate, itemcode FROM hourly GROUP BY itemcode),
     sales AS (SELECT itemcode, daydate, sumsell/quantity as asell, sumcost/quantity as acost FROM hourly INDEXED BY hourly_sold_prices),
     latestsell AS MATERIALIZED (SELECT min(asell) AS asell, max(acost) AS acost, maxdates.itemcode AS itemcode
         FROM (sales
         JOIN maxdates
         ON maxdates.itemcode = sales.itemcode AND maxdates.maxdate = sales.daydate)
         GROUP BY sales.itemcode),
	 possible_barcodes AS (SELECT itemcode, group_concat(format('%013d', barcode), ',') AS possible_barcodes 
		 FROM (
			SELECT itemcode AS barcode, itemcode FROM sih_current UNION ALL 
			SELECT barcode, itemcode FROM barcodes WHERE barcode NOT IN (SELECT itemcode FROM sih_current)
		 )
		 GROUP BY itemcode)
SELECT
  sih_current.itemcode AS PLU_CODE,
  desc AS PLU_DESC,
  sih_current.sell/iif(sih=0,1,sih) AS PLU_SELL_AV,
  (iif(iif(latestsell.asell > 0, latestsell.asell, selling.sell) > 0,
       iif(latestsell.asell > 0, latestsell.asell, selling.sell),
       full_inventory_current.sell)) AS PLU_SELL,
  sih_current.sih AS SIH,
  coalesce(iif(cost_purchase.cost > 0, cost_purchase.cost, latestsell.acost), 'UNKNOWN') AS cost,
  possible_barcodes
FROM sih_current
LEFT JOIN selling ON selling.itemcode = sih_current.itemcode
LEFT JOIN cost_purchase ON cost_purchase.itemcode = sih_current.itemcode
LEFT JOIN latestsell ON latestsell.itemcode = sih_current.itemcode
LEFT JOIN full_inventory_current ON sih_current.itemcode = full_inventory_current.itemcode
LEFT JOIN possible_barcodes ON sih_current.itemcode = possible_barcodes.itemcode
"
);
$stmt = $stmt_sql->execute();
$cur_data = $stmt_sql->fetchAll();
$dbh->commit();
//var_dump($past_data);
$response = $cur_data;
$response_dec = $response; // json_decode($response);
$tlat = $dbh->beginTransaction();
$stmt_sql_tlat = $dbh->prepare(
	"SELECT max(daydate) AS last_updated FROM hourly"
);
$stmt_tlat = $stmt_sql_tlat->execute();
$lastu = $stmt_sql_tlat->fetchAll();
$dbh->commit();
$t_q1_end = microtime(true);
$t_q2_start = microtime(true);
$ta = $dbh->beginTransaction();
//$stmta_sql = $dbh->prepare("SELECT ifnull(S_D60, 0) AS S_D60, ifnull(S_D30, 0) AS S_D30, ifnull(S_D15, 0) AS S_D15, everything.itemcode AS CODE FROM sih_current AS everything LEFT JOIN (SELECT total(quantity) as S_D60, itemcode FROM hourly WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-61 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) S60T ON everything.itemcode = S60T.itemcode LEFT JOIN (SELECT total(quantity) AS S_D30, itemcode FROM hourly WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-31 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) S30T ON S30T.itemcode = S60T.itemcode LEFT JOIN (SELECT total(quantity) AS S_D15, itemcode FROM hourly WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-16 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) AS S15T ON S15T.itemcode = S30T.itemcode ");
//$stmta_sql = $dbh->prepare("SELECT ifnull(S_D60, 0) AS S_D60, ifnull(S_D30, 0) AS S_D30, ifnull(S_D15, 0) AS S_D15, everything.itemcode AS CODE FROM sih_current AS everything LEFT JOIN (SELECT total(quantity) as S_D60, itemcode FROM hourly WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-61 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) S60T ON everything.itemcode = S60T.itemcode LEFT JOIN (SELECT total(quantity) AS S_D30, itemcode FROM hourly WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-31 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) S30T ON S30T.itemcode = S60T.itemcode LEFT JOIN (SELECT total(quantity) AS S_D15, itemcode FROM hourly WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-16 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) AS S15T ON S15T.itemcode = S30T.itemcode ");
$stmta_sql = $dbh->prepare(
	"SELECT ifnull(S_D60, 0) AS S_D60, ifnull(S_D30, 0) AS S_D30, ifnull(S_D15, 0) AS S_D15, everything.itemcode AS CODE FROM sih_current AS everything LEFT JOIN (SELECT total(quantity) as S_D60, itemcode FROM hourly INDEXED BY hourly_index_for_trends_replaceme WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-61 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) S60T ON everything.itemcode = S60T.itemcode LEFT JOIN (SELECT total(quantity) AS S_D30, itemcode FROM hourly INDEXED BY hourly_index_for_trends_replaceme WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-31 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) S30T ON S30T.itemcode = S60T.itemcode LEFT JOIN (SELECT total(quantity) AS S_D15, itemcode FROM hourly INDEXED BY hourly_index_for_trends_replaceme WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-16 days') AND date((SELECT max(daydate) FROM hourly), '-1 day') GROUP BY itemcode) AS S15T ON S15T.itemcode = S30T.itemcode "
);
$stmta_sql = $dbh->prepare(
	"SELECT
  ifnull(S_D60, 0) AS S_D60,
  ifnull(S_D30, 0) AS S_D30,
  ifnull(S_D15, 0) AS S_D15,
  everything.itemcode AS CODE
FROM sih_current AS everything
LEFT JOIN (
  SELECT total(quantity) as S_D60, itemcode
  FROM hourly
  WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-61 days')
                     AND date((SELECT max(daydate) FROM hourly), '-1 day')
  GROUP BY itemcode
) S60T ON everything.itemcode = S60T.itemcode
LEFT JOIN (
  SELECT total(quantity) AS S_D30, itemcode
  FROM hourly
  WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-31 days')
                     AND date((SELECT max(daydate) FROM hourly), '-1 day')
  GROUP BY itemcode
) S30T ON S30T.itemcode = S60T.itemcode
LEFT JOIN (
  SELECT total(quantity) AS S_D15, itemcode
  FROM hourly
  WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-16 days')
                     AND date((SELECT max(daydate) FROM hourly), '-1 day')
  GROUP BY itemcode
) AS S15T ON S15T.itemcode = S30T.itemcode"
);
$stmta = $stmta_sql->execute();
$past_data = $stmta_sql->fetchAll();
$dbh->commit();
$t_q2_end = microtime(true);
echo "<script>var list = " .
	json_encode(json_encode($response)) .
	"; list_p = JSON.parse((list));</script>";
echo "<script>var lista = " .
	json_encode(json_encode($past_data)) .
	"; list_a = JSON.parse((lista));</script>";

//var_dump($response);
?>
	<title>DETAILS: <?php echo $lastu[0][0]; ?></title>
		<link rel="stylesheet" type="text/css" href="style.css?ref=no-cache-5" />
		<link rel="icon" type="image/svg+xml" href="/srm-icons/barcode.svg" />
		<link rel="apple-touch-icon" size="180x180" type="image/png" href="/srm-icons/barcode_180.png" />
		<link rel="apple-touch-icon" size="120x120" type="image/png" href="/srm-icons/barcode_120.png" />
		<link rel="apple-touch-icon" size="152x152" type="image/png" href="/srm-icons/barcode_152.png" />
		<link rel="apple-touch-icon" size="any" type="image/svg+xml" href="/srm-icons/barcode.svg" />
		<script src="html5-qrcode.min.js" type="text/javascript"></script>
		<script src="printd.umd.min.js" type="module"></script>
		<script>
			document.addEventListener("DOMContentLoaded", start)
			document.addEventListener("DOMContentLoaded", initevents)
			var htmlQrcodeScanner;
			var lastScanResult="";
			var qrbox_size=200;
			function start(){
				var resultContainer = document.getElementById('qr-reader-results');
				var lastResult, countResults = 0;

				function onScanSuccess(decodedText, decodedResult) {
					console.log(`1: Scan result ${decodedText}`, decodedResult);
					if (decodedText !== lastScanResult) {
						++countResults;
						lastScanResult = decodedText;
						// Handle on success condition with the decoded message.
						console.log(`2: Scan result ${decodedText}`, decodedResult);
						lookupAndAdd(`${decodedText}`);
						console.log(`3: Scan result ${decodedText}`, decodedResult);
					}
				}

				html5QrcodeScanner = new Html5QrcodeScanner(
					"qr-reader", { fps: 10, qrbox: qrbox_size, aspectRatio: 0.5, 
						videoConstraints: {
							/*"zoom": {ideal: 0.5},*/
							facingMode: {ideal: "environment"},
							focusMode: "continuous",
							height: {ideal: 1080}
							/*"height": {"min": 1080}*/
						} 
					});
				html5QrcodeScanner.render(onScanSuccess);
			}
			async function stop(){
				await html5QrcodeScanner.clear();
			}
			async function restart(){await stop();start();}
			function initevents(){
			document.getElementById("qrbox_l").addEventListener("click", function(){qrbox_size=300; restart()});
			document.getElementById("qrbox_m").addEventListener("click", function(){qrbox_size=200; restart()});
			document.getElementById("qrbox_s").addEventListener("click", function(){qrbox_size=150; restart()});
			}
		</script>
<script>
list_p.forEach((v) => {DataCache.set(v.PLU_CODE, v)});
list_a.forEach((v) => {AnalyticsCache.set(v.CODE, v)});
</script>
</head>
<body class="cached">
<button onclick="goback()" class="navigation-button" style="position: fixed; bottom: 0; left: 0; font-size: 4vh; z-index: 2;" id="back">🔙 Go back!</button><br />
<div class="centered-container">
<span class="notice">Data loaded on (please check today's date): <?php echo $lastu[0][0]; ?></span> <br />
		<div id="everything">
		<div id="qr-reader" style="width:50vw"></div>
		<div id="qr-reader-results"></div><br />
		<br />
		<br />
		<div class="centered-container">
		<button id="qrbox_l">QRbox: large</button>
		<button id="qrbox_m">QRbox: med</button>
		<button id="qrbox_s">QRbox: small</button>
		</div>
		<br />
		</div>
</div>
<details>
<summary>More options...</summary>
<div class="centered-container">
<button onclick="(new printd.Printd()).print(document.getElementById('printer'), ['https://in.test.vz.al/scan/style.css'])">Print/export PDF</button>
<button onclick="">Copy as TSV</button>
</div>
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
<?php
//echo "<pre>".json_encode($response, JSON_PRETTY_PRINT)."</pre>";


$t_q1_diff_ms = ($t_q1_end - $t_q1_start) * 1000;
$t_q2_diff_ms = ($t_q2_end - $t_q2_start) * 1000;
echo "Took $t_q1_diff_ms milliseconds for query 1, $t_q2_diff_ms for query 2";
?>
</details>
</body>
</html>
