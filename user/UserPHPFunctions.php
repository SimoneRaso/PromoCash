<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (isset($_POST['salva_su_file'])) {
    $today = date("d-m-Y H.i.s");
    $nomefile = 'Cli' . $today . '.txt';

    try {
        $ListaCLIOK = GetCouponCli(false);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nel POST salva_su_file . Dettagli:" . $ex->getMessage());
        echo($ex->getMessage());
        exit;
    }

    try {
        $file_handle = fopen($nomefile, "w");
        if (!$file_handle) {
            logWrite("error", "Impossibile creare il file con i cli per il download.");
            throw new Exception('file.txt: file new failed.');
        }

        //cancello i vecchi files di cli presenti nella cartella
        foreach (glob("Cli*.txt") as $f) {
            if ($f == $nomefile)
                continue; //salto il file corrente
            unlink($f);
        }

        //Scrivo i Cli sul file
        foreach ($ListaCLIOK as $cli):
            fwrite($file_handle, $cli . "\r\n");
        endforeach;
        fclose($file_handle);
    } catch (Exception $e) {
        logWrite("error", "Eccezione nella salva_su_file.Dettagli:" . $e->getMessage());
    }

    // verifico che il file esista
    if (!file_exists($nomefile)) {
        // se non esiste stampo un errore
        echo "Il file non è stato creato!";
    } else {
        download($nomefile);
    }
}

function download($path, $name = '', $type = 'application/octet-stream', $force_download = true) {

    if (!is_file($path))
        logWrite("error", "il file da uplodare non esiste");

    switch (connection_status()) {
        case CONNECTION_NORMAL:
            break;
        case CONNECTION_ABORTED:
            logWrite("error", "Connection aborted");
            exit;
        case CONNECTION_TIMEOUT:
            logWrite("error", "Connection timed out");
            exit;
        case (CONNECTION_ABORTED & CONNECTION_TIMEOUT):
            logWrite("error", "Connection aborted and timed out");
            exit;
        default:
            $txt = 'Unknown';
            exit;
    }

    if ($force_download) {
        header("Cache-Control: public");
    } else {
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    header("Expires: " . gmdate("D, d M Y H:i:s", mktime(date("H") + 2, date("i"), date("s"), date("m"), date("d"), date("Y"))) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Content-Type: $type");
    header("Content-Length: " . (string) (filesize($path)));

    $disposition = $force_download ? 'attachment' : 'inline';

    if (trim($name) == '') {
        header("Content-Disposition: $disposition; filename=" . basename($path));
    } else {
        header("Content-Disposition: $disposition; filename=\"" . trim($name) . "\"");
    }

    header("Content-Transfer-Encoding: binary\n");

    ob_clean();
    flush();

    try {

        if ($file = fopen($path, 'rb')) {
            while (!feof($file) and (connection_status() == 0)) {
                print(fread($file, 1024 * 8));
                flush();
            }
            fclose($file);
        }
    } catch (Exception $ex) {
        $ErrMsg = "Eccezione nella salva_su_file.Dettagli:" . $ex->getMessage();
        logWrite("error", $ErrMsg);
        die($ErrMsg);
    }
    die();
}

function GetConsumedCouopon($Merchant_code, $Phone_service, $Coupon_Channel, $StartDate, $StartTime, $StopDate, $StopTime, $ignored_code_error = "", $Echoenabled = true) {
    global $endPoint, $ResultCode, $CodeDescription, $MessageError;

    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?username=" . $_SESSION['db_user'] . "&password=" . $_SESSION['db_password'] . "&cmd=coupon_usage";
    if (isset($_SESSION['user']['username']))
        $url = $url . '&customer_limit=' . $_SESSION['user']['username'];
    $url .= "&merchant_code=" . $Merchant_code;
    $url .= "&phone_service=" . $Phone_service;
    $url .= $Coupon_Channel;
    $url .= $StartDate . $StartTime . $StopDate . $StopTime;

    try {
        $sxml_Coupon = APIFetch($url);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (coupon_usage) della GetConsumedCouopon. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    if ($sxml_Coupon != false) {
        $logm = "Risposta dell API " . $url . ": Code=" . $ResultCode . " CodeDescription=" . $CodeDescription . " Dettagli=" . $MessageError;
        logWrite("info", $logm, $Echoenabled);
    } else {
        if ($ignored_code_error != "") {
            $array_ignored_code_error = explode(",", $ignored_code_error); //Mette i valori separati da virgola in un array
            if (in_array($ResultCode, $array_ignored_code_error))
                logWrite("debug", "Errore di APIFetch (coupon_usage) IGNORATO (da codice). Codice Errore:$ResultCode. Chiamata API: $url", $Echoenabled);
            else
                logWrite("error", "Errore nell\'APIFetch (coupon_usage). Dettagli:$MessageError", $Echoenabled);
        } else {
            logWrite("error", "Errore nell\'APIFetch (coupon_usage). Dettagli:$MessageError", $Echoenabled);
            exit;
        }
    }
    return $sxml_Coupon;
}

function GetCouoponStatistic($Merchant_code, $Phone_service, $Coupon_Channel, $StartDate, $StartTime, $StopDate, $StopTime, $ignored_code_error = "", $Echoenabled = true) {
    global $endPoint, $ResultCode, $CodeDescription, $MessageError;

    $url = "https://secure.tcserver.it/cgi-bin/" . $endPoint . "?username=" . $_SESSION['db_user'] . "&password=" . $_SESSION['db_password'] . "&cmd=coupon_log";
    if (isset($_SESSION['user']['username']))
        $url = $url . '&customer_limit=' . $_SESSION['user']['username'];
    $url .= "&merchant_code=" . $Merchant_code;
    $url .= "&phone_service=" . $Phone_service;
    $url .= $Coupon_Channel;
    $url .= $StartDate . $StartTime . $StopDate . $StopTime;

    try {
        $sxml_Coupon = APIFetch($url);
    } catch (Exception $ex) {
        logWrite("error", "Eccezione nell\'APIFetch (coupon_usage) della GetConsumedCouopon. Dettagli:" . $ex->getMessage());
        throw new Exception($ex->getMessage());
    }
    if ($sxml_Coupon != false) {
        $logm = "Risposta dell API " . $url . ": Code=" . $ResultCode . " CodeDescription=" . $CodeDescription . " Dettagli=" . $MessageError;
        logWrite("info", $logm, $Echoenabled);
    } else {
        if ($ignored_code_error != "") {
            $array_ignored_code_error = explode(",", $ignored_code_error); //Mette i valori separati da virgola in un array
            if (in_array($ResultCode, $array_ignored_code_error))
                logWrite("debug", "Errore di APIFetch (coupon_usage) IGNORATO (da codice). Codice Errore:$ResultCode. Chiamata API: $url", $Echoenabled);
            else
                logWrite("error", "Errore nell\'APIFetch (coupon_usage). Dettagli:$MessageError", $Echoenabled);
        } else {
            logWrite("error", "Errore nell\'APIFetch (coupon_usage). Dettagli:$MessageError", $Echoenabled);
            exit;
        }
    }
    return $sxml_Coupon;
}

function filter_callback($element) {
    if (IsNullOrEmptyString($_POST['nome_coupon']))
        return TRUE;

    $filter = $_POST['nome_coupon'];
    if (isset($element->Coupon_code)) {
        $tolowelem = strtolower($element->Coupon_code);
        $tolowfilter = strtolower($filter);
        if (strpos($tolowelem, $tolowfilter) !== false)
            return TRUE;
    }
    return FALSE;
}