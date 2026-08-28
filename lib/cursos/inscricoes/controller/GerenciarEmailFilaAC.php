<?php

class GerenciarEmailFilaAC
{
    public function __construct()
    {
        FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false);
    }

    public function carregarTabelaEmailFila($fws_, $debug = false)
    {
        try {
            $obj = new EmailFila();

            if ($fws_['filtroStatus'] != '') {
                $obj->setStatus($fws_['filtroStatus']);
            }
            if ($fws_['filtroCodEmailTemplate'] != '') {
                $obj->setCodEmailTemplate($fws_['filtroCodEmailTemplate']);
            }

            $lista = [];
            try {
                $lista = $obj->carregarLista($debug);
            } catch (Exception $e) {
                // Sem registros
            }
            $resumo = ['PENDENTE' => 0, 'ENVIADO' => 0, 'ERRO' => 0, 'CANCELADO' => 0];
            try {
                $resumo = array_merge($resumo, $obj->carregarResumo($debug));
            } catch (Exception $e) {}

            ?>
            <div style="padding:8px 18px;">
                <div class="row">
                    <div class="col-sm-3"><span class="badge-tipo"><span class="dot dot-blue"></span>Pendentes: <?php echo $resumo['PENDENTE'] ?></span></div>
                    <div class="col-sm-3"><span class="badge-tipo"><span class="dot dot-green"></span>Enviados: <?php echo $resumo['ENVIADO'] ?></span></div>
                    <div class="col-sm-3"><span class="badge-tipo"><span class="dot dot-red"></span>Erros: <?php echo $resumo['ERRO'] ?></span></div>
                    <div class="col-sm-3"><span class="badge-tipo"><span class="dot dot-orange"></span>Cancelados: <?php echo $resumo['CANCELADO'] ?></span></div>
                </div>
            </div>
            <table class="tbl-fin" id="tblEmailFila">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th data-col="1" style="cursor:pointer;">Destinat&aacute;rio <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="2" style="cursor:pointer;">Assunto <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="3" style="cursor:pointer; width:90px;">Status <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="4" style="cursor:pointer; width:130px;">Programado <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="5" style="cursor:pointer; width:130px;">Enviado <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th style="width:60px;">A&ccedil;&otilde;es</th>
                    </tr>
                </thead>
                <tbody>
            <?php
            $ii = 0;
            if (!empty($lista)) {
                foreach ($lista as $objL) {
                    $ii++;
                    $dotClass = 'dot-blue';
                    if ($objL->getStatus() == 'ENVIADO') $dotClass = 'dot-green';
                    if ($objL->getStatus() == 'ERRO') $dotClass = 'dot-red';
                    if ($objL->getStatus() == 'CANCELADO') $dotClass = 'dot-orange';
                    ?>
                    <tr data-id="<?php echo $objL->getCodEmailFila() ?>">
                        <td style="color:#999; font-size:12px;"><?php echo $objL->getCodEmailFila() ?></td>
                        <td class="col-nome">
                            <?php echo htmlspecialchars($objL->getNomeDestinatario()) ?>
                            <div style="font-weight:normal; color:#999; font-size:11px;"><?php echo htmlspecialchars($objL->getEmailDestinatario()) ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($objL->getAssunto()) ?></td>
                        <td><span class="badge-tipo"><span class="dot <?php echo $dotClass ?>"></span><?php echo $objL->getStatus() ?></span></td>
                        <td style="font-size:12px;"><?php echo $objL->getDatProgramado() ? date('d/m/Y H:i', strtotime($objL->getDatProgramado())) : '-' ?></td>
                        <td style="font-size:12px;"><?php echo $objL->getDatEnviado() ? date('d/m/Y H:i', strtotime($objL->getDatEnviado())) : '-' ?></td>
                        <td>
                            <span class="btn-acao btn-ver-corpo" title="Ver conte&uacute;do"><i class="fa fa-eye"></i></span>
                            <?php if ($objL->getStatus() == 'PENDENTE') { ?>
                            <span class="btn-acao btn-acao-excluir btn-cancelar-email" title="Cancelar"><i class="fa fa-times"></i></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            if ($ii === 0) {
                ?>
                <tr><td colspan="7" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum e-mail encontrado.</td></tr>
                <?php
            }
            ?>
                </tbody>
                <tfoot><tr><td colspan="7" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot>
            </table>
            <?php
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
        }
    }

    public function verCorpoEmail($fws_, $debug = false)
    {
        try {
            $obj = new EmailFila();
            $obj->setCodEmailFila($fws_['codEmailFila']);

            if (!$obj->getById($debug)) {
                echo json_encode(['statusMessage' => false, 'message' => 'E-mail não encontrado.']);
                exit();
            }

            echo json_encode([
                'statusMessage' => true,
                'assunto' => $obj->getAssunto(),
                'corpo' => $obj->getCorpo(),
                'destinatario' => $obj->getEmailDestinatario(),
                'nome' => $obj->getNomeDestinatario(),
                'status' => $obj->getStatus(),
                'erroMensagem' => $obj->getErroMensagem(),
                'datProgramado' => $obj->getDatProgramado(),
                'datEnviado' => $obj->getDatEnviado()
            ]);
        } catch (Exception $e) {
            echo json_encode(['statusMessage' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function cancelarEmail($fws_, $debug = false)
    {
        $ok = false;
        $msg = '';
        try {
            $obj = new EmailFila();
            $obj->setCodEmailFila($fws_['codEmailFila']);
            $obj->cancelar($debug);
        } catch (OKException $e) {
            $ok = true;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        echo json_encode(['statusMessage' => $ok, 'message' => $msg]);
        exit();
    }

    public function carregarOptionsTemplates($fws_, $debug = false)
    {
        try {
            $obj = new EmailTemplate();
            $obj->carregarListaEmailTemplateByIdEvento($debug);
            $lista = $obj->getListaEmailTemplates();

            echo '<option value="">Todos</option>';
            foreach ($lista as $objL) {
                $sel = ($fws_['filtroCodEmailTemplate'] == $objL->getCodEmailTemplate()) ? 'selected' : '';
                echo '<option value="' . $objL->getCodEmailTemplate() . '" ' . $sel . '>' . htmlspecialchars($objL->getAssunto()) . '</option>';
            }
        } catch (Exception $e) {
            echo '<option value="">Todos</option>';
        }
    }
}
