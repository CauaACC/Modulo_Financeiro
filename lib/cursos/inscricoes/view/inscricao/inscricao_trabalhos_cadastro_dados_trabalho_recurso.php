<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
$Conexao_db = FWA_Conexao("OpcTec");
$fws_['cod_trabalho'] = $fws_['cod_trabalho']==''? $_GET['cod_trabalho'] : $fws_['cod_trabalho'];
$debug = false;
If (!$OP_SUBMISSAO_TRABALHO) {
    $msg = "Envio de trabalho bloqueado!";
    $_SESSION['MSG']=$msg;
    $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
    echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
    exit();
}
?>
<h3>Área do Inscrito</h3>  
<div class="panel-group">
   <?php
    try {
        require_once 'inscricao_congresso_cabecalho.php';
        
        $obj = new Trabalho();
        if($fws_['cod_trabalho']!=''){
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            $obj->getTrabalhoById($debug);
            $fws_['cod_area_tematica'] = $obj->getCodAreaTematica();
            $fws_['formato_texto'] = $obj->getFormatoTexto();
            $fws_['formato_apresentacao'] = $obj->getFormatoApresentacao();
        }
        ?>
    <!--<form name="form" method="POST" action="view/inscricao/inscricao_trabalhos_grava.php"  onsubmit="return criticar();">-->
    <form name="form" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticar();" enctype="multipart/form-data"  >
        <input type="hidden" name="c" value="GerenciarInscricao">
        <input type="hidden" name="m" value="salvarTrabalho">
        <input type="hidden" name="cod_trabalho" value="<?php echo $obj->getCodTrabalho()?>">
        <input type="hidden" name="tipo_documento" value="RECURSO">
        
    <div class="panel panel-default">
        
        <div class="panel-heading"><h4>Apresentação de Recurso: <?php echo $objEvConf->getDescTrabAnexoTipo() ?></h4></div>
        <div class="panel-body"> 
            <?php if ($obj->getCodTrabalho()) {?>
            <label for="titulo_trabalho">Código do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>: <?php echo  $obj->getCodTrabalho() ?></label>
            <br>
            <?php }?>
            <?php
            if($fws_['msg']){
                FWA_Mostrar_Mensagem($fws_['msg']);
            }
            ?> 
            <?php if ($objEvConf->getTrabTitulo()==1){ ?>
                <br>
                <label for="titulo_trabalho">Titulo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>:</label>
                <input type="text" id="titulo_trabalho" name="titulo_trabalho" value="<?php echo  $obj->getTituloTrabalho() ?>"
                       class="form-control" placeholder="Titulo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>" required autofocus>
                <br>
            <?php } ?>
                                   
            <?php if($objEvConf->getTrabAvaliacaoPermitirRecurso()==1 and $objEvConf->getTrabAvaliacaoPermitirRecursoAnexo()==1){ ?>
                <br>
                <h4>
                    <?php if($objEvConf->getCodEvento()==70 or $objEvConf->getTrabResumoArquivoSemIdentificacao()==1){?>
                    Anexar Arquivo Sem Identificação: 
                    <?php }else if ($objEv->getCodEvento()==80){ ?>
                    Anexar Um Único Arquivo que contenha todos os documentos: 
                    <?php }else{ ?>
                    Anexar Arquivo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>: 
                    <?php } ?>
                </h4>
                <input type="file" name="arquivo" id="arquivo" value="<?php echo  $obj->getArquivo() ?>"
                       placeholder="Inserir arquivo    " required>             
                    <span style="font-size:12px;">Extensões de arquivo permitidas são: 
                        <?php
                        if ($objEvConf->getCodEvento() == 70){
                            echo  'doc ou docx';
                        }else if ($objEvConf->getTrabArquivoExtensao()!=''){
                            echo $objEvConf->getTrabArquivoExtensao();
                        }else{
                            echo 'doc, docx ou rtf.';
                        }  
                        ?></span>
                <br>
            <?php }?>
            <?php
            if ($objEvConf->getTrabAvaliacaoPermitirRecurso()==1){
                $maxCaracTrabalhoRecurso = 2000;
                ?><br>
                <!-- <h4>Solicitação de Recurso:</h4> -->
                <lable>Descreva abaixo os argumentos com os quais contesta a decissão sobre sua inscrição?</label>
                (máximo <?php echo $maxCaracTrabalhoRecurso ?> caracteres, com espaços)
                <br><font id="DigitadoRecurso" color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id="RestanteRecurso" color=red><?php echo $maxCaracTrabalhoRecurso ?></font>
                <textarea id="recurso_trabalho" name="recurso_trabalho" rows="15" cols="80" maxlength="<?php echo $maxCaracTrabalhoRecurso ?>"
                          onkeyup="MaximoCaracter(this, <?php echo $maxCaracTrabalhoRecurso ?>,'DigitadoRecurso','RestanteRecurso')" onkeypress="MaximoCaracter(this, <?php echo $maxCaracTrabalhoRecurso ?>,'DigitadoRecurso','RestanteRecurso')"
                          class="form-control" placeholder="Digite suas argumentações para o recurso aqui" required autofocus><?php echo  $obj->getRecursoTrabalho() ?></textarea>
                <br>
                <br><lable>Deseja realmente solicitar recurso?</label>
                <br>
                <input type="radio" name="recurso_trabalho_solicitar" value="1" <?= verifItemChecked($obj->getRecursoTrabalhoSolicitar(), '1', false) ?> /> Sim, Confirmo essa solicitação de recurso
                <br>
                <input type="radio" name="recurso_trabalho_solicitar" value="0" <?= verifItemChecked($obj->getRecursoTrabalhoSolicitar(), array('', 0, false), false) ?> /> Não, não quero solicitar recurso
                <br>
            <?php }?>
               
                <br>
                <!-- Animação de carregamento -->
                <div id="loadingAnimation" style="display: none;">
                    <div class="spinner"></div> <!-- O spinner vai aparecer durante o envio -->
                    <div class="message">Enviando arquivo...</div>
                </div>                
            <!--<div class='btn-group'>-->
                <!--<button class='btn btn-xs btn-primary' type="submit">Avançar</button>-->
                <button class='btn btn btn-primary' onclick="showLoadingAnimation()" type="submit">
                    <?php //echo $objEvConf->getTxtBtnSalvarTrabalho() ?>
                    Clique aqui para salvar Pedido de Recurso
                </button>
                <button class='btn btn-xs btn-primary' type="reset">Limpar</button>
                <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"
                   class="btn btn-xs btn-primary" role="button" style="color: #fff">Voltar</a>
            <!--</div>-->
            </div>
        </div>
        <?php
    } catch (Exception $exc) {
        echo FWA_Mostrar_Mensagem($exc->getMessage());
    }
    ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <br>
        </div>
    </div>
</div>
</form>
<script language="JavaScript1.2" src="resources/js/default.js"></script>
<script language="JavaScript" type="text/javascript">
    $(document).ready(function () {
        $("#criterios_table tr:odd").css("background-color", "#f2f2f2");
        $("#criterios_table tr:even").css("background-color", "#e6e6e6");

        let reminderTime = 30 * 60 * 1000; // 30 minutos
        function showReminder() {
    //        Ops, notamos que você não está interagindo com a página.
            alert("Salve seu progresso para não perder suas informações.");
        }
        // Inicializa o timer quando a página é carregada
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(showReminder, reminderTime);
        });

        function criticar() {
            // document.getElementById('loadingAnimation').style.display = 'block';
        //    if (document.form.autor_apresentador_nome.value == "") {
        //        alert("O campo Nome Autor Apresentador não está preenchido!");
        //        document.form.autor_apresentador_nome.focus();
        //        return false;
        //    }

        //    // testa se o email foi preenchido e se está no formato correto
        //    if (!isEmail(document.form.autor_apresentador_e_mail.value)) {
        //        alert("O email deve ser preenchido no formato correto (usuário@dominio).");
        //        document.form.autor_apresentador_e_mail.focus();
        //        return false;
        //    }
        //
        //    if (document.form.instituicao_trabalho.value == "") {
        //        alert("O campo Instituição do trabalho não está preenchido!");
        //        document.form.instituicao_trabalho.focus();
        //        return false;
        //    }
        //
        //    if (document.form.titulo_trabalho.value == "") {
        //        alert("O campo Titulo Trabalho não está preenchido!");
        //        document.form.titulo_trabalho.focus();
        //        return false;
        //    }
        //
        //    if (document.form.palavras_chave.value == "") {
        //        alert("O campo Palavras-Chave não está preenchido!");
        //        document.form.palavras_chave.focus();
        //        return false;
        //    }

        //    if (document.form.resumo_trabalho.value == "") {
        //        alert("O campo Resumo Trabalho não está preenchido!");
        //        document.form.resumo_trabalho.focus();
        //        return false;
        //    }
                // Chama a função de exibição da animação de carregamento
                // showLoadingAnimation();
            return true;
        }
            
        <?php if(TRABALHO_ANEXA_RESUMO){?>   
        form.onsubmit = function(event) {
            const tiposPermitidos = ["application/pdf"];
            const descTiposPermitidos = "<?php echo $objEvConf->getTrabArquivoExtensao()!=''?  $objEvConf->getTrabArquivoExtensao() : 'doc, docx ou rtf.';?>";
        //        const tiposPermitidos = [
        //            "application/pdf", 
        //            "application/msword", 
        //            "application/vnd.openxmlformats-officedocument.wordprocessingml.document", 
        //            "application/rtf"
        //        ];
            if (arquivo.files.length === 0) {
                alert("Atenção, sua inscrição será salva, mas está faltando anexar um arquivo contendo seus comprovantes e documentos pessoais")
    //            alert("Para salvar os dados é necessário anexar o arquivos.");
    //            event.preventDefault(); // Impede a submissão
    //            return;
            }
    //        if (!(arquivo.files.length === 0)) {
    //        if (!tiposPermitidos.includes(arquivo.files[0].type)) {
    //            alert("Tipo de arquivo não permitido. Tipos permitidos ("+descTiposPermitidos+") .");
    //            event.preventDefault(); // Impede a submissão
    //        }
    //        }
        }        
        <?php } ?>
        
    });

    function showLoadingAnimation() {
    // alert(1);
    // Exibe a animação de carregamento
    <?php if ($objEvConf->getTrabAvaliacaoPermitirRecursoAnexo()==1){ ?>
        document.getElementById('loadingAnimation').style.display = 'block';
        // Configura o tempo máximo de envio para 2 minutos (120 segundos)
        timeout = setTimeout(function() {
            alert("Tempo limite excedido! Por favor, diminua o tamanho do arquivo.");
            hideLoadingAnimation();
        }, 120000); // 120000ms = 2 minutos    
    <?php } ?>
    }
    // Função para esconder a animação de carregamento
    function hideLoadingAnimation() {
        document.getElementById('loadingAnimation').style.display = 'none';
        clearTimeout(timeout); // Cancela o timeout se o processo terminar antes dos 2 minutos
    }
</script>
<style>
         #loadingAnimation {
            margin-top: 20px;
            text-align: center;
            font-size: 16px;
            display: none; /* Inicialmente escondido */
        }

        .pulsing-circle {
            width: 50px;
            height: 50px;
            background-color: #3498db;
            border-radius: 50%;
            animation: pulse 1.5s infinite ease-in-out;
            margin: 0 auto;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Estilo da mensagem */
        .message {
            margin-top: 10px;
            font-size: 18px;
            color: #3498db;
        }
    </style>
