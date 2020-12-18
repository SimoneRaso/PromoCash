//Mostra o nasconde un elemento con id = id e v = true o false
function mostra(id,v) {
	if(document.getElementById(id)!=null)
		document.getElementById(id).style.display=v?'block':'none';
}

function isANumber(str){return /^\d+$/.test(str);}

function isNumberKey(evt){
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;
	return true;
}

//Rimuove il country da un numero telefoni senza riferimenti al country caricato
//Nota: Funziona solo con il prefisso internazionale italiano (39)
function RemoveCountryWoutRef(Tel){
    var RetVal="";
    var Prefix= String(Tel).substr(0,3);

    if(Prefix=="390")
        RetVal="";
    if(Prefix=="393")
        RetVal=String(Tel).substr(2);
    return RetVal;
}

//esegue il download di un file di testo (filename) contentente il testo (text)
function download(filename, text) 
{
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
}