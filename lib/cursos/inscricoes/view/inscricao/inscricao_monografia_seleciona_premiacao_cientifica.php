<?php
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");

$_SESSION['Cod_premiacao_cientifica']="";
#echo $_SESSION['cod_oficina'];


?>

<SCRIPT LANGUAGE="JAVASCRIPT">

<!--

//------------------------------------------------------------------------
// Testa os campos

function criticar() {

  //alert(getCheckedRadioButton(document.form.cod_premiacao_cientifica));
  /*
  if (getCheckedRadioButton(document.form.cod_premiacao_cientifica)==-1){
     alert("Você deve selecionar um tema para apresentação de trabalho!");
     return false;
  }
  */
  /*
  if ((document.form.cod_curso_1.checked)&&(document.form.cod_curso_2.checked)){
     alert("Você não pode fazer inscrição nos dois cursos ao mesmo tempo!");
     return false;
  }
  */


}

// -->

</SCRIPT>

</head>

  <body>
  <blockquote>
  <table width='520' border='1' bgcolor='FFFFFF'  CELLSPACING='0' CELLPADDING='10'><tr><td>
    <FONT FACE='verdana' SIZE='3'>
      <B>Opções de Premiação Científica</B>
    </FONT>
    <br><br>

    <FONT FACE='verdana' SIZE='-1' COLOR='#000000'>
    <P ALIGN="justify">

     &nbsp;Selecione uma das opções de premiação científica e clique avançar:
     <br><br>
     &nbsp;<a href='index.php?content=view/inscricao/inscricao_trabalhos_inicio.php&cod_premiacao_cientifica='><font color="black">Para NÃO submeter monografia, clique aqui</font></a>.

    <FORM method='POST' name='form'  action='index.php' onsubmit='return criticar()'>

    <fieldset width='500'><legend> Lista de Opções para Premiação Científica </legend>
    <br>
    <table border='0' width='500' CELLSPACING='0' CELLPADDING='6'>

    <?php
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
                    cod_premiacao_cientifica
                  ";


     #Definindo comando SQL
      $var_sql = "
                 SELECT ".$Var_Campos."
                 FROM tab_opcoes_premiacoes_cientifica
                 WHERE
                 (cod_evento  = ".COD_EVENTO.")
                 and (status  = 'ATIVADA')
                 ORDER BY ordem
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
        $STATUS             = $RS->fields[0];
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
        $COD_PREMIACAO_CIENTIFICA  = $RS->fields[19];

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
        If(($PALAVRAS_CHAVE     =="")or($PALAVRAS_CHAVE     ==" ")or($PALAVRAS_CHAVE     ==null)){ $PALAVRASS_CHAVE    =" - "; }
        If(($PUBLICO_ALVO       =="")or($PUBLICO_ALVO       ==" ")or($PUBLICO_ALVO       ==null)){ $PUBLICO_ALVO       =" - "; }
        If(($OBJETIVO           =="")or($OBJETIVO           ==" ")or($OBJETIVO           ==null)){ $OBJETIVO           =" - "; }
        If(($PRE_REQUISITO      =="")or($PRE_REQUISITO      ==" ")or($PRE_REQUISITO      ==null)){ $PRE_REQUISITO      =" - "; }
        If(($EMENTA             =="")or($EMENTA             ==" ")or($EMENTA             ==null)){ $EMENTA             =" - "; }
        If(($DESCRICAO          =="")or($DESCRICAO          ==" ")or($DESCRICAO          ==null)){ $DESCRICAO          =" - "; }
        If(($OBSERVACAO         =="")or($OBSERVACAO         ==" ")or($OBSERVACAO         ==null)){ $OBSERVACAO         =" - "; }

        #Definindo tabela com resultado

If (@$bgcolorant=="#F4F4F4"){
   $bgcolor="#FFFFFF";
}Else{
   $bgcolor="#F4F4F4";
}

$bgcolorant=$bgcolor;

echo <<<corpo

<!--          <tr><td colspan='2'><hr size='1' width='100%'></td></tr>-->


<!--      <tr><td colspan='2'>&nbsp;<b>Código da AREA TEMATICA: {$COD_PREMIACAO_CIENTIFICA}</td></tr>-->
<!--      <tr><td colspan='2'>&nbsp;<b> Status:  {$STATUS}</b></td></tr>-->

      <tr valign='top' bgcolor='{$bgcolor}'><td width='6'><INPUT TYPE='radio' NAME='cod_premiacao_cientifica' VALUE='{$COD_PREMIACAO_CIENTIFICA}' ></td><td width='494'><font size='1'> {$TITULO} </td></tr>


<!--      <tr><td colspan='2'>&nbsp;<b> Nome Ministrante:        </b> {$NOME_MINISTRANTE  }</td></tr>-->
<!--      <tr><td colspan='2'>&nbsp;<b> E-mail Ministrante:      </b> {$E_MAIL_MINISTRANTE}</td></tr>-->
<!--      <tr><td colspan='2'>&nbsp;<b> Fone Ministrante:        </b> {$FONE_MINISTRANTE  }</td></tr>-->

<!--      <tr><td colspan='2'>&nbsp;<b> Local Minicurso:         </b> {$LOCAL}             </td></tr>-->

<!--      <tr><td>&nbsp;<b> Carga Horária:      </td><td> {$CARGA_HORARIA}     </td></tr>-->
<!--      <tr><td>&nbsp;<b> Número Vagas:       </td><td> {$NUMERO_VAGAS }     </td></tr>-->

<!--      <tr><td>&nbsp;<b> Horário Inicio:     </td><td> {$HORARIO_INI}       </td></tr>-->
<!--      <tr><td>&nbsp;<b> Horário Fim:        </td><td> {$HORARIO_FIM}       </td></tr>-->

<!--      <tr><td>&nbsp;<b> Data Inicio:        </td><td> {$DATA_INI   }       </td></tr>-->
<!--      <tr><td>&nbsp;<b> Data Fim:           </td><td> {$DATA_FIM   }       </td></tr>-->

<!--      <tr><td colspan=2>&nbsp;<b> Palavras-Chave: </td></tr>-->
<!--      <tr><td colspan=2><blockquote>{$PALAVRAS_CHAVE}</td></tr>-->

<!--      <tr><td colspan=2>&nbsp;<b> Publico Alvo: </td></tr>-->
<!--      <tr><td colspan=2><blockquote>{$PUBLICO_ALVO  }</td></tr>-->

<!--      <tr><td colspan=2>&nbsp;<b> Objetivo: </td></tr>-->
<!--      <tr><td colspan=2><blockquote>{$OBJETIVO      }</td></tr>-->

<!--      <tr><td colspan=2>&nbsp;<b> Pré-Requisito: </td></tr>-->
<!--      <tr><td colspan=2><blockquote>{$PRE_REQUISITO }</td></tr>-->

<!--      <tr><td colspan=2>&nbsp;<b> Ementa: </td></tr>-->
<!--      <tr><td colspan=2><blockquote>{$EMENTA        }</td></tr>-->

<!--      <tr><tr><td width='6'> &nbsp;</td><td><b> Descrição: </td></tr>-->
<!--      <tr><td colspan=2><blockquote>{$DESCRICAO}</blockquote></td></tr>-->

<!--      <tr><td colspan=2>&nbsp;<b> Observação: </td></tr>-->
<!--      <tr><td colspan=2><blockquote>{$OBSERVACAO    }</td></tr>-->

corpo;


        #Movendo para o proximo registro
        $RS->MoveNext();
      }
echo <<<corpo
        </TABLE>
        <br>
        <TABLE BORDER='0' WIDTH='500' CELLSPACING='0' CELLPADDING='6'>
          <TR>
            <TD VALIGN='middle' align='right' WIDTH='250'>

                <INPUT TYPE="hidden" NAME="content" VALUE="view/inscricao/inscricao_monografia_cadastro_dados_monografia.php">
                <INPUT TYPE='hidden' NAME='acao'    VALUE='Inserir'>
                <INPUT TYPE='SUBMIT' VALUE='Avançar'>
                &nbsp;&nbsp;
                <INPUT TYPE='RESET'  VALUE='Limpar'>
                </FORM>
            </TD>
            <TD VALIGN='middle' align='left'>
                <FORM NAME='exit' METHOD='POST' ACTION='index.php'>
                   <INPUT TYPE='hidden' VALUE='inscricao_trabalhos_inicio.php' NAME='content'>
                   <INPUT TYPE='hidden' VALUE='Voltar' NAME='acao'>
                   <INPUT TYPE='SUBMIT' VALUE='Voltar'>
                </FORM>

            </TD>
          </TR>
        </TABLE>
corpo;

    }

#-----------------------------------------------------------------------------------------
?>
      </fieldset>
    </td>
  </tr>
</table>
