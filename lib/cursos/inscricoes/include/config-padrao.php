<?php

// // constantes externas
// @include_once($_SERVER['DOCUMENT_ROOT'] . '/../logn/eventos/cache/cfg/env.php');

define('TITLE_APP', $objEv->getTituloEvento());
define('DESC_EVENTO', $objEv->getTituloEvento());
define('DESC_EVENTO_RODAPE',  $objEv->getDescRodape());
define('NOME_EVENTO', $objEv->getTituloEvento());
define('ABREV_EVENTO',$objEv->getAbrevEvento());
define('ANO_EVENTO',  $objEv->getAnoEvento());

define('FONE_CONTATO',$objEv->getFoneContato());
define('FONE_ORGANIZADOR',$objEv->getFoneOrganizador());
// if($objEv->getFoneContatoWhats()!=''){
//     define('FONE_CONTATO_WHATS',$objEv->getFoneContatoWhats());
//     define('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone='.$objEv->getFoneContatoWhats());
// }else{
    // if($objEv->getFoneOrganizador()!=''){
    //     define('FONE_CONTATO_WHATS','');
    //     define('LINK_CONTATO_WHATS','');     
    // }else{
    //     define('FONE_CONTATO_WHATS','55 43 99696-7790');
    //     define('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone=5543996967790');
    // }
// }
// define('FONE_CONTATO_WHATS','55 43 98868-9433');
// define('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone=5543996967790');   
define('FONE_CONTATO_WHATS','55 43 99696-7790');
define('LINK_CONTATO_WHATS','https://api.whatsapp.com/send?phone=5543996967790');
if($objEv->getAbrevEventoEmail()!=''){
    define('ABREV_EVENTO_EMAIL',$objEv->getAbrevEventoEmail());
}else{
    define('ABREV_EVENTO_EMAIL',$objEv->getGestor());
}
if($objEv->getEmailContato()){
    define('EMAIL_CONTATO',$objEv->getEmailContato());
}else{
    define('EMAIL_CONTATO','eventos@opctec.com.br');
}
define('EMAIL_REPLAY_TO',$objEv->getEmailReplayTo());
define('EMAIL_BCC',$objEv->getEmailBcc());
define('EMAIL_CONTATO_ATT', $objEv->getEmailContatoAtt());
if($objEv->getEmailContatoLink()){
    define('EMAIL_CONTATO_LINK', $objEv->getEmailContatoLink());
}else{
    define('EMAIL_CONTATO_LINK', WB_URL_SITE);
}
if($objEv->getEmailContatoLinkAvaliacao()){
    define('EMAIL_CONTATO_LINK_AVALIACAO', $objEv->getEmailContatoLinkAvaliacao());
}else{
    define('EMAIL_CONTATO_LINK_AVALIACAO', WB_URL_SITE."/index.php?content=view/admin/default.php#adminSistema");
}
if($objEv->getEmailContatoLinkAdmin()){
    define('EMAIL_CONTATO_LINK_ADMIN', $objEv->getEmailContatoLinkAdmin());
}else{
    define('EMAIL_CONTATO_LINK_ADMIN', WB_URL_LOGIN);
}

if($objEv->getEmailContatoLinkLogin()){
    define('EMAIL_CONTATO_LINK_LOGIN', $objEv->getEmailContatoLinkLogin());
}else{
    define('EMAIL_CONTATO_LINK_LOGIN', WB_URL_LOGIN);
}
define('LINK_CONTATO', $objEv->getLinkContato());

// Fallback caso o arquivo de chaves nao exista
if (!defined('WB_CRIPTOGRAFIA_CHAVE') || !defined('WB_CRYPTO_KEY')) {
    die('Erro: arquivo de configuracao nao encontrado.');
}

define('PRESIDENTE',   $objEv->getPresidente());
define('PRESIDENTE_TITULO', $objEv->getPresidenteTitulo());
define('PERIODO', $objEv->getPeriodo());
define('CIDADE_UF', $objEv->getCidadeUf());

define('URL_HOME_MENU', $objEv->getUrlHomeMenu());
define('URL_LOGO', $objEv->getUrlLogo());
//define('URL_BOLETO', $objEv->getUrlBoleto());
define('URL_FACE', $objEv->getUrlFace());
define('URL_YOUTUBE', $objEv->getUrlYoutube());
define('URL_INSTAGRAM', $objEv->getUrlInstagram());
define('URL_TWITTER', $objEv->getUrlTwitter());
define('URL_LINKEDIN', $objEv->getUrlLinkedin());
define('URL_OFICIAL', $objEv->getUrlOficial());
//define('LOGO_RODAPE', 'logo-rodape-'.COD_EVENTO.'.png');
define('LOGO_RODAPE', $objEv->getLogoRodape());

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

$ipProcurado = $_SERVER['REMOTE_ADDR'];
if($objEvConf->getSimularData()==true AND (verificaIP($objEvConf->getIpsSimulacao(), $ipProcurado))){
    $FWS_Data_Hoje   = formatDateTimeSemSeparador($objEvConf->getDataSimulacao());
    $FWS_Data_HojeHM = formatDateTimeSemSeparador($objEvConf->getDataSimulacao());
    define('Data_Hoje',formatDateTimeSemSeparador($objEvConf->getDataSimulacao()));
    define('Data_HojeHM',formatDateTimeSemSeparador($objEvConf->getDataSimulacao()));
}else{
    $FWS_Data_Hoje   = Date('Ymd');
    $FWS_Data_HojeHM = Date('YmdHis');
    define('Data_Hoje',Date('Ymd'));
    define('Data_HojeHM',Date('YmdHis'));
}
$FWS_Data_Inscricao_Inicio      = formatDateTimeSemSeparador($objEvConf->getDataInscricaoIni());
$FWS_Data_Inscricao_Fim         = formatDateTimeSemSeparador($objEvConf->getDataInscricaoFim());

$FWS_Data_Ini_Submissao_Trabalho  = formatDateTimeSemSeparador($objEvConf->getDataIniSubimissaoTrabalho());

$FWS_Data_Liberar_Boleto = formatDateTimeSemSeparador($objEvConf->getDataLiberarBoleto());
$FWS_Data_Liberar_Certificado   = formatDateTimeSemSeparador($objEvConf->getDataLiberarCertificado());
$FWS_Data_Edicao_Dados_Pessoais = formatDateTimeSemSeparador($objEvConf->getEdDadosDatFim());
if(is_numeric($FWS_Data_Inscricao_Fim)){
    $FWS_Data_Edicao_Dados_PessoaisInsc = $FWS_Data_Inscricao_Fim+7;
}
if($FWS_Data_Edicao_Dados_Pessoais < $FWS_Data_Edicao_Dados_PessoaisInsc){
    $FWS_Data_Edicao_Dados_Pessoais = $FWS_Data_Edicao_Dados_PessoaisInsc;
}
$FWS_Data_Trabalho_Tramitacao_Fim = formatDateTimeSemSeparador($objEvConf->getTrabTramitacaoDatFim());
$FWS_Data_Max_Submissao_Trabalho  = formatDateTimeSemSeparador($objEvConf->getTrabSubmissaoDatFim());
$FWS_Data_Divulgacao_Trabalho_Aprovado  = formatDateTimeSemSeparador($objEvConf->getTrabAprovadoDatDiv());
$FWS_Data_Divulgacao_Trabalho_Reprovado = formatDateTimeSemSeparador($objEvConf->getTrabReprovadoDatDiv());

define('TRABALHO_ANEXA_RESUMO', $objEvConf->getTrabResumoArquivo());
define('TRABALHO_ANEXA_RESUMO_FORMULARIO', $objEvConf->getTrabResumoFormulario());
define('DESC_ATIVIDADE', $objEvConf->getDescAtividade());

$FWS_Numero_Maximo_Trabalhos = $objEvConf->getTrabSubmissaoQtd();
$FWS_Numero_Maximo_Trabalhos_Atrelado_ao_Perfil = $objEvConf->getTrabSubmissaoQtdPerfil();

$FWS_Data_Max_Submissao_Trabalho_Mensagem = formatDateTimeSemSeparador($objEvConf->getDataMaxSubmissaoTrabalhoMensagem());
$msg_tela_login_envio_trabalho = $objEvConf->getMsgTelaLoginEnvioTrabalho();
$FWS_Liberar_Ja_Estou_Inscrito = $objEvConf->getLiberarJaEstouInscrito();
$msg_tela_previsao_inicio      = "Inicio das inscrições previsto para ".formatDateView($FWS_Data_Inscricao_Inicio).".";
//$msg_tela_previsao_inicio      = 1;

$FWS_Dias_Prazo_Pagamento = 5;
$OP_PROCESSO_INSCRICAO_SUSPENSO = $objEvConf->getProcessoInscricaoSuspenso();

/**Evento Online */
if($objEvConf->getEventoOnline()==1){
    $OP_EVENTO_ONLINE_LIBERADO = $objEvConf->getHallVirtualAtivar();
    $OP_EVENTO_ONLINE_YOUTUBE_LIBERADO = $objEvConf->getOpEventoOnlineYoutubeLiberado();
    $OP_EVENTO_ONLINE_MOSTRA_LIBERADO = $objEvConf->getOpEventoOlineMostraLiberado();
    $OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO = $objEvConf->getAtividadeLinkOnlineLiberar();
    $OP_EVENTO_AVISOS = $objEvConf->getOpEventoAvisos();
}else{
    $OP_EVENTO_ONLINE_LIBERADO = false;
    $OP_EVENTO_ONLINE_YOUTUBE_LIBERADO = false;
    $OP_EVENTO_ONLINE_MOSTRA_LIBERADO = false;
    $OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO = false;
}
$OP_EVENTO_AVISOS = $objEvConf->getOpEventoAvisos();

$OP_PROCESSO_INSCRICAO = $objEvConf->getProcessoInscricao();
$OP_SUBMISSAO_TRABALHO = $objEvConf->getTrabSubmissao();
$OP_SUBMISSAO_TRABALHO_AGUARDANDO = $objEvConf->getOpSubimissaoTrabalhoAguardando();
$OP_SUBMISSAO_TRABALHO_ENCERRADA = $objEvConf->getOpSubimissaoTrabalhoEncerrada();
$OP_TRAMITACAO_TRABALHO = $objEvConf->getOpTramitacaoTrabalho();
$OP_AVALIACAO_ENVIAR_AO_SALVAR = $objEvConf->getTrabAvaliacaoAoSalvar();

$SIS_ADM_AVALIACAO_TRABALHOS = $objEvConf->getSisAdmAvaliacaoTrabalho();
$SIS_AVALIACAO_TRABALHOS_PREMIADOS = $objEvConf->getSisAvaliacaoTrabalhosPremiados();
$SIS_ADM_AVALIACAO_TRABALHOS_DEFINIR_PREMIADOS = $objEvConf->getSisAdmAvaliacaoTrabalhosDefinirPremiados();

$OP_DIVULGAR_TRABALHO_REPROVADO = $objEvConf->getOpDivultarTrabalhoReprovado();
$OP_DIVULGAR_TRABALHO_APROVADO = $objEvConf->getOpDivulgarTrabalhoAprovado();
$OP_DIVULGAR_ENSALAMENTO = $objEvConf->getOpDivulgarEnsalamento();

//$OP_ANEXAR_VIDEO = $objEvConf->getOpAnexarVideo();
//$OP_ANEXAR_APRESENTACAO_VIDEO_ENCERRADO = $objEvConf->getOpAnexarApresentacaoVideoEncerrado();
//$OP_PERMITIR_ANEXAR_VIDEO = $objEvConf->getOpPermitirAnexarVideo();
//if($_SERVER['REMOTE_ADDR']=='189.90.70.56' or $_SERVER['REMOTE_ADDR']=='200.250.28.242'){
//    $OP_ANEXAR_APRESENTACAO_VIDEO_ENCERRADO = false;
//    $OP_PERMITIR_ANEXAR_VIDEO = true;
//}

$OP_PERMITIR_ANEXAR_VIDEO  = false;
$OP_ANEXAR_APRESENTACAO_VIDEO_ENCERRADO = false;
$OP_ANEXAR_VIDEO           = false;
$OP_ANEXAR_VIDEO           = $objEvConf->getApresentacaoAnexarVideo();
if($OP_ANEXAR_VIDEO){
    /*calcular pelas datas se é permitiro anexar ou não*/
    if (testaPeriodo($objEvConf->getApresentacaoAnexarVideoDatIni(),$objEvConf->getApresentacaoAnexarVideoDatFim())){
        $OP_ANEXAR_APRESENTACAO_VIDEO_ENCERRADO = false;
        $OP_PERMITIR_ANEXAR_VIDEO  = true;
    }
    if (testaPeriodo($objEvConf->getApresentacaoAnexarVideoDatFim(),Data_HojeHM)){
        $OP_ANEXAR_APRESENTACAO_VIDEO_ENCERRADO = true;
        $OP_PERMITIR_ANEXAR_VIDEO  = false;
    }
//    if($_SERVER['REMOTE_ADDR']=='200.203.142.25'){
//        $OP_ANEXAR_APRESENTACAO_ENCERRADO = false;
//        $OP_PERMITIR_ANEXAR_APRESENTACAO  = true;
//    }
}

$OP_PERMITIR_ANEXAR_APRESENTACAO  = false;
$OP_ANEXAR_APRESENTACAO_ENCERRADO = false;
$OP_ANEXAR_APRESENTACAO           = false;
$OP_ANEXAR_APRESENTACAO           = $objEvConf->getApresentacaoAnexar();
if($OP_ANEXAR_APRESENTACAO){
    /*calcular pelas datas se é permitiro anexar ou não*/
    if (testaPeriodo($objEvConf->getApresentacaoAnexarDatIni(),$objEvConf->getApresentacaoAnexarDatFim())){
        $OP_ANEXAR_APRESENTACAO_ENCERRADO = false;
        $OP_PERMITIR_ANEXAR_APRESENTACAO  = true;
    }
    if (testaPeriodo($objEvConf->getApresentacaoAnexarDatFim(),Data_HojeHM)){
        $OP_ANEXAR_APRESENTACAO_ENCERRADO = true;
        $OP_PERMITIR_ANEXAR_APRESENTACAO  = false;
    }
}

$OP_PERMITIR_ANEXAR_AVULSO  = false;
$OP_ANEXAR_AVULSO_ENCERRADO = false;
$OP_ANEXAR_AVULSO           = false;
$OP_ANEXAR_AVULSO           = $objEvConf->getAvulsoAnexar();
if($OP_ANEXAR_AVULSO){
    /*calcular pelas datas se é permitiro anexar ou não*/
    if (testaPeriodo($objEvConf->getAvulsoAnexarDatIni(),$objEvConf->getAvulsoAnexarDatFim())){
        $OP_ANEXAR_AVULSO_ENCERRADO = false;
        $OP_PERMITIR_ANEXAR_AVULSO  = true;
    }
    if (testaPeriodo($objEvConf->getAvulsoAnexarDatFim(),Data_HojeHM)){
        $OP_ANEXAR_AVULSO_ENCERRADO = true;
        $OP_PERMITIR_ANEXAR_AVULSO  = false;
    }
}

$OP_PERMITIR_ANEXAR_CONTRATO  = false;
$OP_ANEXAR_CONTRATO_ENCERRADO = false;
$OP_ANEXAR_CONTRATO           = false;
$OP_ANEXAR_CONTRATO           = $objEvConf->getContratoAnexar();
if($OP_ANEXAR_CONTRATO){
    /*calcular pelas datas se é permitiro anexar ou não*/
    if (testaPeriodo($objEvConf->getContratoAnexarDatIni(),$objEvConf->getContratoAnexarDatFim())){
        $OP_ANEXAR_CONTRATO_ENCERRADO = false;
        $OP_PERMITIR_ANEXAR_CONTRATO  = true;
    }
    if (testaPeriodo($objEvConf->getContratoAnexarDatFim(),Data_HojeHM)){
        $OP_ANEXAR_CONTRATO_ENCERRADO = true;
        $OP_PERMITIR_ANEXAR_CONTRATO  = false;
    }
}
// if($_SERVER['REMOTE_ADDR']=='177.82.0.28'){
//     $OP_PERMITIR_ANEXAR_CONTRATO  = TRUE;
//     $OP_ANEXAR_CONTRATO_ENCERRADO = false;
//     $OP_ANEXAR_CONTRATO           = TRUE;
// }

$OP_PERMITIR_ANEXAR_BOLETO  = false;
$OP_ANEXAR_BOLETO_ENCERRADO = false;
$OP_ANEXAR_BOLETO           = false;
$OP_ANEXAR_BOLETO           = $objEvConf->getBoletoAnexar();
if($OP_ANEXAR_BOLETO){
    /*calcular pelas datas se é permitiro anexar ou não*/
    if (testaPeriodo($objEvConf->getBoletoAnexarDatIni(),$objEvConf->getBoletoAnexarDatFim())){
        $OP_ANEXAR_BOLETO_ENCERRADO = false;
        $OP_PERMITIR_ANEXAR_BOLETO  = true;
    }
    if (testaPeriodo($objEvConf->getBoletoAnexarDatFim(),Data_HojeHM)){
        $OP_ANEXAR_BOLETO_ENCERRADO = true;
        $OP_PERMITIR_ANEXAR_BOLETO  = false;
    }
}

$OP_PERMITIR_ANEXAR_RPA  = false;
$OP_ANEXAR_RPA_ENCERRADO = false;
$OP_ANEXAR_RPA           = false;
$OP_ANEXAR_RPA           = $objEvConf->getRpaAnexar();
if($OP_ANEXAR_RPA){
    /*calcular pelas datas se é permitiro anexar ou não*/
    if (testaPeriodo($objEvConf->getRpaAnexarDatIni(),$objEvConf->getRpaAnexarDatFim())){
        $OP_ANEXAR_RPA_ENCERRADO = false;
        $OP_PERMITIR_ANEXAR_RPA  = false;
    }
    if (testaPeriodo($objEvConf->getRpaAnexarDatFim(),Data_HojeHM)){
        $OP_ANEXAR_RPA_ENCERRADO = false;
        $OP_PERMITIR_ANEXAR_RPA  = false;
    }
}

//$FWS_Data_Max_Submissao_Trabalho  = formatDateTimeSemSeparador($objEvConf->getTrabSubmissaoDatFim());
//$FWS_Data_Max_Submissao_Trabalho  = formatDateTimeSemSeparador($objEvConf->getTrabSubmissaoDatFim());
$OP_PERMITIR_SOLICITAR_RECURSO = false;
if (testaPeriodo($objEvConf->getTrabRecursoDatIni(),$objEvConf->getTrabRecursoDatFim())){
    $OP_PERMITIR_SOLICITAR_RECURSO = true;
}
$OP_EMISSAO_CERTIFICADOS  = $objEvConf->getEmitirCertificado();
$OP_EMISSAO_RECIBOS       = $objEvConf->getEmitirRecibo();
$OP_EMISSAO_CARTA_ACEITE  = $objEvConf->getEmitirCartaAceite();
$OP_EMISSAO_COMPROVANTE_INSCRICAO = $objEvConf->getEmitirComprovanteInscricao();
$OP_EMISSAO_DECLARACAO_PRESENCA  =$objEvConf->getEmitirDeclaracaoPresenca();
$OP_EMISSAO_INGRESSOS     = $objEvConf->getEmitirIngressos();

$OP_TIPO_FLUXO_INSCRICAO = $objEvConf->getTipoFluxoInscricao();
if ($OP_TIPO_FLUXO_INSCRICAO == '') {
    $OP_TIPO_FLUXO_INSCRICAO = 'PADRAO';
    // Valores: PADRAO | TICKETS
}

$OP_BLOQUEAR_ALTERACAO_SE_BOLETO_GERADO = false;
$OP_GERAR_BOLETO = $objEvConf->getOpGerarBoleto();
$OP_INCRICAO_GRATUITA = $objEvConf->getOpInscricaoGratuita();
$OP_EMISSAO_BOLETO = $objEvConf->getOpEmissaoBoleto();
$OP_EMISSAO_BOLETO_EM_MANUTENCAO = $objEvConf->getOpEmissaoBoletoManutencao();

$OP_EMISSAO_BOLETO_EXTRA = false;
$OP_MENSAGEM_BTN_BOLETO  = false;
$OP_SELECIONAR_ALMOCO    = $objEvConf->getAlmoco();
$OP_VALOR_ALMOCO         = $objEvConf->getAlmocoValor();
/**/
$fws_['op_selecionar_evento_paralelo'] = $objEvConf->getEventoParalelo();
$fws['op_selecionar_evento_paralelo_botao_personalizado'] =$objEvConf->getEventoParaleloBtnPersonalizado();
$OP_PERMITIR_ALTERAR_EVENTO_PARALELO   = $objEvConf->getEventoParaleloAlterar();
$OP_PERMITIR_ADICIONAR_EVENTO_PARALELO = $objEvConf->getEventoParaleloAdicionar();
$OP_PERMITIR_MAX_EVENTO_PARALELO        = $objEvConf->getEventoParaleloQtd();

$fws_['op_avaliar_ev_certificado']    = $objEvConf->getLiberarAvaliarEvento();

$fws_['op_selecionar_oficina']         = $objEvConf->getAtividade();
$fws_['op_selecionar_oficina_apos_inscricao'] = $objEvConf->getAtividadeAdicionar();
$fws_['op_excluir_oficina_apos_inscricao']    = $objEvConf->getAtividadeExcluir();
$fws_['op_atividade_valor_variavel']    = $objEvConf->getAtividadeValorVariavel();
$OP_PERMITIR_ALTERAR_OFICINA = $objEvConf->getAtividadeAlterar();
if($objEvConf->getAtividadeQtd()){
    define('MAX_OFICINAS', $objEvConf->getAtividadeQtd());
}else{
    define('MAX_OFICINAS', 0);
}

/**@todo acrescentar na tabela*/

//$OP_EDICAO_DADOS_PESSOAIS              = $objEvConf->getDadosPessoais();
$OP_EDICAO_DADOS_PESSOAIS_NOME         = $objEvConf->getDadosPessoaisNome();
$fws_['op_dados_pessoais_profissao']   = $objEvConf->getDadosPessoaisProfissao();
$fws_['op_dados_pessoais_complemento'] = $objEvConf->getDadosPessoaisComplemento();
$OP_NOME_CRACHA = $objEvConf->getOpNomeCracha();
$fws_['op_dados_pessoais_tipo_instituicao'] = $objEvConf->getDadosPessoaisTipoInstituicao();
$fws_['op_dados_pessoais_data_nascimento']  = $objEvConf->getDadosPessoaisDataNascimento();
$fws_['op_dados_pessoais_recibo'] = $objEvConf->getEmitirReciboTerceiro();

/*Valores*/
$OP_VALOR_OFICINA   = 0;
$OP_VALOR_INSCRICAO = 0;
/*Até quantos dias apos a data de inscrição é permitir alterar as atividades*/
$OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO = $objEvConf->getAtividadePermitirAlterarDias();
  
if('202207090730'<=$FWS_Data_HojeHM and $FWS_Data_HojeHM<='202207112359'){
    $OP_EVENTO_ONLINE_YOUTUBE_LIBERADO = true;
}
if($_SERVER['REMOTE_ADDR']=='179.154.183.222' or ('202207090730'<=$FWS_Data_HojeHM and $FWS_Data_HojeHM<='202207112359')){
    $OP_EVENTO_ONLINE_MOSTRA_LIBERADO = true;
}
//echo $FWS_Data_Inscricao_Inicio.' <= '.$FWS_Data_HojeHM.' and '.$FWS_Data_HojeHM.' <= '.$FWS_Data_Inscricao_Fim;
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_Hoje and $FWS_Data_Hoje <= $FWS_Data_Inscricao_Fim){
    $OP_PROCESSO_INSCRICAO = true;
}
if($FWS_Data_Ini_Submissao_Trabalho <= $FWS_Data_HojeHM and $FWS_Data_HojeHM <= $FWS_Data_Max_Submissao_Trabalho ){
    $OP_SUBMISSAO_TRABALHO = true;
}
if($FWS_Data_HojeHM < $FWS_Data_Ini_Submissao_Trabalho ){
    $OP_SUBMISSAO_TRABALHO_AGUARDANDO = true;
}
if($FWS_Data_Max_Submissao_Trabalho <= $FWS_Data_HojeHM){
    $OP_SUBMISSAO_TRABALHO_ENCERRADA = true;
}
//if ($_SESSION['cod_inscrito'] == '5016') {
//    $OP_SUBMISSAO_TRABALHO = true;
//}
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_HojeHM and $FWS_Data_HojeHM <= $FWS_Data_Trabalho_Tramitacao_Fim ){
    $OP_TRAMITACAO_TRABALHO = true;
}
if($FWS_Data_Divulgacao_Trabalho_Reprovado <= $FWS_Data_HojeHM){
    $OP_DIVULGAR_TRABALHO_REPROVADO = true;
}
if($FWS_Data_Divulgacao_Trabalho_Aprovado <= $FWS_Data_HojeHM){
    $OP_DIVULGAR_TRABALHO_APROVADO = true;
}
if($FWS_Data_Liberar_Boleto <= $FWS_Data_HojeHM and $FWS_Data_HojeHM <= $FWS_Data_Inscricao_Fim){
    $OP_GERAR_BOLETO = true;
}
$OP_EDICAO_DADOS_PESSOAIS = false;
if($FWS_Data_HojeHM <= $FWS_Data_Edicao_Dados_Pessoais){
    $OP_EDICAO_DADOS_PESSOAIS = true;
}
if($FWS_Data_Liberar_Certificado <= $FWS_Data_HojeHM and isDateStringValid($FWS_Data_Liberar_Certificado)){
    $OP_EMISSAO_CERTIFICADOS = true;
}
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_HojeHM){
    $OP_EMISSAO_RECIBOS = true;
}
if($FWS_Data_Inscricao_Inicio <= $FWS_Data_HojeHM){
    $OP_EMISSAO_CARTA_ACEITE = $objEvConf->getEmitirCartaAceite();
}
//if($FWS_Data_Inscricao_Inicio <= $FWS_Data_HojeHM){
//    $OP_EMISSAO_COMPROVANTE_INSCRICAO = true;
//}
//if($_SERVER['REMOTE_ADDR']=='10.10.105.3'){
//    $OP_EMISSAO_BOLETO = false;
//    $OP_EMISSAO_BOLETO_EM_MANUTENCAO = true;
//}
    
////if($arqConfig==false){
//    header('Location: https://'.$_SERVER['SERVER_NAME']);
//    exit;
//}

$ipLiberado = false;

if($_SERVER['REMOTE_ADDR']=='189.90.70.56' or $_SERVER['REMOTE_ADDR']=='177.100.49.198'){
    $ipLiberado = true;
    define('IP_LIBERADO',true);
}
if (!isset($_POST['content'])) {
    $_POST['content'] = null; // Ou atribua um valor padrão desejado
}
$content = addslashesopctec($_GET['content']);
If ($content == "") {
    $content = addslashesopctec($_POST['content']);
    If ($content == "") {
        $content = "default.php";
    }
}
