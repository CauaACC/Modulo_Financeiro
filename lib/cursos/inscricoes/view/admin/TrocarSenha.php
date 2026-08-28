<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
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
            alert(senhaNova)
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
                erro += 'A senha deve conter pelo menos um caractere especial (ex.: @, #, $).\n';
            }
            if (erro) {
                alert('Erro na troca de senha:\n' + erro);
            } else {
                salvarTrocarSenha();
            }

        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
    });
    function salvarTrocarSenha() {
        $('#formTrocarSenha').ajaxSubmit({
            target: '#mensagem'
            , dataType: 'json'
            , async: false
            , cache: false
            , beforeSend: function() {
                startLoading();
            }
            , timeout: 4000
                    , error: function(ajax) {
                showResponse(ajax);
            }
            , success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    limparCampos();
                }
            }
            , complete: function() {
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
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
<h3>Área Administrativa</h3>  
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-body">
        <?php
        require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_meus_dados.php");
            echo "<h4>Trocar Senha</h4>";
            try {
                If ($_SESSION['_administrativo']!=1) {
                    throw new Exception("Seu login não possui acesso a esta área.", 1);
                }    
                ?>
                <form  id="formTrocarSenha" name="formTrocarSenha" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-7" style="padding-left: 0px;">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="login">Login:</label>
                            <h4><?php  echo $_SESSION['_login'] ?></h4>
                            <div>
                            <p>Requisitos para nova senha:</p>
                            <ul>
                                <li>Mínimo de 8 caracteres.</li>
                                <li>Pelo menos uma letra e um número.</li>
                                <li>Incluir ao menos um caractere especial (ex.: @, #, $).</li>
                            </ul>
                            </div>
                            Essas medidas ajudam a proteger sua conta contra acessos não autorizados e garantem uma maior segurança dos seus dados                            
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="login">Login:</label>
                            <?php  echo $_SESSION['_login'] ?>
                            <br>
                            <label for="senha_atual">Senha Atual:</label>
                            <input type="password" id="senha_atual" name="senha_atual"  autocomplete="new-password"
                                   class="form-control" placeholder="Senha atual" >
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="senha_nova">Senha Nova:</label>
                            <input type="password" id="senha_nova" name="senha_nova" value=""
                                   class="form-control" placeholder="Senha nova" >
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="senha_nova_repetir">Repetir Senha Nova:</label>
                            <input type="password" id="senha_nova_repetir" name="senha_nova_repetir" value=""
                                   class="form-control" placeholder="Repetir senha nova" >
                        </div>
                        <br>
                <?php if(defined('GOOGLE_KEY')){ ?>
                <div class="g-recaptcha" data-sitekey="<?php echo GOOGLE_KEY?>"></div>
                <?php } ?>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">                  
                            <div id="carregando" class="shown hidden">
                                <div class="imagem">
                                    <div class="frase">Carregando...</div>
                                    <img src="<?php echo  WB_PATH ?>/resources/image/loading-9.gif" border="0">

                                </div>
                            </div>
                            <div id="mensagem"></div>
                            <div id="sucesso"    class="hidden"></div>
                            <div id="erro"       class="hidden"></div>                
                            <input type="hidden" value="1"               name="_submit_check">
                            <input type="hidden" value="GerenciarLogin" name="c">
                            <input type="hidden" value="trocarSenha"    name="m">
                            <button class="btn btn-md btn-primary" type="button" id="btnSalvar" >Alterar</button>
                            &nbsp;&nbsp;&nbsp;                
                            <button class="btn btn-md btn-primary" type="button" id="btnLimpar" >Limpar e sair</button>
                        </div>
                    </div>  
                </form>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>        
            <?php } ?>
        </div>
    </div>    
</div>    
