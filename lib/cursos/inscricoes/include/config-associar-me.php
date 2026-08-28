<?php

define('TITLE_APP', 'Associação Inesco');
define('DESC_EVENTO', 'Associação Inesco');
define('DESC_EVENTO_RODAPE', 'INESCO<br>Instituto de Estudos em Saúde Coletiva');
define('NOME_EVENTO', 'Associação Inesco');
define('ABREV_EVENTO','Associação');
define('ABREV_EVENTO_EMAIL','Associação');
define('ANO_EVENTO',  '2021');
define('MAX_OFICINAS', '2');
define('FONE_CONTATO','(43) 996967790');
define('EMAIL_CONTATO','eventos@inesco.org.br');
define('EMAIL_REPLAY_TO','');
define('EMAIL_BCC','');
define('EMAIL_CONTATO_ATT', 'Comissão Organizadora da Associação');
define('EMAIL_CONTATO_LINK', 'https://associar.me');
define('LINK_CONTATO', 'http://mostrasaudepublica.org.br/#contact');
define('PRESIDENTE',   'Prof.Dr. Marcio José de Almeida');
define('PRESIDENTE_TITULO', 'Coordenador da Associação Inesco');
define('PERIODO', 'Evento Online'); #<BR>horário: 19h às 21h30.
define('CIDADE_UF', '');

define('URL_FACE', 'https://www.facebook.com/inesco');
define('URL_YOUTUBE', 'https://www.youtube.com/c/inesco-saudepublica');
define('URL_INSTAGRAM', 'https://www.instagram.com/inesco.saudepublica');
define('URL_TWITTER', '');
define('URL_LINKEDIN', 'https://br.linkedin.com/in/inesco-instituto-de-estudos-em-sa%C3%BAde-coletiva-453534b7');

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

$FWS_Data_Inscricao_Inicio = '20210222';
$FWS_Data_Ini_Submissao_Trabalho  = '20210222';

if($_SERVER['REMOTE_ADDR']=='189.90.70.566'){
    $FWS_Data_Liberar_Boleto = '20191003';
}else{
$FWS_Data_Liberar_Boleto = '20210222';
}

$FWS_Data_Liberar_Certificado = '202107101400';
if($_SERVER['REMOTE_ADDR']=='201.21.130.22'){
    $FWS_Data_Liberar_Certificado = '202007261400';
}
$FWS_Data_Inscricao_Fim    = '20210701';
$FWS_Data_Edicao_Dados_Pessoais = $FWS_Data_Inscricao_Fim+7;
$FWS_Data_Trabalho_Tramitacao_Fim= '20210620';
$FWS_Data_Max_Submissao_Trabalho = '20210531';
$FWS_Data_Divulgacao_Trabalho_Aprovado  = '';
$FWS_Data_Divulgacao_Trabalho_Reprovado = '';
define('TRABALHO_ANEXA_RESUMO', false);
define('TRABALHO_ANEXA_RESUMO_FORMULARIO', true);
define('DESC_ATIVIDADE', 'Minicurso');

$FWS_Numero_Maximo_Trabalhos = 3;
$FWS_Numero_Maximo_Trabalhos_Atrelado_ao_Perfil = false;

$FWS_Data_Max_Submissao_Trabalho_Mensagem = "Envio de trabalho encerrado.";
$msg_tela_login_envio_trabalho = 'Envio de Trabalhos<br>Encerra-se em 31 de maio';
$FWS_Liberar_Ja_Estou_Inscrito = 1;
$msg_tela_previsao_inicio      = "Inicio das inscrições previsto para ".formatDateView($FWS_Data_Inscricao_Inicio).".";
$msg_tela_previsao_inicio      = 0;

$FWS_Dias_Prazo_Pagamento = 5;
$OP_PROCESSO_INSCRICAO_SUSPENSO = false;

/**Evento Online */
$OP_EVENTO_ONLINE_LIBERADO = false;
$OP_EVENTO_ONLINE_YOUTUBE_LIBERADO = false;
if($_SERVER['REMOTE_ADDR']=='179.154.183.222' or ('202107090730'<=Date('YmdHi') and Date('YmdHi')<='202107112359')){
    $OP_EVENTO_ONLINE_YOUTUBE_LIBERADO = true;
}
$OP_EVENTO_ONLINE_MOSTRA_LIBERADO = false;
if($_SERVER['REMOTE_ADDR']=='179.154.183.222' or ('202107090730'<=Date('YmdHi') and Date('YmdHi')<='202107112359')){
    $OP_EVENTO_ONLINE_MOSTRA_LIBERADO = true;
}
$OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO = false;
if($_SERVER['REMOTE_ADDR']=='179.154.183.222' or ('202107090730'<=Date('YmdHi') and Date('YmdHi')<='202107112359')){
    $OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO = true;
}
//$OP_EVENTO_AVISOS = false;
//if($_SERVER['REMOTE_ADDR']!='179.154.183.222'){
    $OP_EVENTO_AVISOS = false;
//}

$OP_PROCESSO_INSCRICAO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Inscricao_Fim){
    $OP_PROCESSO_INSCRICAO = true;
}
//$OP_PROCESSO_INSCRICAO = false;

$OP_SUBMISSAO_TRABALHO = false;
if($FWS_Data_Ini_Submissao_Trabalho <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Max_Submissao_Trabalho ){
    $OP_SUBMISSAO_TRABALHO = true;
}
$OP_SUBMISSAO_TRABALHO_AGUARDANDO = false;
if($FWS_Data_Hoje <= $FWS_Data_Ini_Submissao_Trabalho ){
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

$SIS_ADM_AVALIACAO_TRABALHOS = true;
$SIS_AVALIACAO_TRABALHOS_PREMIADOS = false;
$SIS_ADM_AVALIACAO_TRABALHOS_DEFINIR_PREMIADOS = false;

$OP_DIVULGAR_TRABALHO_REPROVADO = true;
if($FWS_Data_Divulgacao_Trabalho_Reprovado <= $FWS_Data_Hoje){
    $OP_DIVULGAR_TRABALHO_REPROVADO = true;
}
$OP_DIVULGAR_TRABALHO_APROVADO = false;
if($FWS_Data_Divulgacao_Trabalho_Aprovado <= $FWS_Data_Hoje){
    $OP_DIVULGAR_TRABALHO_APROVADO = true;
}

$OP_ANEXAR_VIDEO = false;
$OP_PERMITIR_ANEXAR_VIDEO = false;
$OP_ANEXAR_APRESENTACAO = true;
$OP_ANEXAR_APRESENTACAO_ENCERRADO = true;
$OP_PERMITIR_ANEXAR_APRESENTACAO = true;
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

$OP_EMISSAO_CARTA_ACEITE = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje){
    $OP_EMISSAO_CARTA_ACEITE = true;
}

$OP_EMISSAO_COMPROVANTE_INSCRICAO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje){
    $OP_EMISSAO_COMPROVANTE_INSCRICAO = true;
}

$OP_INCRICAO_GRATUITA = false;
$OP_EMISSAO_BOLETO = true;
$OP_EMISSAO_BOLETO_EM_MANUTENCAO = false;
$OP_EMISSAO_BOLETO_EXTRA = true;
$OP_MENSAGEM_BTN_BOLETO  = false;
$OP_SELECIONAR_ALMOCO     = false;
/**/
$fws_['op_selecionar_oficina']    = true;
$fws_['op_selecionar_oficina_apos_inscricao']    = true;
$fws_['op_excluir_oficina_apos_inscricao']    = true;
$fws_['op_dados_pessoais_profissao']    = true;
$fws_['op_dados_pessoais_complemento'] = true;
$OP_NOME_CRACHA = true;
$fws_['op_dados_pessoais_tipo_instituicao'] = true;
$fws_['op_dados_pessoais_data_nascimento'] = false;
$fws_['op_dados_pessoais_recibo'] = true;

$OP_PERMITIR_ALTERAR_OFICINA = true;
/*Valores*/
$OP_VALOR_OFICINA   = 0; 
$OP_VALOR_ALMOCO    = 0; 
$OP_VALOR_INSCRICAO = 0; 
$OP_EMISSAO_CARTA_ACEITE  = false;
$OP_EMISSAO_DECLARACAO_PRESENCA  = false;
$OP_AVALIACAO_ENVIAR_AO_SALVAR = true;

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
define('WB_URL_AVALIAR_EVENTO',WB_URL.'/avaliar_evento');
define('WB_URL_CERTIFICADO',WB_URL.'/certificados');
define('WB_URL_VALIDAR_CERTIFICADO',WB_URL.'/certificados/validar');
define('WB_URL_CARTA_ACEITE',WB_URL.'/carta_aceite');
define('WB_URL_COMPROVANTE_INSCRICAO',WB_URL.'/comprovante_inscricao');

define('IP',$_SERVER['REMOTE_ADDR']);
define('DOMINIO_URL',WB_URL);


define('WB_PATH_COD_EVENTO',str_pad(COD_EVENTO, '3', "0", STR_PAD_LEFT));
define('WB_PATH_LOG', WB_ROOT . '../../../../log/eventos/'.WB_PATH_COD_EVENTO . '/');

define('WB_PATH_LOG_TRABALHO',      WB_PATH_LOG.'trabalhos/');
define('WB_PATH_LOG_APRESENTACAO',  WB_PATH_LOG.'apresentacao/');
define('WB_PATH_LOG_REMESSA',       WB_PATH_LOG.'remessa/');
define('WB_PATH_LOG_EXTBOLETO',     WB_PATH_LOG.'extrato_boletos/');
define('WB_PATH_LOG_RETBOLETO',     WB_PATH_LOG.'retorno/');
define('WB_PATH_LOG_BOLETO',        WB_PATH_LOG.'boletos/');
define('WB_PATH_LOG_CERTIFICADOS',  WB_PATH_LOG.'certificados/');
define('WB_PATH_LOG_RECIBOS',       WB_PATH_LOG.'recibos/');
define('WB_PATH_LOG_CARTAS_ACEITE', WB_PATH_LOG.'cartas_aceite/');
define('WB_PATH_LOG_COMPROVANTES_INSCRICAO', WB_PATH_LOG.'comprovantes_inscricao/');
define('WB_PATH_LOG_DECLARACAO_PRESENCA', WB_PATH_LOG.'declaracao_presenca/');
define('WB_PATH_LOG_LISTA_CHAMADA', WB_PATH_LOG.'lista_chamada/');
define('WB_PATH_LOG_APP',           WB_PATH_LOG.'app/');

define('SISTEM_PATH', dirname(__FILE__));
define('SITE', '/inscricoes'); // local onde está a raiz do site
define('SITE_PATH', dirname(__FILE__)); // local onde está a raiz do site

#define('TEMPLATE_DIR', SITE . '/portal/templates/'.DEFAULT_TEMPLATE); // local do template usado no site, a partir de SITE
#define('TEMPLATE_PATH', SITE_PATH . '/portal/templates/'.DEFAULT_TEMPLATE); // local do template usado no site, a partir de SITE

define('WB_URL_ACTION', $WB_URL .'/index-action.php');
define('WB_URL_CLEAN',  $WB_URL .'/index-clean.php');
define('WB_URL_MODAL',  $WB_URL .'/index-modal.php');
define('WB_URL_SITE',   $WB_URL);
define('WB_URL_COMMAND', WB_URL_ACTION.'?content=controller/CommandAC.php');

define('WB_URL_IMG',    $WB_URL .'/resources/image');
define('WB_URL_IMG_BOLETO', $WB_URL .'/lib/boleto/imagens/');
//@define('WB_URL',       $WB_URL );
define('WB_URL_VIEW',   $WB_URL .'/index.php');

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
