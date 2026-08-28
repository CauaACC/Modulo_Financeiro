<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/login-seguranca.php");
$debug=false;
#$acao     = "INSERIR";
$fws_Data_Ini  = "200604010001"; #aaaammddhhii
$fws_Data_Fim  = "200606232359"; #aaaammddhhii
$fws_Data_Hoje = Date('YmdHi');
$FWA_Execucao = false;
$MSG = "";

@$fws_['data_alteracao']  = Date("YmdHi");
@$fws_['data_inclusao']   = Date("YmdHi");
@$fws_['status']          = "ATIVADO";
@$fws_['session_id']      = session_id();
@$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

#-----------------------------------
try {
    $objUE = new UsuarioEvento();
    $objLogin = new Usuario();
    $objLogin->setLogin($fws_['login']);
    $objLogin->setSenha($fws_['senha']);
    $objLogin->setNome($fws_['nome']);
    $objLogin->getUsuarioByLogin($debug);
    if ($objLogin->getCodUsuario()==''){
        echo $debug? 'Login não existe-' : '';
        $objLogin->salvar($debug);
    }else{
        echo $debug? 'Login existe-('.$objLogin->getCodUsuario().')' : '';
        throw new OKException('Login já cadastrado');
    }
} catch (OKException $exc) {
    echo $debug? 'verificar associacao-' : '';
    /*veriricar se usuario associado a evento*/
    $objUE = new UsuarioEvento();
    $objUE->setCodEvento(COD_EVENTO);
    $objUE->setCodUsuario($objLogin->getCodUsuario());
    $objUE->getUsuarioEventoById($debug);
    if($objUE->getStatus()=='ATIVADA'){
        /*já esta associado*/
        echo $debug? 'já esta associado-' : '';
        $MSG .= 'Login '.$objLogin->getLogin().' já esta associado ao evento-';
        @$FWA_Execucao = true;
    }else{
        echo $debug? 'não esta associado-' : '';
        try {
            echo $debug? 'adicionando ao evento-' : '';
            $objUE->adicionarUsuarioEvento($debug);
        } catch (OKException $exc) {
            echo $debug? 'adicionado '.$objLogin->getLogin().'-' : '';
        }
    }
    try {
        /*criando ou associando a convidado*/
        $MSG .= $exc->getMessage();
        $objConv = new Convidado();
        $objConv->setEMail($objLogin->getLogin());
        /**@todo aqui é preciso na verdade verificar se o cod_usuario tem ligação com a tabela convidado (dados pessoais) se não tiver criar um convidado;
         * também precisaria desativar os cadastros
         * é bom que todos os convidados encontrados com e-mail sejam atualizados para o mesmo usuario ou desativados para inserir um novo
         */
        // if($objConv->getCodConvidado()!=''){
        /**Exite um convidado com e-mail do login */
        if($objConv->getConvidadoByEmail($debug)){
            echo $debug? 'Email '.$objLogin->getLogin().' já cadastrado como convidado - ' : '';

            if($objConv->getCodUsuario()=='' or $objConv->getCodUsuario()=='0'){
                echo $debug? 'atualizando dados pessoias '.$objLogin->getLogin().'-' : '';
                $objConv->setCodUsuario($objLogin->getCodUsuario());
                $objConv->salvar($debug);
            }
            if($objConv->getCodUsuario()==$objLogin->getCodUsuario()){
                echo $debug? 'já login já possui dados pessoias '.$objLogin->getLogin().'-' : '';
                 throw new OKException('Email '.$objLogin->getLogin().' já cadastrado como convidado');
            }else{
                throw new OKException('Email '.$objLogin->getLogin().' já criado mas não associado');
            }
        }else{
            echo $debug? 'insere dados pessoias '.$objLogin->getLogin().'-' : '';
            $objConv->setNomeConvidado($objLogin->getNome());
            $objConv->setCodUsuario($objLogin->getCodUsuario());
            $objConv->salvar($debug);
        }
    } catch (OKException $exc) {
        @$FWA_Execucao = true;
        $MSG .= $exc->getMessage();
    } catch (Exception $exc) {
        $MSG .=$exc->getMessage();
        $FWA_Execucao = false;
    }
} catch (Exception $exc) {
    $MSG .=$exc->getMessage();
    $FWA_Execucao = false;
}
if($FWA_Execucao==true){
    /*Enviar email com a senha*/
    if ($fws_['enviar_aviso'] =='ON' and $objLogin->getCodConvidado()!=''){
        $objEmail = new GerenciarEmailAC();
        $fws_['cod_convidado'] = $objConv->getCodConvidado();
        $objEmail->enviarEmailLoginConvidado($fws_, $debug);
    }
}
//if($objUE->getStatus()=='ATIVADA'){
/*@todo se o login existe e esta cadastrado manda para listagem de grupos*/
$_SESSION['MSG']=$MSG;
If(($FWA_Execucao==true) or ($objUE->getStatus()=='ATIVADA')){
//   $fws_URL = WB_URL_VIEW."?content=view/admin/convidados-cadastro-ok-listagem-grupos.php&COD_CONVIDADO=".$objConv->getCodConvidado();
  $fws_URL = WB_URL_VIEW."?content=view/admin/convidados-cadastro-ok.php&COD_CONVIDADO=".$objConv->getCodConvidado();
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
}Else{
  $fws_URL = WB_URL_VIEW."?content=view/admin/login-cadastro-incluir.php&COD_USUARIO=".$objLogin->getCodUsuario();
  echo "<br>Mensagens:<BR>";
  echo $MSG;
}

If($debug==1){
echo "<br><a href='{$fws_URL}'>{$fws_URL}</a><br>";
}Else{
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$fws_URL."\">";
}