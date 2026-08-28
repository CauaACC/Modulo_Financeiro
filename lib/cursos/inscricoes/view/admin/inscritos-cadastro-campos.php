<?php
/*
 * Campos da ficha do inscrito no admin. Usado por inscritos-cadastro-alterar.php
 * e por inscritos-cadastro-incluir.php.
 *
 * Layout: o mesmo desenho da PREVIA DA NFS-e (.nfse-grupo, em
 * inscritos-cadastro-ok-nfse-scripts.php) — caixa por assunto, titulo em caixa
 * alta cinza, rotulos pequenos e campos compactos. Antes eram ~30 blocos
 * col-sm-* soltos em sequencia, sem nenhum agrupamento: o Estado do endereco
 * caia ao lado do Fone de contato e o RG ficava a tres telas do CPF.
 *
 * As secoes seguem a MESMA ordem da aba Dados Pessoais
 * (inscritos-cadastro-ok-dados-pessoais.php), para conferir e alterar serem a
 * mesma leitura.
 *
 * Nenhum name/id/handler mudou — so o markup em volta.
 */
$objInscrito = new Inscrito();
if($COD_INSCRITO){
    $objInscrito->setCodInscrito($COD_INSCRITO);
    $objInscrito->getInscritoById($debug);
}

#require_once(dirname(__FILE__)."/../../include/config.php");
$FWA_Option_Dia    = FWA_Option_Dia();
$FWA_Option_Mes    = FWA_Option_Mes();
$FWA_Option_Ano    = FWA_Option_Ano(3);
$FWA_Option_Hora   = FWA_Option_Hora();
$FWA_Option_Minuto = FWA_Option_Minuto();

/*
#echo $cod_atuacao_convidado."<br>";
#echo $nome_atuacao_convidado."<br>";
If($cod_atuacao_convidado!=""){
  $OPTIONS_Atuacao = "<option class='form-control' value='".cod_atuacao_convidado."' selected>".$nome_atuacao_convidado."--</option>";
}Else{
  $OPTIONS_Atuacao = "<option class='form-control' value='-1' selected>Selecione um Estado</option>";
}
*/
#include_once($_SERVER["DOCUMENT_ROOT"]."/inscritos-cadastro-select-atuacao.php");
?>
        <?php /* As telas de alterar/incluir nao passam pelo inscritos-cadastro-ok.php,
                 que e quem normalmente carrega este CSS. */ ?>
        <link href="resources/css/cartao-listagem.css" rel="stylesheet">

        <input type='hidden' name='COD_EVENTO' value='<?php echo $COD_EVENTO?>'>

        <div id="avisoCertificado" class="alert alert-warning alert-dismissible" style="display:none; margin:10px 0;">
            <strong>Cadastro somente para certificado.</strong> CPF e e-mail n&atilde;o s&atilde;o obrigat&oacute;rios. Este inscrito n&atilde;o ter&aacute; acesso ao login de inscrito.
        </div>

        <!-- ================= IDENTIFICACAO ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Identificação</div>
            <div class="linha">
                <div class="dp-c6">
                    <label for="nome">Nome</label>
                    <input class='form-control' type='text' name='nome_inscrito' value='<?php echo $nome_inscrito?>'
                      required  size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                </div>
                <div class="dp-c3">
                    <label for="nome_apelido">Nome p/ Crachá</label>
                    <input class='form-control' type='text' name='nome_apelido' value='<?php echo $objInscrito->getNomeApelido()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c3">
                    <label for="cod_perfil">Perfil de inscrição</label>
                    <select class='form-control' name='cod_perfil' required>
                        <option value=''>Selecione um perfil</option>
                        <?php CarregarOptionsAC::perfisCadAdmin($fws_, $debug);?>
                    </select>
                </div>
            </div>
            <div class="linha">
                <div class="dp-c2">
                    <label for="sexo">Sexo</label>
                    <select class='form-control' name='sexo' id='sexo' placeholder='sexo' required autofocus>
                        <?php
                        $fws_['sexo'] = @$objInscrito->getSexo();
                        CarregarOptionsAC::sexo($fws_, $debug);
                        ?>
                    </select>
                </div>
                <div class="dp-c3">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input class='form-control' type='text' name='data_nascimento' value='<?php echo formatDateView($objInscrito->getDataNascimento());?>'
                       SIZE='20' maxlength='10'  onKeyUp='dateMask(this.value, this);' onkeypress='javascript: return isNum(window.event.keyCode);'><!-- onkeypress='return isDateFormat(window.event.keyCode);'>-->
                </div>
                <div class="dp-c4">
                    <label for="cidade_nascimento">Cidade Nascimento</label>
                    <select class='form-control' name='cidade_nascimento' id='cidade_nascimento' placeholder="Cidade Nascimento" autofocus>
                        <?php
                            $fws_['cidade'] = "";
                            $fws_['cidade'] = $objInscrito->getCidadeNascimento();
                            CarregarOptionsAC::cidades($fws_, $debug, false);
                        ?>
                    </select>
                </div>
                <div class="dp-c3">
                    <label for="estado_nascimento">Estado Nascimento</label>
                    <select class='form-control' name='estado_nascimento' id='estado_nascimento' placeholder="Estado Nascimento"  autofocus>
                        <option class='form-control' value='' selected>Selecione um Estado</option>
                        <?php
                        $fws_['estado'] = "";
                        $fws_['estado'] = $objInscrito->getEstadoNascimento();
                        CarregarOptionsAC::estados($fws_, $debug, false);
                        ?>
                    </select>
                </div>
            </div>
            <div class="linha">
                <div class="dp-c3">
                    <label for="raca">Raça</label>
                    <select class='form-control' name='raca' id='raca' placeholder="Raca"  autofocus>
                        <option class='form-control' value='' selected>Selecione sua Raça</option>
                        <?php
                        $fws_['raca'] = "";
                        $fws_['raca'] = $objInscrito->getRaca();
                        CarregarOptionsAC::raca($fws_, $debug, false);
                        ?>
                    </select>
                </div>
                <div class="dp-c3">
                    <label for="estado_civil">Estado Civil</label>
                    <select class='form-control' name='estado_civil' id='estado_civil' placeholder="Estado Civil" autofocus>
                        <?php
                        $fws_['estado_civil'] = "";
                        $fws_['estado_civil'] = $objInscrito->getEstadoCivil();
                        CarregarOptionsAC::EstadoCivil($fws_, $debug, false);
                        ?>
                    </select>
                </div>
                <?php /** @todo o campo nis e nome da mãe, pode ser exigo apenas para um determina perfil para casos de requerimentos */ ?>
                <?php if ($objEvConf->getFormNomeMae() == true  and $objInscrito->getCodPerfil()==560) { ?>
                    <div class="dp-c3">
                        <label for="nome_mae">Nome da Mãe</label>
                        <input type="text" id="nome_mae" name="nome_mae" value="<?php echo $objInscrito->getNomeMae() ?>" class="form-control" placeholder="Nome da Mãe" autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                    </div>
                <?php } ?>
                <?php if ($objEvConf->getFormNomePai() == true  and $objInscrito->getCodPerfil()==560) { ?>
                    <div class="dp-c3">
                        <label for="nome_pai">Nome do Pai</label>
                        <input type="text" id="nome_Pai" name="nome_pai" value="<?php echo $objInscrito->getNomePai() ?>" class="form-control" placeholder="Nome do Pai" autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='60' maxlength='255' onBlur='trim(this);' >
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- ================= SITUACAO ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Situação da inscrição</div>
            <div class="linha">
                <div class="dp-c3">
                    <label for="status">Status</label>
                    <select class='form-control' name='status'>
                        <option <?php echo verifItemSelected($objInscrito->getStatus(), 'ATIVADA')  ?> value='ATIVADA'>ATIVADA</option>
                        <option <?php echo verifItemSelected($objInscrito->getStatus(), 'DESATIVA') ?> value='DESATIVA'>DESATIVA</option>
                        <option <?php echo verifItemSelected($objInscrito->getStatus(), 'EXCLUÍDA') ?> value='EXCLUÍDA'>EXCLUÍDA</option>
                    </select>
                </div>
                <div class="dp-c3">
                    <label for="origem_cadastro">Origem Cadastro</label>
                    <select class='form-control' name='origem_cadastro' id='origem_cadastro'>
                        <option <?php echo verifItemSelected($objInscrito->getOrigemCadastro(), 'INSCRICAO')  ?> value='INSCRICAO'>INSCRICAO</option>
                        <option <?php echo verifItemSelected($objInscrito->getOrigemCadastro(), 'CERTIFICADO') ?> value='CERTIFICADO'>CERTIFICADO</option>
                        <option <?php echo verifItemSelected($objInscrito->getOrigemCadastro(), 'VINCULO_LOGIN') ?> value='VINCULO_LOGIN'>VINCULO_LOGIN</option>
                    </select>
                </div>
                <div class="dp-c3">
                    <label for="status_processo">Status Processo</label>
                    <select class='form-control' name='status_processo'>
                        <option <?php echo verifItemSelected($objInscrito->getStatusProcesso(), 'INSCRITO')  ?> value='INSCRITO'>INSCRITO</option>
                        <option <?php echo verifItemSelected($objInscrito->getStatusProcesso(), 'CONVOCADO')  ?> value='CONVOCADO'>CONVOCADO</option>
                        <option <?php echo verifItemSelected($objInscrito->getStatusProcesso(), 'PARTICIPANDO') ?> value='PARTICIPANDO'>PARTICIPANDO</option>
                        <option <?php echo verifItemSelected($objInscrito->getStatusProcesso(), 'DESISTENTE') ?> value='DESISTENTE'>DESISTENTE</option>
                        <option <?php echo verifItemSelected($objInscrito->getStatusProcesso(), 'CANCELADO') ?> value='CANCELADO'>CANCELADO</option>
                        <option <?php echo verifItemSelected($objInscrito->getStatusProcesso(), 'SUSPENSO') ?> value='SUSPENSO'>SUSPENSO</option>
                        <option <?php echo verifItemSelected($objInscrito->getStatusProcesso(), '') ?> value=''>NENHUM</option>
                    </select>
                </div>
                <div class="dp-c3">
                    <label for="flg_presenca_inscrito">Presença</label>
                    <select class="form-control" id="flg_presenca_inscrito" name="flg_presenca_inscrito">
                        <option value=""></option>
                        <?php echo CarregarOptionsAC::presenca($objInscrito->getFlgPresencaInscrito(), $debug=false)?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ================= DOCUMENTOS ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Documentos</div>
            <div class="linha">
                <div class="dp-c3">
                    <label for="cpf">CPF</label>
                    <input class='form-control' type='text' name='cpf' value='<?php echo $cpf?>'
                      <?php //echo $liberarCadastroSemCpf? "" : "required";?> size='20' maxlength='18' onkeypress='javascript: return isNum(window.event.keyCode);'>
                </div>
                <div class="dp-c3">
                    <label for="rg">RG</label>
                    <input class='form-control' type='text' name='rg' value='<?php echo $rg?>'
                       size='20' maxlength='15' onkeypress='javascript: return isNum(window.event.keyCode);'>
                </div>
                <div class="dp-c3">
                    <label for="rg_data_emissao">RG Data de Emissão</label>
                    <input class='form-control' type='text' name='rg_data_emissao' value='<?php echo $objInscrito->getRgDataEmissao()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c3">
                    <label for="rg_orgao_expedidor">RG Órgão Expedidor</label>
                    <input class='form-control' type='text' name='rg_orgao_expedidor' value='<?php echo $objInscrito->getOrgaoExpedidor()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
            </div>
            <div class="linha">
                <div class="dp-c3">
                    <label for="rg_cidade_emissao">RG Cidade Emissão</label>
                    <select class='form-control' name='rg_cidade_emissao' id='rg_cidade_emissao' placeholder="Cidade Emissão RG" autofocus>
                        <?php
                            $fws_['cidade'] = "";
                            $fws_['cidade'] = $objInscrito->getRgCidadeEmissao();
                            CarregarOptionsAC::cidades($fws_, $debug, false);
                        ?>
                    </select>
                </div>
                <div class="dp-c3">
                    <label for="rg_estado_emissao">RG Estado Emissão</label>
<!--                <input class='form-control' type='text' name='rg_estado_emissao' value='<?php echo $objInscrito->getRgEstadoEmissao()?>'
                  size='60' maxlength='255' value=' ' onBlur='trim(this);'>-->
                    <select class='form-control' name='rg_estado_emissao' id='rg_estado_emissao' placeholder="Estado Emissão RG"  autofocus>
                        <option class='form-control' value='' selected>Selecione um Estado</option>
                        <?php
                        $fws_['estado'] = "";
                        $fws_['estado'] = $objInscrito->getRgEstadoEmissao();
                        CarregarOptionsAC::estados($fws_, $debug, false);
                        ?>
                    </select>
                </div>
                <div class="dp-c3">
                    <label for="pis">PIS</label>
                    <input class='form-control' type='text' name='pis' value='<?php echo $objInscrito->getPis() ?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <?php if ($objEvConf->getFormNis() == true and $objInscrito->getCodPerfil()==560) { ?>
                    <div class="dp-c3">
                        <label for="nis">NIS</label>
                        <input type="text" id="nis" name="nis" value="<?php echo $objInscrito->getNis() ?>" class="form-control" placeholder="Informe o número de seu NIS"  autofocus oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');" SIZE='11' maxlength='11'                           onBlur="validarNIS(this); trim(this);" onkeypress='javascript: return isNum(getKey(event));'>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- ================= CONTATO ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Contato</div>
            <div class="linha">
                <div class="dp-c6">
                    <label for="e_mail">E-mail</label>
                    <input class='form-control' type='text' name='e_mail' value='<?php echo $objInscrito->getEMail()?>' required
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c6">
                    <label for="e_mail_2">E-mail 2</label>
                    <input class='form-control' type='text' name='e_mail_2' value='<?php echo $objInscrito->getEMail2()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
            </div>
            <div class="linha">
                <div class="dp-c2">
                    <label for="fone_contato_ddd">DDD</label>
                    <input type="text" id="fone_contato_ddd" name="fone_contato_ddd" value="<?php echo $objInscrito->getFoneContatoDdd() ?>"
                           class="form-control" SIZE='60' maxlength='2' onBlur='trim(this);' >
                </div>
                <div class="dp-c3">
                    <label for="fone_contato">Fone Contato</label>
                    <input type="text" id="fone_contato" name="fone_contato" value="<?php echo $objInscrito->getFoneContato() ?>"
                           class="form-control" SIZE='60' maxlength='14' onBlur='trim(this);'>
                </div>
                <div class="dp-c3">
                    <label for="fone_residencial">Telefone Res</label>
                    <input class='form-control' TYPE='text' NAME='fone_residencial' value='<?php echo $objInscrito->getFoneResidencial()?>'
                           SIZE='20' maxlength='20'  onBlur='trim(this);' onkeypress='javascript: return isTelefone(window.event.keyCode);'>
                </div>
                <div class="dp-c2">
                    <label for="fone_celular">Telefone Cel</label>
                    <input class='form-control' TYPE='text' NAME='fone_celular' value='<?php echo $objInscrito->getFoneCelular()?>'
                           SIZE='20' maxlength='20'  onBlur='trim(this);' onkeypress='javascript: return isTelefone(window.event.keyCode);'>
                </div>
                <div class="dp-c2">
                    <label for="fone_comercial">Telefone Com</label>
                    <input class='form-control' TYPE='text' NAME='fone_comercial' value='<?php echo $objInscrito->getFoneComercial()?>'
                           SIZE='20' maxlength='20'  onBlur='trim(this);' onkeypress='javascript: return isTelefone(window.event.keyCode);'>
                </div>
            </div>
        </div>

        <!-- ================= ENDERECO ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Endereço</div>
            <div class="linha">
                <div class="dp-c2">
                    <?php /* Mesmo aviso de progresso do CEP do tomador na emissao de
                             NFS-e: sem ele a busca no ViaCEP e invisivel e parece que o
                             campo nao fez nada. */ ?>
                    <label for="cep">Cep <span id="cepStatus" style="font-weight:normal; color:#1976d2;"></span></label>
                    <input type="text" id="cep" name="cep" value="<?php echo  $objInscrito->getCep() ?>"
                           class="form-control" placeholder="Cep"  autofocus
                            SIZE='60' maxlength='9' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                </div>
                <div class="dp-c6">
                    <label for="endereco">Rua</label>
                    <input type="text" id="endereco" name="endereco" value="<?php echo  $objInscrito->getEndereco() ?>"
                           class="form-control" placeholder=""  autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="dp-c2">
                    <label for="numero">Número</label>
                    <input type="text" id="numero" name="numero" value="<?php echo  $objInscrito->getNumero() ?>"
                           class="form-control" placeholder="Número"  autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="dp-c2">
                    <label for="complemento">Complemento</label>
                    <input type="text" id="complemento" name="complemento" value="<?php echo  $objInscrito->getComplemento() ?>"
                           class="form-control"
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
            </div>
            <div class="linha">
                <div class="dp-c4">
                    <label for="bairro">Bairro</label>
                    <input type="text" id="bairro" name="bairro" value="<?php echo  $objInscrito->getBairro() ?>"
                           class="form-control" placeholder="Bairro"  autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="dp-c2">
                    <label for="estado">Estado</label>
                    <select class='form-control' name='estado' id='estado' placeholder="Estado"  autofocus>
                        <option class='form-control' value='' selected>Selecione um Estado</option>
                        <?php
                        $fws_['estado'] = $objInscrito->getEstado();
                        CarregarOptionsAC::estados($fws_, $debug, false);
                        ?>
                    </select>
                </div>
                <div class="dp-c6">
                    <label for="cidade">Cidade</label>
                    <select class='form-control' name='cidade' id='cidade' placeholder="Cidade"  autofocus>
                        <option class='form-control' value='' selected>Selecione um Cidade</option>
                        <?php
                        $fws_['cidade'] = $objInscrito->getCidade();
                        CarregarOptionsAC::cidades($fws_, $debug, false);
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ================= FORMACAO ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Formação</div>
            <div class="linha">
                <div class="dp-c3">
                    <label for="grau_escolaridade">Grau de Escolaridade</label>
                    <input class='form-control' type='text' name='grau_escolaridade' value='<?php echo $objInscrito->getGrauEscolaridade()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c3">
                    <label for="graduacao">Graduação</label>
                    <input class='form-control' type='text' name='graduacao' value='<?php echo $objInscrito->getGraduacao()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c2">
                    <label for="graduacao_ano">Ano da Graduação</label>
                    <input class='form-control' type='text' name='graduacao_ano' value='<?php echo $objInscrito->getGraduacaoano()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c4">
                    <label for="graduacao_ies">IES da Graduação</label>
                    <input class='form-control' type='text' name='graduacao_ies' value='<?php echo $objInscrito->getGraduacaoIes()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
            </div>
            <div class="linha">
                <div class="dp-c4">
                    <label for="pos_graduacao">Pós-Graduação</label>
                    <input class='form-control' type='text' name='pos_graduacao' value='<?php echo $objInscrito->getPosGraduacao()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c2">
                    <label for="pos_graduacao_ano">Ano da Pós-Graduação</label>
                    <input class='form-control' type='text' name='pos_graduacao_ano' value='<?php echo $objInscrito->getPosGraduacaoAno()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c6">
                    <label for="pos_graduacao_ies">IES da Pós-Graduação</label>
                    <input class='form-control' type='text' name='pos_graduacao_ies' value='<?php echo $objInscrito->getPosGraduacaoIes()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
            </div>
        </div>

        <!-- ================= PROFISSIONAL ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Profissional</div>
            <div class="linha">
                <div class="dp-c4">
                    <label for="tipo_instituicao">Tipo da Instituição</label>
                    <select class='form-control' name='tipo_instituicao' id='tipo_instituicao' placeholder="Tipo da instituição" required autofocus>
                        <option class='form_campos' value=''>Selecione um Tipo de Instituição</option>
                        <?php
                        CarregarOptionsAC::tipoInstituicoes($fws_, $debug);
                        ?>
                    </select>
                </div>
                <div class="dp-c4">
                    <label for="instituicao">Nome de Instituição/Empresa</label>
                    <input type="text" id="instituicao" name="instituicao" value="<?php echo $objInscrito->getInstituicao() ?>"
                           class="form-control" placeholder="Nome da Instituição/Empresa"
                           oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');"
                            SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
                </div>
                <div class="dp-c4">
                    <label for="profissao">Profissão/Atividade</label>
                    <!--
                    <input type="text" id="instituicao" name="profissao" value="<?php echo $objInscrito->getDadosPessoaisProfissao() ?>"
                           class="form-control" placeholder="Nome de sua profissão/atividade" required autofocus
                           oninvalid="setMessageDefault('oninvalid');" onchange="setMessageDefault('onchange');"
                            SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
                    -->
                    <select class='form-control' name='profissao' id='profissao' placeholder="Nome de sua profissão/atividade">
                        <option class='form_campos' value=''>Selecione uma Profissão/Atividade</option>
                        <?php
                        CarregarOptionsAC::profissoes($fws_, $debug);
                        ?>
                    </select>
                </div>
            </div>
            <div class="linha">
                <div class="dp-c4">
                    <label for="empresa_atual">Empresa Atual</label>
                    <input class='form-control' type='text' name='empresa_atual' value='<?php echo $objInscrito->getEmpresaAtual()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c4">
                    <label for="cargo_atual">Cargo Atual</label>
                    <input class='form-control' type='text' name='cargo_atual' value='<?php echo $objInscrito->getCargoAtual()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
                <div class="dp-c4">
                    <label for="pretensao_carreira">Pretensão na Carreira</label>
                    <input class='form-control' type='text' name='pretensao_carreira' value='<?php echo $objInscrito->getPretensaoCarreira()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
            </div>
        </div>

        <!-- ================= INSCRICAO ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Sobre a inscrição</div>
            <div class="linha">
                <div class="dp-c12">
                    <label for="como_ficou_sabendo">Como ficou sabendo do Curso?</label>
                    <input class='form-control' type='text' name='como_ficou_sabendo' value='<?php echo $objInscrito->getComoFicouSabendo()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
            </div>
            <div class="linha">
                <div class="dp-c12">
                    <label for="qual_motivo_inscreveu">Por qual motivo você decidiu se inscrever no Curso?</label>
                    <input class='form-control' type='text' name='qual_motivo_inscreveu' value='<?php echo $objInscrito->getQualMotivoInscreveu()?>'
                      size='60' maxlength='255' value=' ' onBlur='trim(this);'>
                </div>
            </div>
            <div class="linha">
                <div class="dp-c12">
                    <label for="tipo_inscricao">Tipo da Inscrição</label>
                    <?php
                    // Sao radios, nao um <select> — havia um </select> orfao aqui,
                    // sobra do markup antigo.
                    $fws_['tipo_inscricao'] = $tipo_inscricao;
                    CarregarOptionsAC::tipoInscricao($fws_, $debug, false);
                    ?>
                </div>
            </div>
        </div>

        <?php if ($objEvConf->getFormDadosBancarios()==true){ ?>
        <!-- ================= DADOS BANCARIOS ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Dados Bancários</div>
            <div class="linha">
                <div class="dp-c3">
                    <label for="banco">Banco</label>
<!--                <input type="text" id="db_banco" name="db_banco" value="<?php echo $objInscrito->getDbBanco() ?>"
                       class="form-control" placeholder="Banco" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                        SIZE='60' maxlength='255' onBlur='trim(this);'>-->
                    <select class='form-control' name='db_banco' id='db_banco' placeholder="Selecione um banco" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus>
                        <?php
                        $fws_['db_banco'] = @$objInscrito->getDbBanco();
                        CarregarOptionsAC::bancos($fws_, $debug);
                        ?>
                    </select>
                </div>
                <div class="dp-c3">
                    <label for="agencia">Agência - DV</label>
                    <input type="text" id="db_agencia" name="db_agencia" value="<?php echo $objInscrito->getDbAgencia() ?>"
                           class="form-control" placeholder="Agencia - dv" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="dp-c3">
                    <label for="conta_corrente">Conta Corrente - DV</label>
                    <input type="text" id="db_conta_corrente" name="db_conta_corrente" value="<?php echo $objInscrito->getDbContaCorrente() ?>"
                           class="form-control" placeholder="Conta Corrente" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="dp-c3">
                    <label for="pix">Pix</label>
                    <input type="text" id="db_pix" name="db_pix" value="<?php echo $objInscrito->getDbPix() ?>"
                           class="form-control" placeholder="Pix" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
            </div>
        </div>
        <?php } ?>

<?php
//if($_SERVER['REMOTE_ADDR']=='189.90.70.566'){

if(COD_EVENTO==1){
?>
        <!-- ================= ALMOCO ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Almoço junto ao boleto da inscrição</div>
            <div class="linha">
                <div class="dp-c12">
                    <div style="font-size:12px; color:#555; margin-bottom:6px;">
                        Valor unitário R$ 49,00 (inclui um refrigerante ou água mineral por pessoa/refeição).
                    </div>
                    <label class="dp-check"><input type="checkbox" <?php echo  verifItemChecked('Terca',   json_decode($objInscrito->getInfoAlmoco())) ?> id="infoDiaSemana" name="info_almoco[]" value="Terca"> Terça-feira – 15/08/2017</label>
                    <label class="dp-check"><input type="checkbox" <?php echo  verifItemChecked('Quarta',  json_decode($objInscrito->getInfoAlmoco())) ?> id="infoDiaSemana" name="info_almoco[]" value="Quarta"> Quarta-feira – 16/08/2017</label>
                    <label class="dp-check"><input type="checkbox" <?php echo  verifItemChecked('Quinta',  json_decode($objInscrito->getInfoAlmoco())) ?> id="infoDiaSemana" name="info_almoco[]" value="Quinta"> Quinta-feira – 17/08/2017</label>
                </div>
            </div>
        </div>
<?php
}
?>

        <!-- ================= RECIBO / INSTITUCIONAL ================= -->
        <div class="dp-grupo">
            <div class="dp-grupo-tit">Pagamento / recibo em nome de terceiro</div>
            <div class="linha">
                <div class="dp-c12">
                    <label class="dp-check">
                        <input type='checkbox' <?php echo  verifItemChecked('1', $objInscrito->getInstitucional()) ?>
                        id='institucional' name='institucional' value='1'/>
                        Deseja emitir Boleto/Recibo em nome de sua Instituição/Empresa?
                    </label>
                </div>
            </div>
            <div class="linha">
                <div class="form-recibo dp-c8">
                    <label for="nome_recibo">Nome da instituição</label>
                    <input type="text" id="nome_recibo" name="nome_recibo" value="<?php echo  $objInscrito->getNomeRecibo() ?>"
                           class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="form-recibo dp-c4">
                    <label for="cpf_recibo">CPF/CNPJ</label>
                    <input type="text" id="cpf_recibo" name="cpf_recibo" value="<?php echo  $objInscrito->getCpfRecibo() ?>"
                           class="form-control" SIZE='60' maxlength='18' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                </div>
            </div>
            <div class="linha">
                <div class="form-recibo dp-c6">
                    <label for="endereco_recibo">Rua</label>
                    <input type="text" id="endereco_recibo" name="endereco_recibo" value="<?php echo  $objInscrito->getEnderecoRecibo() ?>"
                           class="form-control" placeholder=""  autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="form-recibo dp-c2">
                    <label for="numero_recibo">Número</label>
                    <input type="text" id="numero_recibo" name="numero_recibo" value="<?php echo  $objInscrito->getNumeroRecibo() ?>"
                           class="form-control" placeholder="Número"  autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="form-recibo dp-c4">
                    <label for="complemento_recibo">Complemento</label>
                    <input type="text" id="complemento_recibo" name="complemento_recibo" value="<?php echo  $objInscrito->getComplementoRecibo() ?>"
                           class="form-control"
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
            </div>
            <div class="linha">
                <div class="form-recibo dp-c2">
                    <label for="cep_recibo">Cep</label>
                    <input type="text" id="cep_recibo" name="cep_recibo" value="<?php echo  $objInscrito->getCepRecibo() ?>"
                           class="form-control" placeholder="Cep"  autofocus
                            SIZE='60' maxlength='9' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                </div>
                <div class="form-recibo dp-c4">
                    <label for="bairro_recibo">Bairro</label>
                    <input type="text" id="bairro_recibo" name="bairro_recibo" value="<?php echo  $objInscrito->getBairroRecibo() ?>"
                           class="form-control" placeholder="Bairro"  autofocus
                            SIZE='60' maxlength='255' onBlur='trim(this);'>
                </div>
                <div class="form-recibo dp-c2">
                    <label for="estado_recibo">Estado</label>
                    <select class='form-control' name='estado_recibo' id='estado_recibo' placeholder="Estado"  autofocus>
                        <option class='form-control' value='' selected>Selecione um Estado</option>
                        <?php
                        $fws_['estado_recibo'] = $objInscrito->getEstadoRecibo();
                        CarregarOptionsAC::estados($fws_, $debug, true);
                        ?>
                    </select>
                </div>
                <div class="form-recibo dp-c4">
                    <label for="cidade_recibo">Cidade</label>
                    <select class='form-control' name='cidade_recibo' id='cidade_recibo' placeholder="Cidade"  autofocus>
                        <option class='form-control' value='' selected>Selecione um Cidade</option>
                        <?php
                        $fws_['cidade_recibo'] = $objInscrito->getCidadeRecibo();
                        CarregarOptionsAC::cidades($fws_, $debug, true);
                        ?>
                    </select>
                </div>
            </div>
        </div>
<br>
<script language="JavaScript1.2" src="resources/js/default.js"></script>
<script language="JavaScript" type="text/javascript">
$(document).ready(function () {
//    $("table.tablesorter").tablesorter({widgets: ['zebra']})
//    .tablesorterPager({container: $("#pager")});

    $("#estado_recibo").change(function () {
        carregarCidadesRecibo(this.value);
    });
    $("#estado").change(function () {
        carregarCidades(this.value);
    });
    // Busca de CEP tambem no admin: o mesmo comportamento que a tela de
    // inscricao ja tinha. Cadastro sem cidade/CEP e o que derruba a emissao
    // de nota fiscal, e digitar tudo a mao e onde o erro entra.
    //
    // So dispara quando o CEP MUDA: abrir o cadastro, tocar no campo e sair nao
    // pode reescrever um endereco que ja estava certo (pode ter complemento ou
    // ajuste manual que o ViaCEP nao tem).
    $("#cep").focus(function () {
        $(this).data('cepAnterior', (this.value || '').replace(/\D/g, ''));
    });
    $("#cep").blur(function () {
        var atual = (this.value || '').replace(/\D/g, '');
        var anterior = $(this).data('cepAnterior');
        if (anterior !== undefined && atual === anterior) {
            return;
        }
        buscaCepAdmin(this.value);
    });
    $("#rg_estado_emissao").change(function () {
        carregarRgCidadeEmissao(this.value);
    });
    $("#estado_nascimento").change(function () {
        carregarCidadeNascimento(this.value);
    });
//    $('#data_nascimento').datepicker();

    // Origem cadastro: ao selecionar CERTIFICADO, dispensa CPF e email
    function aplicarOrigemCadastro() {
        var origem = $('#origem_cadastro').val();
        if (origem === 'CERTIFICADO') {
            $('input[name="e_mail"]').removeAttr('required').closest('div').find('label').first().html('E-mail <small style="color:#999">(opcional)</small>');
            $('#avisoCertificado').show();
        } else {
            $('input[name="e_mail"]').attr('required', 'required').closest('div').find('label').first().html('E-mail');
            $('#avisoCertificado').hide();
        }
    }
    $('#origem_cadastro').change(function() { aplicarOrigemCadastro(); });
    aplicarOrigemCadastro();

    $('#estrangeiro').change(function () {
        if (this.checked) {
            loading($('#textoCpf'),'hidden')
            loading($('#textoPassaporte'),'show')
            $('#cpf').attr("placeholder", "Cpf/Passaporte");
        } else {
            loading($('#textoCpf'),'show')
            loading($('#textoPassaporte'),'hidden')
            $('#cpf').attr("placeholder", "Cpf");
        }
    });
    $('#institucional').change(function () {
        if (this.checked) {
            $('#nome_recibo').removeAttr("disabled");
            $('#cpf_recibo').removeAttr("disabled");
            loading($('.form-recibo'),'show')
        } else {
            $('#nome_recibo').attr("disabled", "true");
            $('#cpf_recibo').attr("disabled", "true");
            loading($('.form-recibo'),'hidden')
        }
    });

    <?php if($objInscrito->getInstitucional() == '1') {?>
        $('#nome_recibo').removeAttr("disabled");
        $('#cpf_recibo').removeAttr("disabled");
        loading($('.form-recibo'),'show')
    <?php }else {?>
        $('#nome_recibo').attr("disabled", "true");
        $('#cpf_recibo').attr("disabled", "true");
        loading($('.form-recibo'),'hidden')
    <?php }?>
});
function mostrarInfoRecibo(){
    $('#institucional').change(function () {
        if (this.checked) {
            $('#nome_recibo').attr("disabled", "true");
            $('#cpf_recibo').attr("disabled", "true");
        } else {
            $('#nome_recibo').attr("disabled", "false");
            $('#cpf_recibo').attr("disabled", "false");
        }
    });
}
function carregarCidadesRecibo(cod, selecionado)
{
   $.ajax({
       type: "POST",
       url: '<?php echo  WB_URL_COMMAND ?>',
       data: ({'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1'}),
       dataType: "html",
       async: false,
       cache: false
       , success: function (data) {
           $("#cidade_recibo").html(data);
       }
       , error: function () {
           alert("Nao foi possivel obter informações:\n" + req.statusText);
       }
   });
}
function carregarCidades(cod, selecionado)
{
   $.ajax({
       type: "POST",
       url: '<?php echo  WB_URL_COMMAND ?>',
       data: ({'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1'}),
       dataType: "html",
       async: false,
       cache: false
       , success: function (data) {
           $("#cidade").html(data);
       }
       , error: function () {
           alert("Nao foi possivel obter informações:\n" + req.statusText);
       }
   });
}
function carregarRgCidadeEmissao(cod, selecionado)
{
   $.ajax({
       type: "POST",
       url: '<?php echo  WB_URL_COMMAND ?>',
       data: ({'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1'}),
       dataType: "html",
       async: false,
       cache: false
       , success: function (data) {
           $("#rg_cidade_emissao").html(data);
       }
       , error: function () {
           alert("Nao foi possivel obter informações:\n" + req.statusText);
       }
   });
}
/**
 * Preenche endereco a partir do CEP (ViaCEP), como na tela de inscricao.
 *
 * Diferenca do publico: aqui estado e cidade sao SELECT, entao e preciso
 * selecionar a UF, recarregar a lista de cidades daquela UF e so entao marcar
 * a cidade. carregarCidades roda com async:false — por isso da para selecionar
 * logo em seguida.
 */
function statusCep(cor, texto) {
    $('#cepStatus').css('color', cor).text(texto);
}

function buscaCepAdmin(cep) {
    var cepLimpo = (cep || '').replace(/\D/g, '');
    if (cepLimpo === '') {
        statusCep('#1976d2', '');
        return;
    }
    if (!/^[0-9]{8}$/.test(cepLimpo)) {
        statusCep('#b71c1c', '— formato inválido');
        return;
    }

    // Aviso ao lado do rotulo, como na emissao de NFS-e: o ViaCEP demora e sem
    // isso quem digitou nao sabe se o sistema esta fazendo algo.
    statusCep('#1976d2', '— buscando...');

    $.getJSON("https://viacep.com.br/ws/" + cepLimpo + "/json/?callback=?", function (dados) {
        if ("erro" in dados) {
            statusCep('#b71c1c', '— CEP não encontrado');
            return;
        }

        // Troca o endereco INTEIRO, nao so o que esta vazio: CEP novo significa
        // endereco novo, e misturar rua antiga com cidade nova produz um
        // endereco que nao existe — foi assim que uma nota fiscal quase saiu
        // com a rua de um lugar e o bairro de outro.
        if (dados.logradouro) { $("#endereco").val(dados.logradouro); }
        if (dados.bairro)     { $("#bairro").val(dados.bairro); }

        if (dados.uf) {
            $("#estado").val(dados.uf);
            carregarCidades(dados.uf);
            selecionarCidadeAdmin(dados.localidade);
        }

        statusCep('#1b5e20', '— ' + dados.localidade + '/' + dados.uf);

        // Numero e complemento o ViaCEP nao tem: sao os unicos que continuam
        // por conta de quem cadastra.
        $("#numero").focus();
    }).fail(function () {
        statusCep('#b71c1c', '— não foi possível consultar');
    });
}

/**
 * O select de cidades vem do nosso banco (tab_opcoes_cidades) e o nome pode
 * divergir do texto do ViaCEP por acento ou grafia. Tenta exato, depois sem
 * acento/caixa; nao achando, avisa em vez de deixar em branco em silencio.
 */
function selecionarCidadeAdmin(nomeCidade) {
    if (!nomeCidade) { return; }

    var $sel = $("#cidade");
    $sel.val(nomeCidade);
    if ($sel.val() === nomeCidade) { return; }

    var alvo = normalizarTextoCidade(nomeCidade);
    var achou = false;
    $sel.find("option").each(function () {
        if (normalizarTextoCidade($(this).val()) === alvo) {
            $sel.val($(this).val());
            achou = true;
            return false;
        }
    });

    if (!achou) {
        alert("A cidade \"" + nomeCidade + "\" nao esta na lista deste estado. Selecione manualmente.");
    }
}

function normalizarTextoCidade(txt) {
    return (txt || '')
        .toString()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}
function carregarCidadeNascimento(cod, selecionado)
{
   $.ajax({
       type: "POST",
       url: '<?php echo  WB_URL_COMMAND ?>',
       data: ({'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1'}),
       dataType: "html",
       async: false,
       cache: false
       , success: function (data) {
           $("#cidade_nascimento").html(data);
       }
       , error: function () {
           alert("Nao foi possivel obter informações:\n" + req.statusText);
       }
   });
}
</script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<?php
//}
?>
