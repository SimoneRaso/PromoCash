<?php
class Result {
    // constructor should be public
    public function __construct($result_code=0, $code_description="",$message_error="") {
        $this->result_code = $result_code;
        $this->code_description = $code_description;
		$this->message_error = $message_error;
    }
}

function APIFetch($url){
	global $ResultCode,$CodeDescription,$MessageError;	
	
	//Resetto le variabili
	$ResultCode="";
	$CodeDescription="";
	$MessageError="";	
	
	ini_set('max_execution_time', 120); //120 seconds = 2 minutes
	$sxml = simplexml_load_file($url);
	if($sxml==false)	
	{
		/*foreach(libxml_get_errors() as $error) 
		{
			$ResultCode=$error->code;
			$CodeDescription="Failed loading XML";
			$MessageError="\t"+$error->message;
    	}*/
		$error=libxml_get_last_error();
		libxml_clear_errors();
		throw new Exception ("simplexml_load_file function error. Code error:".$error->code." Message error:".$error->message);
	}
	Fetch_Code($sxml->Result_API->Code);
	if($ResultCode==0)
		return $sxml;
	else
		return false;
	}

function Fetch_Code($cod) {
	global $ResultCode,$CodeDescription,$MessageError;
	$ResultCode=$cod;
	switch ($cod) {
		case "0":
			$CodeDescription="API_correct_parameter";
			$MessageError="Chiamata corretta.";
			break;
		case "-1":
			$CodeDescription="API_internal_error";
			$MessageError="Errore interno dell\'applicativo.Contattare l\'assistenza.";
			break;
		case "-2":
			$CodeDescription="API_invalid_username_or_password";
			$MessageError="Errore di autenticazione. I campi potrebbero essere vuoti.";
			break;
		case "-3":
			$CodeDescription="API_invalid_cmd_parameter";
			$MessageError="Il campo \'cmd\' è vuoto o con un comando non riconosciuto.";
			break;
		case "-4":
			$CodeDescription="API_invalid_coupon_code_parameter";
			$MessageError="Il campo \'coupon_code\' non è stato valorizzato, oppure contiene dei case caratteri alfabetici non ammessi dal campo coupon_channel = 2";
			break;
		case "-5":
			$CodeDescription="API_invalid_coupon_type_parameter";
			$MessageError="Il campo \'coupon_type\' non è stato valorizzato, oppure contiene un valore diverso da 1,2,3";
			break;
		case "-6":
			$CodeDescription="API_invalid_coupon_value_parameter";
			$MessageError="Il parametro \'coupon_value\' non è stato valorizzato, oppure contiene un numero non intero.";
			break;
		case "-7":
			$CodeDescription="API_invalid_coupon_channel_parameter";
			$MessageError="Il parametro \'coupon_channel\' non è stato valorizzato, oppure contiene un valore diverso da 1,2 o caratteri non ammessi.";
			break;
		case "-8":
			$CodeDescription="API_invalid_merchant_code_parameter";
			$MessageError="Il parametro \'merchant_code\' non è stato valorizzato, oppure contiene caratteri non ammessi.";
			break;
		case "-9":
			$CodeDescription="API_invalid_phone_service_parameter";
			$MessageError="Il parametro \'phone_service\' non è stato valorizzato, oppure contiene caratteri non ammessi.";
			break;
		case "-10":
			$CodeDescription="API_invalid_start_date_parameter";
			$MessageError="Il parametro \'start_date\' non è stato valorizzato o contiene la data in formato errato.";
			break;
		case "-11":
			$CodeDescription="API_invalid_stop_date_parameter";
			$MessageError="Il parametro \'stop_date\' non è stato valorizzato o contiene la data in formato errato.";
			break;
		case "-12":
			$CodeDescription="API_invalid_start_time_parameter";
			$MessageError="Il parametro \'start_time\' non è stato valorizzato o contiene l\'ora in formato errato.";
			break;
		case "-13":
			$CodeDescription="API_invalid_stop_time_parameter";
			$MessageError="Il parametro \'stop_time\' non è stato valorizzato o contiene l\'ora in formato errato";
			break;
		case "-14":
			$CodeDescription="API_invalid_date_or_time_value";
			$MessageError="La differenza fra DATA_ORA_INIZIO e DATA_ORA_FINE è inferiore a 30 minuti, periodo minimo di validità del coupon. Oppure DATA_ORA_FINE è antecedente a DATA_ORA_INIZIO.";
			break;
		case "-15":
			$CodeDescription="API_internal_error";
			$MessageError="Errore interno dell\'applicativo. Contattare l\'assistenza.";
			break;
		case "-16":
			$CodeDescription="API_invalid_remote_ip";
			$MessageError="L\'IP del server da dove proviene la richiesta di login è diverso da quello configurato nelle credenziali di accesso del rivenditore. Contattare TELECASH.IT";
			break;
		case "-17":
			$CodeDescription="API_invalid_merchantcustomer_or_phone_service";
			$MessageError="Il servizio (merchant_code + phone_service) non è di pertinenza del rivenditore, oppure del merchant se il parametro \'customer_limit\' è valorizzato.";
			break;
		case "-18":
			$CodeDescription="API_failure_insert_new_coupon[dati]";
			$MessageError="L\'inserimento di un nuovo coupon (new) non è andato abuon fine.";
			break;
		case "-19":
			$CodeDescription="API_coupon_not_found Coupon";
			$MessageError="non trovato(i)/inesistente(i).";
			break;
		case "-20":
			$CodeDescription="API_IDS_detected";
			$MessageError="Il sistema di Intrusion Detections ha rilevato un potenziale attacco ed ha bloccato l\'esecuzione dello script. L\'evento potrebbe essere causato da un falso allarme come, ad esempio, un carattere non ammesso in un campo. Controllare attentamente i valori immessi. Contattare TELECASH.IT.";
			break;
		case "-21":
			$CodeDescription="API_invalid_coupon_id_parameter";
			$MessageError="Il campo coupon_id (quando obbligatorio) non è stato valorizzato, oppure contiene un carattere non numerico.";
			break;
		case "-22":
			$CodeDescription="API_failed_update_coupon";
			$MessageError="La modifica di un coupon non è andata a buon fine.Controllare i dati e la formattazione di tutti i campi obbligatori. Verificare un\'eventuale duplicazione dei campi Coupon_code + Merchant_code +Phone_service + Coupon_channel";
			break;
		case "-23":
			$CodeDescription="API_usage_data_non_found";
			$MessageError="Nessun coupon utilizzato è stato trovato con i parametri di ricerca forniti \'coupon_usage\'.";
			break;
		case "-24":
			$CodeDescription="API_invalid_customer";
			$MessageError="Il merchant (cliente) specificato nel parametro \'customer_limit\' è inesistente oppure non associato al rivenditore titolare del servizio (Merchant_code + Phone_service).";
			break;
		case "-25":
			$CodeDescription="API_phoneservice_list_not_found";
			$MessageError="Il comando \'PHONESERVICE_LIST\' non ha trovato servizi associati al rivenditore, oppure al merchant (cliente) se parametro \'customer_limit\' è valorizzato";
			break;
		case "-26":
			$CodeDescription="API_customers_not_found";
			$MessageError="Il comando \'CUSTOMERS_LIST\' non ha trovato nessun cliente associato al rivenditore.";
			break;
		case "-30":
			$CodeDescription="API_existing_similar_coupon";
			$MessageError="I dati di un nuovo coupon sono simili ad uno già esistente:Coupon_code + Merchant_code + Phone_service + Coupon_channel";
			break;
		case "-31":
			$CodeDescription="API_invalid_user_cli_parameter";
			$MessageError="Il parametro \'user_cli\' della funzione \'coupon_reset\' contiene un valore non ammesso. Oltre al valore di un numero telefonico, è consentito unicamente il carattere \'*\'.";
			break;
		case "-32":
			$CodeDescription="API_no_coupon_find_for_reset";
			$MessageError="Nessun coupon utilizzato, o la relativa data di utilizzo,rispecchia i parametri di ricerca della funzione \'coupon_reset\'.";
			break;
		case "-33":
			$CodeDescription="API_log_data_non_found";
			$MessageError="Nessuna statistica di coupon utilizzati è stata trovata con i parametri di ricerca forniti \'coupon_log\'.";
			break;
		case "-34":
			$CodeDescription="API_sandbox_usage_coupon_already_exist";
			$MessageError="Si è tentato, tramite la funzione SANDBOX \'coupon_simulate_usage\', di simulare l\'utilizzo (annullamento) di un coupon già presente (duplicato).I campi \'merchant, dnis, ani, codice, canale\' sono univoci.Prima di tentare nuovamente l\'inserimento, occorre cancellare l\'utilizzo tramite la funzione \'coupon_reset\'.";
			break;
		case "-35":
			$CodeDescription="API_sandbox_invalid_user_cli_parameter";
			$MessageError="Nel comando \'coupon_simulate_usage\' il parametro \'user_cli\' deve riferirsi solo ad utenze telefoniche italiane mobili ed iniziare con “393”.";
			break;
		case "-36":
			$CodeDescription="API_sandbox_couponcustom_not_equal_user-cli";
			$MessageError="Nel comando \'coupon_simulate_usage\' il parametro \'user_cli\' (numero utente) deve essere identico a \'custom\', se valorizzato.";
			break;
		case "-37":
			$CodeDescription="API_smslead_cli_not_found";
			$MessageError="Nessun CLI utente trovato nelle statistiche SMS_LEAD per il merchant e periodo indicato ed eventuale flag privacy 0/1 impostato";
			break;
		case "-38":
			$CodeDescription="API_smslead_incorrect_privacy_parameter";
			$MessageError="Il parametro privacy è un FLAG e, se valorizzato, deve contenere solo 0/1.";
			break;
		case "-39":
			$CodeDescription="API_blacklist_cli_incorrect_list_parameter";
			$MessageError=" Il parametro \'list\' della funzione \'blacklist_cli\' non è valido.";
			break;
		case "-40":
			$CodeDescription="API_return_cli_incorrect_type_parameter";
			$MessageError="Valori ammessi: 0,1,2,3";
			break;
		case "-41":
			$CodeDescription="API_return_cli_incorrect_check_blklst_parameter";
			$MessageError="FLAG, valori ammessi 0,1";
			break;
		case "-42":
			$CodeDescription="API_return_cli_incorrect_length_call_parameter";
			$MessageError="Durata della chiamata maggiore (>) valore minimo 0, se parametro type = 0";
			break;
		case "-43":
			$CodeDescription="API_return_cli_incorrect_only_mobile_parameter";
			$MessageError="FLAG, valori ammessi 0,1";
			break;
                case "-44":
			$CodeDescription="API_overlaps_date_coupon";
			$MessageError="Il periodo di validità di un coupon custom telefonico si sovrappone ad un altro esistente.";
			break;
		default:
			$CodeDescription="API_Non_Definito";
			$MessageError="Codice errore sconosciuto";
		}
	}
