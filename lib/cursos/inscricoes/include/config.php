<?php
// if ($_SERVER['REMOTE_ADDR'] != '177.82.0.2888'){
//     echo "Sistema em manutenção, previsão de retorno 21h 15/01/2025...<br>";
//     exit();
// }
if (!session_id()) {
    session_start();
}
// $_SESSION['_administrativo'] = null;
// $_SESSION['_logado'] = null;

if(!empty($_GET['figura'])){
    // header('Content-Type: image/png');
} else {
    header('Content-Type: text/html; charset=UTF-8');
}
// header('Content-Type: text/html; charset=UTF-8');
//ini_set("memory_limit", "128M");
//if (!ini_set('default_charset', 'utf-8')) {
//    echo "could not set default_charset to utf-8<br>";
//}
date_default_timezone_set('America/Sao_Paulo');
if ($_SERVER['REMOTE_ADDR'] == '177.82.0.288'
    // or @$_SERVER['REMOTE_ADDR']=='201.66.95.230'
    //        or @$_SERVER['REMOTE_ADDR']=='200.203.142.25'
    //        or @$_SERVER['REMOTE_ADDR']=='189.90.66.7'
            or $_SERVER['REMOTE_ADDR']=='177.82.0.28'
        or $_SERVER['HTTP_HOST'] == 'eventos.test'
) {
    // error_reporting(E_ALL & ~E_DEPRECATED);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
    ini_set("display_errors", "On");
    $debug = false;
    $debugTeste = false;
} else {
    //if($_SESSION['_login']=='zakacc@gmail.com3'){
    //    error_reporting(E_ALL ^ E_NOTICE);
    //    ini_set("display_errors", "On");
    //}else{
        error_reporting(~E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
    ini_set("display_errors", "Off");
    $debug = false;
}
// function getCountryByIP($ip) {
//     $ch = curl_init("https://ipapi.co/{$ip}/country/");
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_TIMEOUT, 2); // evita travar
//     $country = curl_exec($ch);
//     curl_close($ch);
//     if (!$country || strlen($country) !== 2) {
//         return null;
//     }
//     return trim($country);
// }
// $ip = $_SERVER['REMOTE_ADDR'];
// $country = getCountryByIP($ip);
// // Para teste local, você pode forçar um IP aqui:
// // $ip = '8.8.8.8'; // exemplo IP dos EUA
// if ($country !== 'BR') {
//     http_response_code(403);
//     exit('Acesso permitido apenas do Brasil.');
// }

$naoMostrar = false;
$FWS_Debug  = false;
// Definindo o diretório de cache do ADODB
// $ADODB_CACHE_DIR = 'c:/tmp';

$MSG = false;
foreach ($_GET as $keyG => $valG) {
    $valG = (!is_array($valG) ? addslashes($valG) : "");
    $keyG = (!is_array($keyG) ? addslashes($keyG) : "");
    $fwsGet_[$keyG] = $valG;
}
if (!isset($_GET['content'])) {
    $_GET['content'] = null; // Ou atribua um valor padrão desejado
}
if (!isset($_GET['figura'])) {
    $_GET['figura'] = null; // Ou atribua um valor padrão desejado
}
if (!isset($COD_EVENTO)) {
    $COD_EVENTO = null; // Ou atribua um valor padrão desejado
}
if (!isset($_SESSION['_administrativo'])){
    $_SESSION['_administrativo'] = null;
}
if (!isset($_SESSION['_administrativo'])){
    $_SESSION['_administrativo'] = null;
}
if (!isset($fws_['msg'])){
    $fws_['msg'] = null;
}
if (!isset($_GET['e_mail'])) {
    $_GET['e_mail'] = null; // Ou atribua um valor padrão desejado
}
if (!isset($fws_['4cpf'])) {
    $fws_['4cpf'] = null; // Ou atribua um valor padrão desejado
}
if (!isset($FWS_Pagamento)) {
    $FWS_Pagamento = null; // Ou atribua um valor padrão desejado
}
if (!isset($debug)) {
    $debug = null; // Ou atribua um valor padrão desejado
}
if (!isset($FWS_Debug)) {
    $FWS_Debug = null; // Ou atribua um valor padrão desejado
}
if (!isset($fws_['c'])) {
    $fws_['m'] = null; // Ou atribua um valor padrão desejado
}
if (!isset($fws_['m'])) {
    $fws_['m'] = null; // Ou atribua um valor padrão desejado
}
if (!isset($_SESSION['content'])) {
    $_SESSION['content'] = null; // Ou atribua um valor padrão desejado
}
if (!isset($_SESSION['_logadoComo'])) {
    $_SESSION['_logadoComo'] = null; // Ou atribua um valor padrão desejado
}
//}
foreach ($_POST as $key => $val) {
    //    if (mb_detect_encoding($val . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    //        $val = utf8_decode(addslashes($val));
    //    } else {
    $val = (!is_array($val) ? addslashes($val) : "");
    //    }
    /**/
    if (strpos($key, '_')) {
        $key = strtolower($key);
    }
    $fws_[$key] = $val;
    if ($fws_[$key] == 'undefined') {
        $fws_[$key] = "";
    }
    if ($debug) {
        echo "post -> fws_['" . $key . "'] => " . $val . " - " . $fws_[$key] . "<br>";
    }
    //Capturando array
    if (is_array($_POST[$key])) {
        $fws_[$key] = $_POST[$key];
        for ($ii = 0; $ii < count($_POST[$key]); $ii++) {
            $valArray = $_POST[$key][$ii];
            if (mb_detect_encoding($valArray . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
                $valArray = utf8_decode(addslashes($valArray));
            } else {
                $valArray = addslashes($valArray);
            }
            $fws_[$key][$ii] = $valArray;
            if ($debug) {
                echo "Array post -> fws_['" . $key . "']['" . $ii . "'] => " . $valArray . " - " . $fws_[$key][$ii] . "<br>";
            }
        }
    }
}
if (@$fws_['msg'] == "") {
    if (isset($_SESSION['MSG'])) {
    if ($_SESSION['MSG'] != '') {
        $fws_['msg'] = $_SESSION['MSG'];
    //    if (!mb_detect_encoding($fws_['msg'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    //        $fws_['msg'] = utf8_decode(addslashes($fws_['msg']));
    //    } else {
            $fws_['msg'] = addslashes($fws_['msg']);
    //    }
    }
    }
}
// if ($fwsGet_['rota'] == 10) {
//     $COD_EVENTO =  '10';
//     define('WB_PROJETO', '10');
// }


require_once(dirname(__FILE__) . "/functions/functions.php");

/*
 * Definindo diretório para busca de classes
 * Evitando a necessidade de inserir includes ou requires
 * Afim de evitar sobrecarregar o servidor com arquivos desnecessários
 */
define('WB_ROOT', $_SERVER['DOCUMENT_ROOT'] . "/");
define('WB_PATH_LOG_CACHE', WB_ROOT . '../logn/cache/');
$path = WB_ROOT . 'lib/cursos/inscricoes' . '/controller';
$path .= PATH_SEPARATOR . WB_ROOT . 'lib/cursos/inscricoes' . '/components';
$path .= PATH_SEPARATOR . WB_ROOT . 'lib/cursos/inscricoes' . '/domain';
$path .= PATH_SEPARATOR . WB_ROOT . 'lib/cursos/inscricoes' . '/domain/util';
$path .= PATH_SEPARATOR . WB_ROOT . 'lib/cursos/inscricoes' . '/domain/business';
$path .= PATH_SEPARATOR . WB_ROOT . 'lib/cursos/inscricoes' . '/domain/entity';
$path .= PATH_SEPARATOR . WB_ROOT . 'lib/cursos/inscricoes' . '/domain/entity/dao';
$path .= PATH_SEPARATOR . WB_ROOT . 'lib/cursos/inscricoes' . '/domain/entity/vo';
$path .= PATH_SEPARATOR . WB_ROOT . 'lib/cursos/inscricoes' . '/lib/vendor';
//$path .= PATH_SEPARATOR . WB_ROOT . 'lib/cursos/inscricoes' . '/lib/PDFMerger';

set_include_path(get_include_path() . PATH_SEPARATOR . $path);

//echo get_include_path();
//if (!function_exists('__autoload')) {
//    function __autoload($class_name) {
spl_autoload_register(function ($class_name) {
    $fileClass = $class_name . ".php";
    //        echo $fileClass.'<br>';
    //    if(file_exists($fileClass)) {
    require_once $fileClass;
    //    }else{
    //        echo "Arquivo da classe não encontrado:<br>".$fileClass.".<br>";
    //    }
});
//}
//

require_once WB_ROOT . 'lib/cursos/inscricoes' . '/lib/vendor/autoload.php';

//spl_autoload_register(function ($class_name) {
//    $base_dirs = [
//        WB_ROOT . 'lib/cursos/inscricoes' . '/controller',
//        WB_ROOT . 'lib/cursos/inscricoes' . '/domain',
//        WB_ROOT . 'lib/cursos/inscricoes' . '/domain/util',
//        WB_ROOT . 'lib/cursos/inscricoes' . '/domain/business',
//        WB_ROOT . 'lib/cursos/inscricoes' . '/domain/entity',
//        WB_ROOT . 'lib/cursos/inscricoes' . '/domain/entity/dao',
//        WB_ROOT . 'lib/cursos/inscricoes' . '/domain/entity/vo',
//        WB_ROOT . 'lib/cursos/inscricoes' . '/lib/vendor/myokyawhtun/pdfmerger',
//    ];
//
//    foreach ($base_dirs as $base_dir) {
//        $file = $base_dir . '/' . str_replace('\\', '/', $class_name) . '.php';
//        echo $file.'<br>';
//        if (file_exists($file)) {
//            require_once $file;
//            return;
//        }
//    }
//});

if (isset($fws_['cpf'])) {
    $fws_['cpf'] = formatCpfInsert($fws_['cpf']);
}

// Verifica se a URL é de validação de certificado
if (strpos($_SERVER['REQUEST_URI'], '/opctec/atividades/certificados/validar?id=') !== false) {
    // Exemplo: participou_atividade-30370-1716
    executarLog($_SERVER['REQUEST_URI'],"validarCertificado",true);
    // $idParts = explode('-', $fwsGet_['id']);
    $idParts = explode('-', $_GET['id']);
    $codInscrito = $idParts[1] ?? null;

    if ($codInscrito) {
        $objEv = new Evento();
        $objEv->getEventoByCodInscrito($codInscrito);
        executarLog('verificando codInscrito:'.$codInscrito,"validarCertificado",true);
        if ($objEv && $objEv->getUrlPrefixo() != '') {
            // Sobrescreve o prefixo padrão
            $fwsGet_['prefixo'] = $objEv->getUrlPrefixo();
            executarLog($fwsGet_['prefixo'],"validarCertificado",true);
        }
    }else{
        executarLog('codInscrito não encontrado na URL:'.$fwsGet_['id'],"validarCertificado",true);
    }
}

$organizacao = new Organizacao();
$organizacao->setPrefixo($fwsGet_['prefixo']);
$organizacao->carregarOrganizacaoByPrefixo($debug);
if ($organizacao->getCodOrganizacao() != '') {
    echo $debug ? __line__ . '-' : '';
    define('WB_GRUPO', $organizacao->getPrefixo());
} else {
    echo $debug ? __line__ . '-' : '';
    define('WB_GRUPO', 'opctec');
}

switch ($fwsGet_['rota']) {
    case '1':
        $COD_EVENTO =  '1';
        $WB_PROJETO = '9congresso';
        break;
    case '4congresso':
        $COD_EVENTO =  '2';
        $WB_PROJETO = '4congresso';
        break;
    case '5congresso':
        $COD_EVENTO =  '4';
        $WB_PROJETO = '5congresso';
        break;
    case 'cresspr/plenaria':
        $COD_EVENTO =  '5';
        $WB_PROJETO = 'plenaria';
        break;
    case '7mostraparanaensesaude':
        $COD_EVENTO =  '6';
        $WB_PROJETO = '7mostraparanaensesaude';
        break;
    case '7':
    case '6congresso':
        $COD_EVENTO =  '7';
        $WB_PROJETO = '6congresso';
        break;
    case 'pacbase':
        $COD_EVENTO =  '8';
        $WB_PROJETO = 'pacpase';
        break;
    case 'ingressos':
        $COD_EVENTO =  '9';
        $WB_PROJETO = 'ingressos';
        break;
    case '15':
    case 'ccip':
        $COD_EVENTO =  '15';
        $WB_PROJETO = 'ccip';
        break;
    case '14':
    case 'hu-depe':
        $COD_EVENTO =  '14';
        $WB_PROJETO = 'hu-depe';
        break;
    case '13':
    case 'hu':
        $COD_EVENTO =  '13';
        $WB_PROJETO = 'hu';
        break;
    case '12':
    case 'acls':
        $COD_EVENTO =  '12';
        $WB_PROJETO = 'acls';
        break;
    case 'comuel':
    case '11':
        $COD_EVENTO =  '11';
        $WB_PROJETO = '11';
        break;
    case '20':
        $COD_EVENTO =  '20';
        $WB_PROJETO = '20';
        break;
    // case '10':
    //     //    case 'cursos':
    //     $COD_EVENTO =  '10';
    //     $WB_PROJETO = '10';
    //     break;
        //    case '10':
        //    case 'cursos':
        //        define('COD_EVENTO',  '10');
        //        define('WB_PROJETO', 'cursos');
        //        break;    
    case 'admin':
    case 'atividades':
    case 'meus-cursos':
    case 'cursos':
        if ($_SESSION['_administrativo'] == 1 and ($fwsGet_['cod_evento'] != '' or $fws_['cod_evento'] != '')) {
            if ($fwsGet_['cod_evento'] != '') {
                $COD_EVENTO =  $fwsGet_['cod_evento'];
            } else {
                $COD_EVENTO =  $fws_['cod_evento'];
            }
            $WB_PROJETO = $COD_EVENTO;
        } else {
            echo $debug ? __line__ . '-' : '';
            $COD_EVENTO =  '0';
            if ($fwsGet_['rota'] == 'atividades') {
                $WB_PROJETO = 'atividades';
            } else {
                $WB_PROJETO = 'admin';
            }
        }
        break;
    default:
        //        if($fwsGet_['rota']>=16){
        echo $debug ? __LINE__ . '-' . $fwsGet_['prefixo'] . '-' . $fwsGet_['rota'] : '';
        $WB_PROJETO = $fwsGet_['rota'];
        //        }else{
        //            $arqConfig = false;
        //        }
        break;
}

if (file_exists(dirname(__FILE__) . '/config-' . $fwsGet_['rota'] . '.php')) {
    echo $debug ? __LINE__ . '-' . $fwsGet_['rota'] . '-<br>' : '';
    $arqConfig = true;
} else {
    echo $debug ? __LINE__ . '-' . $fwsGet_['rota'] . '-<br>' : '';
    $arqConfig = false;
}

//echo $debug? __LINE__.'-codEvento:'.COD_EVENTO.'-' : '';
$objEv = new Evento();
if ($COD_EVENTO != '') {
    echo $debug ? __LINE__ . '-' : '';
    $objEv->setCodEvento($COD_EVENTO);
    $objEv->getEventoById($debug);
} else {
    echo $debug ? __LINE__ . '-' : '';
    if (is_numeric($fwsGet_['rota'])) {
        echo $debug ? __LINE__ . '-' : '';
        $objEv->setCodEvento($fwsGet_['rota']);
        $objEv->getEventoById($debug);
    } else {
        echo $debug ? __LINE__ . '-' : '';
        $objEv->setUrlPrefixo($fwsGet_['prefixo'] . '/' . $fwsGet_['rota']);
        $objEv->getEventoByUrlPrefixo($debug);
    }
    // echo $debug? dd($objEv) : '';
    if ($objEv->getCodEvento() != '') {
        echo $debug ? __LINE__ . '->'.$objEv->getCodEvento().'<-' : '';
        $COD_EVENTO = $objEv->getCodEvento();
        define('COD_EVENTO', $objEv->getCodEvento());
        // echo $debug ? '->' . COD_EVENTO . '<-' : '';
        // exit();
    } else {
        echo $debug ? __LINE__ . '-' : '';
        if ($WB_PROJETO != 'atividades' and $WB_PROJETO != 'cursos' and $WB_PROJETO != 'admin') {
            $URL = 'https://' . FWA_Dominio() . '/' . WB_GRUPO . '/atividades/inscricoes/index.php';
            echo 'Corrigindo url não encontrada... 1<br>';
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=" . $URL . "\"></HTML>";
            exit();
        }
    }
}

if (!defined('COD_EVENTO')) {
    define('COD_EVENTO', $COD_EVENTO);
}

if (!defined('WB_PROJETO')) {
    define('WB_PROJETO', $WB_PROJETO);
}

$cod_evento = $COD_EVENTO;

//echo $debug? __LINE__.'-' : '';
$objEvConf = new EventoConfiguracao();
if ($objEv->getCodEvento() != '' and $objEv->getCodEvento() != '0') {
    $objEvConf = $objEv->getEventoConfiguracao($debug);
}

$PROTOCOLO = "https";
//if($_SERVER["HTTPS"]){
//    $PROTOCOLO = "https";
//}


switch ($_SERVER["SERVER_NAME"]) {
    case "www.opctec.net.br":
    case "opctec.net.br":
    case "www.opctec.com.br":
    case "opctec.com.br":
        define('WB_PERSPECTIVA', 'local');
        //        define('WB_ROOT', $_SERVER['DOCUMENT_ROOT'] ."/");
        //definindo WB_PROJETO
        $dir = dirname(__FILE__);
        //para linux a barra do explode para separar os diretório é ao contrario
        $arrayDir = explode('/', $dir);
        //        define('WB_PROJETO', $arrayDir[count($arrayDir)-3]);
        define('WB_PATH', '/' . WB_GRUPO . '/' . WB_PROJETO . '/inscricoes');
        //define('GOOGLE_KEY', '6LegLLkUAAAAAPmMa9BQnN6VSOKQ4xAwADvbHZM1');
        define('GOOGLE_KEY', '6Lc2oAIbAAAAABgnuDtnwj9twSFm1IXHKzN6Xi-K');
        define('URL_BOLETO', 'https://opctec.net.br');
        define('WB_URL', 'https://opctec.net.br');

        //        if(WB_PROJETO==10){
        //            include 'config-cursos.php';
        //        }else{
        //            include 'config-'.WB_PROJETO.'.php';
        //        }
        break;
    case "eventos.test":
        $PROTOCOLO = "https";
        define('URL_BOLETO', 'https://eventos.test');
        define('WB_URL', 'https://eventos.test');
        //        define('GOOGLE_KEY', '6Lc2oAIbAAAAABgnuDtnwj9twSFm1IXHKzN6Xi-K');
        define('WB_PERSPECTIVA', 'local');
        //        define('WB_ROOT', $_SERVER['DOCUMENT_ROOT'].'/');
        define('WB_PATH', '/' . WB_GRUPO . '/' . WB_PROJETO . '/inscricoes');

        //        include 'config-cursoslocal.php';
        //        include 'config-'.WB_PROJETO.'.php';
        break;
    default:
        define('WB_PERSPECTIVA', 'prod');
        //        define('WB_ROOT', $_SERVER['DOCUMENT_ROOT']);
        //definindo WB_PROJETO
        $dir = dirname(__FILE__);
        $arrayDir = explode('\\', $dir);
        //        define('WB_PROJETO', $arrayDir[count($arrayDir)-3]);
        define('WB_PATH', '/' . WB_GRUPO . '/' . WB_PROJETO . '/inscricoes');
        define('WB_URL', 'http://www.opctec.net.br');
        define('URL_BOLETO', 'https://opctec.net.br');
        //        include 'config-'.WB_PROJETO.'.php';
        break;
}
if ($_SERVER['REMOTE_ADDR'] == '189.90.70.56' or $_SERVER['REMOTE_ADDR'] == '189.90.70.203' or $_SERVER['REMOTE_ADDR'] == '189.35.135.201' or $_SERVER['REMOTE_ADDR'] == '177.100.49.198') {
    //    $OP_EDICAO_DADOS_PESSOAIS = true;
    //    $OP_PROCESSO_INSCRICAO    = true;
    //    $OP_SUBMISSAO_TRABALHO    = true;
    //    $OP_TRAMITACAO_TRABALHO   = true;
    //    $OP_EMISSAO_BOLETO        = false;
    //    $OP_GERAR_BOLETO          = false;
    //    $OP_EMISSAO_CERTIFICADOS  = false;
    //    $OP_EMISSAO_RECIBOS       = false;
    //    $OP_EMISSAO_CARTA_ACEITE  = false;
    //    $OP_EMISSAO_COMPROVANTE_INSCRICAO  = false;
    //    $OP_EMISSAO_BOLETO_EXTRA = false;
}
define('WB_URL_AVALIAR_EVENTO', WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/avaliar_evento');
define('WB_URL_CERTIFICADO', WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/certificados');
define('WB_URL_VALIDAR_CERTIFICADO', WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/certificados/validar');
define('WB_URL_CARTA_ACEITE', WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/carta_aceite');
define('WB_URL_COMPROVANTE_INSCRICAO', WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/comprovante_inscricao');
define('WB_URL_LOGIN_ADM', WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/admin');
define('WB_URL_LOGIN_AVALIADOR', WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/admin');

define('IP', $_SERVER['REMOTE_ADDR']);
define('DOMINIO_URL', WB_URL);

define('WB_PATH_COD_EVENTO', str_pad(COD_EVENTO, '3', "0", STR_PAD_LEFT));
define('WB_PATH_LOG', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/');
define('WB_PATH_LOG_MIDIA', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/midia/');
define('WB_PATH_LOG_TRABALHO', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/trabalhos/');
define('WB_PATH_LOG_APRESENTACAO', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/apresentacao/');
define('WB_PATH_LOG_AVULSO', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/avulso/');
define('WB_PATH_LOG_VIDEO', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/video/');
define('WB_PATH_LOG_CONTRATO', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/contrato/');
define('WB_PATH_LOG_RPA', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/rpa/');
define('WB_PATH_LOG_NOTA_FISCAL', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/nota_fiscal/');
define('WB_PATH_LOG_REMESSA', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/remessa/');
define('WB_PATH_LOG_EXTBOLETO', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/extrato_boletos/');
define('WB_PATH_LOG_RETBOLETO', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/retorno/');
define('WB_PATH_LOG_BOLETO', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/boletos/');
define('WB_PATH_LOG_CERTIFICADOS', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/certificados/');
define('WB_PATH_LOG_CARGA', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/carga/');
define('WB_PATH_LOG_RECIBOS', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/recibos/');
define('WB_PATH_LOG_CARTAS_ACEITE', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/cartas_aceite/');
define('WB_PATH_LOG_COMPROVANTES_INSCRICAO', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/comprovantes_inscricao/');
define('WB_PATH_LOG_DECLARACAO_PRESENCA', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/declaracao_presenca/');
define('WB_PATH_LOG_LISTA_CHAMADA', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/lista_chamada/');
define('WB_PATH_LOG_APP', WB_ROOT . '../logn/eventos/' . WB_PATH_COD_EVENTO . '/app/');
define('WB_PATH_LIB', WB_ROOT . WB_PATH_COD_EVENTO . '/inscricoes/lib/');

define('SISTEM_PATH', dirname(__FILE__));
define('SITE', FWA_URL_Prefixo()); // local onde está a raiz do site
define('SITE_PATH', dirname(__FILE__)); // local onde está a raiz do site

#define('TEMPLATE_DIR', SITE . '/portal/templates/'.DEFAULT_TEMPLATE); // local do template usado no site, a partir de SITE
#define('TEMPLATE_PATH', SITE_PATH . '/portal/templates/'.DEFAULT_TEMPLATE); // local do template usado no site, a partir de SITE

define('WB_URL_ACTION', $PROTOCOLO . '://' . FWA_Dominio() . SITE . '/index-action.php');
define('WB_URL_CLEAN', $PROTOCOLO . '://' . FWA_Dominio() . SITE . '/index-clean.php');
define('WB_URL_MODAL', $PROTOCOLO . '://' . FWA_Dominio() . SITE . '/index-modal.php');
define('WB_URL_SITE', $PROTOCOLO . '://' . FWA_Dominio() . SITE);
define('WB_URL_LOGIN', $PROTOCOLO . '://' . FWA_Dominio() . SITE);
define('WB_URL_DOMINIO', $PROTOCOLO . '://' . FWA_Dominio());
define('WB_URL_COMMAND', WB_URL_ACTION . '?content=controller/CommandAC.php');

define('WB_URL_IMG', $PROTOCOLO . '://' . FWA_Dominio() . SITE . '/resources/image');
define('WB_URL_IMG_BOLETO', $PROTOCOLO . '://' . FWA_Dominio() . SITE . '/lib/boleto/imagens/');
@define('WB_URL', $PROTOCOLO . '://' . FWA_Dominio() . SITE);
define('WB_URL_VIEW', $PROTOCOLO . '://' . FWA_Dominio() . SITE . '/index.php');
define('WB_URL_INSCRICAO', $PROTOCOLO . '://' . FWA_Dominio() . SITE . '/index.php');
define('WB_URL_ATIVIDADES', $PROTOCOLO.'://'.FWA_Dominio() . '/atividades/index.php');

if(file_exists('resources/image/logo-60X235pxN-'.COD_EVENTO.'.png')){
    $imgLogoGestor = '/logo-60X235pxN-'.COD_EVENTO.'.png';
}else if(file_exists('resources/image/logo-60X235pxN-'.$objEv->getGestor().'.png')){ 
    $imgLogoGestor = '/logo-60X235pxN-'.$objEv->getGestor().'.png';
}else if(file_exists('resources/image/logo-60X235pxN-'. strtoupper($organizacao->getSigla()).'.png')){
    $imgLogoGestor = '/logo-60X235pxN-'.strtoupper($organizacao->getSigla()).'.png';
}else{ 
    $imgLogoGestor = '/logo-OPCTEC.png';
} 
define('WB_IMG_LOGO_GESTOR', $imgLogoGestor);

define('WB_PATH_SECURITY', "view/financeiro,view/admin");

// settings globais (env.php, etc.) — antes do switch para valer em qualquer rota
include 'config-base.php';

echo $debug ? __LINE__ . '-config-' . $objEv->getConfiguracao() . '-' . COD_EVENTO . '-<br>' : '';
if ($objEv->getConfiguracao() != '' and $arqConfig == true) {
    include 'config-' . $objEv->getConfiguracao() . '.php';
} else {
    if (COD_EVENTO == 0 or COD_EVENTO == '-1' or COD_EVENTO == '' or COD_EVENTO == 9999) {
        echo $debug ? __LINE__ . '-config-' . $objEv->getConfiguracao() . '-' . COD_EVENTO . '-' : '';
        include 'config-opctec.php';
        //        if(WB_GRUPO!='opctec'){
        //            echo '<br>corrigindo url...<br>';
        //            $URL = $PROTOCOLO.'://'.FWA_Dominio().'/opctec/eventos';
        //            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"2; URL=" . $URL . "\"></HTML>";
        //            exit();
        //        }
    } else if ($objEv->getStatus() == 'ATIVADO') {
        echo $debug ? __LINE__ . '-config-' . $objEv->getConfiguracao() . '-' . COD_EVENTO . '-' : '';
        include 'config-padrao.php';
    } else {
        if ($_SERVER['HTTP_HOST'] == 'eventos.test') {
            //            dd($objEv);
            //echo __LINE__.'-código-evento:'.COD_EVENTO.'<-Não encontrado-';
            //exit();

            header('Location: ' . $PROTOCOLO . '://' . FWA_Dominio() . '/' . WB_GRUPO . '/atividades/index.php');
        } else {
            //header('Location: https://'.$_SERVER['SERVER_NAME']);
            $URL = 'https://' . $_SERVER['SERVER_NAME'] . '?ERROR=' . __LINE__ . '&->' . COD_EVENTO . '<-';
            echo '<br>corrigindo url... ' . __LINE__ . ' ' . COD_EVENTO . '  ' . $URL . '<br>';
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"2; URL=" . $URL . "\"></HTML>";
        }
        exit;
    }
}
//if ($_SERVER['REMOTE_ADDR']=='200.203.142.25'){
//    echo WB_GRUPO.'/'.WB_PROJETO.'!='.$objEv->getUrlPrefixo().' AND ->'.$objEv->getUrlPrefixo().'<-<br>';
//    echo "((".$organizacao->getPrefixo()." != ".strtolower($objEv->getGestor()).") and (".$fwsGet_['rota']."!='atividades' and ".$fwsGet_['rota']."!='admin') and ".$fwsGet_['rota']."!='cursos')";
//    exit();
//}
echo $debug ? WB_GRUPO . '/' . WB_PROJETO . '!=' . $objEv->getUrlPrefixo() . ' AND ->' . $objEv->getUrlPrefixo() . '<-<br>' : '';
if($debug==1){
    echo "((" . $organizacao->getPrefixo() . " != " . strtolower($objEv->getGestor()) . ") and (" . $fwsGet_['rota'] . "!='atividades' and " . $fwsGet_['rota'] . "!='admin') and " . $fwsGet_['rota'] . "!='cursos' and " . $_GET['figura'] . "!=true)";
    exit();
}
if (($organizacao->getPrefixo() != strtolower($objEv->getGestor())) and ($fwsGet_['rota'] != 'atividades' and $fwsGet_['rota'] != 'admin') and $fwsGet_['rota'] != 'cursos' and $_GET['figura'] != true) {
    $URL = $PROTOCOLO . '://' . FWA_Dominio() . '/' . $organizacao->getPrefixo() . '/atividades/inscricoes';
    echo '<br>corrigindo url... 2 ' . $URL . '<br>';
    if ($debug) {
        echo $organizacao->getPrefixo() . '!=' . strtolower($objEv->getGestor());
        exit();
    }
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"2; URL=" . $URL . "\"></HTML>";
    exit();
}
if (WB_GRUPO != strtolower($objEv->getGestor()) and $objEv->getUrlPrefixo() != '' and $objEv->getStatus() == 'ATIVADO') {
    if ($debug) {
        echo WB_GRUPO . '!=' . strtolower($objEv->getGestor()) . ' and ' . $objEv->getUrlPrefixo() . '!=\'\' and ' . $objEv->getStatus() . '==ATIVADO';
        exit();
    }
    echo '<br>corrigindo url... 3<br>';
    $URL = $PROTOCOLO . '://' . FWA_Dominio() . '/' . $objEv->getUrlPrefixo() . '/inscricoes';
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"2; URL=" . $URL . "\"></HTML>";
    //    header('Location: '.$PROTOCOLO.'://'.FWA_Dominio().'/'.$objEv->getUrlPrefixo().'/inscricoes');
    exit();
} else if (WB_GRUPO != strtolower($objEv->getGestor()) and $objEv->getStatus() == 'ATIVADO') {
    echo '<br>corrigindo url... 4<br>';
    $URL = $PROTOCOLO . '://' . FWA_Dominio() . '/' . strtolower($objEv->getGestor()) . '/' . $objEv->getCodEvento() . '/inscricoes';
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"2; URL=" . $URL . "\"></HTML>";
    //    header('Location: '.$PROTOCOLO.'://'.FWA_Dominio().'/'. strtolower($objEv->getGestor()).'/'.$objEv->getCodEvento().'/inscricoes');
    exit();
}
/* para criar a pasta do evento é necessario ja ter criado no servidor a pasta logn/eventos, em uma pasta paralela a pasta public_html */
if (!file_exists(WB_PATH_LOG)) {
    mkdir(WB_PATH_LOG);
}

if ($_SERVER['REMOTE_ADDR'] == '189.90.70.56' or $_SERVER['REMOTE_ADDR'] == '177.100.49.198') {
    $ipLiberado = true;
    define('IP_LIBERADO', true);
}
if (!isset($_POST['content'])) {
    $_POST['content'] = null; // Ou atribua um valor padrão desejado
}
if ($_GET['content'] == "") {
    if ($_POST['content'] == "") {
        $content = "default.php";
    }else{
        $content = $_POST['content'];
    }
}else{
    $content = $_GET['content'];
}

// Proteção de roteamento: whitelist por diretório + validações de segurança
if ($content !== "default.php") {
    // Rotas permitidas para inclusão via content
    $diretoriosPermitidos = array('view/', 'controller/CommandAC.php');

    // Verifica se começa com um diretório permitido
    $permitido = false;
    foreach ($diretoriosPermitidos as $dir) {
        if (strpos($content, $dir) === 0) {
            $permitido = true;
            break;
        }
    }

    if (!$permitido
        || strpos($content, '..') !== false
        || preg_match('/^(\/|[a-zA-Z]:)/', $content)
        || strpos($content, "\0") !== false
        || strtolower(pathinfo($content, PATHINFO_EXTENSION)) !== 'php'
    ) {
        executarLog(
            "ROTA BLOQUEADA: " . $content . " | IP: " . $_SERVER['REMOTE_ADDR'] . " | Referer: " . @$_SERVER['HTTP_REFERER'],
            "roteamento-bloqueado",
            true
        );
        $content = "default.php";
    }
}
//if(!($cod_evento!='' and  $cod_evento!=0)){
//    $content = "listar_eventos.php";
//}
