<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/oficina-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/oficina-cadastro-busca.php");

#Fazendo conexão com o banco

$COD_OFICINA = $_GET['COD_OFICINA'];

#echo $COD_OFICINA  ."<br>";

If(isset($COD_OFICINA)){

  $Conexao_db = FWA_Conexao("OpcTec");

  $Var_Campos = "
                  ordem,
                  status,
                  titulo,
                  nome_ministrante,
                  e_mail_ministrante,
                  fone_ministrante,
                  local,
                  carga_horaria,
                  numero_vagas,
                  horario_ini,
                  horario_fim,
                  data_ini,
                  data_fim,
                  palavras_chave,
                  publico_alvo,
                  objetivo,
                  pre_requisito,
                  ementa,
                  descricao,
                  observacao,
                  numero_oficial,
                  tipo
                ";


     #Definindo comando SQL
      $var_sql = "
                 SELECT ".$Var_Campos."
                 FROM tab_opcoes_oficinas
                 WHERE
                 (cod_oficina = ".$COD_OFICINA.")and
                 (cod_evento  = ".COD_EVENTO.")
                 ORDER BY cod_oficina DESC
                ";

  #echo $var_sql;

  #Fim da definição do comando SQL

  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);

  #Verificando se houve retorno de algum registro
  If (!$RS->EOF) {
    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    While (!$RS->EOF) {

      #echo "<br>Adicionando nas variaveis, os valores dos campos retirados dos registros<br>";
      $ORDEM              = $RS->fields[0];
      $STATUS             = $RS->fields[1];
      $TITULO             = $RS->fields[2];
      $NOME_MINISTRANTE   = $RS->fields[3];
      $E_MAIL_MINISTRANTE = $RS->fields[4];
      $FONE_MINISTRANTE   = $RS->fields[5];
      $LOCAL              = $RS->fields[6];
      $CARGA_HORARIA      = $RS->fields[7];
      $NUMERO_VAGAS       = $RS->fields[8];
      $HORARIO_INI        = $RS->fields[9];;
      $HORARIO_FIM        = $RS->fields[10];
      $DATA_INI           = $RS->fields[11];
      $DATA_FIM           = $RS->fields[12];
      $PALAVRAS_CHAVE     = $RS->fields[13];
      $PUBLICO_ALVO       = $RS->fields[14];
      $OBJETIVO           = $RS->fields[15];
      $PRE_REQUISITO      = $RS->fields[16];
      $EMENTA             = $RS->fields[17];
      $DESCRICAO          = $RS->fields[18];
      $OBSERVACAO         = $RS->fields[19];
      $NUMERO_OFICIAL     = $RS->fields[20];
      $TIPO              = $RS->fields[21];

      if ($DATA_INI != ''){
        $DATA_INI_DIA = substr($DATA_INI, 0, 2);
        $DATA_INI_MES = substr($DATA_INI, 3, 2);
        $DATA_INI_ANO = substr($DATA_INI, 6, 4);
      }
      if ($DATA_FIM !=''){
        $DATA_FIM_DIA = substr($DATA_FIM, 0, 2);
        $DATA_FIM_MES = substr($DATA_FIM, 3, 2);
        $DATA_FIM_ANO = substr($DATA_FIM, 6, 4);
  
      }
      if ($HORARIO_INI !=''){
        $HORARIO_INI_HORA   = substr($HORARIO_INI, 0, 2);
        $HORARIO_INI_MINUTO = substr($HORARIO_INI, 3, 2);
      }
      if ($HORARIO_FIM !=''){
        $HORARIO_FIM_HORA   = substr($HORARIO_FIM, 0, 2);
        $HORARIO_FIM_MINUTO = substr($HORARIO_FIM, 3, 2);
      }


      #echo $STATUS             ."<br>";
      #echo $TITULO             ."<br>";
      #echo $NOME_MINISTRANTE   ."<br>";
      #echo $E_MAIL_MINISTRANTE ."<br>";
      #echo $FONE_MINISTRANTE   ."<br>";
      #echo $LOCAL              ."<br>";
      #echo $CARGA_HORARIA      ."<br>";
      #echo $NUMERO_VAGAS       ."<br>";
      #echo $HORARIO_INI        ."<br>";
      #echo $HORARIO_FIM        ."<br>";
      #echo $DATA_INI           ."<br>";
      #echo $DATA_FIM           ."<br>";
      #echo $PALAVRAS_CHAVE     ."<br>";
      #echo $PUBLICO_ALVO       ."<br>";
      #echo $OBJETIVO           ."<br>";
      #echo $PRE_REQUISITO      ."<br>";
      #echo $EMENTA             ."<br>";
      #echo $DESCRICAO          ."<br>";
      #echo $OBSERVACAO         ."<br>";
      
    $objOficina= new Oficina();
    if($COD_OFICINA){
        $objOficina->setCodOficina($COD_OFICINA);
        $objOficina->getOficinaById($debug);
    }
    if($objOficina->getTipo()=='Agenda'){
        $tituloAtividade = "Sessão: ".$objOficina->getDataAgenda();
    }else{
        $tituloAtividade = $objOficina->getCodOficina();
        if($objOficina->getTituloCertificado()!=''){
          $tituloAtividade .= ' - '. $objOficina->getTituloCertificado();
        }else{
          $tituloAtividade .= ' - '. $objOficina->getTitulo();
        }
    }
    $tipoAtividade = $objOficina->getTipo()?  $objOficina->getTipo() : "Atividade";
    ?>
    <hr>
    <h4><b><?php echo $tituloAtividade?></b></h4>
    <?php
      echo "
      <hr size='1' width='100%'>
      <FONT SIZE='2' FACE='VERDANA'> <B> Ficha de Cadastro de {$tipoAtividade}</B></FONT><br>
      <FONT SIZE='1' FACE='VERDANA'> <B> * Campos Obrigatórios</B></FONT>
      <FONT SIZE='1' FACE='VERDANA'>

      <FORM method='POST' name='form' action='view/admin/oficina-cadastro-alterar-grava.php' id='formOficina' onsubmit='return criticar()'>
        <input type='hidden' name='COD_OFICINA' id='codOficina' value='{$COD_OFICINA}'>
        <input type='hidden' name='COD_EVENTO'  value='{$COD_EVENTO}'>

      <table border='0' width='565'>
      <tr><td colspan='2' align='left'>
            <input type='submit' value='Salvar'  class='button btn btn-primary btn-xs'>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='reset' value='Limpar'  class='button btn btn-primary btn-xs'>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <button type='submit' onclick='javascript:duplicarOficina(event);' class='button btn btn-primary btn-xs'>Duplicar</button>";
            if($objOficina->getTipo()!='Agenda'){
            echo "&nbsp;&nbsp;&nbsp;&nbsp;      
            <a href='index.php?content=view/admin/oficina-cadastro-ok.php&COD_OFICINA={$objOficina->getCodOficina()}'  class='button btn btn-primary btn-xs'> Certificado</a>";
            }
            echo "&nbsp;&nbsp;&nbsp;&nbsp;      
            <a href='index.php?content=view/admin/GerenciarArquivoAtividade.php&cod_oficina={$objOficina->getCodOficina()}'  class='button btn btn-primary btn-xs'> Add Conteúdo</a>";
            
      echo "
      </td></tr>
      </table>
      <fieldset width='570'><legend> Dados da {$tipoAtividade} </legend>
      ";

        $camposEditar = true;
        include_once("oficina-cadastro-campos.php");

      echo "
      </fieldset>
      <table border='0' width='565'>
      <tr><td colspan='2' align='left'>

            <input type='hidden' name='c' value='GerenciarOficiana'>
            <input type='hidden' name='m' value='salvar'>
      
            <br><br>
            <input type='submit' value='Salvar' class='button btn btn-primary btn-xs'>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='reset' value='Limpar' class='button btn btn-primary btn-xs'>
            &nbsp;&nbsp;&nbsp;&nbsp;      
            <button type='submit' onclick='javascript:duplicarOficina(event);' class='button btn btn-primary btn-xs'>Duplicar</button>";
            if($objOficina->getTipo()!='Agenda'){
            echo "&nbsp;&nbsp;&nbsp;&nbsp;      
            <a href='index.php?content=view/admin/oficina-cadastro-ok.php&COD_OFICINA={$objOficina->getCodOficina()}'  class='button btn btn-primary btn-xs'> Certificado</a>";
            }
      echo "
      </td></tr>
      </table>
      </FORM>
      ";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }


  }Else{
    echo "Codigo não encontrado.";
  }

}Else{
  echo "É Necessário Codigo da Oficina.";
}

?>
</div></div></div></div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#btnDuplicar').click(function(event) {
            event.preventDefault();
//            $('#novoCodOficina').val(0);
//            $('#salvarOficina').val('Duplicar');
//            document.location.href='/index.php';
            $('#c').val('GerenciarOficina');
            $('#m').val('duplicar');
            duplicarOficina();
        });
    });
    function duplicarOficina(event) {
        event.preventDefault();
        $.ajax({
            type: "POST"
            ,url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
            ,data: ({'codOficina': $('#codOficina').val(), 'c': 'GerenciarOficina', 'm': 'duplicar', '_submit_check': '1'})
            ,dataType: "json"
            ,async: false
            ,cache: false
            , beforeSend: function() {
                startLoading();
            }
            , timeout: 4000
            , error: function(ajax) {
                showResponse(ajax);
            }
            , success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    document.location.href='<?php echo WB_URL_VIEW ?>?content=view/admin/oficina-cadastro-alterar.php&COD_OFICINA='+data.codOficina
                    //location.reload(); 
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
</script>