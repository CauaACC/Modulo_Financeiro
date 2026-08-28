<?php
if(@$fws_['cod_perfil'] != ""){
    $obj = new Inscrito();
    $obj->setEMail($fws_['e_mail']);
    $obj->setCpf($fws_['cpf']);
    if ($obj->verificarInscritoApto($debug)) {
        $inscritoSocio = true;
    }
  #-----------------------------------------------------------------------------------------
  $var_sql = "
              SELECT cod_perfil, titulo, numero_vagas, verificar_inscrito_apto
              FROM tab_opcoes_perfis
              WHERE (cod_evento        = COD_EVENTO)
                and (status  = 'ATIVADO')
                and (cod_perfil = ".$fws_['cod_perfil'].")"
          . "";

  #echo $var_sql;

  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);

  #Verificando se houve retorno de algum registro
  if (!$RS->EOF) {

    while (!$RS->EOF) {

      $fws_['cod_perfil'] = $RS->fields[0];
      $titulo             = $RS->fields[1];
      $numero_vagas       = $RS->fields[2];
      $verificar_inscrito_apto = $RS->fields[3];

      $vagas_net_disponiveis = $numero_vagas;

      #echo $fws_['cod_perfil']."<br>";
      #echo $titulo."<br>";
      #echo $numero_vagas."<br>";
      #echo $vagas_net_disponiveis."<br>";


      #Definindo tabela com resultado
      echo "
           <table cellspacing='0' cellpadding='0' width='100%' border=0>
           <tr>
              <td>";
            $inscrito_apto_liberado = true;
            if ($verificar_inscrito_apto == true) {
                if ($inscritoSocio == true) {
                    $inscrito_apto_liberado = true;
                } else {
                    $inscrito_apto_liberado = false;
                }
            }

            if ($vagas_net_disponiveis <= 0) {
                $vagas_net_disponiveis = "<b>Inscrições Encerradas</b>";
            } else {
                if($inscrito_apto_liberado == true){
                    echo "<INPUT TYPE='hidden'  NAME='cod_perfil' VALUE='" . $fws_['cod_perfil'] . "'>";
                }else{
                    echo "Seu e-mail não consta na lista de associados";
                    echo "<a href='http://www.sbmp.org.br/regionais' target='blank'>clique aqui para verificar sua situação como associado</a>";
                }
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
  }else{
    #Se não comando SQL não retornou nenhum registro imprime mensagens de aviso
    #Mensagem de aviso q não comando SQL não retornou nenhum registro.
    echo "O sistema encontrou nenhum Perfil, Tente novamente.";
  }
}else{
  $Selecionou_Curso = 0;
  #echo "<b>Como nenhuma Área Temática foi selecionada,<br> entende-se que você não deseja Apresentar Trabaho.</b><br><br>";
  echo "É necessário selecionar um Perfil.<br><br>";
  
  if($_SESSION['content']!="view/inscricao/inscricao_trabalhos_confirmar.php"){
        ?>
        <a href="index.php?content=view/inscricao/inscricao_trabalhos_seleciona_area_tematica.php&cod_trabalho=<?php echo $fws_['cod_trabalho']?>"
           class="btn btn-xs btn-primary" role="button">Alterar Área Temática</a>
        <?php
  }

}
?>
