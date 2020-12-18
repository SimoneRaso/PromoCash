<?php

//Inserimento di un nuovo coupon
if (isset($_POST['submitCoupon'])) {
    $coupon_code = "";
    $coupontype = "";
    $couponchannel = "";
    $customer_limit = "";

    //Recupero il valore di unlimited
    $unlimited = "";
    if (!isset($_POST['chkUnlimited'])) {
        $unlimited = "";
    } else {
        $unlimited = "1";
    }

    //Converto il coupontype nel valore corretto per l'API
    switch ($_POST['benefit']) {
        case 'omaggio_secondi':
            $coupontype = "1";
            break;
        case 'sconto_importo':
            $coupontype = "2";
            break;
        case 'sconto_percentuale':
            $coupontype = "3";
            break;
        case 'omaggio_secondi_percentuale':
            $coupontype = "4";
            break;
    }
    //Converto il couponchannel nel valore corretto per l'API
    switch ($_POST['couponchannel']) {
        case 'web':
            $couponchannel = "1";
            break;
        case 'telefonico':
            $couponchannel = "2";
            break;
    }
    //Recupero l'eventuale customer_limit
    if (isset($_SESSION['user']['username']) && (!IsRetailer()))
        $customer_limit = $_SESSION['user']['username'];

    //Se sto trattando un coupon di tipo CUSTOM
    if ($_POST['coupontype'] == 'custom') {
        logWrite("debug", "Inserimento Coupon Custom...");
        try {
            $ListaCLIOK = GetCouponCli();
        } catch (Exception $ex) {
            logWrite("error", "Eccezione nel POST submitCoupon . Dettagli:" . $ex->getMessage());
            echo($ex->getMessage());
            exit;
        }

        //Procedo all'inserimento dei coupons
        if (count($ListaCLIOK) == 0) {
            logWrite("debug", "Nessun coupon custom da inserire...");
            array_push($messages, "Nessun coupon custom da inserire");
        } else {
            logWrite("debug", "Passo all\'effettivo inserimento dei coupon custom");
            //ciclo sui numeri presenti in ListaCLIOK 

            foreach ($ListaCLIOK as $ElemListaCLIOK) {
                $status = 1;       //Coupon ancora da creare
                //$last_action = date("Y-m-d H:i:s");		//Now()
                $username = mysqli_real_escape_string($db, $_SESSION['db_user']);
                $password = mysqli_real_escape_string($db, $_SESSION['db_password']);
                $merchant_code = e($_POST['selectMerchantCode']);
                $phone_service = e($_POST['selectPhoneService']);
                $coupon_custom = trim($ElemListaCLIOK); //rimuovo eventuali spazi inizio e fine testo letto
                switch ($couponchannel) {
                    case "1":
                        //$coupon_code = e($_POST['country_file']).$coupon_custom;
                        $coupon_code = $coupon_custom;
                        break;
                    case "2":
                        $coupon_code = $coupon_custom . date('YmdHis' . substr((string) microtime(), 2, 8)); //Aggiungo ora minuti e secondi e millisecondi al coupon_code
                        break;
                }
                $coupon_value = e($_POST['nbenefit']);
                $coupon_custom = e($_POST['country_file']) . $coupon_custom;
                $start_date = dateformat($_POST['valido_dal_date']);
                $start_time = timeformat(e($_POST['valido_dal_time']));
                $stop_date = dateformat($_POST['valido_al_date']);
                $stop_time = timeformat(e($_POST['valido_al_time']));

                // Insert nel DB nella tabella custom_queue , tutti i valori di $ListaClicOK
                $query = " INSERT INTO coupon_queue (status, last_action, username, password, merchant_code, phone_service, coupon_code, coupon_type, coupon_value, coupon_channel, coupon_custom, start_date, start_time, stop_date, stop_time, customer_limit, unlimited) VALUES ('$status', NOW(), '$username', '$password', '$merchant_code', '$phone_service', '$coupon_code', '$coupontype', '$coupon_value', '$couponchannel', '$coupon_custom', '$start_date', '$start_time', '$stop_date', '$stop_time', '$customer_limit', '$unlimited') ";
                logWrite("debug", "Query di insert completa: $query");
                mysqli_query($db, $query) or die(mysqli_error($db));
            }
            // Scrivo all' utente che i coupon sono stati inseriti nel sistema e verranno processati 
            $count = count($ListaCLIOK);
            array_push($messages, "$count Coupons accodati per l'inserimento");
            logWrite("info", "$count Coupons accodati per l'inserimento");
        }//fine if(count($ListaCLIOK)==0)
    } //fine if($_POST['coupontype']=='custom')
    else { //non è un coupon custom
        logWrite("debug", "Inserimento Coupon Standard...");
        $cod_coupon = e($_POST['cod_coupon']);
        if (NewCoupon($_SESSION['db_user'], $_SESSION['db_password'], e($_POST['selectMerchantCode']), e($_POST['selectPhoneService']),
                        e($_POST['cod_coupon']), $coupontype, e($_POST['nbenefit']), $couponchannel, "",
                        dateformat($_POST['valido_dal_date']), timeformat(e($_POST['valido_dal_time'])),
                        dateformat($_POST['valido_al_date']), timeformat(e($_POST['valido_al_time'])),
                        $customer_limit, $unlimited, $ignored_code_error = ""))
            logWrite("info", "Coupon con codice $cod_coupon inserito correttamente");
        else
            logWrite("error", "Errore nell'inserimento del coupon con codice $cod_coupon.");
    }
}

//$Echoenabled=true o false abilita gli echo 
function GetCouponCli($Echoenabled = true) {
    global $messages, $errors, $endPoint, $ResultCode, $CodeDescription, $MessageError;
    $ListaCLIOK = array();
    //Se devo caricare cli da file
    if (isset($_POST['chkDaFile'])) {
        logWrite("debug", "Check \"da File\" selezionato", $Echoenabled);

        $ListaCLIDaFile = array();
        $ListaCLIBLDaFile = array();
        //Carico e verifico il file txt con i CLI
        $target_dir = "uploads/";

        if (isset($_FILES['fileToUpload'])) {
            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            $nMaxCLIperFileCustom = 1000;
            $ListaCLIDaFile = LoadCLIFIle($target_file, "fileToUpload", "CLI");
            if (count($ListaCLIDaFile) > (intval($nMaxCLIperFileCustom))) {
                array_push($errors, "File CLI importato avente troppi CLI (" . count($ListaCLIDaFile) . "). Max consentito = " . $nMaxCLIperFileCustom . ". Gli eccedenti vengono troncati");
                $ListaCLIDaFile = array_slice($ListaCLIDaFile, 0, $nMaxCLIperFileCustom, true);
            }
        }//fine if(isset($_FILES['fileToUpload']))
        
        if ($Echoenabled)
            EchoArrayOnDEBUG("ListaCLIOK solo da file", __LINE__, $ListaCLIDaFile);

        if (isset($_POST['chkFileBlackList'])) {
            logWrite("debug", "Check \"Black-List\" selezionato", $Echoenabled);

            //Carico e verifico il file txt con i CLI
            if (isset($_FILES['blacklistToUpload'])) {
                $index=0;
                $ListaCLIBLDaFileTmp = array();
                $ListaCLIBLDaFileTot = array();
                foreach ($_FILES["blacklistToUpload"]["name"] as $BlacklistToUpload){
                    $target_file = $target_dir . basename($BlacklistToUpload);
                    $ListaCLIBLDaFileTmp = LoadCLIFIle($target_file, "blacklistToUpload", "BLACK LIST",$index);
                    $ListaCLIBLDaFile = array_merge($ListaCLIBLDaFileTot , $ListaCLIBLDaFileTmp);
                    $ListaCLIBLDaFileTot=$ListaCLIBLDaFile;
                    $index++;
                }
            }//fine if(isset($_FILES['blacklistToUpload']))
        }//fine if (isset($_POST['chkFileBlackList']))
        //faccio la differenza tra i due array
        $ListaCLIOK = array_diff($ListaCLIDaFile, $ListaCLIBLDaFile);

        if ($Echoenabled)
            EchoArrayOnDEBUG("ListaCLIOK dopo applicazione black list da file: ", __LINE__, $ListaCLIOK);
    }//fine if (isset($_POST['chkDaFile']))
    //Se devo caricare cli da merchant //Ricorda: $ListaCLIOK(array di cli privi del prefisso internazionale "es:3393212345")
    if (isset($_POST['chkDaMerchant'])) {
        $StartDate = DEFAULT_START_DATE;
        $StartTime = DEFAULT_START_TIME;
        $StopDate = DEFAULT_STOP_DATE;
        $StopTime = DEFAULT_STOP_TIME;

        //Se devo filtrare per date di acquisto recupero le date inserite (altrimenti uso quelle di default)
        if (isset($_POST['chkFiltroDate_mf'])) {
            logWrite("info", "Filtro date selezionato", $Echoenabled);
            $StartDate = e($_POST['dal_data_filtro1_mf']);
            $StartTime = e($_POST['dal_time_filtro1_mf']);
            $StopDate = e($_POST['al_data_filtro1_mf']);
            $StopTime = e($_POST['al_time_filtro1_mf']);
        }
        if (isset($_POST['chkTuttiMerchant_mf'])) { //se devo considerarli Tutti
            //Recupero la lista dei servizi associati al rivenditore corrente
            $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?username=" . $_SESSION['db_user'] . "&password=" . $_SESSION['db_password'] . "&cmd=phoneservice_list";
            if (isset($_SESSION['user']['username']))
                $url = $url . '&customer_limit=' . $_SESSION['user']['username'];
            try {
                $sxml_PhoneService = APIFetch($url);
            } catch (Exception $ex) {
                logWrite("error", "Eccezione nell\'APIFetch (phoneservice_list) della GetCouponCli da Merchant. Dettagli:" . $ex->getMessage());
                throw new Exception($ex->getMessage());
            }
            $logm = "Risposta dell API phoneservice_list (" . $url . ") : Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
            if ($sxml_PhoneService != false)
                if ($Echoenabled)
                    logWrite("info", "$logm");
                else
                if ($Echoenabled)
                    logWrite("error", "Errore nell\'APIFetch (phoneservice_list). Dettagli:$logm", $Echoenabled);

            //Ciclo su tutti i servizi presenti ed eseguo il codice dell'else
            foreach ($sxml_PhoneService->PhoneService as $Elem) {
                try {
                    $NewCli = GetCliFromMerchant($Elem->Merchant_code, $Elem->Phone_service, e($_POST['selectTypeOfCli_mf']),
                            $StartDate, $StartTime, $StopDate, $StopTime, $Echoenabled);
                } catch (Exception $ex) {
                    logWrite("error", "Eccezione nella chiamata GetCliFromMerchant. Dettagli:" . $ex->getMessage());
                    throw new Exception($ex->getMessage());
                }

                //array_push($ListaCLIOK,$NewCli);	
                $ListaCLIOK = array_merge($ListaCLIOK, $NewCli);
            }
        } else {
            //PUNTO 1
            try {
                $Cli = GetCliFromMerchant(e($_POST['selectMerchantCode_mf']), e($_POST['selectPhoneService_mf']), e($_POST['selectTypeOfCli_mf']),
                        $StartDate, $StartTime, $StopDate, $StopTime, $Echoenabled);
            } catch (Exception $ex) {
                logWrite("error", "Eccezione nella chiamata GetCliFromMerchant. Dettagli:" . $ex->getMessage());
                throw new Exception($ex->getMessage());
            }

            //PUNTO 2
            if (isset($_POST['chkMatch_mf'])) { //se devo Fare il Math con chi non ha acquistato (devo togliere qusti risultati da quelli trovati al PUNTO 1)
                $StartDate = e($_POST['dal_data_filtro2_mf']);
                $StartTime = e($_POST['dal_time_filtro2_mf']);
                $StopDate = e($_POST['al_data_filtro2_mf']);
                $StopTime = e($_POST['al_time_filtro2_mf']);
                try {
                    $CliToRemove = GetCliFromMerchant(e($_POST['selectMerchantCode_mf']), e($_POST['selectPhoneService_mf']), e($_POST['selectTypeOfCliMatch_mf']), $StartDate, $StartTime, $StopDate, $StopTime, $Echoenabled);
                } catch (Exception $ex) {
                    logWrite("error", "Eccezione nella chiamata GetCliFromMerchant. Dettagli:" . $ex->getMessage());
                    throw new Exception($ex->getMessage());
                }

                if (count($Cli) > 0) {
                    $TempArray = array_diff($Cli, $CliToRemove); //riga inglobabile in quella successiva
                    $nEliminati = count($Cli) - count($CliToRemove);
                    if ($nEliminati > 0)
                        logWrite("info", "Il match ha eliminato N° $nEliminati Cli", $Echoenabled);
                    $ListaCLIOK = array_merge($ListaCLIOK, $TempArray);
                }
            } else
                $ListaCLIOK = array_merge($ListaCLIOK, $Cli);
        }
    }
    if ($Echoenabled)
        EchoArrayOnDEBUG("ListaCLIOK completa con merchant prima del Leads", __LINE__, $ListaCLIOK);

    //Se devo caricare cli da Leads
    if (isset($_POST['chkDaLeads'])) {
        $StartDate = DEFAULT_START_DATE;
        $StartTime = DEFAULT_START_TIME;
        $StopDate = DEFAULT_STOP_DATE;
        $StopTime = DEFAULT_STOP_TIME;

        //Se devo filtrare per date di acquisto recupero le date inserite (altrimenti uso quelle di default)
        if (isset($_POST['chkFiltroData_lf'])) {
            logWrite("debug", "Filtro date selezionato", $Echoenabled);
            $StartDate = e($_POST['dal_data_filtro1_lf']);
            $StartTime = e($_POST['al_data_filtro1_lf']);
            $StopDate = e($_POST['dal_data_filtro2_lf']);
            $StopTime = e($_POST['al_data_filtro2_lf']);
        }
        if (isset($_POST['chkTuttiMerchant_lf'])) { //se devo considerare Tutti i Merchant
            //Recupero la lista dei servizi associati al rivenditore corrente
            $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?username=" . $_SESSION['db_user'] . "&password=" . $_SESSION['db_password'] . "&cmd=phoneservice_list";
            if (isset($_SESSION['user']['username']))
                $url = $url . '&customer_limit=' . $_SESSION['user']['username'];
            try {
                $sxml_PhoneService = APIFetch($url);
            } catch (Exception $ex) {
                logWrite("error", "Eccezione nell\'APIFetch (phoneservice_list) della GetCouponCli da Leads. Dettagli:" . $ex->getMessage());
                throw new Exception($ex->getMessage());
            }
            $logm = "Risposta dell API phoneservice_list (" . $url . ") : Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
            if ($sxml_PhoneService != false)
                logWrite("info", $logm, $Echoenabled);
            else
                logWrite("error", "Errore nell\'APIFetch (phoneservice_list). Dettagli:$logm", $Echoenabled);

            //Ciclo su tutti i servizi presenti ed eseguo il codice dell'else
            foreach ($sxml_PhoneService->PhoneService as $Elem) {
                try {
                    $NewCli = GetCliFromLeads($Elem->Merchant_code, $Elem->Phone_service, $StartDate, $StartTime, $StopDate, $StopTime, $Echoenabled, "-37");
                } catch (Exception $ex) {
                    logWrite("error", "Eccezione nella chiamata GetCliFromLeads. Dettagli:" . $ex->getMessage());
                    throw new Exception($ex->getMessage());
                }
                $ListaCLIOK = array_merge($ListaCLIOK, $NewCli);
            }
        } else {
            try {
                $Cli = GetCliFromLeads(e($_POST['selectMerchantCode_lf']), e($_POST['selectPhoneService_lf']), $StartDate, $StartTime, $StopDate, $StopTime, $Echoenabled, "-37");
            } catch (Exception $ex) {
                logWrite("error", "Eccezione nella chiamata GetCliFromLeads. Dettagli:" . $ex->getMessage());
                throw new Exception($ex->getMessage());
            }

            $ListaCLIOK = array_merge($ListaCLIOK, $Cli);
        }
    }
    if ($Echoenabled)
        EchoArrayOnDEBUG("ListaCLIOK completa con Leads (prima del filtro black-list amministrativa-utente)", __LINE__, $ListaCLIOK);

    //Se devo caricare lo user cli
    if (isset($_POST['chkDaCli'])) {
        $UserCli = e($_POST['numero_cli']);
        array_push($ListaCLIOK, $UserCli);
    }
    if ($Echoenabled)
        EchoArrayOnDEBUG("ListaCLIOK completa con User Cli (prima del filtro black-list amministrativa-utente)", __LINE__, $ListaCLIOK);

    //Eseguo blacklist_cli per eliminare i cli presenti nelle black-list amministrativa e cli
    logWrite("debug", "Eseguo Filtro Cli in black-list amministrative+utente", $Echoenabled);
    $cust_limit = (isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : "0");
    try {
        $BlackListCli = GetBlackListCli($_SESSION['db_user'], $_SESSION['db_password'], $Echoenabled, $cust_limit);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nella chiamata GetBlackListCli nella funzione GetCouponCli. Dettagli:", $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    $nBlackListCli = count($BlackListCli);
    if ($nBlackListCli == 0)
        logWrite("error", "Black-list amministrative+utente vuota!", $Echoenabled);
    else {
        logWrite("info", "La Black-list amministrative+utente contiene $nBlackListCli Cli!", $Echoenabled);
        if (count($ListaCLIOK) > 0) {
            $ListaCLIOKFiltrati = array_diff($ListaCLIOK, $BlackListCli);
            $nElem = count($ListaCLIOK) - count($ListaCLIOKFiltrati);
            if ($nElem != 0) {
                //Stampo su un alert il numero di elementi cancellati nel file
                logWrite("info", "Sono stati eliminati n° " . $nElem . " cli presenti nelle black-list amministrative e utente", $Echoenabled);
                array_push($messages, "Sono stati eliminati n° " . $nElem . " cli presenti nelle black-list amministrative+utente");
                $ListaCLIOK = array_merge($ListaCLIOKFiltrati);
            }
        } else
            logWrite("info", "Black-list amministrative+utente: Nessun Cli da elaborare", $Echoenabled);
    }
    if ($Echoenabled)
        EchoArrayOnDEBUG("ListaCLIOK completa dopo filtro black-list amministrativa-utente", __LINE__, $ListaCLIOK);
    return $ListaCLIOK;
}

//$Echoenabled=true o false abilita gli echo 
function GetCliFromMerchant($MerchantCode, $PhoneService, $TypeOfCli, $StartDate, $StartTime, $StopDate, $StopTime, $Echoenabled, $ignored_code_error = "") {
    global $endPoint, $ResultCode, $CodeDescription, $MessageError;
    $ListaCLI = array();
    logWrite("info", "(Da Merchant): Processo il Merchant:$MerchantCode (Phoneservice:$PhoneService)", $Echoenabled);

    $tempdate = dateformat($StopDate);
    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?username=" . $_SESSION['db_user'] . "&password=" . $_SESSION['db_password'] .
            "&merchant_code=" . $MerchantCode . "&phone_service=" . $PhoneService . "&cmd=return_cli" . "&start_date=" . dateformat($StartDate) .
            "&start_time=" . timeformat($StartTime) . "&stop_date=" . dateformat($StopDate) . "&stop_time=" . timeformat($StopTime) .
            "&type=" . $TypeOfCli . "&check_blklst=0" . "&only_mobile=1";

    if (isset($_SESSION['user']['username']))
        $url = $url . '&customer_limit=' . $_SESSION['user']['username'];
    try {
        $sxml_Cli = APIFetch($url);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (return_cli) della GetCliFromMerchant. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    $logm = "Risposta dell API return_cli di GetCliFromMerchant (" . $url . ") : Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
    $count = 0;
    if ($sxml_Cli != false) {
        $count = count($sxml_Cli->return_cli);
        logWrite("info", "Trovati $count Cli. Dettagli chiamata:$logm", $Echoenabled);
    } else {
        if ($ignored_code_error != "") {
            $array_ignored_code_error = explode(",", $ignored_code_error); //Mette i valori separati da virgola in un array
            if (in_array($ResultCode, $array_ignored_code_error))
                logWrite("debug", "Errore di APIFetch (return_cli) IGNORATO (da codice). Codice Errore:$ResultCode", $Echoenabled);
            else
                logWrite("error", "Errore nell\'APIFetch (return_cli). Dettagli:$logm", $Echoenabled);
        } else
            logWrite("error", "Errore nell\'APIFetch (return_cli). Dettagli:$logm", $Echoenabled);
    }
    if ($count > 0) {
        foreach ($sxml_Cli->return_cli as $return_cli) {
            if (is_number($return_cli->Cli)) {
                $NewCli = RemoveCountry($return_cli->Cli);
                //logWrite("info","(Da Merchant): Rimosso il country a $return_cli->Cli che diventa $NewCli");
                array_push($ListaCLI, $NewCli);
                logWrite("info", "(Da Merchant): $NewCli Inserito nella lista destinatari", $Echoenabled);
            } else
                logWrite("error", "(Da Merchant): $return_cli->Cli non è un numero", $Echoenabled);
        }
    } else
        logWrite("info", "(Da Merchant): Nessun cli presente per il merchant selezionato", $Echoenabled);
    return $ListaCLI;
}

function GetCliFromLeads($MerchantCode, $PhoneService, $StartDate, $StartTime, $StopDate, $StopTime, $Echoenabled, $ignored_code_error = "") {
    global $endPoint, $ResultCode, $CodeDescription, $MessageError;
    $ListaCLILeads = array();
    logWrite("info", "(Da Leads): Processo il Merchant:$MerchantCode (Phoneservice:$PhoneService)", $Echoenabled);

    $tempdate = dateformat($StopDate);
    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?username=" . $_SESSION['db_user'] . "&password=" . $_SESSION['db_password'] .
            "&merchant_code=" . $MerchantCode . "&phone_service=" . $PhoneService . "&cmd=smslead_cli" . "&start_date=" . dateformat($StartDate) .
            "&start_time=" . timeformat($StartTime) . "&stop_date=" . dateformat($StopDate) . "&stop_time=" . timeformat($StopTime);

    if (isset($_SESSION['user']['username']))
        $url = $url . '&customer_limit=' . $_SESSION['user']['username'];
    try {
        $sxml_Cli = APIFetch($url);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (smslead_cli) della GetCliFromLeads. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    $logm = "Risposta dell API smslead_cli di GetCliFromLeads (" . $url . ") : Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
    $count = 0;
    if ($sxml_Cli != false) {
        $count = count($sxml_Cli->Smslead_customer);
        logWrite("info", "Trovati $count Cli aderenti ai Leads. Dettagli chiamata:$logm", $Echoenabled);
    } else {
        if ($ignored_code_error != "") {
            $array_ignored_code_error = explode(",", $ignored_code_error); //Mette i valori separati da virgola in un array
            if (in_array($ResultCode, $array_ignored_code_error))
                logWrite("debug", "Errore di APIFetch (smslead_cli) IGNORATO (da codice). Codice Errore:$ResultCode", $Echoenabled);
            else
                logWrite("error", "Errore nell\'APIFetch (smslead_cli). Dettagli:$logm", $Echoenabled);
        } else
            logWrite("error", "Errore nell\'APIFetch (smslead_cli). Dettagli:$logm", $Echoenabled);
    }
    if ($count > 0) {
        foreach ($sxml_Cli->Smslead_customer as $Smslead_customer) {
            if (is_number($Smslead_customer->Smslead_cli)) {
                $NewSmslead_Cli = RemoveCountry($Smslead_customer->Smslead_cli);
                array_push($ListaCLILeads, $NewSmslead_Cli);
                logWrite("info", "(Da Leads): $NewSmslead_Cli Inserito nella lista destinatari", $Echoenabled);
            } else
                logWrite("error", "(Da Leads): $Smslead_customer->Smslead_cli non è un numero", $Echoenabled);
        }
    } else
        logWrite("info", "(Da Leads): Nessun cli presente per il merchant selezionato", $Echoenabled);
    return $ListaCLILeads;
}

//Restituisce la Blacklist come da api blacklist_cli dentro ad un array senza il prefisso internazionale
function GetBlackListCli($username, $password, $Echoenabled, $customer_limit = 0) {
    global $endPoint, $errors;
    global $ResultCode, $CodeDescription, $MessageError;
    $BlackListCli1 = [];

    //Eseguo l'apifetch
    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?username=" . $username . "&password=" . $password . "&customer_limit=" . $customer_limit . "&cmd=blacklist_cli" . "&list=0";
    try {
        $sxml_BlackListCli = APIFetch($url);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (blacklist_cli) della GetBlackListCli. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    if ($sxml_BlackListCli != false)
        logWrite("info", "Risposta dell API blacklist_cli: Code:$ResultCode CodeDescription:$CodeDescription Dettagli:$MessageError", $Echoenabled);
    else
        logWrite("error", "Errore nell\'APIFetch (blacklist_cli) . Dettagli:$MessageError", $Echoenabled);
    if (isset($sxml_BlackListCli->blacklist_cli)) {
        foreach ($sxml_BlackListCli->blacklist_cli as $blacklist_cli) :
            $BlackListCli1[] = (string) $blacklist_cli->Cli;
            //array_push($BlackListCli1, $blacklist_cli->Cli);		
        endforeach;
    }
    logWrite("info", "APIFetch blacklist_cli comando completo:$url", $Echoenabled);

    //Devo tener conto del country selezionato che, se non specificato nella maschera, assumo per default italia ovvero 39
    $BlackListCli2 = [];

    foreach ($BlackListCli1 as $Cli):
        $BlackListCli2[] = RemoveCountry($Cli); //tolgo il prefisso internazionale	
        //array_push($BlackListCli2, RemoveCountry($Cli));//tolgo il prefisso internazionale	
    endforeach;

    return $BlackListCli2;
}

//Rimuove il country tenendo conto del country selezionato che, se non specificato nella maschera, assumo per default italia ovvero 39
function RemoveCountry($Tel) {
    $Country = "39";
    $RetVal = "";
    //Se il numero inizia con il country selezionato elimino il country 
    if (isset($_POST['country_file']))
        $Country = str_replace("+", "", $_POST['country_file']); //Tolgo eventuale '+'
    $TelCountry = mb_substr($Tel, 0, strlen($Country));
    if ($TelCountry == $Country)
        $RetVal = mb_substr($Tel, strlen($Country));
    else
        $RetVal = $Tel;
//	logWrite("info","RemoveCountry ritorna: $RetVal");

    return (string) $RetVal;
}

//Modifica un coupon
if (isset($_GET['action'])) {
    $action = e($_GET['action']);
    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?username=" . $_SESSION['db_user'] . "&password=" . $_SESSION['db_password'] . "&cmd=report_full";
    $customer_limit = "";
    $array_ignored_code_error = array("-19"); //Errori DB ignorati da questa funzione
    if (isset($_SESSION['user']['username']) && (!IsRetailer())) {
        $customer_limit = '&customer_limit=' . $_SESSION['user']['username'];
        $url = $url . $customer_limit;
    }
    switch ($action) {
        case'modify':
            $idcoupon = intval(e($_GET['id']));
            logWrite("info", "modify selected. couponid=$idcoupon");
            break;
        case'delete':
            $idcoupon = intval(e($_GET['id']));
            $merchant_code = e($_GET['merchant_code']);
            $phone_service = e($_GET['phone_service']);
            $customer_limit = "";
            if (isset($_SESSION['user']['username']) && (!IsRetailer()))
                $customer_limit = $_SESSION['user']['username'];
            logWrite("info", "delete selected. couponid=$idcoupon");
            if (DeleteCoupon($_SESSION['db_user'], $_SESSION['db_password'], $idcoupon, $merchant_code, $phone_service, $customer_limit) == false)
                exit;
            break;
        case 'elimina_tutti_coupon_standard':
            try {
                $sxml_Coupon = APIFetch($url);
            } catch (Exception $ex) {
                logWrite("error", "Eccezione nell\'APIFetch (elimina_tutti_coupon_standard) della if (isset(_GET['action'])). Dettagli:" . $ex->getMessage());
                echo ($ex->getMessage());
                exit;
            }
            $logm = "Risposta dell API report_full per la eliminazione dei coupon standard(" . $url . "): Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
            if ($sxml_Coupon != false)
                logWrite("info", "$logm");
            else {
                if (in_array($ResultCode, $array_ignored_code_error))
                    logWrite("debug", "Errore nell\'APIFetch (report_full) per la eliminazione dei coupon standard IGNORATO da codice. Dettagli:$logm");
                else
                    logWrite("error", "Errore nell\'APIFetch (report_full) per la eliminazione dei coupon standard. Dettagli:$logm");
            }
            if (isset($sxml_Coupon->Coupon)) {
                foreach ($sxml_Coupon->Coupon as $Coupon) :
                    if ($Coupon->Coupon_custom == 0) { //Se è un custom
                        logWrite("info", "delete selected. couponid=$Coupon->Coupon_ID");
                        if (DeleteCoupon($_SESSION['db_user'], $_SESSION['db_password'], $Coupon->Coupon_ID, $Coupon->Merchant_code, $Coupon->Phone_service, $customer_limit))
                            logWrite("info", "cancellazione di tutti i coupon standard riuscita");
                        else
                            logWrite("error", "Errore cancellazione di tutti i standard custom");
                    }
                endforeach;
            }
            array_push($messages, "Tutti i coupon custom sono stati eliminati!");
            break;
        case 'elimina_tutti_coupon_custom':
            try {
                $sxml_Coupon = APIFetch($url);
            } catch (Exception $ex) {
                logWrite("error", "Eccezione nell\'APIFetch (elimina_tutti_coupon_custom) della if (isset(_GET['action'])). Dettagli:" . $ex->getMessage());
                echo ($ex->getMessage());
                exit;
            }
            $logm = "Risposta dell API report_full per la eliminazione dei coupon custom(" . $url . "): Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
            if ($sxml_Coupon != false)
                logWrite("info", "$logm");
            else {
                if (in_array($ResultCode, $array_ignored_code_error))
                    logWrite("debug", "Errore nell\'APIFetch (report_full) per la eliminazione dei coupon custom IGNORATO da codice. Dettagli:$logm");
                else
                    logWrite("error", "Errore nell\'APIFetch (report_full) per la eliminazione dei coupon custom. Dettagli:$logm");
            }
            if (isset($sxml_Coupon->Coupon)) {
                foreach ($sxml_Coupon->Coupon as $Coupon) :
                    if ($Coupon->Coupon_custom != 0) { //Se è un custom
                        logWrite("info", "delete selected. couponid=$Coupon->Coupon_ID");
                        if (DeleteCoupon($_SESSION['db_user'], $_SESSION['db_password'], $Coupon->Coupon_ID, $Coupon->Merchant_code, $Coupon->Phone_service, $customer_limit))
                            logWrite("info", "Cancellazione di tutti i coupon custom riuscita");
                        else
                            logWrite("error", "Errore cancellazione di tutti i coupon custom");
                    }
                endforeach;
            }
            array_push($messages, "Tutti i coupon custom sono stati eliminati!");
            break;
        case 'elimina_tutti_coupon':
            try {
                $sxml_Coupon = APIFetch($url);
            } catch (Exception $ex) {
                logWrite("error", "Eccezione nell\'APIFetch (elimina_tutti_coupon) della if (isset(_GET['action'])). Dettagli:" . $ex->getMessage());
                echo ($ex->getMessage());
                exit;
            }
            $logm = "Risposta dell API report_full per la eliminazione totale(" . $url . "): Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
            if ($sxml_Coupon != false)
                logWrite("info", "$logm");
            else {
                if (in_array($ResultCode, $array_ignored_code_error))
                    logWrite("debug", "Errore nell\'APIFetch (report_full) per la eliminazione di tutti i coupon IGNORATO da codice. Dettagli:$logm");
                else
                    logWrite("error", "Errore nell\'APIFetch (report_full) per la eliminazione totale. Dettagli:$logm");
            }

            if (isset($sxml_Coupon->Coupon)) {
                foreach ($sxml_Coupon->Coupon as $Coupon) :
                    logWrite("info", "delete selected. couponid=$Coupon->Coupon_ID");
                    if (DeleteCoupon($_SESSION['db_user'], $_SESSION['db_password'], $Coupon->Coupon_ID, $Coupon->Merchant_code, $Coupon->Phone_service, $customer_limit))
                        logWrite("info", "Cancellazione di tutti i coupon riuscita");
                    else
                        logWrite("error", "Errore cancellazione di tutti i coupon");
                endforeach;
            }
            array_push($messages, "Tutti i coupon sono stati eliminati!");
            break;
    }
}


//$ignored_code_error: contiene eventualii codici di errore ($ResultCode) che vengono ignorati separati da , (es:"-20,-19")
function DeleteCoupon($username, $password, $coupon_id, $merchant_code, $phone_service, $customer_limit, $ignored_code_error = "") {
    global $endPoint, $ResultCode, $CodeDescription, $MessageError, $errors;

    logWrite("info", "Inizio delete couponid=$coupon_id");
    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?"
            . "cmd=" . "delete"
            . "&username=" . $username
            . "&password=" . $password
            . "&coupon_id=" . $coupon_id
            . "&merchant_code=" . $merchant_code
            . "&phone_service=" . $phone_service;
    if (($customer_limit != "") && (!IsRetailer()))
        $url .= '&customer_limit=' . $customer_limit;
    logWrite("info", "delete url= $url");

    try {
        $RetVal = APIFetch($url);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (delete) della DeleteCoupon. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    if ($RetVal != false)
        logWrite("info", "Risposta dell API delete: Code:$ResultCode CodeDescription:$CodeDescription Dettagli:$MessageError");
    else {
        if ($ignored_code_error != "") {
            $array_ignored_code_error = explode(",", $ignored_code_error); //Mette i valori separati da virgola in un array
            foreach ($array_ignored_code_error as $singlecode) :
                if ($ResultCode == $singlecode) {
                    logWrite("debug", "Errore di APIFetch (delete) IGNORATO (da codice). Codice Errore:$ResultCode");
                    return true;
                }
            endforeach;
            $logm = "Errore dell API (" . $url . ") durante una delete: Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
            logWrite("error", "Errore nell APIFetch (delete). Dettagli:$logm");
        }
        array_push($errors, "Errore durante la cancellazione del coupon con id $coupon_id: " . $MessageError);
        return false;
    }
    logWrite("info", "Delete couponid=$coupon_id completato correttamente");
    return true;
}

//$ignored_code_error: contiene eventualii codici di errore ($ResultCode) che vengono ignorati separati da , (es:"-20,-19")
function ResetCoupon($username, $password, $coupon_code, $merchant_code, $phone_service, $user_cli, $start_date, $start_time, $stop_date,
        $stop_time, $customer_limit, $ignored_code_error = "") {
    global $endPoint, $ResultCode, $CodeDescription, $MessageError;

    logWrite("info", "Inizio reset couponcode=$coupon_code");
    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?"
            . "cmd=" . "coupon_reset"
            . "&username=" . $username
            . "&password=" . $password
            . "&coupon_code=" . $coupon_code
            . "&merchant_code=" . $merchant_code
            . "&phone_service=" . $phone_service
            . "&user_cli=" . $user_cli
            . "&start_date=" . dateformat($start_date)
            . "&start_time=" . $start_time
            . "&stop_date=" . dateformat($stop_date)
            . "&stop_time=" . $stop_time;
    if (($customer_limit != "") && (!IsRetailer()))
        $url .= '&customer_limit=' . $customer_limit;
    logWrite("info", "reset url= $url");

    try {
        $RetVal = APIFetch($url);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (reset_coupon) della ResetCoupon. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    if ($RetVal != false)
        logWrite("info", "'Risposta dell API reset: Code:$ResultCode CodeDescription:$CodeDescription Dettagli:$MessageError");
    else {
        if ($ignored_code_error != "") {
            $array_ignored_code_error = explode(",", $ignored_code_error); //Mette i valori separati da virgola in un array
            foreach ($array_ignored_code_error as $singlecode) :
                if ($ResultCode == $singlecode) {
                    logWrite("debug", "Errore di APIFetch (reset_coupon) IGNORATO (da codice). Codice Errore:$ResultCode");
                    return true;
                }
            endforeach;
            $logm = "Errore dell API (" . $url . ") durante una reset: Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
            logWrite("error", "Errore nell APIFetch (reset). Dettagli:$logm");
        }
        array_push($errors, "Errore durante il reset del coupon $coupon_code: " . $MessageError);
        return false;
    }
    logWrite("info", "Reset coupon_code=$coupon_code completato correttamente");
    return true;
}

class ResetCoupon {

    public $Coupon_ID;
    public $Coupon_code;
    public $Coupon_type;
    public $Coupon_value;
    public $Coupon_custom;
    public $Coupon_channel;
    public $Merchant_code;
    public $Phone_service;
    public $Start;
    public $Stop;

}

//Recupera i dati dell'eventuale coupon già inserito tramite l'API report_full
function FindCoupon($username, $password, $customer_limit, $coupon_code, $merchant_code, $phone_service, $coupon_channel) {
    global $endPoint, $errors;
    global $ResultCode, $CodeDescription, $MessageError;
    $array_ignored_code_error = array("-19"); //Errori DB ignorati da questa funzione
    logWrite("info", "Recupero tutti i dati del coupon: $coupon_code");
    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?username=" . $username . "&password=" . $password . "&customer_limit=" . $customer_limit . "&cmd=report_full";
    logWrite("info", "URL completo report_full della FindCoupon: $url");
    try {
        $sxml_Coupon = APIFetch($url);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (report_full) della FindCoupon. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    if ($sxml_Coupon != false)
        logWrite("info", "Risposta dell API report_full: Code:$ResultCode CodeDescription:$CodeDescription Dettagli:$MessageError");
    else {
        $logm = "Errore dell API (" . $url . ") durante una FindCoupon: Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
        if (in_array($ResultCode, $array_ignored_code_error))
            logWrite("debug", "Errore nell\'APIFetch (report_full) della FindCoupon IGNORATO da codice. Dettagli:$logm");
        else
            logWrite("error", "Errore nell\'APIFetch (report_full) della FindCoupon. Dettagli:$logm");
    }
    if (isset($sxml_Coupon->Coupon)) {
        $userObj = new Finded_Coupon();
        foreach ($sxml_Coupon->Coupon as $Coupon) :
            if (($Coupon->Coupon_code == $coupon_code) && ($Coupon->Merchant_code == $merchant_code) &&
                    ($Coupon->Phone_service == $phone_service) && ($Coupon->Coupon_channel == $coupon_channel)) {
                $userObj->Coupon_ID = $Coupon->Coupon_ID;
                $userObj->Coupon_code = $Coupon->Coupon_code;
                $userObj->Coupon_type = $Coupon->Coupon_type;
                $userObj->Coupon_value = $Coupon->Coupon_value;
                $userObj->Coupon_custom = $Coupon->Coupon_custom;
                $userObj->Coupon_channel = $Coupon->Coupon_channel;
                $userObj->Merchant_code = $Coupon->Merchant_code;
                $userObj->Phone_service = $Coupon->Phone_service;
                $userObj->Start = $Coupon->Start;
                $userObj->Stop = $Coupon->Stop;
                logWrite("info", "FindCoupon: Coupon con codice $coupon_code trovato");
                return $userObj;
            }
        endforeach;
    }
    logWrite("info", "Coupon con codice $coupon_code NON trovato");
    return null;
}

function ReturnIdCoupon($username, $password, $coupon_code, $merchant_code, $phone_service, $coupon_channel, $customer_limit, $ignored_code_error = "") {
    global $endPoint, $errors;
    global $ResultCode, $CodeDescription, $MessageError;

    //Recupero l'ID del coupon da cancellare
    $urlid = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?"
            . "cmd=" . "return_id"
            . "&username=" . $username
            . "&password=" . $password
            . "&coupon_code=" . $coupon_code
            . "&merchant_code=" . $merchant_code
            . "&phone_service=" . $phone_service
            . "&coupon_channel=" . $coupon_channel
            . "&customer_limit=" . $customer_limit;

    //Eseguo l'interrogazione dell'ID univoco
    logWrite("info", "Eseguo il recupero dell ID (return_id) del coupon $coupon_code . Comando:$urlid");
    try {
        $sxml_Coupon = APIFetch($urlid);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (return_id) della ReturnIdCoupon. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    if ($sxml_Coupon != false) {
        logWrite("info", "Risposta dell API return_id: Code:$ResultCode CodeDescription:$CodeDescription Dettagli:$MessageError");
        if ($ResultCode != '0')
            array_push($errors, $MessageError);
    } else {
        if ($ignored_code_error != "") {
            $array_ignored_code_error = explode(",", $ignored_code_error); //Mette i valori separati da virgola in un array
            foreach ($array_ignored_code_error as $singlecode) :
                if ($ResultCode == $singlecode) {
                    logWrite("debug", "Errore di APIFetch (return_id) IGNORATO (da codice). Codice Errore:$ResultCode");
                    return true;
                }
            endforeach;
        }
        $logm = "Errore dell API (" . $urlid . ") : Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
        logWrite("error", "Errore nell APIFetch (return_id). Dettagli:$logm");
        array_push($errors, "Errore durante il recuper dell'ID del coupon $coupon_code: " . $MessageError);
        return false;
    }
    $coupon_id = "";
    if (isset($sxml_Coupon->Coupon)) {
        foreach ($sxml_Coupon->Coupon as $Coupon) :
            $coupon_id = $Coupon->Coupon_ID;
        endforeach;
    }

    return $coupon_id;
}

//$ignored_code_error: contiene eventualii codici di errore ($ResultCode) che vengono ignorati separati da , (es:"-20,-19")
//$coupon_custom se ="" non viene inserito
function NewCoupon($username, $password, $merchant_code, $phone_service, $coupon_code, $coupon_type, $coupon_value, $coupon_channel, $coupon_custom,
        $start_date, $start_time, $stop_date, $stop_time, $customer_limit, $unlimited, $ignored_code_error = "") {
    global $endPoint, $errors;
    global $ResultCode, $CodeDescription, $MessageError;

    logWrite("info", "Inizio inserimento nuovo coupon=$coupon_code");
    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?"
            . "cmd=" . "new"
            . "&username=" . $username
            . "&password=" . $password
            . "&merchant_code=" . $merchant_code
            . "&phone_service=" . $phone_service
            . "&coupon_code=" . $coupon_code
            . "&coupon_type=" . $coupon_type
            . "&coupon_value=" . $coupon_value
            . "&coupon_channel=" . $coupon_channel;
    if ($coupon_custom != "")
        $url .= "&coupon_custom=" . $coupon_custom;
    $url .= "&start_date=" . $start_date
            . "&start_time=" . $start_time
            . "&stop_date=" . $stop_date
            . "&stop_time=" . $stop_time
            . "&customer_limit=" . $customer_limit
            . "&unlimited=" . $unlimited;

    logWrite("info", "submitCoupon url= $url");
    //echo ("submitCoupon url= $url");
    try {
        $RetVal = APIFetch($url);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (new) della NewCoupon. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }

    if ($RetVal == true) {
        logWrite("info", "Risposta dell API new: Code:$ResultCode CodeDescription:$CodeDescription Dettagli:$MessageError");
        if ($ResultCode != '0')
            array_push($errors, $MessageError);
    } else {
        if ($ignored_code_error != "") {
            $array_ignored_code_error = explode(",", $ignored_code_error); //Mette i valori separati da virgola in un array
            foreach ($array_ignored_code_error as $singlecode) :
                if ($ResultCode == $singlecode) {
                    logWrite("debug", "Errore di APIFetch (new) IGNORATO (da codice). Codice Errore:$ResultCode");
                    return true;
                }
            endforeach;
        }
        $logm = "Errore dell API (" . $url . ") : Code:" . $ResultCode . " CodeDescription:" . $CodeDescription . " Dettagli:" . $MessageError;
        logWrite("error", "Errore nell APIFetch (new). Dettagli:$logm");
        array_push($errors, "Errore durante l'inserimento del coupon  $coupon_code: " . $MessageError);
        return false;
    }
    logWrite("info", "Inserimento nuovo coupon=$coupon_code completato correttamente");
    return true;
}

// Function for basic field validation (present and neither empty nor only white space
function IsNullOrEmptyString($str){
    return (!isset($str) || trim($str) === '');
}

function LoadCLIFIle($target_file, $FILES_INDEX, $desc_file_type,$index=0) {
    global $messages, $errors;
    $ListaCLIOK = array();
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Allow certain file formats
    if ($imageFileType != "txt") {
        array_push($errors, "File $desc_file_type errato. Ricorda:Sono permessi solamente file '.TXT'.");
    } else {
        if (file_exists($target_file)) {
            unlink($target_file);
            //..e vado avanti
        }
        if(is_array($_FILES[$FILES_INDEX]["tmp_name"])){
            $tmpFile =($_FILES[$FILES_INDEX]["tmp_name"])[$index];
        }
        else {
            $tmpFile =$_FILES[$FILES_INDEX]["tmp_name"];
        }
                
        if (move_uploaded_file($tmpFile, $target_file)) {
            //Il file è stato uploadato
            $handle = fopen($target_file, "r");
            if ($handle) {
                //Controllo di coerenza sui file
                $ListaCLI = [];
                $counterBuoni = 0;
                $counterTot = 0;
                while (($line = fgets($handle)) !== false) {
                    // process the line read.
                    $line_trimmed = trim($line); //tolgo eventuali spaziF
                    $counterTot += 1;

                    if (strlen($line_trimmed) == 0) {
                        array_push($messages, "Riga " . $counterTot . ": Riga Vuota. Ho corretto il problema");
                        continue;
                    }
                    if (substr($line_trimmed, 0, 3) === "+39") {
                        array_push($messages, "Riga " . $counterTot . " Numero:" . $line_trimmed . ": +39 trovato. Ho corretto il problema");
                        array_push($ListaCLI, substr($line_trimmed, 3)); //Tolgo il '+39'
                        $counterBuoni += 1;
                        continue;
                    }
                    if (substr($line_trimmed, 0, 4) === "0039") {
                        array_push($messages, "Riga " . $counterTot . " Numero:" . $line_trimmed . ": 0039 trovato. Ho corretto il problema");
                        array_push($ListaCLI, substr($line_trimmed, 4)); //Tolgo '0039'
                        $counterBuoni += 1;
                        continue;
                    }
                    if (!ctype_digit($line_trimmed)) {
                        array_push($errors, "Riga " . $counterTot . " Numero:" . $line_trimmed . " :numero telefonico contenente valori non numerici. Numero scartato");
                        continue;
                    }
                    if (!ControllaPrefisso(substr($line_trimmed, 0, 3))) {
                        array_push($errors, "Riga " . $counterTot . " Numero:" . $line_trimmed . " :Prefisso non consentito. Numero scartato");
                        continue;
                    }
                    array_push($ListaCLI, $line_trimmed); //Se non ho trovato errori nella riga la inserisco com'è
                }//fine while
                fclose($handle);

                //Rimuovo i doppioni
                $ListaCLIOK = array_unique($ListaCLI);
                $nElemDuplicati = count($ListaCLI) - count($ListaCLIOK);
                if ($nElemDuplicati > 0)
                    array_push($errors, "No. di elementi duplicati nel file: " . (count($ListaCLI) - count($ListaCLIOK)));

                //Stampo contenuto array pulito da doppioni
                $ListaResult = ""; //Contiene il risultato da stampare
                foreach ($ListaCLIOK as $ElemListaCLIOK) {
                    $ListaResult = $ListaResult . trim($ElemListaCLIOK) . " "; //\r\n non lo accetta;
                }
            }//fine if (handle)
            else {
                // error opening the file.
                array_push($errors, "Errore di apertura del file");
            }
        }//fine if (move_uploaded_file
        else { //else if (move_uploaded_file
            array_push($errors, "Spiacente, errore durante l\'upload del file");
        }
    }
    return $ListaCLIOK;
}

//ControllaPrefisso (PHP)
function controllaPrefisso($num)
{
	$prefissiConsentiti = array("320","322","323","383","324","327","328","329","330","331","333","334","335","336","337","338","339","340","341","342","343","344","345","346","347","348","349","350","351","353","360","361","362","363","366","368","370","371","373","375","376","377","379","380","383","388","389","390","391","392","393","397");
	$retval=false;
	foreach($prefissiConsentiti as $prefisso)
	{
		if(substr($num, 0, 3)==$prefisso)
			$retval=true;
	}
	return $retval;
}

//Restituisce nel formato corretto per le specifiche una time prelevato da un input type="time" 
function timeformat($time){
	$newtime=$time.":00";
	return $newtime;
}

//Restituisce nel formato corretto per le specifiche una data prelevata da un input type="date" 
function dateformat($date){
/*	logWrite("info","Prima vale : $date");
	// Create a new DateTime object
	$mydate = DateTime::createFromFormat('Y/m/d', $date);

	// Output the date in different formats
	$newdate=$mydate->format('d/m/Y');*/

	$newdate=date("d/m/Y", strtotime($date));
	//logWrite("info","Dopo data vale : $date");
	return $newdate;
}

function is_number($numbers){
	foreach($numbers as $n)
	{
    	if( preg_match('/^\d+$/',$n) )
        	continue;
    	else
        	return false;
	}
	return true;
}