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
if ($fws_['cod_oficina'] == '' and $COD_EVENTO == 12) {
    $URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/index.php?content=view/inscricao/inscricao_congresso_perfil_seleciona.php";
    $MSG = "Para se inscrever é necessário escolher ao menos uma atividade.";
    FWA_Mostrar_Mensagem($MSG);
    $_SESSION['MSG'] = $MSG;
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\">";
    exit();
}
// Recuperar dados da sessão se houver erro no cadastro (evitar perder preenchimento)
if (isset($_SESSION['_form_dados']) && !empty($_SESSION['_form_dados'])) {
    foreach ($_SESSION['_form_dados'] as $k => $v) {
        if ($fws_[$k] == '' && $v != '') {
            $fws_[$k] = $v;
        }
    }
    unset($_SESSION['_form_dados']);
}
$objInscrito = new Inscrito();
$objInscrito->setEMail($fws_['e_mail']);
$objInscrito->setCpf(formatCpfInsert($fws_['cpf']));
$objInscrito->setCodPerfil($fws_['cod_perfil']);
$objInscrito->setCodOficina($fws_['cod_oficina']);
// Se voltou de erro, preencher o objeto com os dados da sessão
if (!empty($fws_['nome_inscrito'])) {
    $objInscrito->setAll($fws_);
}
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

    if(COD_EVENTO==108){
        /** @todo Permitir inscrição duplicada, mas não no mesmo perfil/cargo */
        if ($objInscrito->verificarCpfEstaInscritoMesmoPerfil($debug)) {
            $codRetorno='efetuarLogin&e_mail=' . $fws_['e_mail'];
            $urlVoltar = WB_URL_VIEW . '?content=view/inscricao/inscricao_01.php&codRetorno=' . $codRetorno . '&cpf='.$fws_['cpf'];
            // $urlVoltar = "index.php?content=view/inscricao/inscricao_congresso_seleciona_perfil.php";
            $MSG = 'Cpf informado: '.$fws_['cpf'] . ' já cadastrado nesse mesmo perfil/cargo, é necessário efetuar login e senha entrar no perfil/cargo selecionado.';
            FWA_Mostrar_Mensagem($MSG);
            $_SESSION['MSG'] = $MSG;
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $urlVoltar . "\">";
            exit();
        }
    }
}
?>
<!-- Overlay de processamento (proteção contra duplo submit) -->
<div id="overlayProcessando" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:30px 40px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.3); text-align:center;">
        <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="width:48px; height:48px;"><br><br>
        <h4 style="margin:0 0 5px;">Processando sua inscri&ccedil;&atilde;o...</h4>
        <p style="margin:0; color:#666;">Por favor, aguarde. N&atilde;o feche esta p&aacute;gina.</p>
    </div>
</div>
<div id="overlayProcessandoMsg" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999;"></div>

<!--<FORM NAME="form" METHOD="POST" ACTION="view/inscricao/inscricao_congresso_grava.php"  onsubmit="return criticar();">-->
<?php
/* Comprovante exigido pelo perfil (tab_opcoes_perfis.comprovante_*) - generaliza o hack do evento 122 */
$objPerfilComprovante = null;
$exigeComprovantePerfil = false;
if ($objInscrito->getCodPerfil() != '') {
    $objPerfilComprovante = new Perfil();
    $objPerfilComprovante->setCodEvento(COD_EVENTO);
    $objPerfilComprovante->setCodPerfil($objInscrito->getCodPerfil());
    $objPerfilComprovante->getPerfilById($debug);
    $exigeComprovantePerfil = ($objPerfilComprovante->getComprovanteExigir() == 1);
}
?>
<form name="form" method="POST" onsubmit="return criticar();" action="<?php echo WB_URL_COMMAND ?>" <?php if(COD_EVENTO == 122 || $exigeComprovantePerfil) echo 'enctype="multipart/form-data"'; ?> >
    <input type="hidden" name="c" value="GerenciarInscricao">
    <?php if ($readOnly == 'readonly') { ?>
        <input type="hidden" name="m" value="salvarInscricaoEditar">
    <?php } else { ?>
        <input type="hidden" name="m" value="salvarInscricao">
    <?php } ?>
    <INPUT TYPE='hidden' VALUE='Inserir' NAME='acao'>
    <INPUT TYPE='hidden' VALUE='<?php echo $fws_['tipo_inscricao']; ?>' NAME='tipo_inscricao'>

    <!--<input type="hidden" name="content" value="view/inscricao/inscricao_congresso_confirmar.php">-->
    <?php
    require_once 'inscricao_congresso_tit_area_inscrito.php';
    ?>
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Processo de Cadastro - Informe Dados Pessoais</h4>
            </div>
            <div class="panel-body">
                <?php
                if ($fws_['msg']) {
                    FWA_Mostrar_Mensagem($fws_['msg']);
                }
                ?>
                <?php if ($objInscrito->getPossuiBoletoGerado()) { ?>
                    <div class="form-group col-sm-12" style="padding-left: 0px;"></div>
                <?php } else { ?>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <?php
                        //if ($_SESSION['REMOTE_ADDR']=='189.90.70.56'){
                        $objInscrito->carregarOjbPerfil($debug);
                        if ($objInscrito->getCodPerfil() == '') {
                            //$url = WB_URL_VIEW . '?content=view/inscricao/inscricao_congresso_perfil_seleciona.php';
                            $msg = 'É necessário selecinar um perfil para inscrição';
                            $_SESSION['MSG'] = $msg;
                            $url = WB_URL_VIEW . '?content=view/inscricao/inscricao_01.php&codRetorno=efetuarLogin&e_mail=' . $fws_['e_mail'];
                            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
                            exit();
                        }
                        if ($fws_['tipo_inscricao'] == '') {
                            $fws_['tipo_inscricao'] = $objInscrito->getTipoInscricao();
                        }

                        if (COD_EVENTO >= 10 and $objInscrito->getObjPerfil()->getAbrevTipo() != '') {
                            echo "<h4 class='corTitulo'>" . $objInscrito->getObjPerfil()->getAbrevTipo() . ": " . $objInscrito->getObjPerfil()->getTitulo() . "</h4>";
                        } else if (COD_EVENTO != 9) {
                            $descTipoInsc = CarregarOptionsAC::descTipoInscricao($fws_, $debug);
                            if ($descTipoInsc != 'Outros') {
                                echo "<h4>Opção Selecionada: " . $descTipoInsc . "</h4>";
                            }
                            echo "<h4 class='corTitulo'>Perfil: " . $objInscrito->getObjPerfil()->getTitulo() . "</h4>";
                        } else {
                            //                            echo "<h4 class='corTitulo'>".$objInscrito->getObjPerfil()->getTitulo()."</h4>";
                        }

                        echo $objInscrito->getObjPerfil()->getInvestimento();
                        echo "<input type='hidden' name='cod_perfil' value='" . $objInscrito->getObjPerfil()->getCodPerfil() . "'>";
                        echo "<hr>";
                        //}
                        ?>
                    </div>
                    <?php if ($fws_['op_selecionar_oficina']) { ?>
                        <?php if ($objInscrito->getObjPerfil()->getVerificarSelecionarAtividade()) { ?>
                            <div class="form-group col-sm-12" style="padding-left: 0px;">
                                <?php
                                //                            $fws_['cod_oficina'][1] = addslashesopctec($_POST['cod_oficina'][1]).'<br>';
                                // if (count($fws_['cod_oficina']) > 0) {
                                if (is_array($fws_['cod_oficina']) && count($fws_['cod_oficina']) > 0) {
                                    
                                    $valorTotalOficinas = 0;
                                    foreach ($fws_['cod_oficina'] as $value) {
                                        try {
                                            $objInscrito->setCodOficina($value);
                                            $objInscrito->carregarOjbOficina($debug);
                                            if ($objInscrito->getObjOficina()->getCodOficina() != '') {
                                                $pos = strpos($objInscrito->getObjOficina()->getTitulo(), $objInscrito->getObjOficina()->getTipo());
                                                if ($pos === false) {
                                                    echo "<h4 class='corTitulo'>" . $objInscrito->getObjOficina()->getTipo() . ": " . $objInscrito->getObjOficina()->getTitulo() . "<br>";
                                                } else {
                                                    echo "<h4 class='corTitulo'>" . $objInscrito->getObjOficina()->getTitulo() . "<br>";
                                                }
                                                $dataAtiv = $objInscrito->getObjOficina()->getDataInicio() ?: $objInscrito->getObjOficina()->getDataIni();
                                                if (isDateStringValid($dataAtiv)) {
                                                    echo formatDateView($dataAtiv) . " - " . $objInscrito->getObjOficina()->getHorarioIni() . "</h4>";
                                                }
                                                if ($fws_['op_atividade_valor_variavel'] == 1) {
                                                    $valorTotalOficinas = $valorTotalOficinas + $objInscrito->getObjOficina()->getValorAtividade();
                                                }
                                                echo $objInscrito->getObjOficina()->getInvestimento();
                                                echo "<input type='hidden' name='cod_oficina[]' value='" . $objInscrito->getObjOficina()->getCodOficina() . "'>";
                                                echo "<hr>";
                                            }
                                            $objInscrito->setValorTotalAtividade($valorTotalOficinas);
                                        } catch (OKException $exc) {
                                        }
                                    }
                                    /*
                        if ($fws_['cod_oficina']['0']!=''){
                            $objInscrito->setCodOficina($fws_['cod_oficina']['0']);
                            $objInscrito->carregarOjbOficina($debug);
                            if($objInscrito->getObjOficina()->getCodOficina()!=''){
                                echo "<h4 class='corTitulo'>".$objInscrito->getObjOficina()->getTipo().": ".$objInscrito->getObjOficina()->getTitulo()."<br>";
                                echo $objInscrito->getObjOficina()->getDataIni()." - ".$objInscrito->getObjOficina()->getHorarioIni()."</h4>";
                                echo $objInscrito->getObjOficina()->getInvestimento();
                                echo "<input type='hidden' name='cod_oficina[]' value='".$objInscrito->getObjOficina()->getCodOficina()."'>";
                                echo "<hr>";
                            }
                        }
                        if ($fws_['cod_oficina']['1']!=''){
                            $objInscrito->setCodOficina($fws_['cod_oficina']['1']);
                            $objInscrito->carregarOjbOficina($debug);
                            if($objInscrito->getObjOficina()->getCodOficina()!=''){
                                echo "<h4 class='corTitulo'>".$objInscrito->getObjOficina()->getTipo().": ".$objInscrito->getObjOficina()->getTitulo()."<br>";
                                echo $objInscrito->getObjOficina()->getDataIni()." - ".$objInscrito->getObjOficina()->getHorarioIni()."</h4>";
                                echo $objInscrito->getObjOficina()->getInvestimento();
                                echo "<input type='hidden' name='cod_oficina[]' value='".$objInscrito->getObjOficina()->getCodOficina()."'>";
                                echo "<hr>";
                            }
                        }
                        if ($fws_['cod_oficina']['2']!=''){
                            $objInscrito->setCodOficina($fws_['cod_oficina']['2']);
                            $objInscrito->carregarOjbOficina($debug);
                            if($objInscrito->getObjOficina()->getCodOficina()!=''){
                                echo "<h4 class='corTitulo'>".$objInscrito->getObjOficina()->getTipo().": ".$objInscrito->getObjOficina()->getTitulo()."<br>";
                                echo $objInscrito->getObjOficina()->getDataIni()." - ".$objInscrito->getObjOficina()->getHorarioIni()."</h4>";
                                echo $objInscrito->getObjOficina()->getInvestimento();
                                echo "<input type='hidden' name='cod_oficina[]' value='".$objInscrito->getObjOficina()->getCodOficina()."'>";
                                echo "<hr>";
                            }
                        }
                        if (($fws_['cod_oficina']['0']=='') and ($fws_['cod_oficina']['1']=='') and ($fws_['cod_oficina']['2']=='')){
                            echo "<h4 class='corTitulo'>Atividade: não optou por atividade</h4>";
                        }
                         * 
                         */
                                } else {
                                    echo "<h4 class='corTitulo'>Atividade: não optou por atividade</h4>";
                                }

                                //                        $objInscrito->carregarOjbOficina($debug);
                                //                        if($objInscrito->getObjOficina()->getCodOficina()!=''){
                                //                            echo "<h4 class='corTitulo'>Atividade: ".$objInscrito->getObjOficina()->getTitulo()."<br>";
                                //                            echo $objInscrito->getObjOficina()->getDataIni()." - ".$objInscrito->getObjOficina()->getHorarioIni()."</h4>";
                                //                            echo $objInscrito->getObjOficina()->getInvestimento();
                                //                            echo "<input type='hidden' name='cod_oficina' value='".$objInscrito->getObjOficina()->getCodOficina()."'>";
                                //                            echo "<hr>";
                                //                        }else{
                                //                            echo "<h4 class='corTitulo'>Atividade: não optou por atividade</h4>";
                                //                        }
                                ?>
                            </div>
                    <?php }
                    } ?>
                    <?php if ($OP_SELECIONAR_ALMOCO) { ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <h4 class='corTitulo'>Acrescentar valor do almoço junto ao boleto da inscrição?</h4>
                            <table class='tb-valor'>
                                <tr>
                                    <td width="100%" height="10" colspan=2>Valor unitário R$ 49,00 (inclui um refrigerante ou água mineral por pessoa/refeição).</td>
                                </tr>
                                <tr>
                                    <td width="100%" height="10">

                                        &nbsp;&nbsp;<input type="checkbox" <?php echo verifItemChecked('Terca',   json_decode($objInscrito->getInfoAlmoco())) ?> id="infoDiaSemana" name="info_almoco[]" value="Terca"> Terça-feira – 15/08/2017
                                        &nbsp;&nbsp;<input type="checkbox" <?php echo verifItemChecked('Quarta',  json_decode($objInscrito->getInfoAlmoco())) ?> id="infoDiaSemana" name="info_almoco[]" value="Quarta"> Quarta-feira – 16/08/2017
                                        &nbsp;&nbsp;<input type="checkbox" <?php echo verifItemChecked('Quinta',  json_decode($objInscrito->getInfoAlmoco())) ?> id="infoDiaSemana" name="info_almoco[]" value="Quinta"> Quinta-feira – 17/08/2017
                                        <br>&nbsp;
                                    </td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                    <?php } ?>
                <?php } ?>
                <!--            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <button class='btn btn-xs btn-primary' type="submit">Avançar</button>
                <button class='btn btn-xs btn-primary' type="submit">Salvar Inscrição</button>
                <button class='btn btn-xs btn-primary' type="reset">Limpar</button>
                <a href="<?php echo $urlVoltar ?>"
                   class="btn btn-xs btn-primary" role="button">Voltar</a>
            </div>-->
                <br>
                <h4 class='corTitulo'>Formulário de Cadastro</h4>
                <hr>
                <?php if ($objEvConf->getDadosPessoaisTermoCompromisso() == 1 and $objEv->getCodEvento() >= 37) { ?>
                    <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                        <h4>Termos e Condições</h4>

                        <?php
                        if ($objEvConf->getDadosPessoaisTermoCompromissoTexto() != '') {
                            echo $objEvConf->getDadosPessoaisTermoCompromissoTexto();
                        } else {
                            echo "Texto do termo não informado";
                        }
                        ?>
                        <?php if ($objEvConf->getDadosPessoaisTermoCompromissoTexto() != '') { ?>
                            <input name="aceitou_termos" required="" type="radio" value="SIM" autofocus="" />
                            <span style="color: green;">
                                SIM, LI todos os documentos indicados acima, CONCORDO e ACEITO OS TERMOS E CONDI&Ccedil;&Otilde;ES NELES CONTIDOS
                            </span>
                        <?php } ?>
                        <br><br>
                        <input id="nao_aceitou_termos" name="aceitou_termos" required="" type="radio" value="NAO" autofocus="" />
                        N&Atilde;O, n&atilde;o aceito o termos descritos acima.
                        <br><br>
                        <hr style="border-top: 1px solid #eee !important;">
                    </div>
                <?php } ?>

                <?php
                $exibeComplementoArquivo = file_exists(dirname(__FILE__).'/inscricao_congresso_cadastro_dados_pessoais_complemento_'.COD_EVENTO.'.php');
                if (!$readOnly || $exibeComplementoArquivo) {
                    if ($fws_['op_dados_pessoais_complemento'] or $objEvConf->getDadosPessoaisComplemento()== 1) {
                        if ($exibeComplementoArquivo or COD_EVENTO == 98) {
                            include_once "inscricao_congresso_cadastro_dados_pessoais_complemento_" . COD_EVENTO . ".php";
                        } else {
                            if ($objEvConf->getDadosPessoaisComplemento()) {
                                echo '<div class="form-group col-sm-12" style="padding-left: 0px;">';
                                echo $objEvConf->getDadosPessoaisComplementoTexto();
                                echo '</div>';
                            }
                        }
                    }
                }
                if ($exigeComprovantePerfil) {
                    include "inscricao_congresso_cadastro_dados_pessoais_comprovante.php";
                }
                ?>

                <div class="form-group col-sm-12" style="margin-bottom:5px;margin-top:10px;padding-left: 0px;">
                    <?php if ($objEvConf->getEstrangeiroSemCpf() == true) { ?>
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <input type='checkbox' id='estrangeiro' <?php echo $disabled ?> name='estrangeiro' value='ON' />&nbsp;Estrangeiro sem CPF?<br />
                    </div>
                    <?php } ?>
                    <div class="form-group col-sm-10" style="padding-left: 0px;">
                        <label for="nome_inscrito">Nome do Inscrito:</label>
                        <input type="text" id="nome_inscrito" name="nome_inscrito" value="<?php echo $objInscrito->getNomeInscrito() ?>" class="form-control" placeholder="Nome do Inscrito" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                    </div>
                    <?php if ($objEvConf->getFormNomeCracha() == true) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="nome_apelido">Nome para crachá:</label>
                            <input type="text" id="nome_apelido" name="nome_apelido" value="<?php echo $objInscrito->getNomeApelido() ?>" class="form-control" placeholder="Nome como quero ser chamado (crachá)" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormSexo() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="sexo">Sexo:</label>
                            <select class='form-control' name='sexo' id='sexo' placeholder="sexo" required autofocus>
                                <?php
                                $fws_['sexo'] = $objInscrito->getSexo();
                                CarregarOptionsAC::sexo($fws_, $debug);
                                ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRaca() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="raca">Raça:</label>
                            <select class="form-control" name="raca" id="raca" placeholder="Informe sua raça" required autofocus>
                                <option class='form_campos' value='' selected>Selecione sua raça</option>
                                <?php
                                $fws_['raca'] = $objInscrito->getRaca();
                                CarregarOptionsAC::raca($fws_, $debug);
                                ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormEstadoCivil() == true) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="estado_civil">Estado civíl:</label>
                            <select class='form-control' value='' name='estado_civil' id='estado_civil' placeholder="Selecione um estado civíl" required autofocus>
                                <?php
                                $fws_['estado_civil'] = @$objInscrito->getEstadoCivil();
                                CarregarOptionsAC::estadoCivil($fws_, $debug);
                                ?>
                            </select>
                        </div>
                    <?php } ?>
                </div>
                <div class="form-group col-sm-12" style="margin-bottom:5px;margin-top:0px;padding-left: 0px;">
                    <?php if ($objEvConf->getFormDataNascimento() == true) { ?>
                        <br>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="data_nascimento">Data de Nasc.:</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo $objInscrito->getDataNascimento() ?>" class="form-control" autofocus placeholder="dd/mm/yyyy" SIZE='60' maxlength='10' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormEstadoNascimento() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="estado_nascimento">Estado Nascimento:</label>
                            <select class='form-control' name="estado_nascimento" id="estado_nascimento" placeholder="Estado de Nascimento" required autofocus>
                                <option class='form_campos' value='' selected>Selecione um Estado</option>
                                <?php
                                $fws_['estado_nascimento'] = $objInscrito->getEstadoNascimento();
                                CarregarOptionsAC::estadosNascimento($fws_, $debug);
                                ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormCidadeNascimento() == true) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="cidade_nascimento">Cidade Nascimento:</label>
                            <select class='form-control' name="cidade_nascimento" id="cidade_nascimento" placeholder="Cidade de Nascimento" required autofocus>
                                <?php
                                $fws_['cidade_nascimento'] = $objInscrito->getCidadeNascimento();
                                CarregarOptionsAC::cidadesNascimento($fws_, $debug);
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                </div>
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="cpf" id="textoCpf" class="show">Cpf:</label>
                        <label for="cpf" id="textoPassaporte" class="hidden">Cpf/Passaporte:</label>
                        <input type="text" id="cpf" <?php echo $readOnly ?> name="cpf" value="<?php echo $objInscrito->getCpf() ?>" class="form-control" placeholder="Cpf" required autofocus SIZE='60' maxlength='11' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="e_mail">E-mail:</label>
                        <input type="email" id="e_mail" <?php echo $readOnly ?> name="e_mail" value="<?php echo $objInscrito->getEMail() ?>" class="form-control" placeholder="Informe e-mail" required autofocus SIZE='60' maxlength='255' onBlur='trimEmail(this);'>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="re_e_mail">Confirme seu E-mail:</label>
                        <input type="email" id="re_e_mail" <?php echo $readOnly ?> name="re_e_mail" value="<?php echo $objInscrito->getReEMail() ?>" class="form-control" placeholder="Confirme seu e-mail" required autofocus SIZE='60' maxlength='255' onBlur='trimEmail(this);'>
                    </div>
                    <?php if ($objEvConf->getFormMelhorEmail() == true) { ?>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="e_mail_2">E-mail 2:</label>
                        <input type="email" id="e_mail_2" <?php echo $readOnly ?> name="e_mail_2" value="<?php echo $objInscrito->getEMail2() ?>" class="form-control" placeholder="Informe um segundo e-mail" required autofocus SIZE='60' maxlength='255' onBlur='trimEmail(this);'>
                    </div>
                    <?php } ?>
                </div>
                <div class="form-group col-sm-12" style="margin-bottom:0px;margin-top:0px;padding-left: 0px;">
                    <div class="form-group col-sm-1" style="padding-left: 0px;">
                        <label for="fone_contato_ddd">DDD:</label>
                        <input type="text" id="fone_contato_ddd" name="fone_contato_ddd" value="<?php echo $objInscrito->getFoneContatoDdd() ?>" class="form-control" SIZE='60' maxlength='2' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));' required autofocus>
                    </div>
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="fone_contato">Fone Contato:</label>
                        <input type="text" id="fone_contato" name="fone_contato" value="<?php echo $objInscrito->getFoneContato() ?>" class="form-control" SIZE='60' maxlength='14' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));' required autofocus>
                    </div>
                </div>
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:0px;padding-left: 0px;">
                    <?php if ($objEvConf->getFormPis() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="pis">PIS:</label>
                            <input type="text" id="pis" name="pis" value="<?php echo $objInscrito->getPis() ?>" class="form-control" placeholder="Informe o número de seu PIS" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='11' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormNis() == true and $objInscrito->getCodPerfil()==560) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="nis">NIS:</label>
                            <input type="text" id="nis" name="nis" value="<?php echo $objInscrito->getNis() ?>" class="form-control" placeholder="Informe o número de seu NIS" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='11' maxlength='11'                           onBlur="validarNIS(this); trim(this);" onkeypress='javascript: return isNum(getKey(event));'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormNomeMae() == true  and $objInscrito->getCodPerfil()==560) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="nome_mae">Nome da Mãe:</label>
                            <input type="text" id="nome_mae" name="nome_mae" value="<?php echo $objInscrito->getNomeMae() ?>" class="form-control" placeholder="Nome da Mãe" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormNomePai() == true  and $objInscrito->getCodPerfil()==560) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="nome_pai">Nome do Pai:</label>
                            <input type="text" id="nome_pai" name="nome_pai" value="<?php echo $objInscrito->getNomePai() ?>" class="form-control" placeholder="Nome do Pai" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRg() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="rg">RG:</label>
                            <input type="text" id="rg" name="rg" value="<?php echo $objInscrito->getRg() ?>" class="form-control" placeholder="Informe o número de seu RG" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isRg(getKey(event));'>
                        </div>
                    <?php } ?>
                    <?php if (
                        ($objEvConf->getTrabAnexoTipo() == 'Termo de Concessão de Bolsa')
                        or ($objEvConf->getTrabAnexoTipo() == 'Contrato de Prestação de Serviço')
                    ) {
                    ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="possui_outro_vinculo_empregaticio">Já possúi outro vínculo empregratício?</label>
                            &nbsp;&nbsp;&nbsp;
                            <input type='radio' name='possui_outro_vinculo_empregaticio' value='SIM' <?php echo verifItemChecked($objInscrito->getPossuiOutroVinculoEmpregaticio(), 'SIM') ?> required autofocus>
                            Sim
                            &nbsp;&nbsp;&nbsp;
                            <input type='radio' name='possui_outro_vinculo_empregaticio' value='NAO' <?php echo verifItemChecked($objInscrito->getPossuiOutroVinculoEmpregaticio(), 'NAO') ?> required autofocus>
                            Não
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRgDataEmissao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="rg_data_emissao">RG Data de Emissão:</label>
                            <input type="date" id="rg_data_emissao" name="rg_data_emissao" value="<?php echo $objInscrito->getRgDataEmissao() ?>" class="form-control" placeholder="Data de emissão do RG" required autofocus placeholder="dd/mm/yyyy" SIZE='60' maxlength='10' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRgEstadoEmissao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="rg_estado_emissao">RG Estado Emissão:</label>
                            <select class="form-control" name="rg_estado_emissao" id="rg_estado_emissao" placeholder="Selecione um Estado" required autofocus>
                                <option class='form_campos' value='' selected>Selecione um Estado</option>
                                <?php
                                $fws_['rg_estado_emissao'] = $objInscrito->getRgEstadoEmissao();
                                CarregarOptionsAC::rgEstadoEmissao($fws_, $debug);
                                ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRgCidadeEmissao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="rg_cidade_emissao">RG Cidade Emissão:</label>
                            <select class="form-control" name="rg_cidade_emissao" id="rg_cidade_emissao" placeholder="Selecione uma Cidade" required autofocus>
                                <?php
                                $fws_['rg_cidade_emissao'] = $objInscrito->getRgCidadeEmissao();
                                CarregarOptionsAC::rgCidadeEmissao($fws_, $debug);
                                ?>
                            </select>
                        </div>

                    <?php } ?>
                    <?php if ($objEvConf->getFormRgOrgaoExpedidor() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="orgao_expedidor">RG Órgão Expedidor:</label>
                            <input type="text" id="orgao_expedidor" name="orgao_expedidor" value="<?php echo $objInscrito->getOrgaoExpedidor() ?>" class="form-control" placeholder="Órgão expedidor de seu RG" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
                        </div>
                    <?php } ?>
                </div>
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="empresa_atual">Endereço: </label>
                    </div>
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="cep">Cep: <span id="cepStatus" style="font-weight:normal; color:#1976d2;"></span></label>
                        <input type="text" id="cep" name="cep" value="<?php echo $objInscrito->getCep() ?>" class="form-control" placeholder="Cep" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus SIZE='60' maxlength='9' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                    </div>
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="endereco">Rua:</label>
                        <input type="text" id="endereco" name="endereco" value="<?php echo $objInscrito->getEndereco() ?>" class="form-control" placeholder="" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus SIZE='60' maxlength='255' onBlur='trim(this);' >
                    </div>
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="numero">Número:</label>
                        <input type="text" id="numero" name="numero" value="<?php echo $objInscrito->getNumero() ?>" class="form-control" placeholder="Número" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));' >
                    </div>
                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="complemento">Complemento:</label>
                        <input type="text" id="complemento" name="complemento" value="<?php echo $objInscrito->getComplemento() ?>" class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);' >
                    </div>
                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="bairro">Bairro:</label>
                        <input type="text" id="bairro" name="bairro" value="<?php echo $objInscrito->getBairro() ?>" class="form-control" placeholder="Bairro" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus SIZE='60' maxlength='255' onBlur='trim(this);' >
                    </div>
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="estado">Estado:</label>
                        <select class='form-control' name='estado' id='estado' placeholder="Estado" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> required autofocus>
                            <option class='form_campos' value='' selected>Selecione um Estado</option>
                            <?php
                            $fws_['estado'] = $objInscrito->getEstado();
                            CarregarOptionsAC::estados($fws_, $debug, false);
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="cidade">Cidade: </label>
                        <select class='form-control' name='cidade' id='cidade' placeholder="Cidade" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus required>
                            <?php
                            $fws_['cidade'] = @$objInscrito->getCidade();
                            CarregarOptionsAC::cidades($fws_, $debug);
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <?php if ($objEvConf->getFormDadosBancarios() == true) { ?>
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
                    <?php } ?>

                    <?php /* if(COD_EVENTO==9 or $OP_EDICAO_DADOS_PESSOAIS_NOME=='PAGADOR'){ ?>
                <div class="form-group col-sm-7" style="padding-left: 0px;">
                    <label for="nome_inscrito">Nome do Pagador:</label>
                    <input type="text" id="nome_inscrito" name="nome_inscrito" value="<?php echo $objInscrito->getNomeInscrito() ?>"
                           class="form-control" placeholder="Nome do Pagador" required autofocus
                           oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');"
                            SIZE='60' maxlength='255' onBlur='trim(this);' >
                </div>
                
            <?php } if($OP_EDICAO_DADOS_PESSOAIS_NOME=='PARTICIPANTE'){   ?>
               
                <div class="form-group col-sm-4" style="padding-left: 0px;">
                    <label for="nome_inscrito">Nome do Participante:</label>
                    <input type="text" id="nome_inscrito" name="nome_inscrito" value="<?php echo $objInscrito->getNomeInscrito() ?>"
                           class="form-control" placeholder="Nome do Participante" required autofocus
                           oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');"
                            SIZE='60' maxlength='255' onBlur='trim(this);' >
                </div> 
            <?php   }else if($OP_EDICAO_DADOS_PESSOAIS_NOME=='INSCRITO'){ ?>
            

            <?php } else { ?>
                
                <div class="form-group col-sm-7" style="padding-left: 0px;">
                    <label for="nome_inscrito">
                        <?php
                        if ($objEvConf->getDadosPessoaisNome()!=''){
                            echo $objEvConf->getDadosPessoaisNome();
                            $placeNomeInscrito = $objEvConf->getDadosPessoaisNome();
                        }else if ($objEvConf->getTrabAnexoTipo()=='Termo de Concessão de Bolsa'){ 
                            echo "Nome do Candidado:";
                            $placeNomeInscrito = "Nome do Candidado";
                        }else if ($objEvConf->getCodEvento()==42){ 
                            echo "Nome do Candidado:";
                            $placeNomeInscrito = "Nome do Candidado";
                        }else if ($objEvConf->getCodEvento()==44){ 
                            echo "Nome do Candidado:";
                            $placeNomeInscrito = "Nome do Candidado";
                        }else if ($objEvConf->getCodEvento()==46){ 
                            echo "Nome do Candidado:";
                            $placeNomeInscrito = "Nome do Candidado";
                        }else if ($objEvConf->getCodEvento()==45){ 
                            echo "Nome do Commpleto:";
                            $placeNomeInscrito = "Nome do Commpleto";
                        }else{ 
                            echo "Nome para o Certificado:";
                            echo $objEvConf->getDadosPessoaisNome();
                            $placeNomeInscrito = "Nome para o Certificado";
                        }
                        ?>
                    </label>
                    <input type="text" id="nome_inscrito" name="nome_inscrito" value="<?php echo $objInscrito->getNomeInscrito() ?>"
                           class="form-control" placeholder="<?php echo $placeNomeInscrito; ?>" required autofocus
                           oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');"
                            SIZE='60' maxlength='255' onBlur='trim(this);' >
                </div>
                
            <?php } */ ?>
                    <?php if ($fws_['op_dados_pessoais_complemento_ingresso']) {
                        include_once "inscricao_congresso_cadastro_dados_pessoais_complemento_ingresso_" . COD_EVENTO . ".php";
                    }
                    ?>

                </div>
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <?php if ($objEvConf->getFormGrauEscolaridade() == true) { ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="grau_escolaridade">Outros: </label>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="grau_escolaridade">Grau de Escolaridade:</label>
                            <select class='form-control' value='' name='grau_escolaridade' id='grau_escolaridade' placeholder="Selecione grau de escolaridade" required autofocus>
                                <?php
                                $fws_['grau_escolaridade'] = @$objInscrito->getGrauEscolaridade();
                                CarregarOptionsAC::grauEscolaridade($fws_, $debug);
                                ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormGraduacao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="graduacao">Graduação:</label>
                            <input type="text" id="graduacao" name="graduacao" value="<?php echo $objInscrito->getGraduacao() ?>" class="form-control" placeholder="Informe sua graduação" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormGraduacaoAno() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="graduacao_ano">Ano da Graduação:</label>
                            <input type="text" id="graduacao_ano" name="graduacao_ano" value="<?php echo $objInscrito->getGraduacaoAno() ?>" class="form-control" placeholder="Informe ano da graduação" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='25' maxlength='4' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormGraduacaoIes() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="graduacao_ies">IES da Graduação:</label>
                            <input type="text" id="graduacao_ies" name="graduacao_ies" value="<?php echo $objInscrito->getGraduacaoIes() ?>" class="form-control" placeholder="Informe IES da Graduação" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormPosGraduacao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="pos_graduacao">Pós-Graduação:</label>
                            <input type="text" id="pos_graduacao" name="pos_graduacao" value="<?php echo $objInscrito->getPosGraduacao() ?>" class="form-control" placeholder="Informe sua pos-graduação" autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormPosGraduacaoAno() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="pos_graduacao_ano">Ano da Pós-Graduação:</label>
                            <input type="text" id="pos_graduacao_ano" name="pos_graduacao_ano" value="<?php echo $objInscrito->getPosGraduacaoAno() ?>" class="form-control" placeholder="Informe ano da pós-graduação" autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='25' maxlength='4' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'> 
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormPosGraduacaoIes() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="pos_graduacao_ies">IES da Pós-Graduação:</label>
                            <input type="text" id="pos_graduacao_ies" name="pos_graduacao_ies" value="<?php echo $objInscrito->getPosGraduacaoIes() ?>" class="form-control" placeholder="Informe IES da Pós-Graduação" autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                </div>
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <?php if ($objEvConf->getFormEmpresaAtual() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="empresa_atual">Empresa Atual:</label>
                            <small class="form-text text-muted">Se não estiver trabalhando no momento, escreva “Nenhuma” ou algo que indique sua ocupação atual.</small>
                            <input type="text" id="empresa_atual" name="empresa_atual" value="<?php echo $objInscrito->getEmpresaAtual() ?>" class="form-control" placeholder="Informe empresa atual" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormCargoAtual() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="cargo_atual">Cargo Atual:</label>
                            <input type="text" id="cargo_atual" name="cargo_atual" value="<?php echo $objInscrito->getCargoAtual() ?>" class="form-control" placeholder="Cargo Atual" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getDadosPessoaisTipoInstituicao() == true) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="tipo_instituicao">Tipo da Instituição:</label>
                            <select class='form-control' name='tipo_instituicao' id='tipo_instituicao' placeholder="Tipo da instituição" required autofocus>
                                <option class='form_campos' value='' selected>Selecione um Tipo de Instituição</option>
                                <?php
                                $fws_['tipo_instituicao'] = $objInscrito->getTipoInstituicao();
                                CarregarOptionsAC::tipoInstituicoes($fws_, $debug);
                                ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getDadosPessoaisInstituicao() == true) { ?>
                        <div class="form-group col-sm-5" style="padding-left: 0px;">
                            <label for="instituicao">Nome da Instituição/Empresa:</label>
                            <input type="text" id="instituicao" name="instituicao" value="<?php echo $objInscrito->getInstituicao() ?>" class="form-control" placeholder="Nome da Instituição/Empresa" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getDadosPessoaisProfissao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="profissao">Profissão/Atividade:</label>
                            <!--
                <input type="text" id="profissao" name="profissao" value="<?php echo $objInscrito->getProfissao() ?>"
                       class="form-control" placeholder="Nome de sua profissão/atividade" required autofocus
                       oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');"
                        SIZE='60' maxlength='255' onBlur='trim(this);' >
                -->
                            <select class='form-control' name='profissao_atuacao' id='profissao_atuacao' placeholder="Nome de sua profissão/atividade" required autofocus>
                                <option class='form_campos' value='' selected>Selecione uma Profissão/Atividade</option>
                                <?php
                                $fws_['profissao_atuacao'] = $objInscrito->getProfissaoAtuacao();
                                CarregarOptionsAC::profissoes($fws_, $debug);
                                ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormPretensaoCarreira() == true) { ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="pretensao_carreira">Pretensão na Carreira:</label>
                            <input type="text" id="pretensao_carreira" name="pretensao_carreira" value="<?php echo $objInscrito->getPretensaoCarreira() ?>" class="form-control" placeholder="Pretensão na Carreira" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormComoFicouSabendo() == true) { ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="como_ficou_sabendo">Como ficou sabendo do Curso?</label>
                            <input type="text" id="como_ficou_sabendo" name="como_ficou_sabendo" value="<?php echo $objInscrito->getComoFicouSabendo() ?>" class="form-control" placeholder="Como ficou sabendo" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormQualMotivoInscreveu() == true) { ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="qual_motivo_inscreveu">Por qual motivo você decidiu se inscrever no Curso?</label>
                            <input type="text" id="qual_motivo_inscreveu" name="qual_motivo_inscreveu" value="<?php echo $objInscrito->getQualMotivoInscreveu() ?>" class="form-control" placeholder="Informe por qual motivo você decidiu se inscrever no curso" required autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormTipoInscricao() == true) { ?>
                        <div class="form-group col-sm-5" style="padding-left: 0px;">
                            <label for="tipo_inscricao">Tipo da Inscrição:</label>
                            <?php
                            $fws_['tipo_inscricao'] = $tipo_inscricao;
                            CarregarOptionsAC::tipoInscricao($fws_, $debug, false);
                            ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($fws_['op_dados_pessoais_recibo'] or $objEvConf->getEmitirReciboTerceiro() == true) { ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;"></div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <input type='checkbox' <?php echo verifItemChecked('1', $objInscrito->getInstitucional()) ?> id='institucional' name='institucional' value='1' />
                            <?php if (in_array($objEv->getCodEvento(), [80,82])) { ?>
                                &nbsp;Deseja inscrever em nome de sua Instituição/Empresa?<br />
                            <?php }else{ ?>
                                &nbsp;Deseja emitir Pagamento/Recibo em nome de sua Instituição/Empresa ou terceiro?<br />
                            <?php } ?>
                        </div>

                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <div class="form-recibo form-group col-sm-7" style="padding-left: 0px;">
                            <?php if (in_array($objEv->getCodEvento(), [80,82])) { ?>
                                <label for="nome_recibo">Nome da Instituição/Empresa:</label>
                            <?php }else{ ?>
                            <label for="nome_recibo">Nome de quem efetuara o pagamento/Instituição:</label>
                            <?php } ?>                                

                                <input type="text" id="nome_recibo" name="nome_recibo" value="<?php echo $objInscrito->getNomeRecibo() ?>" class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);'>
                            </div>
                            <div class="form-recibo form-group col-sm-3" style="padding-left: 0px;">
                                <label for="cpf_recibo">CPF/CNPJ:</label>
                                <input type="text" id="cpf_recibo" name="cpf_recibo" value="<?php echo $objInscrito->getCpfRecibo() ?>" class="form-control" SIZE='60' maxlength='18' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                            </div>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <div class="form-recibo form-group col-sm-2" style="padding-left: 0px;">
                                <label for="cep_recibo">Cep: <span id="cepReciboStatus" style="font-weight:normal; color:#1976d2;"></span></label>
                                <input type="text" id="cep_recibo" name="cep_recibo" value="<?php echo  $objInscrito->getCepRecibo() ?>" class="form-control" placeholder="Cep" required autofocus SIZE='60' maxlength='9' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                            </div>
                            <div class="form-recibo form-group col-sm-6" style="padding-left: 0px;">
                                <label for="endereco_recibo">Rua:</label>
                                <input type="text" id="endereco_recibo" name="endereco_recibo" value="<?php echo $objInscrito->getEnderecoRecibo() ?>" class="form-control" placeholder="" required autofocus SIZE='60' maxlength='255' onBlur='trim(this);'>
                            </div>
                            <div class="form-recibo form-group col-sm-1" style="padding-left: 0px;">
                                <label for="numero_recibo">Número:</label>
                                <input type="text" id="numero_recibo" name="numero_recibo" value="<?php echo $objInscrito->getNumeroRecibo() ?>" class="form-control" placeholder="Número" required autofocus SIZE='60' maxlength='255' onBlur='trim(this);'>
                            </div>
                            <div class="form-recibo form-group col-sm-3" style="padding-left: 0px;">
                                <label for="complement_reciboo">Complemento:</label>
                                <input type="text" id="complemento_recibo" name="complemento_recibo" value="<?php echo $objInscrito->getComplementoRecibo() ?>" class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);'>
                            </div>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <div class="form-recibo form-group col-sm-1" style="padding-left: 0px;">
                                <label for="estado_recibo">Estado:</label>
                                <select class='form-control' name='estado_recibo' id='estado_recibo' placeholder="Estado" autofocus>
                                    <option class='form_campos' value='' selected>Selecione um Estado</option>
                                    <?php
                                    $fws_['estado_recibo'] = $objInscrito->getEstadoRecibo();
                                    CarregarOptionsAC::estados($fws_, $debug);
                                    ?>
                                </select>
                            </div>
                            <div class="form-recibo form-group col-sm-5" style="padding-left: 0px;">
                                <label for="cidade_recibo">Cidade:<?php echo $fws_['cidade_recibo'] ?> </label>
                                <select class='form-control' name='cidade_recibo' id='cidade_recibo' placeholder="Cidade" autofocus>
                                    <option class='form_campos' value='' selected>Selecione um Cidade</option>
                                    <option>Londrina</option>
                                    <?php
                                    $fws_['cidade_recibo'] = $objInscrito->getCidadeRecibo();
                                    CarregarOptionsAC::cidades($fws_, $debug);
                                    ?>
                                </select>
                            </div>
                            <div class="form-recibo form-group col-sm-2" style="padding-left: 0px;">
                                <label for="bairro_recibo">Bairro:</label>
                                <input type="text" id="bairro_recibo" name="bairro_recibo" value="<?php echo  $objInscrito->getBairroRecibo() ?>" class="form-control" placeholder="Bairro" required autofocus SIZE='60' maxlength='255' onBlur='trim(this);'>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <!--<button class='btn btn-xs btn-primary' type="submit">Avançar</button>-->
                            <?php
                            $labelBtnSalvar = trim($objEvConf->getFormBtnSalvarLabel());
                            if (!$labelBtnSalvar) {
                                $labelBtnSalvar = 'Salvar Inscrição';
                            }
                            ?>
                            <div style="margin:20px 15px 15px; padding-top:15px; border-top:1px solid #eee; display:flex; gap:8px; flex-wrap:wrap;">
                                <button class='btn btn-primary modalBtnSalvar' type="submit"><?php echo htmlspecialchars($labelBtnSalvar); ?></button>
                                <button class='btn btn-default modalBtnCancelar' type="reset">Limpar</button>
                                <a href="<?php echo $urlVoltar ?>" class="btn btn-default modalBtnCancelar" role="button">Voltar</a>
                            </div>
                        </div>
                        <br><br><br><br><br><br>&nbsp;
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script language="JavaScript1.2" src="resources/js/default.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script language="JavaScript" type="text/javascript">
    $(document).ready(function() {
        //    $("table.tablesorter").tablesorter({widgets: ['zebra']})
        //    .tablesorterPager({container: $("#pager")}); 

        $("#qtd_ingressos").change(function() {
            console.log('1')
            calcularValorIngressos(this.value);
        });
        $("#estado").change(function() {
            carregarCidades(this.value);
        });
        $("#estado_recibo").change(function() {
            carregarCidadesRecibo(this.value);
        });
        $("#cep").blur(function() {
            buscaCep(this.value);
        });
        $("#cep_recibo").blur(function() {
            buscaCepRecibo(this.value);
        });
        //    $('#data_nascimento').datepicker();
        //    $('#data_nascimento').datepicker({
        //        dateFormat: 'dd/mm/yyyy',
        //        viewMode: "years",
        //        todayHighlight: true,
        //        autoclose: true
        //        });
        //    });
        //    $("#data_nascimento").datepicker({dateFormat: 'dd/mm/yy'});

        $('#estrangeiro').change(function() {
            if (this.checked) {
                loading($('#textoCpf'), 'hidden')
                loading($('#textoPassaporte'), 'show')
                $('#cpf').attr("placeholder", "Cpf/Passaporte");
            } else {
                loading($('#textoCpf'), 'show')
                loading($('#textoPassaporte'), 'hidden')
                $('#cpf').attr("placeholder", "Cpf");
            }
        });
        $('#institucional').change(function() {
            if (this.checked) {
                $('#nome_recibo').removeAttr("disabled");
                $('#cpf_recibo').removeAttr("disabled");
                $('#endereco_recibo').removeAttr("disabled");
                $('#numero_recibo').removeAttr("disabled");
                $('#complement_reciboo').removeAttr("disabled");
                $('#estado_recibo').removeAttr("disabled");
                $('#cidade_recibo').removeAttr("disabled");
                $('#cep_recibo').removeAttr("disabled");
                $('#bairro_recibo').removeAttr("disabled");
                loading($('.form-recibo'), 'show')
            } else {
                $('#nome_recibo').attr("disabled", "true");
                $('#cpf_recibo').attr("disabled", "true");
                $('#endereco_recibo').attr("disabled", "true");
                $('#numero_recibo').attr("disabled", "true");
                $('#complement_reciboo').attr("disabled", "true");
                $('#estado_recibo').attr("disabled", "true");
                $('#cidade_recibo').attr("disabled", "true");
                $('#cep_recibo').attr("disabled", "true");
                $('#bairro_recibo').attr("disabled", "true");
                loading($('.form-recibo'), 'hidden')
            }

        });

        <?php if ($objInscrito->getInstitucional() == '1') { ?>
            $('#nome_recibo').removeAttr("disabled");
            $('#cpf_recibo').removeAttr("disabled");
            $('#endereco_recibo').removeAttr("disabled");
            $('#numero_recibo').removeAttr("disabled");
            $('#complement_reciboo').removeAttr("disabled");
            $('#estado_recibo').removeAttr("disabled");
            $('#cidade_recibo').removeAttr("disabled");
            $('#cep_recibo').removeAttr("disabled");
            $('#bairro_recibo').removeAttr("disabled");
            loading($('.form-recibo'), 'show')
        <?php } else { ?>
            $('#nome_recibo').attr("disabled", "true");
            $('#cpf_recibo').attr("disabled", "true");
            $('#endereco_recibo').attr("disabled", "true");
            $('#numero_recibo').attr("disabled", "true");
            $('#complement_reciboo').attr("disabled", "true");
            $('#estado_recibo').attr("disabled", "true");
            $('#cidade_recibo').attr("disabled", "true");
            $('#cep_recibo').attr("disabled", "true");
            $('#bairro_recibo').attr("disabled", "true");
            loading($('.form-recibo'), 'hidden')
        <?php } ?>
        //$("#fone_contato").mask("(00) 0000-00009");
        //    $("#fone_contato").keydown(function () {
        //    $("#fone_contato").unmask();
        //    var tamanho = $("#fone_contato").val().length;
        //    if (tamanho <= 10) {
        //        $("#fone_contato").mask("(99)9999-9999");
        //    } else {
        //        $("#fone_contato").mask("(99)99999-9999");
        //    }});

        const termoNao = document.getElementById("nao_aceitou_termos");
        if (termoNao) {
            termoNao.addEventListener("click", function() {
                const confirmacao = confirm("Para sair sem aceitar os termos clique em OK?");
                if (confirmacao) {
                    window.location.href = "<?php echo WB_URL_VIEW ?>";
                }
            });
        }
    });

    function calcularValorIngressos(qtd_ingressos) {
        console.log('2')
        if (qtd_ingressos !== '') {
            console.log('3')
            var valor = qtd_ingressos * 5;
            $('#valorIngressos').html(valor.toFixed(2)); // Exibe o valor com 2 casas decimais
        } else {
            console.log('4')
            $('#valorIngressos').html('')
        }
    }

    /* Aviso ao lado do rotulo do CEP enquanto o ViaCEP responde (mesmo padrao do admin
     * em inscritos-cadastro-campos.php): sem isso quem digita nao sabe se o sistema esta fazendo algo. */
    function statusCep(seletor, cor, texto) {
        $(seletor).css('color', cor).text(texto);
    }

    function buscaCep(cep) {
        var cep = cep.replace(/\D/g, '');
        if (cep == "") {
            statusCep('#cepStatus', '#1976d2', '');
            return;
        }
        //Expressão regular para validar o CEP.
        var validacep = /^[0-9]{8}$/;
        if (!validacep.test(cep)) {
            statusCep('#cepStatus', '#b71c1c', '— formato inválido');
            return;
        }
        statusCep('#cepStatus', '#1976d2', '— buscando...');
        //Consulta o webservice viacep.com.br/
        $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
            if ("erro" in dados) {
                statusCep('#cepStatus', '#b71c1c', '— CEP não encontrado');
                return;
            }
            //Atualiza os campos com os valores da consulta.
            $("#endereco").val(dados.logradouro);
            $("#bairro").val(dados.bairro);
            $("#estado").val(dados.uf);
            carregarCidades(dados.uf);
            $("#cidade").val(dados.localidade);
            statusCep('#cepStatus', '#1b5e20', '— ' + dados.localidade + '/' + dados.uf);
            $("#numero").focus();
        }).fail(function() {
            statusCep('#cepStatus', '#b71c1c', '— não foi possível consultar');
        });
    }

    function buscaCepRecibo(cep) {
        var cep = cep.replace(/\D/g, '');
        if (cep == "") {
            statusCep('#cepReciboStatus', '#1976d2', '');
            return;
        }
        var validacep = /^[0-9]{8}$/;
        if (!validacep.test(cep)) {
            statusCep('#cepReciboStatus', '#b71c1c', '— formato inválido');
            return;
        }
        statusCep('#cepReciboStatus', '#1976d2', '— buscando...');
        $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
            if ("erro" in dados) {
                statusCep('#cepReciboStatus', '#b71c1c', '— CEP não encontrado');
                return;
            }
            $("#endereco_recibo").val(dados.logradouro);
            $("#bairro_recibo").val(dados.bairro);
            $("#estado_recibo").val(dados.uf);
            carregarCidadesRecibo(dados.uf);
            $("#cidade_recibo").val(dados.localidade);
            statusCep('#cepReciboStatus', '#1b5e20', '— ' + dados.localidade + '/' + dados.uf);
            $("#numero_recibo").focus();
        }).fail(function() {
            statusCep('#cepReciboStatus', '#b71c1c', '— não foi possível consultar');
        });
    }

    function setMessageDefault(acao) {
        if (acao == 'oninvalid') {
            this.addEventListener('invalid', function() {
                this.setCustomValidity('É necessário preencher este campo!');
            }, false);
        }
        if (acao == 'onchange') {
            this.addEventListener('change', function() {
                this.setCustomValidity('');
            }, false);
        }
    }

    function mostrarInfoRecibo() {
        $('#institucional').change(function() {
            if (this.checked) {
                $('#nome_recibo').attr("disabled", "true");
                $('#cpf_recibo').attr("disabled", "true");
            } else {
                $('#nome_recibo').attr("disabled", "false");
                $('#cpf_recibo').attr("disabled", "false");
            }
        });
    }
    $("#estado_nascimento").change(function() {
        carregarCidadeNascimento(this.value);
    });
    $("#rg_estado_emissao").change(function() {
        carregarRgCidadeEmissao(this.value);
    });

    function carregarCidades(cod, selecionado) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>',
            data: ({
                'uf': cod,
                'c': 'CarregarOptions',
                'm': 'cidades',
                '_submit_check': '1'
            }),
            dataType: "html",
            async: false,
            cache: false,
            success: function(data) {
                $("#cidade").html(data);
            },
            error: function() {
                alert("Nao foi possivel obter informações:\n" + req.statusText);
            }
        });
    }

    function carregarCidadesRecibo(cod, selecionado) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>',
            data: ({
                'uf': cod,
                'c': 'CarregarOptions',
                'm': 'cidades',
                '_submit_check': '1'
            }),
            dataType: "html",
            async: false,
            cache: false,
            success: function(data) {
                $("#cidade_recibo").html(data);
            },
            error: function() {
                alert("Nao foi possivel obter informações:\n" + req.statusText);
            }
        });
    }

    function carregarCidadeNascimento(cod, selecionado) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>',
            data: ({
                'uf': cod,
                'c': 'CarregarOptions',
                'm': 'cidades',
                '_submit_check': '1'
            }),
            dataType: "html",
            async: false,
            cache: false,
            success: function(data) {
                $("#cidade_nascimento").html(data);
            },
            error: function() {
                alert("Nao foi possivel obter informações:\n" + req.statusText);
            }
        });
    }

    function carregarRgCidadeEmissao(cod, selecionado) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>',
            data: ({
                'uf': cod,
                'c': 'CarregarOptions',
                'm': 'cidades',
                '_submit_check': '1'
            }),
            dataType: "html",
            async: false,
            cache: false,
            success: function(data) {
                $("#rg_cidade_emissao").html(data);
            },
            error: function() {
                alert("Nao foi possivel obter informações:\n" + req.statusText);
            }
        });
    }

    // Limpa marcação de erro do campo (borda + mensagem inline) assim que o usuário corrige.
    // Uso addEventListener nativo porque o projeto carrega múltiplas jQuerys (1.12.4, 2.1.1)
    // que se sobrescrevem e removem handlers delegados via $(document).on(...).
    document.addEventListener('input', function(ev) {
        var t = ev.target;
        if (t && t.classList && t.classList.contains('is-invalid')) {
            t.classList.remove('is-invalid');
            var prox = t.nextElementSibling;
            while (prox) {
                if (prox.classList && prox.classList.contains('invalid-feedback-dyn')) {
                    prox.parentNode.removeChild(prox);
                    break;
                }
                prox = prox.nextElementSibling;
            }
        }
    }, true);
    document.addEventListener('change', function(ev) {
        var t = ev.target;
        if (t && t.classList && t.classList.contains('is-invalid')) {
            t.classList.remove('is-invalid');
            var prox = t.nextElementSibling;
            while (prox) {
                if (prox.classList && prox.classList.contains('invalid-feedback-dyn')) {
                    prox.parentNode.removeChild(prox);
                    break;
                }
                prox = prox.nextElementSibling;
            }
        }
    }, true);

    function limparErrosValidacao() {
        $('form[name=form] .is-invalid').removeClass('is-invalid');
        $('form[name=form] .invalid-feedback-dyn').remove();
    }

    function mostrarErrosValidacao(erros) {
        erros.forEach(function(e) {
            var $el = $(e.el);
            $el.addClass('is-invalid');
            // insere mensagem inline logo após o campo (BS5 .invalid-feedback)
            $el.nextAll('.invalid-feedback-dyn').first().remove();
            var $msg = $('<div class="invalid-feedback invalid-feedback-dyn" style="display:block;"></div>')
                .text(e.mensagem || e.label);
            $el.after($msg);
        });
        var primeiro = erros[0].el;
        try {
            var topo = $(primeiro).offset().top - 100;
            $('html, body').animate({ scrollTop: topo < 0 ? 0 : topo }, 300, function() {
                try { primeiro.focus({preventScroll: true}); } catch(err) { try { primeiro.focus(); } catch(err2) {} }
            });
        } catch(err) {
            try { primeiro.focus(); } catch(err2) {}
        }
    }

    function criticar() {
        limparErrosValidacao();
        var erros = [];
        var elEstrangeiro = document.form.elements['estrangeiro'];
        var isEstrangeiro = elEstrangeiro && elEstrangeiro.value === 'ON';

        function checar(nome, mensagem, validar) {
            var el = document.form.elements[nome];
            if (!el) return; // campo não renderizado por configuração — ignora
            if (el.disabled || el.readOnly) return;
            var valor = (el.value || '').toString();
            if (!validar(valor, el)) erros.push({ nome: nome, mensagem: mensagem, el: el });
        }

        checar('nome_inscrito', 'Informe seu nome', function(v) { return v.trim() !== ''; });
        if (!isEstrangeiro) {
            checar('cpf', 'Informe um CPF válido', function(v) { return isCpf(v); });
        }
        checar('e_mail', 'Informe um e-mail válido (usuário@dominio)', function(v) { return isEmail(v); });
        checar('re_e_mail', 'Informe um e-mail válido (usuário@dominio)', function(v) { return isEmail(v); });

        var elEmail = document.form.elements['e_mail'];
        var elReEmail = document.form.elements['re_e_mail'];
        if (elEmail && elReEmail
            && isEmail(elEmail.value) && isEmail(elReEmail.value)
            && elEmail.value !== elReEmail.value) {
            erros.push({ nome: 're_e_mail', mensagem: 'Não confere com o e-mail informado', el: elReEmail });
        }
        <?php if (COD_EVENTO != 9 && $OP_PERMITIR_ALTERAR_REQUIRED == 'required') { ?>
            checar('data_nascimento',  'Informe a data de nascimento', function(v) { return v !== ''; });
            checar('endereco',         'Informe a rua',                function(v) { return v.trim() !== ''; });
            checar('numero',           'Informe o número',             function(v) { return v.trim() !== ''; });
            checar('bairro',           'Informe o bairro',             function(v) { return v.trim() !== ''; });
            checar('cidade',           'Selecione a cidade',           function(v) { return v !== ''; });
            checar('cep',              'Informe o CEP',                function(v) { return v.trim() !== ''; });
            checar('estado',           'Selecione o estado',           function(v) { return v !== ''; });
            checar('fone_contato_ddd', 'DDD',                          function(v) { return v.trim() !== ''; });
            checar('fone_contato',     'Informe o telefone',           function(v) { return v.trim() !== ''; });
        <?php } ?>

        // Validar tamanho do arquivo antes de enviar
        var inputArquivo = document.getElementById('arquivo');
        if (inputArquivo && inputArquivo.files && inputArquivo.files.length > 0) {
            var tamanhoMax = 20 * 1024 * 1024; // 20MB
            if (inputArquivo.files[0].size > tamanhoMax) {
                erros.push({
                    nome: 'arquivo',
                    mensagem: 'Arquivo muito grande (m\u00e1x. 20MB; selecionado: ' + (inputArquivo.files[0].size / 1024 / 1024).toFixed(1) + 'MB)',
                    el: inputArquivo
                });
            }
        }

        if (erros.length > 0) {
            mostrarErrosValidacao(erros);
            return false;
        }

        // Proteção contra duplo submit
        if (window._inscricaoSubmetida) {
            return false;
        }
        window._inscricaoSubmetida = true;

        // Overlay de processamento
        $('#overlayProcessando').fadeIn(200);
        $('button[type="submit"]').prop('disabled', true);

        // Timeout de 30s - se travar, libera novamente
        window._timeoutInscricao = setTimeout(function() {
            window._inscricaoSubmetida = false;
            $('#overlayProcessando').fadeOut(200);
            $('button[type="submit"]').prop('disabled', false);
            $('#overlayProcessandoMsg').html(
                '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;padding:20px 30px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);text-align:center;max-width:450px;">'
                + '<i class="fa fa-exclamation-triangle" style="font-size:40px;color:#d9534f;"></i>'
                + '<h4 style="margin:15px 0 10px;">A opera&ccedil;&atilde;o demorou mais que o esperado</h4>'
                + '<p>Verifique sua conex&atilde;o e tente novamente.<br>Se o problema persistir, entre em contato conosco.</p>'
                + '<button class="btn btn-primary" onclick="window.location.reload();">Tentar novamente</button>'
                + '</div>'
            );
            $('#overlayProcessandoMsg').fadeIn(200);
        }, 30000);
    }
    // Função para validar NIS (11 dígitos, com dígito verificador)
    function validarNIS(campo) {
        let nis = campo.value.replace(/\D/g, ''); // remove não numéricos

        if (nis.length !== 11) {
            alert("O NIS deve conter exatamente 11 dígitos.");
            campo.focus();
            return false;
        }

        // Validação do dígito verificador
        let soma = 0;
        let multiplicador = 2;

        for (let i = 9; i >= 0; i--) {
            soma += parseInt(nis.charAt(i)) * multiplicador;
            multiplicador++;
            if (multiplicador > 9) multiplicador = 2;
        }

        let resto = soma % 11;
        let dv = (resto < 2) ? 0 : 11 - resto;

        if (dv !== parseInt(nis.charAt(10))) {
            alert("NIS inválido. Verifique o número informado.");
            campo.focus();
            return false;
        }

        return true;
    }
    //-->
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mailcheck/1.1.2/mailcheck.min.js"></script>
<script>
// Sugestão de correção de domínio de e-mail (Mailcheck) — pega typos como gmial/hotmial/yahho
$(function() {
    if (typeof Mailcheck === 'undefined') return;

    var dominiosConhecidos = [
        'gmail.com','hotmail.com','outlook.com','yahoo.com','icloud.com',
        'live.com','msn.com','aol.com','me.com','protonmail.com',
        'uol.com.br','bol.com.br','terra.com.br','ig.com.br','r7.com',
        'globo.com','globomail.com','gmail.com.br','hotmail.com.br',
        'yahoo.com.br','outlook.com.br'
    ];

    ['e_mail','re_e_mail','e_mail_2'].forEach(function(id) {
        var $campo = $('#' + id);
        if ($campo.length === 0) return;

        var $aviso = $('<small class="email-sugestao" style="display:none;margin-top:4px;color:#b97a00;"></small>');
        $campo.after($aviso);

        $campo.on('blur', function() {
            var valor = ($campo.val() || '').trim();
            if (!valor || valor.indexOf('@') === -1) { $aviso.hide().empty(); return; }

            Mailcheck.run({
                email: valor,
                domains: dominiosConhecidos,
                suggested: function(suggestion) {
                    $aviso.html(
                        '<i class="fa fa-question-circle"></i> Você quis dizer ' +
                        '<a href="#" class="email-aplicar" style="font-weight:bold;text-decoration:underline;">' +
                        $('<div>').text(suggestion.full).html() +
                        '</a>?'
                    ).show();
                    $aviso.find('.email-aplicar').on('click', function(e) {
                        e.preventDefault();
                        $campo.val(suggestion.full).trigger('change');
                        $aviso.hide().empty();
                    });
                },
                empty: function() {
                    $aviso.hide().empty();
                }
            });
        });

        $campo.on('input', function() { $aviso.hide().empty(); });
    });
});
</script>