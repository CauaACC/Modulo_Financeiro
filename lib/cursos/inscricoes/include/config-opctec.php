<?php

tratarConstantes('TITLE_APP', $organizacao->getSigla());
tratarConstantes('DESC_EVENTO', $organizacao->getSigla());
tratarConstantes('DESC_EVENTO_RODAPE', $organizacao->getSigla());
tratarConstantes('NOME_EVENTO', $organizacao->getSigla());
tratarConstantes('ABREV_EVENTO',$organizacao->getSigla());
tratarConstantes('ABREV_EVENTO_EMAIL',$organizacao->getSigla());
tratarConstantes('ANO_EVENTO',  Date('Y'));
tratarConstantes('MAX_OFICINAS', '');
tratarConstantes('FONE_CONTATO','');
// if($organizacao->getFoneContatoWhats()!=''){
//     tratarConstantes('FONE_CONTATO_WHATS',$organizacao->getFoneContatoWhats());
//     tratarConstantes('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone='.$organizacao->getFoneContatoWhats());
// }else{
//     tratarConstantes('FONE_CONTATO_WHATS','55 43 99696-7790');
//     tratarConstantes('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone=5543996967790');
// }
tratarConstantes('FONE_CONTATO_WHATS','55 43 98868-9433');
tratarConstantes('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone=5543996967790');  
if($organizacao->getAbrevEventoEmail()!=''){
    tratarConstantes('ABREV_EVENTO_EMAIL',$organizacao->getAbrevEventoEmail());
}else{
    tratarConstantes('ABREV_EVENTO_EMAIL','OPCTEC');
}
if($organizacao->getEmailContato()){
    tratarConstantes('EMAIL_CONTATO',$organizacao->getEmailContato());
}else{
    tratarConstantes('EMAIL_CONTATO','eventos@opctec.com.br');
}

tratarConstantes('EMAIL_REPLAY_TO','');
tratarConstantes('EMAIL_BCC','');
tratarConstantes('EMAIL_CONTATO_ATT', 'Comissão Organizadora');
tratarConstantes('EMAIL_CONTATO_LINK', 'https://www.opctec.net.br/'.$organizacao->getPrefixo().'/admin/');
tratarConstantes('EMAIL_CONTATO_LINK_AVALIACAO', 'https://www.opctec.net.br/'.$organizacao->getPrefixo().'/admin/avaliacao');
tratarConstantes('EMAIL_CONTATO_LINK_ADMIN', 'https://www.opctec.net.br/'.$organizacao->getPrefixo().'/admin/admin');
tratarConstantes('EMAIL_CONTATO_LINK_LOGIN', 'https://www.opctec.net.br/'.$organizacao->getPrefixo().'/admin');
tratarConstantes('LINK_CONTATO', 'https://www.opctec.net.br/'.$organizacao->getPrefixo().'/admin/#contact');
tratarConstantes('PRESIDENTE',   '');
tratarConstantes('PRESIDENTE_TITULO', 'Coordenação da '.$organizacao->getSigla());
tratarConstantes('PERIODO', ''); #<BR>horário: 19h às 21h30.
tratarConstantes('CIDADE_UF', '');

tratarConstantes('URL_BOLETO', '');
tratarConstantes('URL_FACE', '');
tratarConstantes('URL_YOUTUBE', '');
tratarConstantes('URL_INSTAGRAM', '');
tratarConstantes('URL_TWITTER', '');
tratarConstantes('URL_LINKEDIN', '');
tratarConstantes('URL_OFICIAL', 'https://www.opctec.net.br');
tratarConstantes('LOGO_RODAPE', 'logo-rodape-'.$objEv->getCodEvento().'.png');
//tratarConstantes('LOGO_RODAPE', '');

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

tratarConstantes('HEADER_EMAIL', $headers);

tratarConstantes('FWA_REFRESH_TIME' , '2');

$FWS_Data_Hoje = Date('Ymd');
$FWS_Data_HojeHM = Date('YmdHi');

$FWS_Data_Inscricao_Inicio = '20220213';
$FWS_Data_Ini_Submissao_Trabalho  = '20220214';

if($_SERVER['REMOTE_ADDR']=='189.90.70.566'){
    $FWS_Data_Liberar_Boleto = '20220213';
}else{
$FWS_Data_Liberar_Boleto = '20220214';
}

$FWS_Data_Liberar_Certificado = '';
$FWS_Data_Inscricao_Fim    = '20231231';
$FWS_Data_Edicao_Dados_Pessoais = $FWS_Data_Inscricao_Fim+7;
$FWS_Data_Trabalho_Tramitacao_Fim= '';
$FWS_Data_Max_Submissao_Trabalho = '';
$FWS_Data_Divulgacao_Trabalho_Aprovado  = '';
$FWS_Data_Divulgacao_Trabalho_Reprovado = '';
tratarConstantes('TRABALHO_ANEXA_RESUMO', false);
tratarConstantes('TRABALHO_ANEXA_RESUMO_FORMULARIO', false);
tratarConstantes('DESC_ATIVIDADE', 'Cursos');

$FWS_Numero_Maximo_Trabalhos = 0;
$FWS_Numero_Maximo_Trabalhos_Atrelado_ao_Perfil = false;

$FWS_Data_Max_Submissao_Trabalho_Mensagem = "Envio de trabalho encerrado.";
$msg_tela_login_envio_trabalho = '';
$FWS_Liberar_Ja_Estou_Inscrito = 1;
$msg_tela_previsao_inicio      = "Inicio das inscrições previsto para ".formatDateView($FWS_Data_Inscricao_Inicio).".";
//$msg_tela_previsao_inicio      = 1;
$msg_tela_login = "Bem Vindo A Arraial Do Cabo";

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
    $OP_SUBMISSAO_TRABALHO = true;
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
    $OP_PERMITIR_ANEXAR_APRESENTACAO = true;
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
    $OP_EMISSAO_CERTIFICADOS = false;
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
$fws_['op_selecionar_pedidos']= true;
$fws_['op_selecionar_evento_paralelo']= false;
$fws_['op_avaliar_ev_certificado']    = false;
$fws_['op_selecionar_ingresso'] = false;
$fws_['op_selecionar_oficina']  = true;
$fws_['op_selecionar_oficina_apos_inscricao'] = false;
$fws_['op_excluir_oficina_apos_inscricao']    = false;
$fws_['op_dados_pessoais_profissao']    = false;
$fws_['op_dados_pessoais_complemento']  = false;
$fws_['op_dados_pessoais_complemento_cursos'] = true;
$OP_NOME_CRACHA = false;
$fws_['op_dados_pessoais_tipo_instituicao'] = false;
$fws_['op_dados_pessoais_data_nascimento'] = false;
$fws_['op_dados_pessoais_recibo'] = false;
$fws_['op_dados_pessoais_instituicao'] = false;

/*Valores*/
$OP_VALOR_OFICINA   = 0;
$OP_VALOR_ALMOCO    = 0;
$OP_VALOR_INSCRICAO = 0;
$OP_EMISSAO_CARTA_ACEITE  = false;
$OP_EMISSAO_DECLARACAO_PRESENCA  = false;
$OP_AVALIACAO_ENVIAR_AO_SALVAR = true;

$OP_PERMITIR_ALTERAR_OFICINA = false;
$OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO = false;
$OP_PERMITIR_ALTERAR_EVENTO_PARALELO   = false;
$OP_PERMITIR_ADICIONAR_EVENTO_PARALELO = false;

$OP_PERMITIR_MAX_EVENTO_PARALELO       = 2;
$OP_BLOQUEAR_ALTERACAO_SE_BOLETO_GERADO = false;

$fws['op_selecionar_evento_paralelo_botao_personalizado'] = false;

$OP_EMISSAO_CERTIFICADOS  = true;

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
tratarConstantes('WB_URL_AVALIAR_EVENTO',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/avaliar_evento');
tratarConstantes('WB_URL_CERTIFICADO',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/certificados');
tratarConstantes('WB_URL_VALIDAR_CERTIFICADO',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/certificados/validar');
tratarConstantes('WB_URL_CARTA_ACEITE',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/carta_aceite');
tratarConstantes('WB_URL_COMPROVANTE_INSCRICAO',WB_URL.'/'.WB_GRUPO.'/'.WB_PROJETO.'/comprovante_inscricao');

tratarConstantes('IP',$_SERVER['REMOTE_ADDR']);
tratarConstantes('DOMINIO_URL',WB_URL);


tratarConstantes('WB_PATH_COD_EVENTO',str_pad(COD_EVENTO, '3', "0", STR_PAD_LEFT));
tratarConstantes('WB_PATH_LOG', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO . '/');
tratarConstantes('WB_PATH_LOG_CACHE', WB_ROOT . '../log/cache/');
tratarConstantes('WB_PATH_LOG_TRABALHO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/trabalhos/');
tratarConstantes('WB_PATH_LOG_APRESENTACAO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/apresentacao/');
tratarConstantes('WB_PATH_LOG_VIDEO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/video/');
tratarConstantes('WB_PATH_LOG_REMESSA', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/remessa/');
tratarConstantes('WB_PATH_LOG_EXTBOLETO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/extrato_boletos/');
tratarConstantes('WB_PATH_LOG_RETBOLETO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/retorno/');
tratarConstantes('WB_PATH_LOG_BOLETO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/boletos/');
tratarConstantes('WB_PATH_LOG_CERTIFICADOS', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/certificados/');
tratarConstantes('WB_PATH_LOG_RECIBOS', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/recibos/');
tratarConstantes('WB_PATH_LOG_CARTAS_ACEITE', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/cartas_aceite/');
tratarConstantes('WB_PATH_LOG_COMPROVANTES_INSCRICAO', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/comprovantes_inscricao/');
tratarConstantes('WB_PATH_LOG_DECLARACAO_PRESENCA', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/declaracao_presenca/');
tratarConstantes('WB_PATH_LOG_LISTA_CHAMADA', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/lista_chamada/');
tratarConstantes('WB_PATH_LOG_APP', WB_ROOT . '../log/eventos/'.WB_PATH_COD_EVENTO.'/app/');
tratarConstantes('WB_PATH_LIB', WB_ROOT . WB_PATH_COD_EVENTO.'/inscricoes/lib/');

tratarConstantes('SISTEM_PATH', dirname(__FILE__));
tratarConstantes('SITE', FWA_URL_Prefixo()); // local onde está a raiz do site
tratarConstantes('SITE_PATH', dirname(__FILE__)); // local onde está a raiz do site

#tratarConstantes('TEMPLATE_DIR', SITE . '/portal/templates/'.DEFAULT_TEMPLATE); // local do template usado no site, a partir de SITE
#tratarConstantes('TEMPLATE_PATH', SITE_PATH . '/portal/templates/'.DEFAULT_TEMPLATE); // local do template usado no site, a partir de SITE

tratarConstantes('WB_URL_ACTION', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/index-action.php');
tratarConstantes('WB_URL_CLEAN', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/index-clean.php');
tratarConstantes('WB_URL_MODAL', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/index-modal.php');
tratarConstantes('WB_URL_SITE', $PROTOCOLO.'://'.FWA_Dominio() . SITE);
tratarConstantes('WB_URL_COMMAND', WB_URL_ACTION.'?content=controller/CommandAC.php');

tratarConstantes('WB_URL_IMG', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/resources/image');
tratarConstantes('WB_URL_IMG_BOLETO', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/lib/boleto/imagens/');
//tratarConstantes('WB_URL', $PROTOCOLO.'://'.FWA_Dominio() . SITE );
tratarConstantes('WB_URL_VIEW', $PROTOCOLO.'://'.FWA_Dominio() . SITE .'/index.php');

tratarConstantes('WB_PATH_SECURITY', "view/financeiro,view/admin");

$ipLiberado = false;

if(!file_exists(WB_PATH_LOG)){ mkdir(WB_PATH_LOG); }

if($_SERVER['REMOTE_ADDR']=='189.90.70.56' or $_SERVER['REMOTE_ADDR']=='177.100.49.198'){
    $ipLiberado = true;
    tratarConstantes('IP_LIBERADO',true);
}

$content = addslashesopctec($_GET['content']);
If ($content == "") {
    $content = addslashesopctec($_POST['content']);
    If ($content == "") {
        $content = "default.php";
    }
}

