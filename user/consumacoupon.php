<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="../log4javascript.js"></script>
	<script type="text/javascript">
		var log = log4javascript.getDefaultLogger();
	</script>
	<script type="text/javascript" src="../moment.js"></script>
	<script src="../jquery-3.3.1.min.js"></script>

	<?php
	include('../CommonPHPFunctions.php');

	if (!isLoggedIn()) {
		$_SESSION['msg'] = "Prima devi fare il login";
		header('location: ../index.php');
	}
	?>
	<meta charset="utf-8">
	<title>PromoCash - Consuma Coupon</title>
	<link rel="stylesheet" type="text/css" href="../style.css">

	<script>
		var customer_limit="<?php if(isset($_SESSION['user']['username']))	echo($_SESSION['user']['username']);?>"
			
		function consumacoupon(coupon){
			if(coupon.Coupon_custom==0){
				alert("Operazione valida solo per coupon di tipo CUSTOM");
				return;
			}
	
			alert("consumo il coupon"+coupon.Coupon_code+"con user_cli="+coupon.Coupon_custom);
			
			var myurl="https://secure.tcserver.it/cgi-bin/"+"<?php echo $endPoint; ?>"+"?"+"cmd=coupon_simulate_usage"+
			"&username="+"<?php echo $_SESSION['db_user']; ?>"+"&password="+"<?php echo $_SESSION['db_password']; ?>"+
			"&merchant_code="+ coupon.Merchant_code+
			"&phone_service="+ coupon.Phone_service+
			"&coupon_id="+ coupon.Coupon_ID +
			"&user_cli="+ coupon.Coupon_custom +
			"&customer_limit="+customer_limit;
			
			//Devo richiamare la pagina con paremtro url per fare APIfetch
			// AJAX code to submit form.
			if (<?php var_export($is_debug); ?>)	log.info('Inizio AJAX');
			var jsonString = JSON.stringify(myurl);
			if (<?php var_export($is_debug); ?>)	log.info('Dati inoltrati(jsonString)= '+jsonString); 
			$.ajax({
				method: "POST",
				url: "../pass-data.php",
				data: {dataposted: jsonString}, 
				cache: false,
				async:false, //attende la risposta della pagina prima di proseguire
				success: function(res) {
					alert(res);
					if (<?php var_export($is_debug); ?>)	log.info("Risposta AJAX OK. Risposta:"+res); 

				},
				error: function(err) {
					if (<?php var_export($is_debug); ?>)	log.info("Risposta AJAX KO. Errore:"+err);
				}
			});
		}
	</script>
</head>

<body>
	<?php
		$url = "https://secure.tcserver.it/cgi-bin/".$endPoint."?username=".$_SESSION['db_user']."&password=".$_SESSION['db_password']."&cmd=report_full";
		if(isset($_SESSION['user']['username']))
			$url = $url.'&customer_limit='.$_SESSION['user']['username'];
		try{
			$sxml_Coupon=APIFetch($url);
		}
		catch (Exception $ex)
		{
			logWrite("error","Eccezione nell\'APIFetch (report_full). Dettagli:".$ex->getMessage());
			echo($ex->getMessage());
			exit;
		}
		$logm="Risposta dell API report_full (".$url."): Code:".$ResultCode." CodeDescription:".$CodeDescription." Descrizione:".$MessageError;
		if($sxml_Coupon!=false)
			logWrite("info","$logm");
		else{
			if($ResultCode=="-19")
				logWrite("debug","Errore nell\'APIFetch N°2 (report_full) IGNORATO codice:$ResultCode. Dettagli:$logm");
			else
				logWrite("error","Errore nell\'APIFetch N°2 (report_full)  codice:$ResultCode. Dettagli:$logm");
		}
		if($sxml_Coupon!=false)
			logWrite("info","$logm");
	else
	?>
	
	<h1 style="display:inline">Consuma coupon per: &nbsp;<?php	if (isset($_SESSION['user'])) echo $_SESSION['user']['nomevisualizzato'];?>
	</h1>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="home.php" style="color: green;">Torna alla Home</a>
	
	<div id="divElenco_Coupon" >
	<form id="frmElencoCoupon" class="frmCommon" method="post" action="<?=($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
		<h3>Elenco dei coupon:</h3>
		<table border='1'>
			<thead>
				<tr>
					<th style="display: none">Coupon_ID</th>
					<th>Coupon_code</th>
					<th>Coupon_type</th>
					<th>Coupon_value</th>
					<th>Coupon_custom</th>
					<th>Coupon_channel</th>
					<th>Merchant_code</th>
					<th>Phone_service</th>
					<th>Start</th>
					<th>Stop</th>
					<th>Consuma</th>
				</tr>
			</thead>
			<tbody>	
				<?php 
					if(isset($sxml_Coupon->Coupon)){
						foreach($sxml_Coupon->Coupon as $Coupon) :
				?>
				<tr>
				<td style="display: none"><?php echo $Coupon->Coupon_ID; ?></td>
				<td><?php echo $Coupon->Coupon_code; ?></td>
				<td><?php echo $Coupon->Coupon_type; ?></td>
				<td><?php echo $Coupon->Coupon_value; ?></td>
				<td><?php echo $Coupon->Coupon_custom; ?></td>
				<td><?php echo $Coupon->Coupon_channel; ?></td>
				<td><?php echo $Coupon->Merchant_code; ?></td>
				<td><?php echo $Coupon->Phone_service; ?></td>
				<td><?php echo $Coupon->Start; ?></td>
				<td><?php echo $Coupon->Stop; ?></td>
				<td>
					<button type="button" name="btnconsumacoupon" 	onclick='consumacoupon(<?php echo json_encode($Coupon); ?>);'>Consuma</button>
				</td>
				</tr>
				<?php 	endforeach; 
					}?>
			</tbody>
		</table>
	</form>
	</div>
	<h4>Totale Elementi visualizzati:<?php if(isset($sxml_Coupon->Coupon)) echo count($sxml_Coupon->Coupon) ?></h4>
</body>
</html>