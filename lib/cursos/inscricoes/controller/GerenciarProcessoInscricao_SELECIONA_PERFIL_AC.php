<?php
class GerenciarProcessoInscricao_SELECIONA_PERFIL_AC {

    public static function passo_01($fws_, $debug = false){
        if($fws_['op_selecionar_oficina']){
            $proximoPasso = "index.php?content=view/inscricao/inscricao_congresso_oficina_seleciona.php";
        }else{
            $proximoPasso = "index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php";
        }
        $objEvConf = new EventoConfiguracao();
        $objEvConf->setCodEvento(COD_EVENTO);
        $objEvConf->getEventoConfiguracaoById($debug);
        ?>
        <!--<div class="panel-heading"><h4>Selecione uma das Categoria</h4></div>-->
        </div>
        <form method="POST" name="form" action="<?php echo $proximoPasso?>" onsubmit="return criticar()">
            <input type="hidden" name="e_mail"      value="<?php echo $fws_['e_mail']?>">
            <input type="hidden" name="cpf"         value="<?php echo $fws_['cpf']?>">

        <table width="100%" class="table-striped" style="font-size: 13px;line-height: 2.429;" cellspacing="6" cellpadding="6" border="0">
            <?php if ($objEvConf->getMsgPerfilAtivar()==1) { ?>
                <tr valign='top'>
                    <td colspan=4>
                        <div style="line-height: 1.4;margin-left: 10px;padding-bottom: 15px;">
                            <?php echo $objEvConf->getMsgPerfilTexto() ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            <?php if (COD_EVENTO == '28-old') { ?>
                <tr valign='top'>
                    <td colspan=4>
                        <blockquote>
                        <span style="font-size: 16px;font-family: Helvetica;font-weight: bold;">Valor Inscrição:</span>
                        <table border="0" width="100%" style="font-size: 11px;line-height: 1.429;" >
                            <tr><th width="120px">Lotes/Períodos</th>
                                <th width="50px">Congresso <!--<br> (apenas quinta e sexta)--></th>
                                <th width="50px">Workshop Individual</th>
                                <th width="120px">Pacote para profissionais (Congresso<br> + 3 workshops)</th>
                                <th width="120px">Pacote para alunos de graduação, pós graduação e associados ABFA (Congresso + 3 workshops) – já com desconto de 50 %</th></tr>
                            <tr><td>1° Lote 05/06 até 09/07</td><td>R$ 170,00</td><td>80,00 (10 vagas)</td><td>R$ 360,00</td><td>R$ 205,00</td></tr>
                            <tr><td>2° Lote 10/07 até 13/08</td><td>R$ 140,00</td><td>70,00 (10 vagas)</td><td>R$ 440,00</td><td>R$ 245,00</td></tr>
                            <tr><td>2° Lote 14/08 até 20/09</td><td>R$ 210,00</td><td>120,00 (10 vagas)</td><td>R$ 520,00</td><td>R$ 285,00</td></tr>
                        </table>
                        </blockquote>
                    </td>
                </tr>
            <?php } ?>
               <?php if (COD_EVENTO == 30) { ?>
            <?php

                    $objDesconto = new Desconto();
                    $fws_['cpf'] = removerEspacos($fws_['cpf']);
                    $fws_['cpf'] = removerHifen($fws_['cpf']);
                    $fws_['cpf'] = removerPontos($fws_['cpf']);
                    $objDesconto->setCpf($fws_['cpf']);
                    $objDesconto->cerregarDescontoTotal($debug);
                    if($objDesconto->getQtdDesconto()>0){
            ?>
            <tr valign='top'>
                <td colspan=4>
                    <blockquote>
                    <span style="font-size: 16px;font-family: Helvetica;font-weight: bold;">
                        Ola <?php echo $objDesconto->getPreNomeDesconto()?>
                        <br>Parabéns! Você está cadastrado(a) na lista de beneficiados por desconto no valor da inscrição
                        <br><font color=green>O valor fixo de R$ <?php echo formatMoneyView($objDesconto->getValorFixo())?> será aplicado para sua inscrição em qualquer uma das categorias abaixo.</font>
                        <br>Prossiga com sua inscrição normalmente, e o desconto será aplicado automaticamente ao final do processo.
                    </span>
                    </blockquote>
                </td>
            </tr>
            <?php } ?>
            <?php } ?>
            <tr valign='top'>
                <td colspan=4>
                <h4 style="margin: 15px 5px 15px 10px;">Selecione uma das Opções:</h4>
                </td>
            </tr>
            <?php
            try {
                $obj = new Perfil();
                try{
                    $obj->carregarListaPerfilInscricao($debug);
                }catch (Exception $exc) {
                    throw new Exception("Nenhum grupo ou categoria de inscrição disponível.");
                }
                $ii = 2;
                $totalPerfilApto = 0;
                foreach ($obj->getListaPerfil() as $objL) {
                    if($objL->getReceberInscricao()==1){

                        if($ii%2 == 0){
                            if ($bgcolorant=="#F4F4F4"){
                               $bgcolor="#FFFFFF";
                            }else{
                               $bgcolor="#F4F4F4";
                            }
                            $bgcolorant=$bgcolor;
                        }
                        /*todo mundo esta apto até que se prove o contrário*/
                        $inscrito_apto_liberado = true;
                        if ($objL->getVerificarInscritoApto() == true) {
                            $inscrito_apto_liberado = false;
                            if ($inscritoSocio == true) {
                                $inscrito_apto_liberado = true;
                            } else {
                                $objInscritoApto = new InscritoApto();
                                $objInscritoApto->setCpf($fws_['cpf']);
                                /*verificar se inscrito apto por perfil ou geral*/
                                $objInscritoApto->setCodPerfil($objL->getCodPerfil());

                                /**quando false, verifica se apto sem consultar o perfil, ou seja libera para todos os perfis, e se true só libera o perfil apto */
                                $condicoes['verificarAptoPorPerfil'] = false;
                                $condicoes['verificarAptoPorPerfil'] = $objEvConf->getVerificarAptoPorPerfil();
                                $objInscritoApto->carregarInscritoAptoByCpf($condicoes, $debug);
                                if ($objInscritoApto->getCodInscrito()!='') {
                                    $inscrito_apto_liberado = true;
                                }else{
                                    $inscrito_apto_liberado = false;
                                }
                            }
                        }
                        $vagas_disponíveis = true;
                        if($objL->getVerificarVagasLimitadas()==1){
                            $vagasOcupadas = $objL->getTotalInscritosPerfil1()+$objL->getTotalInscritosPerfil2();
                            if($objL->getVagas()<=$vagasOcupadas){
                                $vagas_disponíveis = false;
                            }
                        }

                        //echo ($ii%2 == 0? "<tr valign='top' bgcolor='{$bgcolor}'>" : "");
                        echo "<tr valign='top' style='line-height: 0.7;'>";
                        echo " <td width='10px'>";
                        if($inscrito_apto_liberado == true and $vagas_disponíveis == true){
                            $totalPerfilApto = $totalPerfilApto + 1;
                            echo "<INPUT TYPE='radio' required NAME='cod_perfil' id='cod_perfil' VALUE='{$objL->getCodPerfil()}'  style='margin:15px;margin-top:18px;' require>";
                        }else{
                            echo "&nbsp;";
                        }

                        echo "</td><td width='100%'>";
                        echo "<h4 class='corTitulo' style='margin-bottom: 5px;'>{$objL->getTitulo()}</h4>";
                        if ($inscrito_apto_liberado==false){
                            echo "<span style='color:red;'>Opção não disponível</span>";
                        }
                        if(!$vagas_disponíveis){
                            echo "<span style='color:red;'>Todas as vagas preenchidas</span>";
                        }
                        echo $objL->getInvestimento()? "<span style='line-height: 1.4;'>".$objL->getInvestimento()."</span>" : "";
                        echo $objL->getDescricao()? "<span style='line-height: 1.4;'>".$objL->getDescricao()."</span>" : "";
                        // echo "<br>&nbsp;</td>";

                        //echo ($ii%2 != 0? "</tr>" : "");
                        echo "</tr>";
                        $ii++;
//                        echo '-'.$totalPerfiLApto.'-';
                        if(count($obj->getListaPerfil())==1 and $totalPerfilApto!=0 and $vagas_disponíveis==true){
                            $submitAutomatico = 1;
                            ?>
                            <script  type="text/javascript">
                                $(document).ready(function () {
                                    $("#cod_perfil").attr('checked', 'checked');
                                    document.form.submit();
                                });
                            </script>
                            <?php
                            echo "ARGUARDE REDIRECIONANDO...";
                        }

                    }
                    if($ii == 0){
                        throw new Exception("Nenhum perfil de inscrição habilitado para receber inscrição.");
                    }
                }
                if($totalPerfilApto<=0 and $vagas_disponíveis==true){
                    if(COD_EVENTO==42){
                        $msgAviso = " – habilitados e convocados";
                    }else{
                        $msgAviso = " – turma fechada";
                    }
                    throw new Exception("O CPF informado: ".$fws_['cpf'].", não encontrado na lista de participantes pré-selecionados".$msgAviso."."
                            . "<br>Confirme o CPF digitado ou entre em contato com a coordenação do evento para verificar");
                }
            } catch (Exception $exc) {
                echo "<tr valign='top'><td colspan=4>";
                echo FWA_Mostrar_Mensagem($exc->getMessage());
                echo "</td></tr>";
            }
            echo "</table>";
            ?>
            <div style="margin:20px 15px 15px; padding-top:15px; border-top:1px solid #eee; display:flex; gap:8px; flex-wrap:wrap;">
                <?php  if ($totalPerfilApto!=0) {?>
                <button class="btn btn-primary modalBtnSalvar" type="submit">Avançar</button>
                <button class="btn btn-default modalBtnCancelar" type="reset">Limpar</button>
                <?php  } ?>
                <a href="index.php?content=view/inscricao/inscricao_01.php"
                   class="btn btn-default modalBtnCancelar">Voltar</a>
            </div>
        </form>

    <?php
    }
}
