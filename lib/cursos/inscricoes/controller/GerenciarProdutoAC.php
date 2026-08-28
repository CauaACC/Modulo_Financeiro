<?php

/**
 * Catalogo de produtos e movimentacao de estoque, por gestor.
 *
 * Mesmo gate e mesmo isolamento do cadastro de emissor: o catalogo de um
 * gestor nao pode aparecer (nem ser editado) pelo financeiro de outro.
 */
class GerenciarProdutoAC {

    function __construct() {
        FWA_exigirPermissao(array(enumSistema::SIS_CONTROLE_FINANCEIRO, enumSistema::SIS_GERENCIAR_ADM_GERAL));
        $this->exigirProjetoLoja();
    }

    /**
     * Modulo comercial so vale em evento classificado como Loja (aba Projeto).
     * Fica no construtor para cobrir todos os metodos: sem isso, bastaria
     * chamar o CommandAC direto para operar vendas num projeto que nao vende.
     */
    private function exigirProjetoLoja() {
        try {
            $conf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
            $ehLoja = $conf && $conf->getTipoProjeto() == 'LOJA';
        } catch (Exception $e) {
            $ehLoja = false;
        }
        if (!$ehLoja) {
            throw new Exception("Modulo de Vendas nao habilitado neste projeto. "
                . "Classifique o evento como 'Loja (venda de mercadoria)' em Configuracao do evento > Projeto.");
        }
    }


    // ------------------------------------------------------------------
    // Isolamento por gestor
    // ------------------------------------------------------------------

    private function ehAdmGeral() {
        $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
        return (bool) @FWA_Autenticacao($codUsuario, enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false);
    }

    /**
     * @return array|null null = pode ver todos (ADM_GERAL); array = so estes.
     */
    private function getGestoresPermitidos($debug = false) {
        if ($this->ehAdmGeral()) {
            return null;
        }
        $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
        $obj = new NotaFiscalEmissor();
        $lista = $obj->getObjDAO()->listarGestoresAtivos($codUsuario, $debug);

        $cods = array();
        foreach ($lista as $g) {
            $cods[] = (int) $g['cod_gestor'];
        }
        return $cods;
    }

    private function assegurarAcessoAoGestor($codGestor, $debug = false) {
        $permitidos = $this->getGestoresPermitidos($debug);
        if ($permitidos === null) {
            return; // ADM_GERAL
        }
        if (!in_array((int) $codGestor, $permitidos, true)) {
            throw new Exception("Este produto pertence a outro gestor.");
        }
    }

    /**
     * Numero vindo do form, aceitando as duas escritas.
     *
     * Se tem virgula, ela e o decimal e o ponto e separador de milhar
     * ("1.234,50"); sem virgula, o PONTO E O DECIMAL ("1234.50").
     *
     * A distincao importa: remover o ponto sempre — que e o que se faz quando
     * so se espera pt-BR — transforma "10.5" em "105". No primeiro teste de
     * estoque isso lancou 100 no lugar de 10 e o saldo saiu 10x errado.
     *
     * @return string|null null quando vazio (para gravar NULL, nao zero)
     */
    private function numeroDoForm($valor) {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        return $valor;
    }

    private function carregarProduto($fws_, $debug = false) {
        $obj = new Produto();
        if (isset($fws_['codProduto']) && $fws_['codProduto'] != '') {
            $obj->setCodProduto($fws_['codProduto']);
            if ($obj->getProdutoById($debug)) {
                $this->assegurarAcessoAoGestor($obj->getCodGestor(), $debug);
            }
        }
        return $obj;
    }

    // ------------------------------------------------------------------
    // Catalogo
    // ------------------------------------------------------------------

    public function carregarTabelaProdutos($fws_, $debug = false) {
        $obj = new Produto();
        $filtros = array(
            'busca'         => isset($fws_['busca']) ? $fws_['busca'] : '',
            'soIncompletos' => !empty($fws_['soIncompletos']),
            'codGestor'     => isset($fws_['filtroGestor']) ? $fws_['filtroGestor'] : ''
        );
        $lista = $obj->listarProdutos($this->getGestoresPermitidos($debug), $filtros, $debug);

        if (empty($lista)) {
            echo "<tr><td colspan='8' style='text-align:center; padding:24px; color:#999;'>"
               . "Nenhum produto encontrado. Cadastre um produto ou importe uma venda — "
               . "o produto tambem nasce sozinho na primeira importacao."
               . "</td></tr>";
            return;
        }

        foreach ($lista as $item) {
            $ativo = $item->getStatus() == 'ATIVADA';
            $incompleto = (int) $item->getFlgIncompleto() === 1;
            $saldo = (float) $item->getEstoqueAtual();

            echo "<tr id='" . (int) $item->getCodProduto() . "'>";
            echo "<td class='col-codigo'>"
               . "<a href='#' class='btn-acao editar' title='Editar'><i class='fa fa-pencil'></i></a>"
               . "<a href='#' class='btn-acao estoque' title='Estoque'><i class='fa fa-cubes'></i></a>"
               . "<a href='#' class='btn-acao alternar' title='" . ($ativo ? 'Inativar' : 'Reativar') . "'>"
               . "<i class='fa fa-power-off'></i></a>"
               . "</td>";
            echo "<td class='col-codigo'>" . htmlspecialchars((string) $item->getSku()) . "</td>";
            echo "<td class='col-nome'>" . htmlspecialchars((string) $item->getDescricao()) . "</td>";
            echo "<td class='col-codigo'>"
               . ($item->getNcm() != '' ? htmlspecialchars((string) $item->getNcm())
                                        : "<span style='color:#c0392b;'>sem NCM</span>")
               . "</td>";
            echo "<td class='col-codigo'>" . htmlspecialchars((string) $item->getUnidade()) . "</td>";

            // Saldo negativo e sintoma legitimo (vendeu mais do que tinha
            // cadastrado), por isso aparece destacado em vez de escondido.
            $corSaldo = $saldo < 0 ? '#c0392b' : ($item->abaixoDoMinimo() ? '#e67e22' : '#333');
            echo "<td class='col-num' style='text-align:right; color:" . $corSaldo . "; font-weight:600;'>"
               . number_format($saldo, 3, ',', '.') . "</td>";
            echo "<td>" . htmlspecialchars((string) $item->getNomeGestor()) . "</td>";

            if ($incompleto) {
                $badge = "<span class='badge-tipo'><span class='dot dot-red'></span>Incompleto</span>";
            } elseif (!$ativo) {
                $badge = "<span class='badge-tipo'><span class='dot dot-blue'></span>Inativo</span>";
            } else {
                $badge = "<span class='badge-tipo'><span class='dot dot-green'></span>OK</span>";
            }
            echo "<td class='col-badge'>" . $badge . "</td>";
            echo "</tr>";
        }
    }

    public function editarProduto($fws_, $debug = false) {
        $obj = $this->carregarProduto($fws_, $debug);

        echo json_encode(array(
            'codProduto'     => $obj->getCodProduto(),
            'codGestor'      => $obj->getCodGestor(),
            'sku'            => $obj->getSku(),
            'descricao'      => $obj->getDescricao(),
            'ncm'            => $obj->getNcm(),
            'unidade'        => $obj->getUnidade(),
            'origem'         => $obj->getOrigem(),
            'cest'           => $obj->getCest(),
            'csosn'          => $obj->getCsosn(),
            'valorVenda'     => $obj->getValorVenda(),
            'estoqueAtual'   => $obj->getEstoqueAtual(),
            'estoqueMinimo'  => $obj->getEstoqueMinimo(),
            'flgIncompleto'  => $obj->getFlgIncompleto(),
            'status'         => $obj->getStatus()
        ), JSON_UNESCAPED_UNICODE);
    }

    public function salvarProduto($fws_, $debug = false) {
        $ok = false;
        $e = null;
        $obj = null;
        try {
            foreach (array('valorVenda', 'estoqueMinimo') as $campo) {
                $fws_[$campo] = $this->numeroDoForm(isset($fws_[$campo]) ? $fws_[$campo] : '');
            }
            // Saldo nunca vem da tela: so muda por movimento de estoque.
            unset($fws_['estoqueAtual']);

            $obj = $this->carregarProduto($fws_, $debug);
            $obj->setAll($fws_, true);
            $this->assegurarAcessoAoGestor($obj->getCodGestor(), $debug);
            $obj->gravar($debug);
        } catch (OKException $ex) {
            $ok = true;
            $e = $ex;
        } catch (Exception $ex) {
            $ok = false;
            $e = $ex;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $e ? $e->getMessage() : '',
            'codProduto'    => $obj ? $obj->getCodProduto() : ''
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Produto nao se exclui: fica ligado a movimentos e a notas emitidas.
     */
    public function alternarStatusProduto($fws_, $debug = false) {
        $ok = false;
        $e = null;
        try {
            $obj = new Produto();
            $obj->setCodProduto(isset($fws_['codProduto']) ? $fws_['codProduto'] : '');
            if (!$obj->getProdutoById($debug)) {
                throw new Exception("Produto nao encontrado.");
            }
            $this->assegurarAcessoAoGestor($obj->getCodGestor(), $debug);
            $obj->setStatus($obj->getStatus() == 'ATIVADA' ? 'DESATIVADA' : 'ATIVADA');
            $obj->gravar($debug);
        } catch (OKException $ex) {
            $ok = true;
            $e = $ex;
        } catch (Exception $ex) {
            $ok = false;
            $e = $ex;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $e ? $e->getMessage() : ''
        ), JSON_UNESCAPED_UNICODE);
    }

    // ------------------------------------------------------------------
    // Estoque
    // ------------------------------------------------------------------

    public function movimentarEstoque($fws_, $debug = false) {
        $ok = false;
        $e = null;
        $saldo = null;
        $aviso = '';
        try {
            $obj = $this->carregarProduto($fws_, $debug);
            if (!$obj->getCodProduto()) {
                throw new Exception("Produto nao encontrado.");
            }

            $qtd = $this->numeroDoForm(isset($fws_['quantidade']) ? $fws_['quantidade'] : '');
            if ($qtd === null) {
                throw new Exception("Quantidade nao foi informada.");
            }

            $mov = $obj->movimentarEstoque(
                isset($fws_['tipo']) ? $fws_['tipo'] : '',
                $qtd,
                array(
                    'documento' => isset($fws_['documento']) ? $fws_['documento'] : '',
                    'motivo'    => isset($fws_['motivo']) ? $fws_['motivo'] : ''
                ),
                $debug
            );

            $saldo = $mov->getSaldoApos();
            // Negativo nao impede o registro — avisa. Ver EstoqueMovimento.
            if ((float) $saldo < 0) {
                $aviso = "Atencao: o saldo ficou NEGATIVO (" . number_format((float) $saldo, 3, ',', '.') . "). "
                       . "Isso costuma significar venda de item que ainda nao teve entrada registrada.";
            }
            $ok = true;
            $e = new OKException("Movimento registrado. Saldo atual: "
                . number_format((float) $saldo, 3, ',', '.'));
        } catch (OKException $ex) {
            $ok = true;
            $e = $ex;
        } catch (Exception $ex) {
            $ok = false;
            $e = $ex;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $e ? $e->getMessage() : '',
            'saldo'         => $saldo,
            'aviso'         => $aviso
        ), JSON_UNESCAPED_UNICODE);
    }

    public function carregarExtratoEstoque($fws_, $debug = false) {
        $obj = $this->carregarProduto($fws_, $debug);
        if (!$obj->getCodProduto()) {
            echo "<tr><td colspan='6' style='text-align:center; padding:20px; color:#999;'>"
               . "Produto nao encontrado.</td></tr>";
            return;
        }

        $mov = new EstoqueMovimento();
        $lista = $mov->listarPorProduto($obj->getCodProduto(), 100, $debug);

        if (empty($lista)) {
            echo "<tr><td colspan='6' style='text-align:center; padding:20px; color:#999;'>"
               . "Nenhum movimento registrado para este produto.</td></tr>";
            return;
        }

        foreach ($lista as $item) {
            $delta = $item->getDeltaDoSaldo();
            $cor = $delta < 0 ? '#c0392b' : '#27ae60';
            echo "<tr>";
            $quando = (string) $item->getDataInclusao();
            $quando = $quando != '' ? date('d/m/Y H:i', strtotime($quando)) : '';
            echo "<td class='col-codigo'>" . $quando . "</td>";
            echo "<td class='col-codigo'>" . htmlspecialchars((string) $item->getTipo()) . "</td>";
            echo "<td class='col-num' style='text-align:right; color:" . $cor . "; font-weight:600;'>"
               . ($delta > 0 ? '+' : '') . number_format($delta, 3, ',', '.') . "</td>";
            echo "<td class='col-num' style='text-align:right;'>"
               . number_format((float) $item->getSaldoApos(), 3, ',', '.') . "</td>";
            echo "<td class='col-codigo'>" . htmlspecialchars((string) $item->getOrigem())
               . ($item->getOrigemId() != '' ? ' #' . htmlspecialchars((string) $item->getOrigemId()) : '')
               . "</td>";
            echo "<td>" . htmlspecialchars((string) $item->getMotivo())
               . ($item->getDocumento() != ''
                    ? " <span style='color:#999;'>(doc: " . htmlspecialchars((string) $item->getDocumento()) . ")</span>"
                    : '')
               . "</td>";
            echo "</tr>";
        }
    }

    /**
     * Options de gestor para o form — mesma regra do cadastro de emissor.
     */
    public function carregarOptionsGestor($fws_, $debug = false) {
        $obj = new NotaFiscalEmissor();
        $codUsuario = $this->ehAdmGeral() ? null
                    : (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
        $lista = $obj->getObjDAO()->listarGestoresAtivos($codUsuario, $debug);

        $selecionado = isset($fws_['codGestor']) ? $fws_['codGestor'] : '';
        echo "<option value=''>Selecione...</option>";
        foreach ($lista as $row) {
            $sel = ($selecionado != '' && $selecionado == $row['cod_gestor']) ? " selected" : "";
            echo "<option value='" . (int) $row['cod_gestor'] . "'" . $sel . ">"
               . htmlspecialchars($row['nome_gestor']) . "</option>";
        }
    }

}
