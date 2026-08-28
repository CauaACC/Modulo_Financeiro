<?php
If(@$fws_['cod_area_tematica'] != ""){
  #-----------------------------------------------------------------------------------------
  $var_sql = "
              SELECT cod_area_tematica, titulo, numero_vagas
              FROM tab_opcoes_areas_tematicas
              WHERE (cod_evento        = ".COD_EVENTO." )
                and (status  = 'ATIVADO')
                and (periodo_inscricao_data_ini<=".$FWS_Data_Hoje.")
                and (".$FWS_Data_Hoje."<=periodo_inscricao_data_fim)
                and (cod_area_tematica = ".$fws_['cod_area_tematica'].")"
          . "";

  #echo $var_sql;

  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);

  #Verificando se houve retorno de algum registro
  If (!$RS->EOF) {

    While (!$RS->EOF) {

      $fws_['cod_area_tematica']     = $RS->fields[0];
      $titulo                = $RS->fields[1];
      $numero_vagas          = $RS->fields[2];

      $vagas_net_disponiveis = $numero_vagas;

      #echo $fws_['cod_area_tematica']."<br>";
      #echo $titulo."<br>";
      #echo $numero_vagas."<br>";
      #echo $vagas_net_disponiveis."<br>";


      #Definindo tabela com resultado
      echo "
           <table cellspacing='0' cellpadding='0' width='100%' border=0>
           <tr>
              <td>";

              If($vagas_net_disponiveis<=0){
                $vagas_net_disponiveis = "<b>Inscrições Encerradas</b>";
              }Else{
                echo "<INPUT TYPE='hidden'  NAME='cod_area_tematica' VALUE='".$fws_['cod_area_tematica']."'>";
              }

      echo "
              </td>
              <td width='100%'>
                Área Temática Selecionada: <br><b>".$titulo."</b><br>
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
    if($_SESSION['content']!="view/inscricao/inscricao_trabalhos_confirmar.php"){
        ?>
        <a href="index.php?content=view/inscricao/inscricao_trabalhos_seleciona_area_tematica.php&cod_trabalho=<?php echo $fws_['cod_trabalho']?>"
           class="btn btn-xs btn-primary" role="button">Alterar Área Temática</a>
        <?php
    }
  }Else{
    #Se não comando SQL não retornou nenhum registro imprime mensagens de aviso
    #Mensagem de aviso q não comando SQL não retornou nenhum registro.
    echo "O sistema encontrou nenhuma Área Temática, Tente novamente.";
  }
}Else{
  $Selecionou_Curso = 0;
  #echo "<b>Como nenhuma Área Temática foi selecionada,<br> entende-se que você não deseja Apresentar Trabaho.</b><br><br>";
  echo "É necessário selecionar uma Área Temática.<br><br>";
  
  if($_SESSION['content']!="view/inscricao/inscricao_trabalhos_confirmar.php"){
        ?>
        <a href="index.php?content=view/inscricao/inscricao_trabalhos_seleciona_area_tematica.php&cod_trabalho=<?php echo $fws_['cod_trabalho']?>"
           class="btn btn-xs btn-primary" role="button">Alterar Área Temática</a>
        <?php
  }

}
?>
