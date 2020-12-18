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
        include('UserPHPFunctions.php');

	if (!isLoggedIn()) {
		$_SESSION['msg'] = "Prima devi fare il login";
		header('location: ../index.php');
	}
	?>
	<meta charset="utf-8">
	<title>PromoCash - Coupon</title>
	<link rel="stylesheet" type="text/css" href="../style.css">
	<script>
	function OnSelectionChange(selectObj) {
		if(selectObj.id=='phoneService'){
			document.getElementById('merchantCode').selectedIndex = document.getElementById('phoneService').selectedIndex;
			}
		else if(selectObj.id=='merchantCode'){
			document.getElementById('phoneService').selectedIndex = document.getElementById('merchantCode').selectedIndex;
			}
		if(selectObj.id=='phoneService_mf'){
			document.getElementById('merchantCode_mf').selectedIndex = document.getElementById('phoneService_mf').selectedIndex;
			}
		else if(selectObj.id=='merchantCode_mf'){
			document.getElementById('phoneService_mf').selectedIndex = document.getElementById('merchantCode_mf').selectedIndex;
			}
		if(selectObj.id=='phoneService_lf'){
			document.getElementById('merchantCode_lf').selectedIndex = document.getElementById('phoneService_lf').selectedIndex;
			}
		else if(selectObj.id=='merchantCode_lf'){
			document.getElementById('phoneService_lf').selectedIndex = document.getElementById('merchantCode_lf').selectedIndex;
			}		
		if(selectObj.id=='tc_country_file'){
			document.getElementById('tc_country_cli').selectedIndex = document.getElementById('tc_country_file').selectedIndex;
			}
		else if(selectObj.id=='tc_country_cli'){
			document.getElementById('tc_country_file').selectedIndex = document.getElementById('tc_country_cli').selectedIndex;
			}		
	}	    

	function omaggio_secondi_selected(selectObj){
		nbenefit.placeholder="Omaggio Secondi";
	}

        function omaggio_secondi_percentuale_selected(selectObj){
		nbenefit.placeholder="Omaggio Secondi %";
	}        

	function sconto_importo_selected(selectObj){
		nbenefit.placeholder="Sconto sull'importo";
	}
	
	function sconto_percentuale_selected(selectObj){
		nbenefit.placeholder="Sconto in percentuale";
	}

	function mostraNascondiUploadFilter(checkBox){
		if (checkBox.checked==true)
			document.getElementById('fld_DaFile').style.visibility = 'visible';
		else
			document.getElementById('fld_DaFile').style.visibility = 'hidden';
	}
		
	function mostraNascondiCaricaBlacklist(checkBox){
		if (checkBox.checked==true)
			document.getElementById('blacklistToUpload').style.visibility = 'visible';
		else
			document.getElementById('blacklistToUpload').style.visibility = 'hidden';
	}
		
/**************Interazione con parte "DA MERCHANT"***********/
	function onchkDaMerchant(checkBox){
		if (checkBox.checked==true){
			document.getElementById('fld_DaMerchant').style.visibility = 'visible';
			document.getElementById('lbl_chkFiltroDate_mf').style.visibility = 'visible';//Visualizzo il check filtro data 
			document.getElementById("phoneService_mf").disabled=false;
			document.getElementById("merchantCode_mf").disabled=false;
		}
		else{
			document.getElementById('fld_DaMerchant').style.visibility = 'hidden';
			mostraFldDateFilter_mf(false);
			mostraFldMatchDateFilter_mf(false);
			document.getElementById("chkTuttiMerchant_mf").checked = false; //Tolgo la spunta dal chk Tutti che rimarrebbe selezionato
		}
		mostraChkFiltroData_mf(checkBox.checked);
	}
	function onchkTutti_mf(checkBox){
		if (checkBox.checked==true){
			mostraFldDateFilter_mf(false);
			mostraFldMatchDateFilter_mf(false);
			document.getElementById("phoneService_mf").disabled=true;
			document.getElementById("merchantCode_mf").disabled=true;
			var selectobject=document.getElementById("selectTypeOfCli_mf").getElementsByTagName("option"); 
			selectobject[7].disabled = true;
			document.getElementById("selectTypeOfCli_mf").selectedIndex = "0"; 
		}
		else{
			document.getElementById("phoneService_mf").disabled=false;
			document.getElementById("merchantCode_mf").disabled=false;
			var selectobject=document.getElementById("selectTypeOfCli_mf").getElementsByTagName("option"); 
			selectobject[7].disabled = false;
		}
		mostraChkFiltroData_mf(!checkBox.checked);
	}
	
	function mostraChkFiltroData_mf($mostra){
		if ($mostra==true)
		{
			document.getElementById('chkFiltroDate_mf').style.visibility = 'visible';//Mostro il filtro data
			document.getElementById('lbl_chkFiltroDate_mf').style.visibility = 'visible';//Nascondo il filtro data 
		}
		else{
			document.getElementById("chkFiltroDate_mf").checked = false; //Tolgo la spunta dal chk filtro data che rimarrebbe selezionato
			document.getElementById('chkFiltroDate_mf').style.visibility = 'hidden';//Nascondo il filtro data 
			document.getElementById('lbl_chkFiltroDate_mf').style.visibility = 'hidden';//Nascondo il filtro data 
		}
	}

	function onChkFiltroDate_mf(checkBox){
		mostraFldDateFilter_mf(checkBox.checked);
	}

	function onselectTypeOfCli_mfChange(selectObject){
		var value = selectObject.value;
		if(value=="7"){
			mostraFldDateFilter_mf(true);
			document.getElementById('chkFiltroDate_mf').disabled= true;
			document.getElementById("chkFiltroDate_mf").checked = true;
		}
		else{
			mostraFldDateFilter_mf(false);
			document.getElementById('chkFiltroDate_mf').disabled=false;
			document.getElementById("chkFiltroDate_mf").checked = false;
		}
		if(document.getElementById("chkTuttiMerchant_mf").checked == true)
			enableChkMatch(false);
		
	}

	function mostraFldDateFilter_mf($mostra){
		if ($mostra==true){
			document.getElementById('fld_Data_mf').style.visibility = 'visible';
			enableChkMatch(true);
		}
		else{
			document.getElementById('fld_Data_mf').style.visibility = 'hidden';
			enableChkMatch(false);
		}
	}

	function enableChkMatch($enable){
		if($enable==true){
			document.getElementById('chkMatch_mf').style.visibility = 'visible';
			document.getElementById('lblMatch_mf').style.visibility = 'visible';
		}else{
			document.getElementById('chkMatch_mf').style.visibility = 'hidden';
			document.getElementById('lblMatch_mf').style.visibility = 'hidden';
			document.getElementById("chkMatch_mf").checked = false; //Tolgo la spunta dal chk Match che rimarrebbe selezionato
			mostraFldMatchDateFilter_mf(false);
		}
	}
		
	function onChkMatch_mf(checkbox){
		mostraFldMatchDateFilter_mf(checkbox.checked);
	}

	function mostraFldMatchDateFilter_mf($mostra){
		if ($mostra==true)
			document.getElementById('fld_Match_mf').style.visibility = 'visible';
		else{
			document.getElementById('fld_Match_mf').style.visibility = 'hidden';
		}
	}	
/**************FINE Interazione con parte "DA MERCHANT"***********/
/**************Interazione con parte "DA LEADS"***********/
	function onChkDaLeads(checkBox){
		if (checkBox.checked==true){
			document.getElementById('chkFiltroData_lf').style.visibility = 'visible';
			document.getElementById('lbl_chkFiltroData_lf').style.visibility = 'visible';
			document.getElementById('fld_DaLeads').style.visibility = 'visible';
		}
		else{
			document.getElementById('fld_DaLeads').style.visibility = 'hidden';
			document.getElementById("chkFiltroData_lf").checked = false;
			document.getElementById('chkFiltroData_lf').style.visibility = 'hidden';
			document.getElementById('lbl_chkFiltroData_lf').style.visibility = 'hidden';
			onChkFiltroData_lf(checkBox);
		}
	}

	function onchkTutti_lf(checkBox){
		if (checkBox.checked==true){
			document.getElementById('fld_Data_lf').style.visibility = 'hidden';
			document.getElementById("chkFiltroData_lf").checked = false;
			document.getElementById('chkFiltroData_lf').style.visibility = 'hidden';
			document.getElementById('lbl_chkFiltroData_lf').style.visibility = 'hidden';
		}
		else{
			document.getElementById('chkFiltroData_lf').style.visibility = 'visible';
			document.getElementById('lbl_chkFiltroData_lf').style.visibility = 'visible';
		}
	}		
	
	function onChkFiltroData_lf(checkBox){
		if (checkBox.checked==true){
			document.getElementById('fld_Data_lf').style.visibility = 'visible';
		}
		else{
			document.getElementById('fld_Data_lf').style.visibility = 'hidden';
			document.getElementById("chkFiltroData_lf").checked = false; //Tolgo la spunta dal chk data che rimarrebbe selezionato
		}
	}
		
	function onChkDaCli(checkBox){
		if (checkBox.checked==true){
			document.getElementById('fld_DaCli').style.visibility = 'visible';
		}
		else{
			document.getElementById('fld_DaCli').style.visibility = 'hidden';
		}
	}		

/**************FINE Interazione con parte "DA LEADS"***********/

        var only_export_cli=<?php echo $_SESSION['user']['only_export_cli']; ?>; //Indica se l'utente che ha effettuato il login ha solo il potere di esportare
	function rdb_custom_Click(checkbox){
			mostra('div_NewCouponCustom_Data',true);
			document.getElementById('coupon_code').disabled = true;
			document.getElementById('coupon_code').value="";
                        
                        if(only_export_cli)
                            document.getElementById('submitCoupon').style.visibility = "hidden";
	}
	
	function rdb_standard_Click(){
		mostra('div_NewCouponCustom_Data',false);
		document.getElementById('coupon_code').disabled = false;
		if(<?php var_export($is_debug); ?>)
			document.getElementById('coupon_code').value="TEST";
		else
			document.getElementById('coupon_code').value="";

                if(only_export_cli)
                        document.getElementById('submitCoupon').style.visibility = "visible";

    }

	function onChangeChannel(selectObj){
		var coupon_code=document.getElementById('coupon_code');

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

	//Eseguito al caricamento della pagina
	window.onload = function() {
		//alert("Eseguo evento onload");
		mostra('frmNewCoupon',true); //Visualizzo il form di immissione di un nuovo coupon
	};

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
	<h1 style="display:inline;">Gestione Coupon per: &nbsp;
            <?php	
            if (isset($_SESSION['user']))
                echo $_SESSION['user']['nomevisualizzato'];	
            ?>
	</h1>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="home.php" style="color: green;">Torna alla Home</a>
	</div>
	
	
	<div id="divCoupon">
	<form id="frmNewCoupon" class="frmCommon" method="post" action="<?=($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
		<div id="div_NewCoupon_Data">
			<fieldset>
				<legend>Nuovo Coupon</legend>
			</fieldset>
			<fieldset>
			Numero Servizio
			<select id="phoneService" name="selectPhoneService" onchange="OnSelectionChange(this);">
			<?php 
				foreach($sxml_PhoneService->PhoneService as $PhoneService) 
					echo "<option value='".$PhoneService->Phone_service."'>" . $PhoneService->Phone_service . "</option>";
			?>
			</select>
			Codice Merchant
			<select id="merchantCode" name="selectMerchantCode" onChange="OnSelectionChange(this);">
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
					if(<?php var_export($is_sandbox); ?>)
						document.write('<input type="text" id="coupon_code" name="cod_coupon" value="TEST" required>');
					else
						document.write('<input type="text" id="coupon_code" name="cod_coupon" required>');
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
                        <br>
                        <div>
                            <label style="font-size: 16px;"><input type="checkbox" id="chkUnlimited" name="chkUnlimited" value="chkUnlimited" ">UNLIMITED</label>
                        </div>
                        <div>
                            <h3>Tipo di Coupon</h3>
                            <input id="rdb_standard" type="radio" name="coupontype" value="standard" checked="checked" onclick="rdb_standard_Click();">Standard
                            <?php
                                if(!is_true($only_standard_coupon)){
                                    if (!is_true($_SESSION['user']['only_export_cli']))
                                        echo '<input id="rdb_custom" type="radio" name="coupontype" value="custom" onclick="rdb_custom_Click(this);" >Custom';
                                    else
                                        echo '<input id="rdb_custom" type="radio" name="coupontype" value="custom" onclick="rdb_custom_Click(this);" >Scarica File Cli';
                                }
                            ?>
                        </div>
                        <div>
                            <h3>Canale Coupon</h3>
                            <input id="rdb_web" type="radio" name="couponchannel" value="web" onclick="onChangeChannel(this);" checked="checked">Web
                            <input id="rdb_telefonico" type="radio" name="couponchannel" value="telefonico" onclick="onChangeChannel(this);">Telefonico
                        </div>
                        <div>
                            <h3>Benefit</h3>
                            <input id="rdb_omaggio_secondi" type="radio" name="benefit" value="omaggio_secondi"
                                       onclick="omaggio_secondi_selected(this);" checked="checked">Omaggio Secondi
                            <input id="rdb_omaggio_secondi_percentuale" type="radio" name="benefit" value="omaggio_secondi_percentuale" onclick="omaggio_secondi_percentuale_selected(this);">Omaggio Secondi %
                            <input id="rdb_sconto_importo" type="radio" name="benefit" value="sconto_importo" onclick="sconto_importo_selected(this);">Sconto Importo
                            <input id="rdb_sconto_percentuale" type="radio" name="benefit" value="sconto_percentuale" onclick="sconto_percentuale_selected(this);">Sconto %
                            <input type="text" id="nbenefit" name="nbenefit" placeholder="Omaggio Secondi" onkeypress="return isNumberKey(event);" required>
                        </div>
                        <div>
                            <input type="submit" id="submitCoupon" name="submitCoupon" value="Conferma">
                        </div>
			</fieldset>
		</div>
		
		<script>
			var nphoneService = document.getElementById("phoneService").length;
			if(nphoneService==0)
				document.getElementById("submitCoupon").disabled = true;
		</script>
		
                
                
                
                <div id="div_NewCouponCustom_Data">
                
                <?php
                if (!is_true($_SESSION['user']['only_export_cli']))
                    echo'   <fieldset>COUPON CUSTOM
                            <br>
                            <label><input type="checkbox" name="chkDaFile" value="DaFile" onchange="mostraNascondiUploadFilter(this);">Da File</label>';
                else 
                    echo'<fieldset>SELEZIONA I FILTRI PER IL DOWNLOAD DEI CLI ';
                ?>

<!--		DA FILE 	-->
                        <fieldset id=fld_DaFile style="visibility: hidden;">
                                <label for="tc_country_file" id="lbl_tc_country_file" >Prefisso Paese</label>
                                <select name="country_file" tabindex="0" id="tc_country_file" onchange="OnSelectionChange(this)"><option value="39">Italia +39</option><option value="93">Afghanistan +93</option><option value="54">Argentina +54</option><option value="61">Australia +61</option><option value="43">Austria +43</option><option value="32">Belgium +32</option><option value="55">Brazil +55</option><option value="56">Chile +56</option><option value="86">China +86</option><option value="57">Colombia +57</option><option value="53">Cuba +53</option><option value="45">Denmark +45</option><option value="20">Egypt +20</option><option value="33">France +33</option><option value="49">Germany +49</option><option value="30">Greece +30</option><option value="36">Hungary +36</option><option value="91">India +91</option><option value="62">Indonesia +62</option><option value="87">Inmarsat +87</option><option value="98">Iran +98</option><option value="81">Japan +81</option><option value="77">Kazakhstan +77</option><option value="82">Korea South +82</option><option value="60">Malaysia +60</option><option value="52">Mexico +52</option><option value="95">Myanmar +95</option><option value="31">Netherlands +31</option><option value="64">New Zealand +64</option><option value="47">Norway +47</option><option value="92">Pakistan +92</option><option value="51">Peru +51</option><option value="63">Philippines +63</option><option value="48">Poland +48</option><option value="40">Romania +40</option><option value="7">Russia +7</option><option value="79">Russia Mobile +79</option><option value="65">Singapore +65</option><option value="27">South Africa +27</option><option value="34">Spain +34</option><option value="94">Sri Lanka +94</option><option value="46">Sweden +46</option><option value="41">Switzerland +41</option><option value="66">Thailand +66</option><option value="90">Turkey +90</option><option value="44">United Kingdom +44</option><option value="1">USA +1</option><option value="58">Venezuela +58</option><option value="84">Vietnam +84</option></select>
                                <input type="file" id="fileToUpload" name="fileToUpload" accept=".txt" >
                                <br>

                                <label><input type="checkbox" name="chkFileBlackList" value="FileBlackList" onchange="mostraNascondiCaricaBlacklist(this);" >File Black-List</label>
                                <input type="file" id="blacklistToUpload" name="blacklistToUpload[]" accept=".txt" style="visibility: hidden;" multiple/>
                        </fieldset>

<!--		DA MERCHANT	-->
                        <label><input type="checkbox" name="chkDaMerchant" value="DaMerchant" onchange="onchkDaMerchant(this);">Da Merchant</label>
                        <fieldset id=fld_DaMerchant style="visibility: hidden;">
                                <label for="phoneService_mf" id="lbl_phoneService_mf" style="float:left;">Numero Servizio</label>
                                <select id="phoneService_mf" name="selectPhoneService_mf" onchange="OnSelectionChange(this);" style="float:left;">
                                <?php 
                                        foreach($sxml_PhoneService->PhoneService as $PhoneService) 
                                                echo "<option value='".$PhoneService->Phone_service."'>" . $PhoneService->Phone_service . "</option>";
                                ?>
                                </select>
                                <label for="merchantCode_mf" id="lbl_merchantCode_mf" style="float:left;">Codice Merchant</label>
                                <select id="merchantCode_mf" name="selectMerchantCode_mf" onChange="OnSelectionChange(this);" style="float:left;">
                                <?php 
                                        foreach($sxml_PhoneService->PhoneService as $PhoneService) 
                                                echo "<option value='". $PhoneService->Merchant_code."'>" . $PhoneService->Merchant_code . "</option>";
                                ?>
                                </select>
                                <label><input type="checkbox" id="chkTuttiMerchant_mf" name="chkTuttiMerchant_mf" value="Tutti" onchange="onchkTutti_mf(this);">Tutti</label>
                                <br class="clear">
                                <select id="selectTypeOfCli_mf" name="selectTypeOfCli_mf" onchange="onselectTypeOfCli_mfChange(this);">
                                        <option value="0">CLI che NON hanno eseguito nessuna transazione</option>
                                        <option value="1">CLI che hanno fatto una qualsiasi transazione OK(OK)</option>
                                        <option value="2">CLI che hanno eseguito almeno una transazione a NON a buon fine (KO)</option>
                                        <option value="3">CLI che hanno usufruito di una qualsiasi promozione</option>
                                        <option value="4">CLI che hanno eseguito una qualsiasi transazione (OK-KO)</option>
                                        <option value="5">CLI che non hanno transato negli ultimi 30 giorni, ma che hanno fatto almento una transazione (o piu') nei 2 mesi precedenti.</option>
                                        <option value="6">CLI che hanno fatto transazioni Paypal, per generare promozioni con coupon Paypal</option>
                                        <option value="7">CLI classificati come TOP 12, lista dei CLI che hanno transato almeno 20EU negli ultimi 3 mesi.</option>
                                        <option value="8">CLI che al 15 del mese hanno transato meno della loro media riferita ai 2 mesi precedenti </option>
                                        <option value="9">CLI che hanno transato negli ultimi 30 giorni, ma che non hanno fatto almeno una transazione (o piu') nei 2 mesi precedenti.</option>
                                        <option value="10">CLI che hanno eseguito almeno una transazione telefonica a buon fine (OK).</option>
                                        <option value="11">CLI che hanno eseguito almeno una transazione PAYPAL nel periodo di ricerca indicato.</option>
                                        <option value="12">CLI che hanno eseguito la prima transazione a pagamento nel periodo di ricerca indicato.</option>
                                        <option value="13">CLI che non hanno mai eseguito una transazione web nel periodo di ricerca</option>
                                </select>
                                <br class="clear">
                                <label id="lbl_chkFiltroDate_mf">
                                        <input type="checkbox" id="chkFiltroDate_mf" name="chkFiltroDate_mf" value="DateFilter"	onchange="onChkFiltroDate_mf(this);" >
                                        Filtro Data
                                </label>
                                <fieldset id=fld_Data_mf style="visibility: hidden">
                                        <label >Dal:</label>
                                        <input type="date" id="dal_data_filtro1_mf" name="dal_data_filtro1_mf">
                                        <input type="time" id="dal_time_filtro1_mf" name="dal_time_filtro1_mf" value="00:00">
                                        <label> al </label>
                                        <input type="date" id="al_data_filtro1_mf" name="al_data_filtro1_mf">
                                        <input type="time" id="al_time_filtro1_mf" name="al_time_filtro1_mf" value="23:59">
                                </fieldset>
                                <label id="lblMatch_mf" style="visibility: hidden;">
                                        <input type="checkbox" id="chkMatch_mf" name="chkMatch_mf" value="Match" style="visibility: hidden;" onchange="onChkMatch_mf(this);">
                                        Match (la 1° - la 2°)
                                </label>
                                <fieldset id=fld_Match_mf style="visibility: hidden;">
                                        <select id="selectTypeOfCliMatch_mf" name="selectTypeOfCliMatch_mf">
                                                <option value="0">CLI che NON hanno eseguito nessuna transazione</option>
                                                <option value="1">CLI che hanno fatto una qualsiasi transazione OK(OK)</option>
                                                <option value="2">CLI che hanno eseguito almeno una transazione a NON a buon fine (KO)</option>
                                                <option value="3">CLI che hanno usufruito di una qualsiasi promozione</option>
                                                <option value="4">CLI che hanno eseguito una qualsiasi transazione (OK-KO)</option>
                                                <option value="5">CLI che non hanno transato negli ultimi 30 giorni, ma che hanno fatto almento una transazione (o piu') nei 2 mesi precedenti.</option>
                                                <option value="6">CLI che hanno fatto transazioni Paypal, per generare promozioni con coupon Paypal</option>
                                                <option value="7">CLI classificati come TOP 12, lista dei CLI che hanno transato almeno 20EU negli ultimi 3 mesi.</option>
                                                <option value="8">CLI che al 15 del mese hanno transato meno della loro media riferita ai 2 mesi precedenti </option>
                                                <option value="9">CLI che hanno transato negli ultimi 30 giorni, ma che non hanno fatto almeno una transazione (o piu') nei 2 mesi precedenti.</option>
                                                <option value="10">CLI che hanno eseguito almeno una transazione telefonica a buon fine (OK).</option>
                                                <option value="11">CLI che hanno eseguito almeno una transazione PAYPAL nel nel periodo di ricerca indicato.</option>
                                                <option value="12">CLI che hanno eseguito la prima transazione a pagamento nel periodo di ricerca indicato.</option>
                                                <option value="13">CLI che non hanno mai eseguito una transazione web nel periodo di ricerca</option>
                                        </select>
                                        <br>
                                        <label>Dal: </label>
                                        <input type="date" id="dal_data_filtro2_mf" name="dal_data_filtro2_mf">
                                        <input type="time" id="dal_time_filtro2_mf" name="dal_time_filtro2_mf" value="00:00">
                                        <label> al </label>
                                        <input type="date" id="al_data_filtro2_mf" name="al_data_filtro2_mf">
                                        <input type="time" id="al_time_filtro2_mf" name="al_time_filtro2_mf" value="23:59">
                                        <script>
                                                //Setto le date dei controlli data ad oggi
                                                if(document.querySelector("#dal_data_filtro1_mf")!=null) 	document.querySelector("#dal_data_filtro1_mf").value= today;
                                                if(document.querySelector("#al_data_filtro1_mf")!=null) 	document.querySelector("#al_data_filtro1_mf").value= today;
                                                if(document.querySelector("#dal_data_filtro2_mf")!=null)	document.querySelector("#dal_data_filtro2_mf").value= today;
                                                if(document.querySelector("#al_data_filtro2_mf")!=null)		document.querySelector("#al_data_filtro2_mf").value= today;
                                        </script>
                                </fieldset>
                        </fieldset>

<!--		DA LEADS	-->
                        <label><input type="checkbox" name="chkDaLeads" value="DaLeads" onchange="onChkDaLeads(this);">Da Leads</label>
                        <fieldset id=fld_DaLeads style="visibility: hidden;">
                                Numero Servizio
                                <select id="phoneService_lf" name="selectPhoneService_lf" onchange="OnSelectionChange(this);">
                                <?php 
                                        foreach($sxml_PhoneService->PhoneService as $PhoneService) 
                                                echo "<option value='".$PhoneService->Phone_service."'>" . $PhoneService->Phone_service . "</option>";
                                ?>
                                </select>
                                Codice Merchant
                                <select id="merchantCode_lf" name="selectMerchantCode_lf" onChange="OnSelectionChange(this);">
                                <?php 
                                        foreach($sxml_PhoneService->PhoneService as $PhoneService) 
                                                echo "<option value='". $PhoneService->Merchant_code."'>" . $PhoneService->Merchant_code . "</option>";
                                ?>
                                </select>
                                <label>
                                        <input type="checkbox" name="chkTuttiMerchant_lf" value="Tutti" onchange="onchkTutti_lf(this);">Tutti
                                </label>
                                <br class="clear">
                                <label id="lbl_chkFiltroData_lf">
                                        <input type="checkbox" id="chkFiltroData_lf" name="chkFiltroData_lf" value="DateFilter"	onchange="onChkFiltroData_lf(this);" >
                                        Filtro Data
                                </label>
                                <br>
                                <fieldset id=fld_Data_lf style="visibility: hidden;">
                                        <label>Intervallo date di ricerca:</label>
                                        <br>
                                        <label >Dal: </label>
                                        <input type="date" id="dal_data_filtro1_lf" name="dal_data_filtro1_lf">
                                        <input type="time" id="al_data_filtro1_lf" name="al_data_filtro1_lf" value="00:00">
                                        <label> al </label>
                                        <input type="date" id="dal_data_filtro2_lf" name="dal_data_filtro2_lf">
                                        <input type="time" id="al_data_filtro2_lf" name="al_data_filtro2_lf" value="23:59">
                                        <script>
                                                //Setto le date dei due controlli data ad oggi
                                                if(document.querySelector("#dal_data_filtro1_lf")!=null)	document.querySelector("#dal_data_filtro1_lf").value = today;
                                                if(document.querySelector("#dal_data_filtro2_lf")!=null) 	document.querySelector("#dal_data_filtro2_lf").value = today;
                                        </script>
                                </fieldset>
                        </fieldset>

<!--		DA CLI	   -->
                <?php
                if (!is_true($_SESSION['user']['only_export_cli']))
                    echo'   <label><input type="checkbox" name="chkDaCli" value="DaCli" onchange="onChkDaCli(this);">Da Cli</label>';
                ?>
                        <fieldset id=fld_DaCli style="visibility: hidden;">
                                <label for="tc_country_cli" id="lbl_tc_country_cli" >Prefisso Paese</label>
                                <select name="country_cli" tabindex="0" id="tc_country_cli" onchange="OnSelectionChange(this);"><option value="39">Italia +39</option><option value="93">Afghanistan +93</option><option value="54">Argentina +54</option><option value="61">Australia +61</option><option value="43">Austria +43</option><option value="32">Belgium +32</option><option value="55">Brazil +55</option><option value="56">Chile +56</option><option value="86">China +86</option><option value="57">Colombia +57</option><option value="53">Cuba +53</option><option value="45">Denmark +45</option><option value="20">Egypt +20</option><option value="33">France +33</option><option value="49">Germany +49</option><option value="30">Greece +30</option><option value="36">Hungary +36</option><option value="91">India +91</option><option value="62">Indonesia +62</option><option value="87">Inmarsat +87</option><option value="98">Iran +98</option><option value="81">Japan +81</option><option value="77">Kazakhstan +77</option><option value="82">Korea South +82</option><option value="60">Malaysia +60</option><option value="52">Mexico +52</option><option value="95">Myanmar +95</option><option value="31">Netherlands +31</option><option value="64">New Zealand +64</option><option value="47">Norway +47</option><option value="92">Pakistan +92</option><option value="51">Peru +51</option><option value="63">Philippines +63</option><option value="48">Poland +48</option><option value="40">Romania +40</option><option value="7">Russia +7</option><option value="79">Russia Mobile +79</option><option value="65">Singapore +65</option><option value="27">South Africa +27</option><option value="34">Spain +34</option><option value="94">Sri Lanka +94</option><option value="46">Sweden +46</option><option value="41">Switzerland +41</option><option value="66">Thailand +66</option><option value="90">Turkey +90</option><option value="44">United Kingdom +44</option><option value="1">USA +1</option><option value="58">Venezuela +58</option><option value="84">Vietnam +84</option></select>
                                <label> Numero Telefono CLI</label>
                                <input type="text" id="numero_cli" name="numero_cli" placeholder="Ins. Num Tel" onkeypress="return isNumberKey(event);">
                        </fieldset>

                        <input type="submit" id="salva_su_file" name="salva_su_file" value="Salva su File" formnovalidate>
                </fieldset> <!--<fieldset>COUPON CUSTOM-->
                </div>	<!--<div id="div_NewCouponCustom_Data">-->
	</form>
	</div>	<!--<div id="divCoupon">-->

	<div style="clear:both;"></div> 

	<input type="button" id="elenco_coupon" name="elenco_coupon" value="Coupon Attivi" onclick="window.open('elenco_coupon.php'); return false;">
	<?php
	if( (!is_true($only_standard_coupon)) && (!is_true($_SESSION['user']['only_export_cli'])) ):
		echo '<input type="button" id="stato_coda" name="stato_coda" value="Stato Coda" onclick="window.open(\'queue_status.php\'); return false;">';
	endif;	
	?>
	
	<script>
//		//Per il debug metto anche un pulsante che cancella tutti i coupon
//		if(IS_DEBUG){
//			document.write("<input type='button' value='Elimina Tutti I Coupon' onclick='gotodelete()'>");
//		}
		function goToDeleteStandard()
		{
			var r = confirm("Sei sicuro di voler eliminare TUTTI i coupon STANDARD?");
    		if (r == true) 
        		window.location='coupon.php?action=elimina_tutti_coupon_standard';
		}
		function goToDeleteCustom()
		{
			var r = confirm("Sei sicuro di voler eliminare TUTTI i coupon CUSTOM?");
    		if (r == true) 
				window.location='coupon.php?action=elimina_tutti_coupon_custom';
		}
		function goToDeleteAll()
		{
			var r = confirm("Sei sicuro di voler eliminare TUTTI i coupon inseriti?");
    		if (r == true) 
				window.location='coupon.php?action=elimina_tutti_coupon';
		}		
	</script>
</body>
</html>