<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="../log4javascript.js"></script>
	<script type="text/javascript">
            var log = log4javascript.getDefaultLogger();
	</script>
	<script src='https://s3-us-west-2.amazonaws.com/s.cdpn.io/14082/FileSaver.js'></script>
	
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
	<title>PromoCash - Statistiche Coupon</title>
	<link rel="stylesheet" type="text/css" href="../style.css">
	<script>
	function OnSelectionChange(selectObj) {
            if(selectObj.id=='selectPhoneService'){
                document.getElementById('selectMerchantCode').selectedIndex = document.getElementById('selectPhoneService').selectedIndex;
                }
            else if(selectObj.id=='selectMerchantCode'){
                document.getElementById('selectPhoneService').selectedIndex = document.getElementById('selectMerchantCode').selectedIndex;
                }
	}
	
	function EsportaCLIsuFile(nelem)
	{
            var filename = document.getElementById("tabella_coupon").rows[1].cells[2].innerHTML; 

            var text="";
            for (i=1;i<=nelem;i++){
                var cellTemp= document.getElementById("tabella_coupon").rows[i].cells[4].innerHTML;
                if(cellTemp.substr(0,2)=="39")//se c'è un 39 davanti lo tolgo
                    cellTemp=cellTemp.substr(2);
                if(cellTemp.substr(0,1)!="3")//se non è un cellulare lo scarto
                    continue;
                else
                    text+= cellTemp + "\r\n"; 
            }

            var blob = new Blob([text], {type: "text/plain;charset=utf-8"});
            saveAs(blob, filename+".txt");
	}
	
	window.onload = function() {
    }

	
	function SelectElement(id, valueToSelect)
	{    
            var element = document.getElementById(id);
            element.value = valueToSelect;
	}
		
	function onchkTutti(checkBox){
            if (checkBox.checked==true){
                document.getElementById("selectPhoneService").disabled=true;
                document.getElementById("selectMerchantCode").disabled=true;
            }
            else{
                document.getElementById("selectPhoneService").disabled=false;
                document.getElementById("selectMerchantCode").disabled=false;
            }
	}
        
        //Eseguito al caricamento della pagina
	window.onload = function() {
            onchkTutti(document.getElementById("chkTuttiMerchant"));
	};
	
	</script>
</head>

<body>
	<?php
	$url = "https://secure.tcserver.it/cgi-bin/".$endPoint.
            "?username=".$_SESSION['db_user']."&password=".$_SESSION['db_password']."&cmd=phoneservice_list";
            if(isset($_SESSION['user']['username']))
                $url = $url.'&customer_limit='.$_SESSION['user']['username'];
            try{
                $sxml_PhoneService=APIFetch($url);
            }
            catch (Exception $ex)
            {
                logWrite("error","Eccezione nell\'APIFetch (report_full). Dettagli:".$ex->getMessage());
                echo($ex->getMessage());
                exit;
            }
            $logm="Risposta dell\'API (".$url.") : Code:".$ResultCode." CodeDescription:".$CodeDescription." Descrizione:".$MessageError;
            if($sxml_PhoneService!=false)
                    logWrite("info","$logm");
            else{
                if($ResultCode=="-25")
                    logWrite("debug","Errore nell\'APIFetch (phoneservice_list) IGNORATO codice:$ResultCode. Dettagli:$logm");
                else{
                    logWrite("error","Errore nell\'APIFetch (phoneservice_list)  codice:$ResultCode. Dettagli:$logm");
                exit;
                }
            }
	?>
	
	<h1 style="display:inline">Statistiche utilizzo coupons di: &nbsp;<?php	if (isset($_SESSION['user']))
												echo $_SESSION['user']['nomevisualizzato'];?>
	</h1>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="home.php" style="color: green;">Torna alla Home</a>
	
	<form id="frmStatisticheCoupon" class="frmCommon" method="post" action="<?=($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
	<div id="divMachera_Statistiche">
		Parametri ricerca
		<fieldset id="fld_MerchantServizio">
                    Numero Servizio
                    <select id="selectPhoneService" name="selectPhoneService" onchange="OnSelectionChange(this)">
                    <?php 
                        foreach($sxml_PhoneService->PhoneService as $PhoneService) 
                            echo "<option value='".$PhoneService->Phone_service."'>" . $PhoneService->Phone_service . "</option>";
                    ?>
                    </select>
                    Codice Merchant
                    <select id="selectMerchantCode" name="selectMerchantCode" onChange="OnSelectionChange(this)">
                    <?php 
                        foreach($sxml_PhoneService->PhoneService as $PhoneService) 
                            echo "<option value='". $PhoneService->Merchant_code."'>" . $PhoneService->Merchant_code . "</option>";
                    ?>
                    </select>
                    <label><input type="checkbox" id="chkTuttiMerchant" name="chkTuttiMerchant" value="Tutti" onchange="onchkTutti(this)">Tutti</label>
		</fieldset>
		<fieldset id=fld_Filtri>
                    <label>Nome Coupon</label>
                    <input type="text" id="nome_coupon" name="nome_coupon" placeholder="Filtro anche parziale (ignora maiuscole)" size="40">
                    <br>
                    <label>Valido dal </label>
                    <br>
                    <input type="date" id="valido_dal_date" class="half_size" name="valido_dal_date">
                    <input type="time" id="valido_dal_time" name="valido_dal_time" class="half_size"  value="00:00">
                    <br>
                    <label> al </label>
                    <br>
                    <input type="date" id="valido_al_date" class="half_size" name="valido_al_date">
                    <input type="time" id="valido_al_time" name="valido_al_time" class="half_size" value="23:59">
                    <script>
                        //Setto le date dei due controlli data ad oggi
                        let today = new Date().toISOString().substr(0, 10);
                        document.querySelector("#valido_dal_date").value = today;
                        document.querySelector("#valido_al_date").value = today;
                    </script>
                    <h3>Canale Coupon</h3>
                    <input id="rdb_web" type="radio" name="couponchannel" value="standard" onclick="changeCouponChannel(this);" checked="checked">Web
                    <input id="rdb_telefonico" type="radio" name="couponchannel" value="custom" onclick="changeCouponChannel(this);">Telefonico
		<br>
		</fieldset>
		<input type="submit" id="btn_filtraCoupon" name="btn_filtraCoupon" value="Filtra">
		<input name="_submit" type="hidden" value="_submit">
	</form>
	</div>
	
	<script>
	var nphoneService = document.getElementById("selectPhoneService").length;
	if(nphoneService==0)
            document.getElementById("btn_filtraCoupon").disabled = true;

	var FirstLoad= <?php	if (!isset($_POST['_submit'])) 
                                        echo json_encode(true); 
                                else 
                                        echo json_encode(false);?>;
	if(FirstLoad==false){
		//Rimetto i precedenti valori negli altri campi (se si tratta di un ricaricamento pagina)
		SelectElement("selectPhoneService", '<?php 
					  if (isset($_POST['selectPhoneService'])) echo $_POST['selectPhoneService']; 
					  else echo 'null';
					  ?>');
		SelectElement("selectMerchantCode", '<?php 
					  if (isset($_POST['selectMerchantCode'])) echo $_POST['selectMerchantCode']; 
					  else echo 'null';
					  ?>');
		document.getElementById("chkTuttiMerchant").checked = 
			<?php if(isset($_POST['chkTuttiMerchant'])) echo 'true'; else echo 'false'; ?> ;
		document.getElementById('nome_coupon').value='<?php 
					  if (isset($_POST['nome_coupon'])) echo $_POST['nome_coupon']; 
					  else echo 'null';
					  ?>';
		document.getElementById('valido_dal_date').value='<?php 
					  if (isset($_POST['valido_dal_date'])) echo $_POST['valido_dal_date']; 
					  else echo 'null';
					  ?>';
		document.getElementById('valido_al_date').value='<?php 
					  if (isset($_POST['valido_al_date'])) echo $_POST['valido_al_date']; 
					  else echo 'null';
					  ?>';
		document.getElementById('valido_dal_time').value='<?php 
					  if (isset($_POST['valido_dal_time'])) echo $_POST['valido_dal_time']; 
					  else echo 'null';
					  ?>';
		document.getElementById('valido_al_time').value='<?php 
					  if (isset($_POST['valido_al_time'])) echo $_POST['valido_al_time']; 
					  else echo 'null';
					  ?>';
		radiobtn = document.getElementById("rdb_web");
		radiobtn.checked=	<?php	if (isset($_POST['couponchannel']) && $_POST['couponchannel'] == 'standard')  
										echo json_encode(true); 
									else 
										echo json_encode(false); ?>;
		radiobtn2 = document.getElementById("rdb_telefonico");
		radiobtn2.checked=	<?php	if (isset($_POST['couponchannel']) && $_POST['couponchannel'] == 'custom') 									
										echo json_encode(true); 
									else 
										echo json_encode(false); ?>;
	}
	</script>
	
	<div id="divUtilizzoCoupon" >
		<h3>Statistica utilizzo coupon:	</h3>
		<table id="tabella_coupon" border='1'>
                    <thead>
                        <tr>
                            <th style="display: none">EmptyColumn</th>
                            <th>Coupon_code</th>
                            <th>Merchant_code</th>
                            <th>Phone_service</th>
                            <th>Cli</th>
                            <th>Divisa</th>
                            <th>Amount</th>
                            <th>CreditTime</th>
                            <th>Coupon_type</th>
                            <th>Coupon_value</th>
                            <th>IDTransaction</th>
                            <th>Coupon_Channel</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>	
                        <?php 
                        if (isset($_POST['btn_filtraCoupon'])){
                            $Coupon_Channel="";
                            switch($_POST['couponchannel'])
                            {
                                case 'standard':
                                    $Coupon_Channel="&coupon_channel=1";
                                    break;
                                case 'custom':
                                    $Coupon_Channel="&coupon_channel=2";
                                    break;
                            }
                            $StartDate=	"&start_date="	.date("d/m/Y", strtotime($_POST['valido_dal_date']));
                            $StartTime=	"&start_time="	.e($_POST['valido_dal_time']).":00";
                            $StopDate=	"&stop_date="	.date("d/m/Y", strtotime($_POST['valido_al_date']));
                            $StopTime=	"&stop_time="	.e($_POST['valido_al_time']).":00";
                        }

                        $sxml_Coupon = array();
                        if(isset($_POST['chkTuttiMerchant'])){	//se devo considerare Tutti i merchant
                            //Ciclo su tutti i servizi presenti ed eseguo il codice dell'else
                            foreach($sxml_PhoneService->PhoneService as $Elem){
                                try{
                                    $NewCoupon	= GetConsumedCouopon($Elem->Merchant_code,$Elem->Phone_service,$Coupon_Channel,$StartDate,$StartTime,$StopDate,$StopTime,"-23",true);
                                }
                                catch (Exception $ex)
                                {
                                    logWrite("error","Eccezione nella chiamata GetConsumedCouopon.Dettagli:".$ex->getMessage());
                                    echo($ex->getMessage());
                                    exit;
                                }

                                if (empty($NewCoupon)==false){//scarto gli array vuoti
                                    foreach($NewCoupon->Coupon as $Coupon) 
                                        array_push($sxml_Coupon,$Coupon);
                                }
                            }
                        }
                        else//Se è da considerare solo un merchant 
                        {
                            if (isset($_POST['btn_filtraCoupon'])){//Se ho premuto il pulsante Filtra
                                if(isset($_POST['selectMerchantCode'])&&isset($_POST['selectPhoneService'])){
                                    $SelectedMerchant=e($_POST['selectMerchantCode']);
                                    $SelectedPhoneService=e($_POST['selectPhoneService']);
                                    try{
                                        $NewCoupon	= GetCouoponStatistic($SelectedMerchant,$SelectedPhoneService,$Coupon_Channel,$StartDate,$StartTime,$StopDate,$StopTime,"-23",true);
                                    }
                                    catch (Exception $ex)
                                    {
                                        logWrite("error","Eccezione nella chiamata GetConsumedCouopon.Dettagli:".$ex->getMessage());
                                        echo($ex->getMessage());
                                        exit;
                                    }
                                }
                            }
                            else //Se non è stato premuto filtra uso i parametri indispensabili come da form default (data odierna , inizio giorno corrente, fine giorno corrente)
                            {
                                try{
                                    $NewCoupon	= GetCouoponStatistic($sxml_PhoneService->PhoneService[0]->Merchant_code,$sxml_PhoneService->PhoneService[0]->Phone_service,
                                                                            "&coupon_channel=1",    "&start_date="  .   date("d/m/Y"),"&start_time=".	"00:00:00",
                                                                            "&stop_date="   .   date("d/m/Y"),"&stop_time=" .   "23:59:00","-23",true);		
                                }
                                catch (Exception $ex)
                                {
                                    logWrite("error","Eccezione nella chiamata GetConsumedCouopon.Dettagli:".$ex->getMessage());
                                    echo($ex->getMessage());
                                    exit;
                                }
                            }
                            if (empty($NewCoupon)==false){//scarto gli array vuoti
                                foreach($NewCoupon->Coupon as $Coupon) 
                                    array_push($sxml_Coupon,$Coupon);
                            }

                            if($sxml_Coupon!=false)
                                logWrite("info","$logm");
                        }
//			EchoArrayOnDEBUG("sxml_Coupon: ",__LINE__,$sxml_Coupon);

                        if(!empty($sxml_Coupon)){
                            $arr_result = array_filter($sxml_Coupon, 'filter_callback'); ?>

                            <?php foreach($arr_result as $Coupon) :?>
                            <tr>
                            <td style="display: none">EmptyColumn</td>
                            <td><?php echo $Coupon->Coupon_code; ?></td>
                            <td><?php echo $Coupon->Merchant_code; ?></td>
                            <td><?php echo $Coupon->Phone_service; ?></td>
                            <td><?php echo $Coupon->Cli; ?></td>
                            <td><?php echo $Coupon->Divisa; ?></td>
                            <td><?php echo $Coupon->Amount; ?></td>
                            <td><?php echo $Coupon->CreditTime; ?></td>
                            <td><?php echo $Coupon->Coupon_type; ?></td>
                            <td><?php echo $Coupon->Coupon_value; ?></td>
                            <td><?php echo $Coupon->IDTransaction; ?></td>
                            <td><?php echo $Coupon->Coupon_channel; ?></td>
                            <td><?php echo $Coupon->Timestamp; ?></td>
                            </tr>
                            <?php endforeach; 
                        }?>
                    </tbody>
		</table>
		<h4>
                    Totale Elementi visualizzati:<?php if(isset($arr_result)) echo count($arr_result) ?>
                    <br>
                    <button type="button" name="EsportaNCell" onclick="EsportaCLIsuFile(<?php if(isset($arr_result)) echo count($arr_result) ?>)">Esporta CLI</button>
		</h4>
	</div>

</body>
</html>