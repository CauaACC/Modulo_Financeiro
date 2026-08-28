<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
$fws_['cod_trabalho'] = $fws_['cod_trabalho']==''? $_GET['cod_trabalho'] : $fws_['cod_trabalho'];
$debug = false;
?>
<h3>Área do Inscrito</h3>  
<div class="panel-group">
    <?php
    try {
        require_once 'inscricao_congresso_cabecalho.php';
        ?>
        <div class="panel panel-default">
                <div class="panel-heading"><h4>Envio de Trabalho - Selecionar Área Temática</h4></div>
                <div class="panel-body">
                <FORM method='POST' name='form'  action='index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php' onsubmit='return criticar()'>
                <input type="hidden" name="content" value="view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php">
                <input type="hidden" name="cod_trabalho" value="<?php echo $fws_['cod_trabalho']?>">
                <table border='0' width='100%' CELLSPACING='6' CELLPADDING='6' style="font-size: 13px;line-height: 2.429;">
                <?php
                $Conexao_db = FWA_Conexao("OpcTec");
                 #Definindo comando SQL
                  $var_sql = "
                             SELECT status,
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
                                cod_area_tematica
                             FROM tab_opcoes_areas_tematicas
                             WHERE (cod_evento  = ".COD_EVENTO.")
                               and (status  = 'ATIVADO')
                               and (periodo_inscricao_data_ini<=".$FWS_Data_Hoje.")
                               and (".$FWS_Data_Hoje."<=periodo_inscricao_data_fim)
                             ORDER BY ORDEM 
                            ";

                #Executando a consulta no banco SQL
            //    $Conexao_db->debug=true;
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
                    $COD_AREA_TEMATICA  = $RS->fields[19];

            If (@$bgcolorant=="#F4F4F4"){
               $bgcolor="#FFFFFF";
            }Else{
               $bgcolor="#F4F4F4";
            }
            $bgcolorant=$bgcolor;

            echo "            

            <!--      <tr><td colspan='2'><hr size='1' width='100%'></td></tr>-->
            <!--      <tr><td colspan='2'>&nbsp;<b>Código da AREA TEMATICA: {$COD_AREA_TEMATICA}</td></tr>-->
            <!--      <tr><td colspan='2'>&nbsp;<b> Status:  {$STATUS}</b></td></tr>-->

            <tr valign='top' bgcolor='{$bgcolor}'>
                <td width='10px'>
                    <INPUT TYPE='radio' NAME='cod_area_tematica' VALUE='{$COD_AREA_TEMATICA}'  style='margin:10px;'>
                </td><td width='100%'> {$TITULO} </td>
            </tr>

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
            ";


                    #Movendo para o proximo registro
                    $RS->MoveNext();
                  }
            ?>     
                </table>
                <br>
                <!--<div class='btn-group'>-->
                    <button class='btn btn-xs btn-primary' type="submit">Avançar</button>
                    <button class='btn btn-xs btn-primary' type="reset">Limpar</button>
                    <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"
                      class="btn btn-xs btn-primary" role="button">Voltar</a>
                <!--</div>-->
            </form>
            <?php
            }
       } catch (Exception $exc) {
           $_SESSION['MSG']=$exc->getMessage();
           echo "<a href='" . $url . "'>" . FWA_Mostrar_Mensagem($exc->getMessage()) . "</a>";
           if (!$debug) {
               header("Location: $url");
               echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
           }
       }
       ?>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
            <br>
        </div>
    </div>
</div>
