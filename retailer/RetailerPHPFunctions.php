<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//Se si vuole eliminare un user
if (isset($_POST['del_user'])) {
	$id = e($_POST['del_user']);
	mysqli_query($db, "UPDATE users SET status=0 WHERE id=$id") or die("Errore durante la cancellazione del cliente:".mysqli_error($db));
	$_SESSION['message'] = "Utente Cancellato!"; 
}

//login dal pannello retailer
if (isset($_POST['enter_user'])) {
	global $db;
	$id = e($_POST['enter_user']);

	$query = "SELECT * FROM users WHERE id='" . $id."' LIMIT 1";
	$results = mysqli_query($db, $query) or die(mysqli_error($db));
	if (mysqli_num_rows($results) == 1) { // user found
		$logged_in_user = mysqli_fetch_assoc($results);
		$_SESSION['user'] = $logged_in_user;
		$_SESSION['success']  = "Ora sei collegato come cliente";

		if (is_true($is_sandbox)){
			$_SESSION['db_user']= $sandbox_user;
			$_SESSION['db_password']= $sandbox_psw;
		}
		else{
			$queryretailer = "SELECT * FROM retailers WHERE id=".$logged_in_user['retailer']." LIMIT 1";
			$resultsretailer = mysqli_query($db, $queryretailer) or die(mysqli_error($db));
			if (mysqli_num_rows($resultsretailer) == 1) { 
				$retailerresult = mysqli_fetch_assoc($resultsretailer);
				$_SESSION['db_user']= $retailerresult['utente_DB_Telecash'];
				$_SESSION['db_password']= $retailerresult['password_DB_Telecash'];
				}
			else{
				array_push($errors, "Impossibile trovare il rivenditore associato all'utente nella tabella retailers");
				$_SESSION['db_user']= "vuoto";
				$_SESSION['db_password']= "vuota";
			}
		}
		header('location: ../user/home.php');
		//header('location: ../index.php');
	}
	else
		array_push($errors, "Utente non presente");	
}

//Se è stato premuto il pulsante Update
if (isset($_POST['update_user'])) {
	if(CheckPassword())
	{
		$id = e($_POST['id']);
		$username = e($_POST['username']);
		$password_1 = e($_POST['password_1']);
		$password_2 = e($_POST['password_2']);
		$nomevisualizzato = e($_POST['nomevisualizzato']);
                $selectsolostandardedownloadcli = e($_POST['selectsolostandardedownloadcli']);

		$SQLCommand="";
		if(empty($password_1))//Se non ho inserito una nuova password 
			$SQLCommand="UPDATE users SET 
						username=				'$username',
						nomevisualizzato=		'$nomevisualizzato',
                                                only_export_cli=                $selectsolostandardedownloadcli
						WHERE id=				$id";
		else
		{
			$md5password = md5($password_1);
			
			$SQLCommand="UPDATE users SET 
			username=				'$username',
			password=				'$md5password',
			nomevisualizzato=                       '$nomevisualizzato',
                        only_export_cli=                        $selectsolostandardedownloadcli
			WHERE id=				$id";
		}
		//echo($SQLCommand); 
		mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
		$_SESSION['message'] = "Utente Aggiornato!"; 
		//header('location: index.php');
	}
}

if (isset($_POST['save_user'])) {
	$id = e($_POST['id']);
	$username = e($_POST['username']);
	$password_1 = e($_POST['password_1']);
	$password_2 = e($_POST['password_2']);
	$idretailer = e($_POST['idretailer']);
	$nomevisualizzato = e($_POST['nomevisualizzato']);
        $selectsolostandardedownloadcli = e($_POST['selectsolostandardedownloadcli']);
	
	if(CheckPassword()){
		$idUser=GetUser($username,$idretailer);
		if($idUser==-1){
			$password = md5($password_1);//encrypt the password before saving in the database
			$query = "INSERT INTO users (status, username, password, retailer, nomevisualizzato, only_export_cli) 
					  VALUES(1, '$username', '$password', '$idretailer', '$nomevisualizzato', $selectsolostandardedownloadcli)";
			mysqli_query($db, $query) or die(mysqli_error($db));
			$_SESSION['message'] = "Nuovo Utente Creato"; 
		}
		else
		{
			$password = md5($password_1);//encrypt the password before saving in the database
			$query = "UPDATE users SET status=1, username='$username', password='$password', retailer='$idretailer', nomevisualizzato='$nomevisualizzato', only_export_cli=$selectsolostandardedownloadcli WHERE id='$idUser'";
			mysqli_query($db, $query) or die(mysqli_error($db));
			$_SESSION['message'] = "Utente ricreato";
		}
	}
	else
		array_push($errors, "Le password non coincidono");
}

//Verifica l'esistenza di uno username nel db users  ritorna l'id associato (-1 se non trovato)
function GetUser($username,$idretailer)
{
	global $db;
	$query = "SELECT * FROM users WHERE username='".$username."' AND retailer='".$idretailer."'";

	$result = mysqli_query($db, $query) or die(mysqli_error($db));
	if(!isset($result))
		return -1;
	if (mysqli_num_rows($result) == 1) { 
		$userresult = mysqli_fetch_assoc($result);
		return $userresult['id'];
		}
	else
		return -1;
}