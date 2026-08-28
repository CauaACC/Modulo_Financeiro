<?php

/**
 * Cadastro das empresas emissoras de nota fiscal, uma ou mais por gestor.
 *
 * Os dados aqui sao os do PRESTADOR no padrao nacional da NFS-e. Mexer neles
 * muda o que vai para a prefeitura, entao o gate e SO ADM_GERAL -- configuracao
 * fiscal da empresa nao e operacao do dia a dia de quem tem o financeiro.
 *
 * Quem EMITE nota (GerenciarNotaFiscalAC, GerenciarVendaAC) nao passa por aqui:
 * le o emissor pelo domain NotaFiscalEmissor, que nao tem gate. Restringir este
 * controller nao bloqueia a emissao.
 */
class GerenciarNotaFiscalEmissorAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    // ------------------------------------------------------------------
    // Isolamento por gestor
    //
    // O emissor pertence ao GESTOR, nao ao evento. Sem estes filtros, o
    // financeiro de um evento enxergava (e editava) os dados fiscais de
    // outro gestor — CNPJ, regime, serie de numeracao.
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

    /**
     * Barra acesso a emissor de gestor alheio — inclusive quando o codigo
     * chega direto na requisicao.
     */
    private function assegurarAcessoAoGestor($codGestor, $debug = false) {
        $permitidos = $this->getGestoresPermitidos($debug);
        if ($permitidos === null) {
            return; // ADM_GERAL
        }
        if (!in_array((int) $codGestor, $permitidos, true)) {
            throw new Exception("Este emissor pertence a outro gestor.");
        }
    }

    public function carregarEmissor($fws_, $debug = false) {
        $obj = new NotaFiscalEmissor();
        if (isset($fws_['codEmissor']) && $fws_['codEmissor'] != '') {
            $obj->setCodEmissor($fws_['codEmissor']);
            if ($obj->getEmissorById($debug)) {
                $this->assegurarAcessoAoGestor($obj->getCodGestor(), $debug);
            }
        }
        return $obj;
    }

    public function editarEmissor($fws_, $debug = false) {
        $obj = $this->carregarEmissor($fws_, $debug);

        echo json_encode(array(
            'codEmissor'                  => $obj->getCodEmissor(),
            'codGestor'                   => $obj->getCodGestor(),
            'ambiente'                    => $obj->getAmbiente(),
            'razaoSocial'                 => $obj->getRazaoSocial(),
            'cnpj'                        => $obj->getCnpj(),
            'inscricaoMunicipal'          => $obj->getInscricaoMunicipal(),
            'codigoMunicipio'             => $obj->getCodigoMunicipio(),
            'logradouro'                  => $obj->getLogradouro(),
            'numero'                      => $obj->getNumero(),
            'complemento'                 => $obj->getComplemento(),
            'bairro'                      => $obj->getBairro(),
            'cep'                         => $obj->getCep(),
            'uf'                          => $obj->getUf(),
            'telefone'                    => $obj->getTelefone(),
            'email'                       => $obj->getEmail(),
            'codigoOpcaoSimplesNacional'  => $obj->getCodigoOpcaoSimplesNacional(),
            'regimeEspecialTributacao'    => $obj->getRegimeEspecialTributacao(),
            'codigoTributacaoNacionalIss' => $obj->getCodigoTributacaoNacionalIss(),
            'codigoNbs'                   => $obj->getCodigoNbs(),
            'tributacaoIss'               => $obj->getTributacaoIss(),
            'tipoRetencaoIss'             => $obj->getTipoRetencaoIss(),
            'percentualAliquota'          => $obj->getPercentualAliquota(),
            'informacoesComplementares'   => $obj->getInformacoesComplementares(),
            'serieDps'                    => $obj->getSerieDps(),
            'proximoNumeroDps'            => $obj->getProximoNumeroDps(),

            // Bloco de NF-e (mercadoria). Vazio nos emissores que so fazem servico.
            'inscricaoEstadual'           => $obj->getInscricaoEstadual(),
            'codigoRegimeTributario'      => $obj->getCodigoRegimeTributario(),
            'csosnPadrao'                 => $obj->getCsosnPadrao(),
            'cstPisCofins'                => $obj->getCstPisCofins(),
            'cfopDentroEstado'            => $obj->getCfopDentroEstado(),
            'cfopForaEstado'              => $obj->getCfopForaEstado(),
            'naturezaOperacao'            => $obj->getNaturezaOperacao(),
            'indicadorPresenca'           => $obj->getIndicadorPresenca(),
            'indicadorConsumidorFinal'    => $obj->getIndicadorConsumidorFinal(),
            'indicadorIeDestinatario'     => $obj->getIndicadorIeDestinatario(),
            'indicadorIntermediador'      => $obj->getIndicadorIntermediador(),
            'tipoPagamento'               => $obj->getTipoPagamento(),
            'modalidadeFrete'             => $obj->getModalidadeFrete(),
            'informacoesComplementaresNfe' => $obj->getInformacoesComplementaresNfe(),
            'serieNfe'                    => $obj->getSerieNfe(),
            'proximoNumeroNfe'            => $obj->getProximoNumeroNfe(),

            'status'                      => $obj->getStatus()
        ), JSON_UNESCAPED_UNICODE);
    }

    public function salvarEmissor($fws_, $debug = false) {
        $ok = false;
        $e = null;
        $obj = null;
        try {
            // Aliquota vem do form em pt-BR ("2,50"): normaliza para decimal.
            if (isset($fws_['percentualAliquota']) && $fws_['percentualAliquota'] !== '') {
                $fws_['percentualAliquota'] = str_replace(',', '.', str_replace('.', '', $fws_['percentualAliquota']));
            } else {
                $fws_['percentualAliquota'] = null;
            }

            // Contador da NF-e e coluna int. Vindo vazio do form, setAll gravaria
            // string vazia por cima da sequencia — em MySQL estrito isso derruba
            // o save, e fora dele zera o contador. Vazio significa "comeca do 1".
            if (!isset($fws_['proximoNumeroNfe']) || trim((string) $fws_['proximoNumeroNfe']) === '') {
                $fws_['proximoNumeroNfe'] = 1;
            }

            // Ambiente e' opcional: vazio significa "usa o global do env.php" e
            // vai como NULL, nao string vazia. Valor fora da lista viraria token
            // inexistente na Focus, entao so estes dois passam.
            $amb = isset($fws_['ambiente']) ? trim((string) $fws_['ambiente']) : '';
            $fws_['ambiente'] = in_array($amb, array('producao', 'homologacao'), true) ? $amb : null;

            $obj = $this->carregarEmissor($fws_, $debug);
            $obj->setAll($fws_, true);
            // Tambem valida o gestor de DESTINO: sem isto daria para cadastrar
            // um emissor em nome de um gestor ao qual o usuario nao pertence.
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
            'codEmissor'    => $obj ? $obj->getCodEmissor() : ''
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Emissor nao se exclui: ele fica ligado a notas ja emitidas. Inativar
     * tira das novas emissoes e preserva o historico.
     */
    public function alternarStatusEmissor($fws_, $debug = false) {
        $ok = false;
        $e = null;
        try {
            $obj = new NotaFiscalEmissor();
            $obj->setCodEmissor(isset($fws_['codEmissor']) ? $fws_['codEmissor'] : '');
            if (!$obj->getEmissorById($debug)) {
                throw new Exception("Emissor nao encontrado.");
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

    public function carregarTabelaEmissores($fws_, $debug = false) {
        $obj = new NotaFiscalEmissor();
        $lista = $obj->listarEmissores($this->getGestoresPermitidos($debug), $debug);

        if (empty($lista)) {
            echo "<tr><td colspan='7' style='text-align:center; padding:24px; color:#999;'>"
               . "Nenhum emissor cadastrado. Cadastre a empresa que vai emitir as notas."
               . "</td></tr>";
            return;
        }

        foreach ($lista as $item) {
            $ativo = $item->getStatus() == 'ATIVADA';
            $dot = $ativo ? 'dot-green' : 'dot-blue';
            $textoStatus = $ativo ? 'Ativo' : 'Inativo';
            $im = trim((string) $item->getInscricaoMunicipal());

            echo "<tr id='" . (int) $item->getCodEmissor() . "'>";
            echo "<td class='col-codigo'>"
               . "<a href='#' class='btn-acao editar' title='Editar'><i class='fa fa-pencil'></i></a>"
               . "<a href='#' class='btn-acao alternar' title='" . ($ativo ? 'Inativar' : 'Reativar') . "'>"
               . "<i class='fa fa-power-off'></i></a>"
               . "</td>";
            echo "<td class='col-nome'>" . htmlspecialchars((string) $item->getRazaoSocial()) . "</td>";
            echo "<td class='col-codigo'>" . htmlspecialchars((string) $item->getCnpj()) . "</td>";
            echo "<td>" . htmlspecialchars((string) $item->getNomeGestor()) . "</td>";
            echo "<td class='col-codigo'>" . ($im != '' ? htmlspecialchars($im) : "<span style='color:#aaa;'>sem IM</span>") . "</td>";
            // Ambiente do emissor. Vazio = segue o padrao do env.php. Fica na
            // listagem para dar num relance quem esta em teste — com um gestor
            // em homologacao e o resto em producao, isso e a informacao critica.
            $amb = trim((string) $item->getAmbiente());
            if ($amb === 'producao') {
                echo "<td class='col-badge'><span class='badge-tipo' style='color:#3c763d;'>Produção</span></td>";
            } elseif ($amb === 'homologacao') {
                echo "<td class='col-badge'><span class='badge-tipo' style='color:#8a6d3b;'>Teste</span></td>";
            } else {
                echo "<td class='col-badge'><span style='color:#aaa;'>padrão</span></td>";
            }
            echo "<td class='col-badge'><span class='badge-tipo'><span class='dot " . $dot . "'></span>"
               . $textoStatus . "</span></td>";
            echo "</tr>";
        }
    }

    /**
     * Options de gestor para o form. Reaproveita sis_gestor, que ja e a
     * entidade dona do CNPJ no resto do financeiro.
     */
    public function carregarOptionsGestor($fws_, $debug = false) {
        $obj = new NotaFiscalEmissor();
        // ADM_GERAL escolhe qualquer gestor; os demais, so os seus.
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
