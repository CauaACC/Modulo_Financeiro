<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/evento-cabecalho.php");
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Configurar Inscrição</h4></div>
    <div class="panel-body"> 
<?php
require_once(dirname(__FILE__)."/../../view/admin/evento-configurar-busca.php");
#Fazendo conexão com o banco

//$cod_evento = $_GET['cod_evento'];
$cod_evento = COD_EVENTO;

#echo $cod_evento  ."<br>";

If(isset($cod_evento)){

  $Conexao_db = FWA_Conexao("OpcTec");
     #Definindo comando SQL
      $var_sql = "
                 SELECT *
                 FROM tab_eventos_configuracao
                 WHERE
                 (cod_evento = ".$cod_evento.")
                 ORDER BY cod_evento DESC
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
//       $objEv = new Evento();
//       $objEv->setCodEvento($cod_evento);
//       $objEv->getEventoById();
//       $objEvConf = $objEv->getEventoConfiguracao($debug);
////       dd($objEvConf);
//       echo "<h4>Evento: ".$objEv->getTitulo()."</h4>";
      
      echo "
      <FONT SIZE='1' FACE='VERDANA'>

      <FORM method='POST' name='form' action='view/admin/evento-configurar-alterar-grava.php' onsubmit=\"return criticar()\">
        <input type='hidden' name='cod_evento' value='{$cod_evento}'>

      <fieldset width='570'>
      ";

      include_once("evento-configurar-campos.php");

      /* Botoes no padrao da aplicacao: salvar = modalBtnSalvar, secundario =
       * modalBtnCancelar (as classes valem em form fora de modal tambem — o
       * nome se refere ao visual, nao ao container). */
      echo "
      </fieldset>
      <div style='text-align:center;margin:24px 0;'>
        <button type='submit' class='btn btn-primary modalBtnSalvar'>
          <i class='fa fa-check'></i> Salvar
        </button>
        &nbsp;&nbsp;
        <button type='reset' class='btn btn-default modalBtnCancelar'>Limpar</button>
      </div>
      </FORM>
      ";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }


  }Else{
    /* Projeto sem configuracao. Alem do "Incluir" de sempre, oferece IMPORTAR de
     * outro projeto — e aqui, nao uma tela adiante: e' nesta tela que se cai
     * pelo menu, e e' aqui que alguem se pergunta se vai ter de preencher as
     * ~273 configuracoes na mao. O bloco so existe enquanto nao ha configuracao,
     * o que faz do importar uma acao de uma vez so. */
    echo "
    <hr size=1 width=100%>
    <table border=0 width=570>
      <tr><td style='padding:6px 0 14px 0;'>
        Este projeto ainda <b>não tem configuração</b>. Escolha por onde começar:
      </td></tr>
      <tr><td style='padding-bottom:6px;'>
        <a href='index.php?content=view/admin/evento-configurar-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
        <span style='font-size:11px;color:#888;display:block;margin-top:4px;'>
          Preencher tudo à mão.
        </span>
      </td></tr>
      <tr><td style='padding-top:10px;'>
    ";
    include_once("evento-configurar-importar.php");
    echo "
      </td></tr>
    </table>
    <hr size=1 width=100%>
    <br>
    ";
  }

}Else{
  echo "É Necessário Codigo da Evento.";
}

?></div></div></div></div>