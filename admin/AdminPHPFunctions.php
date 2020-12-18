<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//login dal pannello amministratore
if (isset($_POST['admin_username'])) {
	global $db;
	$username = e($_POST['admin_username']);

	$query = "SELECT * FROM retailers WHERE username='" . $username."' LIMIT 1";
	$results = mysqli_query($db, $query) or die(mysqli_error($db));
	if (mysqli_num_rows($results) == 1)  { // retailer found
		$logged_in_user = mysqli_fetch_assoc($results);
		if ($logged_in_user['user_type'] == 'retailer') {
			$_SESSION['user'] = $logged_in_user;
			$_SESSION['success']  = "Ora sei collegato come rivenditore";
			if (is_true($is_sandbox)){
					$_SESSION['db_user']= $sandbox_user;
					$_SESSION['db_password']= $sandbox_psw;
				}
			else{
				$_SESSION['db_user']= $logged_in_user['utente_DB_Telecash'];
				$_SESSION['db_password']= $logged_in_user['password_DB_Telecash'];
			}
			header('location: ../retailer/home.php');
		}
	}
	else
		array_push($errors, "Utente non presente");	
}

//Se l'utente ha premuto Edit sul form per modificare il record
//Questo metodo non tiene conto della password tanto sarà l'update a eseguire l'aggiornamento del record
if (isset($_GET['edit_retailer'])) {
	$id = $_GET['edit_retailer'];
	$update = true;
	$SQLCommand="SELECT * FROM retailers WHERE id=$id";
	//echo($SQLCommand);
	$record = mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
	
	if ($record->num_rows == 1 ) {
		$n = mysqli_fetch_array($record);
		$id= $n['id']; //Lo DEVO fare altrimenti l'id passato dalla GET non funziona (formati di dato diversi)
		$username = $n['username'];
		$user_type = $n['user_type'];
		//La password non mi serve
		$nomevisualizzato = $n['nomevisualizzato'];
		$utente_DB_Telecash = $n['utente_DB_Telecash'];
		$password_DB_Telecash = $n['password_DB_Telecash'];
	}
}

//Se è stato premuto il pulsante Update
if (isset($_POST['update_retailer'])) {
	if(CheckPassword())
	{
		$id = e($_POST['userid']);
		$username = e($_POST['username']);
		$user_type = e($_POST['user_type']);
		$password_1 = e($_POST['password_1']);
		$password_2 = e($_POST['password_2']);
		$nomevisualizzato = e($_POST['nomevisualizzato']);
		$utente_DB_Telecash = e($_POST['user_DB_Telecash']);
		$password_DB_Telecash = e($_POST['psw_DB_Telecash']);

		$SQLCommand="";
		if(empty($password_1))//Se non ho inserito una nuova password (basta controllare il primo campo, 							ChekPassword() fa il resto)
			$SQLCommand="UPDATE retailers SET 
						username=				'$username',
						user_type=				'$user_type',
						nomevisualizzato=		'$nomevisualizzato',
						utente_DB_Telecash=		'$utente_DB_Telecash',
						password_DB_Telecash=	'$password_DB_Telecash'
						WHERE id=				$id";
		else
		{
			$md5password = md5($password_1);
			
			$SQLCommand="UPDATE retailers SET 
			username=				'$username',
			user_type=				'$user_type',
			password=				'$md5password',
			nomevisualizzato=		'$nomevisualizzato',
			utente_DB_Telecash=		'$utente_DB_Telecash',
			password_DB_Telecash=	'$password_DB_Telecash'
			WHERE id=				$id";
		}
		//echo($SQLCommand);
		mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
		$_SESSION['message'] = "Utente Aggiornato!"; 
		//header('location: index.php');
	}
}

//Salva il retailers
if (isset($_POST['save_retailer'])) {
	if (isset($_POST['id'])) $id = e($_POST['id']);
	$username = e($_POST['username']);
	$user_type = e($_POST['user_type']);
	$password_1 = e($_POST['password_1']);
	$password_2 = e($_POST['password_2']);
	$nomevisualizzato = e($_POST['nomevisualizzato']);
	$utente_DB_Telecash = e($_POST['user_DB_Telecash']);
	$password_DB_Telecash = e($_POST['psw_DB_Telecash']);
	
	if(CheckPassword()){
		$idRetailer=GetRetailer($username);
		if($idRetailer==-1)
		{
	   		$user_type = e($_POST['user_type']);
		   	$password = md5($password_1);//encrypt the password before saving in the database
			$query = "INSERT INTO retailers (status, username, user_type, password, nomevisualizzato, utente_DB_Telecash, password_DB_Telecash) 
					  VALUES(1, '$username', '$user_type', '$password', '$nomevisualizzato', 
					  '$utente_DB_Telecash', '$password_DB_Telecash')";
			mysqli_query($db, $query) or die(mysqli_error($db));
			$_SESSION['message'] = "Nuovo Retailer Creato"; 
		}
		else
		{
	   		$user_type = e($_POST['user_type']);
		   	$password = md5($password_1);//encrypt the password before saving in the database
			$query = "UPDATE retailers SET status=1, username='$username', user_type='$user_type', password='$password', nomevisualizzato='$nomevisualizzato', 				utente_DB_Telecash='$utente_DB_Telecash', password_DB_Telecash='$password_DB_Telecash' WHERE id='$idRetailer'";
			mysqli_query($db, $query) or die(mysqli_error($db));
			$_SESSION['message'] = "Retailer ricreato";
		}
	}
	else
		array_push($errors, "Le password non coincidono");
}

//Se si vuole eliminare un retailers
if (isset($_POST['del_retailer'])) {
	$id = e($_POST['del_retailer']);
	//mysqli_query($db, "DELETE FROM retailers WHERE id=$id") or die("Errore durante la cancellazione del rivenditore:".mysqli_error($db));
	mysqli_query($db, "UPDATE retailers SET status=0 WHERE id=$id") or die("Errore durante la cancellazione del rivenditore:".mysqli_error($db));
	$_SESSION['message'] = "Rivenditore Cancellato!"; 
}

//Verifica l'esistenza di retailer con username specifico nel db retailers e ritorna l'id associato (-1 se non trovato)
function GetRetailer($username)
{
	global $db;
	$query = "SELECT * FROM retailers WHERE username='" . $username."'";

	$result = mysqli_query($db, $query) or die(mysqli_error($db));
	if(!isset($result))
		return -1;
	if (mysqli_num_rows($result) == 1) { 
		$retailerresult = mysqli_fetch_assoc($result);
		return $retailerresult['id'];
		}
	else
		return -1;
}