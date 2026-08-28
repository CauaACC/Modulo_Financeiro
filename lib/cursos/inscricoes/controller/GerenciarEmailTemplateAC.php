<?php

class GerenciarEmailTemplateAC
{

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    /**
     * Quem pode LER template (escolher no select e ver a previa antes de disparar).
     * Enviar comunicado em lote sem poder ver o texto que vai sair nao faz sentido,
     * entao quem tem a area de Acao em Lote enxerga o conteudo -- mas nao edita.
     */
    public static function permissoesLeitura()
    {
        return array(
            enumSistema::cod('SIS_ACOES_EM_LOTE'),
            enumSistema::SIS_GERENCIAR_ADM_GERAL,
        );
    }

    /** Criar, alterar, excluir e importar template continua sendo ADM_GERAL. */
    public static function permissoesEdicao()
    {
        return array(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    public function carregarObjEmailTemplate($fws_, $debug = false)
    {
        // helper usado pelos dois lados: aqui vale a leitura; quem grava e barrado
        // no proprio metodo publico (salvar/excluir/importar)
        FWA_exigirPermissao(self::permissoesLeitura());
        $obj = new EmailTemplate();
        if ($fws_['codEmailTemplate']) {
            $obj->setCodEmailTemplate($fws_['codEmailTemplate']);
            $obj->getEmailTemplateById($debug);
        }
        return $obj;
    }

    public function salvarEmailTemplate($fws_, $debug = false)
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $ok = false;
        $e = null;

        try {
            /**updateEmailTemplate igual a 1 significa que estamos atualizando um template existente */
            if ($fws_['updateEmailTemplate'] == 0) {
                $fws_['codEmailTemplate'] = NULL;
            }
            $obj = $this->carregarObjEmailTemplate($fws_);
            $obj->setAll($fws_, true);
            $obj->salvar();
            $ok = true;
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        echo json_encode([
            'statusMessage' => $ok,
            'message' => $ex ? $ex->getMessage() : '',
            'codEmailTemplate' => $obj ? $obj->getCodEmailTemplate() : '',
        ]);
    }

    public function editarEmailTemplate($fws_, $debug = false)
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $ok = 0;            
        $obj = $this->carregarObjEmailTemplate($fws_, $debug);
        if($obj->getAssunto()){
            $ok = 1;
        }
        echo json_encode([
            'statusMessage' => $ok,
            'message' => $obj->getMessage(),
            'codEmailTemplate' => $obj->getCodEmailTemplate(),
            'assunto' => $obj->getAssunto(),
            'corpo' => $obj->getCorpo(),
            'descricao' => $obj->getDescricao(),
            'tipoDestinatario' => $obj->getTipoDestinatario(),
            'modeloChave' => $obj->getModeloChave(),
            'appLocal' => $obj->getAppLocal(),
            'status' => $obj->getStatus(),
        ]);
        exit();
    }

    public function carregarEmailTemplate($fws_, $debug = false)
    {
        // previa do e-mail na area de Acao em Lote -- leitura
        FWA_exigirPermissao(self::permissoesLeitura());
        $ok = 0;
        $obj = $this->carregarObjEmailTemplate($fws_, $debug);
        if($obj->getAssunto()){
            $ok = 1;
        }
        echo json_encode([
            'statusMessage' => $ok,
            'message' => $obj->getMessage(),
            'codEmailTemplate' => $obj->getCodEmailTemplate(),
            'assunto' => $obj->getAssunto(),
            'corpo' => $obj->getCorpo(),
            'descricao' => $obj->getDescricao(),
            'tipoDestinatario' => $obj->getTipoDestinatario(),
            'modeloChave' => $obj->getModeloChave(),
            'appLocal' => $obj->getAppLocal(),
            'status' => $obj->getStatus(),
        ]);
        exit();
    }

    public function excluirEmailTemplate($fws_, $debug = false)
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        try {
            $obj = $this->carregarObjEmailTemplate($fws_);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo json_encode([
            'statusMessage' => $ok,
            'message' => $e ? $e->getMessage() : '',
            'codEmailTemplate' => $fws_['codEmailTemplate'],
        ]);
        exit();
    }
    
    public function carregarTabelaEmailTemplate($fws_, $debug = false)
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        try {
            $obj = new EmailTemplate();
            $obj->carregarListaEmailTemplateByIdEvento($debug);
            $lista = $obj->getListaEmailTemplates($debug);
            $ii = 0;

            if (empty($lista)) {
                throw new Exception("Não possui registros cadastrados.");
            }
            ?>
            <table class="tbl-fin" id="tblTemplates">
                <thead>
                    <tr>
                        <th style="width:90px;">A&ccedil;&otilde;es</th>
                        <th>Assunto</th>
                        <th>Descri&ccedil;&atilde;o / Objetivos</th>
                        <th>Destinat&aacute;rio</th>
                    </tr>
                </thead>
                <tbody>
            <?php
            foreach ($lista as $objL) {
                if ($objL->getStatus() !== 'ATIVADA') continue;
                $ii++;
                $corpoTexto = strip_tags($objL->getCorpo());
                $corpoTruncado = mb_strlen($corpoTexto) > 120 ? htmlspecialchars(mb_substr($corpoTexto, 0, 120)) . '...' : htmlspecialchars($corpoTexto);
                $corpoCompleto = nl2br(htmlspecialchars($objL->getCorpo()));
                $temMais = mb_strlen($corpoTexto) > 120;
                ?>
                <tr data-id="<?php echo $objL->getCodEmailTemplate() ?>">
                    <td>
                        <span class="btn-acao btn-editar" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="btn-acao btn-acao-excluir btn-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>
                    <td class="col-nome">
                        <?php echo htmlspecialchars($objL->getAssunto()) ?>
                        <div style="font-weight:normal; color:#999; font-size:11px; margin-top:2px;">
                            <span class="corpo-preview"><?php echo $corpoTruncado ?></span>
                            <?php if($temMais){ ?>
                            <span class="corpo-completo" style="display:none;"><?php echo $corpoCompleto ?></span>
                            <a href="#" class="toggle-corpo" style="font-size:11px;">ver mais</a>
                            <?php } ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($objL->getDescricao()) ?></td>
                    <td><?php echo htmlspecialchars($objL->getTipoDestinatario()) ?></td>
                </tr>
                <?php
            }
            if ($ii === 0) throw new Exception("N&atilde;o possui templates ativos.");
            ?>
                </tbody>
                <tfoot><tr><td colspan="4" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> template<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot>
            </table>
            <?php
        } catch (Exception $e) {
            ?>
            <div class="text-center text-muted py-4">
                <?php echo $e->getMessage() ?: 'Lista vazia.'; ?>
            </div>
            <?php
        }
    }

    public function carregarAssuntosImportacao($fws_, $debug = false)
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        try {
            $obj = new EmailTemplate();
            $templates = $obj->carregarAssuntosOutrosEventos($debug);

            if (empty($templates)) {
                echo json_encode([
                    'statusMessage' => false,
                    'message' => 'Não há templates disponíveis para importação.',
                    'templates' => []
                ]);
            } else {
                echo json_encode([
                    'statusMessage' => true,
                    'templates' => $templates
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'statusMessage' => false,
                'message' => $e->getMessage(),
                'templates' => []
            ]);
        }
        exit();
    }

    public function importarTemplatesPorIds($fws_, $debug = false)
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $ok = false;
        $msg = '';
        try {
            $ids = isset($fws_['ids']) ? $fws_['ids'] : array();
            if (is_string($ids)) {
                $ids = array($ids);
            }
            $obj = new EmailTemplate();
            $obj->importarEmailTemplatesPorIds($ids, $debug);
            $ok = true;
        } catch (OKException $e) {
            $ok = true;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        echo json_encode([
            'statusMessage' => $ok,
            'message' => $msg
        ]);
        exit();
    }

    public function carregarOptionsEmailTemplate($fws_, $debug = false)
    {
        try {
            $obj = new EmailTemplate();
            $obj->carregarListaEmailTemplateByIdEvento($debug);
            $lista = $obj->getListaEmailTemplates($debug);
            $ii = 0;

            if (empty($lista)) {
                throw new Exception("");
            }
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['comunicado'], $objL->getCodEmailTemplate()) ?>
                    value="<?php echo  $objL->getCodEmailTemplate() ?>"><?php echo $objL->getAssunto() ?></option>
                <?php
            }
        } catch (Exception $e) {  }
    }
}