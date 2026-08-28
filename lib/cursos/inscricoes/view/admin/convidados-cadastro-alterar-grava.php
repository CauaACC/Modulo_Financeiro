<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/convidados-seguranca.php");

@$COD_CONVIDADO = $_POST['COD_CONVIDADO'];
@$COD_ATUACAO_CONVIDADO = $_POST['COD_ATUACAO_CONVIDADO'];

$FWS_Debug=false;

@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
@$FWS_['status']          = "ATIVADO";
@$FWS_['session_id']      = $_SESSION['_cod_usuario'];
@$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

If (!empty($_POST['nome_convidado'   ])){ $NOME_CONVIDADO   = $_POST['nome_convidado'   ]; }Else{ $NOME_CONVIDADO   = NULL; };
If (!empty($_POST['cpf'              ])){ $CPF              = $_POST['cpf'              ]; }Else{ $CPF              = NULL; };
If (!empty($_POST['rg'               ])){ $RG               = $_POST['rg'               ]; }Else{ $RG               = NULL; };
If (!empty($_POST['e_mail'           ])){ $E_MAIL           = $_POST['e_mail'           ]; }Else{ $E_MAIL           = NULL; };
If (!empty($_POST['data_nascimento'  ])){ $DATA_NASCIMENTO  = $_POST['data_nascimento'  ]; }Else{ $DATA_NASCIMENTO  = NULL; };
If (!empty($_POST['sexo'             ])){ $SEXO             = $_POST['sexo'             ]; }Else{ $SEXO             = NULL; };
If (!empty($_POST['profissao_atuacao'])){ $PROFISSAO_ATUACAO= $_POST['profissao_atuacao']; }Else{ $PROFISSAO_ATUACAO= NULL; };
If (!empty($_POST['grau_escolaridade'])){ $GRAU_ESCOLARIDADE= $_POST['grau_escolaridade']; }Else{ $GRAU_ESCOLARIDADE= NULL; };
If (!empty($_POST['instituicao'      ])){ $INSTITUICAO      = $_POST['instituicao'      ]; }Else{ $INSTITUICAO      = NULL; };
If (!empty($_POST['endereco'         ])){ $ENDERECO         = $_POST['endereco'         ]; }Else{ $ENDERECO         = NULL; };
If (!empty($_POST['numero'           ])){ $NUMERO           = $_POST['numero'           ]; }Else{ $NUMERO           = NULL; };
If (!empty($_POST['complemento'      ])){ $COMPLEMENTO      = $_POST['complemento'      ]; }Else{ $COMPLEMENTO      = NULL; };
If (!empty($_POST['bairro'           ])){ $BAIRRO           = $_POST['bairro'           ]; }Else{ $BAIRRO           = NULL; };
If (!empty($_POST['cep'              ])){ $CEP              = $_POST['cep'              ]; }Else{ $CEP              = NULL; };
If (!empty($_POST['cidade'           ])){ $CIDADE           = $_POST['cidade'           ]; }Else{ $CIDADE           = NULL; };
If (!empty($_POST['estado'           ])){ $ESTADO           = $_POST['estado'           ]; }Else{ $ESTADO           = NULL; };
If (!empty($_POST['uf'               ])){ $UF               = $_POST['uf'               ]; }Else{ $UF               = NULL; };
If (!empty($_POST['fone_residencial' ])){ $FONE_RESIDENCIAL = $_POST['fone_residencial' ]; }Else{ $FONE_RESIDENCIAL = NULL; };
If (!empty($_POST['fone_comercial'   ])){ $FONE_COMERCIAL   = $_POST['fone_comercial'   ]; }Else{ $FONE_COMERCIAL   = NULL; };
If (!empty($_POST['fone_celular'     ])){ $FONE_CELULAR     = $_POST['fone_celular'     ]; }Else{ $FONE_CELULAR     = NULL; };


/*
$DATA_INI = $DATA_INI_DIA."/".$DATA_INI_MES."/".$DATA_INI_ANO;
$DATA_FIM = $DATA_FIM_DIA."/".$DATA_FIM_MES."/".$DATA_FIM_ANO;

$HORARIO_INI = $HORARIO_INI_HORA.":".$HORARIO_INI_MINUTO;
$HORARIO_FIM = $HORARIO_FIM_HORA.":".$HORARIO_FIM_MINUTO;

If (Strlen($DATA_INI)<10){
  $DATA_INI = " ";
}

If (Strlen($DATA_FIM)<10){
  $DATA_FIM = " ";
}

If (Strlen($HORARIO_INI)<5){
  $HORARIO_INI = " ";
}

If (Strlen($HORARIO_FIM)<5){
  $HORARIO_FIM = " ";
}
*/

#-------------------------------------------


#echo $Var_Sql."<br>".$Var_Campos."<br>";

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG'] = $MSG;
  $CONTENT = "view/admin/convidados-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&COD_CONVIDADO=".$COD_CONVIDADO;

}Else{

  $consql  = "
              UPDATE tab_convidados SET
                cod_evento       = ".COD_EVENTO       ." ,
                cod_atuacao_convidado = ".$COD_ATUACAO_CONVIDADO." ,
                nome_convidado   ='".$NOME_CONVIDADO   ."',
                cpf              ='".$CPF              ."',
                rg               ='".$RG               ."',
                e_mail           ='".$E_MAIL           ."',
                data_nascimento  ='".$DATA_NASCIMENTO  ."',
                sexo             ='".$SEXO             ."',
                profissao_atuacao='".$PROFISSAO_ATUACAO."',
                grau_escolaridade='".$GRAU_ESCOLARIDADE."',
                instituicao      ='".$INSTITUICAO      ."',
                endereco         ='".$ENDERECO         ."',
                numero           ='".$NUMERO           ."',
                complemento      ='".$COMPLEMENTO      ."',
                bairro           ='".$BAIRRO           ."',
                cep              ='".$CEP              ."',
                cidade           ='".$CIDADE           ."',
                estado           ='".$ESTADO           ."',
                uf               ='".$UF               ."',
                fone_residencial ='".$FONE_RESIDENCIAL ."',
                fone_comercial   ='".$FONE_COMERCIAL   ."',
                fone_celular     ='".$FONE_CELULAR     ."',
                data_alteracao   ='".$FWS_['data_alteracao']  ."',
                status           ='".$FWS_['status']         ."',
                session_id       ='".$FWS_['session_id']     ."',
                http_user_agent  ='".$FWS_['http_user_agent']."',
                ip_cliente       ='".$FWS_['ip_cliente']     ."'
              WHERE (cod_convidado=".$COD_CONVIDADO.")and(cod_evento =".COD_EVENTO.")
             ";

//  echo $consql."<hr>";

  If (!$Conexao_db->Execute($consql)) { //Executando o Insert

    $MSG     = "Erro-de-Execucao-ERRO-SQL";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/convidados-cadastro-ok.php";
    $URL = "index.php?content=".$CONTENT."&COD_CONVIDADO=".$COD_CONVIDADO;

  }Else{

    $MSG     = "Operação-Executada-com-Sucesso";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/convidados-cadastro-ok.php";
    $URL = "index.php?content=".$CONTENT."&COD_CONVIDADO=".$COD_CONVIDADO;

  }
}
$loc   = "-";
$subst = " ";
echo preg_replace($loc,$subst,$MSG)." ,aguarde um momento - ".date("d/m/Y - H:i:s");

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=".$URL."\"></HTML>";
