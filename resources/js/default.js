var varDebug = false

function recarregarPagina(){
    location.reload();
}

// prepare the form when the DOM is ready
function alertControleAcesso(data) {
    if (data.controleAcessoStatus == '0') {
        alert(data.controleAcessoMsg);
        return false;
    }
}
$('#sucesso').click(function() {
    loading('div#sucesso', 'hidden');
});
$('#erro').click(function() {
    loading('div#sucesso', 'hidden');
});
// prepare the form when the DOM is ready
function processJson(data) {
    if ((data.statusMessage == 1) || (data.statusMessage == 'ok')) {
        //alert('Sucesso: '+ data.message);
        loading('div#sucesso', 'show');
        loading('div#carregando', 'hidden');
        loading('div#erro', 'hidden');
        $('div#sucesso').html(data.message);
//        $('div#sucesso').fadeIn('fast');
        $('div#sucesso').fadeOut(5000, function() {
            loading('div#sucesso', 'hidden');
        });
        
    } else {
        if (data.statusMessage == 0) {
            //alert('Erro J01: ' + data.message);
            loading('div#sucesso', 'hidden');
            loading('div#erro', 'show');
            loading('div#carregando', 'hidden');
            $('div#erro').html(data.message);
        } else {
            //alert('Erro J02: ' + ajax.responseText);
            loading('div#sucesso', 'hidden');
            loading('div#erro', 'show');
            loading('div#carregando', 'hidden');
            $('div#erro').html(ajax.responseText);
        }
    }
}
function processJsonLimpar() {
    loading('div#sucesso', 'hidden');
    loading('div#carregando', 'hidden');
    loading('div#erro', 'hidden');
    $('div#sucesso').html('');
    $('div#erro').html('');
}
function processJsonSemFade(data) {
    if ((data.statusMessage == 1) || (data.statusMessage == 'ok')) {
        //alert('Sucesso: '+ data.message);
        loading('div#sucesso', 'show');
        loading('div#carregando', 'hidden');
        loading('div#erro', 'hidden');
        $('div#sucesso').addClass('show');
        $('div#sucesso').html(data.message);
        $('div#sucesso').fadeIn('fast');
    } else {
        if (data.statusMessage == 0) {
            //alert('Erro J01: ' + data.message);
            loading('div#sucesso', 'hidden');
            loading('div#erro', 'show');
            loading('div#carregando', 'hidden');
            $('div#erro').html(data.message);
        } else {
            //alert('Erro J02: ' + ajax.responseText);
            loading('div#sucesso', 'hidden');
            loading('div#erro', 'show');
            loading('div#carregando', 'hidden');
            $('div#erro').html(ajax.responseText);
        }
    }
//alert('json');
}
function processJsonElement(data, element) {
    if ((data.statusMessage == 1) || (data.statusMessage == 'ok')) {
        /*mostra verde*/
        $(element).html(data.message);
        $(element).addClass('sucesso');
    } else {
        if (data.statusMessage == 0 || data.statusMessage == '') {
            $(element).html(data.message);
            $(element).addClass('erro');
        } else {
            $(element).html(data.message);
            $(element).addClass('erro');
        }
    }
//alert('json');
}
function processJsonElementLeve(data, element) {
    if ((data.statusMessage == 1) || (data.statusMessage == 'ok')) {
        /*mostra verde*/
        $(element).html(data.message);
        $(element).addClass('sucessoLeve');
    } else {
        if (data.statusMessage == 0 || data.statusMessage == '') {
            $(element).html(data.message);
            $(element).addClass('erroLeve');
        } else {
            $(element).html(data.message);
            $(element).addClass('erroLeve');
        }
    }
//alert('json');
}
// post-submit callback
function showResponse(ajax, statusText, xhr, $form) {
    if (statusText == 'timeout') {
        //alert('Tempo de execução esgotado.');
        loading('div#erro', 'show');
        loading('div#carregando', 'hidden');
        $('div#erro').html('Tempo de execução esgotado' + ajax.responseText);
    }
    if (statusText == 'error') {
        //alert('Error: ' + ajax);
        loading('div#erro', 'show');
        loading('div#carregando', 'hidden');
        $('div#erro').html(ajax);
    }
    //alert('showReponse: ' + ajax.responseText);
    loading('div#erro', 'show');
    loading('div#carregando', 'hidden');
    $('div#erro').html(ajax.responseText);
//alert('response');
}
function loading(id, acao) {
    if (acao == 'show') {
        $(id).removeClass('hidden');
        $(id).addClass('show');
    } else {
        $(id).removeClass('show');
        $(id).addClass('hidden');
    }
}
function startLoading() {
    //alert('start Ajax');        
    $('div#mensagem').html('');
    loading('div#carregando', 'show');
    loading('div#sucesso', 'hidden');
    loading('div#erro', 'hidden');
}
function stopLoading() {
    //alert('stop Ajax');
    loading('div#carregando', 'hidden');
}

//------------------------------------------------------------------------------
function testaCampo(element, Msg)
{
    var element
    if (element) {
        if ((element.value == "") || (element.value == "-1")) {
            alert(Msg);
            element.focus();
            return false;
        } else {
            return true;
        }
    }
    else
    {
        //alert('O elemento testado não esta definido')
        //return false;
        return true
    }
}
function testaCampoRadio(element, Msg)
{
    var element
    if (element) {
        if (getCheckedRadioButton(element) == -1) {
            alert(Msg);
            element[0].focus();
            return false;
        } else {
            return true;
        }
    }
    else
    {
        //alert('O elemento testado não esta definido')
        //return false;
        return true
    }
}

function comparaValor(radioSet, value) {
    if (getCheckedRadioButton(radioSet) != -1) {
        if (getValueCheckedRadioButton(radioSet) == value) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }

}
function getKey(e) {
    //retorna a tecla pressionada - Internet Explorer , Mozilla e Netscape
    var code;

    if (e.charCode)
        code = e.charCode; // Mozilla
    else if (e.which)
        code = e.which; // Netscape 4.?
    else if (e.keyCode)
        code = e.keyCode;
    if (varDebug)
        alert(code);
    return code;
}

function MaxCaracter(txarea, limite) {

    /****************************************
     * Para contar caracteres e bloquear a digitação ao exceder o limite de 50 caracteres;
     * Siga o exemplo abaixo:
     *
     * <font id=Digitado color=red>0</font> Caracteres digitados &nbsp; / &nbsp; restam <font id=Restante color=red>50</font>
     * <textarea onkeyup="MaxCaracter(this,50)" onkeypress="MaxCaracter(this,50)" rows="2" cols="35" name="Area"></textarea><br>
     *****************************************/

    total = limite;
    tam = txarea.value.length;
    str = "";
    str = str + tam;
    Digitado.innerHTML = str;
    Restante.innerHTML = total - str;

    if (tam > total) {
        aux = txarea.value;
        txarea.value = aux.substring(0, total);
        Digitado.innerHTML = total
        Restante.innerHTML = 0
        alert("Seu resumo atingiu o limite de " + limite + " caracteres");
    }
}

function MaximoCaracter(txarea, limite, digitado, restante) {

    /****************************************
     * Para contar caracteres e bloquear a digitação ao exceder o limite de 50 caracteres;
     * Siga o exemplo abaixo:
     *
     * <font id=Digitado color=red>0</font> Caracteres digitados &nbsp; / &nbsp; restam <font id=Restante color=red>50</font>
     * <textarea onkeyup="MaxCaracter(this,50)" onkeypress="MaxCaracter(this,50)" rows="2" cols="35" name="Area"></textarea><br>
     *****************************************/

    vardigitado = document.getElementById(digitado);
    varrestante = document.getElementById(restante);
    

    var total = limite;
    var tam = txarea.value.length;
    var str = "";
    str = str + tam;
    vardigitado.innerHTML = str;
    varresto = total - str;
    varrestante.innerHTML = varresto;

    if (tam > total) {

        alert(varrestante);

        aux = txarea.value;

        txarea.value = aux.substring(0, total);
        vardigitado.innerHTML = total
        varrestante.innerHTML = 0
        alert("Seu resumo atingiu o limite de " + limite + " caracteres");
    }
}

function isTelefone(key)
{
    //alert(key);
    if ((key != 8) && (key != 9) && (key != 13) && (key != 37) && (key != 38) && (key != 39) && (key != 40) && (key != 46)) //libera o <enter>
        if (key < 48 || key > 57) // libera Numeros
            if ((key != 45) & (key != 40) & (key != 41) & (key != 32))   // libera Hifen AND ( -> Parentese AND  ) -> Parentese AND  Espaço
                return false;
}

function isRg(key)
{
    //alert(key);
    if ((key != 8) && (key != 9) && (key != 13) && (key != 37) && (key != 38) && (key != 39) && (key != 40) && (key != 46)) //libera o <enter>
        if (key < 48 || key > 57) // libera Numeros
            if ((key != 45) & (key != 40) & (key != 41) & (key != 32))   // libera Hifen AND ( -> Parentese AND  ) -> Parentese AND  Espaço
                return false;
}

function isNumHifens(key)
{
    //alert(key);
    if ((key != 8) && (key != 9) && (key != 13) && (key != 37) && (key != 38) && (key != 39) && (key != 40) && (key != 46)) //libera o <enter>
        if (key < 48 || key > 57)
            if (key != 45)
                return false;
}
function isNum(key)
{
  //alert(key);
  if (( key!=8) && ( key!=9) && ( key!=13) && ( key!=37) && ( key!=38) && ( key!=39) && ( key!=40) && ( key!=46)) //libera o <enter>
     if ( key<48  ||  key>57  )
        return false;
}
function isNumLibCola(key)
{
    var ctrl = window.event.ctrlKey;
    var tecla = window.event.keyCode;

//    if (ctrl && tecla == 67) {
//        alert("CTRL+C");
//        event.keyCode = 0;
//        event.returnValue = false;
//    }
//
//    if (ctrl && tecla == 86) {
//        alert("CTRL+V");
//        event.keyCode = 0;
//        event.returnValue = false;
//    }
    if ((key != 8) && (key != 9) && (key != 13) && (key != 37) && (key != 38) && (key != 39) && (key != 40) && (key != 46) && (ctrl && tecla != 67) && (ctrl && tecla != 86)) //libera o <enter>
        if (key < 48 || key > 57)
            return false;
    /*
     if((key>47 && key<58)) return true;
     else{
     if (key==8 || key==0) return true;
     else  return false;
     }
     */
}
function isNumInt(key)
{
    //alert(key);
    if ((key != 8) && (key != 9) && (key != 13) && (key != 37) && (key != 38) && (key != 39) && (key != 40) && (key != 46)) //libera o <enter>
        if (key < 48 || key > 57)
            return false;

    if (key == 46)
        return false;
    /*
     if((key>47 && key<58)) return true;
     else{
     if (key==8 || key==0) return true;
     else  return false;
     }
     */
}

function isReal(key) //função para valor em R%
{
    //48 a 57 numbers
    //44 comma
    //46 dot

    if (!((key >= 48 && key <= 57) || (key == 44) || (key == 46)))
        return false;
}

function isNumAlfa(key) //permitir digitação de números e letras somente (sem hífens, pontos, espaços)
{
    //alert(key);
    if ((key < 48 || key > 57) && (key < 97 || key > 122) && (key < 65 || key > 90))
        return false;
}

function isAlfa(key) //permitir digitação somente de letras
{
    //alert(key);
    if (key >= 48 && key <= 57)
        return false;

    if (key >= 33 && key <= 41) //bloqueia caracteres especiais (#$%&*\";\'!?)
        return false;

    if (key == 92) //bloqueia caracteres especiais \
        return false;

}

// Returns true if string s is empty
function isEmpty(s)
{
    return ((s == null) || (s.length == 0));
}


// Returns true if string s is empty or all blank chars
function isBlank(s)
{
    var i;

    // Is s empty?
    if (isEmpty(s))
        return true;


    // Search through string's chars one by one until we find first
    // non-blank char, then return false; if we don't, return true
    for (i = 0; i < s.length; i++)
    {
        // Check that current character isn't blank
        var c = s.charAt(i);
        if (blanks.indexOf(c) == -1)
            return false;
    }
    // All characters are blank
    return true;
}

function trimUpper(element)
{

    txt = element.value.toUpperCase();

    //alert(txt.charAt(0));
    //alert(txt.length);
    //alert (txt.length-1+"->'"+txt.charAt(txt.length-1)+"'");


    while (txt.charAt(0) == " " || txt.charAt(0) == "'" || txt.charAt(0) == "\"")
    {
        txt = txt.substring(1, txt.length); // Retira os espaços e caracteres especiais do inicio da string
    }

    while (txt.charAt(txt.length - 1) == " ")
    {
        //alert("'" + txt + "'" );
        txt = txt.substring(0, txt.length - 1); // Retira os espaços do final da string
        //alert("'" + txt + "'" );
    }

    element.value = txt;

}

function trim(element)
{

    txt = element.value;

    //alert(txt.charAt(0));
    //alert(txt.length);
    //alert (txt.length-1+"->'"+txt.charAt(txt.length-1)+"'");


    while (txt.charAt(0) == " " || txt.charAt(0) == "'" || txt.charAt(0) == "\"")
    {
        txt = txt.substring(1, txt.length); // Retira os espaços e caracteres especiais do inicio da string
    }

    while (txt.charAt(txt.length - 1) == " ")
    {
        //alert("'" + txt + "'" );
        txt = txt.substring(0, txt.length - 1); // Retira os espaços do final da string
        //alert("'" + txt + "'" );
    }

    element.value = txt;

}

function trimEmail(element)
{

    txt = element.value;

    //alert(txt.charAt(0));
    //alert(txt.length);
    //alert (txt.length-1+"->'"+txt.charAt(txt.length-1)+"'");


    while (txt.charAt(0) == " " || txt.charAt(0) == "'" || txt.charAt(0) == "\"")
    {
        txt = txt.substring(1, txt.length); // Retira os espaços e caracteres especiais do inicio da string
    }

    while (txt.charAt(txt.length - 1) == " ")
    {
        //alert("'" + txt + "'" );
        txt = txt.substring(0, txt.length - 1); // Retira os espaços do final da string
        //alert("'" + txt + "'" );
    }

    element.value = txt;

}
//------------------------------------------------------------------------

function checkDate(date)
{

    day = parseInt(date.substring(0, 2), 10);
    a = date.substring(2, 3);
    month = parseInt(date.substring(3, 5), 10);
    b = date.substring(5, 6);
    year = date.substring(6, 10);

    //alert(day + "/" + month + "/" + year);

    //verifica se o formato é valido
    if (a != "/")
        return false;

    if (b != "/")
        return false;

    //verifica se foram informados dias em formato correto
    if (isNaN(day))
        return false;

    if (isNaN(month))
        return false;

    if ((isNaN(year)) || (year.length != 4))
        return false;

    if ((isNaN(year)) || (year.length != 4))
        return false;

    // verifica o dia valido para cada mes
    if ((day < 1) || (day < 1 || day > 30) && (month == 4 || month == 6 || month == 9 || month == 11) || day > 31)
        return false;

    // verifica se o mes e valido
    if (month < 1 || month > 12)
        return false;

    if (year < 1920 || year > 1991)
        return false;

    // verifica se e ano bissexto
    if (month == 2 && (day < 1 || day > 29 || (day > 28 && (parseInt(year / 4) != year / 4))))
        return false;

    return true;

}

//verificação da datas
function checkDateMaior(date)
{

    day = parseInt(date.substring(0, 2), 10);
    a = date.substring(2, 3);
    month = parseInt(date.substring(3, 5), 10);
    b = date.substring(5, 6);
    year = date.substring(6, 10);

    //alert(day + "/" + month + "/" + year);

    //verifica se o formato é valido
    if (a != "/")
        return false;

    if (b != "/")
        return false;

    //verifica se foram informados dias em formato correto
    if (isNaN(day))
        return false;

    if (isNaN(month))
        return false;

    if ((isNaN(year)) || (year.length != 4))
        return false;

    if ((isNaN(year)) || (year.length != 4))
        return false;

    // verifica o dia valido para cada mes
    if ((day < 1) || (day < 1 || day > 30) && (month == 4 || month == 6 || month == 9 || month == 11) || day > 31)
        return false;

    // verifica se o mes e valido
    if (month < 1 || month > 12)
        return false;

    if (year < 1920 || year > 1989)
        return false;

    // verifica se e ano bissexto
    if (month == 2 && (day < 1 || day > 29 || (day > 28 && (parseInt(year / 4) != year / 4))))
        return false;

    return true;

}

function dateMask(date, field)
{
    var dateAtual;
    tamanho = date.length;
    if ((tamanho == 8) && (date.indexOf("/") <= 0)) {
        //dia e mês com um dígito e ano com quatro dígitos
        if (date.length == 8) {
            dateAtual = date.substring(0, 2) + '/' + date.substring(2, 4) + '/' + date.substring(4, 8);
            field.value = dateAtual;
            field.blur();
        }
    }
    field.focus();
    return true;
}

function isDateFormat(key) //data com barras
{
    if (key == 0 || key == 8 || key == 32) //tab, backspace, space (netscape)
        return true;

    if (((key < 47) || (key > 57)))
        return false;
}

function CheckEmail(element)
{
    // testa se o email foi preenchido e se está no formato correto
    //alert(element);
    if (element.value != "") {
        if (!isEmail(element.value)) {
            alert("O email deve ser preenchido no formato correto (usuário@dominio).");
            element.focus();
            return false;
        }
    }
    else
    {
        alert("O email deve ser preenchido no formato correto (usuário@dominio).");
        element.focus();
        return false;
    }
}

//Verifica se exite rodio buton
function getExistsRadioButton(radioSet)
{
    //alert(radioSet);
    if (radioSet) {
        return 1;
    } else {
        return -1;
    }
}

//Verifica se o rodio buton esta ticado
function getCheckedRadioButton(radioSet)
{
    //alert(radioSet.length);
    if (radioSet.length) {
        for (var i = 0; i < radioSet.length; i++)
            if (radioSet[i].checked)
                return i;
        return -1;
    } else {
        //alert(radioSet.checked)
        if (radioSet.checked) {
            return 1;
        } else {
            return -1;
        }
    }
}

//Verifica valor do buton ticado
function getValueCheckedRadioButton(radioSet)
{
    //alert(radioSet.length);
    if (radioSet.length) {
        for (var i = 0; i < radioSet.length; i++)
            if (radioSet[i].checked)
                return radioSet[i].value;
        return -1;
    } else {
        //alert(radioSet.checked)
        if (radioSet.checked) {
            return radioSet[0].value;
        } else {
            return -1;
        }
    }
}

// Função de validação de emails
function isEmail(emailStr) {
    /* The following pattern is used to check if the entered e-mail address
     fits the user@domain format.  It also is used to separate the username
     from the domain. */
    var emailPat = /^(.+)@(.+)$/
    /* The following string represents the pattern for matching all special
     characters.  We don't want to allow special characters in the address.
     These characters include ( ) < > @ , ; : \ " . [ ]    */
    var specialChars = "\\(\\)<>@,;:\\\\\\\"\\.\\[\\]"
    /* The following string represents the range of characters allowed in a
     username or domainname.  It really states which chars aren't allowed. */
    var validChars = "\[^\\s" + specialChars + "\]"
    /* The following pattern applies if the "user" is a quoted string (in
     which case, there are no rules about which characters are allowed
     and which aren't; anything goes).  E.g. "jiminy cricket"@disney.com
     is a legal e-mail address. */
    var quotedUser = "(\"[^\"]*\")"
    /* The following pattern applies for domains that are IP addresses,
     rather than symbolic names.  E.g. joe@[123.124.233.4] is a legal
     e-mail address. NOTE: The square brackets are required. */
    var ipDomainPat = /^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/
    /* The following string represents an atom (basically a series of
     non-special characters.) */
    var atom = validChars + '+'
    /* The following string represents one word in the typical username.ce
     Basically, a word is either an atom or quoted string. */
    var word = "(" + atom + "|" + quotedUser + ")"
    // The following pattern describes the structure of the user
    var userPat = new RegExp("^" + word + "(\\." + word + ")*\\.?$")
    /* The following pattern describes the structure of a normal symbolic
     domain, as opposed to ipDomainPat, shown above. */
    var domainPat = new RegExp("^" + atom + "(\\." + atom + ")*$")


    /* Finally, let's start trying to figure out if the supplied address is
     valid. */

    /* Begin with the coarse pattern to simply break up user@domain into
     different pieces that are easy to analyze. */
    var matchArray = emailStr.match(emailPat)
    if (matchArray == null) {
        /* Too many/few @'s or something; basically, this address doesn't
         even fit the general mould of a valid e-mail address. */
        //  alert("Email address seems incorrect (check @ and .'s)")
        return false
    }
    var user = matchArray[1]
    var domain = matchArray[2]

    // See if "user" is valid
    if (user.match(userPat) == null) {
        // user is not valid
//      alert("The username doesn't seem to be valid.")
        return false
    }

    /* if the e-mail address is at an IP address (as opposed to a symbolic
     host name) make sure the IP address is valid. */
    var IPArray = domain.match(ipDomainPat)
    if (IPArray != null) {
        // this is an IP address
        for (var i = 1; i <= 4; i++) {
            if (IPArray[i] > 255) {
//            alert("Destination IP address is invalid!")
                return false
            }
        }
        return true
    }

    // Domain is symbolic name
    var domainArray = domain.match(domainPat)
    if (domainArray == null) {
//    alert("The domain name doesn't seem to be valid.")
        return false
    }

    /* domain name seems valid, but now make sure that it ends in a
     three-letter word (like com, edu, gov) or a two-letter word,
     representing country (uk, nl), and that there's a hostname preceding
     the domain or country. */

    /* Now we need to break up the domain to get a count of how many atoms
     it consists of. */
    var atomPat = new RegExp(atom, "g")
    var domArr = domain.match(atomPat)
    var len = domArr.length
    if (domArr[domArr.length - 1].length < 2 ||
            domArr[domArr.length - 1].length > 3) {
        // the address must end in a two letter or three letter word.
        //   alert("The address must end in a three-letter domain, or two letter country.")
        return false
    }

    // Make sure there's a host name preceding the domain.
    if (len < 2) {
        //   var errStr="This address is missing a hostname!"
        //   alert(errStr)
        return false
    }

    // If we've gotten this far, everything's valid!
    return true;
}


// Função de auto-tabulação
// Parâmetros:
// input - o elemento de formulário que está sendo utilizado
// len - o tamanho do campo antes do tab automático (normalmente o maxlength)
// e - ? Sempre passar event
//
// Forma de utilização no HTML:
// <input onKeyUp="return autoTab(this, 3, event);">
var isNN = (navigator.appName.indexOf("Netscape") != -1);

function autoTab(input, len, e) {
    var keyCode = (isNN) ? e.which : e.keyCode;
    var filter = (isNN) ? [0, 8, 9] : [0, 8, 9, 16, 17, 18, 37, 38, 39, 40, 46];

    if (input.value.length >= len && !containsElement(filter, keyCode)) {
        input.value = input.value.slice(0, len);
        input.form[(getIndex(input) + 1) % input.form.length].focus();
    }

    function containsElement(arr, ele) {
        var found = false, index = 0;
        while (!found && index < arr.length)
            if (arr[index] == ele)
                found = true;
            else
                index++;
        return found;
    }

    function getIndex(input) {
        var index = -1, i = 0, found = false;
        while (i < input.form.length && index == - 1)
            if (input.form[i] == input)
                index = i;
            else
                i++;
        return index;
    }
    return true;
}


function isCpf(CpfStr) {
    var dig_1 = 0;
    var dig_2 = 0;
    var controle_1 = 10;
    var controle_2 = 11;
    var lsucesso = 1;

    if (CpfStr.length != 11) {
        return false;
    } else {
        if ((CpfStr == '00000000000') || (CpfStr == '11111111111') ||
                (CpfStr == '22222222222') || (CpfStr == '33333333333') ||
                (CpfStr == '44444444444') || (CpfStr == '55555555555') ||
                (CpfStr == '66666666666') || (CpfStr == '77777777777') ||
                (CpfStr == '88888888888') || (CpfStr == '99999999999')) {
            return false;
        } else {

            //alert (CpfStr.length);

            for (i = 0; i < 9; i++) {
                dig_1 = dig_1 + parseInt(CpfStr.substring(i, i + 1) * controle_1);
                controle_1 = controle_1 - 1;
            }

            resto = dig_1 % 11;
            dig_1 = 11 - resto;

            if ((resto == 0) || (resto == 1))
                dig_1 = 0;

            for (i = 0; i < 9; i++) {
                dig_2 = dig_2 + parseInt(CpfStr.substring(i, i + 1) * controle_2);
                controle_2 = controle_2 - 1;
            }

            dig_2 = dig_2 + 2 * dig_1;
            resto = dig_2 % 11;
            dig_2 = 11 - resto;

            if ((resto == 0) || (resto == 1))
                dig_2 = 0;

            dig_ver = (dig_1 * 10) + dig_2;

            if (dig_ver != parseFloat(CpfStr.substring(CpfStr.length - 2, CpfStr.length))) {
                //alert("CPF inválido!");
                //document.form.cpf.focus();
                return false;
            } else {
                return true;
            }
        }
    }
}

function IEHoverPseudo() {

    var navItems = document.getElementById("nivel-0").getElementsByTagName("li");

    for (var i = 0; i < navItems.length; i++) {
        if (navItems[i].className == "submenu") {
            navItems[i].onmouseover = function() {
                this.className += " over";
            }
            navItems[i].onmouseout = function() {
                this.className = "submenu";
            }
        }
    }

}
function reverterTratarJson(corpo) {
    return corpo.replace(/\\r/g, '\r')
                .replace(/\\n/g, '\n')
                .replace(/\\t/g, '\t')
                .replace(/\\'/g, '\'')
                .replace(/\\"/g, '"')
                .replace(/\\\\/g, '\\');
}



