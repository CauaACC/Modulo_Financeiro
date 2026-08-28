<table border='0' width='570'  cellspacing="10" cellpadding="10" class="dataTable">
    <tr><td colspan="2"><b>Configuração de Mensagens</b></td></tr>
    <tr>
        <td colspan="2">Mensagem de Texto acima do campo CPF para substituir o texto padrão
            <br> "Para iniciar inscrição informe CPF.":
            <br> <input type='text' name='msg_btn_iniciar_inscricao_tela_acima' value='<?php echo $objEvConf->getMsgBtnIniciarInscricaoTelaAcima() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
    </tr>
    <tr>
        <td colspan="2">Substituir o texto do botão "inicar inscrição" por:
            <br><input type='text' name='msg_btn_iniciar_inscricao' value='<?php echo $objEvConf->getMsgBtnIniciarInscricao() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
    </tr>
    <tr><td colspan="2">
    Ativar Mensagem em Selecionar Perfil:     
        <input type="radio" name="msg_perfil_ativar" value="1" <?= verifItemChecked($objEvConf->getMsgPerfilAtivar(), '1', false) ?> /> Sim 
        &nbsp;&nbsp;
        <input type="radio" name="msg_perfil_ativar" value="0" <?= verifItemChecked($objEvConf->getMsgPerfilAtivar(), array('', 0, false), false) ?> /> Não
    </td></tr>                
    <tr><td colspan="2"> <br><span style="font-size:15px;font-weight: bold;">Mensagens/Avisos na Tela de Login:</span> </td></tr>
    <tr><td colspan="2">

    <div id="tabsMsgTela">
        <ul>
            <li class="txtAba"><a href="#tabsMsgTelaAntes">Antes de iniciar inscrição</a></li>
            <li class="txtAba"><a href="#tabsMsgTelaDurante">Durante inscrição</a></li>
            <li class="txtAba"><a href="#tabsMsgTelaDuranteRealizacao">Durante Realização</a></li>
            <li class="txtAba"><a href="#tabsMsgTelaDepoisEncerrado">Depois Encerramento</a></li>
            <li class="txtAba"><a href="#tabsMsgTelaSelecionarPerfil">Ao Selecionar Perfil</a></li>
        </ul>

        <?php 
        $tabId     = "tabsMsgTelaAntes";
        $nomeCampo = "msg_tela_antes";
        $label = "";
        EditorTexto::renderizar($tabId, $nomeCampo, $label, $objEvConf);
        ?>

        <?php 
        $tabId     = "tabsMsgTelaDurante";
        $nomeCampo = "msg_tela_durante";
        $label = "";
        EditorTexto::renderizar($tabId, $nomeCampo, $label, $objEvConf);
        ?>

        <?php 
        $tabId     = "tabsMsgTelaDuranteRealizacao";
        $nomeCampo = "msg_avisos";
        $label = "";
        EditorTexto::renderizar($tabId, $nomeCampo, $label, $objEvConf);
        ?>

        <?php 
        $tabId     = "tabsMsgTelaDepoisEncerrado";
        $nomeCampo = "msg_tela_depois";
        $label = "";
        EditorTexto::renderizar($tabId, $nomeCampo, $label, $objEvConf);
        ?>

        <?php 
        $tabId     = "tabsMsgTelaSelecionarPerfil";
        $nomeCampo = "msg_perfil_texto";
        $label = "";
        EditorTexto::renderizar($tabId, $nomeCampo, $label, $objEvConf);
        ?>
    </div>

    </td></tr>        

</table>

