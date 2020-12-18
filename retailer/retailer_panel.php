<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="../log4javascript.js"></script>
	<script type="text/javascript">
		var log = log4javascript.getDefaultLogger();
	</script>
	<script src="../jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="../CommonScriptFunctions.js"></script>

	<?php 
	include('../CommonPHPFunctions.php');
        include('RetailerPHPFunctions.php');

	if (!isLoggedIn()) {
		$_SESSION['msg'] = "Prima devi fare il login";
		header('location: ../index.php');
	}
	?>
	<title>PromoCash - Modifica utente</title>
	<link rel="stylesheet" type="text/css" href="../style.css">
	<style>
		.header {
			background: #003366;
		}
		button[name=register_btn] {
			background: #003366;
		}
	</style>
	<script>
	var customers;
	var updating=false;

	//ritorna il testo di un select control, es: var text = getSelectedText('test');
	function getSelectText(elementId) {
            var elt = document.getElementById(elementId);
            if (elt.selectedIndex == -1)
        	return null;
    	return elt.options[elt.selectedIndex].text;
	}

	//ritorna il value di un select control, es: var text = getSelectValue('test');
	function getSelectValue(elementId) {
            var elt = document.getElementById(elementId);
            if (elt.selectedIndex == -1)
                return null;
            return elt.options[elt.selectedIndex].value;
	}

        //riempe i dati della form in base al contenuto da editare (edit) selezionato 
	function editUser(id,source){
            updating=false;//in questo caso è sicuramente gia presente però seguo lo stesso il processo di ricerca
            //Cerco nella tabella degli utenti gia presenti se questo userName è gia presente nel DB
            for(i=0;i<document.getElementById("tab_utenti").rows.length;i++){
                //0 è la posizione  di Id, 1 di username, 2  di nomevisualizzato, 3 di only_export_cli
                var cella_id= document.getElementById("tab_utenti").rows[i].cells[0]; 
                var cella_username= document.getElementById("tab_utenti").rows[i].cells[1]; 
                var cella_nomevisualizzato= document.getElementById("tab_utenti").rows[i].cells[2];  
                var cella_onlyexportcli= document.getElementById("tab_utenti").rows[i].cells[3]; 
                if((cella_id!=null)&&(cella_id.innerHTML==id)){
                    document.getElementById('id').value= cella_id.innerHTML;
                    document.getElementById('username').value= cella_username.innerHTML;
                    document.getElementById('nomevisualizzato').value= cella_nomevisualizzato.innerHTML;
                    selectElement('selectsolostandardedownloadcli',cella_onlyexportcli.innerHTML);

                    updating=true;
                    break; //interrompo il for
                }
            }
            displayUpdateOrSaveButton(updating);
            if (source=='button')//se il click è stato fatto sul bottone
                    document.getElementById("tab_utenti").addEventListener("click", stopEvent, false);
	}
        
        //seleziona l'elemento di una select in base al testo
        function selectElement(id, valueToSelect) {    
            let element = document.getElementById(id);
            element.value = valueToSelect;
        }
        
	function stopEvent(ev){
		ev.stopPropagation();
	}
	
        //Quando si fa click sulla select dei clienti di telecash
	function onClickSelectCliTelecash(userName){
            updating=false;//in questo caso potrebbe non essere gia presente

            //Cerco nella tabella degli utenti gia presenti se questo userName è gia presente nel DB
            for(i=0;i<document.getElementById("tab_utenti").rows.length;i++){
                var cella_id= document.getElementById("tab_utenti").rows[i].cells[0]; 
                var cella_username= document.getElementById("tab_utenti").rows[i].cells[1];
                var cella_nomevisualizzato= document.getElementById("tab_utenti").rows[i].cells[2]; 
                var cella_onlyexportcli= document.getElementById("tab_utenti").rows[i].cells[3]; 

                if((cella_username!=null)&&(cella_username.innerHTML==userName)){
                    document.getElementById('id').value= cella_id.innerHTML;
                    document.getElementById('username').value= cella_username.innerHTML;
                    document.getElementById('nomevisualizzato').value= cella_nomevisualizzato.innerHTML;
                    selectElement('selectsolostandardedownloadcli',cella_onlyexportcli.innerHTML);

                    updating=true;
                    break; //interrompo il for
                }
            }
            //se non ho trovato l'elemento in quelli già inseriti comunque compilo i campi leggendo i dati contenuti nella select
            if(updating==false){ 
                var customersize = Object.keys(customers['Customer']).length;
                if (customers === undefined ||  customersize == 0)
                        return;
                else{
                    for(i=0;i<customersize;i++){
                       if(customers['Customer'][i].Customer_username==userName){
                            document.getElementById('username').value= customers['Customer'][i].Customer_username;
                            document.getElementById('nomevisualizzato').value= customers['Customer'][i].Customer;
                            selectElement('selectsolostandardedownloadcli',0);
                            break;
                       }
                    }
                }
            }
            displayUpdateOrSaveButton(updating);
	}
		
	//se updating=true visualizza update e nasconde save
	function displayUpdateOrSaveButton(updating){
            mostra('btn_updateuser',updating);
            mostra('btn_saveuser',!updating);
	}

	//Eseguito al caricamento della pagina
	window.onload = function() {
            displayUpdateOrSaveButton(updating);
	}

	</script>	
</head>
<body>
	<div class="header">
		<h2>Retailer - Pannello clienti</h2>
		<h2><?php echo $_SESSION['user']['nomevisualizzato']?></h2>
	
	<?php 
	$idretailer=$_SESSION['user']['id'];
	if 	(isset($idretailer))	
		$results = mysqli_query($db, "SELECT * FROM users WHERE retailer='$idretailer' AND status=1");
	else
		die(mysqli_error($db)); 
	?>
	</div>
	<a href="home.php" style="color: green;">Torna alla Home</a>
	<div id="div_tabella_Utenti">
		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" id="frmRetailers">
			<table id="tab_utenti" class="table-fill">
			<thead>
				<tr>
					<th class="text-left">Id</th>
					<th class="text-left">UserName</th>
					<th class="text-left">NomeVisualizzato</th>
                                        <th class="text-left" >only_export_cli</th>
				</tr>
			</thead>

			<tbody class="table-hover" style="cursor:pointer">
			<?php while ($row = mysqli_fetch_array($results)) { ?>
				<tr>
					<td><?php echo $row['id']; ?></td>
					<td><?php echo $row['username']; ?></td>
					<td><?php echo $row['nomevisualizzato']; ?></td>
                                        <td><?php echo $row['only_export_cli']; ?></td>
					<td><a onclick="editUser('<?php echo $row['id']; ?>','button');" style="color: darkgreen; text-decoration: underline;" >Edit</a></td>
					<td>
						<button type="submit" name="del_user" onclick="return confirm('Vuoi veramente cancellare questo utente?')" value="<?php echo $row['id']; ?>"> Delete</button>
					</td>
					<td> 
						<button type="submit" name="enter_user" value="<?php echo $row['id']; ?>"> Entra</button>
					</td>
				</tr>
			<?php } ?>
				<tr><td></td></tr>
			</tbody>
			</table>
		</form>
	</div>
	<?php
	global $endPoint;
	$url = "https://secure.tcserver.it/cgi-bin/".$endPoint."?username=".$_SESSION['db_user']."&password=".$_SESSION['db_password']."&cmd=customers_list";
	try{
		$sxml_cliTelecash=APIFetch($url);
	}
	catch (Exception $ex)
	{
		logWrite("error","Eccezione nell\'APIFetch (customers_list). Dettagli:".$ex->getMessage());
		echo($ex->getMessage());
		exit;
	}
	$logm="Risposta dell\'API (".$url.") : Code:".$ResultCode." CodeDescription:".$CodeDescription." Descrizione:".$MessageError;
	if($sxml_cliTelecash!=false){
		logWrite("info","$logm");
	}
	else{
		logWrite("error","Errore nell\'APIFetch (customers_list). Dettagli:$logm");
		exit;
	}
	$json_array = json_encode($sxml_cliTelecash);
	?>
	<script>
	//customers contiene l'array dei customers preso dal php
		customers 	= <?php echo $json_array; ?>;
	</script>	

	<div id="divform_utenti">
	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		
		<?php echo display_error(); ?>
		<?php echo display_message(); ?>

		<div class="input-group">
			<label>Richiama un cliente Telecash</label>
			<select id="cliTelecash" onchange="onClickSelectCliTelecash(getSelectValue('cliTelecash'))">
			<option value=''></option>
			<?php 
				foreach($sxml_cliTelecash->Customer as $Customer) 
					echo "<option value='".$Customer->Customer_username."'>" . $Customer->Customer . "</option>";
			?>
			</select>
		</div>
		<input type="hidden" id="id" name="id" value="<?php if (isset($id)) echo $id; ?>">
		<div class="input-group">
			<label>Username</label>
			<input type="text" id="username" name="username" value="<?php if (isset($username)) echo $username; ?>">
		</div>
		<div class="input-group">
			<label>Password</label>
			<input type="password" id="password_1" name="password_1">
		</div>
		<div class="input-group">
			<label>Confirm new password</label>
			<input type="password" id="password_2" name="password_2">
		</div>		
		<input type="hidden" id="idretailer" name="idretailer" value="<?php echo $idretailer; ?>">
		<div class="input-group">
			<label>Nome Visualizzato</label>
			<input type="text" id="nomevisualizzato" name="nomevisualizzato" value="<?php if (isset($nomevisualizzato)) echo $nomevisualizzato; ?>">
		</div>	
                <div class="input-group">
                    <label>Solo coupon standard + download Cli</label>
                    <select id="selectsolostandardedownloadcli" name="selectsolostandardedownloadcli" >
                    <option value='0' >0</option>
                    <option value='1' >1</option>
                    </select>
		</div>
		<div class="input-group">
			<button class="btn" type="submit" id="btn_updateuser" name="update_user" style="background: #556B2F;" >update</button>
			<button class="btn" type="submit" id="btn_saveuser" name="save_user" >Save</button>
		</div>
	</form>
	</div>
						
</body>
</html>