<?php
// Menu de administração simplificado - apenas link de logout
?>
<div class="row">
    <div class="col-sm-12" style="margin-bottom: 5px;">
        <small>
            Logado como: <strong><?php echo htmlspecialchars($_SESSION['_nome_usuario'] ?? ''); ?></strong>
            &nbsp;|&nbsp;
            <a href="<?php echo WB_URL_COMMAND ?>&c=GerenciarLogin&m=efetuarLogout">Sair</a>
        </small>
    </div>
</div>
