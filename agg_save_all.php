<?php 
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once("/etc/auth.php");
for($i=0;$i<30000;$i+=100)
{
	echo "Iteration: $i.";
	$IDs = (range($i,$i+100));
	foreach ($IDs as $k=>$ID){
		$IDs[$k]=str_pad($ID, 6, "0", STR_PAD_LEFT);
	}
	$BASEURL = "http://127.0.0.1:9090/api/Items2/GetSalesDataForAnalysis";
	$REQUESTS = [];
	$mh = curl_multi_init();
	foreach($IDs as $ID){
		$req = curl_init();
		curl_setopt($req, CURLOPT_URL, "$BASEURL?PLU_CODE=$ID");
		curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($req, CURLOPT_HTTPHEADER, ["Authorization: Basic $ENCODED_AUTH"]);
		curl_multi_add_handle($mh, $req);
		array_push($REQUESTS, $req);
		unset($req);
	}
	do{
		$status = curl_multi_exec($mh, $active);
		if ($active){
			curl_multi_select($mh);
		}
	}
	while ($active && $status == CURLM_OK);
	$RESPONSES=[];
	foreach($REQUESTS as $req)
	{
		$res=curl_multi_getcontent($req);
		//var_dump($res);
		array_push($RESPONSES, json_decode($res));
	};
	var_dump($RESPONSES);
	$BASEURL2 = "http://127.0.0.1:9090/api/Items2";
	$REQUESTS2 = [];
	$mh2 = curl_multi_init();
	foreach($IDs as $ID){
		$req2 = curl_init();
		$ID_padded = str_pad($ID, 6, "0", STR_PAD_LEFT);
		curl_setopt($req2, CURLOPT_URL, "$BASEURL2/$ID_padded");
		curl_setopt($req2, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($req2, CURLOPT_HTTPHEADER, ["Authorization: Basic $ENCODED_AUTH"]);
		curl_multi_add_handle($mh2, $req2);
		array_push($REQUESTS2, $req2);
		unset($req2);
	}
	do{
		$status2 = curl_multi_exec($mh2, $active2);
		if ($active2){
			curl_multi_select($mh2);
		}
	}
	while ($active2 && $status2 == CURLM_OK);
	$RESPONSES2=[];
	foreach($REQUESTS2 as $req2)
	{
		$res2=curl_multi_getcontent($req2);
		//var_dump($res);
		array_push($RESPONSES2, json_decode($res2));
	};
	var_dump($RESPONSES2);
	$dbh = new PDO("sqlite:/saru/www-data/db.sqlite3");
	$t = $dbh->beginTransaction();
	$stmt_sql = $dbh->prepare("insert into productsattime ('ID', 'SIH', 's15', 's30', 's60') values (?, ?, ?, ?, ?)");
	$stmt_sql_misc = $dbh->prepare("insert into productsattime_misc ('ID', 'DESC', 'BARCODE', 'SELL') values (?, ?, ?, ?)");
	foreach($RESPONSES as $RESPONSE){
		if ($RESPONSE==null) continue;
		$stmt = $stmt_sql->execute([$RESPONSE->CODE, $RESPONSE->SIH, $RESPONSE->S_D15, $RESPONSE->S_D30, $RESPONSE->S_D60]);
	}
	foreach($RESPONSES2 as $RESPONSE2){
		if ($RESPONSE2==null) continue;
		echo "BARCODE: ";
		var_dump($RESPONSE2);
		if(property_exists($RESPONSE2, "BARCODES")){
		if (count($RESPONSE2->BARCODES)==0){
		$stmt2 = $stmt_sql_misc->execute([$RESPONSE2->PLU_CODE, $RESPONSE2->PLU_DESC, null, $RESPONSE2->PLU_SELL]);
		}
		else{
			foreach($RESPONSE2->BARCODES as $BARCODE){
				$stmt2 = $stmt_sql_misc->execute([$RESPONSE2->PLU_CODE, $RESPONSE2->PLU_DESC, $BARCODE, $RESPONSE2->PLU_SELL]);
			}
		}
		} else {
			echo "NO KEY: BARCODE\n";
		}
	}
	$dbh->commit();
	sleep(5);
}
?>
