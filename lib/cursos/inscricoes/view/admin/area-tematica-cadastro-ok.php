<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/area-tematica-cabecalho.php");


@$cod_area_tematica   = $_GET['cod_area_tematica'];
require_once(dirname(__FILE__)."/../../view/admin/area-tematica-cadastro-busca.php");
if(@$cod_area_tematica!=''){
} else {
    require_once(dirname(__FILE__)."/../../view/admin/area-tematica-cadastro-listar.php");    
}

If(isset($cod_area_tematica)){

  #echo $cod_area_tematica."<br>";

  $Conexao_db = FWA_Conexao("OpcTec");

  $Var_Campos = "
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
                  n_max_trab_por_avaliador
                ";


     #Definindo comando SQL
      $var_sql = "
                 SELECT ".$Var_Campos."
                 FROM tab_opcoes_areas_tematicas
                 WHERE
                 (cod_area_tematica = ".$cod_area_tematica.")and
                 (cod_evento  = ".COD_EVENTO.") and (status='ATIVADO')
                 ORDER BY cod_area_tematica DESC
                ";

    #echo $var_sql;

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      #Definindo cabeçalho da tabela dos resultados da consula

      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $STATUS           = $RS->fields[0];
        $TITULO             = $RS->fields[1];
        $NOME_MINISTRANTE   = $RS->fields[2];
        $E_MAIL_MINISTRANTE = $RS->fields[3];
        $FONE_MINISTRANTE   = $RS->fields[4];
        $LOCAL              = $RS->fields[5];
        $CARGA_HORARIA      = $RS->fields[6];
        $NUMERO_VAGAS       = $RS->fields[7];
        $HORARIO_INI        = $RS->fields[8];
        $HORARIO_FIM        = $RS->fields[9];;
        $DATA_INI           = $RS->fields[10];
        $DATA_FIM           = $RS->fields[11];
        $PALAVRAS_CHAVE     = $RS->fields[12];
        $PUBLICO_ALVO       = $RS->fields[13];
        $OBJETIVO           = $RS->fields[14];
        $PRE_REQUISITO      = $RS->fields[15];
        $EMENTA             = $RS->fields[16];
        $DESCRICAO          = $RS->fields[17];
        $OBSERVACAO         = $RS->fields[18];
        $N_MAX_TRAB_POR_AVALIADOR = $RS->fields[19];

        #Definindo tabela com resultado

echo <<<corpo

      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/area-tematica-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/area-tematica-cadastro-alterar.php&cod_area_tematica={$cod_area_tematica}'><img src='resources/image/txt-alterar.gif' border='0'></a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='view/admin/area-tematica-cadastro-excluir-grava.php?cod_area_tematica={$cod_area_tematica}&COD_EVENTO={COD_EVENTO}&acao=Excluir'><img src='resources/image//txt-excluir.gif' border='0'></a>
      <hr size='1' width='100%'>
corpo;

FWA_Mostrar_Mensagem($_SESSION['MSG']);

echo <<<corpo
      <table border='0' width='570'>

      <tr><td colspan='2'>&nbsp;<b> Código da AREA TEMATICA: {$cod_area_tematica}</td></tr>

      <tr><td colspan='2'>&nbsp;<b> Status:  {$STATUS}</b></td></tr>

      <tr><td width='110'>&nbsp;</td><td width='460'>&nbsp;</td></tr>


      <tr><td colspan='2'>&nbsp;<b> Titulo da Area Tematica: </b> {$TITULO            }</td></tr>
      <tr><td colspan='2'>&nbsp;<b> Nome Ministrante:        </b> {$NOME_MINISTRANTE  }</td></tr>
      <tr><td colspan='2'>&nbsp;<b> E-mail Ministrante:      </b> {$E_MAIL_MINISTRANTE}</td></tr>
      <tr><td colspan='2'>&nbsp;<b> Fone Ministrante:        </b> {$FONE_MINISTRANTE  }</td></tr>

      <tr><td colspan='2'>&nbsp;<b> Local Minicurso:         </b> {$LOCAL}             </td></tr>

      <tr><td>&nbsp;<b> Carga Horária:      </td><td> {$CARGA_HORARIA}     </td></tr>
      <tr><td>&nbsp;<b> Número Vagas:       </td><td> {$NUMERO_VAGAS }     </td></tr>

      <tr><td>&nbsp;<b> Horário Inicio:     </td><td> {$HORARIO_INI}       </td></tr>
      <tr><td>&nbsp;<b> Horário Fim:        </td><td> {$HORARIO_FIM}       </td></tr>

      <tr><td>&nbsp;<b> Data Inicio:        </td><td> {$DATA_INI   }       </td></tr>
      <tr><td>&nbsp;<b> Data Fim:           </td><td> {$DATA_FIM   }       </td></tr>
      
      <tr><td colspan=2>&nbsp;<b> Número maximo de trabalhos por avaliador: {$N_MAX_TRAB_POR_AVALIADOR}</td></tr>

      <tr><td colspan=2>&nbsp;<b> Palavras-Chave: </td></tr>
      <tr><td colspan=2><blockquote>{$PALAVRAS_CHAVE}</td></tr>

      <tr><td colspan=2>&nbsp;<b> Publico Alvo: </td></tr>
      <tr><td colspan=2><blockquote>{$PUBLICO_ALVO  }</td></tr>

      <tr><td colspan=2>&nbsp;<b> Objetivo: </td></tr>
      <tr><td colspan=2><blockquote>{$OBJETIVO      }</td></tr>

      <tr><td colspan=2>&nbsp;<b> Pré-Requisito: </td></tr>
      <tr><td colspan=2><blockquote>{$PRE_REQUISITO }</td></tr>

      <tr><td colspan=2>&nbsp;<b> Ementa: </td></tr>
      <tr><td colspan=2><blockquote>{$EMENTA        }</td></tr>

      <tr><td colspan=2>&nbsp;<b> Descrição: </td></tr>
      <tr><td colspan=2><blockquote>{$DESCRICAO     }</td></tr>

      <tr><td colspan=2>&nbsp;<b> Observação: </td></tr>
      <tr><td colspan=2><blockquote>{$OBSERVACAO    }</td></tr>
      </table>
      <br>
corpo;


        #Movendo para o proximo registro
        $RS->MoveNext();
      }
    }Else{

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/area-tematica-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;

              <!--
              <a href=index.php?content=view/admin/area-tematica-cadastro-alterar.php&cod_area_tematica=".$cod_area_tematica.">Alterar</a>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='view/admin/area-tematica-cadastro-grava-exclusao.php?cod_area_tematica=".$cod_area_tematica."&acao=Excluir'>Excluir</a>
              -->
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    ".FWA_Mostrar_Mensagem("A AREA TEMATICA Número: ".$cod_area_tematica.", não Existe ou foi Excluída!")." 
                  </td></tr>
                </table>
                <br>
            ";

     }
}Else{

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/area-tematica-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    Clique no Botão Incluir para Inserir uma AREA TEMATICA!
                  </td></tr>
                </table>
                <br>
            ";



}


?>
</div></div></div></div>
</body>
</html>