<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$FWA_Option_Dia    = FWA_Option_Dia();
$FWA_Option_Mes    = FWA_Option_Mes();
$FWA_Option_Ano    = FWA_Option_Ano(3);
$FWA_Option_Hora   = FWA_Option_Hora();
$FWA_Option_Minuto = FWA_Option_Minuto();

// if($_SERVER['REMOTE_ADDR'] == '189.90.70.56'){
//     error_reporting(E_ALL ^ E_NOTICE);
//     ini_set("display_errors", "On");
// }
#$STATUS="DESATIVADO";
#echo $status."<br>";

$FWA_Select_Inscrito = FWA_Select_Inscrito($cod_inscrito,$nome_inscrito,COD_EVENTO);

       
if($autor_apresentador_nome==''){
    $autor_apresentador_nome = $nome_inscrito;
}

       
if($autor_apresentador_nome==''){
    $autor_apresentador_nome = $nome_inscrito;
}

echo "
<TABLE WIDTH='100%' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
<TR><TD>Status Trabalho:
    <select name='status'>";
    echo CarregarOptionsAC::status($objTrabalho->getStatus(), $debug);
            
echo "</select>
        </TD>
        </TR>";
                
if($flg_enviado_avaliacao==1){
    $descFlgEnviadoAvaliacao = "SIM";
}else{
    $descFlgEnviadoAvaliacao = "NÃO";
}
                
        echo "
        <TR><TD>Enviado para Avaliação : {$descFlgEnviadoAvaliacao}
            <br>&nbsp;
<!--
              <select name='flg_enviado_avaliacao'>
                <option value='1'>NÃO</option>
                <option value=''>SIM</option>
              </select>
-->
            </TD>
        </TR>
        ";
        echo "<TR><TD VALIGN='top'>";
        ?>
    <div class="panel panel-default">
        
        <div class="panel-heading"><h4>Cadastro de <?php echo $objEvConf->getDescTrabAnexoTipo() ?></h4></div>
        <div class="panel-body"> 
            <?php if ($obj->getCodTrabalho()) {?>
            <label for="titulo_trabalho">Código do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>: <?php echo  $obj->getCodTrabalho() ?></label>
            <br>
            <?php }?>
            <?php
            if($fws_['msg']){
                FWA_Mostrar_Mensagem($fws_['msg']);
            }
            ?> 
            <!--<div class='btn-group'>-->
            <?php if ($naoMostrar) { ?>
                <?php if($OP_AVALIACAO_ENVIAR_AO_SALVAR){ ?>
                    <button class='btn btn-xs btn-primary' type="submit">Salvar e Enviar para Avaliação</button>
                <?php }else {?>
                    <button class='btn btn-xs btn-primary' type="submit">Salvar <?php echo $objEvConf->getDescTrabAnexoTipo() ?></button>
                <?php } ?>
                <button class='btn btn-xs btn-primary' type="reset">Limpar</button>
                <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"
                       class="btn btn-xs btn-primary" role="button" style="color: #fff">Voltar</a>
                <!--</div>-->
                <br>
            <?php } ?>
            <?php
            if($objI->getStatusProcesso()=='INSCRITO'){
                // if ($objEvConf->getTrabComplementoForm()==1) {
                //     echo '<div class="form-group col-sm-12" style="padding-left: 0px;padding-top:10px">';
                //     echo $objEvConf->getTrabComplementoFormText();
                //     echo '</div>';
                // } 
            }
            ?>
            <?php if ($objEvConf->getTrabTitulo()==1){ ?>
                <br>
                <label for="titulo_trabalho">Titulo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>:</label>
                <input type="text" id="titulo_trabalho" name="titulo_trabalho" value="<?php echo  $obj->getTituloTrabalho() ?>"
                       class="form-control" placeholder="Titulo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>" required autofocus>
                <br>
            <?php } ?>           
            <?php if($objEvConf->getTrabFormatoTexto()==true){
                ?>
                <br>
                <label for="format_texto">Tipo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>:</label>
                <select class='form-control' name='formato_texto' id='formato_texto' required autofocus>
                    <option class='form_campos' value='' selected>Informe Tipo de Trabalho</option>
                    <?php
                    $fws_['formato_texto'] = $obj->getFormatoTexto();
                    CarregarOptionsAC::formatoTexto($fws_);
                    ?>
                </select>
            <?php } ?>        
            <?php if($objEvConf->getTrabPremiacao()==true){?>
                <br>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="premiacao">Deseja concorrer ao PRÊMIO<?php echo $obj->getTrabPremiacaoTitulo()? " ".$objEvConf->getTrabPremiacaoTitulo() : "" ?>? </label> 
                </div>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    <div class="col-sm-6">
                        <input type='radio' name='premiacao' value='NAO'
                        <?php echo verifItemChecked($obj->getPremiacao(), 'NAO') ?> >
                        Não desejo participar.
                    </div>
                    <div class="col-sm-6">
                        <input type='radio' name='premiacao' value='SIM'
                        <?php echo verifItemChecked($obj->getPremiacao(), 'SIM') ?> >
                        Sim, desejo participar.
                    </div>
                </div>
            <?php }?>
            <?php if(COD_EVENTO==1) {?>
                <br>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="premiacao">Deseja concorrer ao <b>Prêmio Jovens Melhoristas</b>? </label> 
                </div>
                <div class="form-group col-sm-3" style="padding-left: 0px;">
                    <input type='radio' name='premiacao' value='NAO'         <?php echo verifItemChecked($obj->getPremiacao(), 'NAO') ?> >  Não desejo participar.
                    <!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='premiacao' value='1-PREMIACAO' <?php echo verifItemChecked($obj->getPremiacao(), '1-PREMIACAO') ?> >-->
                </div>
                <div class="form-group col-sm-3" style="padding-left: 0px;">
                    <input type='radio' name='premiacao' value='2-PREMIACAO' <?php echo verifItemChecked($obj->getPremiacao(), '2-PREMIACAO') ?> >
                    desejo participar na categoria Iniciação Científica.
                </div>
                <div class="form-group col-sm-3" style="padding-left: 0px;">
                    <input type='radio' name='premiacao' value='3-PREMIACAO' <?php echo verifItemChecked($obj->getPremiacao(), '3-PREMIACAO') ?> >
                    desejo participar na categoria Dissertação de Mestrado.
                </div>
                <div class="form-group col-sm-3" style="padding-left: 0px;">
                    <input type='radio' name='premiacao' value='4-PREMIACAO' <?php echo verifItemChecked($obj->getPremiacao(), '4-PREMIACAO') ?> >
                    desejo participar na categoria Tese de Doutorado.
                </div>
            <?php }?>
            <?php if($objEvConf->getTrabEixoTematico()==1){ ?>
                <br>
                <label for="cod_area_tematica">Eixos Temáticos:
                    <?php if(COD_EVENTO==2){?>
                    Iluminando caminhos para o futuro da saúde:
                    <?php }?>
                </label>
                <select class='form-control' name='cod_area_tematica' id='cod_area_tematica' required autofocus>
                    <option class='form_campos' value='' selected>Selecione uma Eixos Temáticos</option>
                    <?php 
                    $fws_['cod_area_tematica'] = $obj->getCodAreaTematica();
                    CarregarOptionsAC::areasTematicas($fws_);
                    ?>
                </select>
                <br>
            <?php } ?>
            <?php if ($objEvConf->getTrabNumCoautores()>0) { ?>
                <label >Nomes dos Co-Autores: </label>
                (O número total de autores, incluindo o autor principal, não deve exceder <?php echo $objEvConf->getTrabNumCoautores() ?>)
                <?php if ($objEvConf->getCodEvento()==70) { ?>
                    Apresentador: 
                    <input type="text" id="nome_co_autor_<?php echo $i ?>" name="autor_apresentador_nome" value="<?php echo $obj->getAutorApresentadorNome(); ?>"
                    class="form-control" placeholder="Autor Apresentador">
                <?php }else{ ?>
                    <br>&nbsp;&nbsp;&nbsp;1º Autor/Apresentador - <?php echo $nome_inscrito?>
                <?php } ?>
                <?php                    
                for ($i = 1; $i < $objEvConf->getTrabNumCoautores(); $i++) {
                ?>
                    <input type="text" id="nome_co_autor_<?php echo $i ?>" name="nome_co_autor_<?php echo $i ?>" value="<?php echo  call_user_func([$obj, "getNomeCoAutor" . $i]) ?>"
                    class="form-control" placeholder="<?php echo $i+1 ?>º Co-autor">
                <?php
                }
                ?>
            * nomes idênticos serão descartados
            <br>
            <?php } ?>
            <?php if ($objEvConf->getTrabInstituicao()==1) { ?>
                <br>
                <label>Instituição:</label>
                <input type="text" id="instituicao_trabalho" name="instituicao_trabalho" value="<?php echo  $obj->getInstituicaoTrabalho() ?>"
                       class="form-control" placeholder="Instituição" required autofocus>
                <br>
            <?php } ?>
            <?php if ($objEvConf->getTrabPalavrasChave()==1) { ?>
                <label>Palavras-chave: </label> (máximo 3 palavras-chave, separadas por um sinal de 'ponto e virgula', ou seja ';')
                <input type="text" id="palavras_chave" name="palavras_chave" value="<?php echo  $obj->getPalavrasChave() ?>"
                       class="form-control" placeholder="Palavras chave" required autofocus>             
                <br>
            <?php } ?>
            <?php if(TRABALHO_ANEXA_RESUMO and ($objEvConf->getTrabAvaliacaoPermitirRecurso()!=1)){?>
                <br>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    Tipo do Documento: 
                    <!-- <input type="text" value="<?php echo $tipo_documento ?>" name="tipo_documento" size="60"> -->
                    <select name="tipo_documento">
                        <option class='form_campos' value='' selected>Informe Tipo de Documento</option>
                        <?php
                        $fws_['tipo_documento'] = $obj->getTipoDocumento();
                        if($fws_['tipo_documento']==''){
                            $fws_['tipo_documento'] = 'RPA e COMPROVANTE DE TRANSFERÊNCIA';
                        }
                        CarregarOptionsAC::tipoDocumento($fws_, $debug);
                        ?>
                    </select>
                </div>
                <label>Período Referência: (ex. para RPA -> "<?php echo getMonthName(date('m')).' '. date('Y'); ?>")</label>
                <?php
                // if($obj->getPeriodoReferencia()==''){
                //     $obj->setPeriodoReferencia('Ref: 11/2024 - 12/2024');
                // }
                ?>
                <input type="text" id="periodo_referencia" name="periodo_referencia" value="<?php echo  $obj->getPeriodoReferencia() ?>"
                       class="form-control" placeholder="Período Referência" autofocus size="40">             
                <br>
                <label>Permitir Edição</label>
                    <input type="radio" name="flg_permitir_alterar" value="1" <?= verifItemChecked($obj->getFlgPermitirAlterar(), '1', false) ?> /> Sim
                        &nbsp;&nbsp;
                        <input type="radio" name="flg_permitir_alterar" value="0" <?= verifItemChecked($obj->getFlgPermitirAlterar(), array('', 0, false), false) ?> /> Não            
                    &nbsp;&nbsp;&nbsp;&nbsp;
                Período até qual dia: 
                <input type='date' name='flg_permitir_alterar_data_fim' value='<?php echo $obj->getFlgPermitirAlterarDataFim() ?>' onBlur="trim(this);">           
                    <br>
                <?php //if ($objEvConf->getTrabAnexoTipo()=='Termo de Concessão de Bolsa') {?>
                <label>O documento está assinado?</label>
                <br>
                    <input type="radio" name="flg_assinado" value="1" <?= verifItemChecked($obj->getFlgAssinado(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="flg_assinado" value="-1" <?= verifItemChecked($obj->getFlgAssinado(), array('-1', 0), false) ?> /> Não
                <br>
                <br>
                <!-- <h4>Anexar Arquivo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>: </h4> -->
                <h4>Anexar Documento: </h4>
                <input type="file" name="arquivo" id="arquivo" value="<?php echo  $obj->getArquivo() ?>"
                       placeholder="Inserir arquivo    ">             
                    <span style="font-size:12px;">Extensões de arquivo permitidas são: <?php echo $objEvConf->getTrabArquivoExtensao()!=''?  $objEvConf->getTrabArquivoExtensao() : 'doc, docx ou rtf.';?></span>
                <br>
                <?php //}?>
            <?php }?>
            <?php if(TRABALHO_ANEXA_RESUMO_FORMULARIO and ($objEvConf->getTrabAvaliacaoPermitirRecurso()!=1)){?>
                <?php
                if(COD_EVENTO==11){
                    $maxCaracTrabalho = 4000;
                }else{
                    $maxCaracTrabalho = 2500;
                }
                ?>
                <br>
                <h4>Resumo: </h4> (máximo <?php echo $maxCaracTrabalho ?> caracteres, com espaços)
                <br><font id=Digitado color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id=Restante color=red><?php echo $maxCaracTrabalho ?></font>
                <textarea id="resumo_trabalho" name="resumo_trabalho" rows='15' cols='80' maxlength='<?php echo $maxCaracTrabalho ?>'
                          onkeyup='MaxCaracter(this, <?php echo $maxCaracTrabalho ?>)' onkeypress='MaxCaracter(this, <?php echo $maxCaracTrabalho ?>)'
                          class="form-control" placeholder="Resumo Trabalho" required autofocus><?php echo  $obj->getResumoTrabalho() ?></textarea>
                <br>
            <?php }?>
            <?php
            if ($objEvConf->getTrabResumoReferencia()==1 and ($objEvConf->getTrabAvaliacaoPermitirRecurso()!=1)){
                $maxCaracTrabalhoRef = 2000;
                ?>
                <h4>Referências: </h4> (máximo <?php echo $maxCaracTrabalhoRef ?> caracteres, com espaços)
                <br><font id="DigitadoRef" color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id="RestanteRef" color=red><?php echo $maxCaracTrabalhoRef ?></font>
                <textarea id="referencia_trabalho" name="referencias_trabalho" rows="15" cols="80" maxlength="<?php echo $maxCaracTrabalhoRef ?>"
                          onkeyup="MaximoCaracter(this, <?php echo $maxCaracTrabalhoRef ?>,'DigitadoRef','RestanteRef')" onkeypress="MaximoCaracter(this, <?php echo $maxCaracTrabalhoRef ?>,'DigitadoRef','RestanteRef')"
                          class="form-control" placeholder="Referências Trabalho" required autofocus><?php echo  $obj->getReferenciasTrabalho() ?></textarea>
                <br>
            <?php }?>
            <?php
            if ($objEvConf->getTrabAvaliacaoPermitirRecurso()==1){
                $maxCaracTrabalhoRecurso = 2000;
                ?><br>
                <h4>Solicitação de Recurso:</h4>
                <lable>Descreva abaixo os argumentos com os quais contesta a decissão sobre sua inscrição?</label>
                (máximo <?php echo $maxCaracTrabalhoRecurso ?> caracteres, com espaços)
                <br><font id="DigitadoRecurso" color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id="RestanteRecurso" color=red><?php echo $maxCaracTrabalhoRecurso ?></font>
                <textarea id="recurso_trabalho" name="recurso_trabalho" rows="15" cols="80" maxlength="<?php echo $maxCaracTrabalhoRecurso ?>"
                          onkeyup="MaximoCaracter(this, <?php echo $maxCaracTrabalhoRecurso ?>,'DigitadoRecurso','RestanteRecurso')" onkeypress="MaximoCaracter(this, <?php echo $maxCaracTrabalhoRecurso ?>,'DigitadoRecurso','RestanteRecurso')"
                          class="form-control" placeholder="Digite suas argumentações para o recurso aqui" required autofocus><?php echo  $obj->getRecursoTrabalho() ?></textarea>
                <br>
                <br><lable>Deseja realmente solicitar recurso?</label>
                <br>
                <input type="radio" name="recurso_trabalho_solicitar" value="1" <?= verifItemChecked($obj->getRecursoTrabalhoSolicitar(), '1', false) ?> /> Sim, Confirmo essa solicitação de recurso
                <br>
                <input type="radio" name="recurso_trabalho_solicitar" value="0" <?= verifItemChecked($obj->getRecursoTrabalhoSolicitar(), array('', 0, false), false) ?> /> Não, não quero solicitar recurso
                <br>
            <?php }?>
               
                <br>
            <!--<div class='btn-group'>-->
                <!--<button class='btn btn-xs btn-primary' type="submit">Avançar</button>-->
                <?php if($OP_AVALIACAO_ENVIAR_AO_SALVAR){ ?>
                    <button class='btn btn-xs btn-primary' type="submit">
                        Salvar e Enviar para Avaliação
                    </button>
                <?php }else {?>
                    <button class='btn btn-xs btn-primary' type="submit">
                        <?php echo $objEvConf->getTxtBtnSalvarTrabalho() ?>
                    </button>
                <?php } ?>
                <button class='btn btn-xs btn-primary' type="reset">Limpar</button>
<!--                <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"
                   class="btn btn-xs btn-primary" role="button" style="color: #fff">Voltar</a>-->
            <!--</div>-->
            </div>
        </div>
        <?php
        echo "</TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
              <!--Anexar Arquivo do Trabalho:<br><FONT FACE='verdana' SIZE=1 COLOR='#555555'>(Extensões de arquivo permitidas são: doc, docx)-->
              <br>&nbsp;
          </TD>
        </TR>";
        echo " 
        <TR>
          <TD VALIGN='top'>";
            
                if($arquivo_gerado){
                    echo "Arquivo anexado: ";
                    echo "<a href=\"javascript: visualiza('{$arquivo_gerado}');\"  title='Visualizar Resumo'>";
                    echo retornarIconeExtensao($arquivo_gerado);
                    echo " Visualizar Arquivo </a><br>";

                }else{
                    echo "<br><font color=red>Anexe o arquivo do trabalho.</font>";
                }
            
            echo "
          </TD>
        </TR>
        ";     

//        echo " 
//        <TR>
//          <TD VALIGN='top'>";
//            
//                if($arquivo_gerado){
//                    echo "Arquivo anexado: ";
//                    echo "
//                        <a href=\"javascript: visualiza('{$arquivo_gerado}');\"  title='Visualizar Resumo'>
//                            <img src='resources/image/doc.gif' border='0'>
//                            Visualizar Arquivo Resumo</a>
//                        <br>
//                    ";
//                }else{
//                    echo "<br><font color=red>Anexe o arquivo do trabalho.</font>";
//                }
//            
//            echo "<INPUT CLASS='form_campos' TYPE='file' NAME='arquivo' ID='arquivo' value='{$arquivo}' SIZE='60' maxlength='255'>
//          </TD>
//        </TR>
//        ";
            
        /*arquivo sem identificação*/
        if ($objEvConf->getTrabResumoArquivoSemIdentificacao()){ 
       echo "     
       <TR>
         <TD VALIGN='top'>";
           
               if($arquivo_identificado_gerado){
                   echo "Arquivo identificado anexado: ";
                   echo "
                       <a href=\"javascript: visualiza('{$arquivo_identificado_gerado}');\"  title='Visualizar'>
                           <img src='resources/image/doc.gif' border='0'>
                           Visualizar Arquivo identificado</a>
                       <br>
                   ";
               }else{
                   echo "<br><font color=red>Anexe o arquivo identificado.</font>";
               }
           
           echo "<INPUT CLASS='form_campos' TYPE='file' NAME='arquivo_identificado' ID='arquivo_identificado' value='' SIZE='60' maxlength='255'>
         </TD>
       </TR>
       ";
        }
        echo " 
        <TR>
          <TD COLSPAN='2'>&nbsp;</TD>
        </TR>
       </TABLE>
       <br>&nbsp;";
