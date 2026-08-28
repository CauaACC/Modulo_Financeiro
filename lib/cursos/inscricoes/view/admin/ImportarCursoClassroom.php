<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$_eadExigir = 'COORDENADOR';   // importar cria curso e itens = montagem
require_once(dirname(__FILE__)."/../../view/admin/curso-ead-seguranca.php");
?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<style>
    .imp-wrap { max-width:640px; margin:20px auto; font-size:14px; color:#333; }
    .imp-card { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,0.05); }
    .imp-card h3 { margin:0 0 6px; font-size:18px; }
    .imp-card p.sub { margin:0 0 18px; color:#777; font-size:13px; }
    .imp-field { margin-bottom:16px; }
    .imp-field label { display:block; font-weight:600; margin-bottom:6px; font-size:13px; }
    .imp-field input[type=file] { display:block; width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; background:#fafafa; }
    .imp-check { display:flex; align-items:center; gap:8px; font-size:13px; color:#555; }
    .imp-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 20px; background:#4A90E2; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; }
    .imp-btn:hover { background:#3d7fca; }
    .imp-msg { margin-top:18px; font-size:13px; }
    .imp-msg .ok  { color:#1e7e34; background:#eafaf0; border:1px solid #bce8cd; padding:12px 14px; border-radius:6px; }
    .imp-msg .err { color:#b02a37; background:#fdeced; border:1px solid #f5c2c7; padding:12px 14px; border-radius:6px; }
    .imp-help { margin-top:14px; font-size:12px; color:#999; line-height:1.5; }
</style>
<script type="text/javascript">
    function importarCursoClassroom() {
        var f = $('#fileManifesto')[0];
        if (!f || !f.files || f.files.length === 0) {
            $('#impMsg').html('<div class="err">Selecione o arquivo do manifesto (.json).</div>');
            return;
        }
        $('#formImportar').ajaxSubmit({
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $('#btnImportar').prop('disabled', true);
                $('#impMsg').html('<div style="color:#888;">Importando, aguarde...</div>');
            },
            success: function(data) {
                if (data.statusMessage) {
                    var r = data.resumo || {};
                    $('#impMsg').html('<div class="ok">' + data.message
                        + '<br><small>Curso cód. ' + (r.cod_curso || '-') + ' &middot; '
                        + (r.capitulos || 0) + ' capítulos &middot; ' + (r.itens || 0) + ' itens</small></div>');
                } else {
                    $('#impMsg').html('<div class="err">' + (data.message || 'Falha na importação.') + '</div>');
                }
            },
            error: function() {
                $('#impMsg').html('<div class="err">Erro de comunicação com o servidor.</div>');
            },
            complete: function() { $('#btnImportar').prop('disabled', false); }
        });
    }
</script>

<div class="imp-wrap">
    <div class="imp-card">
        <h3>Importar curso do Google Classroom</h3>
        <p class="sub">Envie o manifesto (.json) gerado a partir da sala do Classroom. O sistema cria o curso, os capítulos e todos os itens (vídeos, PDFs, links e formulários) automaticamente.</p>

        <form id="formImportar" method="post" enctype="multipart/form-data">
            <input type="hidden" name="c" value="ImportarCursoClassroom">
            <input type="hidden" name="m" value="importar">
            <input type="hidden" name="_submit_check" value="1">

            <div class="imp-field">
                <label for="fileManifesto">Arquivo do manifesto (.json)</label>
                <input type="file" id="fileManifesto" name="manifesto" accept="application/json,.json">
            </div>

            <div class="imp-field imp-check">
                <input type="checkbox" id="forcar" name="forcar" value="1">
                <label for="forcar" style="margin:0;font-weight:normal;">Forçar importação mesmo que já exista curso com o mesmo título</label>
            </div>

            <button type="button" id="btnImportar" class="imp-btn" onclick="importarCursoClassroom()">
                &#8681; Importar curso
            </button>
        </form>

        <div id="impMsg" class="imp-msg"></div>

        <div class="imp-help">
            O manifesto é gerado localmente pelo script <code>classroomManifesto.py</code>.
            A importação roda em transação: se algo falhar, nada é gravado.
        </div>
    </div>
</div>
