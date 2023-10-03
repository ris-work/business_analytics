<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once("/etc/auth.php");
//var_dump($_POST["ids"]);
//var_dump($_POST);
$IDs = json_decode($_POST["ids"]);
//var_dump($IDs);
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
echo json_encode($RESPONSES);
//$response = json_decode(curl_exec($req));
//var_dump($response);
?>
