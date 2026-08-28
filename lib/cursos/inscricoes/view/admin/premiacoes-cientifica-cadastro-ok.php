<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/premiacoes-cientifica-cabecalho.php");

require_once(dirname(__FILE__)."/../../view/admin/premiacoes-cientifica-cadastro-busca.php");


@$COD_PREMIACAO_CIENTIFICA   = $_GET['COD_PREMIACAO_CIENTIFICA'];
If(isset($COD_PREMIACAO_CIENTIFICA)){

  #echo $COD_PREMIACAO_CIENTIFICA."<br>";

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
                  observacao
                ";


     #Definindo comando SQL
      $var_sql = "
                 SELECT ".$Var_Campos."
                 FROM tab_opcoes_premiacoes_cientifica
                 WHERE
                 (cod_premiacao_cientifica = ".$COD_PREMIACAO_CIENTIFICA.")and
                 (cod_evento  = ".COD_EVENTO.")
                 ORDER BY cod_premiacao_cientifica DESC
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

        If(($ORDEM              =="")or($ORDEM              ==" ")or($ORDEM              ==null)){ $ORDEM              =" - "; }
        If(($STATUS             =="")or($STATUS             ==" ")or($STATUS             ==null)){ $STATUS             =" - "; }
        If(($TITULO             =="")or($TITULO             ==" ")or($TITULO             ==null)){ $TITULO             =" - "; }
        If(($NOME_MINISTRANTE   =="")or($NOME_MINISTRANTE   ==" ")or($NOME_MINISTRANTE   ==null)){ $NOME_MINISTRANTE   =" - "; }
        If(($E_MAIL_MINISTRANTE =="")or($E_MAIL_MINISTRANTE ==" ")or($E_MAIL_MINISTRANTE ==null)){ $E_MAIL_MINISTRANTE =" - "; }
        If(($FONE_MINISTRANTE   =="")or($FONE_MINISTRANTE   ==" ")or($FONE_MINISTRANTE   ==null)){ $FONE_MINISTRANTE   =" - "; }
        If(($LOCAL              =="")or($LOCAL              ==" ")or($LOCAL              ==null)){ $LOCAL              =" - "; }
        If(($CARGA_HORARIA      =="")or($CARGA_HORARIA      ==" ")or($CARGA_HORARIA      ==null)){ $CARGA_HORARIA      =" - "; }
        If(($NUMERO_VAGAS       =="")or($NUMERO_VAGAS       ==" ")or($NUMERO_VAGAS       ==null)){ $NUMERO_VAGAS       =" - "; }
        If(($HORARIO_INI        =="")or($HORARIO_INI        ==" ")or($HORARIO_INI        ==null)){ $HORARIO_INI        =" - "; }
        If(($HORARIO_FIM        =="")or($HORARIO_FIM        ==" ")or($HORARIO_FIM        ==null)){ $HORARIO_FIM        =" - "; }
        If(($DATA_INI           =="")or($DATA_INI           ==" ")or($DATA_INI           ==null)){ $DATA_INI           =" - "; }
        If(($DATA_FIM           =="")or($DATA_FIM           ==" ")or($DATA_FIM           ==null)){ $DATA_FIM           =" - "; }
        If(($PALAVRAS_CHAVE      =="")or($PALAVRAS_CHAVE      ==" ")or($PALAVRAS_CHAVE      ==null)){ $PALAVRASS_CHAVE     =" - "; }
        If(($PUBLICO_ALVO       =="")or($PUBLICO_ALVO       ==" ")or($PUBLICO_ALVO       ==null)){ $PUBLICO_ALVO       =" - "; }
        If(($OBJETIVO           =="")or($OBJETIVO           ==" ")or($OBJETIVO           ==null)){ $OBJETIVO           =" - "; }
        If(($PRE_REQUISITO      =="")or($PRE_REQUISITO      ==" ")or($PRE_REQUISITO      ==null)){ $PRE_REQUISITO      =" - "; }
        If(($EMENTA             =="")or($EMENTA             ==" ")or($EMENTA             ==null)){ $EMENTA             =" - "; }
        If(($DESCRICAO          =="")or($DESCRICAO          ==" ")or($DESCRICAO          ==null)){ $DESCRICAO          =" - "; }
        If(($OBSERVACAO         =="")or($OBSERVACAO         ==" ")or($OBSERVACAO         ==null)){ $OBSERVACAO         =" - "; }

        #Definindo tabela com resultado

echo <<<corpo

      <hr size='1' width='100%'>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/premiacoes-cientifica-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/premiacoes-cientifica-cadastro-alterar.php&COD_PREMIACAO_CIENTIFICA={$COD_PREMIACAO_CIENTIFICA}'><img src='resources/image/txt-alterar.gif' border='0'></a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='view/admin/premiacoes-cientifica-cadastro-excluir-grava.php?COD_PREMIACAO_CIENTIFICA={$COD_PREMIACAO_CIENTIFICA}&COD_EVENTO={COD_EVENTO}&acao=Excluir'><img src='resources/image//txt-excluir.gif' border='0'></a>
      <hr size='1' width='100%'>

      <table border='0' width='570'>

      <tr><td colspan='2'>&nbsp;<b> Código da Premiação Cientifica: {$COD_PREMIACAO_CIENTIFICA}</td></tr>

      <tr><td colspan='2'>&nbsp;<b> Status:  {$STATUS}</b></td></tr>

      <tr><td width='110'>&nbsp;</td><td width='460'>&nbsp;</td></tr>

      <tr><td colspan='2'>&nbsp;<b> Posição na Listagem das Premiações Cientifica:  </b> {$ORDEM}º </td></tr>
      <tr><td colspan='2'>&nbsp;<b> Titulo da Premiações Cientifica:  </b> {$TITULO            }</td></tr>
      <tr><td colspan='2'>&nbsp;<b> Nome Ministrante:   </b> {$NOME_MINISTRANTE  }</td></tr>
      <tr><td colspan='2'>&nbsp;<b> E-mail Ministrante: </b> {$E_MAIL_MINISTRANTE}</td></tr>
      <tr><td colspan='2'>&nbsp;<b> Fone Ministrante:   </b> {$FONE_MINISTRANTE  }</td></tr>

      <tr><td colspan='2'>&nbsp;<b> Local Atividade:    </b> {$LOCAL}             </td></tr>

      <tr><td>&nbsp;<b> Carga Horária:      </td><td> {$CARGA_HORARIA}     </td></tr>
      <tr><td>&nbsp;<b> Número Vagas:       </td><td> {$NUMERO_VAGAS }     </td></tr>

      <tr><td>&nbsp;<b> Horário Inicio:     </td><td> {$HORARIO_INI}       </td></tr>
      <tr><td>&nbsp;<b> Horário Fim:        </td><td> {$HORARIO_FIM}       </td></tr>

      <tr><td>&nbsp;<b> Data Inicio:        </td><td> {$DATA_INI   }       </td></tr>
      <tr><td>&nbsp;<b> Data Fim:           </td><td> {$DATA_FIM   }       </td></tr>

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
              <a href='index.php?content=view/admin/premiacoes-cientifica-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;

              <!--
              <a href=index.php?content=view/admin/premiacoes-cientifica-cadastro-alterar.php&COD_PREMIACAO_CIENTIFICA=".$COD_PREMIACAO_CIENTIFICA.">Alterar</a>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='view/admin/premiacoes-cientifica-cadastro-grava-exclusao.php?COD_PREMIACAO_CIENTIFICA=".$COD_PREMIACAO_CIENTIFICA."&acao=Excluir'>Excluir</a>
              -->
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    A Premiações Cientifica Número: ".$COD_PREMIACAO_CIENTIFICA.", não Existe ou foi Excluída!
                  </td></tr>
                </table>
                <br>
            ";

     }
}Else{

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/premiacoes-cientifica-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    Clique no Botão Incluir para Inserir uma Premiações Cientifica!
                  </td></tr>
                </table>
                <br>
            ";



}


?>
</body>
</html>
</div></div></div></div></div>