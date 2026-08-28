<?php
@$cod_premiacao_cientifica = $_SESSION['cod_premiacao_cientifica'];
If(@$cod_premiacao_cientifica != ""){
  #-----------------------------------------------------------------------------------------
  $var_sql = "
              SELECT cod_premiacao_cientifica, titulo, numero_vagas
              FROM tab_opcoes_premiacoes_cientifica
              WHERE (status            = 'ATIVADA')
                AND (cod_premiacao_cientifica = $cod_premiacao_cientifica)
                AND (cod_evento        = COD_EVENTO)
             ";

  #echo $var_sql;

  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);

  #Verificando se houve retorno de algum registro
  If (!$RS->EOF) {

    While (!$RS->EOF) {

      $cod_premiacao_cientifica     = $RS->fields[0];
      $titulo                = $RS->fields[1];
      $numero_vagas          = $RS->fields[2];

      $vagas_net_disponiveis = $numero_vagas;

      #echo $cod_premiacao_cientifica."<br>";
      #echo $titulo."<br>";
      #echo $numero_vagas."<br>";
      #echo $vagas_net_disponiveis."<br>";


      #Definindo tabela com resultado
      echo "
           <table cellspacing='0' cellpadding='0' width='400' border=0>
           <tr>
              <td>";

              If($vagas_net_disponiveis<=0){
                $vagas_net_disponiveis = "<b>Inscrições Encerradas</b>";
              }Else{
                echo "<INPUT TYPE='hidden'  NAME='cod_premiacao_cientifica' VALUE='".$cod_premiacao_cientifica."'>";
              }

      echo "
              </td>
              <td width='380'>
                Premiação Científica Selecionada: <br><b>".$titulo."</b><br>
                <!--<b>".$numero_vagas."</b><br>-->
              </td>
            </tr>
            <!--
            <tr>
              <td>&nbsp;</td>
              <td>".$vagas_net_disponiveis." - Vagas Disponíveis <br>&nbsp; </td>
           </tr>
           -->
           </table><br>
           ";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }
    $Selecionou_Curso = 1;
    if($_SESSION['content']!="view/inscricao/inscricao_monografia_confirmar.php"){
    echo "<a href='index.php?content=view/inscricao/inscricao_monografia_seleciona_premiacao_cientifica.php' style='TEXT-DECORATION: none'  ><!--img src='img/back.gif'  border=0--> Para alterar a opção de Premiação Científica selecionada, clique aqui.</a><br><br>";
    }
  }Else{
    #Se não comando SQL não retornou nenhum registro imprime mensagens de aviso
    #Mensagem de aviso q não comando SQL não retornou nenhum registro.
    echo "O sistema não encontrou Premiações Científicas, Tente novamente.";
  }
}Else{
  $Selecionou_Curso = 0;

  #echo "<b>Como nenhuma Premiação Científica foi selecionada,<br> entende-se que você não deseja Apresentar Trabaho.</b><br><br>";

  if($_SESSION['content']!="view/inscricao/inscricao_monografia_confirmar.php"){
  echo "<a href='index.php?content=view/inscricao/inscricao_monografia_seleciona_premiacao_cientifica.php' style='TEXT-DECORATION: none'  ><!--img src='img/back.gif'  border=0--> Para selecionar uma Premiação Científica, clique aqui.</a><br><br><br>";
  }

}
?>