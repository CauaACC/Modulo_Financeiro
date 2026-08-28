<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/atuacoes-convidados-seguranca.php");

@$COD_ATUACAO_CONVIDADO = $_POST['COD_ATUACAO_CONVIDADO'];

#echo "isset-existe:   ".isset($_POST['ORDEM'              ])."<br>";
#echo "empty-não-exite:".empty($_POST['ORDEM'              ])."<br>";
#echo "isset-existe:   ".isset($_POST['ORDEMg'              ])."<br>";
#echo "empty-não-exite:".empty($_POST['ORDEMg'              ])."<br>";

#echo $_POST['ORDEM'              ]."<br>";

$ORDEM               = $_POST['ORDEM'             ];
$STATUS              = $_POST['STATUS'            ];
$TITULO              = $_POST['TITULO'            ];
$NOME_MINISTRANTE    = $_POST['NOME_MINISTRANTE'  ];
$E_MAIL_MINISTRANTE  = $_POST['E_MAIL_MINISTRANTE'];
$FONE_MINISTRANTE    = $_POST['FONE_MINISTRANTE'  ];
$LOCAL               = $_POST['LOCAL'             ];
$CARGA_HORARIA       = $_POST['CARGA_HORARIA'     ];
$NUMERO_VAGAS        = $_POST['NUMERO_VAGAS'      ];

$HORARIO_INI_HORA    = $_POST['HORARIO_INI_HORA'  ];
$HORARIO_INI_MINUTO  = $_POST['HORARIO_INI_MINUTO'];
$HORARIO_FIM_HORA    = $_POST['HORARIO_FIM_HORA'  ];
$HORARIO_FIM_MINUTO  = $_POST['HORARIO_FIM_MINUTO'];

$DATA_INI_DIA        = $_POST['DATA_INI_DIA'      ];
$DATA_INI_MES        = $_POST['DATA_INI_MES'      ];
$DATA_INI_ANO        = $_POST['DATA_INI_ANO'      ];

$DATA_FIM_DIA        = $_POST['DATA_FIM_DIA'      ];
$DATA_FIM_MES        = $_POST['DATA_FIM_MES'      ];
$DATA_FIM_ANO        = $_POST['DATA_FIM_ANO'      ];

$PALAVRAS_CHAVE      = $_POST['PALAVRAS_CHAVE'    ];
$PUBLICO_ALVO        = $_POST['PUBLICO_ALVO'      ];
$OBJETIVO            = $_POST['OBJETIVO'          ];
$PRE_REQUISITO       = $_POST['PRE_REQUISITO'     ];
$EMENTA              = $_POST['EMENTA'            ];
$DESCRICAO           = $_POST['DESCRICAO'         ];
$OBSERVACAO          = $_POST['OBSERVACAO'        ];


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
if($ORDEM==''){
    $ORDEM = 1;
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
  $_SESSION['MSG'] = $MSG;
  $CONTENT = "view/admin/atuacoes-convidados-cadastro-ok.php";
  $URL     = "index.php?content=".$CONTENT."&COD_ATUACAO_CONVIDADO=".$COD_ATUACAO_CONVIDADO;

}Else{

     $consql  = "
                 Insert Into tab_opcoes_atuacoes_convidados (".$Var_Campos.")
                 Values (".$Var_Sql.")
                ";

//  echo $consql."<hr>";

  If (!$Conexao_db->Execute($consql)) { //Executando o Insert

    $MSG     = "Erro-de-Execucao-ERRO-SQL";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/erro.php";
    $URL = "index.php?content=".$CONTENT."&COD_ATUACAO_CONVIDADO=".$COD_ATUACAO_CONVIDADO;

  }Else{

    $consql = "SELECT cod_evento, cod_atuacao_convidado "
            . "FROM tab_opcoes_atuacoes_convidados "
            . "WHERE (cod_evento=".COD_EVENTO.")"
            . "and(titulo='".$TITULO."')and(E_MAIL_MINISTRANTE='".$E_MAIL_MINISTRANTE."')"
            . " ORDER BY cod_atuacao_convidado DESC";

    $RS = $Conexao_db->Execute($consql);
    $COD_ATUACAO_CONVIDADO = $RS->fields[1];

    $MSG     = "Operação-Executada-com-Sucesso";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/atuacoes-convidados-cadastro-ok.php";
    $URL = "index.php?content=".$CONTENT."&COD_ATUACAO_CONVIDADO=".$COD_ATUACAO_CONVIDADO;

  }
}

FWA_Mostrar_Mensagem($MSG);

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=".$URL."\"></HTML>";
?>