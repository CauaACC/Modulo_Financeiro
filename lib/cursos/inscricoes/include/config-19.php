<?php

define('TITLE_APP', 'HUTEC-CCIP');
define('DESC_EVENTO', 'HUTEC-CCIP');
define('DESC_EVENTO_RODAPE', 'HUTEC-CCIP');
define('NOME_EVENTO', 'HUTEC-CCIP');
define('ABREV_EVENTO','HUTEC-CCIP');
define('ABREV_EVENTO_EMAIL','HUTEC-CCIP');
define('ANO_EVENTO',  '2023');
define('MAX_OFICINAS', '');
define('FONE_CONTATO','');
define('FONE_CONTATO_WHATS','(43) 99696-7790');
define('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone=5543996967790');
//define('LINK_CONTATO_WHATS','');
define('EMAIL_CONTATO','contato.opctec@gmail.com');
define('EMAIL_REPLAY_TO','');
define('EMAIL_BCC','');
define('EMAIL_CONTATO_ATT', 'Comissão Organizadora');
define('EMAIL_CONTATO_LINK', 'https://www.opctec.net.br/hutec/ccip/');
define('EMAIL_CONTATO_LINK_AVALIACAO', 'https://www.opctec.net.br/hutec/ccip/avaliacao');
define('EMAIL_CONTATO_LINK_ADMIN', 'https://www.opctec.net.br/hutec/ccip/admin');
define('EMAIL_CONTATO_LINK_LOGIN', 'https://www.opctec.net.br/hutec/ccip');
define('LINK_CONTATO', 'https://www.opctec.net.br/hutec/ccip/#contact');
define('PRESIDENTE',   '');
define('PRESIDENTE_TITULO', 'Coordenação da HUTEC-CCIP');
define('PERIODO', ''); #<BR>horário: 19h às 21h30.
define('CIDADE_UF', '');

define('URL_HOME_MENU', 'https://www.hutec.org.br');
define('URL_BOLETO', '');
define('URL_FACE', '');
define('URL_YOUTUBE', '');
define('URL_INSTAGRAM', '');
define('URL_TWITTER', '');
define('URL_LINKEDIN', '');
define('URL_OFICIAL', 'https://www.opctec.net.br');
define('LOGO_RODAPE', 'logo-rodape-'.COD_EVENTO.'.png');
//define('LOGO_RODAPE', '');

$fws['headers']     = "Bcc: zakacc@gmail.com\r\n".
$fws['emailEvento'] = "zakacc@gmail.com";


$headers = "MIME-Version: 1.1\r\n";
$headers .= "Content-type: text/plain; charset=UTF-8\r\n";
$headers .= "From: ".ABREV_EVENTO_EMAIL." <eventos@opctec.com.br>\r\n"; // remetente
$headers .= "Bcc: eventos@opctec.com.br\r\n"; // remetente
//$headers .= "Bcc: zakacc@gmail.com\r\n"; // remetente
if(EMAIL_REPLAY_TO!=''){
    $headers .= "Replay-To: ".EMAIL_REPLAY_TO."\r\n"; // remetente
    $headers .= "Return-Path: ".EMAIL_REPLAY_TO."\r\n"; // return-path
}

define('HEADER_EMAIL', $headers);

define('FWA_REFRESH_TIME' , '2');

$FWS_Data_Hoje = Date('Ymd');
$FWS_Data_HojeHM = Date('YmdHi');

$FWS_Data_Inscricao_Inicio = '20230120';
$FWS_Data_Ini_Submissao_Trabalho  = '20230120';

if($_SERVER['REMOTE_ADDR']=='189.90.70.566'){
    $FWS_Data_Liberar_Boleto = '20230213';
}else{
$FWS_Data_Liberar_Boleto = '20230214';
}

$FWS_Data_Liberar_Certificado = '202305180000';
$FWS_Data_Inscricao_Fim    = '20230421';
$FWS_Data_Edicao_Dados_Pessoais = $FWS_Data_Inscricao_Fim+7;
$FWS_Data_Trabalho_Tramitacao_Fim= '20230226';
$FWS_Data_Max_Submissao_Trabalho = '20230226';
$FWS_Data_Divulgacao_Trabalho_Aprovado  = '';
$FWS_Data_Divulgacao_Trabalho_Reprovado = '';
define('TRABALHO_ANEXA_RESUMO', FALSE);
define('TRABALHO_ANEXA_RESUMO_FORMULARIO', FALSE);
define('DESC_ATIVIDADE', 'Workshop');

$FWS_Numero_Maximo_Trabalhos = 0;
$FWS_Numero_Maximo_Trabalhos_Atrelado_ao_Perfil = false;

$FWS_Data_Max_Submissao_Trabalho_Mensagem = "Envio de trabalho encerrado.";
$msg_tela_login_envio_trabalho = '';
$FWS_Liberar_Ja_Estou_Inscrito = 1;
$msg_tela_previsao_inicio      = "Inicio das inscrições previsto para ".formatDateView($FWS_Data_Inscricao_Inicio).".";
//$msg_tela_previsao_inicio      = 1;
$msg_tela_login = "Bem Vindo ao HUTEC/HU";

$FWS_Dias_Prazo_Pagamento = 5;
$OP_PROCESSO_INSCRICAO_SUSPENSO = false;

/**Evento Online */
$OP_EVENTO_ONLINE_LIBERADO = false;
$OP_EVENTO_ONLINE_YOUTUBE_LIBERADO = false;
if($_SERVER['REMOTE_ADDR']=='179.154.183.222' or ('202207090730'<=Date('YmdHi') and Date('YmdHi')<='202207112359')){
    $OP_EVENTO_ONLINE_YOUTUBE_LIBERADO = true;
}
$OP_EVENTO_ONLINE_MOSTRA_LIBERADO = false;
if($_SERVER['REMOTE_ADDR']=='179.154.183.222' or ('202207090730'<=Date('YmdHi') and Date('YmdHi')<='202207112359')){
    $OP_EVENTO_ONLINE_MOSTRA_LIBERADO = true;
}
$OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO = false;
if ($_SERVER['REMOTE_ADDR']=='179.154.183.222' or ('202207090730'<=Date('YmdHi') and Date('YmdHi')<='202207112359')){
    $OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO = true;
}
if ($_SERVER['SERVER_NAME']=='eventos.test'){
    $OP_EVENTO_ONLINE_YOUTUBE_LIBERADO = true;
    $OP_EVENTO_ONLINE_MOSTRA_LIBERADO = true;
    $OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO = true;
}
$OP_EVENTO_AVISOS = false;
//if($_SERVER['REMOTE_ADDR']!='179.154.183.222'){
//    $OP_EVENTO_AVISOS = true;
//}

$OP_PROCESSO_INSCRICAO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Inscricao_Fim){
    $OP_PROCESSO_INSCRICAO = true;
}

$OP_SUBMISSAO_TRABALHO = false;
if($FWS_Data_Ini_Submissao_Trabalho <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Max_Submissao_Trabalho ){
    $OP_SUBMISSAO_TRABALHO = FALSE;
}
$OP_SUBMISSAO_TRABALHO_AGUARDANDO = false;
if($FWS_Data_Hoje < $FWS_Data_Ini_Submissao_Trabalho ){
    $OP_SUBMISSAO_TRABALHO_AGUARDANDO = true;
}
$OP_SUBMISSAO_TRABALHO_ENCERRADA = false;
if($FWS_Data_Max_Submissao_Trabalho <= $FWS_Data_Hoje){
    $OP_SUBMISSAO_TRABALHO_ENCERRADA = true;
}
//if ($_SESSION['cod_inscrito'] == '5016') {
//    $OP_SUBMISSAO_TRABALHO = true;
//}
$OP_TRAMITACAO_TRABALHO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Trabalho_Tramitacao_Fim ){
    $OP_TRAMITACAO_TRABALHO = true;
}

$SIS_ADM_AVALIACAO_TRABALHOS = false;
$SIS_AVALIACAO_TRABALHOS_PREMIADOS = false;
$SIS_ADM_AVALIACAO_TRABALHOS_DEFINIR_PREMIADOS = false;

$OP_DIVULGAR_TRABALHO_REPROVADO = false;
if($FWS_Data_Divulgacao_Trabalho_Reprovado <= $FWS_Data_Hoje){
    $OP_DIVULGAR_TRABALHO_REPROVADO = true;
}
$OP_DIVULGAR_TRABALHO_APROVADO = false;
if($FWS_Data_Divulgacao_Trabalho_Aprovado <= $FWS_Data_Hoje){
    $OP_DIVULGAR_TRABALHO_APROVADO = true;
}

$OP_ANEXAR_VIDEO = false;
if($_SERVER['REMOTE_ADDR']=='189.90.70.56' or $_SERVER['REMOTE_ADDR']=='200.250.28.242'){
    $OP_ANEXAR_APRESENTACAO_VIDEO_ENCERRADO = false;
    $OP_PERMITIR_ANEXAR_VIDEO = true;
}else{
    $OP_ANEXAR_APRESENTACAO_VIDEO_ENCERRADO = true;
    $OP_PERMITIR_ANEXAR_VIDEO = false;
}
$OP_ANEXAR_APRESENTACAO = false;
if($_SERVER['REMOTE_ADDR']=='189.90.70.56' or $_SERVER['REMOTE_ADDR']=='200.250.28.242'){
    $OP_ANEXAR_APRESENTACAO_ENCERRADO = false;
    $OP_PERMITIR_ANEXAR_APRESENTACAO = FALSE;
}else{
    $OP_ANEXAR_APRESENTACAO_ENCERRADO = true;
    $OP_PERMITIR_ANEXAR_APRESENTACAO = false;
}
$OP_DIVULGAR_ENSALAMENTO = false;

$OP_GERAR_BOLETO = false;
if($FWS_Data_Liberar_Boleto <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Inscricao_Fim){
    $OP_GERAR_BOLETO = true;
}

$OP_EDICAO_DADOS_PESSOAIS = false;
if($FWS_Data_Hoje <= $FWS_Data_Edicao_Dados_Pessoais){
    $OP_EDICAO_DADOS_PESSOAIS = true;
}

$OP_EMISSAO_CERTIFICADOS = false;
if($FWS_Data_Liberar_Certificado <= $FWS_Data_HojeHM){
    $OP_EMISSAO_CERTIFICADOS = true;
}

$OP_EMISSAO_RECIBOS = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje){
    $OP_EMISSAO_RECIBOS = true;
}
$OP_EMISSAO_RECIBOS = false;

$OP_EMISSAO_CARTA_ACEITE = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje){
    $OP_EMISSAO_CARTA_ACEITE = true;
}

$OP_EMISSAO_COMPROVANTE_INSCRICAO = false;
//if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje){
//    $OP_EMISSAO_COMPROVANTE_INSCRICAO = true;
//}

$OP_INCRICAO_GRATUITA = false;
//if($_SERVER['REMOTE_ADDR']=='10.10.105.3'){
    $OP_EMISSAO_BOLETO = true;
    $OP_EMISSAO_BOLETO_EM_MANUTENCAO = false;
//}else{
//    $OP_EMISSAO_BOLETO = false;
//    $OP_EMISSAO_BOLETO_EM_MANUTENCAO = true;
//}
$OP_EMISSAO_BOLETO_EXTRA = true;
$OP_MENSAGEM_BTN_BOLETO  = false;
$OP_SELECIONAR_ALMOCO     = false;
/**/
$fws_['op_selecionar_oficina']= true;
$fws_['op_selecionar_pedidos']= FALSE;
$fws_['op_selecionar_evento_paralelo']= false;
$fws_['op_avaliar_ev_certificado']    = false;
$fws_['op_selecionar_ingresso'] = false;
$fws_['op_selecionar_oficina_apos_inscricao'] = true;
$fws_['op_excluir_oficina_apos_inscricao']    = true;
$fws_['op_dados_pessoais_profissao']    = false;
$fws_['op_dados_pessoais_complemento']  = false;
$fws_['op_dados_pessoais_complemento_cursos'] = false;
$OP_NOME_CRACHA = false;
$fws_['op_dados_pessoais_tipo_instituicao'] = false;
$fws_['op_dados_pessoais_data_nascimento'] = false;
$fws_['op_dados_pessoais_recibo'] = true;
$fws_['op_dados_pessoais_instituicao'] = false;
$fws_['op_dados_pessoais_n_especiais'] = true;

/*Valores*/
$OP_VALOR_OFICINA   = 0;
$OP_VALOR_ALMOCO    = 0;
$OP_VALOR_INSCRICAO = 0;
$OP_EMISSAO_CARTA_ACEITE  = true;
$OP_EMISSAO_DECLARACAO_PRESENCA  = false;
$OP_AVALIACAO_ENVIAR_AO_SALVAR = true;

$OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO = false;
$OP_PERMITIR_ALTERAR_OFICINA = false;
$OP_PERMITIR_ALTERAR_EVENTO_PARALELO   = false;
$OP_PERMITIR_ADICIONAR_EVENTO_PARALELO = false;

$OP_PERMITIR_MAX_EVENTO_PARALELO       = 2;
$OP_BLOQUEAR_ALTERACAO_SE_BOLETO_GERADO = false;

$fws['op_selecionar_evento_paralelo_botao_personalizado'] = false;



if($_SERVER['REMOTE_ADDR']=='189.90.70.56' or $_SERVER['REMOTE_ADDR']=='189.90.70.203' or $_SERVER['REMOTE_ADDR']=='189.35.135.201' or $_SERVER['REMOTE_ADDR']=='177.100.49.198'){
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
define('WB_URL_AVALIAR_EVENTO',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/avaliar_evento');
define('WB_URL_CERTIFICADO',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/certificados');
define('WB_URL_VALIDAR_CERTIFICADO',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/certificados/validar');
define('WB_URL_CARTA_ACEITE',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/carta_aceite');
define('WB_URL_COMPROVANTE_INSCRICAO',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/comprovante_inscricao');

define('IP',$_SERVER['REMOTE_ADDR']);
define('DOMINIO_URL',WB_URL);


define('WB_PATH_COD_EVENTO',str_pad(COD_EVENTO, '3', "0", STR_PAD_LEFT));
define('WB_PATH_LOG', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO . '/');
define('WB_PATH_LOG_CACHE', WB_ROOT . '../log/cache/');
define('WB_PATH_LOG_TRABALHO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/trabalhos/');
define('WB_PATH_LOG_APRESENTACAO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/apresentacao/');
define('WB_PATH_LOG_VIDEO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/video/');
define('WB_PATH_LOG_REMESSA', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/remessa/');
define('WB_PATH_LOG_EXTBOLETO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/extrato_boletos/');
define('WB_PATH_LOG_RETBOLETO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/retorno/');
define('WB_PATH_LOG_BOLETO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/boletos/');
define('WB_PATH_LOG_CERTIFICADOS', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/certificados/');
define('WB_PATH_LOG_RECIBOS', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/recibos/');
define('WB_PATH_LOG_CARTAS_ACEITE', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/cartas_aceite/');
define('WB_PATH_LOG_COMPROVANTES_INSCRICAO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/comprovantes_inscricao/');
define('WB_PATH_LOG_DECLARACAO_PRESENCA', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/declaracao_presenca/');
define('WB_PATH_LOG_LISTA_CHAMADA', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/lista_chamada/');
define('WB_PATH_LOG_APP', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/app/');
define('WB_PATH_LIB', WB_ROOT . WB_PATH_COD_EVENTO.'/inscricoes/lib/');

define('SISTEM_PATH', dirname(__FILE__));
define('SITE', FWA_URL_Prefixo()); // local onde está a raiz do site
define('SITE_PATH', dirname(__FILE__)); // local onde está a raiz do site

#define('TEMPLATE_DIR', SITE . '/portal/templates/'.DEFAULT_TEMPLATE); // local do template usado no site, a partir de SITE
#define('TEMPLATE_PATH', SITE_PATH . '/portal/templates/'.DEFAULT_TEMPLATE); // local do template usado no site, a partir de SITE

define('WB_URL_ACTION', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/index-action.php');
define('WB_URL_CLEAN', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/index-clean.php');
define('WB_URL_MODAL', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/index-modal.php');
define('WB_URL_SITE', $PROTOCOLO.'://'.FWA_Dominio() . SITE);
define('WB_URL_COMMAND', WB_URL_ACTION.'?content=controller/CommandAC.php');

define('WB_URL_IMG', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/resources/image');
define('WB_URL_IMG_BOLETO', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/lib/boleto/imagens/');
//define('WB_URL', $PROTOCOLO.'://'.FWA_Dominio() . SITE );
define('WB_URL_VIEW', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/index.php');

define('WB_PATH_SECURITY', "view/financeiro,view/admin");

$ipLiberado = false;

if(!file_exists(WB_PATH_LOG)){ mkdir(WB_PATH_LOG); }

if($_SERVER['REMOTE_ADDR']=='189.90.70.56' or $_SERVER['REMOTE_ADDR']=='177.100.49.198'){
    $ipLiberado = true;
    define('IP_LIBERADO',true);
}

$content = addslashes($_GET['content']);
If ($content == "") {
    $content = addslashes($_POST['content']);
    If ($content == "") {
        $content = "default.php";
    }
}
/*
 * Definindo diretório para busca de classes
 * Evitando a necessidade de inserir includes ou requires
 * Afim de evitar sobrecarregar o servidor com arquivos desnecessários
 */
$path = WB_ROOT . 'hutec/cursos/inscricoes' . '/controller';
$path .= PATH_SEPARATOR . WB_ROOT . 'hutec/cursos/inscricoes' . '/domain';
$path .= PATH_SEPARATOR . WB_ROOT . 'hutec/cursos/inscricoes' . '/domain/util';
$path .= PATH_SEPARATOR . WB_ROOT . 'hutec/cursos/inscricoes' . '/domain/business';
$path .= PATH_SEPARATOR . WB_ROOT . 'hutec/cursos/inscricoes' . '/domain/entity';
$path .= PATH_SEPARATOR . WB_ROOT . 'hutec/cursos/inscricoes' . '/domain/entity/dao';
$path .= PATH_SEPARATOR . WB_ROOT . 'hutec/cursos/inscricoes' . '/domain/entity/vo';
$path .= PATH_SEPARATOR . WB_ROOT . 'hutec/cursos/inscricoes' . '/lib/PDFMerger';

set_include_path(get_include_path() . PATH_SEPARATOR . $path);

//echo get_include_path();
//if (!function_exists('__autoload')) {
//    function __autoload($class_name) {
        spl_autoload_register(function($class_name) {
        $fileClass = $class_name . ".php";
    //    if(file_exists($fileClass)) {
            require_once $fileClass;
    //    }else{
    //        echo "Arquivo da classe não encontrado:<br>".$fileClass.".<br>";
    //    }
    });
//}
if($_SERVER["SERVER_NAME"]=='eventos.test'){
//    echo __FILE__;
}
