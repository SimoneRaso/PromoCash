<?php
include('APIFetch.php');

//COSTANTI
define('DEFAULT_START_DATE', '2014-12-28');//Formato aaaa-mm-gg
define('DEFAULT_START_TIME', '00:00:00');
define('DEFAULT_STOP_DATE', '2038-01-19');//Formato aaaa-mm-gg
define('DEFAULT_STOP_TIME', '23:59:59');
define('SANDBOX_USER', 'servi');
define('SANDBOX_PSW', 'f1100e9f3c60ca99295d77759e307748dc9b30e82f461168b474fc4addbde211c8389763f943d438ca74ffb9f8f87e5c920f996b55d5d901d006044e2da33ac9');


if (!isset($_SESSION)){
  session_start();
}

// Database connection
//$db = mysqli_connect('localhost', 'coupon', 'a3fdsgjhS8!', 'coupon'); //per coupon.tcserver.it
//$db = mysqli_connect('localhost', 'couponcustom', 'Wuqz*616', 'couponcustom'); //per couponcustom.tcserver.it
//$db = mysqli_connect('localhost', 'dat42372_admin', '@dmin1996', 'dat42372_telecash'); //per datastoresolution.it
$db = mysqli_connect('localhost', 'root', 'S@rida', 'dat42372_telecash'); //per debug locale

// Global variable declaration
$errors   	= array();
$messages  	= array();
$sandbox_user	="";
$sandbox_psw	="";
$endPoint	="";
$ResultCode	="";
$CodeDescription="";
$MessageError	="";

// Global variables switch
//$is_sandbox				=	true;
$is_sandbox				=	false;
//$is_debug 				= 	true;
$is_debug 				= 	false;
//$only_standard_coupon	= 	true;
$only_standard_coupon	= 	false;

//Leggo i valori da javascript (eseguita ad ogni chiamata della function.php)
if(is_true($is_sandbox)){
	$sandbox_user	=	SANDBOX_USER;
	$sandbox_psw	=	SANDBOX_PSW;
	//echo("is_sandbox=".$is_sandbox." sandbox_user=".$sandbox_user." sandbox_psw=".$sandbox_psw);
}
//else{	echo("parametri normali");}

//Recupero il valore dell'endpoint
if(is_true($is_sandbox))
	$endPoint	=	"API_coupon_SANDBOX.cgi";
else
	$endPoint	=	"API_coupon.cgi";

// log user out if logout button clicked
if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: login.php");
}

//Se l'utente ha premuto Edit sul form per modificare il record
//Questo metodo non tiene conto della password tanto sarà l'update a eseguire l'aggiornamento del record
if (isset($_GET['edit_user'])) {
	$id = $_GET['edit_user'];
	echo '<script>','updating=true;','</script>';
	$SQLCommand="SELECT * FROM users WHERE id=$id";
	//echo($SQLCommand);
	$record = mysqli_query($db, $SQLCommand) or die(mysqli_error($db));
	//echo($SQLCommand);
	if ($record->num_rows == 1 ) {
		$n = mysqli_fetch_array($record);
		$id= $n['id']; //Lo DEVO fare altrimenti l'id passato dalla GET non funziona (formati di dato diversi)
		$username = $n['username'];
		//La password non mi serve
		$retailer = $n['retailer'];
		$nomevisualizzato = $n['nomevisualizzato'];
	}
}

// call the login() function if login_btn is clicked
if (isset($_POST['login_btn'])) {
	login();
}

//////////////////////////////FUNZIONI///////////////////////////////
// LOGIN USER, RETAILER AND ADMIN
function login(){
	global $db, $username, $password, $errors, $is_sandbox, $sandbox_user, $sandbox_psw;
	
	// grap form values
	$username = e($_POST['username']);
	$password = e($_POST['password']);

	// make sure form is filled properly
	if (empty($username)) {
		array_push($errors, "Username richiesto");
	}
	if (empty($password)) {
		array_push($errors, "Password richiesta");
	}
	// attempt login if no errors on form
	if (count($errors) == 0) {
		$password = md5($password);

		$query = "SELECT * FROM retailers WHERE username='$username' AND password='$password' LIMIT 1";
		$results = mysqli_query($db, $query) or die(mysqli_error($db));

		if (mysqli_num_rows($results) == 1) { // admin o retailer found
			$logged_in_user = mysqli_fetch_assoc($results);
			if (is_true($is_sandbox))
			{
				$_SESSION['db_user']= $sandbox_user;
				$_SESSION['db_password']= $sandbox_psw;
				logWrite("info","Siamo in SANDBOX");

			}else
			{
				$_SESSION['db_user']= $logged_in_user['utente_DB_Telecash'];
				$_SESSION['db_password']= $logged_in_user['password_DB_Telecash'];
				logWrite("info","NON Siamo in SANDBOX");
			}
			
			// check if user is admin or retailer
			if ($logged_in_user['user_type'] == 'admin') {

				$_SESSION['user'] = $logged_in_user;
				$_SESSION['success']  = "Ora sei collegato come ammministratore";
				header('location: admin/home.php');		  
			}else if ($logged_in_user['user_type'] == 'retailer') {
				$_SESSION['user'] = $logged_in_user;
				$_SESSION['success']  = "Ora sei collegato come rivenditore";
				header("location: retailer/home.php");
			}
		}
		else //Cliente
		{
			$query = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
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
					else
					{
						array_push($errors, "Impossibile trovare il rivenditore associato all'utente nella tabella retailers");
						$_SESSION['db_user']= "vuoto";
						$_SESSION['db_password']= "vuota";
					}
				}
				header('location: user/home.php');
			}
			else
			{
				array_push($errors, "Errata combinazione username/password");
			}
		}
	}
}

function isLoggedIn()
{
	if (isset($_SESSION['user'])) {
		return true;
	}else{
		return false;
	}
}

// escape string
function e($val){
	global $db;
	return mysqli_real_escape_string($db, trim($val));
}

//Verifica l'esistenza e la congruenza dellle password inserite
function CheckPassword()
{
	global $errors;
	if (isset($_POST['password_1'])&&isset($_POST['password_2'])) {
		$newpassword_1=e($_POST['password_1']);
		$newpassword_2=e($_POST['password_2']);
		if($newpassword_1==$newpassword_2)
			return(TRUE);
		else{
			array_push($errors, "Le nuove password non coincidono");
			return(FALSE);
		}
	}
}

//Visualizza errori generati da php
function display_error() {
	global $errors,$is_debug;

	if (count($errors) > 0){
		echo '<div class="error">';
			foreach ($errors as $error){
				echo $error .'<br>';
			}
		echo '</div>';
	}
	else
		if(is_true($is_debug)):
			echo '<div class="error"> Nessun Errore da visualizzare</div>';
		endif;
}

//Funzione ora inutilizzata che visualizza un singolo messaggio inserito con una chiamata $_SESSION['message'] = "testo";
function display_single_message_unused() {
    global $is_debug;
		if (isset($_SESSION['message'])): 
?>
		<div class="msg">
			<?php 
				echo $_SESSION['message']; 
				unset($_SESSION['message']);
			?>
		</div>
<?php	else :
			if (is_true($is_debug)):
?>
		<div class="msg">
		<?php 
				echo "Nessun messaggio da visualizzare";
		?>
		</div>
<?php
			endif;
		endif;
}

//Visualizza messaggi generati da php
function display_message() {
	global $messages,$is_debug;
	
	if (count($messages) > 0){
		echo '<div class="msg">';
			foreach ($messages as $message){
				echo $message .'<br>';
			}
		echo '</div>';
	}
	else
		if (is_true($is_debug)):
			echo '<div class="msg"> Nessun Messaggio da visualizzare</div>';
		endif;
}

function is_true($val, $return_null=false){
    $boolval = ( is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val );
    return ( $boolval===null && !$return_null ? false : $boolval );
}

//True se l'utente loggato è l'amministratore
function isAdmin()
{
	if (isset($_SESSION['user']) && $_SESSION['user']['user_type'] == 'admin' ) 
		return true;
	else
		return false;
	
}

//True se l'utente loggato è un retailer (rivenditore)
function isRetailer()
{
	if (isset($_SESSION['user']) && isset($_SESSION['user']['user_type']) )
		if($_SESSION['user']['user_type'] == 'retailer' ) 
			return true;
	else
		return false;
	
}

//Overload della funzione alert javascript in php
function alert($msg) {
    echo "<script type='text/javascript'>alert('$msg');</script>";
}

function EchoArrayOnDEBUG($msg,$nRiga,$array){
	global $is_debug;
	if(is_true($is_debug)):
		if($array):
			$lenght=count((array)$array);
			echo "<br><br> EchoArrayOnDEBUG chiamamta alla riga:$nRiga msg:$msg (n° elementi dell'array:$lenght)<br>";
			if($lenght>0)
				print_r($array);
			else
				echo "array vuoto";
		endif;
	endif;
}

function GetBoolSessionValue($PHPSessionVarName,$echo=false) {
	if (!isset($_SESSION[$PHPSessionVarName])){ 
		if(is_true($echo))
			echo("false"); 
		return false;	
	} 
	else{
		if(is_true($_SESSION[$PHPSessionVarName])){
			if(is_true($echo))
				echo("true"); 
			return true;
		}
		else {
			if(is_true($echo))
				echo("false");
			return false;
		}
	}
}

//$Type: info,debug o error
function logWrite($Type="info",$msg,$enabled=true){
	global $is_debug;
	
	if($enabled)
	{
		$msg2 = str_replace('\'','^', $msg); //Sostituisco il carattere ' con ^ per evitare problemi
		if (is_true($is_debug))
			echo "<script>log.".$Type."('".$msg2."');</script>";
	}
}

