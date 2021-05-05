<!doctype html>
<html>
<head>
	<script type="text/javascript" src="../log4javascript.js"></script>
	<script type="text/javascript">
		var log = log4javascript.getDefaultLogger();
	</script>
	<script type="text/javascript" src="../moment.js"></script>
	<script src="../jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="../CommonScriptFunctions.js"></script>
        <script src="../tablefilter/tablefilter.js"></script>
        
	<?php
	include('../CommonPHPFunctions.php');
	include('../CouponFunctions.php');

	if (!isLoggedIn()) {
		$_SESSION['msg'] = "Prima devi fare il login";
		header('location: ../index.php');
	}
	?>
	<meta charset="utf-8">
	<title>PromoCash - Elenco Coupon</title>
	<link rel="stylesheet" type="text/css" href="../style.css">
	<script>
		var uploadfile=null;
		//Eseguito al caricamento della pagina
		window.onload = function() {
			//alert("Eseguo evento onload");
			mostra('frmModCoupon',false); //Nascondo il form di modifica del coupon
			<?php
			//Inizializzo la variabile di sessione bElencoCouponSoloAttivi
			if (!isset($_SESSION['bElencoCouponTutti']))    
				$_SESSION['bElencoCouponTutti'] = true;
			//Inizializzo la variabile di sessione bElencoCouponStandard
			if (!isset($_SESSION['bElencoCouponStandard']))    
				$_SESSION['bElencoCouponStandard'] = true;
			//Inizializzo la variabile di sessione bElencoCouponCustom
			if (!isset($_SESSION['bElencoCouponCustom']))    
			{
				if(is_true($only_standard_coupon)):
					$_SESSION['bElencoCouponCustom'] = false;
				else:
					$_SESSION['bElencoCouponCustom'] = true;
				endif;	
			}
			?>

			var bTuttiActive=new Boolean(<?php GetBoolSessionValue('bElencoCouponTutti',true); ?>);
			var bStandardActive=new Boolean(<?php GetBoolSessionValue('bElencoCouponStandard',true); ?>);
			var bCustomActive=new Boolean(
				<?php 
				if(is_true($only_standard_coupon)):
					GetBoolSessionValue('bElencoCouponCustom',false); 
				else:
					GetBoolSessionValue('bElencoCouponCustom',true); 
				endif;
				?>);
			

			changeFilterButtonText(bTuttiActive);//Metto un testo al pulsante di switch
			changeButtonBgColor(bTuttiActive,"btnTuttiSoloAttivi");
			changeButtonBgColor(bStandardActive,'btnOnlyStandard');
			changeButtonBgColor(bCustomActive,'btnOnlyCustom');

			//Aggiungo un evento a fileinput per i coupon custom
			if(document.getElementById('fileinput')!=null)
				document.getElementById('fileinput').addEventListener('change', function(){
					uploadfile = this.files[0];
					// This code is only for demo ...
					console.log("name : " + uploadfile.name);
					console.log("size : " + uploadfile.size);
					console.log("type : " + uploadfile.type);
					console.log("date : " + uploadfile.lastModified);
				}, false);	

		};	
		
		function changeFilterButtonText(Status){
                    var Button=document.getElementById('btnTuttiSoloAttivi');
                    if(Button!=null){
                        //La scritta è inversa al valore
                        if(Status==true){
                            Button.innerHTML ="Tutti";
                        }
                        else {
                            Button.innerHTML ="Solo Attivi";
                        }
                    }
		}

		function changeButtonBgColor(Status,ButtonId){
			var Button=document.getElementById(ButtonId);
			if(Button!=null)
				if(Status==true)
					Button.style.backgroundColor="#4CAF50";
				else 
					Button.style.backgroundColor="#ff0000";
		}
		
		function switchNewCouponToModify(e){
			var coupon=e;
			mostra('frmModCoupon',true);

                        document.getElementById('id_coupon_mc').value=coupon.Coupon_ID;
			var index=moveSelectToValue("phoneService_mc",coupon.Phone_service);
			document.getElementById('merchantCode_mc').selectedIndex	=	index;
			document.getElementById('coupon_code_mc').value			=	coupon.Coupon_code;
			document.getElementById('coupon_code_mc').disabled 		= 	true;

			var mdatestart = moment(coupon.Start,'DD/MM/YYYY HH:mm:ss');
			var mdatestop = moment(coupon.Stop,'DD/MM/YYYY HH:mm:ss');
			document.getElementById('valido_dal_date_mc').value = mdatestart.format('YYYY-MM-DD');
			document.getElementById('valido_dal_time_mc').value = mdatestart.format('HH:mm');
			document.getElementById('valido_al_date_mc').value = mdatestop.format('YYYY-MM-DD');
			document.getElementById('valido_al_time_mc').value = mdatestop.format('HH:mm');
                        
			var unlimited =  coupon.Unlimited;
			if(unlimited==1)
                            document.getElementById('chkUnlimited').checked=true;
                        else
                            document.getElementById('chkUnlimited').checked=false;
                        
			if (coupon.Coupon_custom=="0")
				document.getElementById('rdb_standard_mc').checked=true;
			else if (coupon.Coupon_custom!="0")
				document.getElementById('rdb_custom_mc').checked=true;

			if (coupon.Coupon_channel=="1")
				document.getElementById('rdb_web_mc').checked=true;
			else if (coupon.Coupon_channel=="2")
				document.getElementById('rdb_telefonico_mc').checked=true;

			if (coupon.Coupon_type=="1")
				document.getElementById('rdb_omaggio_secondi_mc').checked=true;
			else if (coupon.Coupon_type=="2")
				document.getElementById('rdb_sconto_importo_mc').checked=true;
			else if (coupon.Coupon_type=="3")
				document.getElementById('rdb_sconto_percentuale_mc').checked=true;
			else if (coupon.Coupon_type=="4")
				document.getElementById('rdb_omaggio_secondi_percentuale').checked=true;
			document.getElementById('nbenefit_mc').value = coupon.Coupon_value;
		}	

		function moveSelectToValue(selectname,textToFind){
			var ddl = document.getElementById(selectname);
			for (var i = 0; i < ddl.options.length; i++) {
				if (ddl.options[i].text === textToFind) {
					ddl.selectedIndex = i;
					return i;
				}
			}	
		}

		function confermaModificheCoupon(){
			var MerchantCode_mc = document.getElementById("merchantCode_mc");
			var PhoneService_mc = document.getElementById("phoneService_mc");
			var myurl;
			myurl="https://secure.tcserver.it/cgi-bin/"+"<?php echo $endPoint; ?>"+"?"+
			"cmd="+"modify"+
			"&username="+"<?php echo $_SESSION['db_user']; ?>"+
			"&password="+"<?php echo $_SESSION['db_password']; ?>"+
			"&coupon_id="+document.getElementById('id_coupon_mc').value+
			"&merchant_code="+MerchantCode_mc.options[MerchantCode_mc.selectedIndex].text +
			"&phone_service="+PhoneService_mc.options[PhoneService_mc.selectedIndex].text; 
			switch(document.querySelector('input[name="benefit_mc"]:checked').value)
			{
				case "omaggio_secondi":
					myurl+="&coupon_type=1";
					break;
				case "sconto_importo":
					myurl+="&coupon_type=2";
					break;
				case "sconto_percentuale":
					myurl+="&coupon_type=3";
					break;
			}
			myurl+="&coupon_value="+document.getElementById('nbenefit_mc').value;	
			switch(document.querySelector('input[name="couponchannel_mc"]:checked').value)
			{
				case 'standard':
					myurl+="&coupon_channel=1";
					break;
				case 'custom':
					myurl+="&coupon_channel=2";
					break;
			}
			if(document.querySelector('input[name="coupontype_mc"]:checked').value=='custom'){
				myurl+="&coupon_custom="+document.getElementById('coupon_code_mc').value;
			}

			var validodaldate = new Date(document.getElementById('valido_dal_date_mc').value);
			var startdate 	= 	moment(validodaldate).format("DD/MM/YYYY");
			var starttime	=	moment(moment(validodaldate).format("YYYY-MM-DD")+' '+ 
								document.getElementById('valido_dal_time_mc').value).format('HH:mm:ss');

			var validoaldate = new Date(document.getElementById('valido_al_date_mc').value);
			var stopdate	= 	moment(validoaldate).format("DD/MM/YYYY");
			var stoptime	=	moment(moment(validoaldate).format("YYYY-MM-DD")+' '+ 												document.getElementById('valido_al_time_mc').value).format('HH:mm:ss');

			myurl+="&start_date="	+	startdate	+
			"&start_time="			+	starttime	+
			"&stop_date="			+	stopdate	+
			"&stop_time="			+	stoptime;

			<?php if(isset($_SESSION['user']['username'])) ?>
				myurl += "&customer_limit="+"<?php echo $_SESSION['user']['username']; ?>"; 
                        
                        var unlimited = 0;  
                        if(document.getElementById("chkUnlimited").checked)
                            unlimited = 1;
                        myurl +="&unlimited="	+	unlimited;

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
					if (<?php var_export($is_debug); ?>)	log.info("Risposta AJAX OK. Risposta:"+res); 

				},
				error: function(err) {
					if (<?php var_export($is_debug); ?>)	log.info("Risposta AJAX KO. Errore:"+err);
				}
			});

			mostra('frmModCoupon',false);
			window.location.reload(); //ricarico la pagina per aggiornare l'elenco dei coupon con le modifiche effettuate
		}	

		function annullaModificheCoupon(){
			mostra('frmModCoupon',false);
		}	

		function onFilterButtonClick(selectObj){
			var AJAXurl="";
			//Recupero la variabile di sessione e la metto in bActive 
			var bActive = new Boolean();
			switch(selectObj.id){
				case 'btnTuttiSoloAttivi':
					bActive=<?php GetBoolSessionValue('bElencoCouponTutti',true); ?>;
					AJAXurl="../updatebElencoCouponTutti.php";
					break;
				case 'btnOnlyStandard':
					bActive=<?php GetBoolSessionValue('bElencoCouponStandard',true); ?>;
					AJAXurl="../updatebElencoCouponStandard.php";
					break;
				case 'btnOnlyCustom':
					bActive=<?php GetBoolSessionValue('bElencoCouponCustom',true); ?>;
					AJAXurl="../updatebElencoCouponCustom.php";
					break;
				default:
					break;
			}
			//Cambio lo stato della variabile
			bActive=!bActive;

			//Aggiorno la variabile di sessione
			$.ajax({
				type: "POST",
				url: AJAXurl,
				data: { 'value': bActive},
				cache: false,
				async: false, //attendo la risposta della pagina prima di proseguire
				//dataType: "json",
				success: 	function(data) {
					if (<?php var_export($is_debug); ?>)	log.info("Risposta AJAX ("+AJAXurl+") OK. Risposta: "+data); 
					/*alert(data);*/
				},
				error: 		function(msg) {
					if (<?php var_export($is_debug); ?>)	log.info("Risposta AJAX ("+AJAXurl+") KO. Errore: "+msg);
					alert("Risposta AJAX KO. Errore:"+msg);	
				}
			});
		}
		
		function OnSelectionChange_ModCoupon(selectObj) {
			if(selectObj.id=='phoneService_mc'){
				document.getElementById('merchantCode_mc').selectedIndex = document.getElementById('phoneService_mc').selectedIndex;
				}
			else if(selectObj.id=='merchantCode_mc'){
				document.getElementById('phoneService_mc').selectedIndex = document.getElementById('merchantCode_mc').selectedIndex;
				}
		}		

		function mostraNascondiUpload_ModCoupon(){
			if (document.getElementById('rdb_standard_mc').checked){
				document.getElementById('fileToUpload_mc').style.visibility = 'hidden';
			}
			if (document.getElementById('rdb_custom_mc').checked){
				document.getElementById('fileToUpload_mc').style.visibility = 'visible';
			}
		}

		function rdb_standard_ModCoupon_Click(){
			mostraNascondiUpload_ModCoupon();
		}

		function rdb_custom_ModCoupon_Click(){
			mostraNascondiUpload_ModCoupon();
		}

		function onChangeChannel(selectObj){
			var coupon_code_mc=document.getElementById('coupon_code_mc');

			if(selectObj.id=='rdb_web'){
				coupon_code.onkeypress = function () {};
			}
			if(selectObj.id=='rdb_telefonico'){
				if(!isANumber(coupon_code.value)){
					coupon_code.value='';
					coupon_code.focus();
					coupon_code.onkeypress = function(event){
						//write your method body
						return isNumberKey(event);
					};
				}
			}	
		}

		function omaggio_secondi_selected(selectObj){
			nbenefit.placeholder="Omaggio Secondi";
		}

		function sconto_importo_selected(selectObj){
			nbenefit.placeholder="Sconto sull'importo";
		}

		function sconto_percentuale_selected(selectObj){
			nbenefit.placeholder="Sconto in percentuale";
		}

		function isNumber(evt) {
			evt = (evt) ? evt : window.event;
			var charCode = (evt.which) ? evt.which : evt.keyCode;
			if (charCode > 31 && (charCode < 48 || charCode > 57)) {
				return false;
			}
			return true;
		}
                
	</script>	
</head>

<body>
	<?php
	global $ResultCode,$CodeDescription,$MessageError;
	
	echo display_error();  //Visualizza eventuali errori all'utente (rossi)
	echo display_message(); //Visualizza eventuali messaggi all'utente (verdi)
	
	$url = "https://secure.tcserver.it/cgi-bin/".$endPoint."?username=".$_SESSION['db_user']."&password=".$_SESSION['db_password']."&cmd=phoneservice_list";
	if(isset($_SESSION['user']['username']))
		$url = $url.'&customer_limit='.$_SESSION['user']['username'];
	try{
		$sxml_PhoneService=APIFetch($url);
	}
	catch (Exception $ex)
	{
		logWrite("error","Eccezione nell\'APIFetch N°1 (phoneservice_list). Dettagli:".$ex->getMessage());
		echo($ex->getMessage());
		exit;
	}
	$logm="Risposta dell API phoneservice_list (".$url.") : Code:".$ResultCode." CodeDescription:".$CodeDescription." Dettagli:".$MessageError;
	if($sxml_PhoneService!=false)
		logWrite("info","$logm");
	else{
		if($ResultCode=="-25")
			logWrite("debug","Errore nell\'APIFetch N°1 (phoneservice_list) IGNORATO codice:$ResultCode. Dettagli:$logm");
		else
			logWrite("error","Errore nell\'APIFetch N°1 (phoneservice_list)  codice:$ResultCode. Dettagli:$logm");
	}		

	$url = "https://secure.tcserver.it/cgi-bin/".$endPoint."?username=".$_SESSION['db_user']."&password=".$_SESSION['db_password'];
	if(is_true(GetBoolSessionValue('bElencoCouponTutti'))) 
		$url.="&cmd=report_full";
	else
		$url.="&cmd=report_active";
	
	if(isset($_SESSION['user']['username']))
		$url = $url.'&customer_limit='.$_SESSION['user']['username'];
	try{
		$sxml_Coupon=APIFetch($url);
	}
	catch (Exception $ex)
	{
		logWrite("error","Eccezione nell\'APIFetch N°2 (report_full). Dettagli:".$ex->getMessage());
		echo($ex->getMessage());
		exit;
	}
	$logm="Risposta dell API report_full (".$url."): Code:".$ResultCode." CodeDescription:".$CodeDescription." Dettagli:".$MessageError;
	if($sxml_Coupon!=false)
		logWrite("info","$logm");
	else{
		if($ResultCode=="-19")
			logWrite("debug","Errore nell\'APIFetch N°2 (report_full) IGNORATO codice:$ResultCode. Dettagli:$logm");
		else
			logWrite("error","Errore nell\'APIFetch N°2 (report_full)  codice:$ResultCode. Dettagli:$logm");
	}
	?>
	<div id="divIntestazione">
		<h1 style="display:inline">Elenco Coupon per: &nbsp;<?php	if (isset($_SESSION['user']))
																echo $_SESSION['user']['nomevisualizzato'];	?>
		</h1>	
	</div>
	
	<form id="frmModCoupon" class="frmCommon" method="post" action="<?=($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
		<div id="div_ModCoupon_Data" style="float:left;">
			<input type="text" id="id_coupon_mc" style="display: none"> 
			<fieldset>
                            <legend id="lgn_coupon_mc">Modifica Coupon</legend>
			</fieldset>
			<fieldset>
			Numero Servizio
			<select id="phoneService_mc" name="selectPhoneService_mc" onchange="OnSelectionChange_ModCoupon(this)">
			<?php 
				foreach($sxml_PhoneService->PhoneService as $PhoneService) 
					echo "<option value='".$PhoneService->Merchant_code."'>" . $PhoneService->Phone_service . "</option>";
			?>
			</select>
			Codice Merchant
			<select id="merchantCode_mc" name="selectMerchantCode_mc" onChange="OnSelectionChange_ModCoupon(this)">
			<?php 
				foreach($sxml_PhoneService->PhoneService as $PhoneService) 
					echo "<option value='". $PhoneService->Phone_service."'>" . $PhoneService->Merchant_code . "</option>";
			?>
			</select>
			</fieldset>
			<fieldset>
			<div class="input-group">
				<label>Codice Coupon</label>
				<input id="coupon_code_mc" type="text" name="cod_coupon_mc" value="" disabled >
			</div>		
			<div>
				<label>Valido dal </label>
				<br>
				<input type="date" id="valido_dal_date_mc" class="half_size" >
				<input type="time" class="half_size" id="valido_dal_time_mc" value="00:00">
				<br>
				<label> al </label>
				<br>
				<input type="date" id="valido_al_date_mc" class="half_size" >
				<input type="time" class="half_size" id="valido_al_time_mc" value="23:59">
			</div>
                        <br>
                        <div>    
                            <label style="font-size: 16px;"><input type="checkbox" id="chkUnlimited" name="chkUnlimited" value="chkUnlimited" ">UNLIMITED</label>    
                        </div>
                        <div>
                            <h3>Tipo di Coupon</h3>
                            <input id="rdb_standard_mc" type="radio" name="coupontype_mc" value="standard" checked="checked" onclick="rdb_standard_ModCoupon_Click();" disabled>Standard
                            <input id="rdb_custom_mc" type="radio" name="coupontype_mc" value="custom" onclick="rdb_custom_ModCoupon_Click();" disabled>Custom
                            <!--<input type="file" id="fileToUpload_mc" name="fileToUpload_mc" accept=".txt" style="visibility: hidden">-->
                        </div>
                        <div>
                            <h3>Canale Coupon</h3>
                            <input id="rdb_web_mc" type="radio" name="couponchannel_mc" value="standard" onclick="onChangeChannel(this);" checked="checked">Web
                            <input id="rdb_telefonico_mc" type="radio" name="couponchannel_mc" value="custom" onclick="onChangeChannel(this);">Telefonico
                        </div>
                        <div>
                            <h3>Benefit</h3>
                            <input id="rdb_omaggio_secondi_mc" type="radio" name="benefit_mc" value="omaggio_secondi"
                                       onclick="omaggio_secondi_selected(this);" checked="checked">Omaggio Secondi
                            <input id="rdb_omaggio_secondi_percentuale_mc" type="radio" name="benefit_mc" value="omaggio_secondi_percentuale" onclick="omaggio_secondi_percentuale_selected(this);">Omaggio Secondi %
                            <input id="rdb_sconto_importo_mc" type="radio" name="benefit_mc" value="sconto_importo" onclick="sconto_importo_selected(this);">Sconto Importo
                            <input id="rdb_sconto_percentuale_mc" type="radio" name="benefit_mc" value="sconto_percentuale" onclick="sconto_percentuale_selected(this);">Sconto %
                            <input type="text" id="nbenefit_mc" name="nbenefit_mc" placeholder="Omaggio Secondi" onkeypress="return isNumber(event);" size="5">
                        </div>
                        <div>
                            <input type="button" id="conferma_modifiche_mc" name="conferma_modifiche_mc" value="Conferma Modifiche" onclick="confermaModificheCoupon();">
                            <input type="button" id="annulla_modifiche_mc" name="annulla_modifiche_mc" value="Annulla" onclick="annullaModificheCoupon();">
                        </div>
			</fieldset>
		</div>
	</form>
	
	<div id="divElenco_Coupon" >
	<form id="frmElencoCoupon" class="frmCommon" method="post" action="<?=($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
		<fieldset style="float:left;">
			Filtri:
			<button class="button" type="submit" id="btnTuttiSoloAttivi" name="btnTuttiSoloAttivi" onclick="onFilterButtonClick(this);"></button>
			<button class="button" type="submit" id="btnOnlyStandard" name="btnOnlyStandard" onclick="onFilterButtonClick(this);">Standard</button>
			<?php
				if( (!is_true($only_standard_coupon)) && (!is_true($_SESSION['user']['only_export_cli'])) ):
				echo '<button class="button" type="submit" id="btnOnlyCustom" name="btnOnlyCustom" onclick="onFilterButtonClick(this);">Custom</button>';
				endif;	
			?>
		</fieldset>
		<br class="clear">
		</h3>
		<?php 
			$ElencoCouponStandard=true;
			$ElencoCouponCustom=true;

			//recupero il valore della variabile di sessione bElencoCouponStandard
			if (isset($_SESSION['bElencoCouponStandard']))
			{
				if(is_true($_SESSION['bElencoCouponStandard'])) 
					$ElencoCouponStandard=true;
				else
					$ElencoCouponStandard=false;
			}
			//recupero il valore della variabile di sessione bElencoCouponCustom
			if (isset($_SESSION['bElencoCouponCustom']))
			{
				if(is_true($_SESSION['bElencoCouponCustom'])) 
					$ElencoCouponCustom=true;
				else
					$ElencoCouponCustom=false;
			}
		  	if($ElencoCouponStandard==true){
		?>
		<div id="divElenco_Coupon_Standard" >
			<table id="tbElencoCouponStandard" border='1'>
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
                                                <th>Unlimited</th>
						<th>Modify</th>
						<th>Delete</th>
					</tr>
				</thead>
				<tbody>	
					<?php
		  					$countStandard=0;
							if(isset($sxml_Coupon->Coupon)){
								echo "<h2 align=\"center\">Coupon Standard</h2>";
								foreach($sxml_Coupon->Coupon as $Coupon) :
									if($Coupon->Coupon_custom==0){
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
                                        <td><?php echo $Coupon->Unlimited; ?></td>
					<td>
						<button type="button" onclick='switchNewCouponToModify(<?php echo json_encode($Coupon); ?>);'>
								MODIFY</button>
					</td>
					<td><button  type="button" onclick="window.location='elenco_coupon.php?action=delete&id=<?php echo $Coupon->Coupon_ID;?>&merchant_code=<?php echo $Coupon->Merchant_code;?>&phone_service=<?php echo $Coupon->Phone_service;?>';" >DELETE</button></td>
					</tr>
					<?php 			$countStandard++;
									}
								endforeach; 
							}
					?>
				</tbody>
			</table>
			<h4>Totale Coupon Standard:<?php echo $countStandard ?></h4>
		</div> <!--divElenco_Coupon_Standard-->	
		<?php 	
			} 
			if($ElencoCouponCustom==true){?>
		<div id="divElenco_Coupon_Custom" >

			<table id="tbElencoCouponCustom" border='1'">
				<thead>
                                        <tr>
						<th>Coupon_code</th>
						<th>Coupon_type</th>
						<th>Coupon_value</th>
						<th>Coupon_custom</th>
						<th>Coupon_channel</th>
						<th>Merchant_code</th>
						<th>Phone_service</th>
						<th>Start</th>
						<th>Stop</th>
                                                <th>Unlimited</th>
						<th>Modify</th>
						<th>Delete</th>
						<th style="display: none">Coupon_ID</th>
					</tr>
				</thead>
				<tbody>	
				<?php 		
		  		$countCustom=0;
				if(isset($sxml_Coupon->Coupon)){
					echo "<h2 align=\"center\">Coupon Custom</h2>";
					foreach($sxml_Coupon->Coupon as $Coupon) :
                                            if($Coupon->Coupon_custom!=0){?>
					<tr>
					<td><?php echo $Coupon->Coupon_code; ?></td>
					<td><?php echo $Coupon->Coupon_type; ?></td>
                                        <td><?php echo $Coupon->Coupon_value; ?></td>
                                        <td><?php echo $Coupon->Coupon_custom; ?></td>
                                        <td><?php echo $Coupon->Coupon_channel; ?></td>
					<td><?php echo $Coupon->Merchant_code; ?></td>
					<td><?php echo $Coupon->Phone_service; ?></td>
					<td><?php echo $Coupon->Start; ?></td>
					<td><?php echo $Coupon->Stop; ?></td>
                                        <td><?php echo $Coupon->Unlimited ? 'false' : 'true';?></td>                                        
					<td style="display: none"><?php echo $Coupon->Coupon_ID; ?></td>
					<td>
						<button type="button" onclick='switchNewCouponToModify(<?php echo json_encode($Coupon); ?>);'>
								MODIFY</button>
					</td>
					<td><button  type="button" onclick="window.location='elenco_coupon.php?action=delete&id=<?php echo $Coupon->Coupon_ID;?>&merchant_code=<?php echo $Coupon->Merchant_code;?>&phone_service=<?php echo $Coupon->Phone_service;?>';" >DELETE</button></td>
					</tr>
					<?php 			
                                            $countCustom++;
                                            }
					endforeach; 
                                }?>

				</tbody>
			</table>
                        <script>
                        //TableFilter configuration
                        var filtersConfig = {
                          // instruct TableFilter location to import ressources from
                          col_1: 'none',
                          col_4: 'select',
                          col_5: 'select',
                          col_6: 'select',
                          col_9: 'select',
                          col_10: 'none',
                          col_11: 'none',
                          col_12: 'none'
                        };

                        var tf = new TableFilter('tbElencoCouponCustom', filtersConfig);
                        tf.init();
                        </script>
			<h4>Totale Coupon Custom:<?php echo $countCustom ?>
                        <input type='button' value='Salva su File TUTTI i Cli Custom' onclick='salvaSuFileCLICouponCustom()'>
                        <input type='button' value='Salva su File SOLO Cli Custom Selezionati' onclick='salvaSuFileCLICouponCustomSelezionati()'>
                        
                        </h4>
		</div>	<!--divElenco_Coupon_Custom-->	
	</form>
	</div>
	<?php 	} ?>
	<h4>Totale Coupon totali:<?php if(isset($sxml_Coupon->Coupon)) echo count($sxml_Coupon->Coupon) ?></h4>
	<input type='button' value='Elimina Tutti I Coupon Standard' onclick='goToDeleteStandard()'>
	<?php
	if(!is_true($only_standard_coupon)):
		echo '<input type=\'button\' value=\'Elimina Tutti I Coupon Custom\' onclick=\'goToDeleteCustom()\'>';
		echo '<input type=\'button\' value=\'Elimina Tutti I Coupon\' onclick=\'goToDeleteAll()\'>';
	endif;	
	?>
    <script>
  

        function goToDeleteStandard()
        {
            var r = confirm("Sei sicuro di voler eliminare TUTTI i coupon STANDARD?");
            if (r == true) 
                window.location='elenco_coupon.php?action=elimina_tutti_coupon_standard';
        }
        function goToDeleteCustom()
        {
            var r = confirm("Sei sicuro di voler eliminare TUTTI i coupon CUSTOM?");
            if (r == true) 
                window.location='elenco_coupon.php?action=elimina_tutti_coupon_custom';
        }
        function goToDeleteAll()
        {
            var r = confirm("Sei sicuro di voler eliminare TUTTI i coupon inseriti?");
            if (r == true) 
                window.location='elenco_coupon.php?action=elimina_tutti_coupon';
        }
        
        function countVisibleRows(){
            let rowCount = $('#tbElencoCouponCustom tr:visible').length - 1;
        }

        function salvaSuFileCLICouponCustom()
        {
            var retContent = [];
            var text = '';

            $("#tbElencoCouponCustom tbody td:nth-child(4").each(function (idx, elem)
            {
                var elemText = [];
                elemText.push($(elem).text());
                //elemText=RemoveCountryWoutRef(elemText);
                //retContent.push(`${elemText.join('\r\n')}`);
                retContent.push(RemoveCountryWoutRef(elemText));
            });
            text = retContent.join('\r\n');
            // Generate download of hello.txt file with some content
            var filename = "CliCustom.txt";

            download(filename, text);
        }

        function salvaSuFileCLICouponCustomSelezionati()
        {
            var retContent = [];
            var text = '';

            $("#tbElencoCouponCustom tbody td:nth-child(4):visible").each(function (idx, elem)
            {
                var elemText = [];
                elemText.push($(elem).text());
                //elemText=RemoveCountryWoutRef(elemText);
                //retContent.push(`${elemText.join('\r\n')}`);
                retContent.push(RemoveCountryWoutRef(elemText));
            });
            text = retContent.join('\r\n');
            // Generate download of hello.txt file with some content
            var filename = "CliCustom.txt";

            download(filename, text);
        }

    </script>
</body>
</html>