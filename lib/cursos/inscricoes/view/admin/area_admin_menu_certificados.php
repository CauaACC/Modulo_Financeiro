<?php
// Submenu do modulo CERTIFICADOS (mesmo padrao do financeiro/outros).
// Cada tela do modulo inclui este arquivo no rodape. O item ativo e destacado
// pela pagina atual ($pg). Reune num lugar so o que antes estava espalhado:
//   - Configuracao: textos/datas/opcoes do certificado (porta de entrada)
//   - Verso/Programa: editor do verso (pode ser geral OU por atividade)
//
// O item "Painel" saiu do menu por decisao de uso: a visao geral ainda estava
// competindo com a Configuracao pela primeira tela. PainelCertificados.php
// continua no ar e acessivel por URL — so nao e mais a porta de entrada.
?>
<div class="row">
<div class="col-sm-12 submenu-area">
       <div style="border-left:3px solid #2e7d32; background:#f4f9f5; border-radius:6px; padding:6px 10px; margin-top:4px;">
              <span style="font-size:12px; color:#2e7d32; font-weight:600; margin-right:8px;"><i class="fa fa-certificate"></i> Certificados:</span>
<?php // Ordem segue o fluxo de montar: visao -> artes -> programa -> textos -> casos especiais.
      // So os ROTULOS e a ordem mudaram; os content=... e as deteccoes (por nome de pagina) sao os mesmos. ?>
              <a class="submenu-btn <?= (strpos($pg, 'ConfiguracaoCertificado') !== false || strpos($pg, 'PainelCertificados') !== false) ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/ConfiguracaoCertificado.php">Regras</a>
              <?php // Textos = texto padrao + texto por atividade, em abas. A rota antiga
                    // CertificadoPorAtividade.php virou stub e cai aqui tambem. ?>
              <a class="submenu-btn <?= (strpos($pg, 'TextosCertificado') !== false || strpos($pg, 'CertificadoPorAtividade') !== false) ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/TextosCertificado.php">Textos</a>
              <a class="submenu-btn <?= strpos($pg, 'PecasCertificado') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/PecasCertificado.php">Assinaturas</a>
              <a class="submenu-btn <?= strpos($pg, 'CertificadoModelo') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/CertificadoModelo.php">Modelo da frente</a>
              <a class="submenu-btn <?= (strpos($pg, 'ProgramasCertificado') !== false || strpos($pg, 'GerenciarVersoPrograma') !== false) ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/ProgramasCertificado.php">Programas</a>
              <a class="submenu-btn <?= strpos($pg, 'GerenciarInscritoPerfil') !== false ? 'submenu-btn-ativo' : '' ?>" href="index.php?content=view/admin/GerenciarInscritoPerfil.php">Libera&ccedil;&atilde;o por pessoa</a>
       </div>
</div>
</div>
