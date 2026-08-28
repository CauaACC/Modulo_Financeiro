<?php
require_once 'inscricao_congresso_tit_area_inscrito.php';

// Redirecionar para fluxo TICKETS se configurado
if ($OP_TIPO_FLUXO_INSCRICAO == 'TICKETS'
    && $OP_PROCESSO_INSCRICAO == true
    && $OP_PROCESSO_INSCRICAO_SUSPENSO == false) {
    $url = WB_URL_VIEW . "?content=view/inscricao/ticket_selecao.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
    exit();
}

// Redirecionar para fluxo AGENDA se configurado (Dia -> Grupo -> Dados Pessoais).
// Excecao: se o request veio do link "Acessar minhas agendas" (?login=1), nao
// redireciona — mostra o form de login (com captcha) desta tela e adapta o
// bloco "iniciar inscricao" para virar link "Fazer novo agendamento".
if ($OP_TIPO_FLUXO_INSCRICAO == 'AGENDA'
    && $OP_PROCESSO_INSCRICAO == true
    && $OP_PROCESSO_INSCRICAO_SUSPENSO == false
    && empty($_GET['login'])) {
    $url = WB_URL_VIEW . "?content=view/inscricao/agenda_passo1_dia.php";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
    exit();
}
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php
            //            $objEv = new Evento();
            //            $objEv->setCodEvento(COD_EVENTO);
            //            $objEv->getEventoById();
            //            echo "<h3>".$objEv->getTitulo()."</h3>";
            if (COD_EVENTO == 7) {
                require 'inscricao_congresso_verifica_inicio_form_000' . COD_EVENTO . '.php';
            } else if ($OP_TIPO_FLUXO_INSCRICAO == 'AGENDA') {
                // No fluxo AGENDA o CPF nao e pedido no inicio (so no fim).
                // Em vez do form "iniciar inscricao", oferece um link direto
                // para o passo 1 do agendamento.
                ?>
                <div class="col-sm-12 text-center" style="padding: 15px;">
                    <a href="<?php echo WB_URL_VIEW ?>?content=view/inscricao/agenda_passo1_dia.php&amp;novo=1"
                       class="btn btn-lg btn-primary">
                        <i class="fa fa-calendar"></i> Fazer novo agendamento
                    </a>
                </div>
                <?php
            } else {
                if ($fwsGet_['rota'] != 'atividades' and $fwsGet_['rota'] != 'cursos' and $fwsGet_['rota'] != 'admin') {
                    require 'inscricao_congresso_verifica_inicio_form.php';
                }
            }
            ?>
        </div>
        <hr>
        <div class="panel-body">
            <?php if ($FWS_Liberar_Ja_Estou_Inscrito == 1) { ?>
                <div class="col-sm-12 text-center" style="padding: 15px;">
                    <?php
                    if ($fws_['msg'] and (addslashesopctec($_GET['codRetorno']) == 'efetuarLogin')) {
                        FWA_Mostrar_Login($fws_['msg']);
                    }
                    ?>
                    <form class="form-signin" name="form" method="POST" onsubmit="return criticarLogin();"
                          action="<?php echo WB_URL_COMMAND ?>" autocomplete="off">
                        <input type="hidden" name="c" value="GerenciarLogin">
                        <input type="hidden" name="m" value="efetuarLogin">

                        <?php if (COD_EVENTO == 10) { ?>
                            <input type="text" name="cpf" id="cpf" value="<?php echo addslashesopctec($_GET['cpf']) ?>"
                                   onBlur="trim(this);"
                                   size='20' class="form_campos form-control" placeholder="Cpf utilizado na inscrição"
                                   required autofocus autocomplete="one-time-code">
                        <?php } else { ?>
                            Se já está inscrito informe e-mail <br>e código de inscrição.<BR>
                            <input type="email" name="e_mail" id="e_mail"
                            <?php if ($_GET['e_mail'] != ''){ ?>
                                value="<?php echo $_GET['e_mail'] != ''? addslashesopctec($_GET['e_mail']) : null; ?>" onBlur="trim(this);"

                            <?php } ?>
                                   size='20' class="form_campos form-control"
                                   placeholder="E-mail utilizado na inscrição" required autofocus>
                        <?php } ?>
                        <input type="password" name="senha"
                               size="20" class="form_campos form-control" placeholder="Senha da inscrição" required
                               autofocus autocomplete="new-password">

                        <input type="text" name="4cpf" id="4cpf" maxlength="4"
                               size="20" class="form_campos form-control" placeholder="Quatro últimos dígitos do seu CPF"
                               required autofocus autocomplete="one-time-code">

                        <script>
                            document.getElementById('4cpf').addEventListener('input', function (e) {
                                var target = e.target, position = target.selectionEnd, length = target.value.length;

                                if (length !== 4) {
                                    target.value = target.value.replace(/[^0-9]/g, '').slice(0, 4);
                                    target.selectionEnd = position -= (length - target.value.length);
                                }
                            });
                        </script>

                        <br>
                        <?php if (defined('GOOGLE_KEY')) { ?>
                            <div class="g-recaptcha" data-sitekey="<?php echo GOOGLE_KEY ?>"
                                 style="transform:scale(0.50);-webkit-transform:scale(0.50);transform-origin:0 0;-webkit-transform-origin:0 0;"></div>
                        <?php } ?>
                        <button class="btn btn-lg btn-primary btn-block" type="submit">Login na Inscrição</button>

                        <br><a href='index.php?content=view/inscricao/inscricao_esqueceu_credenciais.php'>Esqueceu suas
                            credenciais?</a>
                        <br><INPUT TYPE='hidden' VALUE='Inserir' NAME='acao'>
                        <br>
                        <br><a href='index.php?content=listar_eventos.php'>Listar Eventos</a>
                    </form>
                </div>
            <?php } ?>
            <div class="col-sm-12 text-center" style="padding: 15px;">
                <?php if ($msg_tela_login_envio_trabalho != '') { ?>
                    <a href="<?php echo WB_URL ?><?php echo WB_PATH ?>/" class="btn btn-primary custom-button red-btn">

                        <span style="font-size: 150%;"><?php echo PERIODO ?><br><?php echo CIDADE_UF ?></span>
                        <span style="font-size: 130%;color: yellow;"><br></span>
                        <br>
                        <span style="font-size: 130%;color: yellow;"><?php echo $msg_tela_login_envio_trabalho ?></span>

                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php if (defined('GOOGLE_KEY')) { ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>
<script language="JavaScript1.2" src="resources/js/default.js"></script>
<script LANGUAGE="JAVASCRIPT">
    var width = $('.g-recaptcha').parent().width();
    if (width < 302) {
        var scale = width / 302;
        $('.g-recaptcha').css('transform', 'scale(' + scale + ')');
        $('.g-recaptcha').css('-webkit-transform', 'scale(' + scale + ')');
        $('.g-recaptcha').css('transform-origin', '0 0');
        $('.g-recaptcha').css('-webkit-transform-origin', '0 0');
    }

    //------------------------------------------------------------------------
    function criticarLogin() {

        // testa se o email foi preenchido e se está no formato correto
        if (!isEmail(document.form.e_mail.value)) {
            alert("O email deve ser preenchido no formato correto (usuário@dominio).");
            document.form.e_mail.focus();
            return false;
        }
        // testa se o email foi preenchido e se está no formato correto
        if (!isEmail(document.form.e_mail.value)) {
            alert("O email deve ser preenchido no formato correto (usuário@dominio).");
            document.form.e_mail.focus();
            return false;
        }
        <?php if(defined('GOOGLE_KEY')){?>
        if ($('#g-recaptcha-response').val() == '') {
            alert('Clique na opção: Não sou um robô!');
            return false;
        }
        <?php } ?>
    }

    // -->
</SCRIPT>