<?php

define('TITLE_APP', '5º Mostra Paranaense de Saúde');
define('DESC_EVENTO', '5º Mostra Paranaense de Saúde');
define('DESC_EVENTO_RODAPE', 'INESCO<br>Instituto de Estudos em Saúde Coletiva');
define('NOME_EVENTO', 'Mostra Paranaense de Saúde');
define('ABREV_EVENTO','5º MPS');
define('ABREV_EVENTO_EMAIL','5.MPS');
define('ANO_EVENTO',  '2019');
define('MAX_OFICINAS', '0');
define('FONE_CONTATO','');
define('FONE_CONTATO_WHATS','(43) 99696-7790');
define('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone=5543996967790');
//define('LINK_CONTATO_WHATS','');
define('EMAIL_CONTATO','eventos@inesco.org.br');
define('EMAIL_REPLAY_TO','');
define('EMAIL_BCC','');
define('EMAIL_CONTATO_ATT', 'Comissão Organizadora da 5.MPS');
define('EMAIL_CONTATO_LINK', 'https://mostraparanaensesaude.org.br/');
define('EMAIL_CONTATO_LINK_AVALIACAO', 'https://opctec.net.br/inesco/5mostraparanaensesaude/avaliacao');
define('EMAIL_CONTATO_LINK_ADMIN', 'https://opctec.net.br/inesco/5mostraparanaensesaude/admin');
define('EMAIL_CONTATO_LINK_LOGIN', 'https://opctec.net.br/inesco/5mostraparanaensesaude');
define('LINK_CONTATO', 'http://mostraparanaensesaude.org.br/#contact');
define('PRESIDENTE',   'Prof.Dr. João José Batista de Campos');
define('PRESIDENTE_TITULO', 'Presidente da 5º Mostra Paranaense de Saúde');
define('PERIODO', ''); #<BR>horário: 19h às 21h30.
define('CIDADE_UF', '');

define('URL_BOLETO', 'https://www.mostraparanaensesaude.org.br');
define('URL_FACE', 'https://www.facebook.com/inesco');
define('URL_YOUTUBE', 'https://www.youtube.com/c/inesco-saudepublica');
define('URL_INSTAGRAM', 'https://www.instagram.com/inesco.saudepublica');
define('URL_TWITTER', '');
define('URL_LINKEDIN', 'https://br.linkedin.com/in/inesco-instituto-de-estudos-em-sa%C3%BAde-coletiva-453534b7');
define('URL_OFICIAL', 'https://www.opctec.net.br');
define('URL_HOME_MENU', 'http://www.mostraparanaensesaude.org.br');
//define('LOGO_RODAPE', 'logo-rodape-'.COD_EVENTO.'.png');
define('LOGO_RODAPE', '');

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

$FWS_Data_Inscricao_Inicio = '20190205';
$FWS_Data_Liberar_Boleto = '20190205';
$FWS_Data_Liberar_Certificado = '20190719';
$FWS_Data_Inscricao_Fim    = '20190610';
$FWS_Data_Edicao_Dados_Pessoais = '20190722';
$FWS_Data_Trabalho_Tramitacao_Fim= '20190701';
$FWS_Data_Max_Submissao_Trabalho = '20190603';
$FWS_Data_Divulgacao_Trabalho_Aprovado  = '20190603';
$FWS_Data_Divulgacao_Trabalho_Reprovado = '20190607';
define('TRABALHO_ANEXA_RESUMO', false);
define('TRABALHO_ANEXA_RESUMO_FORMULARIO', true);
define('DESC_ATIVIDADE', 'Minicurso');

$FWS_Numero_Maximo_Trabalhos = 2;
$FWS_Numero_Maximo_Trabalhos_Atrelado_ao_Perfil = false;

$FWS_Data_Max_Submissao_Trabalho_Mensagem = "Envio de trabalho encerrado.";
$msg_tela_login_envio_trabalho = 'Envio de Trabalhos<br>Encerrado em 22 de abril';
$FWS_Liberar_Ja_Estou_Inscrito = 1;
$msg_tela_previsao_inicio      = "Inicio das inscrições previsto para ".formatDateView($FWS_Data_Inscricao_Inicio).".";
$msg_tela_previsao_inicio      = 0;

$FWS_Dias_Prazo_Pagamento = 5;
$OP_PROCESSO_INSCRICAO_SUSPENSO = false;

$OP_PROCESSO_INSCRICAO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Inscricao_Fim){
    $OP_PROCESSO_INSCRICAO = true;
}
//$OP_PROCESSO_INSCRICAO = false;

$OP_SUBMISSAO_TRABALHO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Max_Submissao_Trabalho ){
    $OP_SUBMISSAO_TRABALHO = true;
}
if ($_SESSION['cod_inscrito'] == '5016') {
    $OP_SUBMISSAO_TRABALHO = true;
}
$OP_TRAMITACAO_TRABALHO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Trabalho_Tramitacao_Fim ){
    $OP_TRAMITACAO_TRABALHO = true;
}

$SIS_ADM_AVALIACAO_TRABALHOS = true;
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

$OP_ANEXAR_APRESENTACAO = true;
$OP_PERMITIR_ANEXAR_APRESENTACAO = false;
$OP_DIVULGAR_ENSALAMENTO = true;

$OP_GERAR_BOLETO = false;
if($FWS_Data_Liberar_Boleto <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Inscricao_Fim){
    $OP_GERAR_BOLETO = true;
}
$OP_GERAR_BOLETO = false;

$OP_EDICAO_DADOS_PESSOAIS = false;
if($FWS_Data_Hoje <= $FWS_Data_Edicao_Dados_Pessoais){
    $OP_EDICAO_DADOS_PESSOAIS = true;
}

$OP_EMISSAO_CERTIFICADOS = false;
if($FWS_Data_Liberar_Certificado <= $FWS_Data_Hoje){
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

$OP_EMISSAO_BOLETO       = false;        
$OP_EMISSAO_BOLETO_EXTRA = true;
$OP_MENSAGEM_BTN_BOLETO  = false;
$OP_SELECIONAR_ALMOCO     = false;
$OP_SELECIONAR_OFICINA    = FALSE;
$OP_PERMITIR_ALTERAR_OFICINA = false;
/*Valores*/
$OP_VALOR_OFICINA   = 0; 
$OP_VALOR_ALMOCO    = 0; 
$OP_VALOR_INSCRICAO = 200; 
$OP_EMISSAO_CARTA_ACEITE  = false;
$OP_EMISSAO_DECLARACAO_PRESENCA  = true;
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


define('WB_URL_AVALIAR_EVENTO',WB_URL.'/inesco/'.WB_PROJETO.'/avaliar_evento');
define('WB_URL_CERTIFICADO',WB_URL.'/inesco/'.WB_PROJETO.'/certificados');
define('WB_URL_VALIDAR_CERTIFICADO',WB_URL.'/inesco/'.WB_PROJETO.'/certificados/validar');
define('WB_URL_CARTA_ACEITE',WB_URL.'/inesco/'.WB_PROJETO.'/carta_aceite');
define('WB_URL_COMPROVANTE_INSCRICAO',WB_URL.'/inesco/'.WB_PROJETO.'/comprovante_inscricao');

define('IP',$_SERVER['REMOTE_ADDR']);
define('DOMINIO_URL',WB_URL);


define('WB_PATH_COD_EVENTO',str_pad(COD_EVENTO, '3', "0", STR_PAD_LEFT));
define('WB_PATH_LOG', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO . '/');
define('WB_PATH_LOG_TRABALHO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/trabalhos/');
define('WB_PATH_LOG_APRESENTACAO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/apresentacao/');
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
@define('WB_URL', $PROTOCOLO.'://'.FWA_Dominio() . SITE );
define('WB_URL_VIEW', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/index.php');

define('WB_PATH_SECURITY', "view/financeiro,view/admin");

$ipLiberado = false;

