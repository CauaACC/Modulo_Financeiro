<?php

class CarregarBotoesAC {

    public static function btnMostrarParecerAvaliacao($fws_, $objL, $debug = false) { ?>
        <?php if ($objL->getParecerAvaliador()!=''){ ?>
            <br>
            <button class="btn btn-xs btn-info" type="button" onclick="toggleParecer(<?php echo $objL->getCodAvaliacao() ?>)">
                Mostrar Parecer
            </button>
            <div id="parecer-<?php echo $objL->getCodAvaliacao() ?>" style="display:none;">
                <h4>Parecer do Avaliador:</h4>
                <?php echo tratarJson($objL->getParecerAvaliador()) ?>
            </div>

            <?php if (!defined('TOGGLE_PARECER_SCRIPT')){ ?>
                <script>
                function toggleParecer(codAvaliacao) {
                    var parecerDiv = document.getElementById('parecer-' + codAvaliacao);
                    if (parecerDiv.style.display === 'none') {
                        parecerDiv.style.display = 'block';
                    } else {
                        parecerDiv.style.display = 'none';
                    }
                }
                </script>
                <?php define('TOGGLE_PARECER_SCRIPT', true); ?>
            <?php } ?>
        <?php } ?>
    <?php 
    }

    public static function btnLogarComo($fws_, $objL, $debug = false) { ?>
        <a title='Logar Como' target="_blank" href='<?php echo UrlInscView($objL) ?>index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo  $objL->getCpf() ?>&cod_inscrito=<?php echo  $objL->getCodInscrito() ?>&cod_evento=<?php echo  $objL->getCodEvento() ?>'>
            <img src='<?php echo  WB_URL_IMG ?>/icone_preview.gif' border='0'></a>
    <?php }

    // Link isolado de "abrir/editar inscricao" (o mesmo que btnGerenciarInscrito
    // usa embutido). Extraido para poder reusar fora da tela de inscritos.
    public static function btnAbrirInscricao($fws_, $objL, $debug = false) { ?>
        <a title="Abrir Inscri&ccedil;&atilde;o" target="_blank" href='<?php echo UrlInscView($objL) ?>index.php?content=view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito=<?php echo  $objL->getCodInscrito() ?>&cod_evento=<?php echo  $objL->getCodEvento() ?>'>
            <img src="<?php echo  WB_URL_IMG ?>/fig-alterar.gif" border="0"></a>
    <?php }

    public static function btnGerenciarEtiqueta($fws_, $objL, $debug = false) { ?>
        <a title="Gerar Etiqueta pra Cracha" href='javascript:void(0);' onclick='javascript:gerarfnEtiqueta(<?php echo  $objL->getCodInscrito() ?>);'><img src='<?php echo  WB_URL_IMG ?>/cracha.jpg' alt='Gerar Etiqueta pra Cracha' border='0' width='30px'>
    <?php }

    public static function btnGerenciarInscrito($fws_, $objL, $debug = false) { ?>
        <?php /**@todo criar area de configuração para evento vinculado, Tabela de Propósitos de Vínculo */ ?>
        <?php if ($objL->getCodEvento() == 80 or $objL->getCodEvento() == 88 or $objL->getCodEvento() == 115) { ?>
            <?php $statusAtual = $objL->getStatusProcesso() ?: 'INSCRITO'; ?>
            <br>Status no Processo:
            <select id="statusProcesso-<?php echo $objL->getCodInscrito() ?>"
                    class="form-control input-sm"
                    style="width:auto;display:inline-block;padding:1px 4px;height:auto;font-size:14px;"
                    data-status-anterior="<?php echo $statusAtual ?>"
                    onchange="alterarStatusProcesso(<?php echo $objL->getCodInscrito() ?>)">
                <option value="INSCRITO"     <?php echo $statusAtual == 'INSCRITO'     ? 'selected' : '' ?>>Inscrito</option>
                <option value="CONVOCADO"    <?php echo $statusAtual == 'CONVOCADO'    ? 'selected' : '' ?>>Convocado</option>
                <option value="PARTICIPANDO" <?php echo $statusAtual == 'PARTICIPANDO' ? 'selected' : '' ?>>Participando</option>
                <option value="DESISTENTE"   <?php echo $statusAtual == 'DESISTENTE'   ? 'selected' : '' ?>>Desistente</option>
                <option value="CANCELADO"    <?php echo $statusAtual == 'CANCELADO'    ? 'selected' : '' ?>>Cancelado</option>
                <option value="SUSPENSO"     <?php echo $statusAtual == 'SUSPENSO'     ? 'selected' : '' ?>>Suspenso</option>
            </select>
            <br><br>
        <?php } ?>
        <?php self::btnAbrirInscricao($fws_, $objL, $debug); ?>

        <?php /* valorPago alimenta o confirm extra: desativar/excluir inscricao
                 PAGA avisa o financeiro do gestor por e-mail */ ?>
        <a title="Excluir" href='javascript:void(0);' onclick="alterarStatusInscricao(<?php echo $objL->getCodInscrito() ?>,'Excluir',<?php echo (float)$objL->getValorPagamentos() ?>)">
            <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir' border='0'></a>

        <a title="Ativar" href='javascript:void(0);' onclick="alterarStatusInscricao(<?php echo $objL->getCodInscrito() ?>,'Ativar',0)">
            <img src='<?php echo  WB_URL_IMG ?>/fig-ativar.gif' alt='Ativar' border='0'></a>

        <a title="Desativar" href='javascript:void(0);' onclick="alterarStatusInscricao(<?php echo $objL->getCodInscrito() ?>,'Desativar',<?php echo (float)$objL->getValorPagamentos() ?>)">
            <img src='<?php echo  WB_URL_IMG ?>/fig-desativar.gif' alt='Desativar' border='0'></a>
        <a title="Novo" target="_blank" href='<?php echo UrlInscView($objL) ?>index.php?content=view/admin/inscritos-cadastro-ok.php&cod_evento=<?php echo  $objL->getCodEvento() ?>'>
            <img src='resources/image/fig-incluir.gif' border='0' width="20px"></a>
    <?php }

    public static function btnExcluirAtividade($fws_, $objL, $debug = false) { ?>
        <a id="btnExcluirAtiv-<?php echo $objL->getCodInscrito() ?>-<?php echo $objL->getCodOficina() ?>" title="Remover inscrito da atividade: <?php echo  $objL->getCodOficina() ?>" href='javascript:void(0);' onclick='javascript:excluirInscritoOficina(<?php echo  $objL->getCodInscrito() ?>,<?php echo  $objL->getCodOficina() ?>);'>
            <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir inscrito da atividade: <?php echo  $objL->getCodOficina() ?>' border='0' width="15px">
            Excluir da atividade: <?php echo $objL->getCodOficina() ?></a>
    <?php }

    public static function btnListarBoletos($fws_, $objL, $debug = false) { ?>
        <a title='Listar Boletos' target="_blank" href='<?php echo "https://" . FWA_Dominio() . "/" . $objL->getUrlPrefixo() ?>/inscricoes/<?php echo  $objL->getQrCode() ?>'>
            <img src='<?php echo  WB_URL_IMG ?>/boleto-1.png' border='0' height="25" width='30px'></a>
    <?php }

    public static function btnListarBoletosAvulso($fws_, $objL, $debug = false) { ?>
        <a title='Listar Boletos' target="_blank" href='<?php echo UrlInscView($objL)?>index.php?content=view/admin/GerenciarBoletoAvulso.php&codInscrito=<?php echo  $objL->getCodInscrito() ?>'>
        <img src='<?php echo  WB_URL_IMG ?>/boleto-1.png'  border='0' height="25" width='30px'></a>
    <?php }

    public static function btnGerenciarIsento($fws_, $objL, $debug = false) { ?>
        <?php if ($objL->getCodIsento()) { ?>
            <a class="show" id="btnExcluirIsento-<?php echo $objL->getCodInscrito() ?>" title="Remover cadastrado da lista de isentos" href='javascript:void(0);' onclick='javascript:excluirIsento(<?php echo  $objL->getCodInscrito() ?>);'>
                <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir isenção' border='0' width="15px"> Excluir Isenção</a>
            <a class="hide" id="btnAdicionarIsento-<?php echo $objL->getCodInscrito() ?>" title="Adicionar cadastrado na lista de isentos" href="javascript:void(0);" onclick="javascript:adicionarIsento(<?php echo  $objL->getCodInscrito() ?>,'<?php echo  $objL->getNomeInscrito() ?>','<?php echo  $objL->getCpf() ?>','<?php echo  $objL->getEMail() ?>');">
                <img src='<?php echo  WB_URL_IMG ?>/abre.gif' alt='Adicionar cadastrado na lista de isentos' border='0' width="15px"> Adicionar Isenção</a>
        <?php }else{ ?>
            <a class="hide" id="btnExcluirIsento-<?php echo $objL->getCodInscrito() ?>" title="Remover cadastrado da lista de isentos" href='javascript:void(0);' onclick='javascript:excluirIsento(<?php echo  $objL->getCodInscrito() ?>);'>
                <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir isenção' border='0' width="15px"> Excluir Isenção</a>
            <a class="show" id="btnAdicionarIsento-<?php echo $objL->getCodInscrito() ?>" title="Adicionar cadastrado na lista de isentos" href="javascript:void(0);" onclick="javascript:adicionarIsento(<?php echo  $objL->getCodInscrito() ?>,'<?php echo  $objL->getNomeInscrito() ?>','<?php echo  $objL->getCpf() ?>','<?php echo  $objL->getEMail() ?>');">
                <img src='<?php echo  WB_URL_IMG ?>/abre.gif' alt='Adicionar cadastrado na lista de isentos' border='0' width="15px"> Adicionar Isenção</a>
        <?php } ?>
        <br>
    <?php }

    public static function bntGerenciarVoucherInscrito ($fws_, $objL, $debug = false) { ?>
        <?php if ($objL->getCodVoucher()) { ?>
            <a class="show" id="btnExcluirInscritoVoucher-<?php echo $objL->getCodInscrito() ?>" title="Remover voucher cadastrado" href='javascript:void(0);' onclick="javascript:excluirInscritoVoucher(<?php echo  $objL->getCodInscrito() ?>);">
                <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir voucher' border='0' width="15px"> Excluir Voucher</a>
            <a class="hide" id="btnAdicionarInscritoVoucher-<?php echo $objL->getCodInscrito() ?>" title="Adicionar voucher para o inscrito" href="javascript:void(0);" onclick="javascript:adicionarInscritoVoucher('<?php echo  $objL->getCodInscrito() ?>');">
                <img src='<?php echo  WB_URL_IMG ?>/abre.gif' alt='Adicionar cadastrado na lista de isentos' border='0' width="15px">Adicionar Voucher</a>
        <?php }else{ ?>
            <a class="hide" id="btnExcluirInscritoVoucher-<?php echo $objL->getCodInscrito() ?>" title="Remover voucher cadastrado" href='javascript:void(0);' onclick="javascript:excluirInscritoVoucher(<?php echo  $objL->getCodInscrito() ?>);">
                <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir voucher' border='0' width="15px"> Excluir Voucher</a>
            <a class="show" id="btnAdicionarInscritoVoucher-<?php echo $objL->getCodInscrito() ?>" title="Adicionar voucher para o inscrito" href="javascript:void(0);" onclick="javascript:adicionarInscritoVoucher('<?php echo  $objL->getCodInscrito() ?>');">
                <img src='<?php echo  WB_URL_IMG ?>/abre.gif' alt='Adicionar cadastrado na lista de isentos' border='0' width="15px">Adicionar Voucher</a>
        <?php } ?>
    <?php }

    public static function btnLoginWhats ($fws_, $objL, $debug = false) { ?>
        <form class="form-signin" name="form" method="POST" target="_blank"
            action="<?php echo WB_URL_COMMAND?>"  style='padding:2px;'>
            <input type="hidden" name="c" value="GerenciarWhats">
            <input type="hidden" name="m" value="enviarCredencial">
            <button class="btn btn-xs btn-primary btn-lembrar-pagamento" type="submit">Enviar credencial: <i class="fa fa-whatsapp"></i> whats</button>
            <?php //echo  mb_strtolower($objL->getEMail()) ?>
            <input type="hidden" name="codInscrito" value="<?php echo $objL->getCodInscrito()?>">
        </form>
        <?php if ($_SESSION['_cod_usuario'] == 668) { ?>
            <?php 
            $url = "https://" . FWA_Dominio() . "/" . $objL->getUrlPrefixo() . "/inscricoes/" . $objL->getQrCode();
            ?>
            &nbsp;<a target='_blank' style="margin-top:4px;margin-bottom:4px;display:inline-block;" href='<?php echo "https://api.whatsapp.com/send?phone=".limpaFoneWhats($objL->getFoneContatoDdd().$objL->getFoneContato())."&text=Olá ".$objL->getPreNome()."\nAcesse: ".$url."\nLogin: ".$objL->getEMail()."\nCódigo: ".$objL->getCodInscrito()?>'>Alternativo por:<i class="fa fa-whatsapp"></i> whats</a>
        <?php } ?>
    <?php }

    public static function btnPagamentoWhats ($fws_, $objL, $debug = false) { ?>
        <form class="form-signin" name="form" method="POST" target="_blank"
            action="<?php echo WB_URL_COMMAND?>"  style='padding:2px;'>
            <input type="hidden" name="c" value="GerenciarWhats">
            <input type="hidden" name="m" value="enviarAvisoPagamento">
            <button class="btn btn-xs btn-primary btn-lembrar-pagamento" type="submit">Lembrar Pagamento: <i class="fa fa-whatsapp"></i> whats</button>
            <?php //echo  mb_strtolower($objL->getEMail()) ?>
            <input type="hidden" name="codInscrito" value="<?php echo $objL->getCodInscrito()?>">
        </form>
    <?php }

    public static function btnLembrarPagamentoWhats ($fws_, $objL, $objEv,$debug = false) { ?>
        <?php /*
        <br>
        <a target='_blank' href='<?php
                                            echo "https://api.whatsapp.com/send?phone=".limpaFoneWhats($objL->getFoneContatoDdd().$objL->getFoneContato())
            . "&text=Olá ".textoMaiusculoMinusculo($objL->getPreNome()) 
            . " _ _ _ Sobre o ".$objEv->getTitulo().", "
            . " segue link de acesso para pagamento: "
            . " [https://".FWA_Dominio()."/".$objL->getUrlPrefixo()?>/inscricoes/<?php echo  $objL->getQrCode()."]."
            . " Caso já tenha efetuado o pagamento, por favor, desconsidere esta mensagem."
            . " Ou digite 2 para sair dessa lista."
            . " _ _ _ Qualquer dúvida entre em contato. 😊 "                               
            . " ";
            ?>
            '>Msg. c/texto<br> p/ sair da lista por: <i class="fa fa-whatsapp"></i> whats</a>
            */
            ?>
    <?php }

    public static function btnEnviarEmail ($fws_, $objL, $debug = false) { ?>
        <a href="mailto:<?php echo  mb_strtolower($objL->getEMail()) ?>&subject=[<?php echo ABREV_EVENTO?>]"> <?php echo  mb_strtolower($objL->getEMail()) ?></a>
    <?php }

    public static function btnLinkEmailGmail ($fws_, $objL, $debug = false) { ?>
        <a target='_blank' href='https://mail.google.com/mail/u/0/?view=cm&fs=1&tf=1&su=[<?php echo ABREV_EVENTO?>]&body=Olá <?php echo mb_strtoupper($objL->getPreNome())?>%0A%0A%0A%0Aatt. Comissão Organizadora%0A<?php echo ABREV_EVENTO?> - <?php echo NOME_EVENTO?>%0ADúvidas: 
            <?php echo EMAIL_CONTATO;?>&to=<?php echo  mb_strtolower($objL->getEMail()) ?>&cc=<?php echo EMAIL_CONTATO?>'>Enviar email pelo Gmail</a>
    <?php }

    public static function btnLinkWhatsOla ($fws_, $objL, $debug = false) { ?>
        &nbsp;<a target='_blank' style="margin-top:4px;margin-bottom:4px;display:inline-block;" href='<?php echo "https://api.whatsapp.com/send?phone=".limpaFoneWhats($objL->getFoneContatoDdd().$objL->getFoneContato())."&text=Olá ".$objL->getPreNome() ?>'>Diga Olá por:<i class="fa fa-whatsapp"></i> whats</a>
    <?php }

    /** é necessário fazer um form para enviar variaveis via post */
    public static function btnMessageSac ($fws_, $objL, $debug = false) { ?>
        <!-- <form class="form-signin" name="form" method="POST"
                action="<?php echo WB_URL_COMMAND?>">
            <input type="hidden" name="c" value="GerenciarEmail">
            <input type="hidden" name="m" value="enviarCertificados">
            <button class="btn btn-xs btn-primary btn-enviar-certificados" type="submit">Enviar certificados para</button>
            <?php echo  mb_strtolower($objL->getEMail()) ?>
            <input type="hidden" name="codInscrito" value="<?php echo $objL->getCodInscrito()?>">
        </form>     -->
        <a target='_blank' href='<?php
        echo "https://sac.opctec.net/atendimento?conversation_to=%2B55".limpaFoneWhats($objL->getFoneContatoDdd().$objL->getFoneContato())
        . "&text=Olá ".$objL->getPreNome()
        . " ";
        ?>
        '>Enviar mensagem via sac-opctec</a>
    <?php }

    public static function btnListarTrabalhos ($fws_, $objL, $debug = false) { ?>
        <a href="index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&cod_inscrito=<?php echo  $objL->getCodInscrito() ?>"target="_blank" title="Listar Trabalhos"> <?php echo  $objL->getTotalTrabalhos() ?></a>
    <?php }

    public static function btnNovoCadastro ($fws_, $objL, $debug = false) {
        echo  "<a href='".UrlInscView($objL)."/index.php?content=view/admin/inscritos-cadastro-ok.php'>" . "<img src='resources/image/fig-incluir.gif' border='0' width='20px'></a>";
    }

    public static function btnEnviarSenhaAdmin ($fws_, $objL, $debug = false) { ?>
        <form class="form-signin" name="form" method="POST"  target="_blank"
            action="<?php echo WB_URL_COMMAND?>">
            <input type="hidden" name="c" value="GerenciarLogin">
            <input type="hidden" name="m" value="recuperarSenhaAdmin">
            <button class="btn btn-xs btn-primary btn-enviar-senha-admin" type="submit">Enviar credencial</button>
            <?php echo  mb_strtolower($objL->getEMail()) ?>
            <input type="hidden" name="e_mail" id="e_mail" value="<?php echo  mb_strtolower($objL->getEMail()) ?>">
            <input type="hidden" name="codInscrito" value="<?php echo  $objL->getCodInscrito() ?>">
        </form>
    <?php }

    public static function btnAcessarAvaliacaoInscrito ($fws_, $objL, $debug = false) { ?>
        <?php if ($objL->getClassificacaoStatus() == '') { ?>
            <button class="btn btn-xs btn-primary btn-acessar-avaliacao-inscrito" style="margin-left: 10px;" type="button" onclick="acessarAvaliacaoInscrito(<?php echo  $objL->getCodInscrito() ?>)">Avaliar Inscrito</button>
        <?php }else{ ?>
            <?php
                $txBtnAvaliacao = 'Consultar Avaliação';
                $btnCor = 'orange'; 
                // if ($objL->getClassificacaoStatus() == '') {
                //     $txBtnAvaliacao = 'Consultar Avaliação';

                if ($objL->getClassificacaoStatusAposRecurso() == '') {
                    $btnCor = 'red'; 
                    $txBtnAvaliacao = 'Aguardando Avaliação Recurso';
                } else {
                    $txBtnAvaliacao = 'Consultar / Avaliar Recurso';
                }
                // }
            ?>
            <?php 
            $btnCor = 'green';
            $txBtnAvaliacao = 'Consultar Avaliação';
            ?>
            <?php //if($objL->getRecursoTrabalhoSolicitar()!=''){ ?>
                <button class="btn btn-xs btn-primary btn-consultar-avaliacao-inscrito" style="margin-left: 10px; background-color: <?php echo $btnCor;  ?>; border-color: <?php echo $btnCor;  ?>;" type="button" onclick="acessarAvaliacaoInscrito(<?php echo  $objL->getCodInscrito() ?>)"><?php echo $txBtnAvaliacao; ?></button>
            <?php } ?>
            <style>
                .btn-consultar-avaliacao-inscrito:hover {
                    border-color: orange;
                }
            </style>
        <?php //} ?>    
    <?php }
    

    public static function btnEnviarCertificados ($fws_, $objL, $debug = false) { ?>
        <form class="form-signin" name="form" method="POST"
                action="<?php echo WB_URL_COMMAND?>" style='padding:2px;' >
            <input type="hidden" name="c" value="GerenciarEmail">
            <input type="hidden" name="m" value="enviarCertificados">
            <button class="btn btn-xs btn-primary btn-enviar-certificados" type="submit">Enviar certificados por e-mail</button>
            <?php //echo  mb_strtolower($objL->getEMail()) ?>
            <input type="hidden" name="codInscrito" value="<?php echo $objL->getCodInscrito()?>">
        </form>
    <?php }

    public static function btnGerarCertificadoAtividade ($fws_, $objL, $debug = false) { ?>
        <form name="abrirCertificado" method="post" action="<?php echo WB_URL_ACTION?>?content=view/certificados/certificado.php" target="_blank">
            <input type="hidden" name="tipoCertificado" id="tipoCertificado" value="participou_atividade">
            <input type="hidden" name="cod_trabalho"    id="cod_trabalho" value="">
            <input type="hidden" name="cod_inscrito"    id="cod_inscrito" value="<?php echo $objL->getCodInscrito()?>">
            <input type="hidden" name="codInscrito"     value="<?php echo $objL->getCodInscrito()?>">
            <input type="hidden" name="cod_oficina"     id="cod_oficina"  value="<?php echo $objL->getCodOficina()?>">
            <input type="hidden" name="cod_evento"      id="cod_evento"   value="<?php echo $objL->getCodEvento()?>">
            <button class="btn btn-xs btn-primary btn-gerar-certificado-atividade" type="submit">Gerar Certificado Atividade</button>
        </form>
    <?php }

    public static function btnLembrarMensalidade ($fws_, $objL, $debug = false) { ?>
        <form class="form-signin" name="form" method="POST" target="_blank"
            action="<?php echo WB_URL_COMMAND?>"  style='padding:2px;'>
            <input type="hidden" name="c" value="GerenciarEmail">
            <input type="hidden" name="m" value="enviarAvisoMensalidade">
            <button class="btn btn-xs btn-primary btn-lembrar-mensalidade" type="submit">Lembrar Mensalidade por e-mail</button>
            <?php //echo  mb_strtolower($objL->getEMail()) ?>
            <input type="hidden" name="codInscrito" value="<?php echo $objL->getCodInscrito()?>">
        </form>
    <?php }

    public static function btnLembrarPagamento ($fws_, $objL, $debug = false) { ?>
        <form class="form-signin" name="form" method="POST" target="_blank"
            action="<?php echo WB_URL_COMMAND?>"  style='padding:2px;'>
            <input type="hidden" name="c" value="GerenciarEmail">
            <input type="hidden" name="m" value="enviarAvisoPagamento">
            <button class="btn btn-xs btn-primary btn-lembrar-pagamento" type="submit">Lembrar Pagamento por e-mail</button>
            <?php //echo  mb_strtolower($objL->getEMail()) ?>
            <input type="hidden" name="codInscrito" value="<?php echo $objL->getCodInscrito()?>">
        </form>
    <?php }

    public static function btnLembrarEnvioTrabalho ($fws_, $objL, $debug = false) { ?>
        <form class="form-signin" name="form" method="POST" target="_blank"
            action="<?php echo WB_URL_COMMAND?>">
            <input type="hidden" name="c" value="GerenciarEmail">
            <input type="hidden" name="m" value="enviarAvisoTrabalho">
            <button class="btn btn-xs btn-primary btn-lembrar-envio-trabalho" type="submit">Lembrar Envio de Trabalho</button>
            <?php echo  mb_strtolower($objL->getEMail()) ?>
            <input type="hidden" name="codInscrito" value="<?php echo $objL->getCodInscrito()?>">
        </form>
    <?php }

    public static function btnLiberarCertificados ($fws_, $objL, $debug = false) { ?>
        <form id="filtroInscritoPerfil" name="filtroInscritoPerfil" action="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarInscritoPerfil.php&dummy=" method="post" target="_blank">                                    
            <input type="hidden" name="cod_inscrito" value="<?php echo  $objL->getCodInscrito() ?>">
            <button class="btn btn-xs btn-primary btn-liberar-certificado" type="submit">Liberar Certificados</button>
        </form>
    <?php }

    // public static function btnTotalPaginas ($fws_, $numeroPaginas, $debug = false) {
    //     echo "<button id='paginarAvancar'>Mostrar + 1000</button> total de paginas ".$numeroPaginas;
    // }

    //  public static function btnChecarPresenca ($fws_, $objL, $checkedPresenca, $debug = false) { 
    //     $checkedPresenca = $objL->getFlgPresenca() ? "checked" : "";
    //     echo "<input id='presencaAtividade-".$objL->getCodTrabalho()."' class='presenca' style='float: left;'"
    //             . " onclick=\"alternarPresenca('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\""
    //             . " title='Marcar Presença/Ausência'"
    //             . $checkedPresenca
    //             . " name='presenca' style='display: inline-block;' type='checkbox'>";
    //     echo "<div id='statusPresenca-".$objL->getCodTrabalho()."' style='float: left;margin-left:10px;'>Presença no Minicurso: ".$fws_['cod_oficina']."</div><br><br>";
    //  }

    public static function btnChecarPresencaInscrito ($fws_, $objL, $checkedPresencaInscrito, $debug = false) { 
        echo "<input id='presencaInscrito-".$objL->getCodInscrito()."' class='presenca' style='float: left;'"
            . " onclick=\"javascript:alternarPresencaInscrito(".$objL->getCodInscrito().");\""
            . " title='Marcar Presença/Ausência'"
            . $checkedPresencaInscrito
            . " name='presenca' style='display: inline-block;' type='checkbox'>";
    }

    public static function btnChecarPresencaOficina ($fws_, $objL, $checkedPresencaOficina, $debug = false) {
        echo "<input id='presencaOficina-".$objL->getCodInscrito()."-".$objL->getCodOficina()."' class='presenca' style='float: left;'"
            . " onclick=\"javascript:alternarPresencaOficina(".$objL->getCodInscrito().",".$objL->getCodOficina().");\""
            . " title='Marcar Presença/Ausência em Oficina'"
            . $checkedPresencaOficina
            . " name='presencaOficina' style='display: inline-block;' type='checkbox'>";
    }

    public static function indicadorDocsContratacao($fws_, $objL, $debug = false) {
        $docsObrigatorios = [
            'ctps' => 'CTPS',
            'certidao_nascimento' => 'Certidão Nasc/Casam.',
            'foto_cracha' => 'Foto Crachá',
            'certidao_antecedentes' => 'Antecedentes',
        ];
        if ($objL->getSexo() == 'MASCULINO') {
            $docsObrigatorios['certificado_reservista'] = 'Reservista';
        }

        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objL->getCodInscrito());
        $objTrabalho->setCodEvento($objL->getCodEvento());
        $entregues = $objTrabalho->getSlugsDocumentosEntregues(array_keys($docsObrigatorios), $debug);

        $totalObrigatorios = count($docsObrigatorios);
        $totalEntregues = count($entregues);

        $faltantes = [];
        foreach ($docsObrigatorios as $slug => $label) {
            if (!in_array($slug, $entregues)) {
                $faltantes[] = $label;
            }
        }

        if ($totalEntregues >= $totalObrigatorios) {
            echo '<div style="margin-top:4px;font-size:12px;color:green;font-weight:bold;">'
               . 'Docs Contratação: ' . $totalEntregues . '/' . $totalObrigatorios . ' &#10003;'
               . '</div>';
        } else {
            echo '<div style="margin-top:4px;font-size:12px;color:red;font-weight:bold;">'
               . 'Docs Contratação: ' . $totalEntregues . '/' . $totalObrigatorios
               . '<br><span style="font-weight:normal;font-size:11px;">Falta: ' . implode(', ', $faltantes) . '</span>'
               . '</div>';
        }
    }

    public static function btnc () {

    }

    public static function btnd () {

    }
}
