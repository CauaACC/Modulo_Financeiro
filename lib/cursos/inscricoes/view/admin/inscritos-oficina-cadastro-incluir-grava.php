<?php
// Segura a saida ate o fim para redirecionar() poder mandar o 303.
ob_start();
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$debug = false;
#-------------------------------------------
@$fws_['cod_evento']      = COD_EVENTO;
@$fws_['data_alteracao']  = Date("YmdHi");
@$fws_['data_inclusao']   = Date("YmdHi");
@$fws_['status']          = "ATIVADA";
@$fws_['session_id']      = $_SESSION['_cod_usuario'];
@$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

#echo $acao."-<BR>";
#-------------------------------------------

$Var_Sql = ""
.""
.$fws_['cod_evento'     ].","
.$fws_['cod_inscrito'   ].","
.$fws_['cod_oficina'    ].",'"
.$fws_['tipo_relacao']."','"
.$fws_['flg_presenca']."','"
.$fws_['carga_horaria']."','"
.$fws_['carga_horaria_pratica']."','"
.$fws_['carga_horaria_teorica']."','"
.$fws_['data_inclusao'  ]."','"
.$fws_['status'         ]."','"
.$fws_['session_id'     ]."','"
.$fws_['http_user_agent']."','"
.$fws_['ip_cliente'     ]."','"
.$fws_['frequencia'     ]."'"
."";
//-------------------------------------------

//I: ---------------------------------------
$Var_Campos = ""
."cod_evento    ,"
."cod_inscrito  ,"
."cod_oficina   ,"
."tipo_relacao, "
."flg_presenca, "
."carga_horaria, "
."carga_horaria_pratica, "
."carga_horaria_teorica, "
."data_inscricao,"
."status        ,"
."session_id    ,"
."http_user_agent ,"
."ip_cliente    ,"
."frequencia     "
."";

#-------------------------------------------
#echo $Var_Sql."<br>".$Var_Campos."<br>";

$fws_Execucao = false;

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro--Não foi possível conectar ao banco de dados.";
  $CONTENT = "view/admin/inscritos-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];

}Else{

  $var_sql= "
             Insert Into tab_lig_inscritos_oficinas (".$Var_Campos.")
             Values (".$Var_Sql.")
            ";

  If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
      $fws_Execucao = true;
      $MSG     = "Operação executada com sucesso--Inscrito incluído na atividade.";
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
  }Else{
    $MSG     = "Erro--Não foi possível incluir o inscrito na atividade.--Verifique se ele já participa dela com esta mesma relação.";
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
  }
}
#--------------------------------------------------------------
If($debug==true){
  $_SESSION['MSG'] = $MSG;
  echo preg_replace('/--/','<br>',$MSG);
  echo "<br><a href='{$URL}'>Continuar</a><br>";
}Else{
  // Volta para a ficha do inscrito por redirecionamento (303), sem tela de
  // espera e sem o endereco impresso.
  redirecionar($URL, $MSG, ($fws_Execucao==true ? 'sucesso' : 'erro'));
}
