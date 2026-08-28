<?php

class GerenciarMsgAvisoAC
{

    function __construct()
    {
        if (!$_SESSION['_cod_usuario'] || !FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_EVENTO, 5, false, false)) {
            throw new Exception("Acesso restrito.");
        }
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    public function carregarObjMsgAvisos($fws_, $debug = false)
    {
        $obj = new MsgAviso();
        if ($fws_['codMsgAviso']) {
            $obj->setCodMsgAviso($fws_['codMsgAviso']);
            $obj->getMsgAvisoById($debug);
        }
        return $obj;
    }

    public function salvarMsgAvisos($fws_, $debug = false)
    {
        $ok = false;
        $e = null;

        try {
            if ($fws_['updateMsgAvisos'] == 0) {
                $fws_['codMsgAviso'] = NULL;
            }
            $obj = $this->carregarObjMsgAvisos($fws_);
            $obj->setAll($fws_, true);
            $obj->salvar();
            $ok = true;
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        echo '{
    "statusMessage":"' . $ok . '",
    "message":      "' . ($ex ? $ex->getMessage() : '') . '",
    "codMsgAviso":"' . ($obj ? $obj->getCodMsgAviso() : '') . '"
    }';
    }

    public function insertMsgAviso($fws_, $debug = false)
    {
        $ok = false;
        $e = null;

        try {
            if ($fws_['updateMsgAvisos'] == 0) {
                $fws_['codMsgAviso'] = NULL;
            }
            $obj = new MsgAviso();
            if ($fws_['codMsgAviso']) {
                $obj->setCodMsgAviso($fws_['codMsgAviso']);
                $obj->getMsgAvisoById($debug);
            }
            $obj->setAll($fws_, true);
            $obj->insertLigacaoInscritoAviso();
            $ok = true;
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . ($ex ? $ex->getMessage() : '') . '",
        "codMsgAviso":"' . ($obj ? $obj->getCodMsgAviso() : '') . '"
        }';
    }

    public function editarMsgAvisos($fws_, $debug = false)
    {
        $ok = 0;            
        $obj = $this->carregarObjMsgAvisos($fws_, $debug);
        if($obj->getAssunto()){
            $ok = 1;
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => $obj->getMessage(),
            'codMsgAviso' => $obj->getCodMsgAviso(),
            'assunto' => $obj->getAssunto(),
            'corpo' => $obj->getCorpo(),
            'descricao' => $obj->getDescricao(),
            'tipoDestinatario' => $obj->getTipoDestinatario(),
            'modeloChave' => $obj->getModeloChave(),
            'appLocal' => $obj->getAppLocal(),
            'status' => $obj->getStatus(),
            'dataPublicacaoIni' => $obj->getDataPublicacaoIni(),
            'dataPublicacaoFim' => $obj->getDataPublicacaoFim(),
            'codPerfil' => $obj->getCodPerfil(),
            'exibirPara' => $obj->getExibirPara() ?: 'TODOS',
            'filtroStatus' => $obj->getFiltroStatus() ?: '',
            'filtroStatusProcesso' => $obj->getFiltroStatusProcesso() ?: '',
            'fixado' => $obj->getFixado() ?: '0'
        ));
        exit();
    }

    public function carregarMsgAvisos($fws_, $debug = false)
    {
        $ok = 0;            
        $obj = $this->carregarObjMsgAvisos($fws_, $debug);
        if($obj->getAssunto()){
            $ok = 1;
        }
        echo '{
                    "statusMessage":"' . $ok . '",
                    "message":"' . $obj->getMessage() . '",
                    "codMsgAviso":"' . $obj->getCodMsgAviso() . '",
                    "assunto":"' . $obj->getAssunto() . '",
                    "corpo":"' . tratarJson($obj->getCorpo()) . '",
                    "descricao":"' . tratarJson($obj->getDescricao()) . '",
                    "tipoDestinatario":"' . $obj->getTipoDestinatario() . '",
                    "modeloChave":"' . $obj->getModeloChave() . '",
                    "appLocal":"' . $obj->getAppLocal() . '",
                    "status":"' . $obj->getStatus() . '",
                    "dataPublicacaoIni":"' . $obj->getDataPublicacaoIni() . '",
                    "dataPublicacaoFim":"' . $obj->getDataPublicacaoFim() . '",
                    "codPerfil":"' . $obj->getCodPerfil() . '"
                 }';
        exit();
    }

    public function excluirMsgAvisos($fws_, $debug = false)
    {
        try {
            $obj = $this->carregarObjMsgAvisos($fws_);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
            "statusMessage":"' . $ok . '",
            "message":"' . $e->getMessage() . '",
            "codMsgAviso":"' . $fws_['codMsgAviso'] . '",
            }';
        exit();
    }
    
    public function carregarTabelaMsgAvisos($fws_, $debug = false)
    {
        $obj = new MsgAviso();
        $lista = [];
        try {
            $obj->carregarListaMsgAvisosByIdEvento($debug);
            $lista = $obj->getListaMsgAvisos($debug);
        } catch (Exception $e) {
            // Sem registros — lista vazia
        }
        $ii = 0;
        ?>
        <table class="tbl-fin" id="tblAvisos">
            <thead>
                <tr>
                    <th style="width:90px;">A&ccedil;&otilde;es</th>
                    <th data-col="1" style="cursor:pointer;">Assunto <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="2" style="cursor:pointer;">Perfil <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="3" style="cursor:pointer;">Per&iacute;odo <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th data-col="4" style="cursor:pointer;">Status <span class="sort-arrow">&uarr;&darr;</span></th>
                </tr>
            </thead>
            <tbody>
        <?php
        if (!empty($lista)) {
            foreach ($lista as $objL) {
                if ($objL->getStatus() === 'EXCLUIDA') continue;
                $ii++;
                $corpoTexto = strip_tags($objL->getCorpo());
                $corpoTruncado = mb_strlen($corpoTexto) > 120 ? htmlspecialchars(mb_substr($corpoTexto, 0, 120)) . '...' : htmlspecialchars($corpoTexto);
                $corpoCompleto = nl2br(htmlspecialchars($objL->getCorpo()));
                $temMais = mb_strlen($corpoTexto) > 120;
                ?>
                <tr data-id="<?php echo $objL->getCodMsgAviso() ?>" id="<?php echo $objL->getCodMsgAviso() ?>">
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
                    <td><?php echo htmlspecialchars($objL->getTituloPerfil()) ?></td>
                    <td style="white-space:nowrap;">
                        <?php echo formatDateView($objL->getDataPublicacaoIni()) ?>
                        <?php if($objL->getDataPublicacaoFim()){ ?>
                            <br>at&eacute; <?php echo formatDateView($objL->getDataPublicacaoFim()) ?>
                        <?php } ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <?php $st = trim($objL->getStatus()); ?>
                        <?php if ($st === 'ATIVADA'): ?>
                            <span style="display:inline-block;padding:3px 9px;border-radius:10px;background:#d4edda;color:#155724;font-size:11px;font-weight:600;">Ativada</span>
                        <?php else: ?>
                            <span style="display:inline-block;padding:3px 9px;border-radius:10px;background:#e0e0e0;color:#555;font-size:11px;font-weight:600;">Desativada</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
            }
        }
        if ($ii === 0) {
            ?>
            <tr><td colspan="5" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum aviso encontrado.</td></tr>
            <?php
        }
        ?>
            </tbody>
            <tfoot><tr><td colspan="4" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> aviso<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot>
        </table>
        <?php
    }
}