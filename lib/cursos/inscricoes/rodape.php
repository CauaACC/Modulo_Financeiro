<?php
// Rodape so exibe contato publico durante periodo de inscricao + 30 dias
$_rodapeExibirContato = false;
if (isset($objEvConf)) {
    $_dataFimInscricao = $objEvConf->getDataInscricaoFim();
    if (!empty($_dataFimInscricao)) {
        $_dataLimiteRodape = date('Y-m-d', strtotime($_dataFimInscricao . ' +30 days'));
        $_rodapeExibirContato = (date('Y-m-d') <= $_dataLimiteRodape);
    } else {
        // Sem data fim definida — exibe se tem data inicio (inscricao ativa/aberta)
        $_dataIniInscricao = $objEvConf->getDataInscricaoIni();
        if (!empty($_dataIniInscricao) && date('Y-m-d') >= date('Y-m-d', strtotime($_dataIniInscricao))) {
            $_rodapeExibirContato = true;
        }
    }
}
?>
<div class="footer-box-wrap">
    <div class="row">
        <div class="col-md-3 footer-box company-details">
            <?php if (LOGO_RODAPE != '') { ?>
            <div class="icon-top">
                <img src="resources/image/<?php echo '/logo-rodape-'.COD_EVENTO.'.png'; ?>" style="width:50px" alt="">
            </div>
            <?php } ?>
            <div class="zerif-footer-address"><?php echo DESC_EVENTO_RODAPE ?></div>
        </div>
        <div class="col-md-3 footer-box company-details">
            <div class="icon-top">
                <img src="resources/image/envelope4-green.png" alt="">
            </div>
            <div class="zerif-footer-email">
                <a href="mailto:eventos@opctec.com.br">eventos@opctec.com.br</a>
            </div>
        </div>
        <div class="col-md-3 footer-box company-details">
            <div class="icon-top" style="font-size:15px;">
                <img src="resources/image/telephone65-blue.png" alt="">
            </div>
            <div class="zerif-footer-phone">
                <?php if ($_rodapeExibirContato && defined('FONE_CONTATO') && FONE_CONTATO != '') { ?>
                    <a href="tel:<?php echo FONE_CONTATO ?>"><?php echo FONE_CONTATO ?></a><br>
                <?php } ?>
                <?php
                $linkContatoGeral = 'https://api.whatsapp.com/send?phone=5543996967790';
                $foneContatoGeral = '55 43 996967790';
                ?>
                <a href="<?php echo $linkContatoGeral ?>" target="_blank" title="Atendimento somente via chat">
                    <i class="fa fa-whatsapp"></i> WhatsApp <?php echo $foneContatoGeral ?>
                </a>
            </div>
        </div>
        <div class="col-md-3 footer-box copyright">
            <ul class="social">
                <?php if (URL_FACE != '') { ?>
                <li><a target="_blank" title="Facebook" href="<?php echo URL_FACE ?>"><i class="fa fa-facebook"></i></a></li>
                <?php } ?>
                <?php if (URL_YOUTUBE != '') { ?>
                <li><a target="_blank" title="Youtube" href="<?php echo URL_YOUTUBE ?>"><i class="fa fa-youtube"></i></a></li>
                <?php } ?>
                <?php if (URL_INSTAGRAM != '') { ?>
                <li><a target="_blank" title="Instagram" href="<?php echo URL_INSTAGRAM ?>"><i class="fa fa-instagram"></i></a></li>
                <?php } ?>
                <?php if (URL_TWITTER != '') { ?>
                <li><a target="_blank" title="Twitter" href="<?php echo URL_TWITTER ?>"><i class="fa fa-twitter"></i></a></li>
                <?php } ?>
                <?php if (URL_LINKEDIN != '') { ?>
                <li><a target="_blank" title="Linkedin" href="<?php echo URL_LINKEDIN ?>"><i class="fa fa-linkedin"></i></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="rodape-creditos">
        <a href="https://portal.opctec.net/" target="_blank">Power By OPCTEC</a>
        <span class="rodape-versao">vers&atilde;o: lib-hcur-001-<?php echo COD_EVENTO ?></span>
    </div>
</div>
