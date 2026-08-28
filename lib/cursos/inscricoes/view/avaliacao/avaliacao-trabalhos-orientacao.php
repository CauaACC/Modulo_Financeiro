<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
<h3>Área do Avaliador</h3>  
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php require_once(dirname(__FILE__)."/../../view/avaliacao/avaliacao-trabalhos-cabecalho.php"); ?>
        </div>
        <div class="panel-body">
            
            <h3>Orientações para leitura e análise dos resumos</h3>
            <br>
            <ol type="1">
                <?php if (COD_EVENTO==81) { ?>
                    <?php require_once(dirname(__FILE__)."/../../view/avaliacao/avaliacao-trabalhos-orientacao-81.php"); ?>
                <?php } else { ?>    
                <li>
                    <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                       margin-bottom:6.0pt;margin-left:10pt"><span style="mso-ansi-language: PT-BR">
                            A análise dos resumos deverá levar em consideração:
                        </span>
                    <ol type="a">
                        <li>
                            <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                               margin-bottom:6.0pt;margin-left:23pt;text-indent:-18.0pt;tab-stops:list 54.0pt"><span style="mso-ansi-language: PT-BR">
                                    A clareza dos objetivos                                    
                                </span></li>                        
                        <li>
                            <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                               margin-bottom:6.0pt;margin-left:23pt;text-indent:-18.0pt;tab-stops:list 54.0pt"><span style="mso-ansi-language: PT-BR">
                                    Metodologia Adequação
                                </span></li>
                        <li>
                            <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                               margin-bottom:6.0pt;margin-left:23pt;text-indent:-18.0pt;tab-stops:list 54.0pt"><span style="mso-ansi-language: PT-BR">
                                   Resultado e Discussão Relevante
                                </span></li>
                        <li>
                            <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                               margin-bottom:6.0pt;margin-left:23pt;text-indent:-18.0pt;tab-stops:list 54.0pt"><span style="mso-ansi-language: PT-BR">
                                     Aderência ao Eixo Temático, se for o caso segerir alteração
                                </span></li>
                        <li>
                            <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                               margin-bottom:6.0pt;margin-left:23pt;text-indent:-18.0pt;tab-stops:list 54.0pt"><span style="mso-ansi-language: PT-BR">
                                    E se atendeu as normas
                                </span></li>
                    </ol>
                </li>
                <?php } ?>
                <li>
                    <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                       margin-bottom:6.0pt;margin-left:10pt"><span style="mso-ansi-language: PT-BR">
                            Após análise do resumo, o avaliador fará a opção on-line por:
                        </span>
                    <ol type="a">
                        <li>
                            <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                               margin-bottom:6.0pt;margin-left:23pt;text-indent:-18.0pt;tab-stops:list 54.0pt"><span style="mso-ansi-language: PT-BR">
                                     Aprovar
                                </span></li>
                        <li>
                            <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                               margin-bottom:6.0pt;margin-left:23pt;text-indent:-18.0pt;tab-stops:list 54.0pt"><span style="mso-ansi-language: PT-BR">
                                    Não aprovar (sendo necessária justificativa logo abaixo)
                                </span></li>
                    </ol>
                </li>
                <li>
                    <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                       margin-bottom:6.0pt;margin-left:10pt"><span style="mso-ansi-language: PT-BR">
                            As situações não previstas nas orientações serão resolvidas pela Comissão Científica
                        </span></li>
                <li>
                    <p class="RefeRenciaBibliogr" style="margin-top:6.0pt;margin-right:0cm;
                       margin-bottom:6.0pt;margin-left:10pt"><span style="mso-ansi-language: PT-BR">
                            <a href="https://docs.google.com/document/d/157LnMQoAGZZxc0C17dnBr6ox1Vca6T2Y-RjpIUSNezg/edit?usp=sharing" target="_blank">Manual do sistema</a>
                        </span></li>
            </ol>
            
            <p><p><p><p><p><p>&nbsp;
        </div>
    </div>
</div>

