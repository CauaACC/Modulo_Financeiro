<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
$debug = false;
require_once 'inscricao_congresso_tit_area_inscrito.php';
?>
<div class="panel-group">
    <?php
    try {
        
        if($fws_['msg']){
            FWA_Mostrar_Mensagem($fws_['msg']);
        }
        require_once 'inscricao_congresso_cabecalho.php';
        
        try {
            $objInscrito = new Inscrito();
            /** esta verndo aqui, o código inscrito de quem esta logado sendo setado para o objeto
             * é isso que precisamos fazer ali no cadastro do registro
             * você esta acompanhando?
             * ???
             */
            $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
            $objInscrito->setCodEvento($cod_evento);
            $objInscrito->getInscritoById($debug);


            $objIsento = new Isento();
            $objIsento->setCpf($objInscrito->getCpf());
            $objIsento->setCodEvento($objInscrito->getCodEvento());
            $objIsento->getIsentoByCpf();
            if ($objIsento->getCodIsento() == '') {
                $objIsento->getIsentoByCpfOld();
                // if ($objIsento->getCodIsento() == '') {
                //     $objIsento->getByCpfPorPerfil();
                //     if($objIsento->getTotalIsencao()>0){
                //          $objIsento->setCodIsento(1);
                //     }
                // }
            }
            
            $objInscrito->carregarOjbPerfil($debug);
            $objPerfil = $objInscrito->getObjPerfil();
        ?>
        <?php //if (COD_EVENTO==82 AND $objInscrito->getCodInscrito()==30491) {?>        
        <?php if (COD_EVENTO==82) {?>
        <div class="panel panel-default">
            <div class="panel-body">
                <?php
                require_once('inscricao_congresso_ok_menu_principal.php');
                ?>
            </div>
        </div>
        <?php }else if (COD_EVENTO==115) {?>
        <div class="panel panel-default">
            <div class="panel-body">
                <?php
                if ($objInscrito->getStatusProcesso() == 'INSCRITO') {
                    // Inscrito que nao avancou no processo: ve apenas o resultado da analise
                    // (homologacao, pontuacao, classificacao preliminar) - sem menu de cards
                    require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_edital_recurso_prorede2-fase-2.php');
                } else {
                    // CONVOCADO / PARTICIPANDO / DESISTENTE: area pos-processo com cards
                    require_once('inscricao_congresso_ok_menu_principal_115.php');
                }
                ?>
            </div>
        </div>
        <?php }else{ ?>
        <?php  
        if($OP_EMISSAO_CERTIFICADOS){?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Emissão de Certificados</b></div>
            <div class="panel-body">
                <?php
                require_once('inscricao_consulta_certificados.php');
                ?>    
            </div>
        </div>
        <?php } ?>
        <?php
        if(in_array(COD_EVENTO, [$objInscrito->getCodEvento()])){
            ob_start();
            require_once('inscricao_congresso_avisos.php');
            $avisosHtml = ob_get_clean();
        if ($ii > 0) {
            ?>
        <div class="panel panel-default">
            <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="width:50%;"><b>Avisos <?php echo "<div>Não lidos: {$total_falta_ler}</div>"; ?></b></div>
                <div style="text-align:right; width:50%; margin-right:50px;">
                    <select id="filtroAviso" style="padding: 5px; font-size: 14px;">
                        <option value="nao-lidos" selected>Avisos não lidos</option>
                        <option value="lidos">Avisos lidos</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
            </div>
        <script type="text/javascript">
            $(document).ready(function () {
                function aplicarFiltro(filtro) {
                    if (filtro === "todos") {
                        $(".aviso-box-lido, .aviso-box-nao-lido").show();
                    } else if (filtro === "lidos") {
                        $(".aviso-box-lido").show();
                        $(".aviso-box-nao-lido").hide();
                    } else if (filtro === "nao-lidos") {
                        $(".aviso-box-nao-lido").show();
                        $(".aviso-box-lido").hide();
                    }
                }

                aplicarFiltro("nao-lidos");

                $("#filtroAviso").on("change", function () {
                    aplicarFiltro($(this).val());
                });
            });
        </script>
            <div class="panel-body"><br>
                <ul>
                    <?= $avisosHtml ?>
                </ul>
            </div>
        </div>
        <?php } 
        }?>
        <?php
        if($objEvConf->getRegistroDiario()){ ?>
        <div class="panel panel-default">
            <div class="panel-heading"><b> Gerenciar Atividade</b></div>
            <div class="panel-body"><br>
                <ul>
                    <a href="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarRegistroAtividade.php">Gerenciar o Registro de atividades</a>
                    <?php
                    //require_once('inscricao_congresso_avisos.php');
                    ?>
                </ul>
                <ul>
                    <a href="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarRegistroAtividadeAvaliacao.php">Gerenciar registro de atividades (avaliador)</a>
                </ul>
            </div>
        </div>
        <?php } ?>
        <?php if (COD_EVENTO == 122) { ?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Dados Maple Bear</b></div>
            <div class="panel-body">
                <p><strong>Unidade Maple Bear:</strong> <?php echo $objInscrito->getInstituicao() ? $objInscrito->getInstituicao() : '<span class="text-danger">Não informada</span>'; ?></p>
                <p><strong>Comprovante de Vínculo:</strong>
                <?php
                    $objTrabalhoConsulta = new Trabalho();
                    $objTrabalhoConsulta->setCodInscrito($objInscrito->getCodInscrito());
                    $objTrabalhoConsulta->setCodEvento(COD_EVENTO);
                    $listaTrabalhos = $objTrabalhoConsulta->carregarListaTrabalhosByIdInscrito($debug);
                    $comprovanteEnviado = false;
                    if ($listaTrabalhos) {
                        foreach ($objTrabalhoConsulta->getListaTrabalhos() as $trab) {
                            if ($trab->getTipoDocumento() == 'COMPROVANTE_VINCULO') {
                                $comprovanteEnviado = true;
                                echo '<span class="text-success"><i class="fa fa-check-circle"></i> Enviado</span>';
                                break;
                            }
                        }
                    }
                    if (!$comprovanteEnviado) {
                        echo '<span class="text-danger"><i class="fa fa-times-circle"></i> Não enviado</span>';
                    }
                ?>
                </p>
            </div>
        </div>
        <?php } ?>
        <?php
        /* Comprovante exigido pelo perfil (config do perfil) - status do envio feito nos dados pessoais */
        if ($objPerfil && $objPerfil->getComprovanteExigir() == 1) {
            $tituloComprovantePerfil = trim($objPerfil->getComprovanteTitulo()) != '' ? $objPerfil->getComprovanteTitulo() : 'Comprovante';
            $objTrabComprovantePerfil = new Trabalho();
            $objTrabComprovantePerfil->setCodEvento(COD_EVENTO);
            $objTrabComprovantePerfil->setCodInscrito($objInscrito->getCodInscrito());
            $objTrabComprovantePerfil->setTipoDocumento(GerenciarInscricaoAC::TIPO_DOC_COMPROVANTE_PERFIL);
            $objTrabComprovantePerfil->setSlugTipoDocumento(GerenciarInscricaoAC::SLUG_DOC_COMPROVANTE_PERFIL);
            $comprovantePerfilEnviado = $objTrabComprovantePerfil->getByDocumentoUltimo($debug)
                && $objTrabComprovantePerfil->getArquivoGerado() != '' && $objTrabComprovantePerfil->getStatus() == 'ATIVADA';
        ?>
        <div class="panel panel-default" id="painel-comprovante-perfil">
            <div class="panel-heading"><b>Comprovante do perfil</b></div>
            <div class="panel-body">
                <p><strong><?php echo htmlspecialchars($tituloComprovantePerfil, ENT_QUOTES, 'UTF-8') ?>:</strong>
                <?php if ($comprovantePerfilEnviado) {
                    $dataEnvioComprovante = $objTrabComprovantePerfil->getDataAlteracao() ? $objTrabComprovantePerfil->getDataAlteracao() : $objTrabComprovantePerfil->getDataInclusao();
                ?>
                    <span class="text-success"><i class="fa fa-check-circle"></i> Enviado em <?php echo formatDateView($dataEnvioComprovante) ?> (versão <?php echo (int) $objTrabComprovantePerfil->getVersao() ?>)</span>
                    &nbsp;<a href="index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php">Substituir</a>
                <?php } else { ?>
                    <span class="text-danger"><i class="fa fa-times-circle"></i> Não enviado</span>
                    &nbsp;<a class="btn btn-sm btn-danger" href="index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php">Enviar comprovante</a>
                <?php } ?>
                </p>
            </div>
        </div>
        <?php } ?>
        <?php
        if($OP_EMISSAO_CARTA_ACEITE){
            if($objInscrito->getTotalTrabalhos()>=1){
            ?>
            <div class="panel panel-default">
                <div class="panel-heading"><b>Carta(s) de Aceite</b></div>
                <div class="panel-body">
                    <?php
                    require_once('inscricao_consulta_carta_aceite.php');
                    ?>    
                </div>
            </div>
            <?php
            }
        }
        ?>
        <?php
        if($objPerfil->getReceberInscricao()){
        if ($OP_EMISSAO_INGRESSOS) {
            if(!$objInscrito->getCodIsento() and !$objInscrito->getInscritoPossuiVoucher()){
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Ingresso(s)</b></div>
            <div class="panel-body">
                <?php
                require_once('inscricao_consulta_ingressos.php');
                ?>    
            </div>
        </div>
        <?php }}}?>
        <?php if($objEvConf->getHabilitarModelosContrato()){ ?>
            <div class="panel panel-default">
                <div class="panel-heading"><b>Contratos e Documentos</b></div>
                <div class="panel-body">
                    <?php require_once('inscricao_congresso_contratos_modelos.php'); ?>
                </div>
            </div>
        <?php } ?>
        <?php
        if($objPerfil->getReceberInscricao() or $objPerfil->getLiberarComprovanteInscricao()==1){
//        $OP_EMISSAO_COMPROVANTE_INSCRICAO = false;
        if($objEvConf->getEmitirComprovante()){ ?>
            <div class="panel panel-default">
                <div class="panel-heading"><b>Comprovante de Inscrição</b></div>
                <div class="panel-body">
                    <?php
                    require_once('inscricao_consulta_comprovante_inscricao.php');
                    ?>    
                </div>
            </div>
        <?php
        }}
        ?>
        <?php
        if($OP_EMISSAO_DECLARACAO_PRESENCA){
            if($objInscrito->getDeclaracaoEstevePresente() == 1){
            ?>
            <div class="panel panel-default">
                <div class="panel-heading"><b>Declaração Presença</b></div>
                <div class="panel-body">
                    <?php
                    require_once('inscricao_consulta_declaracao_presenca.php');
                    ?>    
                </div>
            </div>
        <?php
            }
        }
        if($objEv->getCodEvento()==49){
            $fws_['op_selecionar_oficina']=1;
            $objPerfil->setVerificarSelecionarAtividade(1);
        }
        if($fws_['op_selecionar_oficina']){
        if($objPerfil->getVerificarSelecionarAtividade()){
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><b><?php echo $objEvConf->getAtividadeTipo()!=''? $objEvConf->getAtividadeTipo() : 'Atividade(s)' ?> Selecionada(s)</b></div>
            <div class="panel-body">
                <?php
                require_once('inscricao_congresso_atividade.php');
                ?>    
            </div>
        </div>
        <?php } } ?>
        <?php
        if($fws_['op_selecionar_pedidos']){
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Pedidos/Faturas</b></div>
            <div class="panel-body">
                <?php
                require_once('inscricao_congresso_pedidos.php');
                ?>    
            </div>
        </div>
        <?php } ?>
        <?php
        if($fws_['op_selecionar_evento_paralelo']){
//            if($objPerfil->getVerificarSelecionarAtividade()){  
            /** Verifica pagamento para liberar seleção de atividade */
            if($objEvConf->getCodEvento()==76){
                $fws_['inscricaoPaga'] = false;
                if($objInscrito->getCodVoucher()!='' or $objInscrito->getCodIsento()!='' or $objInscrito->getTotalPagamentos()>0 or $objInscrito->getTotalPerfisIsentos()>0 or $objInscrito->getValorTotalPedidos()==0){
                    $fws_['inscricaoPaga'] = true;
                }
            }else{
                $fws_['inscricaoPaga'] = true;
            }
            ?>
            <div class="panel panel-default">
                <div class="panel-heading"><b>Participe: <?php echo $objEvConf->getEventoParaleloDescricao() ?></b></div>
                <div class="panel-body">
                    <?php
                    if($fws_['inscricaoPaga']){
                        if($objEvConf->getCodEvento()==76){
                            echo "É permitido selecionar 1 minicurso e até 2 visitas técnicas";
                        }
                        require_once('inscricao_congresso_consulta_evento_paralelo.php');
                    }else{
                        echo "<font color='red'>Seleção de atividades permitida após confirmação de pagamento</font>";
                    }
                    ?>
                </div>
            </div>
            <?php
//            }
        }?>
        <?php
        if($objPerfil->getVerificarEnvioTrabalho() AND $OP_SUBMISSAO_TRABALHO==true){
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><b><?php echo $objEvConf->getDescTrabAnexoTipo() ?></b></div>
            <div class="panel-body">
                <?php
                /** @todo Area de Editais divulgação e envio de documentos
                 * 37, 38, 46, 69 = eventos de inscrição com edital
                 * 80, 88 = eventos de editais com recursos ultimos tratados 2025
                 * 42, 44, 50, 73, 82 = eventos com área de colaboradores
                 * 106 = requerimento de impugnação
                 * 107 = requerimento de isenção
                 * 70  = eventos com avaliação de trabalhos
                 * outros eventos apenas listam os trabalhos enviados e avaliações de trabalhos
                 */
                if(in_array($objEv->getCodEvento(), [80, 88, 107])){
                    if(trim($objInscrito->getStatusProcesso())=='CONVOCADO'){
                        require_once('inscricao_consulta_aptos.php');
                    }                      
                    /** removido durante inscrição */
                    require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_edital_recurso.php');
                   
                
                }else if(in_array($objEv->getCodEvento(), [37, 38, 46, 69])){
                    /** removido durante inscrição */
                    require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_edital.php');
                   
                }else if(in_array($objEv->getCodEvento(), [108])){
                    // require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_recurso.php');
                    // require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_prorede.php');
                    require_once('inscricao_trabalhos_consulta_dados_bancarios.php');
                }else if(in_array($objEv->getCodEvento(), [115])){
                    // 115 (edital 032/2025) esta em fase 2 desde fev/2026
                    require_once 'inscricao_trabalhos_consulta_trabalhos_submetidos_edital_recurso_prorede2-fase-2.php';
                    // require_once 'inscricao_trabalhos_consulta_trabalhos_submetidos_edital_recurso_prorede2.php';
                    // require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_prorede.php');
                    // require_once('inscricao_trabalhos_consulta_dados_bancarios.php');
                }else if(in_array($objEv->getCodEvento(), [129])){
                    // 129 (edital 003/2026) - apresentacao em abas (espelha 115999)
                    // A aba do momento atual do cronograma fica ATIVA por padrao.
                    // Quando o prazo de envio encerra, a aba "Envio de documentação"
                    // muda pra "Documentos" (consulta/historico).
                    $envioEncerrado_129 = $objEvConf && $objEvConf->isPeriodoEnvioTrabalhoEncerrado();
                    $labelAbaEnvio_129          = $envioEncerrado_129 ? '📁 Documentos' : 'Envio de Documentos';
                    $labelAbaHomol_129          = 'Homologação 1ª Fase';
                    $labelAbaFase2_129          = '2ª Fase';
                    $labelAbaResultadoFinal_129 = '🏁 Resultado Final';

                    // Aba 2a Fase so aparece pra quem foi HOMOLOGADO na 1a Fase
                    // (status efetivo = final | apos_recurso | inicial = 'H').
                    $mostrarAbaFase2_129 = false;
                    $objF1Check = new AvaliacaoInscrito();
                    $objF1Check->setCodEvento($objEv->getCodEvento());
                    $objF1Check->setCodInscrito($objInscrito->getCodInscrito());
                    if ($objF1Check->carregarAvaliacaoFase1ByIdInscrito()) {
                        $statusF1Efetivo = $objF1Check->getClassificacaoStatusFinal();
                        if (!$statusF1Efetivo) { $statusF1Efetivo = $objF1Check->getClassificacaoStatusAposRecurso(); }
                        if (!$statusF1Efetivo) { $statusF1Efetivo = $objF1Check->getClassificacaoStatus(); }
                        $mostrarAbaFase2_129 = ($statusF1Efetivo === 'H');
                    }

                    // Aba Resultado Final: aparece quando flag resultado_final_divulgado=1
                    // OU data divulgacao_final_dat ja passou. So pra quem chegou na Fase 2.
                    $mostrarAbaResultadoFinal_129 = false;
                    if ($mostrarAbaFase2_129) {
                        $objEtapaCheck = new Etapa();
                        if ($objEtapaCheck->getEtapaByOrdem(2, false)) {
                            $datDivFinal     = $objEtapaCheck->getDivulgacaoFinalDat();
                            $flagDivFinal    = ((int) $objEtapaCheck->getResultadoFinalDivulgado() === 1);
                            $dataDivFinalPas = ($datDivFinal && time() >= strtotime($datDivFinal));
                            $mostrarAbaResultadoFinal_129 = ($flagDivFinal || $dataDivFinalPas);
                        }
                    }
                    ?>
                        <style>
                            @media (max-width:768px){
                                #tabsProRede129 { display:flex; flex-wrap:nowrap; }
                                #tabsProRede129 > li { flex:1 1 0; }
                                #tabsProRede129 > li > a {
                                    text-align:center;
                                    padding:8px 4px;
                                    font-size:13px;
                                    white-space:normal;
                                }
                            }
                        </style>
                        <?php
                        // Aba ativa por padrao = a do momento mais recente do processo:
                        //   1) Resultado Final divulgado -> "Resultado Final"
                        //   2) Fase 2 disponivel        -> "2ª Fase"
                        //   3) Senao                     -> "Homologação 1ª Fase"
                        if ($mostrarAbaResultadoFinal_129) {
                            $abaAtiva129 = 'tab_resultado_final_129';
                        } elseif ($mostrarAbaFase2_129) {
                            $abaAtiva129 = 'tab_fase2_129';
                        } else {
                            $abaAtiva129 = 'tab_homologacao_129';
                        }
                        ?>
                        <ul class="nav nav-tabs" id="tabsProRede129" style="margin-bottom:10px;flex-wrap:wrap;">
                            <li>
                                <a href="#tab_envio_129" data-toggle="tab"><?= $labelAbaEnvio_129 ?></a>
                            </li>
                            <li<?= $abaAtiva129 === 'tab_homologacao_129' ? ' class="active"' : '' ?>>
                                <a href="#tab_homologacao_129" data-toggle="tab"><?= $labelAbaHomol_129 ?></a>
                            </li>
                            <?php if ($mostrarAbaFase2_129) { ?>
                            <li<?= $abaAtiva129 === 'tab_fase2_129' ? ' class="active"' : '' ?>>
                                <a href="#tab_fase2_129" data-toggle="tab"><?= $labelAbaFase2_129 ?></a>
                            </li>
                            <?php } ?>
                            <?php if ($mostrarAbaResultadoFinal_129) { ?>
                            <li<?= $abaAtiva129 === 'tab_resultado_final_129' ? ' class="active"' : '' ?>>
                                <a href="#tab_resultado_final_129" data-toggle="tab"><?= $labelAbaResultadoFinal_129 ?></a>
                            </li>
                            <?php } ?>
                        </ul>
                        <div class="tab-content" style="margin-top:10px;">
                            <div class="tab-pane fade" id="tab_envio_129">
                                <?php require_once 'inscricao_trabalhos_consulta_trabalhos_submetidos_prorede2_129.php'; ?>
                            </div>
                            <div class="tab-pane fade<?= $abaAtiva129 === 'tab_homologacao_129' ? ' in active' : '' ?>" id="tab_homologacao_129">
                                <?php require_once 'inscricao_trabalhos_consulta_trabalhos_submetidos_edital_recurso_prorede2-fase-2_129.php'; ?>
                            </div>
                            <?php if ($mostrarAbaFase2_129) { ?>
                            <div class="tab-pane fade<?= $abaAtiva129 === 'tab_fase2_129' ? ' in active' : '' ?>" id="tab_fase2_129">
                                <?php require_once 'inscricao_trabalhos_consulta_prorede2_fase2_avaliacao_129.php'; ?>
                            </div>
                            <?php } ?>
                            <?php if ($mostrarAbaResultadoFinal_129) { ?>
                            <div class="tab-pane fade<?= $abaAtiva129 === 'tab_resultado_final_129' ? ' in active' : '' ?>" id="tab_resultado_final_129">
                                <?php require_once 'inscricao_trabalhos_consulta_prorede2_resultado_final_129.php'; ?>
                            </div>
                            <?php } ?>
                        </div>
                    <?php
                }else if(in_array($objEv->getCodEvento(), [115999])){
                    ?>
                        <!-- Abas -->
                        <ul class="nav nav-tabs" id="tabsProRede">
                            <li class="active">
                                <a href="#tab_recurso" data-toggle="tab">Recurso</a>
                            </li>
                            <li>
                                <a href="#tab_inscricao" data-toggle="tab">Inscrições / Envio</a>
                            </li>
                        </ul>

                        <!-- Conteúdo das abas -->
                        <div class="tab-content" style="margin-top:10px;">
                            <div class="tab-pane fade in active" id="tab_recurso">
                                <?php require_once 'inscricao_trabalhos_consulta_trabalhos_submetidos_edital_recurso_prorede2.php'; ?>
                            </div>

                            <div class="tab-pane fade" id="tab_inscricao">
                                <?php require_once 'inscricao_trabalhos_consulta_trabalhos_submetidos_prorede2.php'; ?>
                            </div>
                        </div>
                    <?php
                }else{
                    require_once('inscricao_trabalhos_consulta_trabalhos_submetidos.php');
                }
                ?>    
                <!-- -->
                <?php if (in_array($objEv->getCodEvento(), [37, 38, 46, 69])) {?>
                <?php
                    // if($_SERVER['SERVER_ADDR']=='189.90.70.56'){
                    //     echo __LINE__;
                    //     require_once('inscricao_consulta_aptos.php');
                    // }
                ?>
            <!-- -->
            <?php } ?>
            </div>
        </div>
        <?php
        }    
        ?>
        <?php
        if($OP_SELECIONAR_ALMOCO){?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Almoços selecionados</b></div>
            <div class="panel-body">
                <?php require_once('inscricao_congresso_almoco.php')?>
            </div>
        </div>
        <?php
        }
        #echo "<fieldset><legend> Monografia </legend><blockquote>";
        #require_once('inscricao_monografia_consulta_monografia_submetida.php');
        #echo "</blockquote></fieldset>";
        #echo "<fieldset><legend> Desconto </legend>";
        #require_once('inscricao_texto_desconto.php');
        #echo "</fieldset>";
       
        ?>
        <?php if ($objInscrito->getCodEvento()==23) { ?>
            <div class="panel panel-default">
                <div class="panel-heading"><b>Ser Associado</b></div>
                <div class="panel-body">
                    <?php
                    if($objInscrito->getQueSerAssociado()=='SIM') {
                        echo "Optou por SIM, se Associar";
                    }else if($objInscrito->getQueSerAssociado()=='NAO') {
                        echo "Optou por NÃO, não se Associar";
                    }else{
                        
                    }
                    ?>
                </div>
            </div>
        <?php } ?>    
        <?php
        if($objPerfil->getReceberInscricao()){
        if ($OP_EMISSAO_RECIBOS AND $OP_INCRICAO_GRATUITA==FALSE) {
            if(!$objInscrito->getCodIsento() and !$objInscrito->getInscritoPossuiVoucher()){
                if ($objEvConf->getEmitirRecibo() || $objEvConf->getSolicitacaoNfHabilitada() == 1) {
                    $tituloPainelRecibo = $objEvConf->getEmitirRecibo()
                        ? ($objEvConf->getSolicitacaoNfHabilitada() == 1 ? 'Recibo(s) e Nota(s) Fiscal(is)' : 'Recibo(s) de Pagamento(s)')
                        : 'Nota(s) Fiscal(is)';
                    ?>
                    <div class="panel panel-default">
                        <div class="panel-heading"><b><?php echo $tituloPainelRecibo ?></b></div>
                        <div class="panel-body">
                            <?php
                            require_once('inscricao_consulta_recibos.php');
                            ?>
                        </div>
                    </div>
        <?php }?>
        <?php }}}?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>
                <?php 
                if($objEvConf->getRecebePagamento()==true){ 
                    echo "Forma de Pagamento";
                }else{
                    echo "Função Escolhida";
                }
                ?>
                </b></div>
            <div class="panel-body">
                <?php
                    require_once('inscricao_botao_boleto.php');
                ?>
            </div>
        </div>
        <?php //if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){ ?>
        <div class="panel panel-default">
            <div class="panel-body" style="padding-top: 5px; padding-bottom: 5px;padding-left: 17px">
            <a href="https://api.whatsapp.com/send?phone=5543996967790&text=Cancelar%20essa%20Inscrição:(<?php echo $objInscrito->getIdPublico()?>)" 
            target="_blank" 
            title="Atendimento via WhatsApp"
            style="display:block; text-align:left;font-size:0.85em; color:#6c757d;">
            <i class="fa fa-whatsapp" style="margin-right:4px;"></i>
            Solicitar cancelamento
            </a>
            </div>
        </div>            
        <div class="panel panel-default" style="background-color: #2D937E;">
            <div class="panel-body">
            <br>
            </div>
        </div>            
        <div class="panel panel-default">
            <div class="panel-heading">
                <b>Demais Projetos aos quais Você se Inscreveu:</b></div>
            <div class="panel-body">
                <?php
                    require_once('inscricao_consulta_outros_eventos.php');
                ?>
            </div>
        </div>
        <?php //} ?>
        <?php } ?>
        <?php
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    } catch (Exception $exc) {
        $_SESSION['MSG'] = $exc->getMessage();
        echo "<a href='" . $url."'>" . FWA_Mostrar_Mensagem($exc->getMessage()) . "</a>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        }
    }
    ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <br>
        </div>
    </div>
</div>
