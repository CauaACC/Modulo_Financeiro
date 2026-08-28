<?php

class GerenciarIngressoAC
{

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    public function gerarIngresso($fws_, $debug = false)
    {

        $arquivo_existente = '/recibo_pessoal-' . $_SESSION['_cod_inscrito'] . '.pdf';

        if (file_exists(WB_PATH_LOG_RECIBOS . $arquivo_existente) and $_SESSION['_cod_inscrito'] != '8175') {

            $fws_['nomeArquivoIngresso'] = $arquivo_existente;

            header("Content-Type: application/pdf");
            header("Content-Disposition: inline; filename=" . $fws_['nomeArquivoIngresso']);

            readfile(WB_PATH_LOG_RECIBOS . $arquivo_existente);
        } else {
            /*carregando texto*/
            try {
                $fws_ = $this->carregarTextoIngresso($fws_);
            } catch (Exception $exc) {
                echo $exc->getMessage();
                exit();
            }

            /*mesclando com o modelo*/
            ob_start();
            header('Content-Type: text/html; charset=ISO-8859-1');
            //        header("Content-Type: application/pdf");
            //        header("Content-Disposition: inline; filename=".$fws_['nomeArquivoIngresso']);
            include(dirname(__FILE__) . '/../view/recibos/modelos/' . $fws_['modelo_template']);
            $content = ob_get_clean();

            /*gerando pdf*/
            require_once(dirname(__FILE__) . '/../lib/html2pdf/html2pdf.class.php');
            $html2pdf = new HTML2PDF('P', 'A4', 'pt');
            $html2pdf->setTestTdInOnePage(false);
//            if($_SESSION['_cod_inscrito']=='8175'){
//                $_GET['vuehtml'] = 1;
//            }
//            $html2pdf->WriteHTML($content, isset($_GET['vuehtml']));
            $html2pdf->WriteHTML($content, 1);

            if (!file_exists(WB_PATH_LOG_RECIBOS)) {
                mkdir(WB_PATH_LOG_RECIBOS, 0755);
            }
            $html2pdf->Output(WB_PATH_LOG_RECIBOS . '/' . $fws_['nomeArquivoIngresso'], 'F'); // grava o arquivo em disco na primeira vez que ? visualizado

            $html2pdf->Output($fws_['nomeArquivoIngresso']); // grava o arquivo em disco na primeira vez que ? visualizado
        }


        exit();
    }

    /**
     * @param type $fws_
     * @param type $debug
     * @return string
     * @throws Exception
     */
    function carregarTextoIngresso($fws_, $debug = false)
    {

        $objPagamento = new Pagamento();
        $objPagamento->setCodPagamento($fws_['cod_pagamento']);
        $objPagamento->carregarPagamentoById();


        if (!$_SESSION['_cod_inscrito']) {
            if ($_SESSION['_administrativo'] == '1') {
                if (!$objPagamento->getFormaPagamento()) {
                    throw new Exception("Inscrito não esta associado a forma de pagamento.");
                }
                $fws_['cod_inscrito'] = $objPagamento->getCodInscrito();
            } else {
                throw new Exception("Sessão do usuário encerrada, retorno ao link anterior.");
            }
        } else {
            $fws_['cod_inscrito'] = $_SESSION['_cod_inscrito'];
        }

        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($fws_['cod_inscrito']);
        $objInscrito->getInscritoById();

        $fws_['string_qr_code'] = $objInscrito->getQrCode();

        if ($objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos()) {
            throw new Exception("Emissão de Ingresso bloqueada.<br>Caso necessário, "
                . "entre em contato com a administração do evento para regularizar sua inscrição.");
        }

        if ($objInscrito->getNomeInscrito() == '') {
            throw new Exception("Inscrito não localizado.");
        }

        if (!$objPagamento->getFormaPagamento()) {
            throw new Exception("Inscrito não esta associado a forma de pagamento.");
        }

        $fws_['nome_inscrito'] = maiusculo($objInscrito->getNomeInscrito());
        $fws_['cod_inscrito'] = $objInscrito->getCodInscrito();
        if ($objInscrito->getInstitucional() == 1) {
            $fws_['tipoIngresso'] = 'recibo_empresa';
        } else {
            $fws_['tipoIngresso'] = 'recibo_pessoal';
        }

        $fws_['valor'] = formatMoneyView($objPagamento->getValor());
        $fws_['cod_pagamento'] = $objPagamento->getCodPagamento();
        $fws_['forma_pagamento'] = $objPagamento->getFormaPagamento();
        $fws_['numero_empenho'] = $objPagamento->getNumeroEmpenho();
        $fws_['data_pagamento'] = substr($objPagamento->getDataPagamento(), 0, 10);

        $fws_['observacao_recibo'] = '';
        if (trim($objPagamento->getObservacaoIngresso()) != '') {
            $fws_['observacao_recibo'] = " " . $objPagamento->getObservacaoIngresso() . ", ";
        }
        if ($fws_['data_pagamento'] != '') {
            $this->dataEmissao = "" . $this->cidadeEvento . ", " . formatDateViewPorExtenso($fws_['data_pagamento']);
        }
        if ($objPagamento->getDataEmissaoIngresso() != '' and $objPagamento->getDataEmissaoIngresso() != '0000-00-00') {
            $this->dataEmissao = "" . $this->cidadeEvento . ", " . formatDateViewPorExtenso($objPagamento->getDataEmissaoIngresso());
        }

        $textoIdPagador = "{$objInscrito->getNomeInscrito()}, {$objInscrito->getTipoDocCpfCnpjInscrito()}:{$objInscrito->getCpfView()}";
        if ($fws_['tipoIngresso'] == 'recibo_empresa') {
            $textoIdPagador = "{$objInscrito->getNomeIngresso()}, {$objInscrito->getTipoDocCpfCnpjIngresso()}:{$objInscrito->getCpfIngressoView()}";
        }

        $textoIdInscrito = "";
        if ($fws_['tipoIngresso'] == 'recibo_empresa') {
            $textoIdInscrito = " de {$objInscrito->getNomeInscrito()}, {$objInscrito->getTipoDocCpfCnpjInscrito()}:{$objInscrito->getCpfView()}, ";
        }

        $fws_['nomeArquivoIngresso'] = $fws_['tipoIngresso'] . '-' . $fws_['cod_inscrito'] . '.pdf';
        $fws_['nomeArquivoValidacao'] = $fws_['tipoIngresso'] . '-' . $fws_['cod_inscrito'];
        $fws_['modelo_template'] = "modelo-recibo.php";

        switch ($fws_['forma_pagamento']) {
            case 'TRANSFERENCIA':
                $fws_['texto_recibo'] = ""
                    . "Recebemos de {$textoIdPagador},"
                    . $fws_['observacao_recibo']
                    . " a importância de R$({$fws_['valor']}),"
                    . " resgitrada na inscrição número {$objInscrito->getCodInscrito()},"
                    . " referente a pagamento de inscrição para a " . $this->nomeEvento . ","
                    . " realizado na cidade de " . $this->cidadeEvento . ", no período de " . $this->periodoEvento . "."
                    . "<br><br><br><br> Por ser verdade firmo o presente."
                    . " ";
                break;
            case 'DOACAO':
                $fws_['texto_recibo'] = ""
                    . "Recebemos de {$textoIdPagador},"
                    . " a importância de R$({$fws_['valor']}),"
                    . " resgitrada na inscrição número {$objInscrito->getCodInscrito()}, "
                    . " em Doação para a " . $this->nomeEvento . ","
                    . " realizado na cidade de " . $this->cidadeEvento . ", no período de " . $this->periodoEvento . "."
                    . "<br><br><br><br> Por ser verdade firmo o presente."
                    . " ";
                break;
            case 'APOIO':
                $fws_['texto_recibo'] = ""
                    . "Recebemos de {$textoIdPagador},"
                    . $fws_['observacao_recibo']
                    . " a importância de R$({$fws_['valor']}),"
                    . " resgitrada na inscrição número {$objInscrito->getCodInscrito()},"
                    . " em Apoio ao " . $this->nomeEvento . ","
                    . " realizado na cidade de " . $this->cidadeEvento . ", no período de " . $this->periodoEvento . "."
                    . "<br><br><br><br> Por ser verdade firmo o presente."
                    . " ";
                break;
            case 'EMPENHO':
                if (trim($fws_['numero_empenho']) != '') {
                    $fws_['texto_recibo'] = ""
                        . "Recebemos de {$textoIdPagador},"
                        . $fws_['observacao_recibo']
                        . " a importância de R$({$fws_['valor']}), via empenho número {$fws_['numero_empenho']},"
                        . " registrado na inscrição número {$objInscrito->getCodInscrito()},"
                        . $textoIdInscrito
                        . " referente a pagamento de inscrição para a " . $this->nomeEvento . ","
                        . " realizado na cidade de " . $this->cidadeEvento . ", no período de " . $this->periodoEvento . "."
                        . "<br><br><br><br> Por ser verdade firmo o presente."
                        . " ";
                } else {
                    $fws_['texto_recibo'] = ""
                        . "Recebemos de {$textoIdPagador},"
                        . $fws_['observacao_recibo']
                        . " a importância de R$({$fws_['valor']}), via empenho,"
                        . " registrado na inscrição número {$objInscrito->getCodInscrito()},"
                        . $textoIdInscrito
                        . " referente a pagamento de inscrição para a " . $this->nomeEvento . ","
                        . " realizado na cidade de " . $this->cidadeEvento . ", no período de " . $this->periodoEvento . "."
                        . "<br><br><br><br> Por ser verdade firmo o presente."
                        . " ";
                }
                break;
            case 'INEXIGIBILIDADE':
                if (trim($fws_['numero_empenho']) != '') {
                    $fws_['texto_recibo'] = ""
                        . "Recebemos de {$textoIdPagador},"
                        . " a importância de R$({$fws_['valor']}), inexigibilidade número {$fws_['numero_empenho']},"
                        . " registrado na inscrição número {$objInscrito->getCodInscrito()},"
                        . $textoIdInscrito
                        . " referente a pagamento de inscrição para a " . $this->nomeEvento . ","
                        . " realizado na cidade de " . $this->cidadeEvento . ", no período de " . $this->periodoEvento . "."
                        . "<br><br><br><br> Por ser verdade firmo o presente."
                        . " ";
                } else {
                    $fws_['texto_recibo'] = ""
                        . "Recebemos de {$textoIdPagador},"
                        . " a importância de R$({$fws_['valor']}), via inexigibilidade,"
                        . " registrado na inscrição número {$objInscrito->getCodInscrito()},"
                        . $textoIdInscrito
                        . " referente a pagamento de inscrição para a " . $this->nomeEvento . ","
                        . " realizado na cidade de " . $this->cidadeEvento . ", no período de " . $this->periodoEvento . "."
                        . "<br><br><br><br> Por ser verdade firmo o presente."
                        . " ";
                }
                break;
            default :
                try {
                    $objPedido = new Pedido();
                    $objPedido->setInscritoId($objInscrito->getCodInscrito());
                    $objPedido->setEventoId($objInscrito->getCodEvento());
                    $objPedido->carregarListaPedidosByInscritoId();

                    $data1 = new DateTime($objInscrito->getDataEstadiaIni());
                    $data2 = new DateTime($objInscrito->getDataEstadiaFim());
                    $intervalo = $data1->diff($data2);
                    //            echo "Intervalo é de {$intervalo->y} anos, {$intervalo->m} meses e {$intervalo->d} dias";
                    ob_start();
                    foreach ($objPedido->getListaPedidos() as $objLI) {
                        echo "<span style='color:black;font-size:15px;'>";
                        echo "<br><br><b>Descrição do Pedido " . $objLI->getInscritoId() . " - " . numberPad($objLI->getId(), 8) . ":</b>";
                        //echo "<br>".$objLI->getDescricao()." - R$ ".$objLI->getValorTotal()."<br>";
                        $objIngresso = new Ingresso();
                        $objIngresso->setEventoId($objPedido->getCodEvento());
                        $objIngresso->setPedidoId($objPedido->getId());
                        $objIngresso->carregarListaIngressosByPedidoId();
                        echo "<br>Pessoas cadastradas:<br>";
                        echo "<table with=150px><tr><td style='width: 155px;'>Nome</td><td>Rg</td></tr>";
                        foreach ($objIngresso->getListaIngressos() as $objLI) {
                            echo "<tr style='font-size:12px;'><td>" . $objLI->getNome() . '</td><td>' . $objLI->getRg() . '</td></tr>';
                        }
                        echo "</table>";
                        echo ""
                            . "<br>Quantidade de Pessoas: " . count($objIngresso->getListaIngressos())
                            . "<br>Quantidade de Dias: ", $intervalo->d
                            . "<br>Valor total do pedido: R$ " . formatMoneyView($objPedido->getValorTotal())
                            . "</span>";
                    }

                } catch (Exception $exc) {
                    echo $exc->getMessage() . '<br>';
                }
                $content = ob_get_clean();
                ob_start();
                echo ""
                    . "Recebemos de {$textoIdPagador},"
                    . " a importância de R$({$fws_['valor']}),"
                    . " registrada na inscrição número {$objInscrito->getCodInscrito()},"
                    . trim($textoIdInscrito)
                    . " referente a pagamento de compra de ingresso(s) para entrada e permanencia na "
                    . $content
                    . "<br>"
                    . " ";
                $content = ob_get_clean();
                $fws_['texto_recibo'] = $content;
        }

        $fws_['cabecalho'] = ""
            . "<br>" . $this->nomeOrganizador . "<br>" . $this->enderecoOrganizador . "<br>" . $this->cnpjOrganizador
            . "";
        $fws_['data_emissao'] = $this->dataEmissao;
        $fws_['link_validacao'] = $this->urlBase . '/recibos/validar/?id=' . $fws_['nomeArquivoValidacao'];
        $fws_['assinatura'] = $this->assinaturaOrganizador;
        if ($this->telefoneOrganizador) {
            $fws_['assinatura'] .= '<br>Telefone: ' . $this->telefoneOrganizador;
        }
        return $fws_;
    }

    public function validarIngresso($fws_, $debug = false)
    {
        $idArquivo = $fws_['idArquivo'];
        $filepath = WB_PATH_LOG_RECIBOS;
        $arquivo = $idArquivo . ".pdf";
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath . $arquivo)) {
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/pdf");
//            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"" . $arquivo . "\"");
            header("Content-Length: " . @filesize($filepath . $arquivo));
            header("Pragma: public");
            header("Expires: 0");
            readfile($filepath . $arquivo) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.location='" . WB_URL_VIEW . "';</script>";
        }
    }

    /**
     *
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function comunicadoIngressoLiberado($obj, $retorno = 'BOOLEAN', $debug = false)
    {

        $msgTo = '';
        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO . " - Comunicado de Liberação de Ingressos";
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= $msgSubject . "\r";
        $msgBody .= "Ola: " . $obj->getNomeInscrito() . "\r\r";
        $msgBody .= "Os recibos do evento já estão liberados.\r";
        $msgBody .= "Para emissão de recibos acesse sua área de inscrito.\r\r";
        $msgBody .= "Login: {$obj->getEmailInscrito()}\r";
        $msgBody .= "Senha: {$obj->getCodInscrito()}\r";
        $msgBody .= $this->urlBase . "/recibos\r\r";
        $msgBody .= "Esta é uma mensagem automática, favor não responder.\r";
        $msgBody .= "att. Comissão Organizadora do " . ABREV_EVENTO . "\r\n";
        $msgBody .= "Dúvidas: " . EMAIL_CONTATO . "\r\n";
        $msgBody .= "";

        if ($debug) {
            //echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $obj->getEmailInscrito();
        //$msgTo = "contato@opctec.com.br";
        //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
        $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
        //$enviou = $enviarEmail();
        //$enviou = false;
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') {
                return false;
            } else {
                echo "<font color='red'><b>E-mail não enviado</b></font><br>";
            }
        } else {
            if ($retorno == 'BOOLEAN') {
                return true;
            } else {
                echo "E-mail enviado - {$obj->getEmailInscrito()}<br>";
            }
        }
    }

    public function carregarIngresso($fws_, $debug = false)
    {
        $obj = new Ingresso();
        $obj->setId($fws_['id']);
        $obj->getIngressoById($debug);
        return $obj;
    }

    public function editarIngresso($fws_, $debug = false)
    {
        $obj = $this->carregarIngresso($fws_, $debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":"' . $obj->getMessage() . '",
                  "inscrito_id": "' . $obj->getInscritoId() . '",
                  "nome":"' . $obj->getNome() . '",
                  "rg": "' . $obj->getRg() . '",
                  "idade": "' . $obj->getIdade() . '",
                  "categoria": "' . $obj->getCategoria() . '",
                  "valor": "' . $obj->getValor() . '",
                  "dias": "' . $obj->getDias() . '"
                 }';
        exit();
    }

    public function salvarIngresso($fws_, $debug = 1)
    {
        try {
            if ($fws_['novoId'] == 0) {
                $fws_['id'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarIngresso($fws_, $debug);
            $obj->setAll($fws_, $isForm);

            $valor = formatMoneyInsert($obj->getValorIngresso());
            $obj->setValorIngresso($valor);

            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "id":"' . $obj->getId() . '"
        }';
    }

    public function excluirIngresso($fws_, $debug = false)
    {
        try {
            $obj = new Ingresso();
            $obj->setId($fws_['id']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "id":"' . $fws_['id'] . '"
        }';
    }

    public function identificarPortador($fws_, $debug = false)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // 1. Validar sessão logada
            if (empty($_SESSION['_cod_inscrito'])) {
                throw new Exception('Sessão expirada. Faça login novamente.');
            }

            // 2. Validar campos obrigatórios
            $ingressoId = isset($fws_['ingresso_id']) ? trim($fws_['ingresso_id']) : '';
            $nome       = isset($fws_['nome_portador']) ? trim($fws_['nome_portador']) : '';
            $email      = isset($fws_['email_portador']) ? trim($fws_['email_portador']) : '';
            $telefone   = isset($fws_['telefone_portador']) ? trim($fws_['telefone_portador']) : '';

            if (empty($ingressoId)) {
                throw new Exception('Ingresso não informado.');
            }
            if (empty($nome)) {
                throw new Exception('Nome do portador é obrigatório.');
            }
            if (empty($email)) {
                throw new Exception('E-mail do portador é obrigatório.');
            }
            if (empty($telefone)) {
                throw new Exception('Telefone do portador é obrigatório.');
            }

            // 3. Carregar ingresso
            $obj = new Ingresso();
            $obj->setId($ingressoId);
            $obj->setEventoId(COD_EVENTO);
            $obj->getIngressoById($debug);

            if (empty($obj->getNome())) {
                throw new Exception('Ingresso não encontrado.');
            }

            // 4. Verificar que o ingresso pertence ao inscrito logado
            if ($obj->getInscritoId() != $_SESSION['_cod_inscrito']) {
                throw new Exception('Você não tem permissão para alterar este ingresso.');
            }

            // 5. Verificar se já foi identificado
            if ($obj->getPortadorIdentificado() == 1) {
                throw new Exception('Este ingresso já foi identificado e não pode ser alterado.');
            }

            // 6. Atualizar dados do portador
            $obj->setNome($nome);
            $obj->setEmail($email);
            $obj->setTelefone($telefone);
            $obj->setPortadorIdentificado(1);

            // 7. Salvar via DAO update (bypass validatorObj que exige campos do form admin)
            $objDAO = new IngressoDAO();
            $result = $objDAO->update($obj, $debug);

            if ($result) {
                echo json_encode(array(
                    'statusMessage' => true,
                    'message'       => 'Portador identificado com sucesso.',
                    'nome'          => $nome,
                    'email'         => $email,
                    'telefone'      => $telefone
                ));
            } else {
                throw new Exception('Erro ao atualizar o ingresso.');
            }
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    public function carregarTabelaIngressos($fws_, $debug = false)
    {
        $obj = new Ingresso();
        try {
            $lista = $obj->getAll($debug);
            $ii = 0;

            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo $objL->getId() ?>">
                    <td>
                        <img class="editar"
                             src="<?php echo WB_PATH ?>/resources/image/alterar.gif" alt="Editar Avaliação Inscrito"
                             border="0">
                        <img class="excluir"
                             src="<?php echo WB_PATH ?>/resources/image/fig-excluir.gif"
                             alt="Excluir Avaliação Inscrito"
                             border="0">
                    </td>

                    <td><?php echo textoMaiusculoMinusculo($objL->getNome()) ?></td>
                    <td><?php echo $objL->getRg() ?></td>
                    <td><?php echo $objL->getIdade() ?></td>
                    <td><?php echo $objL->getCategoria() ?></td>
                    <td><?php echo formatMoneyInView($objL->getValor()) ?></td>
                    <td><?php echo $objL->getDias() ?></td>
                    <td><?php echo $objL->getQuantidade() ?></td>
                    <td><?php
                        echo $objL->getJaInscrito();
                        if ($objL->getJaInscrito()) {
                            $totalJaInscrito++;
                            ?>
                            <a title='Logar Como' target="_blank"
                               href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $objL->getCpf() ?>&cod_inscrito=<?php echo $objL->getCodInscricao() ?>&cod_evento=<?php echo COD_EVENTO ?>'>
                                <img src='<?php echo WB_URL_IMG ?>/icone_preview.gif' border='0'></a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            <br>Total de Ingressos cadastrados: <?php echo $ii ?>.
            <?php
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=3>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }
}