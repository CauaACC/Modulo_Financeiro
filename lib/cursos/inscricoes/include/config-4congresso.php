<?php

define('TITLE_APP', '4º Congresso Paranaense de Saúde Pública/Coletiva');
define('DESC_EVENTO', '4º Congresso Paranaense de Saúde<br> Pública/Coletiva');
define('DESC_EVENTO_RODAPE', 'INESCO<br>Instituto de Estudos em Saúde Coletiva');
define('NOME_EVENTO', 'Congresso Paranaense de Saúde Pública/Coletiva');
define('ABREV_EVENTO', '4º CPSPC');
define('COD_EVENTO', '2');
define('ANO_EVENTO', '2018');
define('MAX_OFICINAS', '1');
define('FONE_CONTATO','');
define('FONE_CONTATO_WHATS','(43) 99696-7790');
define('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone=5543996967790');
//define('LINK_CONTATO_WHATS','');
define('EMAIL_CONTATO','eventos@inesco.org.br');
define('EMAIL_REPLAY_TO','');
define('EMAIL_BCC','');
define('EMAIL_CONTATO_ATT', 'Comissão Organizadora da 6º Congresso');
define('EMAIL_CONTATO_LINK', 'https://congressosaudepublica.org.br/');
define('EMAIL_CONTATO_LINK_AVALIACAO', 'https://opctec.net.br/inesco/4congresso/avaliacao');
define('EMAIL_CONTATO_LINK_ADMIN', 'https://opctec.net.br/inesco/4congresso/admin');
define('EMAIL_CONTATO_LINK_LOGIN', 'https://opctec.net.br/inesco/4congresso');
define('LINK_CONTATO', 'http://congressosaudepublica.org.br/#contact');
define('PRESIDENTE', 'Prof.Dr. João José Batista de Campos');
define('PRESIDENTE_TITULO', 'Presidente do 4º Congresso Paranaense de Saúde<br> Pública/Coletiva');
define('CIDADE_UF', '');

define('URL_BOLETO', 'https://www.congressosaudepublica.org.br');
define('URL_FACE', 'https://www.facebook.com/inesco');
define('URL_YOUTUBE', 'https://www.youtube.com/c/inesco-saudepublica');
define('URL_INSTAGRAM', 'https://www.instagram.com/inesco.saudepublica');
define('URL_TWITTER', '');
define('URL_LINKEDIN', 'https://br.linkedin.com/in/inesco-instituto-de-estudos-em-sa%C3%BAde-coletiva-453534b7');
define('URL_OFICIAL', 'https://opctec.net.br');
define('URL_HOME_MENU', 'http://www.congressosaudepublica.org.br');
//define('LOGO_RODAPE', 'logo-rodape-'.COD_EVENTO.'.png');
define('LOGO_RODAPE', '');

$fws['headers']     = "Bcc: zakacc@gmail.com\r\n".
$fws['emailEvento'] = "zakacc@gmail.com";


$headers = "MIME-Version: 1.1\r\n";
$headers .= "Content-type: text/plain; charset=UTF-8\r\n";
$headers .= "From: ".ABREV_EVENTO." <4cpspc-sis@opctec.com.br>\r\n"; // remetente
$headers .= "Bcc: 4cpspc-sis@opctec.com.br\r\n"; // remetente
$headers .= "Bcc: zakacc@gmail.com\r\n"; // remetente
$headers .= "Replay-To: cpspccontato@gmail.com\r\n"; // remetente
$headers .= "Return-Path: cpspccontato@gmail.com\r\n"; // return-path
define('HEADER_EMAIL', $headers);

define('FWA_REFRESH_TIME' , '2');

$FWS_Data_Hoje = Date('Ymd');

$FWS_Data_Inscricao_Inicio = '20171116';
$FWS_Data_Inscricao_Fim    = '20180630';
$FWS_Data_Edicao_Dados_Pessoais = '20180712';
$FWS_Data_Trabalho_Tramitacao_Fim= '20180601';
$FWS_Data_Max_Submissao_Trabalho = '20180531';
define('TRABALHO_ANEXA_RESUMO', false);
define('TRABALHO_ANEXA_RESUMO_FORMULARIO', true);
define('DESC_ATIVIDADE', 'Minicurso');

$FWS_Numero_Maximo_Trabalhos = 2;
$FWS_Numero_Maximo_Trabalhos_Atrelado_ao_Perfil = false;

$FWS_Data_Max_Submissao_Trabalho_Mensagem = "Envio de trabalho encerrado.";

$FWS_Dias_Prazo_Pagamento = 5;

$FWS_Liberar_Ja_Estou_Inscrito = 1;

$OP_PROCESSO_INSCRICAO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Inscricao_Fim){
    $OP_PROCESSO_INSCRICAO = true;
}

$OP_SUBMISSAO_TRABALHO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Max_Submissao_Trabalho ){
    $OP_SUBMISSAO_TRABALHO = true;
}

$OP_TRAMITACAO_TRABALHO = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Trabalho_Tramitacao_Fim ){
    $OP_TRAMITACAO_TRABALHO = true;
}

$OP_GERAR_BOLETO = false;
if('20170501' <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Inscricao_Fim){
    $OP_GERAR_BOLETO = true;
}

$OP_EDICAO_DADOS_PESSOAIS = false;
if($FWS_Data_Hoje <= $FWS_Data_Edicao_Dados_Pessoais){
    $OP_EDICAO_DADOS_PESSOAIS = true;
}

$OP_EMISSAO_CERTIFICADOS = false;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje){
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

$OP_EMISSAO_BOLETO       = true;        
$OP_EMISSAO_BOLETO_EXTRA = true;
$OP_MENSAGEM_BTN_BOLETO  = false;
$OP_SELECIONAR_ALMOCO     = false;
$OP_SELECIONAR_OFICINA    = true;
$OP_PERMITIR_ALTERAR_OFICINA = false;
/*Valores*/
$OP_VALOR_OFICINA   = 0; 
$OP_VALOR_ALMOCO    = 0; 
$OP_VALOR_INSCRICAO = 200; 
$OP_EMISSAO_CERTIFICADOS  = true;
$OP_EMISSAO_CARTA_ACEITE  = false;
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

$PROTOCOLO = "https";
//if($_SERVER["HTTPS"]){
//    $PROTOCOLO = "https";
//}
define('URL_LOGO', 'http://congressosaudepublica.org.br');
define('URL_HALL', 'http://www.opctec.com.br/inesco/5congresso/digital_hall');
define('URL_OFICIAL', 'http://www.opctec.com.br/inesco/5congresso/inscricoes/index.php?content=view/inscricao/inscricao_00.php#inscricao');

