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
        <input type="hidden" name="trabalho_tipo" value="<?php echo $objEvConf->getTrabAnexoTipo()?>">
        
    <div class="panel panel-default">
        
        <div class="panel-heading"><h4>Cadastro de <?php echo $objEvConf->getDescTrabAnexoTipo() ?></h4></div>
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
            <!--<div class='btn-group'>-->
            <?php if ($naoMostrar) { ?>
                <?php if($OP_AVALIACAO_ENVIAR_AO_SALVAR){ ?>
                    <button class='btn btn btn-primary' type="submit">Salvar e Enviar para Avaliação</button>
                <?php }else {?>
                    <button class='btn btn btn-primary' type="submit">Salvar <?php echo $objEvConf->getDescTrabAnexoTipo() ?></button>
                <?php } ?>
                <button class='btn btn-xs btn-primary' type="reset">Limpar</button>
                <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"
                       class="btn btn-xs btn-primary" role="button" style="color: #fff">Voltar</a>
                <!--</div>-->
                <br>
            <?php } ?>
            
            <?php if(COD_EVENTO==4){ ?>
                <br>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    Aviso: O não <b>pagamento da inscrição</b> com submissão de trabalho <b>até o dia 15/05/2020</b>, cancela automaticamente a apresentação do trabalho neste evento.
                </div>
            <?php } ?>
            <?php if(COD_EVENTO==11){ ?>
                <br>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    Aviso: O não <b>pagamento do envio do trabalho</b> até o dia 26/02/2023, cancela automaticamente a apresentação do trabalho neste evento.
                </div>
            <?php } ?>
            <?php
            if ($objEvConf->getTrabComplementoForm()==1) {
                echo '<div class="form-group col-sm-12" style="padding-left: 0px;padding-top:10px">';
                echo $objEvConf->getTrabComplementoFormText();
                echo '</div>';
            } ?>
            <?php if ($objEvConf->getTrabTitulo()==1){ ?>
                <br>
                <label for="titulo_trabalho">Titulo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>:</label>
                <input type="text" id="titulo_trabalho" name="titulo_trabalho" value="<?php echo  $obj->getTituloTrabalho() ?>"
                       class="form-control" placeholder="Titulo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>" required autofocus>
                <br>
            <?php } ?>
            
            <?php if($objEvConf->getTrabFormatoApresentacao()==true or $objEvConf->getCodEvento()==70){?>
                <br>
                <label for="formato_apresentacao">Forma da Apresentação:</label>
                <select class='form-control' name='formato_apresentacao' id='formato_apresentacao' required autofocus>
                    <option class='form_campos' value='' selected>Informe Formato do Apresentação</option>
                    <?php
                    CarregarOptionsAC::formatoApresentacao($fws_)
                    ?>
                </select>
            <?php } ?>              
            <?php if($objEvConf->getTrabFormatoTexto()==true){?>
                <br>
                <label for="format_texto">Tipo do <?php echo $objEvConf->getDescTrabAnexoTipo() ?>:</label>
                <select class='form-control' name='formato_texto' id='formato_texto' required autofocus>
                    <option class='form_campos' value='' selected>Informe Formato do Texto</option>
                    <?php
                    CarregarOptionsAC::formatoTexto($fws_)
                    ?>
                </select>
            <?php } ?>              
            <?php if($objEvConf->getTrabPremiacao()==true){?>
                <br>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="premiacao">Deseja concorrer ao <?php echo $objEvConf->getTrabPremiacaoTitulo()? $objEvConf->getTrabPremiacaoTitulo()."?" : "PRÊMIO?"; ?>
                </label> 
                </div>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                    <div class="col-sm-6">
                        <input type='radio' name='premiacao' value='NAO'
                        <?php echo verifItemChecked($obj->getPremiacao(), 'NAO') ?> >
                        Não desejo participar.
                    </div>
                    <div class="col-sm-6">
                        <input type='radio' name='premiacao' value='SIM'
                        <?php echo verifItemChecked($obj->getPremiacao(), 'SIM') ?> >
                        Sim, desejo participar.
                    </div>
                </div>
                <br>
            <?php }?>
            <?php if((COD_EVENTO!=11 and COD_EVENTO!=33) and ($objEvConf->getTrabEixoTematico()==1)){ ?>
                <br>
                <label for="cod_area_tematica">Eixos Temáticos:
                    <?php if(COD_EVENTO==2){?>
                    Iluminando caminhos para o futuro da saúde:
                    <?php }?>
                </label>
                <select class='form-control' name='cod_area_tematica' id='cod_area_tematica' required autofocus>
                    <option class='form_campos' value='' selected>Selecione uma Eixos Temáticos</option>
                    <?php 
                    CarregarOptionsAC::areasTematicas($fws_);
                    ?>
                </select>
                <br>
            <?php } ?>
            <?php if ($objEvConf->getTrabNumCoautores()>0) { ?>
                <label >Nomes dos Co-Autores: </label>
                (O número total de autores, incluindo o autor principal, não deve exceder <?php echo $objEvConf->getTrabNumCoautores() ?>)
                <?php if ($objEvConf->getCodEvento()==70) { ?>
                    Apresentador: 
                    <input type="text" id="nome_co_autor_<?php echo $i ?>" name="autor_apresentador_nome" value="<?php echo $obj->getAutorApresentadorNome(); ?>"
                    class="form-control" placeholder="Autor Apresentador">
                <?php }else{ ?>
                    <br>&nbsp;&nbsp;&nbsp;1º Autor/Apresentador - <?php echo $nome_inscrito?>
                <?php } ?>
                <?php                    
                for ($i = 1; $i < $objEvConf->getTrabNumCoautores(); $i++) {
                ?>
                    <input type="text" id="nome_co_autor_<?php echo $i ?>" name="nome_co_autor_<?php echo $i ?>" value="<?php echo  call_user_func([$obj, "getNomeCoAutor" . $i]) ?>"
                    class="form-control" placeholder="<?php echo $i+1 ?>º Co-autor">
                <?php
                }
                ?>
                <!--
                <label for="nome_co_autor_2" class="sr-only">1º Co-autor</label>
                <input type="text" id="nome_co_autor_2" name="nome_co_autor_2" value="<?php echo  $obj->getNomeCoAutor2() ?>"
                       class="form-control" placeholder="3º Co-autor">

                <label for="nome_co_autor_3" class="sr-only">3º Co-autor</label>
                <input type="text" id="nome_co_autor_3" name="nome_co_autor_3" value="<?php echo  $obj->getNomeCoAutor3() ?>"
                       class="form-control" placeholder="4º Co-autor">

                <label for="nome_co_autor_4" class="sr-only">4º Co-autor</label>
                <input type="text" id="nome_co_autor_4" name="nome_co_autor_4" value="<?php echo  $obj->getNomeCoAutor4() ?>"
                       class="form-control" placeholder="5º Co-autor">

                <label for="nome_co_autor_5" class="sr-only">5º Co-autor</label>
                <input type="text" id="nome_co_autor_5" name="nome_co_autor_5" value="<?php echo  $obj->getNomeCoAutor5() ?>"
                       class="form-control" placeholder="6º Co-autor">
                
                <label for="nome_co_autor_6" class="sr-only">6º Co-autor</label>
                <input type="text" id="nome_co_autor_6" name="nome_co_autor_6" value="<?php echo  $obj->getNomeCoAutor6() ?>"
                       class="form-control" placeholder="7º Co-autor">
                
                <label for="nome_co_autor_7" class="sr-only">7º Co-autor</label>
                <input type="text" id="nome_co_autor_7" name="nome_co_autor_7" value="<?php echo  $obj->getNomeCoAutor7() ?>"
                       class="form-control" placeholder="8º Co-autor">
                -->
            * nomes idênticos serão descartados.
            <br>
            <?php } ?>
            <?php if ($objEvConf->getTrabInstituicao()==1) { ?>
                <br>
                <label>Instituição:</label>
                <input type="text" id="instituicao_trabalho" name="instituicao_trabalho" value="<?php echo  $obj->getInstituicaoTrabalho() ?>"
                       class="form-control" placeholder="Instituição" required autofocus>
                <br>
            <?php } ?>
            <?php if ($objEvConf->getTrabPalavrasChave()==1) { ?>
                <label>Palavras-chave: </label> (máximo 3 palavras-chave, separadas por um sinal de 'ponto e virgula', ou seja ';')
                <input type="text" id="palavras_chave" name="palavras_chave" value="<?php echo  $obj->getPalavrasChave() ?>"
                       class="form-control" placeholder="Palavras chave" required autofocus>             
                <br>
            <?php } ?>
            <?php if(TRABALHO_ANEXA_RESUMO and ($objEvConf->getTrabAvaliacaoPermitirRecurso()!=1)){?>
                <?php //if ($objEvConf->getTrabAnexoTipo()=='Termo de Concessão de Bolsa') {?>
                <br>
                <h4>
                    <?php if($objEvConf->getCodEvento()==103  or $objEvConf->getTrabResumoArquivoSemIdentificacao()==1){?>
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
                <?php //}?>
            <?php }?>
            <?php if($objEvConf->getCodEvento()==103 or $objEvConf->getTrabResumoArquivoSemIdentificacao()==1){?>
                <br>
                <h4>Anexar Arquivo Identificado: </h4>
                <input type="file" name="arquivo_identificado" id="arquivo_identificado" value="<?php echo  $obj->getArquivoIdentificado() ?>"
                       placeholder="Inserir arquivo Identificado   " required>             
                    <span style="font-size:12px;">Extensões de arquivo permitidas são: <?php echo $objEvConf->getTrabArquivoExtensao()!=''?  $objEvConf->getTrabArquivoExtensao() : 'doc, docx ou rtf.';?></span>
            <?php }?>
            <?php if(TRABALHO_ANEXA_RESUMO_FORMULARIO and ($objEvConf->getTrabAvaliacaoPermitirRecurso()!=1)){?>
                <?php
                if(COD_EVENTO==106){
                    $maxCaracTrabalho = 4000;
                    $placeHolder = "Descreva seus argumentos.";
                }else{
                    $maxCaracTrabalho = 2500;
                    $placeHolder = "Resumo Trabalho";
                }
                ?>
                <br>
                <?php if (COD_EVENTO==106) {?>
                <h4>Descreva seus argumentos: </h4> (máximo <?php echo $maxCaracTrabalho ?> caracteres, com espaços)
                <?php }else {?>
                <h4>Resumo: </h4> (máximo <?php echo $maxCaracTrabalho ?> caracteres, com espaços)
                <?php }?>
                <br><font id=Digitado color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id=Restante color=red><?php echo $maxCaracTrabalho ?></font>
                <textarea id="resumo_trabalho" name="resumo_trabalho" rows='15' cols='80' maxlength='<?php echo $maxCaracTrabalho ?>'
                          onkeyup='MaxCaracter(this, <?php echo $maxCaracTrabalho ?>)' onkeypress='MaxCaracter(this, <?php echo $maxCaracTrabalho ?>)'
                          class="form-control" placeholder="<?php echo $placeHolder ?>" required autofocus><?php echo  $obj->getResumoTrabalho() ?></textarea>
                <br>
            <?php }?>
            <?php
            if ($objEvConf->getTrabResumoReferencia()==1 and ($objEvConf->getTrabAvaliacaoPermitirRecurso()!=1)){
                $maxCaracTrabalhoRef = 2000;
                ?>
                <h4>Referências: </h4> (máximo <?php echo $maxCaracTrabalhoRef ?> caracteres, com espaços)
                <br><font id="DigitadoRef" color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id="RestanteRef" color=red><?php echo $maxCaracTrabalhoRef ?></font>
                <textarea id="referencia_trabalho" name="referencias_trabalho" rows="15" cols="80" maxlength="<?php echo $maxCaracTrabalhoRef ?>"
                          onkeyup="MaximoCaracter(this, <?php echo $maxCaracTrabalhoRef ?>,'DigitadoRef','RestanteRef')" onkeypress="MaximoCaracter(this, <?php echo $maxCaracTrabalhoRef ?>,'DigitadoRef','RestanteRef')"
                          class="form-control" placeholder="Referências Trabalho" required autofocus><?php echo  $obj->getReferenciasTrabalho() ?></textarea>
                <br>
            <?php }?>
            <?php
            if ($objEvConf->getTrabAvaliacaoPermitirRecurso()==1){
                $maxCaracTrabalhoRecurso = 2000;
                ?><br>
                <h4>Solicitação de Recurso:</h4>
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
                <?php if($OP_AVALIACAO_ENVIAR_AO_SALVAR){ ?>
                    <button class='btn btn btn-primary' onclick="showLoadingAnimation()" type="submit">
                        Salvar e Enviar para Avaliação
                    </button>
                <?php }else {?>
                    <button class='btn btn btn-primary' onclick="showLoadingAnimation()" type="submit">
                        <?php echo $objEvConf->getTxtBtnSalvarTrabalho() ?>
                    </button>
                <?php } ?>
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
    <?php if ($objEvConf->getTrabComplementoForm()==1) {  ?>
    <?php
    if ($objEvConf->getTrabAvaliacaoCriterioQtd()!='') {
        echo "var qtdCriterios = ".$objEvConf->getTrabAvaliacaoCriterioQtd();    
    }else{
        echo "var qtdCriterios = 12";
    }
    ?>
        // Adicione listeners apenas para os campos 'criterio'
        for (let i = 1; i <= qtdCriterios; i++) {
            //document.getElementById('criterio_' + i).addEventListener('input', () => calculatePoints(i));
            let elemento = document.getElementById('criterio_' + i);
            if (elemento) { // Verifica se o elemento existe
                elemento.addEventListener('input', () => calculatePoints(i));
            }
        }
        function calculatePoints(index) {
            let criterioValue = parseFloat(document.getElementById('criterio_' + index).value) || 0;
            let pesoValue = parseFloat(document.getElementById('peso_' + index).value) || 0; // Mesmo que peso seja fixo, precisamos pegar seu valor
            document.getElementById('pontos_' + index).value = criterioValue * pesoValue;
            updateTotal(); // Chame esta função toda vez que calcular os pontos
        }

        function updateTotal() {
            let total = 0;
            for (let i = 1; i <= qtdCriterios; i++) {
                let elemento = document.getElementById('pontos_' + i);
                if (elemento) { // Verifica se o elemento existe
                    total += parseFloat(elemento.value) || 0;
                }
            }
            document.getElementById('pontos_total').value = total;
        }
    
        <?php if ($obj->getCriteriosRespostas()!='') { ?>
            var dados = <?php echo $obj->getCriteriosRespostas()? $obj->getCriteriosRespostas() : ''; ?>;
            for (var chave in dados) {
                var campo = document.querySelector('input[name="' + chave + '"]');
                if (campo) {
                    campo.value = dados[chave];
                }
            }
        <?php } ?>
        <?php if ($obj->getCriteriosPesos()!='') { ?>    
            var dados = <?php echo $obj->getCriteriosPesos()? $obj->getCriteriosPesos() : ''; ?>;
            for (var chave in dados) {
                var campo = document.querySelector('input[name="' + chave + '"]');
                if (campo) {
                    campo.value = dados[chave];
                }
            }
        <?php } ?>
        <?php if ($obj->getCriteriosPontos()!='') { ?>
            var dados = <?php echo $obj->getCriteriosPontos()? $obj->getCriteriosPontos() : ''; ?>;
            for (var chave in dados) {
                var campo = document.querySelector('input[name="' + chave + '"]');
                if (campo) {
                    campo.value = dados[chave];
                }
            }
        <?php } ?>
        updateTotal()    
    <?php } ?>
        
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
    /*
    document.getElementById('loadingAnimation').style.display = 'block';
            // Configura o tempo máximo de envio para 2 minutos (120 segundos)
            timeout = setTimeout(function() {
                alert("Tempo limite excedido! Por favor, diminua o tamanho do arquivo.");
                hideLoadingAnimation();
            }, 120000); // 120000ms = 2 minutos    
            */
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
