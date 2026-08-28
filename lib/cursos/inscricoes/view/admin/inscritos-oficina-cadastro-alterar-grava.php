<?php
// Segura a saida ate o fim: sem isso, uma quebra de linha de qualquer require ja
// envia o cabecalho e redirecionar() nao consegue mandar o 303.
ob_start();
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$debug = false;

#-------------------------------------------
@$fws_['data_alteracao']  = Date("YmdHi");
@$fws_['data_inclusao']   = Date("YmdHi");
@$fws_['status']         = "ATIVADA";
@$fws_['session_id']      = $_SESSION['_cod_usuario'];
@$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

#echo $acao."-<BR>";
#-------------------------------------------

$fws_Execucao = false;

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro--Não foi possível conectar ao banco de dados.";
  $CONTENT = "view/admin/inscritos-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];

}Else{

  // A participacao a alterar e' a que estava na tela: atividade E relacao
  // ANTERIORES. Usar os valores novos aqui fazia o backup copiar outra linha e
  // o UPDATE nao achar nenhuma quando o usuario trocava o tipo de relacao.
  @$cod_oficina_anterior  = $fws_['cod_oficina_anterior'];
  @$tipo_relacao_anterior = isset($fws_['tipo_relacao_anterior']) && $fws_['tipo_relacao_anterior']!==''
                          ? $fws_['tipo_relacao_anterior']
                          : $fws_['tipo_relacao'];

  $whereRelacaoAnterior = ($tipo_relacao_anterior===null || $tipo_relacao_anterior==='')
                        ? " and (tipo_relacao IS NULL or tipo_relacao='') "
                        : " and (tipo_relacao='".$tipo_relacao_anterior."') ";

  $whereParticipacao = " (cod_oficina=".(int)$cod_oficina_anterior.")"
                     . " and (cod_inscrito=".(int)$fws_['cod_inscrito'].")"
                     . " and (cod_evento=".(int)$fws_['cod_evento'].")"
                     . $whereRelacaoAnterior;

  // Atividade + relacao formam a chave do registro. Mudar para uma combinacao
  // que o inscrito ja tem estouraria a chave primaria com erro cru de SQL --
  // melhor dizer o que houve.
  $mudouChave = ((int)$fws_['cod_oficina'] != (int)$cod_oficina_anterior)
             || ($fws_['tipo_relacao'] !== $tipo_relacao_anterior);
  $jaExiste = false;
  if($mudouChave){
      $sqlDuplicado = "SELECT cod_inscrito FROM tab_lig_inscritos_oficinas
                        WHERE cod_evento=".(int)$fws_['cod_evento']."
                          AND cod_inscrito=".(int)$fws_['cod_inscrito']."
                          AND cod_oficina=".(int)$fws_['cod_oficina']."
                          AND tipo_relacao='".$fws_['tipo_relacao']."' LIMIT 1";
      $RS_Dup = FWA_Executa_SQL($debug,$Conexao_db,$sqlDuplicado);
      $jaExiste = ($RS_Dup && !$RS_Dup->EOF);
  }

  $var_sql = "
               INSERT INTO tab_lig_inscritos_oficinas_atualizados
               SELECT *
               FROM tab_lig_inscritos_oficinas
               WHERE ".$whereParticipacao."
             ";

  If($jaExiste){
    $fws_Execucao=false;
    // A ficha do inscrito imprime a MSG trocando so '--' por quebra de linha,
    // entao a frase vai com espaco normal -- hifen no meio da palavra e' legado.
    $MSG     = "Erro--Este inscrito já tem esta atividade com esta mesma relação. Nada foi alterado.";
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
  }ElseIf($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
    $fws_Execucao = true;
  }Else{
    $fws_Execucao=false;
    $MSG     = "Erro--Falha ao gravar a cópia de segurança da participação. Nada foi alterado.";
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
  }
  
  If($fws_Execucao==true){
    $var_sql = "
              UPDATE tab_lig_inscritos_oficinas SET
                cod_oficina      =".$fws_['cod_oficina'    ].",
                tipo_relacao     ='".$fws_['tipo_relacao'    ]."',
                flg_presenca     ='".$fws_['flg_presenca'   ]."',
                carga_horaria     ='".$fws_['carga_horaria'   ]."',
                carga_horaria_pratica     ='".$fws_['carga_horaria_pratica'   ]."',
                carga_horaria_teorica     ='".$fws_['carga_horaria_teorica'   ]."',
                data_alteracao   ='".$fws_['data_alteracao' ]."',
                status           ='".$fws_['status'         ]."',
                session_id       ='".$fws_['session_id'     ]."',
                http_user_agent  ='".$fws_['http_user_agent']."',
                ip_cliente       ='".$fws_['ip_cliente'     ]."',
                frequencia       ='".addslashesopctec($fws_['frequencia'     ])."'
              WHERE ".$whereParticipacao." ";

    If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
      $fws_Execucao=true;
      $MSG     = "Operação executada com sucesso--Participação atualizada.";
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_oficina=".$fws_['cod_oficina']."&cod_inscrito=".$fws_['cod_inscrito'];
    }Else{
      $fws_Execucao=false;
      $MSG     = "Erro--Falha ao salvar a alteração da participação.";
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
    }
  }

}
#--------------------------------------------------------------
If($debug==true){
  $_SESSION['MSG'] = $MSG;
  echo preg_replace('/--/','<br>',$MSG);
  echo "<br><a href='{$URL}'>Continuar</a><br>";
}Else{
  // Volta direto para a ficha do inscrito (303), sem tela de espera nem o
  // endereco impresso — a mensagem chega pela sessao.
  redirecionar($URL, $MSG, ($fws_Execucao==true ? 'sucesso' : 'erro'));
}
