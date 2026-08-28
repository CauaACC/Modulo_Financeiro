<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
/*Permitir a edição de atividade durante o logar como*/
@$SIS_RECEPCAO_PARTICIPANTES = 1;
@$cod_msg = false;
@$redirect = false;
if (FWA_Autenticacao($_SESSION['_cod_usuario'], @$SIS_RECEPCAO_PARTICIPANTES, 5, $cod_msg, $redirect)) {
    $OP_PERMITIR_ALTERAR_REQUIRED = '';
} else {
    $OP_PERMITIR_ALTERAR_REQUIRED = 'required';
}

$objInscrito = new Inscrito();
$objInscrito->setEMail($fws_['e_mail']);
$objInscrito->setCpf(formatCpfInsert($fws_['cpf']));
$objInscrito->setCodPerfil($fws_['cod_perfil']);
$objInscrito->setCodOficina($fws_['cod_oficina']);

//$urlVoltar = "index.php?content=view/inscricao/inscricao_01.php";
$urlVoltar = "index.php?content=view/inscricao/inscricao_congresso_seleciona_oficina.php";
if ($_SESSION['_cod_inscrito'] != '') {
    $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
    $objInscrito->getInscritoById($debug);
    $objInscrito->setReEMail($objInscrito->getEMail());
    $readOnly = 'readonly';
    $disabled  = 'disabled';
    $urlVoltar = "index.php?content=view/inscricao/inscricao_congresso_ok.php";
}else{
    // echo __LINE__;
    $codRetorno='efetuarLogin&e_mail=' . $fws_['e_mail'];
    $urlVoltar = WB_URL_VIEW . '?content=view/inscricao/inscricao_01.php&codRetorno=' . $codRetorno . '&cpf='.$fws_['cpf'];
    // $urlVoltar = "index.php?content=view/inscricao/inscricao_congresso_seleciona_perfil.php";
    $MSG = 'Cpf informado: '.$fws_['cpf'] . ' já cadastrado nesse mesmo perfil/cargo, é necessário efetuar login e senha entrar no perfil/cargo selecionado.';
    FWA_Mostrar_Mensagem($MSG);
    $_SESSION['MSG'] = $MSG;
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $urlVoltar . "\">";
    exit();
}
?>
<?php //if ($objEvConf->getFormDadosBancarios() == true) { ?>

    <?php
    $Conexao_db = FWA_Conexao("OpcTec");
    $debug = false;    
    require_once 'inscricao_congresso_tit_area_inscrito.php';
    ?>
    <div class="panel-group">
        <?php require_once 'inscricao_congresso_cabecalho.php'; ?>
    <form class="formDB" method="post" onsubmit="return criticar();" action="<?php echo WB_URL_COMMAND ?>">
    <input type="hidden" name="c" value="GerenciarInscricao">
    <input type="hidden" name="m" value="atualizarDados">
<!--<input type="hidden" name="content" value="view/inscricao/inscricao_congresso_confirmar.php">-->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Processo de Alteração - Informe Dados Bancários</h4>
            </div>
            <div class="panel-body">
                <?php
                if ($fws_['msg']) {
                    FWA_Mostrar_Mensagem($fws_['msg']);
                }
                ?>
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="rg">Dados Bancários:</label>
                    </div>
                    <?php if ($objEvConf->getFormDadosBancariosSomentePix() == true) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;" >
                            <label for="pix">Pix:</label>
                            <input type="text" id="db_pix" name="db_pix" value="<?php echo $objInscrito->getDbPix() ?>" class="form-control" placeholder="Pix" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>

                    <?php } ?>    
                    <?php if ($objEvConf->getFormDadosBancariosSomentePix() == 0) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="banco">Banco:</label>
                            <!--                <input type="text" id="db_banco" name="db_banco" value="<?php echo $objInscrito->getDbBanco() ?>"
                    class="form-control" placeholder="Banco" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                        SIZE='60' maxlength='255' onBlur='trim(this);'>-->
                            <select class='form-control' name='db_banco' id='db_banco' placeholder="Selecione um banco" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus required>
                                <?php
                                $fws_['db_banco'] = @$objInscrito->getDbBanco();
                                CarregarOptionsAC::bancos($fws_, $debug);
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="agencia">Agência - DV:</label>
                            <input type="text" id="db_agencia" name="db_agencia" value="<?php echo $objInscrito->getDbAgencia() ?>" class="form-control" placeholder="Agencia - dv" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="conta_corrente">Conta Corrente - DV:</label>
                            <input type="text" id="db_conta_corrente" name="db_conta_corrente" value="<?php echo $objInscrito->getDbContaCorrente() ?>" class="form-control" placeholder="Conta Corrente" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;" >
                            <label for="pix">Pix:</label>
                            <input type="text" id="db_pix" name="db_pix" value="<?php echo $objInscrito->getDbPix() ?>" class="form-control" placeholder="Pix" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <button class='btn btn-xs btn-primary' type="submit">Salvar Alterações</button>
                            <!-- <button class='btn btn-xs btn-primary' type="reset">Limpar</button> -->
                            <a href="<?php echo $urlVoltar ?>" class="btn btn-xs btn-primary" role="button">Voltar</a>
                        </div>
                        <br><br><br><br><br><br>&nbsp;
                    </div>
                </div> 
            </div> 
        </div> 
    </div> 
    </form>           
<?php //} ?>