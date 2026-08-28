<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-busca.php");

$cod_area_tematica = $_SESSION['cod_area_tematica'];
$cod_trabalho      = $_SESSION['cod_trabalho'];
$FWS_Debug         = false;

#echo $_SESSION['cod_area_tematica'];

If((isset($cod_area_tematica))and(isset($cod_trabalho))){

    $Conexao_db = FWA_Conexao("OpcTec");


    ## Consulta Trabalhos por Área Temática ##

    #Definindo comando SQL
    $var_sql = "
                 SELECT
                  toa.titulo
                 ,ti.cod_inscrito
                 ,ti.nome_inscrito
                 ,tt.cod_trabalho
                 ,tt.autor_apresentador_nome
                 ,tt.autor_apresentador_e_mail
                 ,tt.nome_co_autor_1
                 ,tt.nome_co_autor_2
                 ,tt.nome_co_autor_3
                 ,tt.nome_co_autor_4
                 ,tt.nome_co_autor_5
                 ,tt.instituicao_trabalho
                 ,tt.titulo_trabalho
                 ,tt.palavras_chave
                 ,tt.resumo_trabalho
                 FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                 WHERE (tt.cod_evento        = ".COD_EVENTO.")and
                       (tt.cod_area_tematica = ".$cod_area_tematica.")and
                       (tt.cod_trabalho      = ".$cod_trabalho.")and
                       (tt.cod_area_tematica = toa.cod_area_tematica)and
                       (tt.cod_inscrito      = ti.cod_inscrito)and
                       (ti.status            <> 'EXCLUÍDA')
                 ORDER BY ti.cod_inscrito
               ";#GROUP BY toa.titulo, toa.cod_area_tematica

    $var_sql = "
                 SELECT
                  toa.titulo
                 ,ti.cod_inscrito
                 ,ti.nome_inscrito
                 ,tt.cod_trabalho
                 ,tt.autor_apresentador_nome
                 ,tt.autor_apresentador_e_mail
                 ,tt.nome_co_autor_1
                 ,tt.nome_co_autor_2
                 ,tt.nome_co_autor_3
                 ,tt.nome_co_autor_4
                 ,tt.nome_co_autor_5
                 ,tt.instituicao_trabalho
                 ,tt.titulo_trabalho
                 ,tt.palavras_chave
                 ,tt.resumo_trabalho
                 FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                 WHERE (tt.cod_evento        = ".COD_EVENTO."       )and
                       (tt.cod_area_tematica = ".$cod_area_tematica.")and
                       (tt.cod_trabalho      = ".$cod_trabalho."     )and
                       (tt.cod_area_tematica = toa.cod_area_tematica  )and
                       (tt.cod_inscrito      = ti.cod_inscrito       )and
                       (ti.status            <> 'EXCLUÍDA')
                 ORDER BY ti.cod_inscrito
               ";#GROUP BY toa.titulo, toa.cod_area_tematica

    #echo "<br>".$var_sql."<br>";

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      #Definindo cabeçalho da tabela dos resultados da consula
      echo "
      <table border='0' width='100%' colspan='2' cellspacing='3'>
      <tr><td colspan='3' class='subtitulo'>Área Temática:<br><br><b>{$RS->fields[0]}</b><br>&nbsp;</td></tr>
      <tr><td colspan='3'>
      Segue abaixo titulo e resumo de trabalho <b>A SER RE-AVALIADO</b>:
      <hr size='1' width='100%'>
      </td></tr>
      ";
      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $titulo                     = $RS->fields[0];
        $cod_inscrito               = $RS->fields[1];
        $nome_inscrito              = $RS->fields[2];
        $cod_trabalho               = $RS->fields[3];
        $autor_apresentador_nome    = $RS->fields[4];
        $autor_apresentador_e_mail  = $RS->fields[5];
        $nome_co_autor_1            = $RS->fields[6];
        $nome_co_autor_2            = $RS->fields[7];
        $nome_co_autor_3            = $RS->fields[8];
        $nome_co_autor_4            = $RS->fields[9];
        $nome_co_autor_5            = $RS->fields[10];
        $instituicao_trabalho       = $RS->fields[11];
        $titulo_trabalho            = $RS->fields[12];
        $palavras_chave             = $RS->fields[13];
        $resumo_trabalho            = $RS->fields[14];

        $var_sql = "
                    SELECT ta.avaliacao, ta.observacao
                    FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa, tab_trabalhos_avaliacao ta
                    WHERE (tt.cod_evento        = ".COD_EVENTO."       )
                       and(tt.cod_area_tematica = ".$cod_area_tematica.")
                       and(tt.cod_trabalho      = ".$cod_trabalho."     )
                       and(tt.cod_area_tematica = toa.cod_area_tematica  )
                       and(tt.cod_evento        = ta.cod_evento         )
                       and(tt.cod_area_tematica = ta.cod_area_tematica  )
                       and(tt.cod_trabalho      = ta.cod_trabalho       )
                       and(tt.cod_area_tematica = ta.cod_area_tematica  )
                       and(tt.cod_inscrito      = ti.cod_inscrito       )
                       and(ti.status            <> 'EXCLUÍDA')
                    ";

        $RSA=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

        If(!$RSA->EOF){
          $avaliacao  = $RSA->fields[0];
          $observacao = $RSA->fields[1];
        }
        #Definindo tabela com resultado


        echo "
        <tr><td colspan='3'>
         
          <SCRIPT LANGUAGE=\"JavaScript1.2\" SRC=\"scripts/avaliacao-trabalhos.js\"></SCRIPT>

          <FORM method='POST' name='form' action='view/admin/trabalhos-cadastro-grava.php' onsubmit='return criticar()'>
          
          <input type='hidden' value='".$cod_area_tematica."' name='cod_area_tematica_atual'>
          <input type='hidden' value='".$cod_trabalho."'      name='cod_trabalho'>
          <input type='hidden' value='".COD_EVENTO."'        name='cod_evento'>
          <input type='hidden' value='".$cod_inscrito."'      name='cod_inscrito'>
          

          <fieldset width='500'><legend> Dados do Trabalho </legend>
          ";
          echo "<fieldset width='500'><legend> Área Temática </legend>";


#Definindo comando SQL
      $var_sql = "
                 SELECT toa.cod_area_tematica, toa.titulo
                 FROM tab_opcoes_areas_tematicas toa
                 WHERE
                 (toa.cod_evento = ".COD_EVENTO.")
                 ORDER BY toa.ordem
                ";

  #echo $var_sql;

  #Fim da definição do comando SQL

  $Conexao_db = FWA_Conexao("OpcTec");

  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);

  #Verificando se houve retorno de algum registro
  If (!$RS->EOF) {
    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

    echo "
    &nbsp;Para Alterar entre Area Tematica, selecione uma outra Area Tematica no campo abaixo:
      &nbsp;<select name='cod_area_tematica_nova'>
               <option value='".$cod_area_tematica."'>".$titulo."</option>
    ";

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    While (!$RS->EOF) {

      $cod_area_tematica = $RS->fields[0];
      $TITULO_OPCAO      = $RS->fields[1];

      #Abreviar titulos com nº de caracteres maiores q 50
      $TITULO_OPCAO=substr($TITULO_OPCAO,0,50);
      If(strlen($TITULO_OPCAO)==50){
        $TITULO_OPCAO=$TITULO_OPCAO."...";
      }

      echo "<option value='{$cod_area_tematica}'>{$cod_area_tematica}-{$TITULO_OPCAO}</option>";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }
    echo "
      </select>
    ";
  }Else{

    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhuma AREA TEMATICA cadastrada!<br><br>";

  }


          echo "</fieldset>";

          echo "
          <TABLE WIDTH='500' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
            <TR>
              <TD VALIGN='top'>
                  Nome do Apresentador:
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                <INPUT CLASS='form_campos' TYPE='text' NAME='autor_apresentador_nome' value='{$autor_apresentador_nome}' SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                  E-mail do Apresentador:
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                <INPUT CLASS='form_campos' TYPE='text' NAME='autor_apresentador_e_mail' value='{$autor_apresentador_e_mail}' SIZE='60' maxlength='255' onBlur='trim(this);'>
              </TD>
            </TR>

            <TR>
              <TD VALIGN='top'>
                  Nomes dos Co-Autores: <FONT FACE='verdana' SIZE=1 COLOR='#555555'>(máximo de 5 co-autores por trabalho)</FONT>
              </TD>
            </TR>

            <TR>
              <TD VALIGN='top'>
                  1º: <INPUT CLASS='form_campos' TYPE='text' NAME='nome_co_autor_1' value='{$nome_co_autor_1}' SIZE='57' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                  2º: <INPUT CLASS='form_campos' TYPE='text' NAME='nome_co_autor_2' value='{$nome_co_autor_2}' SIZE='57' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                  3º: <INPUT CLASS='form_campos' TYPE='text' NAME='nome_co_autor_3' value='{$nome_co_autor_3}' SIZE='57' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                  4º: <INPUT CLASS='form_campos' TYPE='text' NAME='nome_co_autor_4' value='{$nome_co_autor_4}' SIZE='57' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                  5º: <INPUT CLASS='form_campos' TYPE='text' NAME='nome_co_autor_5' value='{$nome_co_autor_5}' SIZE='57' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
              </TD>
            </TR>

            <TR>
              <TD VALIGN='top'>
                  Instituição:<br>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                <INPUT CLASS='form_campos' TYPE='text' NAME='instituicao_trabalho' value='{$instituicao_trabalho}' SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                  Titulo do Trabalho:
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                <INPUT CLASS='form_campos' TYPE='text' NAME='titulo_trabalho' value='{$titulo_trabalho}' SIZE='60' maxlength='255' onBlur='trim(this);'>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                  Palavras-chave:<br><FONT FACE='verdana' SIZE=1 COLOR='#555555'>(Máximo 3 palavras-chave, separadas por um sinal de 'ponto e virgula', ou seja ';')
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                <INPUT CLASS='form_campos' TYPE='text' NAME='palavras_chave' value='{$palavras_chave}' SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                  Resumo do Trabalho: <FONT FACE='verdana' SIZE=1 COLOR='#555555'>(Máximo 2100 caracteres, com espaços)
                  <br>
                  <font id=Digitado color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id=Restante color=red>2100</font>
              </TD>
            </TR>
            <TR>
              <TD VALIGN='top'>
                  <textarea  onkeyup='MaxCaracter(this,2100)' onkeypress='MaxCaracter(this,2100)'  rows='15' cols='80' maxlength='2100' name='resumo_trabalho' class='textob'>{$resumo_trabalho}</textarea>
              </TD>
            </TR>
            <TR>
              <TD COLSPAN='2'>&nbsp;</TD>
            </TR>
           </TABLE>

           <TABLE BORDER='0'>
            <TR BGCOLOR='#FFFFFF'  VALIGN='middle'>
              <TD  VALIGN='middle'>
                  <INPUT TYPE='hidden' VALUE='Alteracao' NAME='acao'>
                  <INPUT TYPE=SUBMIT VALUE='Alterar Dados do Trabalho'>
                </FORM>
              </TD>

              <TD VALIGN='middle'>
                <FORM NAME='exit' METHOD='POST' ACTION='index.php'>
                   <INPUT TYPE='hidden' VALUE='view/admin/trabalhos-cadastro-listagem.php' NAME='content'>
                   <INPUT TYPE='hidden' VALUE='Voltar' NAME='acao'>
                   <INPUT TYPE='SUBMIT' VALUE='Voltar'>
                </FORM>
              </TD>
            </TR>
           </TABLE>
          </fieldset>
          
<!--          
          <fieldset width='500'><legend> Dados da Avaliação</legend>
          <SCRIPT LANGUAGE=\"JavaScript1.2\" SRC=\"scripts/avaliacao-trabalhos.js\"></SCRIPT>
          <FORM method='POST' name='form' action='view/admin/trabalhos-cadastro-grava.php' onsubmit='return criticar()'>
          <TABLE WIDTH='500' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
            <TR>
              <TD VALIGN='top'>

              <b>Avalição:</b>&nbsp;&nbsp;&nbsp;
              <input type='radio' name='avaliacao' value='APROVADO' ".FWA_Checked(APROVADO,$avaliacao)."> Aprovado
              &nbsp;&nbsp;&nbsp;
              <input type='radio' name='avaliacao' value='REPROVADO' ".FWA_Checked(REPROVADO,$avaliacao)."> Reprovado
              <BR><BR>
              <b>Justificativa (Exigida somente em caso de reprovação):</b>
              <br>
              <textarea name='observacao' cols='100' rows='10'>".$observacao."</textarea>
              <BR><BR>
              <input type='hidden' value='".COD_EVENTO."'        name='cod_evento'>
              <input type='hidden' value='".$cod_area_tematica."' name='cod_area_tematica'>
              <input type='hidden' value='".$cod_inscrito."'      name='cod_inscrito'>
              <input type='hidden' value='".$cod_trabalho."'      name='cod_trabalho'>
              <input type='hidden' value='Avaliação'            name='acao'>
              <input type='submit' value='Clique aqui para Confirmar Avaliação'>
              </form>
              </TD>
            </TR>
          </TABLE>
          </fieldset>
-->          
        </td></tr>
        ";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
      echo "</table><br>";
    }Else{
       echo "O código da área temática não existe.";
    }

}Else{
   echo "O código da área temática não foi informado.";
}
?>
<hr size='1' width='100%'>
<!--a class='link' href='javascript:history.go(-1)'><b>voltar<b></a-->
<a class='link' href='index.php?content=view/admin/trabalhos-cadastro-listagem.php&cod_area_tematica=<?php echo $cod_area_tematica?>'><b>voltar<b></a>
</body>
</html>

