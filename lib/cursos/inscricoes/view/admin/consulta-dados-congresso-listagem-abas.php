<?php if($fws_['perspectiva'] == 'avaliacao'){?>
    <ul class="nav nav-tabs">
    <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php">Distribuir Avaliação </a></li>
    <li>
        <a href='https://docs.google.com/document/d/1ehzOOse6Z5VrfPs7UxQP7b9Pymhrg8-oh_fxg-j3wNU/edit?usp=drive_link' target="_blank">Guia do Distrituidor</a>
        </li>
    </ul>
<?php }else if($fws_['content'] == 'view/admin/consulta-dados-congresso-listagem-trabalhos.php'){?>
    <ul class="nav nav-tabs">
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php">Localizar Inscrito </a></li>
        <li class="active"><a href="#">Localizar <?php echo $objEvConf->getTrabAnexoTipo()!=''? $objEvConf->getTrabAnexoTipo() : 'Trabalhos' ?></a></li>
        <?php if (COD_EVENTO == 1) { ?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos-premiacao.php">Definir Premiações</a></li>
        <?php } ?>
    </ul>
<?php }else{?>
    <ul class="nav nav-tabs">
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php">Localizar Inscrito </a></li>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php"><b>Localizar Trabalhos</b></a></li>
        <?php if (COD_EVENTO == 1) { ?>
        <li class="active"><a href="#"><b>Definir Premiações</b></a></li>
        <?php } ?>
    </ul>
<?php } ?>
