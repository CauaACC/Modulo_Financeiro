<?php
require_once(dirname(__FILE__)."/../../include/config.php");

@$COD_PREMIACAO_CIENTIFICA = $_POST['COD_PREMIACAO_CIENTIFICA'];

#echo "isset-existe:   ".isset($_POST['ORDEM'              ])."<br>";
#echo "empty-não-exite:".empty($_POST['ORDEM'              ])."<br>";
#echo "isset-existe:   ".isset($_POST['ORDEMg'              ])."<br>";
#echo "empty-não-exite:".empty($_POST['ORDEMg'              ])."<br>";

#echo $_POST['ORDEM'              ]."<br>";

If (!empty($_POST['ORDEM'              ])){ $ORDEM               = $_POST['ORDEM'             ]; }Else{ $ORDEM               = NULL  };
If (!empty($_POST['STATUS'             ])){ $STATUS              = $_POST['STATUS'            ]; }Else{ $STATUS              = NULL  };
If (!empty($_POST['TITULO'             ])){ $TITULO              = $_POST['TITULO'            ]; }Else{ $TITULO              = NULL  };
If (!empty($_POST['NOME_MINISTRANTE'   ])){ $NOME_MINISTRANTE    = $_POST['NOME_MINISTRANTE'  ]; }Else{ $NOME_MINISTRANTE    = NULL  };
If (!empty($_POST['E_MAIL_MINISTRANTE' ])){ $E_MAIL_MINISTRANTE  = $_POST['E_MAIL_MINISTRANTE']; }Else{ $E_MAIL_MINISTRANTE  = NULL  };
If (!empty($_POST['FONE_MINISTRANTE'   ])){ $FONE_MINISTRANTE    = $_POST['FONE_MINISTRANTE'  ]; }Else{ $FONE_MINISTRANTE    = NULL  };
If (!empty($_POST['LOCAL'              ])){ $LOCAL               = $_POST['LOCAL'             ]; }Else{ $LOCAL               = NULL  };
If (!empty($_POST['CARGA_HORARIA'      ])){ $CARGA_HORARIA       = $_POST['CARGA_HORARIA'     ]; }Else{ $CARGA_HORARIA       = NULL  };
If (!empty($_POST['NUMERO_VAGAS'       ])){ $NUMERO_VAGAS        = $_POST['NUMERO_VAGAS'      ]; }Else{ $NUMERO_VAGAS        = NULL  };

If (!empty($_POST['HORARIO_INI_HORA'   ])){ $HORARIO_INI_HORA    = $_POST['HORARIO_INI_HORA'  ]; }Else{ $HORARIO_INI_HORA    = NULL  };
If (!empty($_POST['HORARIO_INI_MINUTO' ])){ $HORARIO_INI_MINUTO  = $_POST['HORARIO_INI_MINUTO']; }Else{ $HORARIO_INI_MINUTO  = NULL  };
If (!empty($_POST['HORARIO_FIM_HORA'   ])){ $HORARIO_FIM_HORA    = $_POST['HORARIO_FIM_HORA'  ]; }Else{ $HORARIO_FIM_HORA    = NULL  };
If (!empty($_POST['HORARIO_FIM_MINUTO' ])){ $HORARIO_FIM_MINUTO  = $_POST['HORARIO_FIM_MINUTO']; }Else{ $HORARIO_FIM_MINUTO  = NULL  };
    
If (!empty($_POST['DATA_INI_DIA'       ])){ $DATA_INI_DIA        = $_POST['DATA_INI_DIA'      ]; }Else{ $DATA_INI_DIA        = NULL  };
If (!empty($_POST['DATA_INI_MES'       ])){ $DATA_INI_MES        = $_POST['DATA_INI_MES'      ]; }Else{ $DATA_INI_MES        = NULL  };
If (!empty($_POST['DATA_INI_ANO'       ])){ $DATA_INI_ANO        = $_POST['DATA_INI_ANO'      ]; }Else{ $DATA_INI_ANO        = NULL  };
    
If (!empty($_POST['DATA_FIM_DIA'       ])){ $DATA_FIM_DIA        = $_POST['DATA_FIM_DIA'      ]; }Else{ $DATA_FIM_DIA        = NULL  };
If (!empty($_POST['DATA_FIM_MES'       ])){ $DATA_FIM_MES        = $_POST['DATA_FIM_MES'      ]; }Else{ $DATA_FIM_MES        = NULL  };
If (!empty($_POST['DATA_FIM_ANO'       ])){ $DATA_FIM_ANO        = $_POST['DATA_FIM_ANO'      ]; }Else{ $DATA_FIM_ANO        = NULL  };

If (!empty($_POST['PALAVRAS_CHAVE'     ])){ $PALAVRAS_CHAVE      = $_POST['PALAVRAS_CHAVE'    ]; }Else{ $PALAVRAS_CHAVE      = NULL  };
If (!empty($_POST['PUBLICO_ALVO'       ])){ $PUBLICO_ALVO        = $_POST['PUBLICO_ALVO'      ]; }Else{ $PUBLICO_ALVO        = NULL  };
If (!empty($_POST['OBJETIVO'           ])){ $OBJETIVO            = $_POST['OBJETIVO'          ]; }Else{ $OBJETIVO            = NULL  };
If (!empty($_POST['PRE_REQUISITO'      ])){ $PRE_REQUISITO       = $_POST['PRE_REQUISITO'     ]; }Else{ $PRE_REQUISITO       = NULL  };
If (!empty($_POST['EMENTA'             ])){ $EMENTA              = $_POST['EMENTA'            ]; }Else{ $EMENTA              = NULL  };
If (!empty($_POST['DESCRICAO'          ])){ $DESCRICAO           = $_POST['DESCRICAO'         ]; }Else{ $DESCRICAO           = NULL  };
If (!empty($_POST['OBSERVACAO'         ])){ $OBSERVACAO          = $_POST['OBSERVACAO'        ]; }Else{ $OBSERVACAO          = NULL  };

/*
$TITULO             = addslashesopctec($TITULO            );
$NOME_MINISTRANTE   = addslashesopctec($NOME_MINISTRANTE  );
$E_MAIL_MINISTRANTE = addslashesopctec($E_MAIL_MINISTRANTE );
$FONE_MINISTRANTE   = addslashesopctec($FONE_MINISTRANTE  );
$LOCAL              = addslashesopctec($LOCAL             );
$CARGA_HORARIA      = addslashesopctec($CARGA_HORARIA     );
$NUMERO_VAGAS       = addslashesopctec($NUMERO_VAGAS      );
$PALAVRAS_CHAVE     = addslashesopctec($PALAVRAS_CHAVE    );
$PUBLICO_ALVO       = addslashesopctec($PUBLICO_ALVO      );
$OBJETIVO           = addslashesopctec($OBJETIVO          );
$PRE_REQUISITO      = addslashesopctec($PRE_REQUISITO     );
$EMENTA             = addslashesopctec($EMENTA            );
$DESCRICAO          = addslashesopctec($DESCRICAO         );
$OBSERVACAO         = addslashesopctec($OBSERVACAO        );
*/

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


//I: ---------------------------------------
$Var_Sql = ""
.""
.COD_EVENTO        ." ,"
.$ORDEM             ." ,'"
.$STATUS            ."','"
.$TITULO            ."','"
.$NOME_MINISTRANTE  ."','"
.$E_MAIL_MINISTRANTE."','"
.$FONE_MINISTRANTE  ."','"
.$LOCAL             ."','"
.$CARGA_HORARIA     ."','"
.$NUMERO_VAGAS      ."','"
.$HORARIO_INI       ."','"
.$HORARIO_FIM       ."','"
.$DATA_INI          ."','"
.$DATA_FIM          ."','"
.$PALAVRAS_CHAVE    ."','"
.$PUBLICO_ALVO      ."','"
.$OBJETIVO          ."','"
.$PRE_REQUISITO     ."','"
.$EMENTA            ."','"
.$DESCRICAO         ."','"
.$OBSERVACAO        ."','"
.$_SERVER["HTTP_USER_AGENT"]."'"
."";
//-------------------------------------------

//I: ---------------------------------------
$Var_Campos = ""
."cod_evento        ,"
."ordem             ,"
."status            ,"
."titulo            ,"
."nome_ministrante  ,"
."e_mail_ministrante,"
."fone_ministrante  ,"
."local             ,"
."carga_horaria     ,"
."numero_vagas      ,"
."horario_ini       ,"
."horario_fim       ,"
."data_ini          ,"
."data_fim          ,"
."palavras_chave    ,"
."publico_alvo      ,"
."objetivo          ,"
."pre_requisito     ,"
."ementa            ,"
."descricao         ,"
."observacao        ,"
."http_user_agent    "
."";

#-------------------------------------------
#echo $Var_Sql."<br>".$Var_Campos."<br>";

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG']=$MSG;
  $CONTENT = "view/admin/premiacoes-cientifica-cadastro-ok.php";
  $URL     = "index.php?content=".$CONTENT."&COD_PREMIACAO_CIENTIFICA=".$COD_PREMIACAO_CIENTIFICA;

}Else{

     $consql  = "
                 Insert Into tab_opcoes_premiacoes_cientifica (".$Var_Campos.")
                 Values (".$Var_Sql.")
                ";

  echo $consql;

  If (!$Conexao_db->Execute($consql)) { //Executando o Insert

    $MSG     = "Erro-de-Execucao-ERRO-SQL";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/erro.php";
    $URL = "index.php?content=".$CONTENT."&COD_PREMIACAO_CIENTIFICA=".$COD_PREMIACAO_CIENTIFICA;

  }Else{

    $consql = "SELECT cod_evento, cod_premiacao_cientifica FROM tab_opcoes_premiacoes_cientifica WHERE (cod_evento=".COD_EVENTO.")and(titulo='".$TITULO."')and(E_MAIL_MINISTRANTE='".$E_MAIL_MINISTRANTE."') ORDER BY cod_premiacao_cientifica DESC";

    $RS = $Conexao_db->Execute($consql);
    $COD_PREMIACAO_CIENTIFICA = $RS->fields[1];

    $MSG     = "Operação-Executada-com-Sucesso";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/premiacoes-cientifica-cadastro-ok.php";
    $URL = "index.php?content=".$CONTENT."&COD_PREMIACAO_CIENTIFICA=".$COD_PREMIACAO_CIENTIFICA;

  }
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
#echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=".$URL."\"></HTML>";
