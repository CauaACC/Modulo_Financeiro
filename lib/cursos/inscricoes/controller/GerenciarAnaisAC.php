<?php

class GerenciarAnaisAC {
    public function carregarListaTrabalhos($fws_,$debug = false){
        
        $objTrabalho = new Trabalho();
        
        $condicoes['estado'] = $fws_['estado'];
        $condicoes['cod_area_tematica'] = $fws_['cod_area_tematica'];
        $condicoes['nome_inscrito']     = $fws_['nome_inscrito'];
        $condicoes['cod_inscrito']      = $fws_['cod_inscrito'];
        $condicoes['cod_trabalho']      = $fws_['cod_trabalho'];
        $condicoes['e_mail']            = $fws_['e_mail'];
        $condicoes['cpf']               = $fws_['cpf'];
        $condicoes['titulo_trabalho']   = $fws_['titulo_trabalho'];
        $condicoes['ordenar']           = $fws_['ordenar'];
        $condicoes['pagamentos']        = $fws_['pagamentos'];
        $condicoes['avaliacao']         = $fws_['avaliacao'];
        $condicoes['situacao']          = $fws_['situacao'];
        $condicoes['trabalhos']         = $fws_['trabalhos'];
        $condicoes['perspectiva']       = $fws_['perspectiva'];
        $condicoes['comunicado']        = $fws_['comunicado'];
        $condicoes['premiacao']         = $fws_['premiacao'];
        $condicoes['premiado']          = $fws_['premiado'];
        $condicoes['media']             = $fws_['tela'];

        if ($_SERVER['REMOTE_ADDR'] == '189.90.70.566') {
            $debug = true;
        }
        try {
            $objTrabalho->carregarListaTrabalhos($condicoes,$debug);
            $tituloAnt   = "";
            $inscritoAnt = "";
            $totalListados = 0;
            
            if(!$objTrabalho->getListaTrabalhos()){
                throw new Exception('Nenhum trabalho foi localizado de acordo com o filtro defindo acima.');
            }
            $this->arrayTotais = array();
            $areaTematicaAnterior = "";
            foreach ($objTrabalho->getListaTrabalhos() as $objL) {
                $totalListados++;
                if($emailAnterior != $objL->getEMail()){
                    $listaEmails = $listaEmails? $listaEmails.",<br/>" : "";
                    $listaEmails = $listaEmails.$objL->getEMail();
                }
                $emailAnterior = $objL->getEMail();
                
                if (($fws_['ordenar'] == 'area_tematica') and ($areaTematicaAnterior != $objL->getTituloAreaTematica())) {
                ?>
                <tr>
                    <td colspan="2"></td>
                    <td valign="top" colspan="5">
                        <h3><?php echo $objL->getTituloAreaTematica()?></h3>
                    </td>    
                </tr>
                <?php
                }
                $areaTematicaAnterior = $objL->getTituloAreaTematica();
                ?>
                <tr id="<?php echo  $objL->getCodTrabalho() ?>">
                    <td valign="top" width="120px">
                        <a name='<?php echo $objL->getCodInscrito()?>'>
                        <?php echo $objL->getCodInscrito()?> - <?php echo mb_strtolower($objL->getStatusInscrito())?>
                        <a title='Logar Como' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $objL->getCpf()?>&cod_inscrito=<?php echo $objL->getCodInscrito()?>&cod_evento=<?php echo COD_EVENTO?>'>
                            <img src='<?php echo WB_URL_IMG?>/icone_preview.gif' border='0'></a>

                        <br>
                        <a title="Editar" target="_blank" href='index.php?content=view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito=<?php echo $objL->getCodInscrito()?>' title="Abrir Inscrição">
                            <img src="<?php echo WB_URL_IMG?>/fig-alterar.gif" border="0"></a>
                        
                        <a title="Excluir" target="_blank" href='view/admin/inscritos-cadastro-excluir-grava.php?acao=Excluir&cpf=<?php echo $objL->getCpf()?>&cod_inscrito=<?php echo $objL->getCodInscrito()?>&cod_evento=<?php echo COD_EVENTO?>'>
                            <img src='<?php echo WB_URL_IMG?>/fig-excluir.gif' alt='Excluir' border='0'></a>
                        
                        <a title="Ativar" target="_blank" href='view/admin/inscritos-cadastro-ativar-grava.php?acao=Ativar&cpf=<?php echo $objL->getCpf()?>&cod_inscrito=<?php echo $objL->getCodInscrito()?>&cod_evento=<?php echo COD_EVENTO?>'>
                            <img src='<?php echo WB_URL_IMG?>/fig-ativar.gif' alt='Ativar' border='0'></a>
                        
                        <a title="Desativar" target="_blank" href='view/admin/inscritos-cadastro-desativar-grava.php?acao=Desativar&cpf=<?php echo $objL->getCpf()?>&cod_inscrito=<?php echo $objL->getCodInscrito()?>&cod_evento=<?php echo COD_EVENTO?>'>
                            <img src='<?php echo WB_URL_IMG?>/fig-desativar.gif' alt='Desativar' border='0'></a>
                        
                        
                        <div id="carregando<?php echo  $objL->getCodTrabalho() ?>" hidden="hidden"><img src="<?php echo  WB_PATH ?>/resources/image/loading-9.gif" width="20" height="20" border="0"></div>
                        <div id="msg-<?php echo $objL->getCodTrabalho()?>"></div>
                        
                    </td>
                    <td valign="top" width="100px">
                        <?php echo $objL->getCodTrabalho()?> - <?php echo $objL->getStatusTrabalho()==''? 'ativada' : mb_strtolower($objL->getStatusTrabalho())?>
                        <br>    
                        <a target="_blank"  href='index.php?content=view/admin/inscritos-trabalho-cadastro-alterar.php&cod_trabalho=<?php echo $objL->getCodTrabalho()?>' title="Abrir Trabalhos">
                            <img src="<?php echo WB_URL_IMG?>/fig-alterar.gif" border="0"></a>
                    </td>
                    <td valign="top">
                        <?php
                        $colorFontInscrito = "black";
                        if($objL->getNomeInscrito()==$inscritoAnt){
                            $colorFontInscrito = "red";
                        }
                        echo "<font color=".$colorFontInscrito."> <!--Nome Inscrito:<br> -->".$objL->getNomeInscrito()."</font>";
                        $inscritoAnt = $objL->getNomeInscrito();
                        ?>
                        
                        <br>E-mail: <a href="https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&to=<?php echo mb_strtolower($objL->getEMail())?>&subject=[<?php echo ABREV_EVENTO?>]" target="_blank"><?php echo mb_strtolower($objL->getEMail())?></a>
                        <br><a target='_blank' href='https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&su=[<?php echo ABREV_EVENTO?>]&body=Ola <?php echo mb_strtoupper($objL->getNomeInscrito())?>%0A%0A%0A%0Aatt. Comissão Organizadora%0A<?php echo ABREV_EVENTO?> - <?php echo NOME_EVENTO?>%0ADúvidas:<?php echo EMAIL_CONTATO?>&to=<?php echo  mb_strtolower($objL->getEMail()) ?>'>Enviar email pelo Gmail</a>
                        <?php
                        $colorFontTitulo = "black";
                        if($objL->getTituloTrabalho()==$tituloAnt){
                            $colorFontTitulo = "red";
                        }
                        
                        echo "<br><br><font color=".$colorFontTitulo."> <!--Titulo:<br>-->".$objL->getTituloTrabalho()."</font>";
                        $tituloAnt = $objL->getTituloTrabalho();

                        if($objL->getTituloAreaTematica()){
                            echo "<br><font color=".$colorFontTitulo."> Área Temática: ".$objL->getTituloAreaTematica()."</font>";
                        }else{
                            echo "<br><font color=red> Área Temática: Não cadastrada</font>";
                        }    
                        ?>
                        
                        <?php if ($fws_['perspectiva'] == 'detalhada') {?>
                            <br>Nomes dos Co-Autores:
                            <?php
                            echo $objL->getNomeInscrito()? "<br>1º - Co-autor: ".$objL->getNomeInscrito() : "";
                            echo $objL->getNomeCoAutor1()? "<br>2º - Co-autor: ".$objL->getNomeCoAutor1() : "";
                            echo $objL->getNomeCoAutor2()? "<br>3º - Co-autor: ".$objL->getNomeCoAutor2() : "";
                            echo $objL->getNomeCoAutor3()? "<br>4º - Co-autor: ".$objL->getNomeCoAutor3() : "";
                            echo $objL->getNomeCoAutor4()? "<br>5º - Co-autor: ".$objL->getNomeCoAutor4() : "";
                            echo $objL->getNomeCoAutor5()? "<br>6º - Co-autor: ".$objL->getNomeCoAutor5() : "";
                            echo $objL->getNomeCoAutor6()? "<br>7º - Co-autor: ".$objL->getNomeCoAutor6() : "";
                            ?>
                            <br>
                            <br>Instituição: <?php echo $objL->getInstituicaoTrabalho()?>
                            <br>Palavras-chave: <?php echo $objL->getPalavrasChave()?>
                            <br>Resumo: <p ALIGN='justify'><?php echo $objL->getResumoTrabalho()?></p>
                        <?php }?>
                        <?php    
                       if($objL->getArquivoGerado()){
                            echo "<br>Arquivo anexado: ";
                            //echo "<br>Arquivo anexado: ".$arquivo_upload;
            //                echo "<br>Arquivo Gravado: ".$arquivo_gerado;
                            //if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
                            echo "
                                <br><a href=\"javascript: visualiza('{$objL->getArquivoGerado()}');\"  title='Visualizar Resumo'>
                                    <img src='resources/image/doc.gif' border='0'>
                                    Visualizar Arquivo Resumo</a>
                                <br>
                            ";
                            if($objL->getArquivoGerado()==$arqGeradoAnterior){
                                echo "&nbsp;&nbsp;<font color=red><b>Arquivo igual ao anterior</b></font>";
                                $totalArqIgual++;
//                                if($emailArqIgual != $objL->getEMail()){
                                    $emailArqIgualListados = $emailArqIgualListados? $emailArqIgualListados.",\n" : "";
                                    $emailArqIgualListados = $emailArqIgualListados.$objL->getEMail();
                                    
                                    $arqIgualCodInscrito = $arqIgualCodInscrito? $arqIgualCodInscrito."," : "";
                                    $arqIgualCodInscrito = $arqIgualCodInscrito."<a href='#".$objL->getCodInscrito()."'>".$objL->getCodInscrito()."</a>";
//                                }
                            }
                            $arqGeradoAnterior = $objL->getArquivoGerado();
                            $emailArqIgual = $objL->getEMail();
                            //}
                        }else{
                            echo "<br><font color=red>Trabalho não anexado.</font>";
                        }
                        ?>
                    </td>
                    <td valign="top">
                        <?php
//                        if($objL->getAvaliacao()!=''){
//                            echo $objL->getAvaliacao();
//                        }else if ($objL->getAvaliacaoFinal()!=''){
//                            echo $objL->getAvaliacaoFinal();
//                        }else{
//                            
//                        }
                        //echo "->".$objL->getFlgEnviadoAvaliacao() ."<->" .$objL->getAvaliacao() ."<->" .$objL->getAvaliacaoFinal()."<-";
                        echo "<div id='msgUT-".$objL->getCodTrabalho()."'>";
                        echo "Sit.A Atual:".$objL->getUltimaTramitacao($this->arrayTotais);
                        echo "<br>Tramitação:".$objL->getAvaliacao();
                        echo "<br><br>E-mail avalidor: ".$objL->getEMailAvaliador();
                        if(@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_GERENCIAR_ADM_GERAL,5,false,false)){
                            echo "
                            <a title='Logar Como' href='javascript:void(0)' onclick='confirmarLogarComo(\"".addslashes($objL->getIdInclusao())."\")'>
                            <img src='resources/image/icone_preview.gif' border='0'></a>
                            ";
                        }
                        echo "<br>".formatDateView($objL->getDataTramitacao());
                        echo "</div>";
                        echo "<div id='msgTrabAval-".$objL->getCodTrabalho()."'></div>";
                        echo "<div id='msgTrabPrem-".$objL->getCodTrabalho()."'><b>".$objL->getDesFlgPremiado()."<br>".$objL->getTxtPremiacao()."</div>";
                        
                        if (FWA_Autenticacao($_SESSION['_cod_usuario'], 999, 5, false, false)) {
                            if($fws_['comunicado']==1){
                                if($objL->getAvaliacao()=='DEVOLVIDO'){
                                    echo "Comunicado devolução para correção: <br>";
                                    GerenciarEmailAvaliacaoAC::comunicadoDevolvido($objL,'MSG',false);
                                }
                            }
                            if($fws_['comunicado']==1){
                                if($objL->getAvaliacao()=='RE-SUBMETIDO'){
                                    echo "Comunicado retorno para avaliação:<br>";
                                    GerenciarEmailAvaliacaoAC::comunicadoReSubmetido($objL,'MSG',false);
                                }
                            }
                        }
                    ?>    
                    </td>
                    <td valign="top">
                    <?php
                        if($fws_['perspectiva'] != "premiacao"){
//                            if(@FWA_Autenticacao($_SESSION['_cod_usuario'],1,5,false,false)){    
//                            if($objL->getUltimaTramitacao()!='Em Avaliação' and $objL->getUltimaTramitacao()!='Submetido'){
                                echo "<a  href=\"javascript:void(0);\" onclick=\"enviarParaAvaliacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
                                class='btn btn-xs btn-primary' role='button' title='Enviar para Avaliação'>Enviar para Avaliação</a>";
//                            }
//                            if($objL->getUltimaTramitacao()!='Não submetido' and $objL->getUltimaTramitacao()!='Devolvido'){
                                echo "<br><a  href=\"javascript:void(0);\" onclick=\"retornarParaInscrito('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
                                class='btn btn-xs btn-primary' role='button' title='Retornar ao Aluno'>Retornar ao Aluno</a>";
//                            }
//                            if($objL->getCodAvaliador() or $objL->getUltimaTramitacao()=='Em Avaliação'){
                                echo "<br><a  href=\"javascript:void(0);\" onclick=\"liberarDeAvaliador('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."','".$objL->getCodAvaliador()."')\"
                                class='btn btn-xs btn-primary' role='button' title='Liberar de Avaliador'>Liberar de Avaliador</a>";
                                
                                echo "<br><a  href=\"javascript:void(0);\" onclick=\"aprovarDireto('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
                                class='btn btn-xs btn-primary' role='button' title='Aprovar Diretamente'>Aprovar</a>";

                                echo "<br><a  href=\"javascript:void(0);\" onclick=\"reprovarDireto('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
                                class='btn btn-xs btn-primary' role='button' title='Reprovar Diretamente'>Reprovar</a>";
//                            }
                        }else{  
                            if($objL->getPremiacao()!='' AND $objL->getPremiacao()!='NAO' AND $objL->getPremiacao()!=NULL){
                                echo '<select name="txt_premiacao" id="txt_premiacao_'.$objL->getCodTrabalho().'" >
                                <option></option>
                                <option>1º Lugar</option>
                                <option>2º Lugar</option>
                                <option>3º Lugar</option>
                                <option>4º Lugar</option>
                                <option value="">Remover Premiação</option>
                                </select>';
                                echo "<br><a  href=\"javascript:void(0);\" onclick=\"definirPremiacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
                                class='btn btn-xs btn-primary' role='button' title='Definir Premiaçao'>Definir Premiaçao</a>";
                            }
                        } 
//                            }
                            /*
                            <a id="btnAtivarInscrito" href="javascript:void(0);" onclick="alterarStatusInscrito(<?php echo  $objI->getCodInscrito() ?>, 'ATIVAR')" 
                            $objI->getStatus() == 'ATIVADO' ? "hidden=hidden" : "" >
                            Ativar Inscrição
                            </a> 
                            */
                       // }
                        ?>
                    </td>
                    <td valign="top">
                        <?php echo $objL->getStatus()?>
                    </td>
                    <td valign="top">
                        <?php echo $objL->getTotalPagamentos()?>
                        <br>R$ <?php echo  formatMoneyView($objL->getValorPagamentos()) ?>
                    </td>
                </tr>
                <?php
            }
            echo "<br><b>De acordo com o filtro acima:</b>";
            echo "<br>Total de trabalhos listados: ".$totalListados;
            if($totalArqIgual>0){
                echo "<br>Total de autores com arquivos de resumo iguais: ".$totalArqIgual." - Cód.Inscritos:".$arqIgualCodInscrito;
                echo "<br><a target='_blank' href='https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&to=sbmpcontato@gmail.com&bcc={$emailArqIgualListados}'>E-mail de autores listados abaixo com arquivos de resumo iguais.</a>";
            }
            echo "<br><a href='javascript:mostrarListaEmails();'>Listar de e-mails</a>";
            $this->mostrarListaEmails($listaEmails, $debug);

        } catch (Exception $ex) {

        }
    }
}

?>
