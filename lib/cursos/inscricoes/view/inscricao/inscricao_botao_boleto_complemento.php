<?php if ($objPerfil->getcodPerfil() == 329){ ?>
     <? if ($objInscrito->getTipoInscricao() == 'ESTUDANTE GRADUAÇÃO') { ?>
    <!-- INICIO DO BOTAO PAGSEGURO --><a href="https://pag.ae/7-DkoiVSa/button" target="_blank" title="Pagar com PagSeguro"><img src="//assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagSeguro - é rápido, grátis e seguro!" /></a><!-- FIM DO BOTAO PAGSEGURO -->
    <?php }else if ($objPedido->getValorTotal()=='85.00'){ ?>
    <!-- INICIO DO BOTAO PAGSEGURO --><a href="https://pag.ae/7-DkSF2hP/button" target="_blank" title="Pagar com PagSeguro"><img src="//assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagSeguro - é rápido, grátis e seguro!" /></a><!-- FIM DO BOTAO PAGSEGURO -->
    <?php
    } else {
        echo $objPerfil->getComplemento();
    }
?>
<?php /**@todo implementar opção de persnolizar botão do pagSeguro para valores especificos ou pessoas */ ?>
<?php }else if ($objPedido->getValorTotal()=='100.00'){ ?>
<!-- INICIO DO BOTAO PAGBANK --><a href="https://pag.ae/81eeub22Q/button" target="_blank" title="Pagar com PagBank"><img src="https://assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagBank - é rápido, grátis e seguro!" /></a><!-- FIM DO BOTAO PAGBANK -->
<?php }else if ($objPedido->getValorTotal()=='10000.00'){ ?>
<!-- INICIO DO BOTAO PAGBANK --><a href="https://pag.ae/7_-1Tq9TM/button" target="_blank" title="Pagar com PagBank"><img src="https://assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagBank - é rápido, grátis e seguro!" /></a><!-- FIM DO BOTAO PAGBANK -->
<?php
} else if ($objPerfil->getcodPerfil() == 330 and $objInscrito->getTipoInscricao() == 'ESTUDANTE GRADUAÇÃO') {
?>
    <!-- INICIO DO BOTAO PAGSEGURO --><a href="https://pag.ae/7-DkueReq/button" target="_blank" title="Pagar com PagSeguro"><img src="//assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagSeguro - é rápido, grátis e seguro!" /></a><!-- FIM DO BOTAO PAGSEGURO -->
<?php
} else if ($objPerfil->getcodPerfil() == 346 and $objInscrito->getTipoInscricao() == 'ESTUDANTE GRADUAÇÃO') {
?>
    <!-- INICIO DO BOTAO PAGSEGURO --><a href="https://pag.ae/7-DkwnQr8/button" target="_blank" title="Pagar com PagSeguro"><img src="//assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagSeguro - é rápido, grátis e seguro!" /></a><!-- FIM DO BOTAO PAGSEGURO -->
<?php
} else if ($objPerfil->getcodPerfil() == 345 and $objInscrito->getTipoInscricao() == 'ESTUDANTE GRADUAÇÃO') {
?>
    <!-- INICIO DO BOTAO PAGSEGURO --><a href="https://pag.ae/7-Dkw_p7J/button" target="_blank" title="Pagar com PagSeguro"><img src="//assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagSeguro - é rápido, grátis e seguro!" /></a><!-- FIM DO BOTAO PAGSEGURO -->
<?php
} else {
    /**desconto para fotobiomolecular */
    if ($objInscrito->getCodEvento()==65 and in_array($objInscrito->getCpf(), ['08968058911','12129807941','09338022935','03923467109','46347269805','03284572996','11187550906'])) {
    ?>
    <a href="https://pag.ae/7-NySWBTQ" target="_blank" title="Pagar com PagSeguro"><img src="//assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagSeguro!" />
    <?php
    }else if($objInscrito->getCodInscrito()==27901){
        ?>
<!-- INICIO DO BOTAO PAGSEGURO --><a href="https://pag.ae/7-DEzSnVq/button" target="_blank" title="Pagar com PagSeguro"><img src="//assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagSeguro - é rápido, grátis e seguro!" /></a><!-- FIM DO BOTAO PAGSEGURO -->
        <?php
    }else{
        if($objInscrito->getcodPerfil() == 382){
            ?>
            <script src="https://www.paypal.com/sdk/js?client-id=BAA84DJcrTArOoZQPAACXuBn2VINORu-0l7NtbkI6KEceHQhh8-fGZ98rk7IFo6y-5-rA24yE_AgEHRSjY&components=hosted-buttons&disable-funding=venmo&currency=BRL"></script>
            <div id="paypal-container-22JC4FWW8NL6Q"></div>
            <script>
            paypal.HostedButtons({
                hostedButtonId: "22JC4FWW8NL6Q",
            }).render("#paypal-container-22JC4FWW8NL6Q")
            </script>
            <?php
        }else if($objInscrito->getcodPerfil() == 383){
            ?>
            <script src="https://www.paypal.com/sdk/js?client-id=BAA84DJcrTArOoZQPAACXuBn2VINORu-0l7NtbkI6KEceHQhh8-fGZ98rk7IFo6y-5-rA24yE_AgEHRSjY&components=hosted-buttons&disable-funding=venmo&currency=BRL"></script>
            <div id="paypal-container-248BY6SDSR8HQ"></div>
            <script>
            paypal.HostedButtons({
                hostedButtonId: "248BY6SDSR8HQ",
            }).render("#paypal-container-248BY6SDSR8HQ")
            </script>
            <?php
        }else if($objInscrito->getcodPerfil() == 384){
            ?>
            <script src="https://www.paypal.com/sdk/js?client-id=BAA84DJcrTArOoZQPAACXuBn2VINORu-0l7NtbkI6KEceHQhh8-fGZ98rk7IFo6y-5-rA24yE_AgEHRSjY&components=hosted-buttons&disable-funding=venmo&currency=BRL"></script>
            <div id="paypal-container-6RTEVYVQG4ALY"></div>
            <script>
            paypal.HostedButtons({
                hostedButtonId: "6RTEVYVQG4ALY",
            }).render("#paypal-container-6RTEVYVQG4ALY")
            </script>
            <?php
        }else if($objInscrito->getcodPerfil() == 385){
            ?>
            <script src="https://www.paypal.com/sdk/js?client-id=BAA84DJcrTArOoZQPAACXuBn2VINORu-0l7NtbkI6KEceHQhh8-fGZ98rk7IFo6y-5-rA24yE_AgEHRSjY&components=hosted-buttons&disable-funding=venmo&currency=BRL"></script>
            <div id="paypal-container-AZXUVE7PTBMNL"></div>
            <script>
            paypal.HostedButtons({
                hostedButtonId: "AZXUVE7PTBMNL",
            }).render("#paypal-container-AZXUVE7PTBMNL")
            </script>
            <?php
        }else{
            echo $objPerfil->getComplemento();
        }
    }
}
?>