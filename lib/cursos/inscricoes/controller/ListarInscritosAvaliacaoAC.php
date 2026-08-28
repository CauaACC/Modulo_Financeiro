<?php

class ListarInscritosAvaliacaoAC {
    
    public static function colunaSituacaoAvaliacao($fws_, $objL, $objEvConf, $debug) {
        // echo $objL->getIdInclusao() != ''
        //     ? '<span style="color: green;">AVALIADO</span>'
        //     : '<span style="color: red;">NÃO AVALIADO</span>';
        if ($objL->getClassificacaoStatus()==''){
            echo '<div class="alert alert-warning" role="alert">Atenção: Sem classificação</div>';
        }else{
            if ($objL->getClassificacaoStatus() == 'H') {
                echo '<br><span style="color: green;">Habilitado 1º Fase</span>';
            }
            if ($objL->getClassificacaoStatus() == 'I') {
                echo '<br><span style="color: red;">Inabilitado 1º Fase</span>';
            }
        }
        if ($objL->getRecursoTrabalhoSolicitar()==''){
            if ($objEvConf && $objEvConf->getTrabAvaliacaoPermitirRecurso()) {
                echo '<br><span style="color: black;">Não Solicitou Recurso</span>';
            }
        }else{
            if ($objL->getClassificacaoStatusAposRecurso() == '') {
                echo '<div class="alert alert-warning" role="alert">Atenção: Falta Classificação após recurso</div>';
            }else{
                if ($objL->getClassificacaoStatusAposRecurso()!=$objL->getClassificacaoStatus()){
                    echo '<br><span style="color: red;">Classificação alterada após recurso</span>';
                    //echo '<br>De:'.$objL->getClassificacaoStatus();
                    echo '<br>Alterado Para:'.$objL->getClassificacaoStatusAposRecurso();
                }
                // echo $objL->getPontosTotalAposRecurso().'-';
                if (
                    ($objL->getPontosTotalConfirmado()!=$objL->getPontosTotalAposRecurso())
                    //  and ($objL->getPontosTotalConfirmado()!='')
                    //  and ($objL->getPontosTotalAposRecurso()!='')
                ){
                    echo '<br><span style="color: red;">Pontuação alterada após recurso</span>';
                    echo '<br>De:'.$objL->getPontosTotalConfirmado();
                    echo '<br>Para:'.$objL->getPontosTotalAposRecurso();                
                }
                echo "<br><b>Classificação Final:</b> ";
                ListarInscritosAvaliacaoAC::descClassificacaoFinal($objL);
                echo '<br>';            
            }
        }
    }

    public static function detalhesAvaliacao($fws_, $objL, $objEvConf, $debug) {
        ?>
        <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)) { ?>
            <?php if ($objEvConf && $objEvConf->getTrabAvaliacao()) { ?>
                <br>
                <span style="margin-left: 0px;font-size:15px;font-weight: bold;">Situação Avaliação:</span>
                <?php
                if ($objL->getClassificacaoStatus()==''){
                    echo 'Não avaliado';
                }else{
                    /** @todo avaliação trabalho, precisamos trabalhar isso para avaliçaõ de resumos */
                    /** @todo Avaliação de Inscrito */
                    echo $objL->getResumoTrabalho()? '<br>Descrição: ' . $objL->getResumoTrabalho() : '';
                    if ($objEvConf && $objEvConf->getTrabAvaliacaoComplementar()) {
                        //echo '<br>Pontos Informados: ' . $objL->getPontosInformadosTrabalho();
                        echo '<br>Pontos Confirmados: ' . $objL->getPontosTotalConfirmado();
                    }
                    echo '<br>Classificação Status: ';
                    if ($objL->getClassificacaoStatus() == 'H') {
                        echo '<span style="color: green;">Habilitado</span>';
                    }
                    if ($objL->getClassificacaoStatus() == 'I') {
                        echo '<span style="color: red;">Inabilitado</span>';
                    }
                    echo '<hr><b>Parecer do avaliador:</b> ';
                    echo $objL->getParecerAvaliador();
                }
                // echo "<br><b>Clique em consultar para verificar e reavaliar o recurso</b>";
                if ($objEvConf && $objEvConf->getTrabAvaliacaoPermitirRecurso()) {
                    if($objL->getRecursoTrabalhoSolicitar()!=''){
                        echo '<hr><b><font color=red>Recurso Solicitado:</font></b>';
                        echo '<br><b>Descrição do Recurso:</b> ';
                        if($objL->getRecursoTrabalhoTxt()!=''){
                            echo formatDateView($objL->getDataInclusaoRecurso());
                            echo "<br>".$objL->getRecursoTrabalhoTxt();
                        }else if($objL->getRecursoTrabalho()!=''){
                            echo $objL->getRecursoTrabalho();
                        }else{
                            echo $objL->getTextoRecursoInscrito();
                        }
                    }
                    
                    if ($objL->getParecerAvaliadorAposRecurso() != '') {
                        echo '<hr><b>Parecer do avaliador Após Recurso:</b> ';
                        echo '<br>'.$objL->getParecerAvaliadorAposRecurso();
                    }
                    if ($objL->getClassificacaoStatusAposRecurso() == 'H') {
                        echo '<br><span style="color: green;">Habilitado após Recurso<?b></span>';
                    }
                    if ($objL->getClassificacaoStatusAposRecurso() == 'I') {
                        echo '<br><span style="color: red;"><b>Inabilitado após Recurso</b></span>';
                    }           
                }
                echo '<br>';
                ?>
                <?php CarregarBotoesAC::btnAcessarAvaliacaoInscrito($fws_, $objL, $debug); ?>

                <?php 
                if ($objL->getIdInclusaoAvaliacao() != '') {
                    echo '<br><b>1º Avaliação:</b> ' . $objL->getIdInclusaoAvaliacao();
                    echo ' - <b>Data:</b> ' . formatDateView($objL->getDataInclusaoAvaliacao());
                }
                if ($objL->getIdAlteracaoAvaliacao() != '') {
                    echo '<br><b>Última Alteração:</b> ' . $objL->getIdAlteracaoAvaliacao();
                    echo ' - <b>Data:</b> ' . formatDateView($objL->getDataAlteracaoAvaliacao());
                }                  
                ?>
            <?php } ?>
        <?php } ?>
        <?php
    }

    public static function descClassificacaoFinal($objL){
        if($objL->getRecursoTrabalhoSolicitar()!=''){
            if ($objL->getClassificacaoStatusAposRecurso() == 'H') {
                echo '<br><span style="color: green;"><b>Recurso:Habilitado no Recurso<?b></span>';
            }
            if ($objL->getClassificacaoStatusAposRecurso() == 'I') {
                echo '<br><span style="color: red;"><b>Inabilitado no Recurso</b></span>';
            }
        }else{
            $objL->getClassificacaoStatus();
            if ($objL->getClassificacaoStatus() == 'H') {
                echo '<br><span style="color: green;"><b>Habilitado</b></span>';
            }
            if ($objL->getClassificacaoStatus() == 'I') {
                echo '<br><span style="color: red;"><b>Inabilitado</b></span>';
            }
        }
    }
}