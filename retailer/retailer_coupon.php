<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="../log4javascript.js"></script>
	<script type="text/javascript">
		var log = log4javascript.getDefaultLogger();
	</script>
	<script type="text/javascript" src="../moment.js"></script>
	<script src="../jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="../CommonScriptFunctions.js"></script>
	
	<?php
	include('../CommonPHPFunctions.php');
	include('../CouponFunctions.php');
        include('RetailerPHPFunctions.php');

	if (!isLoggedIn()) {
		$_SESSION['msg'] = "Prima devi fare il login";
		header('location: ../index.php');
	}
	?>
	<meta charset="utf-8">
	<title>PromoCash - Retailer Coupon</title>
	<link rel="stylesheet" type="text/css" href="../style.css">

	<script>
	function OnSelectionChange(selectObj) {
		if(selectObj.id=='phoneService'){
			document.getElementById('merchantCode').selectedIndex = document.getElementById('phoneService').selectedIndex;
			}
		else if(selectObj.id=='merchantCode'){
			document.getElementById('phoneService').selectedIndex = document.getElementById('merchantCode').selectedIndex;
			}
	}	    

	function OnSelectionChange_mc(selectObj) {
		if(selectObj.id=='phoneService_mc'){
			document.getElementById('merchantCode_mc').selectedIndex = document.getElementById('phoneService_mc').selectedIndex;
			}
		else if(selectObj.id=='merchantCode_mc'){
			document.getElementById('phoneService_mc').selectedIndex = document.getElementById('merchantCode_mc').selectedIndex;
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
	function mostraNascondiUpload(){
		if (document.getElementById('rdb_standard').checked){
			document.getElementById('btnUpload').style.visibility = 'hidden';
			document.getElementById('fileToUpload').style.visibility = 'hidden';
			document.getElementById('fileinput').style.visibility = 'hidden';
			document.getElementById('btnUploadJS').style.visibility = 'hidden';
		}
		if (document.getElementById('rdb_custom').checked){
			document.getElementById('btnUpload').style.visibility = 'visible';
			document.getElementById('fileToUpload').style.visibility = 'visible';
			document.getElementById('fileinput').style.visibility = 'visible';
			document.getElementById('btnUploadJS').style.visibility = 'visible';
		}
	}


	function changeInputType(selectObj){
		var coupon_code=document.getElementById('coupon_code');
		if(selectObj.id=='rdb_web')
		{
			coupon_code.onkeypress = function () {};
		}
		if(selectObj.id=='rdb_telefonico'){
			if(!isANumber(coupon_code.value)){
				alert("Attenzione: per coupon di tipo \'telefonico\' vengono accettati solo codici numerici!");
				coupon_code.value='';
				coupon_code.focus();
				coupon_code.onkeypress = function(event){
					//write your method body
					return isNumberKey(event)
				};
			}
		}	
	}

	function isANumber(str){return /^\d+$/.test(str);}

	function isNumberKey(evt){
		var charCode = (evt.which) ? evt.which : evt.keyCode;
		if (charCode > 31 && (charCode < 48 || charCode > 57))
			return false;
		return true;
	}

	function switchNewCouponToModify(coupon){
		mostra('divform_ModCoupon',true);
		mostra('divform_NewCoupon',false);

		document.getElementById('id_coupon_mc').value=coupon.Coupon_ID;
		var index=moveSelectToValue("phoneService_mc",coupon.Phone_service);
		document.getElementById('merchantCode_mc').selectedIndex	=	index;
		document.getElementById('coupon_code_mc').value				=	coupon.Coupon_code;

		var mdatestart = moment(coupon.Start,'DD/MM/YYYY HH:mm:ss');
		var mdatestop = moment(coupon.Stop,'DD/MM/YYYY HH:mm:ss');
		document.getElementById('valido_dal_date_mc').value = mdatestart.format('YYYY-MM-DD');
		document.getElementById('valido_dal_time_mc').value = mdatestart.format('HH:mm');
		document.getElementById('valido_al_date_mc').value = mdatestop.format('YYYY-MM-DD');
		document.getElementById('valido_al_time_mc').value = mdatestop.format('HH:mm');

		if (coupon.Coupon_custom=="0")
			document.getElementById('rdb_standard_mc').checked=true;
		else if (coupon.Coupon_custom!="0")
			document.getElementById('rdb_custom_mc').checked=true;
		//mostraNascondiUpload_mc(); //Per far apparire il pulsante file e relativo bottone

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
			myurl+="&coupon_custom=";//devo inserire i numeri telefonici
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

		mostra('divform_ModCoupon',false);
		mostra('divform_NewCoupon',true);
		window.location.reload(); //ricarico la pagina per aggiornare l'elenco dei coupon con le modifiche effettuate
	}	

	function annullaModificheCoupon(){
		mostra('divform_ModCoupon',false);
		mostra('divform_NewCoupon',true);
	}	

	var uploadfile=null;
	//Eseguito al caricamento della pagina
	window.onload = function() {
		//alert("Eseguo evento onload");
		mostra('divform_NewCoupon',true); //Visualizzo il form di immissione di un nuovo coupon
		mostra('divform_ModCoupon',false);//Nascondo il form di modifica coupon
		<?php
		//Inizializzo la variabile di sessione bElencoCouponSoloAttivi
		if (!isset($_SESSION['bElencoCouponSoloAttivi']))    
			$_SESSION['bElencoCouponSoloAttivi'] = false;
		?>

		var bActive = new Boolean();
		bActive=<?php GetBoolSessionValue('bElencoCouponSoloAttivi',true); ?>;
		
		//Metto un testo al pulsante di switch
		changeButtonText(bActive);
		
		//Sposto il divSession_Function in fondo
		//var offsetHeight=getDivHeight('divform_NewCoupon');
		//document.getElementById("divSession_Function").style.marginTop = offsetHeight + "px";
		
		
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

	function getDivHeight(elementId) { 
		var num = document.getElementById(elementId).offsetHeight; 
		return num; 
	}

	function modifyVar(obj, val) {
		obj.valueOf = obj.toSource = obj.toString = function(){ return val; };
	}

	function changeButtonText(Status){
		if(document.getElementById('btnCambiaFullActive')!=null)
			if(Status==true)
				document.getElementById('btnCambiaFullActive').innerHTML ="Tutti";
			else 
				document.getElementById('btnCambiaFullActive').innerHTML ="Solo Attivi";
	}


	function showFullOrOnlyActive(){
//			var temp=<?php
//				if (isset($_SESSION['bElencoCouponSoloAttivi'])){
//					if(is_true($_SESSION['bElencoCouponSoloAttivi'])){
//						echo "true"; 
//					}
//					else {
//						echo "false";
//					}
//				}	
//				else
//					echo "null";
//				?>;
//			alert("showFullOrOnlyActive (inizio). La variabile di sessione vale "+temp);

		debugger;
		
		//Recupero la variabile di sessione e la metto in bActive
		var bActive = new Boolean();
		bActive=<?php GetBoolSessionValue('bElencoCouponSoloAttivi',true); ?>;

		//Cambio lo stato della variabile
		bActive=!bActive;

		//Aggiorno la variabile di sessione
		$.ajax({
			type: "POST",
			url: "../updatebElencoCouponSoloAttivi.php",
			data: { 'value': bActive},
			cache: false,
			async: false, //attendo la risposta della pagina prima di proseguire
			//dataType: "json",
			success: 	function(data) {
				if (<?php var_export($is_debug); ?>)	log.info("Risposta AJAX OK. Risposta:"+data); 
				/*alert(data);*/
			},
			error: 		function(msg) {
				if (<?php var_export($is_debug); ?>)	log.info("Risposta AJAX KO. Errore:"+msg);
				alert("Risposta AJAX KO. Errore:"+msg);	
			}
		});

	}
		
	function caricaFileJavaScript(){
		var maxCLI=10;
		var target_dir = "uploads/";
		var target_file = (uploadfile!=null?uploadfile.name:null);
		var uploadOk = 1;
		var imageFileType =(uploadfile!=null?uploadfile.type:null);
		
		if(target_file==null){
			alert("Nessun File selezionato");
			return;
		}
		if(imageFileType!="text/plain"){
			alert("Sono consentiti solo file di testo (.txt)");
			return;
		}
		
		//Leggo il file riga per riga e li metto in RigheLette
		var RigheLette=[];
		var reader = new FileReader();
		reader.onload = function(progressEvent){
			var counterBuoni=0;
			var Errori=[];
			var lines = this.result.split('\n');
			var nRighe=lines.length;
			if(nRighe>maxCLI){
					Errori.push("N° cellulari inseriti superiore al max consentito ("+maxCLI+"). Gli eccedenti vengono troncati\r\n");
					nRighe=maxCLI;
			}
			for(var line = 0; line < nRighe; line++){
			  	console.log(lines[line]);
			
				//Tolgo eventuali spazi vuoti
				//var linea = lines[line].replace(/ /g, ''); 
				var linea = lines[line].trim(); 
				
				//Verifico riga vuota
				if(linea==""){
					Errori.push("Riga "+(line+1)+": Riga Vuota. Ho corretto il problema\r\n");
				}
				//Verifico presenza "+39"
				if(linea.substring(0, 3)=="+39"){
					Errori.push("Riga "+line+" Numero:"+linea+": +39 trovato. Ho corretto il problema\r\n");
					linea=linea.substring(3);
					counterBuoni+=1;
				}
				//Verifico presenza "0039"
				if(linea.substring(0, 4)=="0039"){
					Errori.push("Riga "+line+" Numero:"+linea+": 0039 trovato. Ho corretto il problema\r\n");
					linea=linea.substring(4);
					counterBuoni+=1;
				}				
				//Verifico che siano solo numeri
				var isnum = /^\d+$/.test(linea);
				if(!isnum){
					Errori.push("Riga "+line+" Numero:"+linea+" :numero telefonico contenente valori non numerici\r\n");
					continue; //salto la riga
				}
				if (!ControllaPrefisso(linea.substring(0, 3))){
					Errori.push("Riga "+line+" Numero:"+linea+" :Prefisso non consentito\r\n");
					continue; //salto la riga
				}
				RigheLette.push(linea);
			}
			if(Errori.length==0)
				alert("File Caricato.");	//Se ci sono stati errori li visualizzzo
			else
				alert("File Caricato. Errori:"+Errori.toString());	//Se ci sono stati errori li visualizzzo
			
		};
		reader.readAsText(uploadfile);
	}
	
	//ControllaPrefisso in Javascript
	function ControllaPrefisso(num){
		var prefissiConsentiti =["320","322","323","383","324","327","328","329","330","331","333","334","335","336","337","338","339","340","341","342","343","344","345","346","347","348","349","350","351","360","361","362","363","366","368","370","371","373","377","380","383","388","389","390","391","392","393","397"];
		for(var i = 0; i< prefissiConsentiti.length; i++){
			if(num.substring(0, 3)==prefissiConsentiti[i])
				return true;
		}
		return false;
	}
	</script>
</head>

<body>
	<?php 
	global $endPoint;

	echo display_error(); 
	echo display_message();
	
	$url = "https://secure.tcserver.it/cgi-bin/".$endPoint."?username=".$_SESSION['db_user']."&password=".$_SESSION['db_password']."&cmd=phoneservice_list";
	try{
		$sxml_PhoneService=APIFetch($url);
	}
	catch (Exception $ex)
	{
		logWrite("error","Eccezione nell\'APIFetch N°1 (phoneservice_list). Dettagli:".$ex->getMessage());
		echo($ex->getMessage());
		exit;
	}
	$logm="Risposta dell API (".$url.") : Code:".$ResultCode." CodeDescription:".$CodeDescription." Descrizione:".$MessageError;
	if($sxml_PhoneService!=false)
		logWrite("info","$logm");
	else{
		if($ResultCode=="-25")
			logWrite("debug","Errore nell\'APIFetch N°1 (phoneservice_list) IGNORATO codice:$ResultCode. Dettagli:$logm");
		else
			logWrite("error","Errore nell\'APIFetch N°1 (phoneservice_list)  codice:$ResultCode. Dettagli:$logm");
	}

	$url = "https://secure.tcserver.it/cgi-bin/".$endPoint."?username=".$_SESSION['db_user']."&password=".$_SESSION['db_password'];
	$cmd="&cmd=report_full";
	if (isset($_SESSION['bElencoCouponSoloAttivi']))
	{
		if(is_true($_SESSION['bElencoCouponSoloAttivi'])) 
			$cmd="&cmd=report_active";
	}
	$url.=$cmd;
	try{
		$sxml_Coupon=APIFetch($url);
	}
	catch (Exception $ex)
	{
		logWrite("error","Eccezione nell\'APIFetch N°2 (report_full). Dettagli:".$ex->getMessage());
		echo($ex->getMessage());
		exit;
	}
	$logm="Risposta dell API report (".$url."): Code:".$ResultCode." CodeDescription:".$CodeDescription." Descrizione:".$MessageError;
	//echo 'logm='.$logm;
	if($sxml_Coupon!=false)
		logWrite("info","$logm");
	else{
		if($ResultCode=="-19")
			logWrite("debug","Errore nell\'APIFetch N°2 (report_full) IGNORATO codice:$ResultCode. Dettagli:$logm");
		else
			logWrite("error","Errore nell\'APIFetch N°2 (report_full)  codice:$ResultCode. Dettagli:$logm");
	}

	?>	

	<h1 style="display:inline">Gestione Coupon per: &nbsp;<?php	if (isset($_SESSION['user']))
												echo $_SESSION['user']['nomevisualizzato'];?>
	</h1>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="home.php" style="color: green;">Torna alla Home</a>
	
	<div id="divform_NewCoupon" >
	<form id="frmNewCoupon" class="frmCommon" method="post" action="<?=($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
		<fieldset>
			<legend>Nuovo Coupon</legend>
		</fieldset>
		<fieldset>
		Numero Servizio
		<select id="phoneService" name="selectPhoneService" onchange="OnSelectionChange(this)">
		<?php 
			foreach($sxml_PhoneService->PhoneService as $PhoneService) 
				echo "<option value='".$PhoneService->Phone_service."'>" . $PhoneService->Phone_service . "</option>";
		?>
		</select>
		Codice Merchant
		<select id="merchantCode" name="selectMerchantCode" onChange="OnSelectionChange(this)">
		<?php 
			foreach($sxml_PhoneService->PhoneService as $PhoneService) 
				echo "<option value='". $PhoneService->Merchant_code."'>" . $PhoneService->Merchant_code . "</option>";
		?>
		</select>
	  </fieldset>
		<fieldset>
		<div class="input-group">
			<label>Codice Coupon</label>
			<script>
				if(<?php var_export($is_debug); ?>)
					document.write('<input id="coupon_code" type="text" name="cod_coupon" value="TEST" required>');
				else
					document.write('<input id="coupon_code" type="text" name="cod_coupon" required>');
			</script>
		</div>		
		<div>
			<label>Valido dal </label>
			<br>
			<input type="date" id="valido_dal_date" class="half_size" name="valido_dal_date">
			<input type="time" class="half_size" name="valido_dal_time" value="00:00">
			<br>
			<label> al </label>
			<br>
			<input type="date" id="valido_al_date" class="half_size" name="valido_al_date">
			<input type="time" class="half_size" name="valido_al_time" value="23:59">
			<script>
				//Setto le date dei due controlli data ad oggi
				let today = new Date().toISOString().substr(0, 10);
				document.querySelector("#valido_dal_date").value = today;
				document.querySelector("#valido_al_date").value = today;
			</script>
		</div>
		<h3>Tipo di Coupon</h3>
		<input id="rdb_standard" type="radio" name="coupontype" value="standard" checked="checked" onclick="mostraNascondiUpload();">Standard
<!--		<input id="rdb_custom" type="radio" name="coupontype" value="custom" onclick="mostraNascondiUpload();">Custom
		<input type="file" id="fileToUpload" name="fileToUpload" accept=".txt" style="visibility: hidden">
		<input type="submit" id="btnUpload" name="caricaFile" value="Upload File" style="visibility: hidden">-->
		
		<br>
		<input type="file" id="fileinput" accept=".txt" style="visibility: hidden">
		<input type="button" id="btnUploadJS" name="caricaFileJS" value="Upload File JS" style="visibility: hidden" onclick="caricaFileJavaScript();">
		
		<h3>Canale Coupon</h3>
		<input id="rdb_web" type="radio" name="couponchannel" value="web" onclick="changeCouponChannel(this);" checked="checked">Web
		<input id="rdb_telefonico" type="radio" name="couponchannel" value="telefonico" onclick="changeCouponChannel(this);">Telefonico
		<h3>Benefit</h3>
		<input id="rdb_omaggio_secondi" type="radio" name="benefit" value="omaggio_secondi"
			   onclick="omaggio_secondi_selected(this);" checked="checked">Omaggio Secondi
		<input id="rdb_sconto_importo" type="radio" name="benefit" value="sconto_importo" onclick="sconto_importo_selected(this);">Sconto Importo
		<input id="rdb_sconto_percentuale" type="radio" name="benefit" value="sconto_percentuale" onclick="sconto_percentuale_selected(this);">Sconto %
		<input type="text" id="nbenefit" name="nbenefit" placeholder="Omaggio Secondi" required>
		<br>
		<input type="submit" id="submitCoupon" name="submitCoupon" value="Conferma">
		</fieldset>
	</form>
	</div>

	<div id="divform_ModCoupon">
	<form id="frmModCoupon" class="frmCommon" method="post" action="<?=($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
		<input type="text" id="id_coupon_mc" style="display: none"> 
		<fieldset>
		<legend id="lgn_coupon_mc">Modifica Coupon</legend>
		</fieldset>
		<fieldset>
		Numero Servizio
		<select id="phoneService_mc" name="selectPhoneService_mc" onchange="OnSelectionChange_mc(this)">
		<?php 
			foreach($sxml_PhoneService->PhoneService as $PhoneService) 
				echo "<option value='".$PhoneService->Merchant_code."'>" . $PhoneService->Phone_service . "</option>";
		?>
		</select>
		Codice Merchant
		<select id="merchantCode_mc" name="selectMerchantCode_mc" onChange="OnSelectionChange_mc(this)">
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
			<input type="time" class="half_size" id="valido_dal_time_mc" value="01:00">
			<br>
			<label> al </label>
			<br>
			<input type="date" id="valido_al_date_mc" class="half_size" >
			<input type="time" class="half_size" id="valido_al_time_mc" value="02:59">
		</div>
		<h3>Tipo di Coupon</h3>
		<input id="rdb_standard_mc" type="radio" name="coupontype_mc" value="standard" checked="checked" >Standard
<!--		<input id="rdb_standard_mc" type="radio" name="coupontype_mc" value="standard" checked="checked" onclick="mostraNascondiUpload_mc();">Standard
		<input id="rdb_custom_mc" type="radio" name="coupontype_mc" value="custom" onclick="mostraNascondiUpload_mc();">Custom
		<input type="file" id="fileToUpload_mc" name="fileToUpload_mc" accept=".txt" style="visibility: hidden">
		<input type="submit" id="btnUpload_mc" name="caricaFile_mc" value="Upload File" style="visibility: hidden">-->
		<h3>Canale Coupon</h3>
		<input id="rdb_web_mc" type="radio" name="couponchannel_mc" value="standard" onclick="changeInputType(this);" checked="checked">Web
		<input id="rdb_telefonico_mc" type="radio" name="couponchannel_mc" value="custom" onclick="changeInputType(this);">Telefonico
		<h3>Benefit</h3>
		<input id="rdb_omaggio_secondi_mc" type="radio" name="benefit_mc" value="omaggio_secondi"
			   onclick="omaggio_secondi_selected(this);" checked="checked">Omaggio Secondi
		<input id="rdb_sconto_importo_mc" type="radio" name="benefit_mc" value="sconto_importo" onclick="sconto_importo_selected(this);">Sconto Importo
		<input id="rdb_sconto_percentuale_mc" type="radio" name="benefit_mc" value="sconto_percentuale" onclick="sconto_percentuale_selected(this);">Sconto %
		<input type="text" id="nbenefit_mc" name="nbenefit_mc" placeholder="Omaggio Secondi" required>
		<br>
		<input type="button" id="conferma_modifiche_mc" name="submitConferma_modifiche_mc" value="Conferma Modifiche" onclick="confermaModificheCoupon();">
		<input type="button" id="annulla_modifiche_mc" name="submit_annulla_modifiche_mc" value="Annulla" onclick="annullaModificheCoupon();">
		</fieldset>
	</form>
	</div>
	
	<div id="divElenco_Coupon" >
	<form id="frmElencoCoupon" class="frmCommon" method="post" action="<?=($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
		<h3>Elenco dei coupon:
		<button type="submit" id="btnCambiaFullActive" name="btnCambiaFullActive" onclick="showFullOrOnlyActive();"></button>
		</h3>
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
					<th>Modify</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>	
				<?php 
					if(isset($sxml_Coupon->Coupon)){
						foreach($sxml_Coupon->Coupon as $Coupon) :?>
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
					<button type="button" name="modifycoupon" 	
							onclick='switchNewCouponToModify(<?php echo json_encode($Coupon); ?>);'>
							MODIFY</button>
				</td>
				<td><button  type="button" name="deletecoupon" onclick="window.location='retailer_coupon.php?action=delete&id=<?php echo $Coupon->Coupon_ID;?>&merchant_code=<?php echo $Coupon->Merchant_code;?>&phone_service=<?php echo $Coupon->Phone_service;?>';" >DELETE</button></td>
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