<?php
class GerenciarProcessoInscricao_9_CBMP_AC {

    public function passo_01($fws_, $debug = false){
        ?>
            <!--<form method="POST" name="form" action="index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php" onsubmit="return criticar()">-->
            <form method="POST" name="form" action="index.php?content=view/inscricao/inscricao_congresso_oficina_seleciona.php" onsubmit="return criticar()">
                <input type="hidden" name="e_mail"      value="<?php echo $fws_['e_mail']?>">
                <input type="hidden" name="cpf"         value="<?php echo $fws_['cpf']?>">
            
            <table width="100%" style="font-size: 13px;line-height: 2.429;" cellspacing="6" cellpadding="6" border="0">
                <tr valign="top" bgcolor="#FFFFFF">
                    <td colspan="4" style="font-size: 13px;line-height: 1.429;">
                        <b>Leia com atenção:</b>
                        <br>Valor unitário de um minicurso R$ 60,00
                        <br>Valor unitário de uma refeição R$ 49,00 (almoço com refrigerante ou água mineral + sobremesa).
                        <br>Valor do minicurso e de refeições não estão inclusos nos valores descritos abaixo.
                        <br>*Para seleção de perfis de graduação ou pós-graduação, será necessário comprovação de matricula na retirada do material.
                        <br>Para se inscrever como associado é necessário estar em dia com sua anuidade.
                        <br>&nbsp;

                        <?php
                        echo "
                        <br><font color='red'>".$FWS_Data_Max_Submissao_Trabalho_Mensagem.": " . formatDateView($FWS_Data_Max_Submissao_Trabalho) . "</font>
                        ";

                        $objInscrito = new Inscrito();

                        $objInscrito->setEMail($fws_['e_mail']);
                        $objInscrito->setCpf(formatCpfInsert($fws_['cpf']));
                        $inscritoSocio = false;

                            if (!$objInscrito->verificarCpfInscritoApto($debug)) {
                                echo "<font color=red size=3>";

                                echo "<br>Seu cpf: ".formatCpfView($fws_['cpf']).", não consta na lista de associados
                                ou pode não estar em dia com sua anuidade.</font>";

                                echo "<br>Links para SBMP: | &nbsp;&nbsp;<a href='http://186.233.153.194:8080/cgi-bin/WebProges.cgi/NovoSocioEtapa1'
                                    target='blank'>Associe-se</a>";
                                echo "&nbsp;&nbsp; | &nbsp;&nbsp;<a href='http://186.233.153.194:8080/cgi-bin/WebProGes.cgi/AtuCadSocioInicio'
                                    target='blank'>Atualize seu cadastro</a>";
                                echo "&nbsp;&nbsp; | &nbsp;&nbsp;<a href='http://www.sbmp.org.br/servico-consulta'
                                    target='blank'>Consulta débito</a>";
                                echo "<br></font>&nbsp;";
                                ?>
                                <br><a href="index.php?content=view/inscricao/inscricao_01.php" class="btn btn-ds btn-primary" role="button">clique aqui para informar outro cpf</a><br>
                                <?php
                            }else{
                                echo "<font color=#006633 size=3>";
                                echo "<br>O cpf informado: ".formatCpfView($fws_['cpf']).", consta na lista de associados.";
                                echo "</font>";
                                $inscritoSocio = true;
                            }
                        ?>
                    </td>
                </tr> 
            <?php
            try {
                $obj = new Perfil();
                try{
                    $obj->carregarListaPerfil($debug);
                }catch (Exception $exc) {
                    throw new Exception("Nenhuma categoria de inscrição encontrada para este evento.");
                }
                echo "<tr valign='top'><td colspan=4><h4>Selecione uma das Categorias:</h4></td></tr>";
                $ii = 2;
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
                        $inscrito_apto_liberado = true;
                        if ($objL->getVerificarInscritoApto() == true) {
                            if ($inscritoSocio == true) {
                                $inscrito_apto_liberado = true;
                            } else {
                                $inscrito_apto_liberado = false;
                            }
                        }

                        echo ($ii%2 == 0? "<tr valign='top' bgcolor='{$bgcolor}'>" : "");
                        //echo "<tr valign='top' bgcolor='{$bgcolor}'>";
                        echo " <td width='10px'>";
                        if($inscrito_apto_liberado == true){
                            echo "<INPUT TYPE='radio' NAME='cod_perfil' VALUE='{$objL->getCodPerfil()}'  style='margin:10px;'>";
                        }else{ echo "&nbsp;"; }

                        echo "</td><td width='50%'>";

    //                    if($inscrito_apto_liberado == false){
    //                        echo "Seu e-mail não consta na lista de associados<br>";
    //                        echo "<a href='http://www.sbmp.org.br/regionais'
    //                            target='blank'>clique aqui para verificar sua situação como associado</a><br>";
    //                    }

                        echo "<h4 class='corTitulo'>{$objL->getTitulo()}</h4>{$objL->getInvestimento()}";

                        echo "</td>";

                        echo ($ii%2 != 0? "</tr>" : "");
                        //echo "</tr>";
                        $ii++;
                    }
                }
            } catch (Exception $exc) {
                echo "<tr valign='top'><td colspan=4>";
                echo FWA_Mostrar_Mensagem($exc->getMessage());
                echo "</td></tr>";
            }
                echo "</table>";
            ?>
<!--
                    <tr valign="top" bgcolor="#F4F4F4">
                        <td width="10px">
                            <input name="cod_perfil" value="2709" style="margin:10px;" type="radio">
                        </td>
                        <td width="100%">
                            <h4>Profissional Sócio</h4>
                            <table class='tb-valor'>
                                <tr><td width="180" height="10">Até 20/06/2017          </td><td>R$ 360,00 </td></tr>
                                <tr><td width="180" height="10">De 21/06 até 28/07/2017 </td><td>R$ 450,00 </td></tr>
                                <tr><td width="180" height="10">No Congresso            </td><td>R$ 580,00 </td></tr>
                                <tr><td width="180" height="10"></td>                   <td></td></tr>
                             </table>
                        </td>
                    </tr>     
                    <tr valign="top" bgcolor="#FFFFFF">
                        <td width="10px">
                            <input name="cod_perfil" value="2719" style="margin:10px;" type="radio">
                        </td>
                        <td width="100%">
                            <h4>Profissional Não Sócio</h4>
                            <table class='tb-valor'>
                                <tr><td width="180" height="10">Até 20/06/2017          </td><td>R$ 600,00 </td></tr>
                                <tr><td width="180" height="10">De 21/06 até 28/07/2017 </td><td>R$ 750,00 </td></tr>
                                <tr><td width="180" height="10">No Congresso            </td><td>R$ 960,00 </td></tr>
                                <tr><td width="180" height="10"></td>                   <td></td></tr>
                             </table>
                        </td>
                    </tr>            
                    <tr valign="top" bgcolor="#F4F4F4">
                        <td width="10px">
                            <input name="cod_perfil" value="2718" style="margin:10px;" type="radio">
                        </td>
                        <td width="100%">
                            <h4>Estudante de Pós-Graduação Sócio</h4>
                            <table class='tb-valor'>
                                <tr><td width="180" height="10">Até 20/06/2017          </td><td>R$ 180,00 </td></tr>
                                <tr><td width="180" height="10">De 21/06 até 28/07/2017 </td><td>R$ 225,00 </td></tr>
                                <tr><td width="180" height="10">No Congresso            </td><td>R$ 288,00 </td></tr>
                                <tr><td width="180" height="10"></td>                   <td></td></tr>
                             </table>
                        </td>
                    </tr>            
                    <tr valign="top" bgcolor="#FFFFFF">
                        <td width="10px">
                            <input name="cod_perfil" value="2717" style="margin:10px;" type="radio">
                        </td>
                        <td width="100%">
                            <h4>Estudante de Pós-Graduação Não Sócio</h4>
                            <table class='tb-valor'>
                                <tr><td width="180" height="10">Até 20/06/2017          </td><td>R$ 290,00 </td></tr>
                                <tr><td width="180" height="10">De 21/06 até 28/07/2017 </td><td>R$ 360,00 </td></tr>
                                <tr><td width="180" height="10">No Congresso            </td><td>R$ 464,00 </td></tr>
                                <tr><td width="180" height="10"></td>                   <td></td></tr>
                             </table>
                        </td>
                    </tr>            
                    <tr valign="top" bgcolor="#F4F4F4">
                        <td width="10px">
                            <input name="cod_perfil" value="2716" style="margin:10px;" type="radio">
                        </td>
                        <td width="100%">
                            <h4>Estudante de Graduação Sócio</h4>
                            <table class='tb-valor'>
                                <tr><td width="180" height="10">Até 20/06/2017          </td><td>R$ 125,00 </td></tr>
                                <tr><td width="180" height="10">De 21/06 até 28/07/2017 </td><td>R$ 156,00 </td></tr>
                                <tr><td width="180" height="10">No Congresso            </td><td>R$ 200,00 </td></tr>
                                <tr><td width="180" height="10"></td>                   <td></td></tr>
                             </table>
                        </td>
                    </tr>            
                    <tr valign="top" bgcolor="#FFFFFF">
                        <td width="10px">
                            <input name="cod_perfil" value="2713" style="margin:10px;" type="radio">
                        </td>
                        <td width="100%">
                            <h4>Estudante de Graduação Não Sócio</h4>
                            <table class='tb-valor'>
                                <tr><td width="180" height="10">Até 20/06/2017          </td><td>R$ 230,00 </td></tr>
                                <tr><td width="180" height="10">De 21/06 até 28/07/2017 </td><td>R$ 287,00 </td></tr>
                                <tr><td width="180" height="10">No Congresso            </td><td>R$ 368,00 </td></tr>
                                <tr><td width="180" height="10"></td>                   <td></td></tr>
                             </table>
                        </td>
                    </tr>            
                    <tr valign="top" bgcolor="#F4F4F4">
                        <td width="10px">
                            <input name="cod_perfil" value="2712" style="margin:10px;" type="radio">
                        </td>
                        <td width="100%">
                            <h4>Acompanhante</h4>
                            <table class='tb-valor'>
                                <tr><td width="180" height="10">Até 20/06/2017          </td><td>R$ 130,00 </td></tr>
                                <tr><td width="180" height="10">De 21/06 até 28/07/2017 </td><td>R$ 130,00 </td></tr>
                                <tr><td width="180" height="10">No Congresso            </td><td>R$ 130,00 </td></tr>
                                <tr><td width="180" height="10"></td>                   <td></td></tr>
                             </table>
                        </td>
                    </tr>            

                </tbody></table>-->
                <br>
                
                <button class="btn btn-xs btn-primary" type="submit">Avançar</button>
                <button class="btn btn-xs btn-primary" type="reset">Limpar</button>
                <a href="index.php?content=view/inscricao/inscricao_01.php" class="btn btn-xs btn-primary" role="button">Voltar</a>

            </form>
        <?php
    }
}
?>
