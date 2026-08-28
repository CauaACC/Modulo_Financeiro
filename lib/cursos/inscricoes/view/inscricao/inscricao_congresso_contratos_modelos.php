<?php
/**
 * Painel de contratos/documentos para download pelo inscrito
 * Incluído em inscricao_congresso_ok.php quando habilitar_modelos_contrato=1
 *
 * Variáveis esperadas do escopo pai: $objInscrito, $objEvConf
 */

$objModeloContrato = new ModeloContrato();
$listaModelos = array();
try {
    $objModeloContrato->carregarListaModeloContratoByEvento();
    $listaModelos = $objModeloContrato->getListaModeloContrato();
} catch (Exception $e) {
}

if (!$listaModelos || count($listaModelos) == 0) {
    
    echo '<p class="text-muted">Nenhum documento dispon&iacute;vel no momento.</p>';
} else {
    
    foreach ($listaModelos as $modelo) {
        
        $camposObrigatorios = $modelo->getCamposObrigatorios() ? json_decode($modelo->getCamposObrigatorios(), true) : array();
        $faltantes = ContratoPlaceholderHelper::getCamposFaltantes($objInscrito, $camposObrigatorios);

        echo '<div style="padding:10px; margin-bottom:10px; border:1px solid #e8e8e8; border-radius:8px;">';
        echo '<div style="display:flex; justify-content:space-between; align-items:center;">';
        echo '<div>';
        echo '<b>' . htmlspecialchars($modelo->getNome()) . '</b>';
        if ($modelo->getDescricao()) {
            echo '<br><small class="text-muted">' . htmlspecialchars($modelo->getDescricao()) . '</small>';
        }
        echo '</div>';
        echo '<div>';

        if (empty($faltantes)) {
            // Dados completos - pode baixar.
            // O arquivo e montado na hora (preenche o DOCX e converte), o que leva alguns
            // segundos. Por isso o download nao e um link comum: um <a target="_blank">
            // abriria uma aba em branco durante a espera. Aqui o botao busca o arquivo em
            // segundo plano, mostrando o progresso, e so entao dispara o download.
            $urlDownload = WB_URL_COMMAND . '&c=GerenciarModeloContrato&m=gerarContratoInscrito&codModeloContrato=' . $modelo->getCodModeloContrato() . '&_submit_check=1';
            echo '<button type="button" class="btn btn-success btn-baixar-contrato"'
               . ' data-url="' . htmlspecialchars($urlDownload) . '"'
               . ' style="padding:8px 20px; font-size:14px; border-radius:8px; color:#fff; border:none;">';
            echo '<i class="fa fa-download" style="margin-right:6px;"></i> Baixar Contrato';
            echo '</button>';
        } else {
            
            // Dados incompletos
            echo '<button class="btn btn-sm btn-warning" disabled>';
            echo '<i class="fa fa-exclamation-triangle"></i> Dados Incompletos';
            echo '</button>';
        }

        echo '</div>';
        echo '</div>';

        if (!empty($faltantes)) {
            
            $urlEditar = WB_URL_VIEW . '?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php';
            echo '<div class="alert alert-warning" style="margin-top:8px; margin-bottom:0; font-size:12px;">';
            echo '<b>Complete seus dados cadastrais para baixar este documento.</b><br>';
            echo 'Campos faltantes: <b>' . implode(', ', $faltantes) . '</b><br>';
            echo '<a href="' . $urlEditar . '" class="btn btn-sm btn-primary" style="margin-top:6px;"><i class="fa fa-pencil"></i> Editar Dados Pessoais</a>';
            echo '</div>';
        }

        echo '</div>';
    }
    ?>

    <div id="modalGerandoContrato"
         style="display:none; position:fixed; inset:0; z-index:20000;
                background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:10px; padding:26px 34px; text-align:center;
                    min-width:280px; box-shadow:0 6px 26px rgba(0,0,0,.28);">
            <i class="fa fa-spinner fa-spin" style="font-size:30px; color:#2d6a4f;"></i>
            <p style="margin:14px 0 0; font-size:15px;"><b>Gerando seu contrato...</b></p>
            <p style="margin:4px 0 0; font-size:12px; color:#777;">
                Isso leva alguns segundos. N&atilde;o feche esta p&aacute;gina.
            </p>
        </div>
    </div>

    <script>
    (function () {
        var modal = document.getElementById('modalGerandoContrato');

        function nomeDoArquivo(resposta, padrao) {
            var cd = resposta.headers.get('Content-Disposition') || '';
            var m = cd.match(/filename="?([^"]+)"?/);
            return m ? m[1] : padrao;
        }

        document.querySelectorAll('.btn-baixar-contrato').forEach(function (botao) {
            botao.addEventListener('click', function () {
                var original = botao.innerHTML;
                botao.disabled = true;
                botao.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Gerando...';
                modal.style.display = 'flex';

                fetch(botao.dataset.url, { credentials: 'same-origin' })
                    .then(function (r) {
                        if (!r.ok) { throw new Error('HTTP ' + r.status); }
                        var tipo = r.headers.get('Content-Type') || '';
                        // erro do servidor volta como HTML, nao como arquivo
                        if (tipo.indexOf('pdf') === -1 && tipo.indexOf('officedocument') === -1) {
                            return r.text().then(function (t) {
                                throw new Error(t.replace(/<[^>]*>/g, '').trim() || 'Falha ao gerar');
                            });
                        }
                        return r.blob().then(function (blob) {
                            return { blob: blob, nome: nomeDoArquivo(r, 'contrato.pdf') };
                        });
                    })
                    .then(function (arquivo) {
                        var url = URL.createObjectURL(arquivo.blob);
                        var link = document.createElement('a');
                        link.href = url;
                        link.download = arquivo.nome;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        setTimeout(function () { URL.revokeObjectURL(url); }, 4000);
                    })
                    .catch(function (erro) {
                        alert('Não foi possível gerar o contrato.\n\n' + erro.message
                              + '\n\nTente novamente em instantes.');
                    })
                    .then(function () {
                        modal.style.display = 'none';
                        botao.disabled = false;
                        botao.innerHTML = original;
                    });
            });
        });
    })();
    </script>
    <?php
}

