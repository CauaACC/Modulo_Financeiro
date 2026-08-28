<?php

class GerenciarBloqueioAvaliacaoAC
{

    function __construct()
    {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AREAS_TEMATICAS]);
        $this->codEvento = COD_EVENTO;
    }

    public function alternarStatus($fws_, $debug = false)
    {
        $ok = false;
        $ex = null;

        try {
            $obj = new BloqueioAvaliacao();
            $novoStatus = ($fws_['novoStatus'] === 'BLOQUEADO') ? 'BLOQUEADO' : 'LIBERADO';
            $obj->alternarStatus(
                intval($fws_['mes']),
                intval($fws_['ano']),
                $novoStatus,
                isset($fws_['observacao']) ? $fws_['observacao'] : '',
                $debug
            );
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
            $ok = false;
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '0',
            'message' => $ex ? $ex->getMessage() : ''
        ));
        exit();
    }

    public function carregarPainel($fws_, $debug = false)
    {
        try {
            $obj = new BloqueioAvaliacao();
            $registros = $obj->carregarListaByEvento($debug);

            $anoAtual = intval(date('Y'));
            $mesAtual = intval(date('m'));
            $meses = array(
                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
            );

            $anos = array($anoAtual - 1, $anoAtual, $anoAtual + 1);
            ?>
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th style="width:120px;">Ano</th>
                            <?php foreach ($meses as $num => $nome): ?>
                                <th><?php echo substr($nome, 0, 3) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($anos as $ano): ?>
                        <tr>
                            <td><strong><?php echo $ano ?></strong></td>
                            <?php for ($mes = 1; $mes <= 12; $mes++):
                                $chave = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);
                                $bloqueado = isset($registros[$chave]) && $registros[$chave]['status'] === 'BLOQUEADO';
                                $passado = ($ano < $anoAtual) || ($ano == $anoAtual && $mes < $mesAtual);
                                $atual = ($ano == $anoAtual && $mes == $mesAtual);
                            ?>
                                <td style="vertical-align:middle;<?php echo $atual ? 'background-color:#fff3cd;' : '' ?>">
                                    <button type="button"
                                            class="btn btn-sm <?php echo $bloqueado ? 'btn-danger' : 'btn-success' ?> btn-toggle-bloqueio"
                                            data-mes="<?php echo $mes ?>"
                                            data-ano="<?php echo $ano ?>"
                                            data-status="<?php echo $bloqueado ? 'BLOQUEADO' : 'LIBERADO' ?>"
                                            title="<?php echo $bloqueado ? 'Bloqueado - Clique para liberar' : 'Liberado - Clique para bloquear' ?>">
                                        <i class="fa <?php echo $bloqueado ? 'fa-lock' : 'fa-unlock' ?>"></i>
                                    </button>
                                </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="row" style="margin-top:10px;">
                <div class="col-sm-12">
                    <span class="badge" style="background-color:#5cb85c;color:#fff;"><i class="fa fa-unlock"></i> Liberado</span>
                    &mdash; Avaliadores podem avaliar registros deste m&ecirc;s
                    &nbsp;&nbsp;
                    <span class="badge" style="background-color:#d9534f;color:#fff;"><i class="fa fa-lock"></i> Bloqueado</span>
                    &mdash; Avalia&ccedil;&atilde;o de registros deste m&ecirc;s est&aacute; bloqueada
                </div>
            </div>
            <?php
        } catch (Exception $e) {
            ?>
            <div class="text-center text-muted py-4">
                <?php echo $e->getMessage() ?: 'Erro ao carregar painel.'; ?>
            </div>
            <?php
        }
    }
}
