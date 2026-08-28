<?php
// require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarLoginAC();
?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');

        $('table').tablesorter({
            //            sortList: [[1, 0]]
        });

        $('#btnSalvar').click(function() {
            var senhaNova = $('#senha_nova').val();
            var erro = '';

            // Tamanho mínimo de 8 caracteres
            if (senhaNova.length < 8) {
                erro += 'A senha deve ter pelo menos 8 caracteres.\n';
            }
            // Deve conter letras maiúsculas e minúsculas e números
            if (!(/[a-z]/.test(senhaNova) && /[A-Z]/.test(senhaNova) && /\d/.test(senhaNova))) {
                erro += 'A senha deve conter letras (maiúsculas e minúsculas) e números.\n';
            }
            // Deve conter ao menos um caractere especial
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(senhaNova)) {
                erro += 'A senha deve conter pelo menos um caractere especial.\n';
            }
            if (erro) {
                alert('Erro na troca de senha:\n' + erro);
            } else {
                salvarTrocarSenha();
                alert('Senha alterada com sucesso!');
                location.reload();
            }

        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
    });

    function salvarTrocarSenha() {
        $('#formTrocarSenha').ajaxSubmit({
            target: '#mensagem',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            timeout: 4000,
            error: function(ajax) {
                showResponse(ajax);
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    limparCampos();
                }
            },
            complete: function() {
                stopLoading();
            }
        });
    }

    function limparCampos() {
        //Limpando campos
        $('#senha_atual').val('');
        $('#senha_nova').val('');
        $('#senha_nova_repetir').val('');
    }
</script>
<!--<script type="text/javascript" src="/<?php echo  WB_PATH ?>/view/js/util/validacoes.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
?>
<h3>Área Administrativa</h3>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php
            require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_meus_dados.php");
            echo "<h4>Dados Pessoais</h4>";
            try {

                $objConvidado = new Convidado();
                $objConvidado->setEMail($_SESSION['_login']);
                $objConvidado->getConvidadoByEmail();
            ?>
            <div class="form-group col-sm-7" style="padding-left: 0px;">
            

        <div class="form-group">
            <label for="nome_convidado">Nome</label>
            <input type="text" class="form-control" id="nome_convidado" value="<?php echo $objConvidado->getNomeConvidado(); ?>">
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" class="form-control" id="cpf" value="<?php echo $objConvidado->getCpf(); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="rg">RG</label>
                    <input type="text" class="form-control" id="rg" value="<?php echo $objConvidado->getRg(); ?>">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="e_mail">E-mail</label>
            <input type="email" class="form-control" id="e_mail" value="<?php echo $objConvidado->getEmail(); ?>">
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input type="text" class="form-control" id="data_nascimento" value="<?php echo $objConvidado->getDataNascimento(); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <select class="form-control" id="sexo">
                        <option value="">Selecione</option>
                        <option value="Masculino" <?php echo ($objConvidado->getSexo() == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="Feminino" <?php echo ($objConvidado->getSexo() == 'Feminino') ? 'selected' : ''; ?>>Feminino</option>
                        <option value="Outro" <?php echo ($objConvidado->getSexo() == 'Outro') ? 'selected' : ''; ?>>Outro</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="profissao_atuacao">Profissão/Atuação</label>
            <input type="text" class="form-control" id="profissao_atuacao" value="<?php echo $objConvidado->getProfissaoAtuacao(); ?>">
        </div>
        <div class="form-group">
            <label for="grau_escolaridade">Grau de Escolaridade</label>
            <input type="text" class="form-control" id="grau_escolaridade" value="<?php echo $objConvidado->getGrauEscolaridade(); ?>">
        </div>
        <div class="form-group">
            <label for="instituicao">Instituição</label>
            <input type="text" class="form-control" id="instituicao" value="<?php echo $objConvidado->getInstituicao(); ?>">
        </div>
        <div class="form-group">
            <label for="endereco">Endereço</label>
            <input type="text" class="form-control" id="endereco" value="<?php echo $objConvidado->getEndereco(); ?>">
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="numero">Número</label>
                    <input type="text" class="form-control" id="numero" value="<?php echo $objConvidado->getNumero(); ?>">
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label for="complemento">Complemento</label>
                    <input type="text" class="form-control" id="complemento" value="<?echo $objConvidado->getComplemento(); ?>">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="bairro">Bairro</label>
            <input type="text" class="form-control" id="bairro" value="<?php echo $objConvidado->getBairro(); ?>">
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="cep">CEP</label>
                    <input type="text" class="form-control" id="cep" value="<?php echo $objConvidado->getCep(); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input type="text" class="form-control" id="cidade" value="<?php echo $objConvidado->getCidade(); ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <input type="text" class="form-control" id="estado" value="<?php echo $objConvidado->getEstado(); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="uf">UF</label>
                    <input type="text" class="form-control" id="uf" value="<?php echo $objConvidado->getUf(); ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="fone_residencial">Telefone Residencial</label>
                    <input type="text" class="form-control" id="fone_residencial" value="<?php echo $objConvidado->getFoneResidencial(); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="fone_comercial">Telefone Comercial</label>
                    <input type="text" class="form-control" id="fone_comercial" value="<?php echo $objConvidado->getFoneComercial(); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="fone_celular">Celular</label>
                    <input type="text" class="form-control" id="fone_celular" value="<?php echo $objConvidado->getFoneCelular(); ?>">
                </div>
            </div>
        </div>


            



            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
            <?php
            } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>
            <?php } ?>
        </div>
    </div>
</div>