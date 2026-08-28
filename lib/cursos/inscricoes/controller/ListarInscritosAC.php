<?php

class ListarInscritosAC
{

    function __construct()
    {
        $this->selecionarAlmoco = FALSE;
        $this->valorAlmoco  = 0;
        $this->selecionarOficina = TRUE;
        $this->valorOficina = 0;
    }

    public function carregarCondicoes($fws_, $debug = false)
    {
        $condicoes['estado'] = $fws_['estado'];
        $condicoes['cidade'] = $fws_['cidade'];
        $condicoes['cod_area_tematica'] = $fws_['cod_area_tematica'];
        $condicoes['cod_macrorregiao'] = $fws_['cod_macrorregiao'];
        $condicoes['cod_oficina']   = $fws_['cod_oficina'];
        $condicoes['nome_inscrito'] = $fws_['nome_inscrito'];
        $condicoes['cod_inscrito']  = $fws_['cod_inscrito'];
        $condicoes['cod_trabalho']  = $fws_['cod_trabalho'];
        $condicoes['e_mail']        = $fws_['e_mail'];
        $condicoes['cpf']           = formatCpfInsert($fws_['cpf']);
        $condicoes['fone_contato']  = $fws_['fone_contato'];
        $condicoes['titulo_inscrito'] = $fws_['titulo_inscrito'];
        $condicoes['ordenar']       = $fws_['ordenar'];
        $condicoes['pagamentos']    = $fws_['pagamentos'];
        $condicoes['pagamentosQtdAte']    = $fws_['pagamentosQtdAte'];
        $condicoes['pagamentosQtdMaior']    = $fws_['pagamentosQtdMaior'];
        $condicoes['pagamentosQtdMenor']    = $fws_['pagamentosQtdMenor'];
        $condicoes['pedidos']       = $fws_['pedidos'];
        $condicoes['pedidosQtdAte']       = $fws_['pedidosQtdAte'];
        $condicoes['pedidosQtdMaior']       = $fws_['pedidosQtdMaior'];
        $condicoes['pedidosQtdMenor']       = $fws_['pedidosQtdMenor'];
        $condicoes['xml_pagamentos']    = $fws_['xml_pagamentos'];
        $condicoes['xml_pagamentos_carregados']    = $fws_['xml_pagamentos_carregados'];
        $condicoes['forma_pagamento'] = $fws_['forma_pagamento'];
        $condicoes['trabalhos']     = $fws_['trabalhos'];
        $condicoes['flg_assinado'] = $fws_['flg_assinado'];
        $condicoes['contrato_anexo']     = $fws_['contrato_anexo'];
        $condicoes['rpa_anexo']     = $fws_['rpa_anexo'];
        $condicoes['comp_transf_anexo']     = $fws_['comp_transf_anexo'];
        $condicoes['termo_desist_anexo']     = $fws_['termo_desist_anexo'];
        $condicoes['boletos_anexo']     = $fws_['boleto_anexo'];
        $condicoes['oficinas']      = $fws_['oficinas'];
        $condicoes['avaliacao']     = $fws_['avaliacao'];
        $condicoes['situacao']      = $fws_['situacao'];
        $condicoes['status_processo'] = $fws_['status_processo'];
        $condicoes['intervalo_registros'] = $fws_['intervalo_registros'];
        $condicoes['limite_registros'] = $fws_['limite_registros'];
        $condicoes['cod_perfil']    = $fws_['cod_perfil'];
        $condicoes['tipo_inscricao']    = $fws_['tipo_inscricao'];
        $condicoes['tipo_perfil']   = $fws_['tipo_perfil'];
        $condicoes['tipo_pedido']   = $fws_['tipo_pedido'];
        $condicoes['id_publico']   = $fws_['id_publico'];
        $condicoes['pedido_id']   = $fws_['pedido_id'];
        $condicoes['transacao_id']   = $fws_['transacao_id'];
        $condicoes['data_pagamento_ini']    = $fws_['data_pagamento_ini'];
        $condicoes['data_pagamento_fim']    = $fws_['data_pagamento_fim'];
        $condicoes['data_pagamento_incl_ini']    = $fws_['data_pagamento_incl_ini'];
        $condicoes['data_pagamento_incl_fim']    = $fws_['data_pagamento_incl_fim'];
        $condicoes['data_inscricao_ini']    = $fws_['data_inscricao_ini'];
        $condicoes['data_inscricao_fim']    = $fws_['data_inscricao_fim'];
        $condicoes['data_vencimento_ini']    = $fws_['data_vencimento_ini'];
        $condicoes['data_vencimento_fim']    = $fws_['data_vencimento_fim'];
        $condicoes['almoco']        = $fws_['almoco'];
        $condicoes['perspectiva']   = $fws_['perspectiva'];
        $condicoes['tela']              = $fws_['tela'];
        $condicoes['patrocinador_voucher'] = $fws_['patrocinador_voucher'];
        $condicoes['presenca_inscrito']    = $fws_['presenca_inscrito'];
        $condicoes['solicitaram_recurso']  = $fws_['solicitaram_recurso'];
        $condicoes['avaliacao_inscrito']  = $fws_['avaliacao_inscrito'];
        $condicoes['registro_atividade_status']  = $fws_['registro_atividade_status'];
        $condicoes['periodo_apuracao_reg_ativ_ini']  = $fws_['periodo_apuracao_reg_ativ_ini'];
        $condicoes['periodo_apuracao_reg_ativ_fim']  = $fws_['periodo_apuracao_reg_ativ_fim'];
        $condicoes['outros_filtros_inscrito'] = $fws_['outros_filtros_inscrito'];
        $condicoes['cod_evento']   = $fws_['cod_evento'];
        $condicoes['cod_evento_vinculado']   = $fws_['cod_evento_vinculado'];
        $condicoes['status_processo_vinculado']   = $fws_['status_processo_vinculado'];
        $condicoes['tipo_produto'] = $fws_['tipo_produto'];
        $condicoes['negar_filtro'] = $fws_['negar_filtro'];
        $condicoes['avaliacao_trabalhos'] = $fws_['avaliacao_trabalhos'];
        $condicoes['docs_contratacao'] = $fws_['docs_contratacao'];
        $condicoes['doc_contratacao_falta'] = $fws_['doc_contratacao_falta'];
        $condicoes['classificacao_ate'] = $fws_['classificacao_ate'];
        return $condicoes;
    }

    public function carregarXls($fws_, $debug = false)
    {

        $objInscrito = new Inscrito();
        $condicoes = $this->carregarCondicoes($fws_, $debug);
        $condicoes['media']         = 'xls';
        $condicoes['perspectiva']   = $fws_['perspectiva'];
        $codEvento = $fws_['cod_evento'];
        if ($codEvento != '') {
            $codEvento = str_pad($codEvento, 4, '0', STR_PAD_LEFT);
            $nomeArquivo = "lista-inscritos-" . $codEvento;
        } else {
            $nomeArquivo = "lista-inscritos";
        }
        if ($condicoes['pagamentos'] == 99) {
            $nomeArquivo = $nomeArquivo . "-pagos";
        }
        if ($fws_['perspectiva'] != '') {
            $nomeArquivo = $nomeArquivo . "-" . $fws_['perspectiva'];
        }
        if ($fws_['cod_oficina'] != '') {
            $objOficina = new Oficina();
            $objOficina->setCodOficina($fws_['cod_oficina']);
            $objOficina->getOficinaById();
            $numero = $objOficina->getNumeroOficial() ? $objOficina->getNumeroOficial() : $objOficina->getOrdem();
            $nomeArquivo = $nomeArquivo . '-atividade-' . lpad($numero, 2, 0);
        }
        if ($fws_['cod_perfil'] != '') {
            $objPerfil = new Perfil();
            $objPerfil->setCodPerfil($fws_['cod_perfil']);
            $objPerfil->getPerfilById();
            $nomeArquivo = $nomeArquivo . '-perfil-' . removerPontos(removerEspacos(removerAcentos($objPerfil->getTitulo())));
        }
        if ($fws_['patrocinador_voucher'] != '') {
            $nomeArquivo = $nomeArquivo . '-vouchers-' . $fws_['patrocinador_voucher'];
        }

        try {

            $objInscrito->carregarListaInscritos($condicoes, $debug);
            gerarArquivoTable2Excel($nomeArquivo, $objInscrito->getColunasListaInscritos(), $objInscrito->getListaInscritos(), $debug);
        } catch (Exception $ex) {
        }
    }

    public function carregarXml($fws_, $debug = false)
    {

        $objInscrito = new Inscrito();
        $condicoes = $this->carregarCondicoes($fws_, $debug);
        $condicoes['media']         = 'xml';
        $condicoes['perspectiva']   = $fws_['perspectiva'];

        if (is_integer($codEvento)) {
            $nomeArquivo = "lista-inscritos-" . str_pad($codEvento, 4, '0', STR_PAD_LEFT) . "-";
        } else {
            $nomeArquivo = "lista-inscritos-";
        }
        if ($condicoes['pagamentos'] == 99) {
            $nomeArquivo = $nomeArquivo . "-pagos";
        }
        if ($fws_['perspectiva'] != '') {
            $nomeArquivo = $nomeArquivo . "-" . $fws_['perspectiva'];
        }
        if ($fws_['cod_oficina'] != '') {
            $objOficina = new Oficina();
            $objOficina->setCodOficina($fws_['cod_oficina']);
            $objOficina->getOficinaById();
            $nomeArquivo = $nomeArquivo . '-atividade-' . lpad($objOficina->getOrdem(), 2, 0);
        }
        if ($fws_['patrocinador_voucher'] != '') {
            $nomeArquivo = $nomeArquivo . '-vouchers-' . $fws_['patrocinador_voucher'];
        }

        try {

            $objInscrito->carregarListaInscritos($condicoes, $debug);
            gerarArquivoTable2Xml($nomeArquivo, $objInscrito->getColunasListaInscritos(), $objInscrito->getListaInscritos(), $debug);
        } catch (Exception $ex) {
        }
    }
    
    public function carregarHtml($fws_, $debug = false)
    {

        $objInscrito = new Inscrito();
        $condicoes = $this->carregarCondicoes($fws_, $debug);
        $condicoes['media']         = 'xml';
        $condicoes['perspectiva']   = $fws_['perspectiva'];

        if (is_integer($codEvento)) {
            $nomeArquivo = "lista-inscritos-" . str_pad($codEvento, 4, '0', STR_PAD_LEFT) . "-";
        } else {
            $nomeArquivo = "lista-inscritos-";
        }
        if ($condicoes['pagamentos'] == 99) {
            $nomeArquivo = $nomeArquivo . "-pagos";
        }
        if ($fws_['perspectiva'] != '') {
            $nomeArquivo = $nomeArquivo . "-" . $fws_['perspectiva'];
        }
        if ($fws_['cod_oficina'] != '') {
            $objOficina = new Oficina();
            $objOficina->setCodOficina($fws_['cod_oficina']);
            $objOficina->getOficinaById();
            $nomeArquivo = $nomeArquivo . '-atividade-' . lpad($objOficina->getOrdem(), 2, 0);
        }
        if ($fws_['patrocinador_voucher'] != '') {
            $nomeArquivo = $nomeArquivo . '-vouchers-' . $fws_['patrocinador_voucher'];
        }

        try {

            $objInscrito->carregarListaInscritos($condicoes, $debug);
            gerarArquivoHtml($nomeArquivo, $objInscrito->getColunasListaInscritos(), $objInscrito->getListaInscritos(), $debug);
        } catch (Exception $ex) {
        }
    }
    /**@todo na geração do arquivo do edital, é necessários incluir classificação dos inscritos*/
    public function carregarEditalHtml($fws_, $debug = false)
    {
        $fws_['perspectiva'] = 'listagemAvaliacao';
        $objInscrito = new Inscrito();
        $condicoes = $this->carregarCondicoes($fws_, $debug);
        $condicoes['media']         = 'xml';
        $condicoes['perspectiva']   = $fws_['perspectiva'];

        if (is_integer($codEvento)) {
            $nomeArquivo = "lista-inscritos-" . str_pad($codEvento, 4, '0', STR_PAD_LEFT) . "-";
        } else {
            $nomeArquivo = "lista-inscritos-";
        }
        if ($condicoes['pagamentos'] == 99) {
            $nomeArquivo = $nomeArquivo . "-pagos";
        }
        if ($fws_['perspectiva'] != '') {
            $nomeArquivo = $nomeArquivo . "-" . $fws_['perspectiva'];
        }
        if ($fws_['cod_oficina'] != '') {
            $objOficina = new Oficina();
            $objOficina->setCodOficina($fws_['cod_oficina']);
            $objOficina->getOficinaById();
            $nomeArquivo = $nomeArquivo . '-atividade-' . lpad($objOficina->getOrdem(), 2, 0);
        }
        if ($fws_['patrocinador_voucher'] != '') {
            $nomeArquivo = $nomeArquivo . '-vouchers-' . $fws_['patrocinador_voucher'];
        }

        try {

            $objInscrito->carregarListaInscritos($condicoes, $debug);
            gerarArquivoEditalHtml($nomeArquivo, $objInscrito->getColunasListaInscritos(), $objInscrito->getListaInscritos(), $debug);
        } catch (Exception $ex) {
        }
    }

    public function carregarMensalidadesXls($fws_, $debug = false)
    {
        /*
        $objInscrito = new Inscrito();
        $condicoes = $this->carregarCondicoes($fws_, $debug);
        $condicoes['media']         = 'xls';
        $condicoes['perspectiva']   = $fws_['perspectiva'];
        $codEvento = $fws_['cod_evento'];
        if ($codEvento != '') {
            $codEvento = str_pad($codEvento, 4, '0', STR_PAD_LEFT);
            $nomeArquivo = "lista-inscritos-" . $codEvento;
        } else {
            $nomeArquivo = "lista-inscritos";
        }
        if ($condicoes['pagamentos'] == 99) {
            $nomeArquivo = $nomeArquivo . "-pagos";
        }
        if ($fws_['perspectiva'] != '') {
            $nomeArquivo = $nomeArquivo . "-" . $fws_['perspectiva'];
        }
        if ($fws_['cod_oficina'] != '') {
            $objOficina = new Oficina();
            $objOficina->setCodOficina($fws_['cod_oficina']);
            $objOficina->getOficinaById();
            $numero = $objOficina->getNumeroOficial() ? $objOficina->getNumeroOficial() : $objOficina->getOrdem();
            $nomeArquivo = $nomeArquivo . '-atividade-' . lpad($numero, 2, 0);
        }
        if ($fws_['cod_perfil'] != '') {
            $objPerfil = new Perfil();
            $objPerfil->setCodPerfil($fws_['cod_perfil']);
            $objPerfil->getPerfilById();
            $nomeArquivo = $nomeArquivo . '-perfil-' . removerPontos(removerEspacos(removerAcentos($objPerfil->getTitulo())));
        }
        try {
            $objInscrito->carregarListaInscritos($condicoes, $debug);
            gerarArquivoTable2Excel($nomeArquivo, $objInscrito->getColunasListaInscritos(), $objInscrito->getListaInscritos(), $debug);
        } catch (Exception $ex) {
        }
        */

        try {
            $objPagamento = new Pagamento();
            $objPagamento->setCodEvento(COD_EVENTO);
            $objPagamento->carregarListaMensalidades($debug);
            $nomeArquivo = "lista-mensalidades-" . $fws_['cod_evento'];
            $lista   = $objPagamento->getListaMensalidades();
            $colunas = $objPagamento->getColunasMensalidades();
            gerarArquivoTable2Excel($nomeArquivo,$colunas,$lista,$debug);
        } catch (Exception $ex) {}
    }
    public function carregarAcertosXls($fws_, $debug = false)
    {
        try {
            $objPagamento = new Pagamento();
            $objPagamento->setCodEvento(COD_EVENTO);
            $objPagamento->carregarListaAcertos($debug);
            $nomeArquivo = "lista-acertos-" . $fws_['cod_evento'];
            $lista   = $objPagamento->getListaAcertos();
            $colunas = $objPagamento->getColunasAcertos();
            gerarArquivoTable2Excel($nomeArquivo,$colunas,$lista,$debug);
        } catch (Exception $ex) {}
    }

    public function cabecalhoTabelaListaInscritos($fws_, $objEv, $objEvConf, $debug = false){
        ?>
        <th>Cód.<br>Inscrito</th>
        <th>Dados Pessoais</th>
        <?php if($fws_['perspectiva']=='listagemComRegistroAtividade'){?> 
            <th>Total Reg. Ativ.</th>
            <th>Total Reg. Ativ. Validados</th>
            <th>Total Reg. Ativ. Não Validados</th>
            <th>Minutos Validada</th>
            <th>Minutos Não Validada</th>
            <th>Minutos Não Trabalhados</th>
            <th>Valor a Pagar</th>
            <th>Tabela de Registros</th>
        <?php } ?>         
        <?php 
        if($fws_['perspectiva'] != 'listagemComRegistroAtividade'){ 
        /** @todo ocultando colunas para execução de tarefas */
        if (!$fws_['acaoEmLote']){
        ?>
            <?php if ($fws_['perspectiva']=='listagemAvaliacao') { ?>
                <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)){ ?>
                    <?php if ($objEvConf && $objEvConf->getTrabAvaliacao()) { ?>
                        <th>Avaliacão Inscrito</th>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
            <?php if ($fws_['perspectiva']=='listagemClassificacao') { ?>
                <th>Classificação</th>
            <?php } ?>
            <?php if($fws_['perspectiva']!='listagemPagamento' and $fws_['perspectiva']!='listagemClassificacao'){?>
            <th>Qtd. MC.</th>
            <th>Qtd. Trab.</th>
            <th>Qtd. Aval.</th>
            <?php } ?>
            <?php if ($objEvConf && $objEvConf->getRecebePagamento()){ ?>
                <!-- <th>Qtd. Pag.</th> -->
                <?php if ($fws_['xml_pagamentos']=='104'){ ?>
                    <th>Pedido</th>
                <?php } ?>
                <?php if($fws_['perspectiva']!='listagemAvaliacao' and $fws_['perspectiva']!='listagemClassificacao'){?>
                <th>Qtd. Vch.</th>
                <th title="Quantidade de pedidos">Qtd. Pedidos</th>
                <th title="Quantidade de boletos gerados">Qtd. Bol.</th>
                <th>Data Pag.</th>
                <th>Total. Pag.</th>
                <th>Valor Pag.</th>
                <?php } ?>
                <?php if ($objEvConf && $objEvConf->getAlmoco()){ ?>
                <th>Qtd. Almoços</th>
                <?php } ?>
                <?php if($fws_['perspectiva']!='listagemAvaliacao' and $fws_['perspectiva']!='listagemClassificacao'){?>
                <th>Valores</th>
                <!-- <th>Valor Inscrição</th> -->
                <?php } ?>
            <?php } ?>
        <?php
        }
        ?>
        <th>Situação</th>
        <?php
        }#($condicoes['perspectiva'] == 'listagemComRegistroAtividade')
    }

    public function carregarListaInscritos($fws_, $debug = false)
    {
        if ($fws_['cod_evento'] != '') {
            $objEv = $fws_['objEv'];
            $objEvConf = $fws_['objEvConf'];
        }
        $objInscrito = new Inscrito();

        $condicoes = $this->carregarCondicoes($fws_, $debug);
        $condicoes['media']         = 'tela';
        try {
            $debug = false;
            $objInscrito->carregarListaInscritos($condicoes, $debug);
            $tituloAnt = "";
            $inscritoAnt = "";
            $cpfAnt = "";
            $totalListados = 0;
            $valorArrecadado = 0;
            $totalRefeicoes = 0;
            $duplicados = 0;

            if (!$objInscrito->getListaInscritos()) {
                throw new Exception('Nenhum inscrito foi localizado de acordo com o filtro defindo acima.');
            }
            $totalListados = count($objInscrito->getListaInscritos());
            //            $numGrupSendMail = $totalListados/200;
            $inscritoAntAvaliacao = "";

            /**
             * Acoes em lote: decide UMA vez, antes do loop, se este envio pode executar.
             * - permissao: mesma lista do gate do ListarInscritosAcoesAC (antes era 999 cravado
             *   aqui, o que faria a liberacao por perfil nao ter efeito nenhum)
             * - token: a listagem e POST, entao F5 reenvia tudo; o token do formulario vale
             *   uma vez so e barra a segunda passagem
             */
            $podeExecutarLote = false;
            if ($fws_['acaoEmLote'] != '') {
                if (FWA_temPermissao(ListarInscritosAcoesAC::permissoesAcaoEmLote())) {
                    $podeExecutarLote = ListarInscritosAcoesAC::consumirTokenAcaoLote($fws_);
                    if (!$podeExecutarLote) {
                        ListarInscritosAcoesAC::avisoAcaoJaExecutada();
                    }
                }
            }
            foreach ($objInscrito->getListaInscritos() as $objL) {
                // echo $objL->getCodInscrito() .'==='. $inscritoAntAvaliacao . '-'.COD_EVENTO;
                if ((COD_EVENTO==80)and((string) trim($objL->getCodInscrito()) === (string) trim($inscritoAntAvaliacao))) {
                    continue;
                }
                if ($objL->getStatus() == 'EXCLUÍDA' and COD_EVENTO!='108') {
                    continue;
                }
                $inscritoAntAvaliacao = $objL->getCodInscrito();

                $totalEmailGrupo++;
                $valorArrecadado = $valorArrecadado + $objL->getValorPagamentos();
                if ($emailAnterior != $objL->getEMail()) {
                    $listaEmails = $listaEmails ? $listaEmails . ",<br/>" : "";
                    $listaEmails = $listaEmails . $objL->getEMail();
                }
                $emailAnterior = $objL->getEMail();
?>
                <!--                <tr><td colspan="15"><div id='msgInscrito-<?php echo $objL->getCodInscrito() ?>' style='float: left;margin-left:10px;'></div></td></tr>-->
                <tr id="<?php echo  $objL->getCodInscrito() ?>">
                    <td valign="top">
                        <?php $this->colunaBotoes($fws_, $objL, $objEv, $objEvConf, $condicoes, $debug) ?>
                    </td>
                    <!-- #atividade -->
                    <td valign="top">
                        <?php
                        # coluna identificacao
                        //if($condicoes['cod_evento']=='' and COD_EVENTO=='9999'){
                        echo "<b>" . $objL->getCodEvento() . ' - ' . $objL->getTituloEvento() . "</b><br>";
                        if (in_array(COD_EVENTO, [80,82,88])) {
                            echo "Cargo: ";
                            echo "<b>" . $objL->getNomePerfil() . "</b><br>";
                            echo "Cidade: ";
                            echo "<b>" . $objL->getNomeOficina() . "</b><br>";
                        } else {
                            if ($condicoes['perspectiva'] == 'listagemComAtividade' or $condicoes['perspectiva'] == 'listagemAvaliacao') {
                                if ($objEvConf && $objEvConf->getAtividadeTipo() == 'Agenda') {
                                    echo "Agendado para:<br><b>" . formatDateHoraTrans($objL->getDataAgendada(), $formatIn = 'YYYY-MM-DD', $formatOut = 'DD/MM/YYYY') . "<br>" . $objL->getNomeOficina() . "</b><br>";
                                } else {
                                    if ($objL->getCodOficina()!=''){
                                        echo "Atividade:<br>";
                                        echo "<b>" . $objL->getCodOficina() . ' - ' . $objL->getNomeOficina() . "</b><br>";
                                    }
                                }
                            }
                            echo "<br>";
                        }
                        //}

                        $colorFontInscrito = "black";
                        $notaDuplicado = "";
                        if ($objL->getNomeInscrito() == $inscritoAnt) {
                            $colorFontInscrito = "red";
                            $duplicados++;
                            $notaDuplicado = "<b>Duplicado</b>";
                        }
                        //                        else if ($objL->getCpf() == $cpfAnt) {
                        //                            $colorFontInscrito = "red";
                        //                            $duplicados++;
                        //                            $notaDuplicado = "<b>Dupla</b>";
                        //                        }
                        ?>
                        
                        <div id='carregando-<?php echo $objL->getCodInscrito() ?>' class='hidden' hidden='hidden'>
                            <img src='<?php echo WB_URL_IMG ?>/loading-9.gif' width='20' height='20' border='0'>
                        </div>
                        <div id='msgInscrito-<?php echo $objL->getCodInscrito() ?>' style='display:block;margin-left:10px;'></div>
                        <div id="msgStatusProcesso-<?php echo $objL->getCodInscrito() ?>" style='display:block;margin-left:10px;'></div>
                        
                        <?php                        
                        echo $notaDuplicado;
                        echo "<font color=" . $colorFontInscrito . "> <!--Nome Inscrito:<br> -->" . mb_strtoupper($objL->getNomeInscrito()) . "</font>";
                        $inscritoAnt = $objL->getNomeInscrito();
                        $cpfAnt = $objL->getCpf();
                        ?>
                        <br>CPF: <?php echo  $objL->getCpf() ?>
                        <?php
                        if (!validarCPF($objL->getCpf()) && !validarCNPJ($objL->getCpf())) {
                            echo "<br><span style='color:red'>Cpf Inválido</span>";
                        }
                        $this->colunaIdentificacao($fws_, $objL, $objEv, $objEvConf, $condicoes, $debug);
                        ?>
                        <?php if ($condicoes['perspectiva'] == 'listagemComRegistroAtividade') {
                            ?>
                            <?php //if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)) { ?>
                                <td><?php echo $objL->getTotalRegistroAtividades();       ?> </td>
                                <td><?php echo $objL->getTotalRegistAtivValidados();      ?> </td>
                                <td><?php echo $objL->getTotalRegistAtivNaoValidados();   ?> </td>
                                <td><?php echo $objL->getTotalCargaHorariaValidados();    ?> </td>
                                <td><?php echo $objL->getHorasNaoTrabalhadas(); ?> </td>
                                <td><?php echo $objL->getTotalCargaHorariaNaoValidados(); ?> </td>
                                <td><?php echo $objL->getValorAPagar(); ?> </td>
                                <td>
                                    <table>
                                    <?php
                                    GerenciarRegistroAtividadeAC::colunaRegistroAtividade($fws_, $objL, $objEvConf, $condicoes, $debug);
                                    ?>
                                    </table>
                                </td>
                            <?php //} ?>
                        <?php } ?>                        
                    </td>
                    <?php
                    if ($condicoes['perspectiva'] != 'listagemComRegistroAtividade'){ 
                    /** @todo ocultando colunas para execução de tarefas */
                    if (!$fws_['acaoEmLote']) {
                    ?>
                            <?php if ($condicoes['perspectiva'] == 'listagemAvaliacao') { ?>
                                <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)) { ?>
                                    <?php if ($objEvConf && $objEvConf->getTrabAvaliacao()) { ?>
                                        <td>
                                        <?php
                                        ListarInscritosAvaliacaoAC::colunaSituacaoAvaliacao($fws_, $objL, $objEvConf, $debug);
                                        ?>
                                        </td>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($condicoes['perspectiva'] == 'listagemClassificacao') { ?>
                                <td valign="top">
                                <?php self::colunaClassificacao($fws_, $objL, $objEv, $objEvConf, $condicoes, $debug); ?>
                                </td>
                            <?php } ?>
                            <?php if ($fws_['perspectiva'] != 'listagemPagamento' and $fws_['perspectiva'] != 'listagemClassificacao') { ?>
                                <td valign="top">
                                    <?php echo  $objL->getTotalOficinas() ?>
                                </td>
                                <td valign="top">
                                    <?php CarregarBotoesAC::btnListarTrabalhos($fws_, $objL, $debug); ?>
                                </td>
                                <td valign="top">
                                    <?php echo  $objL->getTotalAvaliacoes() ?>
                                </td>
                            <?php } ?>
                            <?php if ($objEvConf && $objEvConf->getRecebePagamento()){ ?>
                                <?php if ($fws_['xml_pagamentos'] == '104') { ?>
                                    <td>
                                        <?php
                                        echo $objL->getDescricaoPedido();
                                        ?>
                                    </td>
                                <?php } ?>
                            <?php if ($fws_['perspectiva'] != 'listagemAvaliacao' and $fws_['perspectiva'] != 'listagemClassificacao') { ?>
                                <td valign="top">
                                    <?php echo  $objL->getTotalVouchers() ?>
                                </td>
                                <td valign="top">
                                    <?php echo  $objL->getTotalPedidos() ?>
                                </td>
                                <td valign="top">
                                    <?php echo  $objL->getTotalBoletos() ?>
                                </td>
                                <td valign="top">
                                    <?php echo formatDateView($objL->getDataPagamento(), false) ?>
                                </td>
                                <td valign="top">
                                    <?php
                                    if ($objL->getTotalPagamentos() != 0) {
                                        echo $objL->getTotalPagamentos() . "<br>";
                                    }
                                    echo $objL->getDesIsento();
                                    ?>
                                </td>
                                <td valign="top">
                                    <?php echo  formatMoneyView($objL->getValorPagamentos()) ?>
                                </td>
                            <?php } ?>
                            <?php
                            $numRefeicaoInscrito = 0;
                            if ($this->selecionarAlmoco) { ?>
                                <td valign="top">
                                    <?php
                                    if ($objL->getInfoAlmoco() != '' and $objL->getInfoAlmoco() != 'null') {
                                        foreach (json_decode($objL->getInfoAlmoco()) as $value) {
                                            echo $value . "<br>";
                                            $totalRefeicoes = $totalRefeicoes + 1;
                                            $numRefeicaoInscrito = $numRefeicaoInscrito + 1;
                                        }
                                    }
                                    ?>
                                </td>
                            <?php } ?>
                            <?php if ($fws_['perspectiva'] != 'listagemAvaliacao' and $fws_['perspectiva'] != 'listagemClassificacao') { ?>
                                <td valign="top">
                                    <?php
                                    $vlrInscricaoPrevisto = 0;
                                    /*Definindo valor esperado para o boleto de acordo com a data do pagamento do mesmo */
                                    if ($dataCompararPagamento != '') {
                                        $dataCompararPagamento = date('Ymd', strtotime(substr($objL->getDataPagamento(), 0, 10)));
                                    }
                                    //                    if($dataCompararPagamento < 20200315){
                                    //                        $vlrInscricaoPrevisto = 15000;
                                    //                    }else if ($dataCompararPagamento < 20200515){
                                    //                        $vlrInscricaoPrevisto = 20000;
                                    //                    }else if ($dataCompararPagamento < 20200707){
                                    //                        $vlrInscricaoPrevisto = 25000;
                                    //                    }else{
                                    //                        $vlrInscricaoPrevisto = 30000;
                                    //                    }
                                    $vlrTotalPagoPrevisto = 0;

                                    $vlrTotalRefeicaoInscrito = $numRefeicaoInscrito * $this->valorAlmoco;

                                    $vlrTotalAtividadeInscrito = $objL->getTotalOficinas() * $this->valorOficina;
                                    $totalEventoAtividade = $totalEventoAtividade + $objL->getTotalOficinas();
                                    $vlrTotalEventoAtividade = $vlrTotalEventoAtividade + $vlrTotalAtividadeInscrito;

                                    //formatMoneyView($objL->getValorPagamentos())
                                    $vlrTotalPago = $objL->getValorPagamentos();
                                    $vlrTotalPagoPrevisto = $vlrInscricaoPrevisto + $vlrTotalRefeicaoInscrito + $vlrTotalAtividadeInscrito;
                                    //                    $vlrTotalPagoPrevisto = $vlrTotalPagoPrevisto.'00;

                                    if ($vlrTotalPago) {
                                        $vlrInscricaoPago = $vlrTotalPago - $vlrTotalRefeicaoInscrito - $vlrTotalAtividadeInscrito;
                                        //                        echo "Pago:".formatMoneyView($vlrTotalPago)."<br>";
                                        echo "Esperado:" . formatMoneyView($objL->getValorTotalPedidos()) . "<br>";

                                        //                        $vlrDiferenca = $vlrTotalPagoPrevisto-$vlrTotalPago;
                                        //                        if($vlrDiferenca != 0){
                                        //                            echo "<font color=red>Verificar valores</font>";
                                        //                        }

                                        echo "<br>";
                                        if ($this->selecionarAlmoco) {
                                            echo "Refeição:" . formatMoneyView($vlrTotalRefeicaoInscrito) . "<br>";
                                        }
                                        if ($this->valorOficina) {
                                            echo "Atividade:" . formatMoneyView($vlrTotalAtividadeInscrito) . "<br>";
                                        }
                                        //                        echo "Inscrição:".formatMoneyView($vlrInscricaoPrevisto)."<br>";
                                        //                        echo "Inscrição:".formatMoneyView($vlrInscricaoPago)."<br>";
                                        //                        if($vlrInscricaoPago>0){
                                        //                            $totalValorInscricaoEvento = $totalValorInscricaoEvento+$vlrInscricaoPago;
                                        //                        }
                                    } else {
                                        $vlrInscricaoPago = "";
                                    }
                                    if ($vlrInscricaoPrevisto > 0) {
                                        $totalValorInscricaoEventoPrevisto = $totalValorInscricaoEventoPrevisto + $vlrInscricaoPrevisto;
                                    }
                                    ?>
                                </td>
                                <!--
                                <td>
                                    <?php
                                    //echo formatMoneyView($vlrInscricaoPago)."<br>";
                                    //                        echo "Previsto: ".formatMoneyView($vlrInscricaoPrevisto)."<br>";
                                    ?>
                                </td>
                                -->
                            <?php } #fim não possui pagamento?>
                            <?php
                            } 
                        } /**Fim if Não é loete de taremas $fws_['acaoEmLote'] */
                        ?>
                        <td valign="top">
                            <?php echo  $objL->getStatus() ?>
                            <?php
                            /**
                             * Ações em lote
                             */
                            if ($podeExecutarLote) {
                                $objAcoes = new ListarInscritosAcoesAC();
                                $objAcoes->executarAcoesEmLote($objL, $fws_, $debug);
                            }
                            ?>
                        </td>
                </tr>
        <?php
            }
            }   #($condicoes['perspectiva'] == 'listagemComRegistroAtividade')

            /* ZIP de certificados gerados em lote */
            if ($fws_['acaoEmLote'] == 'gerarCertificado') {
                ListarInscritosAcoesAC::gerarZipCertificados();
            } else {
                ListarInscritosAcoesAC::exibirBotaoDownloadZip();
            }

            $vlrTotalRefeicoes = $totalRefeicoes * $this->valorAlmoco;
            echo "<br><b>De acordo com o filtro acima:</b>";
            echo "<br>Total de registros encontrados: {$objInscrito->getCountListaInscritos()}";

            $numeroPaginas = $objInscrito->getCountListaInscritos() / 1000;
            //CarregarBotoesAC::btnTotalPaginas($fws_,$numeroPaginas, $debug);
            if ($totalListados >= 4000) {
                echo "<br><font color=red>Atingiu 4000 registros, entrar em contato com administrador.</font>";
            }
            echo "<br>Total de inscritos listados: {$totalListados}";
            echo "<br>Total de nomes duplicados: {$duplicados}";
            if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONSULTAR_PAINEL_BORDO, 5, $cod_msg, 0)) {
                if (COD_EVENTO == 31 or COD_EVENTO == 32  or COD_EVENTO == 91) {
                    echo "<br>Valor arrecadado listados: " . formatMoneyView($valorArrecadado);
                } else {
                    if (COD_EVENTO == 45) {
                        echo "<br>Valor arrecadado listados: " . formatMoneyView($valorArrecadado);
                    } else {
                        echo "<br>Valor arrecadado listados: " . formatMoneyBoleto($valorArrecadado);
                    }
                }
                //            echo "<br>Valor em incrições previsto: ".formatMoneyView($totalValorInscricaoEventoPrevisto);
                //            echo "<br>Valor em incrições listados: ".formatMoneyView($totalValorInscricaoEvento);
            }
            if ($this->selecionarAlmoco) {
                echo "<br>Total em refeições (não necessariamente pagas): " . $totalRefeicoes;
                echo " - Valor total R$ " . formatMoneyView($vlrTotalRefeicoes);
            }
            if ($this->selecionarOficina) {
                if ($condicoes['cod_oficina'] != '') {
                    echo "<br>Total inscritos em minicursos " . $totalEventoAtividade;
                }
                if ($this->valorOficina) {
                    echo " - Valor total R$ " . formatMoneyView($vlrTotalEventoAtividade);
                }
            }
            echo "<br><a href='javascript:mostrarListaEmails();'>Lista de e-mails</a>";
            $this->mostrarListaEmails($listaEmails, $debug);
        } catch (Exception $ex) {
            if ((@FWA_Autenticacao($_SESSION['_cod_usuario'], '1', '5', false, false) == true)
                or (@FWA_Autenticacao($_SESSION['_cod_usuario'], '13', '5', false, false) == true)
            ) {
                echo "<a href='".WB_URL_VIEW."?content=view/admin/inscritos-cadastro-ok.php'>"
            . "<img src='resources/image/fig-incluir.gif' border='0' width='20px'> Cadastrar Inscrito</a>";
            }
        }
    }

    public function mostrarListaEmails($emails, $debug = false)
    {
        ?>
        <script type="text/javascript">
            function mostrarListaEmails() {
                <?php if ($emails != '') { ?>
                    var generator = window.open('', 'name', 'height=400,width=300,resizable=no,scrollbars=yes,toolbar=no,status=no');
                    generator.document.write('<html><head><title>E-mails</title>');
                    generator.document.write('</head><body><font size=2 face=verdana>');
                    generator.document.write('<fieldset><?php echo $emails ?></fieldset>');
                    generator.document.write('<p><a href="javascript:self.close()">Fechar</a> janela.</p>');
                    generator.document.write('</body></html>');
                    generator.document.close();
                <?php } else { ?>
                    alert("Nenhum registro selecionado.");
                <?php } ?>
            }
        </script>
    <?php
    }

    public function alternarPresencaOficina($fws_, $debug = false)
    {
        try {
            $obj = new InscritoOficina();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodOficina($fws_['cod_oficina']);
            $obj->setStatus('ATIVADA');
            $flgPresenca = $obj->getFlgPresenca();
            if ($obj->getFlgPresenca() == '1') {
                $obj->setFlgPresenca(NULL);
                $msg = "Não compareceu";
            } else {
                $obj->setFlgPresenca('1');
                $msg = "Compareceu";
            }
            $obj->salvarPresenca($debug);
        } catch (OKException $exc) {
            $flgPresenca = $obj->getFlgPresenca();
            $statusExcec = 1;
        } catch (Exception $exc) {
            $statusExcec = 0;
        }
        echo '{
            "flgPresenca":"' . $flgPresenca . '",
            "message":"' . $msg . '", 
            "statusMessage":"' . $statusExcec . '"
            }';
        exit();
    }
    public function excluirInscritoOficina($fws_, $debug = false)
    {
        try {
            $obj = new InscritoOficina();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodOficina($fws_['cod_oficina']);
            $obj->getByCodInscritoCodOficina();
            if ($obj->getStatus() != 'ATIVADA') {
                throw new Exception("Não foi possível carregar registro para exclusão.");
            }
            $flgPresenca = $obj->excluir();
        } catch (OKException $exc) {
            $statusExcec = 1;
        } catch (Exception $exc) {
            $statusExcec = 0;
        }
        echo '{
            "message":"<b>' . $exc->getMessage() . '</b>",
            "statusMessage":"' . $statusExcec . '"
            }';
        exit();
    }

    public function alternarPresencaInscrito($fws_, $debug = false)
    {
        try {
            $obj = new Inscrito();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->getInscritoById();
            if ($obj->getStatus() != 'ATIVADA') {
                throw new Exception("Não foi possível carregar os dados do inscrito");
            }
            $flgPresencaInscrito = $obj->getFlgPresencaInscrito();
            if ($obj->getFlgPresencaInscrito() == '1') {
                $obj->setFlgPresencaInscrito(NULL);
                $msg = "Não compareceu";
            } else {
                $obj->setFlgPresencaInscrito('1');
                $msg = "Compareceu";
            }
            $obj->salvarPresenca($debug);
        } catch (OKException $exc) {
            $flgPresencaInscrito = $obj->getFlgPresencaInscrito();
            $statusExcec = 1;
        } catch (Exception $exc) {
            $msg = "Erro, não alterou para " . $msg;
            $statusExcec = 0;
        }
        echo '{
            "flgPresencaInscrito":"' . $flgPresencaInscrito . '",
            "message":"<b>' . $msg . '</b>",
            "statusMessage":"' . $statusExcec . '"}';
        exit();
    }

    public function alterarStatusProcesso($fws_, $debug = false)
    {
        $statusPermitidos = ['INSCRITO', 'CONVOCADO', 'PARTICIPANDO', 'DESISTENTE', 'CANCELADO', 'SUSPENSO'];
        $statusProcessoNovo = strtoupper(trim($fws_['statusProcessoNovo']));
        $codInscrito = intval($fws_['cod_inscrito']);

        try {
            if (!in_array($statusProcessoNovo, $statusPermitidos)) {
                throw new Exception("Status no Processo informado não é válido.");
            }
            $obj = new Inscrito();
            $obj->setCodInscrito($codInscrito);
            $obj->getInscritoById();
            if ($obj->getStatus() != 'ATIVADA') {
                throw new Exception("Inscrição não está ativada.");
            }
            $obj->setStatusProcesso($statusProcessoNovo);
            $obj->salvar();
        } catch (OKException $exc) {
            $msg = "Status no Processo alterado para " . $statusProcessoNovo;
            $statusExcec = 1;
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $statusExcec = 0;
        }
        echo '{"message":"<b>' . $msg . '</b>","statusMessage":"' . $statusExcec . '"}';
        exit();
    }

    public function alterarStatusInscricao($fws_, $debug = false)
    {
        $statusMap = [
            'Excluir'   => 'EXCLUÍDA',
            'Ativar'    => 'ATIVADA',
            'Desativar' => 'DESATIVADA'
        ];
        $acao = $fws_['acao'];
        $codInscrito = intval($fws_['cod_inscrito']);
        $resumoPag = null;
        $codEventoInscrito = '';
        // O aviso ao financeiro e OPT-IN: quem cancela responde "deseja
        // notificar o financeiro?" na tela. Sem a flag, nao sai e-mail.
        $notificarFinanceiro = !empty($fws_['notificar_financeiro'])
                            && $fws_['notificar_financeiro'] !== '0';

        try {
            if (!isset($statusMap[$acao])) {
                throw new Exception("Ação informada não é válida.");
            }
            $novoStatus = $statusMap[$acao];

            $obj = new Inscrito();
            $obj->setCodInscrito($codInscrito);
            $obj->getInscritoById();
            $codEventoInscrito = $obj->getCodEvento();

            // Inscricao PAGA sendo desativada/excluida: preparar o aviso ao
            // financeiro do gestor ANTES do salvar (o sucesso cai no catch OKException)
            if ($acao !== 'Ativar' && $notificarFinanceiro) {
                $resumoPag = (new PagamentoDAO())->resumoPagamentosAtivos($codEventoInscrito, $codInscrito);
            }

            // Arquivar registro antes de alterar
            $obj->arquivarParaExcluidos();

            $obj->setStatus($novoStatus);
            $obj->salvar();
        } catch (OKException $exc) {
            $msg = "Inscrição " . $codInscrito . ": " . $acao . " executado com sucesso";
            $statusExcec = 1;

            // Aviso ao financeiro do gestor (estorno/multa) — nunca quebra a acao
            if ($resumoPag && $resumoPag['total'] > 0) {
                try {
                    GerenciarNotificacaoFinanceiroAC::notificarCancelamentoPago(
                        $codEventoInscrito,
                        $codInscrito,
                        'admin-listagem',
                        array(
                            'acao'   => $novoStatus,
                            'valor'  => $resumoPag['valor'],
                            'formas' => $resumoPag['formas'],
                        )
                    );
                    $msg .= ". Financeiro do gestor avisado por e-mail (inscrição paga)";
                } catch (Exception $eNotif) {
                    error_log('[NOTIF_FINANCEIRO] alterarStatusInscricao: ' . $eNotif->getMessage());
                }
            }
        } catch (Exception $exc) {
            $novoStatus = '';
            $msg = $exc->getMessage();
            $statusExcec = 0;
        }
        echo '{"message":"<b>' . $msg . '</b>","statusMessage":"' . $statusExcec . '","novoStatus":"' . mb_strtolower($novoStatus) . '"}';
        exit();
    }

    public static function colunaBotoes($fws_, $objL, $objEv, $objEvConf, $condicoes, $debug = false){ 
    ?>
        <?php if ($objL->getStatus() == 'EXCLUÍDA') { ?> 
            <img src='resources/image/fig-excluir.gif' border='0' width='20px' title='Inscrição Excluída'>
            Inscrição Excluída
            <br>
        <?php } else { ?>
        <?php
        echo '<span id="statusInscricao-' . $objL->getCodInscrito() . '" style="color:' . ($objL->getStatus() != 'ATIVADA' ? 'red' : 'green') . '">';
        echo  $objL->getCodInscrito() . ' - ' . mb_strtolower($objL->getStatus()) . '</span>';
        ?>
        <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_LOGAR_COMO, 5, false, false)) { ?>
            <?php if ($objL->getStatus() == 'ATIVADA') { ?>
                <?php CarregarBotoesAC::btnLogarComo($fws_, $objL, $debug); ?>

                <br>
            <?php } ?>
        <?php } ?>

        <?php
        // if($objL->getStatusProcesso()!='' and $objL->getStatusProcesso()!='INSCRITO' ){
        if ($objL->getStatusProcesso() != '') {
            if (in_array($objL->getCodEvento(), [80, 88, 115])) {
                // select em btnGerenciarInscrito substitui o texto estático
            } else {
                echo 'Status no Processo: <font color=';
                echo $objL->getStatusProcessoAtivo() == false ? 'red>' : 'green>';
                echo  maiusculo($objL->getStatusProcesso()) . '</font><br>';
            }        
        }
        ?>
        <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_INSCRITO, 5, false, false)) {
            /** Inclui os botões para excluir, editar, ativar, desativar e novo. */
            CarregarBotoesAC::btnGerenciarInscrito($fws_, $objL, $debug);
            if ($objL->getStatus() == 'ATIVADA') { 
             //CarregarBotoesAC::btnGerenciarEtiqueta($fws_,$objL,$debug); 
            }
            if ($fws_['cod_oficina'] != '' and $objL->getCodOficina() != '') { ?>
                <br><br>
                <?php CarregarBotoesAC::btnExcluirAtividade($fws_, $objL, $debug);
            }
            if($objEvConf && $objEvConf->getRecebePagamento()){
                if ($objL->getStatus() == 'ATIVADA' and $objL->getQrCode() != '') {
                    CarregarBotoesAC::btnListarBoletos($fws_, $objL, $debug);
                    //CarregarBotoesAC::btnListarBoletosAvulso($fws_, $objL, $debug);
                }
            }
            ?>
        <?php } ?>
        <br><br>
        <?php
        if ($condicoes['perspectiva'] != 'listagemAvaliacao' && $objEvConf && $objEvConf->getControleParticipacao() == 1) {
            $checkedPresencaInscrito = $objL->getFlgPresencaInscrito() ? "checked" : "";
            CarregarBotoesAC::btnChecarPresencaInscrito($fws_, $objL, $checkedPresencaInscrito, $debug);
            echo "<div id='statusPresencaInscrito-" . $objL->getCodInscrito() . "' style='float: left;margin-left:10px;'>Presença Inscrito</div>";
        ?>
            <br><br>
            <?php
            if ($objL->getCodOficina() != '') {
                $checkedPresencaOficina = $objL->getFlgPresencaOficina() ? "checked" : "";
                CarregarBotoesAC::btnChecarPresencaOficina($fws_, $objL, $checkedPresencaOficina);
                echo "<div id='statusPresencaOficina-" . $objL->getCodInscrito() . "-" . $objL->getCodOficina() . "' style='float: left;margin-left:10px;'>Presença na Oficina " . $objL->getCodOficina() . "</div><br>";
            }
            ?>

            <?php
            if($objEvConf && $objEvConf->getRecebePagamento()){
                CarregarBotoesAC::btnGerenciarIsento($fws_, $objL, $debug);
            }
            ?>
            <input type="hidden" value="<?php echo  $objL->getCodIsento() ?>" id="codInscritoIsento-<?php echo $objL->getCodInscrito() ?>">
            <div id="txtInscritoIsento-<?php echo $objL->getCodInscrito() ?>">
                <?php
                if ($objL->getCodIsento()) {
                    echo "Cód.Isento: " . $objL->getCodIsento() . "<br>";
                }
                ?>
            </div>

            <?php
            if($objEvConf && $objEvConf->getRecebePagamento() and $objEvConf->getEmitirVoucher()){
                CarregarBotoesAC::bntGerenciarVoucherInscrito($fws_, $objL, $debug);
            }
            ?>
            <input type="hidden" value="<?php echo  $objL->getCodVoucher() ?>" id="codInscritoVoucher-<?php echo $objL->getCodInscrito() ?>">
            <div id="txtInscritoVoucher-<?php echo $objL->getCodInscrito() ?>">
                <?php
                if ($objL->getCodVoucher()) {
                    echo "Cód.Voucher: " . $objL->getCodVoucher() . "<br>";
                }
                ?>
            </div>
        <?php } ?>
        <?php
        if ($fws_['cod_oficina']) {
            //                          CarregarBotoesAC::btnChecarPresenca($fws_, $objL, $checkedPresenca);
        }
        ?>
        <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_ENVIAR_MENSAGEM, 5, false, false)) { ?>
            <?php CarregarBotoesAC::btnLinkWhatsOla($fws_, $objL, $debug); ?>
            <?php if ($objL->getStatus() == 'ATIVADA') { ?> 
            <br>
            <?php CarregarBotoesAC::btnLoginWhats($fws_, $objL, $debug); ?>
            <!-- <br><?php //CarregarBotoesAC::btnMessageSac($fws_, $objL, $debug); 
                        ?> -->
            <?php CarregarBotoesAC::btnEnviarSenhaAdmin($fws_, $objL, $debug); ?>
            <?php } ?>
        <?php } ?>
        <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_ENVIAR_MENSAGEM, 5, false, false)) { ?>
            <?php if ((($objL->getStatus() == 'ATIVADA') and ($objL->getEmitirCertificado() == 1)) or ($objL->getCodEvento() == 10)) { ?>
                <?php CarregarBotoesAC::btnEnviarCertificados($fws_, $objL, $debug); ?>
            <?php } ?>
        <?php } ?>
        <?php if ($objL->getCodOficina() != '') { ?>
            <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_ASSOCIAR_PERFIS, 5, false, false)) { ?>
                <?php if ((($objL->getStatus() == 'ATIVADA') and ($objL->getEmitirCertificado() == 1)) or ($objL->getCodEvento() == 10)) { ?>
                    <?php //if($condicoes['perspectiva']=='listagemComAtividade'){
                    ?>
                    <?php CarregarBotoesAC::btnGerarCertificadoAtividade($fws_, $objL, $debug); ?>
                    <?php //} 
                    ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
        <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_ENVIAR_MENSAGEM, 5, false, false)) { ?>
            <?php if($objEvConf && $objEvConf->getRecebePagamento()){ ?>
                <?php if (($objL->getStatus() == 'ATIVADA') and
                    ($objL->getAvisoMensalidade() == 1 or ($objL->getAvisoPagamento()==1 and $objL->getTotalPagamentos()<=0))) {
                    CarregarBotoesAC::btnPagamentoWhats($fws_, $objL, $debug);
                    CarregarBotoesAC::btnLembrarPagamentoWhats($fws_, $objL, $objEv, $debug);
                }
                ?>            
                <?php if (($objL->getStatus() == 'ATIVADA') and ($objL->getAvisoMensalidade() == 1)) { ?>
                    <?php CarregarBotoesAC::btnLembrarMensalidade($fws_, $objL, $debug); ?>
                <?php }
                if (($objL->getStatus() == 'ATIVADA') and ($objL->getAvisoPagamento()==1 and $objL->getTotalPagamentos()<=0)) { ?>
                    <?php CarregarBotoesAC::btnLembrarPagamento($fws_, $objL, $debug) ?>
                <?php } ?>
            <?php } ?>
            
            <?php if (($objL->getStatus() == 'ATIVADA') and ($objL->getAvisoAulaOnline() == 1)) { ?>
                <?php //@todo criar se já não existir aviso de aula online CarregarBotoesAC::btnLembrarPagamento($fws_, $objL, $debug) ?>
            <?php } ?>
            <?php if (($objL->getStatus() == 'ATIVADA') and ($objEvConf && $objEvConf->getTrabSubmissao() == 1)) { ?>
                <?php //if ($OP_SUBMISSAO_TRABALHO_ENCERRADA==true){ 
                ?>
                <?php //CarregarBotoesAC::btnLembrarEnvioTrabalho($fws_, $objL, $debug); ?>
                <?php //} 
                ?>
            <?php } ?>
        <?php } ?>
        <?php } ?>
    <?php
    }

    public static function colunaClassificacao($fws_, $objL, $objEv, $objEvConf, $condicoes, $debug = false) {
        $statusProcesso = $objL->getStatusProcesso() ?: 'INSCRITO';
        $corStatus = $objL->getStatusProcessoAtivo() == false ? 'red' : 'green';
        $pontuacaoFinal = $objL->getPontosTotalAposRecurso() ?: $objL->getPontosTotalConfirmado();
        $posicao = $objL->getClassificacaoFinal();
        ?>
        <b>Status no Processo:</b>
        <span style="color:<?php echo $corStatus ?>; font-weight:bold;">
            <?php echo maiusculo($statusProcesso) ?>
        </span>
        <hr style="margin:4px 0;">
        <b>Posição:</b> <?php echo $posicao ?: '-' ?>
        &nbsp;|&nbsp;
        <b>Pontuação:</b> <?php echo $pontuacaoFinal ?: '-' ?>
        <br>
        <b>Resultado:</b>
        <?php ListarInscritosAvaliacaoAC::descClassificacaoFinal($objL); ?>
        <hr style="margin:4px 0;">
        <b>Docs Contratação:</b><br>
        <?php CarregarBotoesAC::indicadorDocsContratacao($fws_, $objL, $debug); ?>
    <?php
    }

    public static function colunaIdentificacao($fws_, $objL, $objEv, $objEvConf, $condicoes, $debug = false){
        ?>                        
        <?php //if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_ENVIAR_MENSAGEM, 5, false, false)) { ?>
            <br>Id Público: <?php echo  $objL->getIdPublico() ?>
            <br>E-mail:
            <?php CarregarBotoesAC::btnEnviarEmail($fws_, $objL, $debug); ?>
            <!-- <br><?php CarregarBotoesAC::btnLinkEmailGmail($fws_, $objL, $debug); ?> -->
            <br>fone: <?php echo  $objL->getFoneContatoDdd() . " " . $objL->getFoneContato() ?>
        <?php //} ?>
        <?php
        // if (in_array($objL->getCodEvento(), [80, 88, 115])) {
        //     CarregarBotoesAC::indicadorDocsContratacao($fws_, $objL, $debug);
        // }
        ?>
        <?php if ($condicoes['perspectiva'] == 'listagemAvaliacao') { ?>
            <?php ListarInscritosAvaliacaoAC::detalhesAvaliacao($fws_, $objL, $objEvConf, $debug); ?>
        <?php } ?>
        <?php if ($fws_['perspectiva'] == 'detalhada') { ?>
            <br>
            <br>Rg: <?php echo  $objL->getRg() ?>
            <br>Data Nascimento: <?php echo  formatDateView($objL->getDataNascimento()) ?>
            <br>Endereço: <?php echo  $objL->getEndereco() ?>, N.<?php echo  $objL->getNumero() ?>, <?php echo  $objL->getComplemento() ?>
            <br>Bairro: <?php echo  $objL->getBairro() ?> - <?php echo  $objL->getCep() ?>
            <br><?php echo  $objL->getCidade() ?> - <?php echo $objL->getEstado() ?> -
            <br>Perfil Principal: <?php echo  $objL->getCodPerfil() ?> - <?php echo  $objL->getNomePerfil() ?>
        <?php } ?>
        <?php
        echo $objL->getImpressaoCracha() ? "<br>Retirou crachá" : "";
        ?>
        <?php echo $objL->getAvaliouEvento() ?   "<br>Avaliou evento" : "" ?>
        <?php if ($fws_['forma_pagamento'] == 'PAG.TERCEIRO') { ?>
            <br><b>Recibo em nome de terceiro</b>
            <br>Nome: <?php echo  $objL->getNomeRecibo() ?> - Cpf/Cnpj: <?php echo  $objL->getCpfRecibo() ?>
            <br>Endereço: <?php echo  $objL->getEnderecoRecibo() ?>, N.<?php echo  $objL->getNumeroRecibo() ?>, <?php echo  $objL->getComplementoRecibo() ?>, <?php echo  $objL->getBairroRecibo() ?>
            <br><?php echo  $objL->getCidadeRecibo() ?> - <?php echo $objL->getEstadoRecibo() ?> - <?php echo  $objL->getCepRecibo() ?>
        <?php } ?>
        <?php /*
        <br>
        <div id='carregando-<?php echo $objL->getCodInscrito() ?>' class='hidden' hidden='hidden'>
            <img src='<?php echo WB_URL_IMG ?>/loading-9.gif' width='20' height='20' border='0'>
        </div>
        <div id='msgInscrito-<?php echo $objL->getCodInscrito() ?>' style='float: left;margin-left:10px;color: red;'></div>
        */ ?>
        <?php
        //                    if($_SESSION['_cod_usuario']=='388'){
        if ($condicoes['cod_evento'] != '' and $condicoes['cod_evento'] != '-1') {
            if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_ASSOCIAR_PERFIS, 5, false, false)) {
                echo "<br><b>Associar Grupos/Certificado</b><br>";

                if ($objL->getCodInscrito()) {
                    $fws_['cod_inscrito']               = $objL->getCodInscrito();
        ?>
                    <?php CarregarBotoesAC::btnLiberarCertificados($fws_, $objL, $debug); ?>
        <?php
                    try {
                        $objL->carregarListaPerfil(false, $debug);
                        $fws_['lista_perfil_administrativo'] = $objL->getListaPerfil();
                    } catch (Exception $exc) {
                        $fws_['lista_perfil_administrativo'] = ""; //$objL->getListaPerfil();
                    }
                    /** @todo corrigir essa listagem, estava dando problema com algumas inscrições, por exemplo a inscrição 27730*/
                    // $objGInscPerfil = new GerenciarInscritoPerfilAC();
                    // $fws_['cod_evento'] = $objL->getCodEvento();
                    // $objGInscPerfil->carregarListaPerfisCheckbox($fws_, 1);
                } else {
                    echo "Inscrito não cadastrado no evento";
                }
            }
        }
        ?>
        <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_INSCRITO, 5, false, false)) { ?>
                <?php echo "<br><br>Data de cadastro: " . formatDateView($objL->getDatIncl()); ?>
                <?php if ($fws_['perspectiva'] == 'detalhada') { ?>
                    <br>Dt. Ultimo Login: <?php echo formatDateView($objL->getDataUltimoLogin()) ?>
                    <br>Sit. Ultimo Login:
                    <?php
                    if ($objL->getSucesso() == 1) {
                        echo 'Logou';
                    } else if ($objL->getSucesso() === 0) {
                        echo '<font color=red>Falha no Login</font>';
                    } else {
                        echo '<font color=red>Nunca Tentou Logar</font>';
                    }
                    ?>
                    <br>Qtd.Tentativas: <?php echo $objL->getTentantivasLogin(); ?>
                <?php } ?>
                <?php
                if ($objEvConf && $objEvConf->getRecebePagamento()) {
                    if (($objL->getStatusDataDesativar(3) == true) and ($objL->getTotalPagamentos() < 1)) {
                        echo "<br><font color='red'>Apta a ser desativada: " . $objL->getDataDesativar() . "</font>";
                    } else {
                        if ($objL->getTotalPagamentos() < 1) {
                            echo "<br><font color='green'>Último dia para pagamento: " . $objL->getDataDesativar() . "</font>";
                        }
                    }
                }
                ?>
        <?php } ?>
        <?php
        if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_ASSOCIAR_PERFIS, 5, false, false)) {
            if ($objL->getFlgPresencaInscrito()) {
                // ListarInscritosAcoesAC::gerarCertificados($fws_, $objL, $debug);
            }
        }
        ?>
        <br>&nbsp;
        <?php
    }    
}
