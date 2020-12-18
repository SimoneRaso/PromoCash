<?php
//include('./CommonFunctions.php');
include('APIFetch.php');

global $ResultCode,$CodeDescription,$MessageError; 
//$data = json_decode(stripslashes($_POST['data']));
//$data = $_POST['data'];
$data = json_decode($_POST['dataposted']);

//$res="AJAX Data Received Successfully.Data received: POST['dataposted']=".$_POST['dataposted']."  data(json_decode)=".$data;

APIFetch($data);

//$myObj = json_decode("{}"); //Creazione di un oggetto al volo
//$myObj->Resultcode = $ResultCode;
//$myObj->CodeDescription = CodeDescription;
//$myObj->MessageError = $MessageError;
$myObj = (object)['ResultCode' => $ResultCode,'CodeDescription' => $CodeDescription,'MessageError'=>$MessageError ]; //Altro modo di creare un oggetto al volo


$res = json_encode($myObj);
echo $res

?>
