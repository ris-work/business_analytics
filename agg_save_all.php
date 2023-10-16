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
	$dbh = new PDO("sqlite:/saru/www-data/db.sqlite3");
	$t = $dbh->beginTransaction();
	$stmt_sql = $dbh->prepare("insert into productsattime ('ID', 'SIH', 's15', 's30', 's60') values (?, ?, ?, ?, ?)");
	foreach($RESPONSES as $RESPONSE){
		if ($RESPONSE==null) continue;
		$stmt = $stmt_sql->execute([$RESPONSE->CODE, $RESPONSE->SIH, $RESPONSE->S_D15, $RESPONSE->S_D30, $RESPONSE->S_D60]);
	}
	$dbh->commit();
	sleep(5);
}
?>
