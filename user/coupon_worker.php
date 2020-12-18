<?php

include('../CommonPHPFunctions.php');
include('../CouponFunctions.php');

$ResultCode="";
$CodeDescription="";
$MessageError="";
$debug_worker=1;

/*
			Campo status tabella coupon_queue
						
						status=0		OK ( coupon inserito correttamente )
						status=1		Coupon ancora da creare
						status=2		Coupon in laorazione ( in stato di lock )
						status=negativi (vedi ApiFetch.php)		Coupon in errore restituiti dalle API
						status=99		Errore di comunicazione della funzione simplexml_load_file
						
			Obiettivo : Inserireire un SOLO COUPON
			
			1-	Connessione al DB local mysql 
			
			2-	leggo dal DB il la prima riga di un cuoupon non ancora lavorato ( status=1 ) e la metto in un array Locale 
				$id = fetch_array (id) ;
				Leggo tutti i parametri dalla riga della tabella coupon_queue e li salvo in piu' variabili locali che utilizzerò per 
				chiamare le api sotto 
			
			3-	Questo e' il lock del singola riga o tupla
				update coupon_queue set status = 2 where id = $id ;
				Lock della riga del db con un update status  -> ( update riga appena letta )
			

			// TrimmedElemListaCLIOK = mysql_riga_di_risultato 
*/
	$SQLCommand="SELECT * FROM coupon_queue WHERE status=1 LIMIT 1"; //Solo una riga
	$record = mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
	$tupla="";
	if ($record->num_rows > 0 ) 
		$tupla = mysqli_fetch_array($record);

	if ($tupla == FALSE)
	{
		WorkerLogWrite("info","Nessuna riga da processare!");
		die();
	}

	$id = $tupla['id'];
	$username = $tupla['username'];
	$password = $tupla['password'];
	$merchant_code = $tupla['merchant_code'];
	$phone_service = $tupla['phone_service'];
	$coupon_code = $tupla['coupon_code'];
	$coupon_type = $tupla['coupon_type'];
	$coupon_value = $tupla['coupon_value'];
	$coupon_channel = $tupla['coupon_channel'];
	$coupon_custom = $tupla['coupon_custom'];
	$start_date = $tupla['start_date'];
	$start_time = $tupla['start_time'];
	$stop_date = $tupla['stop_date'];
	$stop_time = $tupla['stop_time'];
        $unlimited = $tupla['unlimited'];
	$customer_limit = $tupla['customer_limit'];

	WorkerLogWrite("info","Inizio ad inserire il coupon con codice $coupon_code!");

	$SQLCommand="UPDATE coupon_queue SET status=2 WHERE id=$id";
	mysqli_query($db, $SQLCommand) or die(mysqli_error($db));

	try{
		$FindedCoupon=FindCoupon($username,$password,$customer_limit,$coupon_code,$merchant_code,$phone_service,$coupon_channel);
		if($FindedCoupon!=null){ 
			//Cancello eventuale coupon già esistente
			if(DeleteCoupon($username,$password,$FindedCoupon->Coupon_ID,
							$FindedCoupon->Merchant_code,$FindedCoupon->Phone_service,$customer_limit)){
				WorkerLogWrite("info","Vecchio coupon con codice $coupon_code cancellato correttamente");
			}
			else{
				WorkerLogWrite("error","Errore nella cancellazione del vecchio coupon con codice $coupon_code");
				// update coupon_queue set status = APIFetchRetVal where id = $id ;
				$SQLCommand="UPDATE coupon_queue SET status=$ResultCode WHERE id=$id";
				mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
			}
			//Resetto il coupon per far si che possa essere riutilizzato
			//-32 ignora errore API_no_coupon_find_for_reset 
			if(ResetCoupon($username,$password,$coupon_code,
						   $FindedCoupon->Merchant_code,$FindedCoupon->Phone_service,
						   $coupon_custom,DEFAULT_START_DATE,DEFAULT_START_TIME,DEFAULT_STOP_DATE,DEFAULT_STOP_TIME,$customer_limit,"-32")){
				WorkerLogWrite("info","Coupon con codice $coupon_code resettato");
			}
			else{
				WorkerLogWrite("error","Errore nel reset del coupon con codice $coupon_code");
				// update coupon_queue set status = APIFetchRetVal where id = $id ;
				$SQLCommand="UPDATE coupon_queue SET status=$ResultCode WHERE id=$id";
				mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
			}
		}//fine if($FindedCoupon!=null
		//Eseguo l'inserimento del/dei coupon custom 
		//I numeri vanno inseriti nel campo cupon_custom nel formato internazionale (es: 3933932112323)
		if(NewCoupon($username,$password,$merchant_code,$phone_service,
						$coupon_code,$coupon_type,$coupon_value,$coupon_channel,$coupon_custom,
						$start_date,$start_time,$stop_date,$stop_time,$customer_limit,$unlimited)){
			WorkerLogWrite("info","Coupon con codice $coupon_code inserito correttamente");
			// update coupon_queue set status = 0 where id = $id ;
			$SQLCommand="UPDATE coupon_queue SET status=0 WHERE id=$id";
			mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
		}
		else{
                        $parameter= "username='$username',password='$password',merchant_code='$merchant_code',phone_service='$phone_service',"
                                  . "coupon_code='$coupon_code',coupon_type='$coupon_type',coupon_value='$coupon_value',coupon_channel='$coupon_channel',"
                                  . "coupon_custom='$coupon_custom',start_date='$start_date',start_time='$start_time',stop_date='$stop_date',stop_time='$stop_time',"
                                  . "unlimited='$unlimited',customer_limit='$customer_limit'";
			WorkerLogWrite("error","Errore nell'inserimento del coupon con codice $coupon_code.");
                        WorkerLogWrite("error","Riga SQL: $SQLCommand");
                        WorkerLogWrite("error","Parametri: $parameter");
			// update coupon_queue set status = APIFetchRetVal where id = $id ;
			$SQLCommand="UPDATE coupon_queue SET status='$ResultCode', error_description='$CodeDescription', error_message='$MessageError' WHERE id=$id";
			mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
		}
	}
	catch (Exception $ex) //C'e' stato un errore di comunicazione API
	{
		WorkerLogWrite("error",$ex->getMessage());
		// update coupon_queue set status = 99 where id = $id ;
		$SQLCommand="UPDATE coupon_queue SET status=99, error_description='simplexml_load_file', error_message='Errore di comunicazione' WHERE id=$id";
		mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
	}

	function WorkerLogWrite($Type="info",$msg)			
	{
	    global $debug_worker;
	    if ( $debug_worker == 1 ){
		echo "\n<pre>\n";
		echo "Severity: $Type - \n";
		echo "Message: $msg\n";
		echo "</pre>\n\n";
	    }
	}
?>