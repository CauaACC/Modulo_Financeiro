<?php

class ListarTrabalhosAC {
    
    public function __construct() {
        /*Acrescente os titulos do trabalhos que faltaram para serem marcados no formulário*/
        $titulosFaltaram = "";
    }
    
    
    public function carregarCondicoes($fws_, $debug = false) {
        $condicoes['estado'] = $fws_['estado'];
        $condicoes['cidade'] = $fws_['cidade'];
        $condicoes['cod_area_tematica'] = $fws_['cod_area_tematica'];
        $condicoes['nome_inscrito']     = $fws_['nome_inscrito'];
        $condicoes['cod_inscrito']      = $fws_['cod_inscrito'];
        $condicoes['cod_trabalho']      = $fws_['cod_trabalho'];
        $condicoes['palavras_chave']    = $fws_['palavras_chave'];
        $condicoes['cod_autor']         = $fws_['cod_autor'];
        $condicoes['e_mail']            = $fws_['e_mail'];
        $condicoes['cpf']               = $fws_['cpf'];
        $condicoes['titulo_trabalho']   = $fws_['titulo_trabalho'];
        $condicoes['titulo_texto_trabalho']   = $fws_['titulo_texto_trabalho'];
        $condicoes['co_autor']          = $fws_['co_autor'];
        $condicoes['ordenar']           = $fws_['ordenar'];
        $condicoes['pagamentos']        = $fws_['pagamentos'];
        $condicoes['avaliacao']         = $fws_['avaliacao'];
        $condicoes['cod_avaliador']     = $fws_['cod_avaliador'];
        $condicoes['formato_texto']     = $fws_['formato_texto'];
        $condicoes['situacao']          = $fws_['situacao'];
        $condicoes['trabalhos']         = $fws_['trabalhos'];
        $condicoes['perspectiva']       = $fws_['perspectiva'];
        $condicoes['comunicado']        = $fws_['comunicado'];
        $condicoes['premiacao']         = $fws_['premiacao'];
        $condicoes['premiado']          = $fws_['premiado'];
        $condicoes['flg_assinado']          = $fws_['flg_assinado'];
        $condicoes['tipo_documento']          = $fws_['tipo_documento'];
        $condicoes['consultaPresenca']          = $fws_['consultaPresenca'];
        $condicoes['dataInclusaoAnexo'] = $fws_['dataInclusaoAnexo'];
        $condicoes['verificarArqAnexo'] = $fws_['verificarArqAnexo'];
        $condicoes['consultaAnexoArqApresentacao'] = $fws_['consultaAnexoArqApresentacao'];
        $condicoes['consultaAnexoArqVideo']        = $fws_['consultaAnexoArqVideo'];
        $condicoes['consultaPremiados']          = $fws_['consultaPremiados'];
        $condicoes['patrocinador_voucher']          = $fws_['patrocinador_voucher'];
        $condicoes['tela']              = $fws_['tela'];
        $condicoes['media']             = $fws_['tela'];
        $condicoes['nome_sala']              = $fws_['nome_sala'];
        
        $condicoes['situacao_inscrito']       = $fws_['situacao_inscrito'];
        $condicoes['presenca_inscrito']       = $fws_['presenca_inscrito'];
        $condicoes['outros_filtros_inscrito'] = $fws_['outros_filtros_inscrito'];
        
        $condicoes['campos_exportar'] = $fws_['campos_exportar'];
        return $condicoes;
    }
    
    public function executarAcoesEmLote($objL, $fws_, $debug = false){
        
        if (FWA_Autenticacao($_SESSION['_cod_usuario'], 999, 5, false, false)) {
            
            if($fws_['acaoEmLote']=='enviarComunicados'){
                
            //    if($fws_['comunicado']==1){
            //        if($objL->getAvaliacao()=='APROVADO'){
            //            echo "Comunicado Aprovação: <br>";
            //            GerenciarEmailAvaliacaoAC::comunicadoDevolvido($objL,'MSG',false);
            //        }
            //    }
            //     if($fws_['comunicado']=='trabalhoAprovacao'){
            //         if($objL->getAvaliacao()=='APROVADO'){
            //             echo "<br>Comunicado aprovação: <br>";
            //             GerenciarEmailAvaliacaoAC::comunicadoAprovacao($objL,'MSG',false);
            //         }
            //     }
            //     if($fws_['comunicado']=='trabalhoReprovado'){
            //         if($objL->getAvaliacao()=='REPROVADO'){
            //             echo "<br>Comunicado reprovação: <br>";
            //             GerenciarEmailAvaliacaoAC::comunicadoReprovacao($objL,'MSG',false);
            //         }
            //     }
            //    if($fws_['comunicado']==1){
            //        if($objL->getAvaliacao()=='DEVOLVIDO'){
            //            echo "Comunicado devolução para correção: <br>";
            //            GerenciarEmailAvaliacaoAC::comunicadoDevolvido($objL,'MSG',false);
            //        }
            //    }
            //    if($fws_['comunicado']==1){
            //        if($objL->getAvaliacao()=='RE-SUBMETIDO'){
            //            echo "Comunicado retorno para avaliação:<br>";
            //            GerenciarEmailAvaliacaoAC::comunicadoReSubmetido($objL,'MSG',false);
            //        }
            //    }
            //    if($fws_['comunicado']=='certificadoLiberacao'){
            //        echo "Comunicado certificado liberado:<br>";
            //        GerenciarCertifiadoAC::comunicadoCertificadoLiberado($objL,'MSG',false);
            //    }

                if($fws_['comunicado']!=0){
                    echo "Código do Template: ".$fws_['comunicado']."<br>";
                    if($fws_['email_bcc']!=''){
                        echo "Com cópia para: ".$fws_['email_bcc']."<br>";
                    }
                    GerenciarEmailComunicadoAC::corpoNovoModelo($objL,$fws_, 'MSG',false);
                }
            }

            if($fws_['acaoEmLote']=='definirPresenca'){
                /*definir string ou array contendo titulos que faltaram, marcar falta ou presença*/
                $tituloComparar = substr(maiusculo($objL->getTituloTrabalho()),0,55);
                if(stristr(maiusculo($this->titulosFaltaram), "|".$tituloComparar."")){
                     GerenciarTrabalhoAC::definirPresenca($objL,'falta',false);
                }else{
                     GerenciarTrabalhoAC::definirPresenca($objL,'presenca',false);
                }
            }
        }
    }
    
    public function carregarXls($fws_, $debug = false) {

        $objTrabalho = new Trabalho();

        $condicoes = $this->carregarCondicoes($fws_,$debug);
        $condicoes['media'] = 'xls';
        $codEvento = $fws_['cod_evento'];
        if($codEvento!=''){
            $codEvento = str_pad($codEvento, 4, '0', STR_PAD_LEFT);
            $nomeArquivo = "lista-trabalhos-".$codEvento;
        }else{
            $nomeArquivo = "lista-trabalhos";
        }
        if ($fws_['cod_area_tematica']!=''){
            $objAreaTematica = new AreaTematica();
            $objAreaTematica->setCodAreaTematica($fws_['cod_area_tematica']);
            $objAreaTematica->getAreaTematicaById();
            $nomeArquivo = $nomeArquivo.'-area-tematica-'.lpad($objAreaTematica->getOrdem(),2,0);
        }
        if ($fws_['patrocinador_voucher']!=''){
            $nomeArquivo = $nomeArquivo.'-vouchers-'.$fws_['patrocinador_voucher'];
        }
        try {
            
            $objTrabalho->carregarListaTrabalhos($condicoes, $debug);
            gerarArquivoTable2Excel($nomeArquivo, $objTrabalho->getColunasListaTrabalhos(), $objTrabalho->getListaTrabalhos(),$debug);
            
        } catch (Exception $ex) {
            
        }
    }
    public function carregarZip($fws_, $debug = false){
//        ControleAcesso::ifAnyGranted ('gerenciarEventos,coordenarEventos,coordenarEventosPlus','url');
//        Perspectiva::setPerfilViewByLocalFile(__FILE__);

        $objTrabalho = new Trabalho();

        $condicoes = $this->carregarCondicoes($fws_,$debug);
        $condicoes['media'] = 'xls';
        $condicoes['ordenar'] = 'area_tematica';
        $codEvento = $fws_['cod_evento'];
        if($codEvento!=''){
            $objTrabalho->setCodEvento($codEvento);
            $objEv = $objTrabalho->getEvento();
            $codEvento = str_pad($codEvento, 4, '0', STR_PAD_LEFT);
            $nomeArquivo = "lista-trabalhos-".$codEvento;
        }else{
            $nomeArquivo = "lista-trabalhos";
        }
        if ($fws_['cod_area_tematica']!=''){
            $objAreaTematica = new AreaTematica();
            $objAreaTematica->setCodAreaTematica($fws_['cod_area_tematica']);
            $objAreaTematica->getAreaTematicaById();
            $nomeArquivo = $nomeArquivo.'-area-tematica-'.lpad($objAreaTematica->getOrdem(),2,0);
            $fws_['titulo'] = $objAreaTematica->getTitulo();
        }
        if ($fws_['patrocinador_voucher']!=''){
            $nomeArquivo = $nomeArquivo.'-vouchers-'.$fws_['patrocinador_voucher'];
        }
        try {
            if(!$fws_['listar']){
                throw new Exception('Clique em aplicar filtros para listar trabalhos.');
            }
            $objTrabalho->carregarListaTrabalhos($condicoes, $debug);
            $listaTrabalhos = $objTrabalho->getListaTrabalhos();
            $conta_trabalho=count($listaTrabalhos);
        } catch (Exception $exc) {
            $listaTrabalhos = null;
            $msgLista = $exc->getMessage();
        }
        /* Não carrega relatório sem aplicação de filtro */
        if (!$listaTrabalhos) {
            echo "Nenhum trabalho foi encontrado";
        } else {
    //       if(in_array($objEv->getCodEvento(), array('61'))){
    //            throw new Exception("Evento não liberado para donwload.");
    //       }
            gerarArquivoZip($nomeArquivo, $fws_, $objEv, $listaTrabalhos);

        }        
    }
    public function carregarListaTrabalhos($fws_,$debug = false){
        
        $objTrabalho = new Trabalho();
        $objEvConf = $objTrabalho->getObjEventoConfiguracao(COD_EVENTO);

        /* Configuracao lida do banco. O $objEvConf acima vem do singleton, que nesta
         * tela chega sem popular (getInstance nao le a tabela) — por isso a instancia
         * propria aqui, sem mexer no singleton para nao alterar o resto da tela. */
        $objConfEvento = new EventoConfiguracao();
        $objConfEvento->setCodEvento(COD_EVENTO);
        $objConfEvento->getEventoConfiguracaoById();

        $condicoes = $this->carregarCondicoes($fws_,$debug);
        $condicoes['media'] = $fws_['tela'];

        if ($_SERVER['REMOTE_ADDR'] == '191.179.175.128' or $_SERVER['REMOTE_ADDR'] == '189.35.130.2366' 
        or $_SERVER['REMOTE_ADDR'] == '189.90.70.566') {
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
            
            $arrayEmails = array();
            foreach ($objTrabalho->getListaTrabalhos() as $objL) {
                if (TRABALHO_ANEXA_RESUMO) {
                    if($condicoes['verificarArqAnexo']!=''){ 
                        $existArquivoGerado=0;
                        if($objL->getArquivoGerado()){
                            $existArquivoGerado = verificarArquivoExiste(WB_PATH_LOG_TRABALHO.$objL->getArquivoGerado());
                        }
                        // // anexaram
                        if($existArquivoGerado==0 and $condicoes['verificarArqAnexo']=='anexaram'){
                            continue; // Pula quem não anexou
                        }

                        // // nao_anexaram
                        if($existArquivoGerado==1 and $condicoes['verificarArqAnexo']=='nao_anexaram'){
                            continue; // Pula quem anexou
                        }                            
                    }
                }

                $totalListados++;
                if (!in_array($objL->getEMail(), $arrayEmails)){
                    array_push($arrayEmails,$objL->getEMail());
                }

                if (($fws_['ordenar'] == 'area_tematica' or $fws_['ordenar']=='nome_sala') and ($areaTematicaAnterior != $objL->getTituloAreaTematica())) {
                ?>
                <?php if ($fws_['perspectiva']!='avaliacao'){ ?>
                <tr>
                    <td valign="top" colspan="5">
                        <h3><?php echo $objL->getTituloAreaTematica()?></h3>
                    </td>    
                </tr>
                <?php } ?>
                <?php
                }
                $areaTematicaAnterior = $objL->getTituloAreaTematica();
                ?>
                <tr id="<?php echo  $objL->getCodTrabalho() ?>">
                    <?php if ($fws_['perspectiva']!='avaliacao'){ ?>
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
                        <div id="txtInscritoIsento-<?php echo $objL->getCodInscrito() ?>">
                            <?php
                            if($objL->getCodIsento()){
                                echo "Cód.Isento: ".$objL->getCodIsento()."<br>";
                            }
                            ?>   
                        </div>
                        <div id="txtInscritoVoucher-<?php echo $objL->getCodInscrito() ?>">
                            <?php
                            if($objL->getCodVoucher()){
                                echo "Cód.Voucher: ".$objL->getCodVoucher()."<br>";
                            }
                            ?>   
                        </div>
                        <?php
                        echo $objL->getImpressaoCracha()? "<br>Retirou crachá" : "";
//                        echo $objL->getFlgPresencaInscrito()? "<br>Presente como Inscrito" : "";
//                        if(!$objL->getImpressaoCracha() and !$objL->getFlgPresencaInscrito()){
//                            echo "presença não confirmada";
//                        }
                        ?>
                        <?php
                        
                        $checkedPresencaInscrito = $objL->getFlgPresencaInscrito() ? "checked" : "";
                        echo "<input id='presencaInscrito-".$objL->getCodInscrito()."' class='presenca' style='float: left;'"
                                . " onclick=\"javascript:alternarPresencaInscrito(".$objL->getCodInscrito().");\""
                                . " title='Marcar Presença/Ausência'"
                                . $checkedPresencaInscrito
                                . " name='presenca' style='display: inline-block;' type='checkbox'>";
                        echo "<div id='statusPresencaInscrito-".$objL->getCodInscrito()."' style='float: left;margin-left:10px;'>Presença Inscrito</div><br><br>";
                        ?>
                        <?php if (($objL->getStatus()=='ATIVADA') and ($objEvConf->getTrabSubmissao()==1)){ ?>
                            <?php //if ($OP_SUBMISSAO_TRABALHO_ENCERRADA==true){ ?>
                            <form class="form-signin" name="form" method="POST" target="_blank"
                                action="<?php echo WB_URL_COMMAND?>">
                                <input type="hidden" name="c" value="GerenciarEmail">
                                <input type="hidden" name="m" value="enviarAvisoTrabalho">
                                <button class="btn btn-xs btn-primary btn-lembrar-envio-trabalho" type="submit">Lembrar Envio de Trabalho</button>
                                <?php echo  mb_strtolower($objL->getEMail()) ?>
                                <input type="hidden" name="codInscrito" value="<?php echo $objL->getCodInscrito()?>">
                            </form>
                            <?php //} ?>
                        <?php } ?>
                        <br><a target='_blank' href='<?php echo "https://api.whatsapp.com/send?phone=".limpaFoneWhats($objL->getFoneContatoDdd().$objL->getFoneContato())."&text=Olá ".$objL->getPreNome() ?>'>Olá com Whats</a>
                    </td>
                    <td valign="top" width="100px">
                        <?php echo $objL->getCodTrabalho()?> - <?php echo $objL->getStatusTrabalho()==''? 'ativada' : mb_strtolower($objL->getStatusTrabalho())?>
                        <br>    
                        <a target="_blank"  href='index.php?content=view/admin/inscritos-trabalho-cadastro-alterar.php&cod_trabalho=<?php echo $objL->getCodTrabalho()?>' title="Abrir Trabalhos">
                            <img src="<?php echo WB_URL_IMG?>/fig-alterar.gif" border="0"></a>
                        
                        <br>
                        <?php
                        if($objL->getNomeSala()){
                            echo "Sala: ".$objL->getNomeSala();
                        }
                        ?>
                        <?php
                            $checkedFlgAssinado = $objL->getFlgAssinado() ? "checked" : "";
                            echo "<input id='flgAssinado-".$objL->getCodTrabalho()."' class='assinado' style='float: left;'"
                                    . " onclick=\"alternarFlgAssinado('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\""
                                    . " title='Marcar Assinado/Não assinado'"
                                    . $checkedFlgAssinado
                                    . " name='flgAssinado' style='display: inline-block;' type='checkbox'>";
                            echo "<div id='statusFlgAssinado-".$objL->getCodTrabalho()."' style='float: left;margin-left:10px;'>Assinado</div><br><br>";
                        ?>                        
                    </td>
                    <?php } ?>
                    <td valign="top">
                        <?php
                        $colorFontInscrito = "black";
                        if($objL->getNomeInscrito()==$inscritoAnt){
                            $colorFontInscrito = "red";
                        }
                        if ($fws_['perspectiva']!='avaliacao'){ 
                            echo "<font color=".$colorFontInscrito."> <!--Nome Inscrito:<br> -->".$objL->getNomeInscrito()."</font>";
                            $inscritoAnt = $objL->getNomeInscrito();
                            ?>
                            
                            <br>E-mail: <a href="https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&to=<?php echo mb_strtolower($objL->getEMail())?>&subject=[<?php echo ABREV_EVENTO?>]" target="_blank"><?php echo mb_strtolower($objL->getEMail())?></a>
                            <br><a target='_blank' href='https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&su=[<?php echo ABREV_EVENTO?>]&body=Ola <?php echo mb_strtoupper($objL->getNomeInscrito())?>%0A%0A%0A%0Aatt. Comissão Organizadora%0A<?php echo ABREV_EVENTO?> - <?php echo NOME_EVENTO?>%0ADúvidas: <?php echo EMAIL_CONTATO;?>&to=<?php echo  mb_strtolower($objL->getEMail()) ?>'>Enviar email pelo Gmail</a>
                            <br>fone: <?php echo  $objL->getFoneContatoDdd(). " " .$objL->getFoneContato() ?> - 
                            <a target='_blank' href='<?php echo "https://api.whatsapp.com/send?phone=".limpaFoneWhats($objL->getFoneContatoDdd().$objL->getFoneContato())."&text=Olá ".$objL->getPreNome() ?>'>Whats</a>
                           <?php } ?>                     
                        <?php
                        $colorFontTitulo = "black";
                        if($objL->getTituloTrabalho()==$tituloAnt){
                            $colorFontTitulo = "red";
                        }
                        /*definir string ou array contendo titulos que faltaram e marcalos para executar ação*/
                        $tituloComparar = substr(maiusculo($objL->getTituloTrabalho()),0,35);
                        if(stristr(strtoupper($this->titulosFaltaram), "|".$tituloComparar."")){
                             $colorFontTitulo = "red";
                             $faltaram = $faltaram+1;
                        }

                        if($objL->getTituloTrabalho()==$tituloAnt and $objL->getTituloTrabalho()!=''){
                            echo "&nbsp;&nbsp;<font color=red><b>titulo igual ao anterior</b></font>";
                            $totalTituloIgual++;
    //                                if($emailArqIgual != $objL->getEMail()){
                                $emailArqIgualListados = $emailArqIgualListados? $emailArqIgualListados.",\n" : "";
                                $emailArqIgualListados = $emailArqIgualListados.$objL->getEMail();

                                $arqIgualCodInscrito = $arqIgualCodInscrito? $arqIgualCodInscrito."," : "";
                                $arqIgualCodInscrito = $arqIgualCodInscrito."<a href='#".$objL->getCodInscrito()."'>".$objL->getCodInscrito()."</a>";
    //                                }
                        }
                        echo "<br><br><font color=".$colorFontTitulo."> <!--Titulo:<br>-->".$objL->getTituloTrabalho()."</font>";
                        $tituloAnt = $objL->getTituloTrabalho();

                        if($objL->getTituloAreaTematica()){
                            echo "<br><font color=".$colorFontTitulo."> Eixo: ".$objL->getTituloAreaTematica()."</font>";
                        }else{
                            if ($objEvConf->getTrabEixoTematico()==1) {
                                echo "<br><font color=red> Eixo: Não cadastrada</font>";
                            }
                        }    
                        echo "<br>Forma Apresentação: ".$objL->getFormatoApresentacao();
                        echo "<br>Formato Texto: ".$objL->getFormatoTexto();
                        ?>
                        <?php //if ($fws_['acaoEmLote']=='') {?>
                            <?php if (($fws_['perspectiva'] == 'detalhada')or($fws_['perspectiva'] != 'avaliacao')) {?>
                                <br>Nomes dos Co-Autores:
                                <?php
                                if ($objEvConf->getCodEvento()==70) {
                                    echo $objL->getAutorApresentadorNome()? "<br>Co-autor apresentador: ".$objL->getAutorApresentadorNome() : "";
                                    echo $objL->getNomeCoAutor1()? "<br>1º - Co-autor: ".$objL->getNomeCoAutor1() : "";
                                    echo $objL->getNomeCoAutor2()? "<br>2º - Co-autor: ".$objL->getNomeCoAutor2() : "";
                                    echo $objL->getNomeCoAutor3()? "<br>3º - Co-autor: ".$objL->getNomeCoAutor3() : "";
                                    echo $objL->getNomeCoAutor4()? "<br>4º - Co-autor: ".$objL->getNomeCoAutor4() : "";
                                    echo $objL->getNomeCoAutor5()? "<br>5º - Co-autor: ".$objL->getNomeCoAutor5() : "";
                                    echo $objL->getNomeCoAutor6()? "<br>6º - Co-autor: ".$objL->getNomeCoAutor6() : "";
                                    echo $objL->getNomeCoAutor7()? "<br>7º - Co-autor: ".$objL->getNomeCoAutor7() : "";
                                    echo $objL->getNomeCoAutor8()? "<br>8º - Co-autor: ".$objL->getNomeCoAutor8() : "";
                                }else{
                                    echo $objL->getNomeInscrito()? "<br>1º - Co-autor: ".$objL->getNomeInscrito() : "";
                                    echo $objL->getNomeCoAutor1()? "<br>2º - Co-autor: ".$objL->getNomeCoAutor1() : "";
                                    echo $objL->getNomeCoAutor2()? "<br>3º - Co-autor: ".$objL->getNomeCoAutor2() : "";
                                    echo $objL->getNomeCoAutor3()? "<br>4º - Co-autor: ".$objL->getNomeCoAutor3() : "";
                                    echo $objL->getNomeCoAutor4()? "<br>5º - Co-autor: ".$objL->getNomeCoAutor4() : "";
                                    echo $objL->getNomeCoAutor5()? "<br>6º - Co-autor: ".$objL->getNomeCoAutor5() : "";
                                    echo $objL->getNomeCoAutor6()? "<br>7º - Co-autor: ".$objL->getNomeCoAutor6() : "";
                                    echo $objL->getNomeCoAutor7()? "<br>8º - Co-autor: ".$objL->getNomeCoAutor7() : "";
                                    echo $objL->getNomeCoAutor8()? "<br>9º - Co-autor: ".$objL->getNomeCoAutor8() : "";
                                }
                                ?>
                                <br>
                                <br>Instituição: <?php echo $objL->getInstituicaoTrabalho()?>
                                <br>Palavras-chave: <?php echo $objL->getPalavrasChave()?>
                                <?php
                                $colorFontresumo = "black";
                                if($objL->getResumoTrabalho() == $colorFontresumo){
                                    $colorFontresumo = "red";
                                }
                                ?>
                                <?php if (TRABALHO_ANEXA_RESUMO_FORMULARIO) { ?>
                                <br>Resumo: <p ALIGN='justify'><font color="<?php echo $colorFontresumo?>">
                                    <?php
                                    $resumoAnt = $objL->getResumoTrabalho();
                                    echo $objL->getResumoTrabalho();
                                    ?></font></p>
                                <?php } ?>
                            <?php }?>
                        <?php //}?>
                        <?php   
                        
                        if(COD_EVENTO=='2'){
                            if($objL->getFormatoTexto() == ''){
                                echo "<br><br>Formato do texto não definido.";
                            }  else {
                                echo "<br><br>Formato do texto: ".$objL->getFormatoTexto();;
                            }
                        }

                        echo "<br><br>";
                        if($objL->getCodArquivoApresentacao()!=''){
                            echo "<img src='".WB_URL_IMG."/pdf.gif'>&nbsp;&nbsp;&nbsp;";
                            echo "<a href=\"javascript: getApresentacao('{$objL->getArquivoGeradoApresentacao()}');\"  title='Visualizar Apresentação'>";
                            echo "<font color=".$colorFontTitulo." size=3>Visualizar Apresentação</font>";
                            echo "</a>";
                            
                            echo "<span style='color: #4e4b4b;font-size: 11px;line-height: 1;'>";
                            echo "<br>Anexado em: ".formatDateView($objL->getDataInclusaoArqApresentacao());
                            echo "</span><br>";
                            
                        }else{
                            if($fws_['perspectiva'] != 'avaliacao'){ 
                                echo "Aguardando anexar apresentação";
                            }
                        }
                        if($objL->getCodArquivoVideo()!=''){
                            echo "<img src='".WB_URL_IMG."/camera.gif'>&nbsp;&nbsp;&nbsp;";
                            echo "<a href=\"javascript: getApresentacaoVideo('{$objL->getArquivoGeradoVideo()}');\"  title='Visualizar Apresentação em Video'>";
                            echo "<font color=".$colorFontTitulo." size=3>Visualizar Video</font><br>";
                            echo "</a>";
                        }

                        if (TRABALHO_ANEXA_RESUMO) {
                            if($objL->getArquivoGerado()){
                                echo "<br>Arquivo anexado: ";
                                //echo "<br>Arquivo anexado: ".$arquivo_upload;
                //                echo "<br>Arquivo Gravado: ".$arquivo_gerado;
                                //if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
                                    // $pathArquivoGerado = WB_PATH_LOG_TRABALHO.$objL->getArquivoGerado();
                                    // $existArquivoGerado=0;
                                    // if (file_exists($pathArquivoGerado)) {
                                    //     $existArquivoGerado=1;
                                    // }
                                    // if($existArquivoGerado==0){
                                    //     echo "<span style='color:red;font-size:14pt;'>Arquivo ".strtolower($objEvConf->getDescTrabAnexoTipo())." não encontrado - tente enviar novamente.</span><br>";
                                    // }
                                    // if($existArquivoGerado==1){
                                // if($fws_['verificarArqAnexo']!='' and $existArquivoGerado==0){
                                //     echo "<span style='color:red; font-weight:bold;'>Arquivo não encontrado</span>";
                                // }
                                echo "<br><a href=\"javascript: visualiza('{$objL->getArquivoGerado()}');\"  title='Visualizar Resumo.'>";
                                echo retornarIconeExtensao($objL->getArquivoGerado());
                                echo " Visualizar Arquivo </a><br>";
                                    // }
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
                                if($objL->getArquivoIdentificadoGerado()){
                                    echo "<br><a href=\"javascript: visualiza('{$objL->getArquivoIdentificadoGerado()}');\"  title='Visualizar Resumo Identificado'>";
                                    echo retornarIconeExtensao($objL->getArquivoGerado());
                                    echo " Visualizar Arquivo Identificado</a><br>";
                                }                                
                            }else{
                                echo "<br><font color=red>Trabalho não anexado.</font>";
                            }
                        }
                        ?><br>&nbsp;
                    </td>
                    <?php //if ($fws_['acaoEmLote']=='') {?>
                    <td valign="top">
                        <?php   
                        if($objL->getPremiacao() == 'SIM'){
                            echo "Quer Participar da Premiação: <b>".$objL->getPremiacao()."</b>";
                        }
                        if (($fws_['perspectiva'] == "administracao") or ($fws_['perspectiva'] == "detalhada")  or ($fws_['perspectiva'] == "avaliacao")){

    //                        if($objL->getAvaliacao()!=''){
    //                            echo $objL->getAvaliacao();
    //                        }else if ($objL->getAvaliacaoFinal()!=''){
    //                            echo $objL->getAvaliacaoFinal();
    //                        }else{
    //                            
    //                        }
    //                        echo "->".$objL->getFlgEnviadoAvaliacao() ."<->" .$objL->getAvaliacao() ."<->" .$objL->getAvaliacaoFinal()."<-";
                            echo "<div id='msgUT-".$objL->getCodTrabalho()."'>";
                            echo "<!--Sit.A Atual:-->".$objL->getUltimaTramitacao($this->arrayTotais);
                            echo "<br>Tramitação:";
                            if ($objL->getAvaliacao()=='REPROVADO') {
                                echo "<font color=red>".$objL->getAvaliacao()."</font>";
                            }else{
                                if($objL->getAvaliacao()!=''){
                                    echo $objL->getAvaliacao();
                                }else{
                                    echo 'não tramitado';
                                }
                            }
                            if(($objL->getAvaliacao()!='' and $objL->getAvaliacao()!='SELECIONADO')or($_SERVER['REMOTE_ADDR']=='189.90.70.56')){
                                echo "<br>Critérios de Avaliação:";
                                echo GerenciarAvaliacaoPadraoAC::mostrarCriterios($objL);
                            }else{
                                echo "<br>";
                                echo "<font color=red>Aguardando avaliação</font>";
                            }
                            echo $objL->getAlterarAreaTematica()!=''? "<br><b>Alterar Eixo Temático</b>" : "";
                            echo "<br>Indicar para premiação: ";
                            echo $objL->getIndicarPremiacao()=='1'? "SIM" : "NÃO";
                            echo "<br><b>Parecer do Avaliador:</b>";                           
                            if ($objL->getObservacao() != '') {
                                echo "<p align=justify>".$objL->getObservacao()."</p>";
                            }else{
                                echo "<br>Nenhum comentário foi inserido.";
                            }
                            
                            echo "<br>E-mail avaliador (CA:".$objL->getCodAvaliador()."):<br>";
                            echo $objL->getEMailAvaliador()!=""? $objL->getEMailAvaliador() : "<font color=red>e-mail do avaliador não encontrado</font>";
                            if((@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_GERENCIAR_ADM_GERAL,5,false,false)) and $objL->getEMailAvaliador()!=""){
                                echo "
                                <a title='Logar Como' href='javascript:void(0)' onclick='confirmarLogarComo(\"".addslashes($objL->getIdInclusao())."\")'>
                                <img src='resources/image/icone_preview.gif' border='0'></a>
                                ";
                            }
                            echo "<br>".formatDateView($objL->getDataTramitacao());
                            /*Calculando atraso na avaliação*/
                            $dateInterval    = "";
                            $cobrarAvaliador = "";
                            if((@FWA_Autenticacao($_SESSION['_cod_usuario'],enumSistema::SIS_GERENCIAR_ADM_GERAL,5,false,false)) and $objL->getEMailAvaliador()!=""){
                            $cobrarAvaliador = "1";
                            }
                            if($objL->getAvaliacao()=='SELECIONADO'){
                                $data_inicio = new DateTime($objL->getDataTramitacao());
                                $data_fim    = new DateTime(Date('Y-m-d H:i:s'));
                                $dateInterval = $data_inicio->diff($data_fim);
                                if ($dateInterval->days >= 7) {
                                    echo "<br><font color=red>".$dateInterval->days." dias de comunicado</font><br>";
                                    $avaliacaoAtrasada++;
                                    $cobrarAvaliador = 1;
                                }   
                            }        
                            echo "</div>";
                            echo "<div id='msgTrabAval-".$objL->getCodTrabalho()."'></div>";
                            echo "<div id='msgTrabPrem-".$objL->getCodTrabalho()."'><b>".$objL->getDesFlgPremiado()."<br>".$objL->getTxtPremiacao()."</div>";

                            if( $objL->getDataUltimoAcessoAvaliador()!=''){
                                echo "Ultimo Acesso Avaliador:<br> ".formatDateView($objL->getDataUltimoAcessoAvaliador());
                            }                        
                        }
                        if(Configuracao::DEFINIR_PREMIACAO and $fws_['perspectiva']=='avaliacaoPremiacao'){
                            echo "<div id='msgUT-".$objL->getCodTrabalho()."'>";
//                            echo "<!--Sit.A Atual:-->".$objL->getUltimaTramitacao($this->arrayTotais);
//                            echo "<br>Tramitação:".$objL->getAvaliacao();
//                            echo "<br><br>E-mail avalidor:<br>".$objL->getEMailAvaliador();
//                            echo "<br>".formatDateView($objL->getDataTramitacao());
                            
                            /*Calculando atraso na avaliação*/
                            if($objL->getAvaliacao()=='SELECIONADO'){
                                GerenciarAvaliacaoPremiacaoAC::imprimirDiasEmAtraso($objL);
                                $objL->getAvaliacaoPremiacaoAtrasada()?  $avaliacaoAtrasadaPremiacao++ : "";
                            }
                            echo "</div>";
                            echo "<div id='msgTrabAval-".$objL->getCodTrabalho()."'></div>";
                            echo "<div id='msgTrabPrem-".$objL->getCodTrabalho()."'><b>".$objL->getDesFlgPremiado()."<br>".$objL->getTxtPremiacao()."</b></div>";

                            GerenciarAvaliacaoPremiacaoAC::carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho($objL,$debug);
                            
                        }
                        /** Ações em lote */
                        // if (($fws_['perspectiva'] == "administracao") or ($fws_['perspectiva'] == "simplificada")) {
                        //     $this->executarAcoesEmLote($objL, $fws_,$debug);
                        // }                        
                        ?>    
                    </td>
                    <td valign="top">
                    <?php
                    @$SIS_ADM_AVALIACAO_TRABALHOS = 4;
                    @$cod_msg = false;
                    @$redirect = false;
                    If (FWA_Autenticacao($_SESSION['_cod_usuario'], $SIS_ADM_AVALIACAO_TRABALHOS, 5, $cod_msg, $redirect)) {
                        
                        echo "<div id='carregando-".$objL->getCodTrabalho()."' class='hidden' hidden='hidden'>"; 
                        echo "<img src='".WB_URL_IMG."/loading-9.gif' width='20' height='20' border='0'>";
                        echo "</div>";

                        if (($fws_['perspectiva'] == "administracao") or ($fws_['perspectiva'] == "detalhada")  or ($fws_['perspectiva'] == "avaliacao")){
                               
                                if ($fws_['perspectiva'] != "avaliacao"){
                                    $checkedPresenca = $objL->getFlgPresenca() ? "checked" : "";
                                    echo "<input id='presenca-".$objL->getCodTrabalho()."' class='presenca' style='float: left;'"
                                            . " onclick=\"alternarPresenca('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\""
                                            . " title='Marcar Presença/Ausência'"
                                            . $checkedPresenca
                                            . " name='presenca' style='display: inline-block;' type='checkbox'>";
                                    echo "<div id='statusPresenca-".$objL->getCodTrabalho()."' style='float: left;margin-left:10px;'>Presença</div><br><br>";
                                }
//                            if(@FWA_Autenticacao($_SESSION['_cod_usuario'],1,5,false,false)){    
                            if($objL->getStatusInscrito()=='ATIVADA' and $objL->getStatusTrabalho()=='ATIVADA'){
//                            if($objL->getUltimaTramitacao()!='Em Avaliação' and $objL->getUltimaTramitacao()!='Submetido'){
                                if(Configuracao::ESPECIFICAR_AVALIADOR){
                                    echo "<button type=\"button\" class=\"btn btn-xs btn-primary\" data-toggle=\"modal\" data-target=\"#modalIndicarAvaliador\""
                                      . " title=\"Indicar Avaliador\" onclick=\"indicarAvaliador('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\">"
                                      . "Inidicar Avaliador</button>";
                                }
                                if(Configuracao::PERMITIR_ENVIAR_A_QUALQUER_AVALIADOR){
                                    echo "<a  href=\"javascript:void(0);\" onclick=\"enviarParaAvaliacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
                                    class='btn btn-xs btn-primary' role='button' title='Enviar para Avaliação'>Enviar para Avaliação</a>";
                                }
                                if(Configuracao::PERMITIR_RETORNAR_AO_ALUNO){
                                    echo "<br><a  href=\"javascript:void(0);\" "
                                    . "onclick=\"retornarParaInscrito('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."','".$objL->getUltimaTramitacao()."')\" "
                                    . "class='btn btn-xs btn-primary' role='button' title='Retornar ao Aluno'>Retornar ao Aluno</a>";
                                }
//                            if($objL->getCodAvaliador() or $objL->getUltimaTramitacao()=='Em Avaliação'){
                                echo "<br><a disable href=\"javascript:void(0);\" onclick=\"liberarDeAvaliador('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."','".$objL->getCodAvaliador()."')\"
                                class='btn btn-xs btn-primary' role='button' title='Trocar/Remover de Avaliador'>Trocar/Remover do Avaliador</a>";
                                
//                            if($objL->getCodAvaliador() or $objL->getUltimaTramitacao()=='Em Avaliação'){
                                if($cobrarAvaliador){
                                        echo "<br><a disable href=\"javascript:void(0);\" onclick=\"cobrarAvaliador('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."','".$objL->getCodAvaliador()."')\"
                                        class='btn btn-xs btn-primary' role='button' title='Cobrar Avaliador'>Cobrar Avaliador</a>";
                                }
                                if($objConfEvento->getTrabAprovarDireto()==1){
                                    echo "<br>";
                                    echo "<a  href=\"javascript:void(0);\" onclick=\"aprovarDireto('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
                                    class='btn btn-xs btn-primary' role='button' title='Aprovar Diretamente'>Aprovar</a>";
                                    echo "&nbsp;&nbsp;";
                                    echo "<a  href=\"javascript:void(0);\" onclick=\"reprovarDireto('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
                                    class='btn btn-xs btn-primary' role='button' title='Reprovar Diretamente'>Reprovar</a>";
                                }
                                //if ($objL->getIndicarPremiacao()=='1'){
                                if ($fws_['perspectiva']!='avaliacao'){ 
                                    echo "<button type=\"button\" class=\"btn btn-xs btn-primary\" data-toggle=\"modal\" data-target=\"#modalIndicarAvaliador\""
                                    . " title=\"Alterar Ind. Premiação Oficial\" onclick=\"alterarIndicadoComissao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\">"
                                    . "Alterar Indicação a Premiação</button>";
                                }
                                //}
//                            }
                            }else{
                                echo "Para avaliar este trabalho é necessário ativar o inscrito ou o trabalho";
                            }
                        }
                        if(Configuracao::DEFINIR_PREMIACAO and $fws_['perspectiva']=='avaliacaoPremiacao'){
                            
/*Definição de Premição removida por segurança*/
//                            if($objL->getPremiacao()!='' AND $objL->getPremiacao()!='NAO' AND $objL->getPremiacao()!=NULL){
//                                echo '<hr><select name="txt_premiacao" id="txt_premiacao_'.$objL->getCodTrabalho().'" >
//                                <option></option>
//                                <option>1º Lugar</option>
//                                <option>2º Lugar</option>
//                                <option>3º Lugar</option>
//                                <option>4º Lugar</option>
//                                <option value="">Remover Premiação</option>
//                                </select>';
//                                echo "<br><a  href=\"javascript:void(0);\" onclick=\"definirPremiacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
//                                class='btn btn-xs btn-primary' role='button' title='Definir Premiaçao'>Definir Premiaçao</a><hr>";
//                            }
//                            //Por área temática
//                            if($objL->getPremiacao()!='' AND $objL->getPremiacao()!='NAO' AND $objL->getPremiacao()!=NULL){

                                $txtComplementarPremiacao = $objL->getTituloAreaTematica().' - '.$objL->getFormatoTexto();
                                echo '<hr><select name="txt_premiacao" id="txt_premiacao_'.$objL->getCodTrabalho().'" >
                                <option '.verifItemSelected($objL->getTxtPremiacao(), '').'></option>
                                <option '.verifItemSelected($objL->getTxtPremiacao(), '1º Lugar - '.$txtComplementarPremiacao).'>1º Lugar - '.$txtComplementarPremiacao.'</option>
                                <option '.verifItemSelected($objL->getTxtPremiacao(), '2º Lugar - '.$txtComplementarPremiacao).'>2º Lugar - '.$txtComplementarPremiacao.'</option>
                                <option '.verifItemSelected($objL->getTxtPremiacao(), '3º Lugar - '.$txtComplementarPremiacao).'>3º Lugar - '.$txtComplementarPremiacao.'</option>
                                <option '.verifItemSelected($objL->getTxtPremiacao(), '4º Lugar - '.$txtComplementarPremiacao).'>4º Lugar - '.$txtComplementarPremiacao.'</option>
                                <option '.verifItemSelected($objL->getTxtPremiacao(), 'Menção Honrosa').'>Menção Honrosa</option>
                                <option value="">Remover Premiação</option>
                                </select>';
                                echo "<br><a  href=\"javascript:void(0);\" onclick=\"definirPremiacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
                                class='btn btn-xs btn-primary' role='button' title='Definir Premiaçao'>Definir Premiaçao</a><hr>";
//                            }
//                            if($objL->getPremiacao()!='' AND $objL->getPremiacao()!='NAO' AND $objL->getPremiacao()!=NULL){
//                                echo '<hr><select name="txt_premiacao" id="txt_premiacao_'.$objL->getCodTrabalho().'" >
//                                <option></option>
//                                <option>1º Lugar - Experiência Exitosa</option>
//                                <option>2º Lugar - Experiência Exitosa</option>
//                                <option>3º Lugar - Experiência Exitosa</option>
//                                <option>4º Lugar - Experiência Exitosa</option>
//                                <option value="">Remover Premiação</option>
//                                </select>';
//                                echo "<br><a  href=\"javascript:void(0);\" onclick=\"definirPremiacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\"
//                                class='btn btn-xs btn-primary' role='button' title='Definir Premiaçao'>Definir Premiaçao Experiência Exitosa</a><hr>";
//                            }
/*Fim Definição de Premição removida por segurança*/
                            
                            if($objL->getStatusInscrito()=='ATIVADA' and $objL->getStatusTrabalho()=='ATIVADA'){
//                                echo "<button type=\"button\" class=\"btn btn-xs btn-primary\" data-toggle=\"modal\" data-target=\"#modalIndicarAvaliador\""
//                                . " title=\"Indicar Avaliador Premiacao\" onclick=\"indicarAvaliadorPremiacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\">"
//                                . "Inidicar Avaliador Premiacao</button>";
                                
//                                echo "<br><a disable href=\"javascript:void(0);\" onclick=\"cobrarAvaliadorPremiacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."','".$objL->getCodAvaliador()."')\"
//                                class='btn btn-xs btn-primary' role='button' title='Cobrar Avaliador Premiacao'>Cobrar Avaliador Premiacao</a>";
                                
//                                echo "<br><a disable href=\"javascript:void(0);\" onclick=\"liberarDeAvaliadorPremiacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."','".$objL->getCodAvaliador()."')\"
//                                class='btn btn-xs btn-primary' role='button' title='Trocar de Avaliador Premiacao'>Trocar de Avaliador Premiacao</a>";
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
                    }
                    ?>
                    </td>
                    <?php //} ?>
                    <td valign="top">
                        <?php echo $objL->getStatus()?>          
                    </td>
                    <?php if ($fws_['perspectiva']!='avaliacao'){ ?>
                        <td valign="top">
                            <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], $COD_SIS_PAINEL_BORDO, 5, $cod_msg, $redirect)) { ?>
                                Qtd.Pag.: <?php echo $objL->getTotalPagamentos()?>
                                <?php if ($objL->getTotalPagamentos() > 0){?>
                                <br>
                                <br>Valor Total<br> R$ <?php echo  formatMoneyView($objL->getValorPagamentos()) ?>
                                <?php
                                }
                            }
                            if($objL->getCodIsento() != ''){
                                echo "<br>Isento";
                            }
                            if($objL->getCodVoucher() != ''){
                                echo "<br>Voucher:".$objL->getCodVoucher();
                            }
                            ?>
                        </td>
                    <?php } ?>
                </tr>
                <?php
                /**
                 * Ações em lote
                 */
                if($fws_['acaoEmLote']=='enviarComunicados'){ 
                    if (FWA_Autenticacao($_SESSION['_cod_usuario'], 999, 5, false, false)){
                        // echo "<tr><td colspan=5>";
                        $objAcoes = new ListarInscritosAcoesAC();
                        $objAcoes->executarAcoesEmLote($objL, $fws_,$debug);
                        // echo "</td></tr>";
                    }            
                }
                if($fws_['acaoEmLote']=='definirPresenca'){
                    GerenciarTrabalhoAC::definirPresenca($objL,'presenca',false);
                }
                
                if($fws_['acaoEmLote']=='compactarVideos' or $fws_['acaoEmLote']=='montarListagemVideo'){
                    if($objL->getArquivoGeradoVideo()!=''){
                        $arrayFileVideo[$objL->getCodTrabalho()] = $objL->getArquivoGeradoVideo();
                        $arrayFilePdf[$objL->getCodTrabalho()] = $objL->getArquivoGeradoApresentacao();
                        $arrayTitulo[$objL->getCodTrabalho()] = $objL->getTituloTrabalho();
                        $arrayInscrito[$objL->getCodTrabalho()] = $objL->getNomeInscrito();
                        $arrayIndice[] = $objL->getCodTrabalho();
                    }
                }
                if($fws_['acaoEmLote']=='compactarApresentacoes'){
                    if($objL->getArquivoGeradoApresentacao()!=''){
                        $arrayFileApresentacao[$objL->getCodTrabalho()] = $objL->getArquivoGeradoApresentacao();
                    }
                }                
            }#fim lista trabalhos
            
            /*Ações em lote*/
            if($fws_['acaoEmLote']=='montarListagemVideo'){
                $objCompactar = new GerenciarUploadAC();
                $fileName = 'arquivoVideo';
                if($fws_['cod_area_tematica']!=''){
                    $objA = new AreaTematica();
                    $objA->setCodAreaTematica($fws_['cod_area_tematica']);
                    $objA->getAreaTematicaById();
//                    $fileName = 'area-tematica-'.$objA->getDesOrdem().'-'.$fileName;
                    $fileName = 'eixo-tematico-'.$objA->getPrefixo().'';
                }
                $objCompactar->montarListagemVideo($arrayIndice,$fileName, $arrayFileVideo,$arrayTitulo,$arrayInscrito);               
            }
            if($fws_['acaoEmLote']=='compactarVideos'){
                $objCompactar = new GerenciarUploadAC();
                $fileName = 'arquivoVideo';
                if($fws_['cod_area_tematica']!=''){
                    $objA = new AreaTematica();
                    $objA->setCodAreaTematica($fws_['cod_area_tematica']);
                    $objA->getAreaTematicaById();
                    $fileName = 'area-tematica-'.$objA->getDesOrdem().'-'.$fileName;
                    $fileName = 'area-tematica-'.$objA->getDesOrdem().'';
                }
                $objCompactar->compactar($fileName, $arrayFileVideo);
            }
            if($fws_['acaoEmLote']=='compactarApresentacoes'){
                $objCompactar = new GerenciarUploadAC();
                $fileName = 'arquivoApresentacao';
                if($fws_['cod_area_tematica']!=''){
                    $fileName = 'area-rematica-'.$fws_['cod_area_tematica'].'-'.$fileName;
                }
                $objCompactar->compactar($fileName, $arrayFileApresentacao);
            }
            if($fws_['perspectiva'] == 'avaliacao'){
                echo "<br><b>De acordo com o filtro acima:</b>";
            }
            echo "<br>Total de trabalhos listados: ".$totalListados;
            
            if($avaliacaoAtrasada>=1){
                echo "<br>Total de avaliações atrasadas: ".$avaliacaoAtrasada;
            }
            if($faltaram>=1){
                echo "<br>Total de trabalhos faltaram: ".$faltaram;
            }
            if($totalArqIgual>0){
                echo "<br>Total de autores com arquivos de resumo iguais: ".$totalArqIgual." - Cód.Inscritos:".$arqIgualCodInscrito;
                echo "<br><a target='_blank' href='https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&to=sbmpcontato@gmail.com&bcc={$emailArqIgualListados}'>E-mail de autores listados abaixo com arquivos de resumo iguais.</a>";
            }
            if($totalTituloIgual>0){
                echo "<br>Total de autores com titulos de resumos iguais: ".$totalTituloIgual." - Cód.Inscritos:".$arqIgualCodInscrito;
                echo "<br><a target='_blank' href='https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&to=sbmpcontato@gmail.com&bcc={$emailArqIgualListados}'>E-mail de autores listados abaixo com arquivos de resumo iguais.</a>";
            }
            if($fws_['perspectiva'] != 'avaliacao'){
                echo "<br><a href='javascript:mostrarListaEmails();'>Listar de e-mails</a>";
                foreach ($arrayEmails as $value) {
                    $listaEmails = $listaEmails.$value.",<br/>";
                }
                $this->mostrarListaEmails($listaEmails, $debug);
            }

//            print_r($arrayFileApresentacao);
//            print_r($arrayFileVideo);
//            echo __LINE__.'-';

        } catch (Exception $ex) {

        }
    }
    public function carregarListaTrabalhosAnaisNovo($fws_, $debug = false) {
    
        $objTrabalho = new Trabalho();
        $objEvConf = $objTrabalho->getObjEventoConfiguracao();
        
        $condicoes = $this->carregarCondicoes($fws_, $debug);
        $condicoes['cache'] = 1000;
        $condicoes['perspectiva'] = 'ANAIS';
    
        try {
            $objTrabalho->carregarListaTrabalhos($condicoes, $debug);
            $tituloAnt = "";
            $inscritoAnt = "";
            $totalListados = 0;
            
            if(!$objTrabalho->getListaTrabalhos()) {
                throw new Exception('Nenhum trabalho foi localizado de acordo com o filtro definido acima.');
            }
    
            $this->arrayTotais = array();
            $areaTematicaAnterior = null;
            $optionEixos = null;
            echo "<tr><td colspan=2>";
            echo "<!-- INICIO -->";
            echo '<div id="articlesContainer">';
            foreach ($objTrabalho->getListaTrabalhos() as $objL) {
                $totalListados++;
    
                // Identificando e organizando exibição por área temática
                if ($fws_['ordenar'] == 'area_tematica' && $areaTematicaAnterior != $objL->getTituloAreaTematica()) {
                    if ($areaTematicaAnterior !== null && $areaTematicaAnterior != $objL->getTituloAreaTematica()) {
                        // Fechar o <ul> e o <div> do eixo anterior
                        echo '</ul></div>';
                    }
                    $optionEixos .= '<option value="'.$objL->getTituloAreaTematica().'">'.$objL->getTituloAreaTematica().'</option>';
                    echo '
                    <div class="eixo" data-eixo="'.$objL->getTituloAreaTematica().'">
                    <h2>'.$objL->getTituloAreaTematica().'</h2>
                    ';
                }
                $areaTematicaAnterior = $objL->getTituloAreaTematica();
    
                echo "<li id='{$objL->getCodTrabalho()}' style='list-style-type: none;'>";
                $colorFontTitulo = ($objL->getTituloTrabalho() == $tituloAnt) ? "red" : "black";
    
                $arquivoConsultar = null;
                if (TRABALHO_ANEXA_RESUMO) {
                    if ($objEvConf->getTrabResumoArquivoSemIdentificacao() == 1) {
                        $arquivoConsultar = $objL->getArquivoIdentificadoGerado();
                    }else{ 
                        $arquivoConsultar = $objL->getArquivoIdentificadoGerado();
                    }
                    echo "<a href=\"javascript: getVisualizarArquivo('".$arquivoConsultar."');\" title='Visualizar Resumo'>";
                    echo "<font color={$colorFontTitulo} size=3>".maiusculo($objL->getTituloTrabalho())."</font><br>";
                    echo "</a>";
                } else {
                    echo "<font color={$colorFontTitulo} size=3>".maiusculo($objL->getTituloTrabalho())."</font>";
                }
    
                $tituloAnt = $objL->getTituloTrabalho();
    
                if ($condicoes['perspectiva'] == 'anais') {
                    echo $objL->getNomeInscrito() ? "<br>".textoMaiusculoMinusculo($objL->getNomeInscrito()) : "";
                    foreach (range(1, 6) as $i) {
                        $nomeCoautor = $objL->{"getNomeCoAutor{$i}"}();
                        if (!empty($nomeCoautor)) echo ", {$nomeCoautor}";
                    }
                } else {
                    echo "<div style='margin-left:10px;margin-bottom:10px;'>";
                    if ($condicoes['perspectiva'] == 'simplificada') {
                        echo "<i>".textoMaiusculoMinusculo($objL->getNomeInscrito())."</i>";
                    } else {
                        echo "Autores: <b>".textoMaiusculoMinusculo($objL->getNomeInscrito())."</b> | ";
                        foreach (range(1, 6) as $i) {
                            $nomeCoautor = $objL->{"getNomeCoAutor{$i}"}();
                            if (!empty($nomeCoautor)) echo ", {$nomeCoautor}";
                        }
    
                        echo "<br>Instituição: ".$objL->getInstituicaoTrabalho();
                        echo "<br>Palavras-chave: ".$objL->getPalavrasChave();
                        echo "<br>Formato: ".$objL->getFormatoTexto();
                        if ($condicoes['perspectiva'] == 'comcodigo') {
                            echo "<br>Cód.Resumo: ".$objL->getCodTrabalho();
                        }
                        echo "<br>";
    
                        if ($objEvConf->getApresentacaoAnexar() == 1) {
                            if (!empty($objL->getCodArquivoApresentacao())) {
                                echo "<img src='".WB_URL_IMG."/pdf.gif'>&nbsp;&nbsp;";
                                echo "<a href=\"javascript: getApresentacao('{$objL->getArquivoGeradoApresentacao()}');\" title='Visualizar Apresentação'>";
                                echo "<font color={$colorFontTitulo} size=3>Visualizar Apresentação</font><br>";
                                echo "</a>";
                            } else {
                                echo "Aguardando anexar apresentação";
                            }
                        }
    
                        if (!empty($objL->getCodArquivoVideo())) {
                            echo "<img src='".WB_URL_IMG."/pdf.gif'>&nbsp;&nbsp;";
                            echo "<a href=\"javascript: getApresentacaoVideo('{$objL->getArquivoGeradoVideo()}');\" title='Visualizar Vídeo'>";
                            echo "<font color={$colorFontTitulo} size=3>Visualizar Vídeo</font><br>";
                            echo "</a>";
                        }
                    }
                    echo "</div>";
                }
                if (TRABALHO_ANEXA_RESUMO && $objL->getArquivoGerado()) {
                    if ($objEvConf->getTrabResumoArquivoSemIdentificacao() == 1) {
                        $arqAprovado = substr($objL->getArquivoGeradoIdentificado(), 0, 13);
                        // echo "<a href=\"javascript: visualiza('{$objL->getArquivoGerado()}');\" title='Visualizar Resumo'>";
                        // echo "<img src='resources/image/doc.gif' border='0'></a><br>";
                    }else{
                        $arqAprovado = substr($objL->getArquivoGerado(), 0, 13);
                        // echo "<a href=\"javascript: visualiza('{$objL->getArquivoGerado()}');\" title='Visualizar Resumo'>";
                        // echo "<img src='resources/image/doc.gif' border='0'></a><br>";
                    }
                    // } else {
                    //     echo "<font color=red>Trabalho não anexado.</font>";
                    // }
                }
                echo "</li>";
            }
            echo '</div>';
            echo "</td></tr>";
            ?>
            
            <p>Use o campo abaixo para buscar por artigos (título, autores, palavras-chave):</p>
            <input type="text" id="searchInput" onkeyup="searchArticles()" placeholder="Buscar artigos...">
            <p>Selecione um eixo temático:</p>
            <select id="eixoInput" onchange="searchArticles()">
                <option value="">Todos os Eixos</option>
                <?php echo $optionEixos; ?>
            </select>           
            <script>
                function searchArticles() {
                    // Pega o valor digitado no input de busca e o eixo selecionado
                    var input = document.getElementById('searchInput').value.toUpperCase();
                    var eixoInput = document.getElementById('eixoInput').value;
                    var articlesContainer = document.getElementById("articlesContainer");
                    var eixos = articlesContainer.getElementsByClassName('eixo');

                    // Loop em cada eixo
                    for (var i = 0; i < eixos.length; i++) {
                        var eixo = eixos[i].getAttribute('data-eixo');
                        var artigos = eixos[i].getElementsByTagName('li');
                        var eixoMatch = eixoInput === "" || eixo === eixoInput;
                        var hasVisibleArticle = false;

                        // Loop em cada artigo dentro do eixo
                        for (var j = 0; j < artigos.length; j++) {
                        var text = artigos[j].innerText.toUpperCase(); // Inclui título, autores e palavras-chave

                        // Verifica se o artigo corresponde ao texto digitado
                        if (text.indexOf(input) > -1 && eixoMatch) {
                            artigos[j].style.display = "";
                            hasVisibleArticle = true;
                        } else {
                            artigos[j].style.display = "none";
                        }
                        }

                        // Mostra ou oculta o eixo dependendo se ele tem artigos visíveis
                        if (hasVisibleArticle) {
                        eixos[i].style.display = "";
                        } else {
                        eixos[i].style.display = "none";
                        }
                    }
                }
                function getVisualizarArquivo(nomeArquivo) {
                    const urlArquivo = `${window.location.origin}/resumo/${nomeArquivo}`;
                    window.open(urlArquivo, '_blank');
                }
                </script>
            <?php
            echo "<!-- FIM -->";
            echo "</td></tr>";
            echo "<br>Total de trabalhos listados: {$totalListados}";
        } catch (Exception $ex) {
            echo "Erro: ".$ex->getMessage();
        }
    }
    public function carregarListaTrabalhosAnais($fws_,$debug = false){
        
        $objTrabalho = new Trabalho();
        $objEvConf = $objTrabalho->getObjEventoConfiguracao();
              
        $condicoes = $this->carregarCondicoes($fws_,$debug);
        $condicoes['cache'] = 1000;
        $condicoes['perspectiva'] = 'ANAIS';

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

                //if (($fws_['ordenar'] == 'area_tematica' or $fws_['ordenar'] == 'ordem_area_tematica') and ($areaTematicaAnterior != $objL->getTituloAreaTematica())) {
                ?>
                <tr>
                    <td style="background-color: #fbc53c;"></td>
                    <td style="background-color: #fbc53c;">
                    <table border="0" width="100%" style="background-color: #fbc53c;">
                            <tr>
                                <td style="background-color: #fbc53c;width: 10px;">
                                    <span style="color: #fff;font-size: 50px;float: left;" >
                                        <?php echo $objL->getOrdemAreaTematica()?>
                                    </span>
                                </td>
                                <td style="background-color: #fbc53c;vertical-align: middle;">
                                    <span style="color: #4e4b4b;font-size: 25px;line-height: 1;">
                                        <?php echo $objL->getTituloAreaTematica()?>
                                    </span>
                                </td>
                            </tr>
                    </table>
                    </td>    
                    <td style="background-color: #fbc53c;"></td>
                </tr>
                <?php
                //}
                $areaTematicaAnterior = $objL->getTituloAreaTematica();
                ?>
                <tr id="<?php echo  $objL->getCodTrabalho() ?>">
                    <td></td>
                    <td valign="top"><br>
                        <?php
                        $colorFontInscrito = "black";
                        $colorFontTitulo = "black";
                        
                        $arqAprovado = substr($objL->getArquivoGerado(),0,13);
                        if($objL->getTituloTrabalho()==$tituloAnt){
                            $colorFontTitulo = "red";
                        }
                        if (TRABALHO_ANEXA_RESUMO) {
                            echo "<a href=\"javascript: getAprovado('{$arqAprovado}');\"  title='Visualizar Resumo'>";
                            echo "<font color=".$colorFontTitulo." size=3> <!--Titulo:<br>-->".maiusculo($objL->getTituloTrabalho())."</font><br>";
                            echo "</a>";
                        }else{
                            echo "<font color=".$colorFontTitulo." size=3>". maiusculo($objL->getTituloTrabalho())."</font><br>";
                        }
//                        if($condicoes['perspectiva']!='anais'){
//                        echo "<span style='color: #4e4b4b;font-size: 11px;line-height: 1;'>";
//                        echo "Anexado em: ".formatDateView($objL->getDataInclusaoArqApresentacao());
//                        echo "</span>";
//                        }
//                        echo "<br>";
                        echo "<br>";
                        $tituloAnt = $objL->getTituloTrabalho();
                        

                        if($condicoes['perspectiva']=='anais'){
                            /*não imprimir nada*/
                        }else if($condicoes['perspectiva']!='simplificada'){
                            if($objL->getTituloAreaTematica()){
//                                echo "<br><font color=".$colorFontTitulo."> Área Temática: ".maiusculo($objL->getTituloAreaTematica())."</font>";
                            }else{
                                echo "<br><font color=red> Área Temática: Não cadastrada</font>";
                            }    
                        }
                        
                        if($condicoes['perspectiva']=='anais'){
                            echo $objL->getNomeInscrito()? "<br>". textoMaiusculoMinusculo($objL->getNomeInscrito()) : "";
                            echo $objL->getNomeCoAutor1()? ", ".$objL->getNomeCoAutor1() : "";
                            echo $objL->getNomeCoAutor2()? ", ".$objL->getNomeCoAutor2() : "";
                            echo $objL->getNomeCoAutor3()? ", ".$objL->getNomeCoAutor3() : "";
                            echo $objL->getNomeCoAutor4()? ", ".$objL->getNomeCoAutor4() : "";
                            echo $objL->getNomeCoAutor5()? ", ".$objL->getNomeCoAutor5() : "";
                            echo $objL->getNomeCoAutor6()!=''? ", ".$objL->getNomeCoAutor6() : "";
                        }else{
                            echo "<div style='margin-left:10px;margin-bottom:10px;'>";
                            if($condicoes['perspectiva']=='simplificada'){
                                echo $objL->getNomeInscrito()? "<i>".textoMaiusculoMinusculo($objL->getNomeInscrito())."</i>" : "";
                            }else {
                                echo "Autores: ";

                                echo $objL->getNomeInscrito()? "<b>".textoMaiusculoMinusculo($objL->getNomeInscrito())."</b> | " : "";
                                echo $objL->getNomeCoAutor1()? $objL->getNomeCoAutor1() : "";
                                echo $objL->getNomeCoAutor2()? ", ".$objL->getNomeCoAutor2() : "";
                                echo $objL->getNomeCoAutor3()? ", ".$objL->getNomeCoAutor3() : "";
                                echo $objL->getNomeCoAutor4()? ", ".$objL->getNomeCoAutor4() : "";
                                echo $objL->getNomeCoAutor5()? ", ".$objL->getNomeCoAutor5() : "";
                                echo $objL->getNomeCoAutor6()!=''? ", ".$objL->getNomeCoAutor6() : "";

                                echo " <br><br>Instituição: ".$objL->getInstituicaoTrabalho();
                                echo " <br>Palavras-chave: ".$objL->getPalavrasChave();
                                echo " <br>Formato: ".$objL->getFormatoTexto();
                                $condicoes['perspectiva']='comcodigo';
                                echo ($condicoes['perspectiva']=='comcodigo')? " <br>Cód.Resumo: ".$objL->getCodTrabalho() : "";
                                echo "<br><br>";
                                if($objEvConf->getApresentacaoAnexar()==1){
                                    if($objL->getCodArquivoApresentacao()!=''){
                                        echo "<img src='".WB_URL_IMG."/pdf.gif'>&nbsp;&nbsp;&nbsp;";
                                        echo "<a href=\"javascript: getApresentacao('{$objL->getArquivoGeradoApresentacao()}');\"  title='Visualizar Apresentação'>";
                                        echo "<font color=".$colorFontTitulo." size=3>Visualizar Apresentação</font><br>";
                                        echo "</a>";
                                    }else{
                                        if($fws_['perspectiva'] != 'avaliacao'){ 
                                            echo "Aguardando anexar apresentacao";
                                        }
                                    }
                                                                    
                                }

//                                if($_SERVER['HTTP_X_REAL_IP']=='187.18.126.240'){
                                if($objL->getCodArquivoVideo()!=''){
                                    echo "<img src='".WB_URL_IMG."/pdf.gif'>&nbsp;&nbsp;&nbsp;";
                                    echo "<a href=\"javascript: getApresentacaoVideo('{$objL->getArquivoGeradoVideo()}');\"  title='Visualizar Apresentação em Video'>";
                                    echo "<font color=".$colorFontTitulo." size=3>Visualizar Video</font><br>";
                                    echo "</a>";
                                }
//                                }

                                echo $objL->getResumoTrabalho()? "<br>Resumo: <p ALIGN='justify'>".negritarResumo($objL->getResumoTrabalho())."</p>" : "";
//                                echo "Nomes dos Co-Autores:";
//
//                                echo $objL->getNomeInscrito()? "<br>1º - Co-autor: ".$objL->getNomeInscrito() : "";
//                                echo $objL->getNomeCoAutor1()? "<br>2º - Co-autor: ".$objL->getNomeCoAutor1() : "";
//                                echo $objL->getNomeCoAutor2()? "<br>3º - Co-autor: ".$objL->getNomeCoAutor2() : "";
//                                echo $objL->getNomeCoAutor3()? "<br>4º - Co-autor: ".$objL->getNomeCoAutor3() : "";
//                                echo $objL->getNomeCoAutor4()? "<br>5º - Co-autor: ".$objL->getNomeCoAutor4() : "";
//                                echo $objL->getNomeCoAutor5()? "<br>6º - Co-autor: ".$objL->getNomeCoAutor5() : "";
//                                echo $objL->getNomeCoAutor6()!=''? "<br>7º - Co-autor: ".$objL->getNomeCoAutor6() : "";
//
//                                echo " <br><br>Instituição: ".$objL->getInstituicaoTrabalho();
//                                echo " <br>Palavras-chave: ".$objL->getPalavrasChave();
//                                echo $objL->getResumoTrabalho()? "<br>Resumo: <p ALIGN='justify'>".$objL->getResumoTrabalho()."</p>" : "";

                            }#perspectiva
                            echo "</div>";
                        }
                        ?>
                    </td>
                    <td valign="top">
                    <?php
                    if (TRABALHO_ANEXA_RESUMO) {
                        if($objL->getArquivoGerado()){
                            $arqAprovado = substr($objL->getArquivoGerado(),0,13);
                            if($condicoes['perspectiva']=='anais'){
                                echo "
                                    <br><a href=\"javascript: visualiza('{$objL->getArquivoGerado()}');\"  title='Visualizar Resumo'>
                                        <img src='resources/image/doc.gif' border='0'></a>
                                    <br>
                                ";
                            }else{
                                echo "
                                    <br><a href=\"javascript: getAprovado('{$arqAprovado}');\"  title='Visualizar Resumo'>
                                        <img src='resources/image/pdf.gif' border='0' width='16px'></a>
                                    <br>
                                ";                                
                            }
                        }else{
                            echo "<br><font color=red>Trabalho não anexado.</font>";
                        }
                    }
                    ?>
                    </td>
                </tr>
                <?php
            }
            echo "<br>Total de trabalhos listados: ".$totalListados;
        } catch (Exception $ex) {

        }
    }
    public function carregarListaTrabalhosListarArquivos($fws_,$debug = false){
        
        $objTrabalho = new Trabalho();
        
        $condicoes['estado'] = $fws_['estado'];
        $condicoes['cod_area_tematica'] = $fws_['cod_area_tematica'];
        $condicoes['nome_inscrito']     = $fws_['nome_inscrito'];
        $condicoes['cod_inscrito']      = $fws_['cod_inscrito'];
        $condicoes['cod_trabalho']      = $fws_['cod_trabalho'];
        $condicoes['palavras_chave']    = $fws_['palavras_chave'];
        $condicoes['co_autor']          = $fws_['co_autor'];
        $condicoes['e_mail']            = $fws_['e_mail'];
        $condicoes['cpf']               = $fws_['cpf'];
        $condicoes['titulo_trabalho']   = $fws_['titulo_trabalho'];
        $condicoes['titulo_texto_trabalho']   = $fws_['titulo_texto_trabalho'];
        $condicoes['tipo_documento']   = $fws_['tipo_documento'];
        $condicoes['palavras_chave']    = $fws_['palavras_chave'];
        $condicoes['cod_autor']         = $fws_['cod_autor'];
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

        if ($_SERVER['REMOTE_ADDR'] == '177.100.49.1988') {
            $debug = true;
        }
        echo "<tr><td colspan='8'>";
        echo "<table>";
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

                
                if(($turnoAnt != $objL->getTurno())or ($salaAnt != $objL->getNomeSala())){
                    echo "<tr><td colspan=6><span style='font-size: 18px;'>Sala: ".$objL->getNomeSala(). " - ". formatDateView($objL->getData()) . " - ".$objL->getTurno()."</span></td></tr>";
                    $totalListados=0;;
                }
                $totalListados++;
                if(($turnoAnt != $objL->getTurno())or ($salaAnt != $objL->getNomeSala())or($tituloAreaTematicaAnt != $objL->getTituloAreaTematica())){
                    echo "<tr><td colspan=6><span style='font-size: 18px;'>Eixo: ".$objL->getTituloAreaTematica()."</span></td></tr>";
                    echo "<tr><td>Seq.</td><td>Horário</td><td>Cod.Trabalho</td><td>Titulo do Trabalho</td><td>Apresentador | Co-autores</td></tr>";
                }
                
                $turnoAnt = $objL->getTurno();
                $salaAnt = $objL->getNomeSala();
                $tituloAreaTematicaAnt = $objL->getTituloAreaTematica();
                $horaIni = substr($objL->getHoraIni(),0,5);
                $horaIni = str_replace(":","h",$horaIni);
                
                echo "<tr>";
                echo "<td>".numberPad($totalListados,3). "</td><td>" . $horaIni . "</td>";
                echo "<td align=right>".$objL->getCodTrabalho()."</td>";
                echo "<td>".maiusculo($objL->getTituloTrabalho())."</td>";
                echo "<td>";
                $coAutor = $objL->getNomeInscrito()? "<b>".textoMaiusculoMinusculo($objL->getNomeInscrito())."</b> |" : "";
                $coAutores = $objL->getNomeCoAutor1()? " ".textoMaiusculoMinusculo($objL->getNomeCoAutor1()) : "";
                $coAutores .= $objL->getNomeCoAutor2()? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor2()) : "";
                $coAutores .= $objL->getNomeCoAutor3()? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor3()) : "";
                $coAutores .= $objL->getNomeCoAutor4()? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor4()) : "";
                $coAutores .= $objL->getNomeCoAutor5()? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor5()) : "";
                $coAutores .= $objL->getNomeCoAutor6()!=''? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor6()) : "";
                
                echo $coAutor;
                echo $coAutores;
                echo "</td>";
                echo "</tr>";
//                        echo "<div style='margin-left:20px;margin-bottom:20px;'>";
            }

        } catch (Exception $ex) {

        }
        echo "<table>";
        echo "</td></tr>";
    }
    public function carregarListaTrabalhosEnsalamento($fws_,$debug = false){
        
        $objTrabalho = new Trabalho();
        
        $condicoes['estado'] = $fws_['estado'];
        $condicoes['cod_area_tematica'] = $fws_['cod_area_tematica'];
        $condicoes['nome_inscrito']     = $fws_['nome_inscrito'];
        $condicoes['cod_inscrito']      = $fws_['cod_inscrito'];
        $condicoes['cod_trabalho']      = $fws_['cod_trabalho'];
        $condicoes['palavras_chave']    = $fws_['palavras_chave'];
        $condicoes['co_autor']          = $fws_['co_autor'];
        $condicoes['e_mail']            = $fws_['e_mail'];
        $condicoes['cpf']               = $fws_['cpf'];
        $condicoes['titulo_trabalho']   = $fws_['titulo_trabalho'];
        $condicoes['titulo_texto_trabalho']   = $fws_['titulo_texto_trabalho'];
        $condicoes['palavras_chave']    = $fws_['palavras_chave'];
        $condicoes['cod_autor']         = $fws_['cod_autor'];
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

        if ($_SERVER['REMOTE_ADDR'] == '177.100.49.1988') {
            $debug = true;
        }
        echo "<tr><td colspan='8'>";
        echo "<table>";
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

                
                if(($turnoAnt != $objL->getTurno())or ($salaAnt != $objL->getNomeSala())){
                    echo "<tr><td colspan=6><span style='font-size: 18px;'>Sala: ".$objL->getNomeSala(). " - ". formatDateView($objL->getData()) . " - ".$objL->getTurno()."</span></td></tr>";
                    $totalListados=0;;
                }
                $totalListados++;
                if(($turnoAnt != $objL->getTurno())or ($salaAnt != $objL->getNomeSala())or($tituloAreaTematicaAnt != $objL->getTituloAreaTematica())){
                    echo "<tr><td colspan=6><span style='font-size: 18px;'>Eixo: ".$objL->getTituloAreaTematica()."</span></td></tr>";
                    echo "<tr><td>Seq.</td><td>Horário</td><td>Cod.Trabalho</td><td>Titulo do Trabalho</td><td>Apresentador | Co-autores</td></tr>";
                }
                
                $turnoAnt = $objL->getTurno();
                $salaAnt = $objL->getNomeSala();
                $tituloAreaTematicaAnt = $objL->getTituloAreaTematica();
                $horaIni = substr($objL->getHoraIni(),0,5);
                $horaIni = str_replace(":","h",$horaIni);
                
                echo "<tr>";
                echo "<td>".numberPad($totalListados,3). "</td><td>" . $horaIni . "</td>";
                echo "<td align=right>".$objL->getCodTrabalho()."</td>";
                echo "<td>".maiusculo($objL->getTituloTrabalho())."</td>";
                echo "<td>";
                $coAutor = $objL->getNomeInscrito()? "<b>".textoMaiusculoMinusculo($objL->getNomeInscrito())."</b> |" : "";
                $coAutores = $objL->getNomeCoAutor1()? " ".textoMaiusculoMinusculo($objL->getNomeCoAutor1()) : "";
                $coAutores .= $objL->getNomeCoAutor2()? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor2()) : "";
                $coAutores .= $objL->getNomeCoAutor3()? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor3()) : "";
                $coAutores .= $objL->getNomeCoAutor4()? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor4()) : "";
                $coAutores .= $objL->getNomeCoAutor5()? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor5()) : "";
                $coAutores .= $objL->getNomeCoAutor6()!=''? ", ".textoMaiusculoMinusculo($objL->getNomeCoAutor6()) : "";
                
                echo $coAutor;
                echo $coAutores;
                echo "</td>";
                echo "</tr>";
//                        echo "<div style='margin-left:20px;margin-bottom:20px;'>";
            }

        } catch (Exception $ex) {

        }
        echo "<table>";
        echo "</td></tr>";
    }

    public function mostrarListaEmails($emails, $debug = false) {
        ?>
        <script type="text/javascript">
            function mostrarListaEmails() {
                <?php if ($emails!=''){ ?>
                    var generator = window.open('', 'name', 'height=400,width=300,resizable=no,scrollbars=yes,toolbar=no,status=no');
                    generator.document.write('<html><head><title>E-mails</title>');
                    generator.document.write('</head><body><font size=2 face=verdana>');
                    generator.document.write('<fieldset><?php echo $emails?></fieldset>');
                    generator.document.write('<p><a href="javascript:self.close()">Fechar</a> janela.</p>');
                    generator.document.write('</body></html>');
                    generator.document.close();                 
                <?php } else {?>
                    alert("Nenhum registro selecionado.");
                <?php }?>
            }
        </script>
        <?php
    }
    public function mostrarExcelEmails($emails, $debug = false) {
        ?>
        <script type="text/javascript">
            function mostrarListaEmails() {
                <?php if ($emails!=''){ ?>
                    var generator = window.open('', 'name', 'height=400,width=300,resizable=no,scrollbars=yes,toolbar=no,status=no');
                    generator.document.write('<html><head><title>E-mails</title>');
                    generator.document.write('</head><body><font size=2 face=verdana>');
                    generator.document.write('<fieldset><?php echo $emails?></fieldset>');
                    generator.document.write('<p><a href="javascript:self.close()">Fechar</a> janela.</p>');
                    generator.document.write('</body></html>');
                    generator.document.close();

//            var htmltable = document.getElementById('exportdata');
//            var html = htmltable.outerHTML;
//            window.open('data:application/vnd.ms-excel, ' + encodeURIComponent('<?php echo $emails?>'));                    

//          e.preventDefault();
//          var table_div = document.getElementById('divTabela');   
//          var table_div = '<?php echo $emails?>';   
//          // esse "\ufeff" é importante para manter os acentos         
//          var blobData = new Blob(['\ufeff'+table_div.outerHTML], { type: 'application/vnd.ms-excel' });
//          var url = window.URL.createObjectURL(blobData);
//          var a = document.createElement('a');
//          a.href = url;
//          a.download = 'Meu arquivo Excel'
//                a.click();
//            });
                   
                <?php } else {?>
                    alert("Nenhum registro selecionado.");
                <?php }?>
            }
        </script>
        <?php
    }
    public function functionName($param) {
    $query = @pg_query($sql) or die(pg_last_error());
        ?><hr>Total de apresentadores listados: <?php
        echo pg_num_rows($query);

        while ($dados = @pg_fetch_assoc($query)) {
            $totalAlunos++;
            echo "<hr />";
            echo "".textoMaiusculoMinusculo($dados['apresentador'])." - ";
    //        echo "Instituição: {$dados['nome_instituicao']} - ";
            echo "{$dados['nome_projeto']}<br />";        
            echo "Orientador(a): ".maiusculo($dados['orientador'])."<br />";
            echo "<b>".maiusculo($dados['titulo'])."</b><br />";

            echo "Área: {$dados['area']} - Área CnpQ: {$dados['areacnpq']}<br />";

            if ($dados['grupo'])
                echo "Grupo: {$dados['grupo']}<br />";

            echo "Palavras-chave: {$dados['palavra_chave']}<br />";

            $objGEAC = new GerenciarEnsalamentoAC();
            echo "<b>";
            $objGEAC->mostraDadosEnsalamento($dados,$debug);
            echo "</b>";
            //Acrescentar ensalamento
            echo "<img src='".SITE."/imgs/icones/doc-2.gif' border=0> <a href='anais/artigo.php?cod={$dados['cod_artigo']}' target='_blank'>Visualizar artigo</a>";
            echo " - {$dados['nome_forma']} <br /><br />";
        }
        ?>
        <hr>Total de apresentadores listados: <?php echo $totalAlunos ?>
        <?php
        echo "<hr />";
    }
}