<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
$debug = false;

// Recuperar tickets_json via POST (fluxo multi-ticket)
$tickets_json_raw = fws_raw('tickets_json');
$tickets = $tickets_json_raw ? json_decode($tickets_json_raw, true) : [];

// Recuperar cod_perfil: do primeiro ticket ou via POST/GET direto (backward compat)
if (!empty($tickets)) {
    $cod_perfil = $tickets[0]['cod_perfil'];
} else {
    $cod_perfil = isset($fws_['cod_perfil']) ? addslashesopctec($fws_['cod_perfil']) : '';
}

if ($cod_perfil == '') {
    $url = WB_URL_VIEW . "?content=view/inscricao/ticket_selecao.php";
    $_SESSION['MSG'] = "É necessário selecionar um ingresso.";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
    exit();
}

// Se não temos tickets_json (fluxo v1 com perfil único), montar array com 1 item
if (empty($tickets)) {
    // Carregar dados do perfil para montar o ticket único
    $objPerfilTmp = new Perfil();
    $objPerfilTmp->setCodPerfil($cod_perfil);
    $objPerfilTmp->setCodEvento(COD_EVENTO);
    $objPerfilTmp->getPerfilById($debug);

    $valorTmp = '';
    try {
        $objTaxaTmp = new Taxa();
        $objTaxaTmp->setCodPerfil($cod_perfil);
        $objTaxaTmp->setCodEvento(COD_EVENTO);
        $condTmp = $objPerfilTmp->getValoresPorPeriodo() ? ['data_hoje' => date('Y-m-d H:i:s')] : false;
        $objTaxaTmp->carregarTaxaAtiva($condTmp);
        $listaTaxasTmp = $objTaxaTmp->getListaTaxas();
        if (is_array($listaTaxasTmp) && count($listaTaxasTmp) > 0) {
            $valorTmp = $listaTaxasTmp[0]->getTxUnicaAVistaValor();
        }
    } catch (Exception $e) {}

    $tickets = [[
        'cod_perfil' => $cod_perfil,
        'quantidade' => 1,
        'titulo' => $objPerfilTmp->getTitulo(),
        'valor_unitario' => floatval($valorTmp)
    ]];
    $tickets_json_raw = json_encode($tickets);
}

// Carregar dados do perfil principal (para campos de configuração do evento)
$objPerfil = new Perfil();
$objPerfil->setCodPerfil($cod_perfil);
$objPerfil->setCodEvento(COD_EVENTO);
$objPerfil->getPerfilById($debug);

// Carregar taxa/preço ativo do perfil principal (para exibição)
$taxaAtiva = null;
$valorExibir = '';
$loteExibir = '';
try {
    $objTaxa = new Taxa();
    $objTaxa->setCodPerfil($cod_perfil);
    $objTaxa->setCodEvento(COD_EVENTO);
    $cond = $objPerfil->getValoresPorPeriodo() ? ['data_hoje' => date('Y-m-d H:i:s')] : false;
    $objTaxa->carregarTaxaAtiva($cond);
    $listaTaxas = $objTaxa->getListaTaxas();
    if (is_array($listaTaxas) && count($listaTaxas) > 0) {
        $taxaAtiva = $listaTaxas[0];
        $valorExibir = $taxaAtiva->getTxUnicaAVistaValor();
        $loteExibir = $taxaAtiva->getTitulo();
    }
} catch (Exception $e) {
    // Sem taxa ativa
}

$isGratuito = ($OP_INCRICAO_GRATUITA == 1) || (empty($valorExibir) || $valorExibir == 0 || $valorExibir == '0.00' || $valorExibir == '0,00');

// Calcular totais do carrinho
$totalQtd = 0;
$totalValor = 0;
$isMultiTicket = (count($tickets) > 1 || (count($tickets) == 1 && $tickets[0]['quantidade'] > 1));
foreach ($tickets as $tk) {
    $totalQtd += intval($tk['quantidade']);
    $totalValor += floatval($tk['valor_unitario']) * intval($tk['quantidade']);
}

// Recuperar dados em caso de erro (redirect de volta)
$e_mail_valor = isset($fwsGet_['e_mail']) ? addslashesopctec($fwsGet_['e_mail']) : '';

require_once 'inscricao_congresso_tit_area_inscrito.php';
?>
<style>
.sympla-progress { display:flex; justify-content:center; margin-bottom:25px; }
.sympla-progress .step { text-align:center; padding:8px 20px; position:relative; flex:1; max-width:200px; }
.sympla-progress .step .num { display:inline-block; width:32px; height:32px; line-height:32px; border-radius:50%; color:#fff; font-weight:bold; margin-bottom:4px; }
.sympla-progress .step.done .num { background:#2D937E; }
.sympla-progress .step.active .num { background:#2D937E; }
.sympla-progress .step.inactive .num { background:#ccc; }
.sympla-progress .step .label-step { display:block; font-size:12px; color:#666; }
.sympla-progress .step.active .label-step { color:#2D937E; font-weight:bold; }
.sympla-progress .step.done .label-step { color:#2D937E; }

.sympla-resumo {
    border: 1px solid #e0e0e0;
    border-left: 4px solid #2D937E;
    border-radius: 6px;
    padding: 15px 20px;
    margin-bottom: 20px;
    background: #f9f9f9;
}
.sympla-resumo .resumo-titulo { font-size: 16px; font-weight: bold; color: #333; }
.sympla-resumo .resumo-lote { font-size: 13px; color: #888; }
.sympla-resumo .resumo-preco { font-size: 18px; font-weight: bold; color: #2D937E; }
.sympla-form-section { margin-top: 15px; }
.sympla-btn-submit {
    background: #2D937E;
    color: #fff;
    border: none;
    padding: 12px 30px;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.2s;
}
.sympla-btn-submit:hover { background: #247a68; }
.sympla-btn-submit:disabled { background: #ccc; cursor: not-allowed; }

/* Tabela resumo carrinho */
.cart-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
.cart-table th, .cart-table td { padding:8px 12px; text-align:left; border-bottom:1px solid #eee; }
.cart-table th { font-size:13px; color:#888; font-weight:normal; }
.cart-table td { font-size:14px; color:#333; }
.cart-table .cart-total td { font-weight:bold; border-top:2px solid #2D937E; }

</style>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <!-- Barra de progresso -->
            <div class="sympla-progress">
                <div class="step done">
                    <span class="num">&#10003;</span>
                    <span class="label-step">Selecionar Ingresso</span>
                </div>
                <div class="step active">
                    <span class="num">2</span>
                    <span class="label-step">Dados Pessoais</span>
                </div>
                <div class="step inactive">
                    <span class="num">3</span>
                    <span class="label-step">Confirmação</span>
                </div>
            </div>

            <?php
            if ($fws_['msg']) {
                FWA_Mostrar_Mensagem($fws_['msg']);
            }
            ?>

            <!-- Resumo do carrinho -->
            <div class="sympla-resumo">
                <?php if ($isMultiTicket) { ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Ingresso</th>
                                <th style="text-align:center;">Qtd</th>
                                <th style="text-align:right;">Valor Unit.</th>
                                <th style="text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $tk) {
                                $subtotal = floatval($tk['valor_unitario']) * intval($tk['quantidade']);
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($tk['titulo']); ?></td>
                                    <td style="text-align:center;"><?php echo intval($tk['quantidade']); ?></td>
                                    <td style="text-align:right;">
                                        <?php if (floatval($tk['valor_unitario']) <= 0) { ?>
                                            Gratuito
                                        <?php } else { ?>
                                            R$ <?php echo number_format(floatval($tk['valor_unitario']), 2, ',', '.'); ?>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <?php if ($subtotal <= 0) { ?>
                                            Gratuito
                                        <?php } else { ?>
                                            R$ <?php echo number_format($subtotal, 2, ',', '.'); ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr class="cart-total">
                                <td>Total</td>
                                <td style="text-align:center;"><?php echo $totalQtd; ?></td>
                                <td></td>
                                <td style="text-align:right;color:#2D937E;">
                                    <?php if ($totalValor <= 0) { ?>
                                        Gratuito
                                    <?php } else { ?>
                                        R$ <?php echo number_format($totalValor, 2, ',', '.'); ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="text-align:right;margin-top:5px;">
                        <a href="<?php echo WB_URL_VIEW; ?>?content=view/inscricao/ticket_selecao.php" class="btn btn-default btn-sm">Alterar ingressos</a>
                    </div>
                <?php } else { ?>
                    <!-- Resumo single ticket (backward compat) -->
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="resumo-titulo"><?php echo $objPerfil->getTitulo(); ?></div>
                            <?php if ($loteExibir) { ?>
                                <div class="resumo-lote"><?php echo $loteExibir; ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-sm-2 text-center" style="display:flex;align-items:center;justify-content:center;">
                            <?php if ($isGratuito) { ?>
                                <span class="resumo-preco">Gratuito</span>
                            <?php } else { ?>
                                <span class="resumo-preco">R$ <?php echo number_format((float)$valorExibir, 2, ',', '.'); ?></span>
                            <?php } ?>
                        </div>
                        <div class="col-sm-2 text-center" style="display:flex;align-items:center;justify-content:center;">
                            <a href="<?php echo WB_URL_VIEW; ?>?content=view/inscricao/ticket_selecao.php" class="btn btn-default btn-sm">Trocar ingresso</a>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- Formulário de Dados Pessoais -->
            <form name="form" method="POST" onsubmit="return criticarSympla();" action="<?php echo WB_URL_COMMAND; ?>">
                <input type="hidden" name="c" value="GerenciarInscricao">
                <input type="hidden" name="m" value="salvarInscricao">
                <input type="hidden" name="acao" value="Inserir">
                <input type="hidden" name="cod_perfil" value="<?php echo $cod_perfil; ?>">
                <input type="hidden" name="tickets_json" id="tickets_json" value="<?php echo htmlspecialchars($tickets_json_raw); ?>">

                <h4 style="color:#333;margin-bottom:15px;">Formulário de Cadastro</h4>
                <hr>

                <?php
                // Termos e condições (se configurado)
                if ($objEvConf->getDadosPessoaisTermoCompromisso() == 1 && $objEv->getCodEvento() >= 37) { ?>
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
                            <input name="aceitou_termos" required type="radio" value="SIM" autofocus />
                            <span style="color: green;">
                                SIM, LI todos os documentos indicados acima, CONCORDO e ACEITO OS TERMOS E CONDI&Ccedil;&Otilde;ES NELES CONTIDOS
                            </span>
                        <?php } ?>
                        <br><br>
                        <input id="nao_aceitou_termos" name="aceitou_termos" required type="radio" value="NAO" autofocus />
                        N&Atilde;O, n&atilde;o aceito os termos descritos acima.
                        <br><br>
                        <hr style="border-top: 1px solid #eee !important;">
                    </div>
                <?php } ?>

                <?php
                // Complemento por evento (se configurado)
                if ($objEvConf->getDadosPessoaisComplemento() == 1) {
                    if (file_exists('inscricao_congresso_cadastro_dados_pessoais_complemento_' . COD_EVENTO . '.php')) {
                        include_once "inscricao_congresso_cadastro_dados_pessoais_complemento_" . COD_EVENTO . ".php";
                    } else {
                        if ($objEvConf->getDadosPessoaisComplementoTexto()) {
                            echo '<div class="form-group col-sm-12" style="padding-left: 0px;">';
                            echo $objEvConf->getDadosPessoaisComplementoTexto();
                            echo '</div>';
                        }
                    }
                }
                ?>

                <!-- Estrangeiro sem CPF -->
                <div class="form-group col-sm-12" style="margin-bottom:5px;margin-top:10px;padding-left: 0px;">
                    <?php if ($OP_ENTRANGEIRO_SEM_CPF) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <input type='checkbox' id='estrangeiro' name='estrangeiro' value='ON' />&nbsp;Estrangeiro sem CPF?<br />
                        </div>
                    <?php } ?>

                    <!-- Nome -->
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="nome_inscrito">Nome Completo: <span style="color:red;">*</span></label>
                        <input type="text" id="nome_inscrito" name="nome_inscrito" value="" class="form-control" placeholder="Nome Completo" required autofocus SIZE='60' maxlength='255' onBlur='trim(this);'>
                    </div>

                    <?php if ($objEvConf->getFormNomeCracha() == true) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="nome_apelido">Nome para crachá:</label>
                            <input type="text" id="nome_apelido" name="nome_apelido" value="" class="form-control" placeholder="Nome como quero ser chamado (crachá)" SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>

                    <?php if ($objEvConf->getFormSexo() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="sexo">Sexo:</label>
                            <select class='form-control' name='sexo' id='sexo' required>
                                <?php CarregarOptionsAC::sexo($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if ($objEvConf->getFormRaca() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="raca">Raça:</label>
                            <select class="form-control" name="raca" id="raca" required>
                                <option class='form_campos' value='' selected>Selecione sua raça</option>
                                <?php CarregarOptionsAC::raca($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if ($objEvConf->getFormEstadoCivil() == true) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="estado_civil">Estado civíl:</label>
                            <select class='form-control' name='estado_civil' id='estado_civil' required>
                                <?php CarregarOptionsAC::estadoCivil($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>
                </div>

                <!-- Data Nascimento / Local Nascimento -->
                <div class="form-group col-sm-12" style="margin-bottom:5px;margin-top:0px;padding-left: 0px;">
                    <?php if ($objEvConf->getFormDataNascimento() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="data_nascimento">Data de Nasc.:</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" value="" class="form-control" placeholder="dd/mm/yyyy" SIZE='60' maxlength='10'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormEstadoNascimento() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="estado_nascimento">Estado Nascimento:</label>
                            <select class='form-control' name="estado_nascimento" id="estado_nascimento" required>
                                <option class='form_campos' value='' selected>Selecione um Estado</option>
                                <?php CarregarOptionsAC::estadosNascimento($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormCidadeNascimento() == true) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="cidade_nascimento">Cidade Nascimento:</label>
                            <select class='form-control' name="cidade_nascimento" id="cidade_nascimento" required>
                                <?php CarregarOptionsAC::cidadesNascimento($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>
                </div>

                <!-- CPF + E-mail -->
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="cpf" id="textoCpf" class="show">CPF: <span style="color:red;">*</span></label>
                        <label for="cpf" id="textoPassaporte" class="hidden">CPF/Passaporte: <span style="color:red;">*</span></label>
                        <input type="text" id="cpf" name="cpf" value="" class="form-control" placeholder="Informe seu CPF" required SIZE='60' maxlength='11' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="e_mail">E-mail: <span style="color:red;">*</span></label>
                        <input type="email" id="e_mail" name="e_mail" value="<?php echo $e_mail_valor; ?>" class="form-control" placeholder="Informe seu e-mail" required SIZE='60' maxlength='255' onBlur='trimEmail(this);'>
                    </div>
                    <div class="form-group col-sm-4" style="padding-left: 0px;">
                        <label for="re_e_mail">Confirme seu E-mail: <span style="color:red;">*</span></label>
                        <input type="email" id="re_e_mail" name="re_e_mail" value="" class="form-control" placeholder="Confirme seu e-mail" required SIZE='60' maxlength='255' onBlur='trimEmail(this);'>
                    </div>
                    <?php if ($objEvConf->getFormMelhorEmail() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="e_mail_2">E-mail 2:</label>
                            <input type="email" id="e_mail_2" name="e_mail_2" value="" class="form-control" placeholder="Informe um segundo e-mail" SIZE='60' maxlength='255' onBlur='trimEmail(this);'>
                        </div>
                    <?php } ?>
                </div>

                <!-- Telefone -->
                <div class="form-group col-sm-12" style="margin-bottom:0px;margin-top:0px;padding-left: 0px;">
                    <div class="form-group col-sm-1" style="padding-left: 0px;">
                        <label for="fone_contato_ddd">DDD: <span style="color:red;">*</span></label>
                        <input type="text" id="fone_contato_ddd" name="fone_contato_ddd" value="" class="form-control" SIZE='60' maxlength='2' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));' required>
                    </div>
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="fone_contato">Telefone: <span style="color:red;">*</span></label>
                        <input type="text" id="fone_contato" name="fone_contato" value="" class="form-control" SIZE='60' maxlength='14' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));' required>
                    </div>
                </div>

                <!-- RG -->
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <?php if ($objEvConf->getFormPis() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="pis">PIS:</label>
                            <input type="text" id="pis" name="pis" value="" class="form-control" placeholder="Informe o número de seu PIS" required SIZE='11' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRg() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="rg">RG:</label>
                            <input type="text" id="rg" name="rg" value="" class="form-control" placeholder="Informe o número de seu RG" required SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isRg(getKey(event));'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRgDataEmissao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="rg_data_emissao">RG Data de Emissão:</label>
                            <input type="date" id="rg_data_emissao" name="rg_data_emissao" value="" class="form-control" placeholder="dd/mm/yyyy" required SIZE='60' maxlength='10'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRgEstadoEmissao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="rg_estado_emissao">RG Estado Emissão:</label>
                            <select class="form-control" name="rg_estado_emissao" id="rg_estado_emissao" required>
                                <option class='form_campos' value='' selected>Selecione um Estado</option>
                                <?php CarregarOptionsAC::rgEstadoEmissao($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRgCidadeEmissao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="rg_cidade_emissao">RG Cidade Emissão:</label>
                            <select class="form-control" name="rg_cidade_emissao" id="rg_cidade_emissao" required>
                                <?php CarregarOptionsAC::rgCidadeEmissao($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormRgOrgaoExpedidor() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="orgao_expedidor">RG Órgão Expedidor:</label>
                            <input type="text" id="orgao_expedidor" name="orgao_expedidor" value="" class="form-control" placeholder="Órgão expedidor de seu RG" required SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                </div>

                <!-- Endereço -->
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label>Endereço:</label>
                    </div>
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="cep">Cep:</label>
                        <input type="text" id="cep" name="cep" value="" class="form-control" placeholder="Cep" SIZE='60' maxlength='9' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                    </div>
                    <div class="form-group col-sm-6" style="padding-left: 0px;">
                        <label for="endereco">Rua:</label>
                        <input type="text" id="endereco" name="endereco" value="" class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);'>
                    </div>
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="numero">Número:</label>
                        <input type="text" id="numero" name="numero" value="" class="form-control" placeholder="Número" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
                    </div>
                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="complemento">Complemento:</label>
                        <input type="text" id="complemento" name="complemento" value="" class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);'>
                    </div>
                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="bairro">Bairro:</label>
                        <input type="text" id="bairro" name="bairro" value="" class="form-control" placeholder="Bairro" SIZE='60' maxlength='255' onBlur='trim(this);'>
                    </div>
                    <div class="form-group col-sm-2" style="padding-left: 0px;">
                        <label for="estado">Estado:</label>
                        <select class='form-control' name='estado' id='estado'>
                            <option class='form_campos' value='' selected>Selecione um Estado</option>
                            <?php CarregarOptionsAC::estados($fws_, $debug, false); ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-3" style="padding-left: 0px;">
                        <label for="cidade">Cidade:</label>
                        <select class='form-control' name='cidade' id='cidade'>
                            <option class='form_campos' value='' selected>Selecione uma Cidade</option>
                        </select>
                    </div>
                </div>

                <!-- Escolaridade / Graduação -->
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <?php if ($objEvConf->getFormGrauEscolaridade() == true) { ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label>Outros:</label>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="grau_escolaridade">Grau de Escolaridade:</label>
                            <select class='form-control' name='grau_escolaridade' id='grau_escolaridade' required>
                                <?php CarregarOptionsAC::grauEscolaridade($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormGraduacao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="graduacao">Graduação:</label>
                            <input type="text" id="graduacao" name="graduacao" value="" class="form-control" placeholder="Informe sua graduação" required SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormGraduacaoAno() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="graduacao_ano">Ano da Graduação:</label>
                            <input type="text" id="graduacao_ano" name="graduacao_ano" value="" class="form-control" required SIZE='25' maxlength='4' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormGraduacaoIes() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="graduacao_ies">IES da Graduação:</label>
                            <input type="text" id="graduacao_ies" name="graduacao_ies" value="" class="form-control" placeholder="Informe IES da Graduação" required SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormPosGraduacao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="pos_graduacao">Pós-Graduação:</label>
                            <input type="text" id="pos_graduacao" name="pos_graduacao" value="" class="form-control" placeholder="Informe sua pós-graduação" SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormPosGraduacaoAno() == true) { ?>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="pos_graduacao_ano">Ano da Pós-Graduação:</label>
                            <input type="text" id="pos_graduacao_ano" name="pos_graduacao_ano" value="" class="form-control" SIZE='25' maxlength='4' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormPosGraduacaoIes() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="pos_graduacao_ies">IES da Pós-Graduação:</label>
                            <input type="text" id="pos_graduacao_ies" name="pos_graduacao_ies" value="" class="form-control" placeholder="Informe IES da Pós-Graduação" SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                </div>

                <!-- Profissão / Empresa -->
                <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                    <?php if ($objEvConf->getFormEmpresaAtual() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="empresa_atual">Empresa Atual:</label>
                            <input type="text" id="empresa_atual" name="empresa_atual" value="" class="form-control" placeholder="Informe empresa atual" required SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormCargoAtual() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="cargo_atual">Cargo Atual:</label>
                            <input type="text" id="cargo_atual" name="cargo_atual" value="" class="form-control" placeholder="Cargo Atual" required SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getDadosPessoaisTipoInstituicao() == true) { ?>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="tipo_instituicao">Tipo da Instituição:</label>
                            <select class='form-control' name='tipo_instituicao' id='tipo_instituicao' required>
                                <option class='form_campos' value='' selected>Selecione um Tipo de Instituição</option>
                                <?php CarregarOptionsAC::tipoInstituicoes($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getDadosPessoaisInstituicao() == true) { ?>
                        <div class="form-group col-sm-5" style="padding-left: 0px;">
                            <label for="instituicao">Nome da Instituição/Empresa:</label>
                            <input type="text" id="instituicao" name="instituicao" value="" class="form-control" placeholder="Nome da Instituição/Empresa" required SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getDadosPessoaisProfissao() == true) { ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="profissao_atuacao">Profissão/Atividade:</label>
                            <select class='form-control' name='profissao_atuacao' id='profissao_atuacao' required>
                                <option class='form_campos' value='' selected>Selecione uma Profissão/Atividade</option>
                                <?php CarregarOptionsAC::profissoes($fws_, $debug); ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormComoFicouSabendo() == true) { ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="como_ficou_sabendo">Como ficou sabendo do Curso?</label>
                            <input type="text" id="como_ficou_sabendo" name="como_ficou_sabendo" value="" class="form-control" placeholder="Como ficou sabendo" required SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                    <?php if ($objEvConf->getFormQualMotivoInscreveu() == true) { ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="qual_motivo_inscreveu">Por qual motivo você decidiu se inscrever no Curso?</label>
                            <input type="text" id="qual_motivo_inscreveu" name="qual_motivo_inscreveu" value="" class="form-control" placeholder="Informe por qual motivo você decidiu se inscrever no curso" required SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                    <?php } ?>
                </div>

                <!-- Recibo Institucional -->
                <?php if ($objEvConf->getEmitirReciboTerceiro() == true) { ?>
                    <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <input type='checkbox' id='institucional' name='institucional' value='1' />
                            &nbsp;Deseja emitir Pagamento/Recibo em nome de sua Instituição/Empresa ou terceiro?<br />
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <div class="form-recibo form-group col-sm-7" style="padding-left: 0px;">
                                <label for="nome_recibo">Nome de quem efetuará o pagamento/Instituição:</label>
                                <input type="text" id="nome_recibo" name="nome_recibo" value="" class="form-control" disabled SIZE='60' maxlength='255' onBlur='trim(this);'>
                            </div>
                            <div class="form-recibo form-group col-sm-3" style="padding-left: 0px;">
                                <label for="cpf_recibo">CPF/CNPJ:</label>
                                <input type="text" id="cpf_recibo" name="cpf_recibo" value="" class="form-control" disabled SIZE='60' maxlength='18' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!-- Botões -->
                <div class="form-group col-sm-12" style="padding-left: 0px;margin-top:20px;">
                    <button id="btnConfirmar" class='sympla-btn-submit' type="submit">Confirmar Inscrição</button>
                    &nbsp;&nbsp;
                    <a href="<?php echo WB_URL_VIEW; ?>?content=view/inscricao/ticket_selecao.php" class="btn btn-default">Voltar</a>
                </div>
                <br><br><br>
            </form>
        </div>
    </div>
</div>

<script language="JavaScript1.2" src="resources/js/default.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // Estrangeiro sem CPF
    $('#estrangeiro').change(function() {
        if (this.checked) {
            $('#textoCpf').addClass('hidden');
            $('#textoPassaporte').removeClass('hidden');
            $('#cpf').removeAttr('required');
            $('#cpf').attr("placeholder", "Cpf/Passaporte");
        } else {
            $('#textoCpf').removeClass('hidden');
            $('#textoPassaporte').addClass('hidden');
            $('#cpf').attr("required", "true");
            $('#cpf').attr("placeholder", "Informe seu CPF");
        }
    });

    // Carregar cidades por estado
    $("#estado").change(function() {
        carregarCidades(this.value);
    });

    // Busca CEP
    $("#cep").blur(function() {
        buscaCep(this.value);
    });

    // Estado nascimento -> Cidade nascimento
    $("#estado_nascimento").change(function() {
        carregarCidadeNascimento(this.value);
    });

    // RG Estado -> RG Cidade
    $("#rg_estado_emissao").change(function() {
        carregarRgCidadeEmissao(this.value);
    });

    // Institucional toggle
    $('#institucional').change(function() {
        if (this.checked) {
            $('#nome_recibo').removeAttr("disabled");
            $('#cpf_recibo').removeAttr("disabled");
            $('.form-recibo').show();
        } else {
            $('#nome_recibo').attr("disabled", "true");
            $('#cpf_recibo').attr("disabled", "true");
            $('.form-recibo').hide();
        }
    });
    $('.form-recibo').hide();

    // Não aceitar termos
    var termoNao = document.getElementById("nao_aceitou_termos");
    if (termoNao) {
        termoNao.addEventListener("click", function() {
            var confirmacao = confirm("Para sair sem aceitar os termos clique em OK?");
            if (confirmacao) {
                window.location.href = "<?php echo WB_URL_VIEW ?>";
            }
        });
    }

    // Desabilitar botão no submit para evitar duplo clique
    $('form[name="form"]').on('submit', function() {
        var btn = $('#btnConfirmar');
        if (btn.prop('disabled')) return false;
        btn.prop('disabled', true).text('Processando...');
        // Re-habilitar após 10s caso algo dê errado
        setTimeout(function() { btn.prop('disabled', false).text('Confirmar Inscrição'); }, 10000);
    });
});

function buscaCep(cep) {
    cep = cep.replace(/\D/g, '');
    if (cep != "") {
        var validacep = /^[0-9]{8}$/;
        if (validacep.test(cep)) {
            $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                if (!("erro" in dados)) {
                    $("#endereco").val(dados.logradouro);
                    $("#bairro").val(dados.bairro);
                    $("#estado").val(dados.uf);
                    carregarCidades(dados.uf);
                    $("#cidade").val(dados.localidade);
                    $("#numero").focus();
                } else {
                    alert("CEP não encontrado.");
                }
            });
        } else {
            alert("Formato de CEP inválido.");
        }
    }
}

function carregarCidades(cod) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>',
        data: ({ 'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1' }),
        dataType: "html",
        async: false,
        cache: false,
        success: function(data) { $("#cidade").html(data); },
        error: function() { alert("Não foi possível obter informações de cidades."); }
    });
}

function carregarCidadeNascimento(cod) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>',
        data: ({ 'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1' }),
        dataType: "html",
        async: false,
        cache: false,
        success: function(data) { $("#cidade_nascimento").html(data); },
        error: function() { alert("Não foi possível obter informações."); }
    });
}

function carregarRgCidadeEmissao(cod) {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>',
        data: ({ 'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1' }),
        dataType: "html",
        async: false,
        cache: false,
        success: function(data) { $("#rg_cidade_emissao").html(data); },
        error: function() { alert("Não foi possível obter informações."); }
    });
}

function criticarSympla() {
    if (document.form.nome_inscrito.value.trim() == "") {
        alert("O campo Nome Completo não está preenchido!");
        document.form.nome_inscrito.focus();
        return false;
    }
    // CPF (se não estrangeiro)
    if (document.form.estrangeiro && document.form.estrangeiro.checked) {
        // Estrangeiro - não validar CPF
    } else {
        if (typeof isCpf === 'function' && !isCpf(document.form.cpf.value)) {
            alert("O CPF não foi preenchido ou não possui um formato válido.");
            document.form.cpf.focus();
            return false;
        }
        if (document.form.cpf.value.trim() == "") {
            alert("O campo CPF não está preenchido!");
            document.form.cpf.focus();
            return false;
        }
    }
    // E-mail
    if (typeof isEmail === 'function' && !isEmail(document.form.e_mail.value)) {
        alert("O email deve ser preenchido no formato correto (usuário@dominio).");
        document.form.e_mail.focus();
        return false;
    }
    if (typeof isEmail === 'function' && !isEmail(document.form.re_e_mail.value)) {
        alert("O email de confirmação deve ser preenchido no formato correto.");
        document.form.re_e_mail.focus();
        return false;
    }
    if ($("#re_e_mail").val() != $("#e_mail").val()) {
        alert("O email de confirmação é diferente do e-mail informado.");
        $("#re_e_mail").focus();
        return false;
    }
    // Telefone
    if (document.form.fone_contato_ddd.value.trim() == "") {
        alert("O campo DDD do telefone não está preenchido!");
        document.form.fone_contato_ddd.focus();
        return false;
    }
    if (document.form.fone_contato.value.trim() == "") {
        alert("O campo Telefone não está preenchido!");
        document.form.fone_contato.focus();
        return false;
    }

    return true;
}
</script>
