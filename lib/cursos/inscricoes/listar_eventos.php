<h3>Lista de Eventos</h3>  
    <div class="panel-group">
        <div class="panel panel-default">
        <div class="panel-body">
                <?php
                try {
                    if($organizacao->getPrefixo()=='opctec'){
                        throw new Exception('opctec');
                    }
                    try {
                        $organizacao->carregarListaEventosByGestor($debug);
                        echo "<br>";
                        echo "<ul>";
                        $aoMenosUm = 0;
                        foreach ($organizacao->getListaEventos() as $objL) {
                            $prefixoEvento = $organizacao->getPrefixo().'/'.$objL->getCodEvento();
                            if($objL->getPrefixo!=''){
                                $prefixoEvento = $objL->getPrefixo();
                            }
                            if ($objL->getStatus()=='ATIVADO' AND $objL->getVisibilidade()=='PUBLICO'){
                                $aoMenosUm = 1;
                                echo '<li><a href="'.$PROTOCOLO.'://'.FWA_Dominio().'/'.$prefixoEvento.'/index.php">'.$objL->getTitulo().'</a></li>';
                            }
                        }
                        if ($aoMenosUm!=1){
                            echo "<ul><li>Gestor não possui projeto ativo</li></ul>";
                        }
                        echo "</ul>";
                                                
                    } catch (Exception $exc) {
                        echo "<ul><li>".$exc->getMessage()."</li></ul>";
                    }
                } catch (Exception $exc) {
                    /*lista geral*/
                    $organizacao->carregarListaEventos($debug);
                    echo "<br>";
                    echo "<ul>";
                    $gestorAnt = '';
                    foreach ($organizacao->getListaEventos() as $objL) {
                        if($objL->getUrlPrefixo()!=''){
                            $prefixoEvento = $objL->getUrlPrefixo();
                        }else{
                            $prefixoEvento = $organizacao->getPrefixo().'/'.$objL->getCodEvento();
                        }
                        if ($gestorAnt != $objL->getGestor()){
                            echo '<br><li>'
                            . '<a style="line-height: 40px" href="'.$PROTOCOLO.'://'.FWA_Dominio().'/'.$objL->getGestor().'/eventos">'
                            . $objL->getGestor().'</a></li>';
                        }
                        if ($objL->getStatus()=='ATIVADO' AND $objL->getVisibilidade()=='PUBLICO'){
                            echo '<a style="padding: 15px" href="'.$PROTOCOLO.'://'.FWA_Dominio().'/'.$prefixoEvento.'/">'.$objL->getTitulo().'</a><br style="line-height: 25px">';
                        }
                        $gestorAnt = $objL->getGestor();
                    }
                    echo "</ul>";

                }

                ?>
        </div>
    </div>
</div>
      

<script language="JavaScript1.2" src="resources/js/default.js"></script>
<script LANGUAGE="JAVASCRIPT">
//------------------------------------------------------------------------

    function criticar() {

        // testa se o email foi preenchido e se está no formato correto
        if (!isEmail(document.form.e_mail.value)) {
            alert("O email deve ser preenchido no formato correto (usuário@dominio).");
            document.form.e_mail.focus();
            return false;
        }
    }
// -->

</SCRIPT>