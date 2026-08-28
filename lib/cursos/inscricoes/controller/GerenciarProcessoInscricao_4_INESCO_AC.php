<?php
class GerenciarProcessoInscricao_4_INESCO_AC {

    public static function passo_01($fws_, $debug = false){
//        if($fws_['op_selecionar_oficina']){
            $proximoPasso = "index.php?content=view/inscricao/inscricao_congresso_oficina_seleciona.php";
//        }else{
//            $proximoPasso = "index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php";
//        }
        ?>
        <form method="POST" name="form" action="<?php echo $proximoPasso?>" onsubmit="return criticar()">
            <input type="hidden" name="e_mail"      value="<?php echo $fws_['e_mail']?>">
            <input type="hidden" name="cpf"         value="<?php echo $fws_['cpf']?>">
            
            <table width="100%" class="table-striped" style="font-size: 13px;line-height: 2.429;" cellspacing="6" cellpadding="6" border="0">
            <?php
            echo "<tr valign='top'><td colspan=4><h4>Selecione uma das Categorias:</h4></td></tr>";
            try {
                $obj = new Perfil();
                try{
                    $obj->carregarListaPerfil($debug);
                }catch (Exception $exc) {
                    throw new Exception("Nenhuma categoria de inscrição encontrada para este evento.");
                }
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

                        //echo ($ii%2 == 0? "<tr valign='top' bgcolor='{$bgcolor}'>" : "");
                        echo "<tr valign='top' style='line-height: 1.7;'>";
                        echo " <td width='10px'>";
                        if($inscrito_apto_liberado == true){
                            echo "<INPUT TYPE='radio' NAME='cod_perfil' id='cod_perfil' VALUE='{$objL->getCodPerfil()}'  style='margin:10px;'>";
                        }else{ echo "&nbsp;"; }

                        echo "</td><td width='100%'>";
                        echo "<h4 class='corTitulo'>{$objL->getTitulo()}</h4>";
//                        echo "<h4 class='corTitulo'>{$objL->getTitulo()}</h4>{$objL->getInvestimento()}";
                        echo "</td>";

                        //echo ($ii%2 != 0? "</tr>" : "");
                        echo "</tr>";
                        $ii++;
                        if(count($obj->getListaPerfil())==1){
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
            } catch (Exception $exc) {
                echo "<tr valign='top'><td colspan=4>";
                echo FWA_Mostrar_Mensagem($exc->getMessage());
                echo "</td></tr>";
            }
            echo "</table>";
            ?>
            <br>

            <button class="btn btn-xs btn-primary" type="submit">Avançar</button>
            <button class="btn btn-xs btn-primary" type="reset">Limpar</button>
            <a href="index.php?content=view/inscricao/inscricao_01.php" class="btn btn-xs btn-primary" role="button">Voltar</a>

        </form>
    <?php
    }
}
?>
