<?php

class GerenciarPresencaAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }

    public function carregarPresenca($fws_, $debug = false) {
//        $obj = new Presenca();
//        $obj->setCodPresenca($fws_['codPresenca']);
//        $obj->getPresencaById($debug);
//        return $obj;
    }

    public function localizarInscrito($fws_, $debug = false) {
        try {
            
            $obj = new Inscrito();
            $fws_['cpf']           !='' ? $obj->setCpf($fws_['cpf'])                    : $ii++ ;
            $fws_['cod_inscrito']  !='' ? $obj->setCodInscrito($fws_['cod_inscrito'])   : $ii++ ;
            $fws_['e_mail']        !='' ? $obj->setEMail($fws_['e_mail'])               : $ii++ ;
            $fws_['nome_inscrito'] !='' ? $obj->setNomeInscrito($fws_['nome_inscrito']) : $ii++ ;
            
            if($ii==4){
                throw new Exception("É necessário informar ao menos um dos campos para pesquisa.");
            }

            $obj->localizarInscricaoAtivada($debug);
            
            if(count($obj->getListaInscritos()) == 0){
//                throw new Exception("Pergunte ao congressista se ele(a) irá trabalhar no evento."
//                        . "<br>Caso positivo, o encaminhe para sala vip.<br>"
//                        . "<br>Caso contrário, procure balcão de informação para maiores esclarecimentos.");
                throw new Exception("Encaminhe o congressista ao balcão de informação para maiores esclarecimentos.");
                
            }
            if(count($obj->getListaInscritos()) == 1){
                /*Marcar presença e imprimir etiqueta*/
                $fws_['listaInscritos'] = $obj->getListaInscritos();
                $this->carregarTabelaInscritos($fws_);
            }
            if(count($obj->getListaInscritos()) > 1){
                /*Mais de uma inscrição localizada, listar para decidir*/
                $fws_['listaInscritos'] = $obj->getListaInscritos();
                $this->carregarTabelaInscritos($fws_);
            }
            
        } catch (Exception $e) {
            echo "<table  style=\"font-size: 20px;\"><tr><td></td><td colspan=6>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "<br><br>&nbsp;</td></tr></table>";
        }
        exit();
    }

    public function carregarTabelaInscritos($fws_, $debug = false) {
        ?>
        <label>Inscrições Localizadas:</label>
        <table class="tablesorter" border="0" style="font-size: 20px;">
        <thead>
            <th width="10px"></th>
            <th width="50px">Cód.</th>
            <th width="400px">Nome</th>
            <th width="100px">Cpf</th>
            <th width="100px">E-mail</th>
            <th width="50px">
                Isento
                <br>Pago
                <br>Perfil
            </th>
        </thead> 
        <tbody class="inputs">
        <?php
        try {
            foreach ($fws_['listaInscritos'] as $objL) {
                if ($objL->getCodPerfil()==25){
                ?>  
                <tr>
                    <td></td>
                    <td colspan="5">
                    Ola <?php echo  $objL->getNomeInscrito() ?>, cpf:<?php echo  $objL->getCpf() ?> e email: <?php echo  $objL->getEMail() ?>
                    <br><b>Seu kit para evento já esta o esperando na sala vip.</b>
                    </td>
                </tr>
                <?php
                }else{
                ?>
                <tr>
                    <td>
                        <a title="Gerar Etiqueta pra Cracha" href='javascript:void(0);' onclick='javascript:gerarfnEtiqueta(<?php echo  $objL->getCodInscrito() ?>);'>
                            <img src='<?php echo  WB_URL_IMG ?>/cracha.jpg' alt='Gerar Etiqueta pra Crachá' border='0' width="40px"></a>
                        
                        
                    <?php if ($objL->getStatus()=='ATIVADA'){?>
                        <br><a title='Abrir Inscrição' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo  $objL->getCpf() ?>&cod_inscrito=<?php echo  $objL->getCodInscrito() ?>&cod_evento=<?php echo  COD_EVENTO ?>'>
                            <img src='<?php echo  WB_URL_IMG ?>/fig-alterar-old.gif' border='0'  width='30px'></a>
                    <?php }?>
                        
                    </td>
                    <td><?php echo  $objL->getCodInscrito() ?></td>
                    <td><a href='javascript:void(0);' onclick='javascript:gerarfnEtiqueta(<?php echo  $objL->getCodInscrito() ?>);'><?php echo  $objL->getNomeInscrito() ?></a></td>
                    <td><?php echo  $objL->getCpf() ?></td>
                    <td><?php echo  $objL->getEMail()?></td>
                    <td>
                        <?php
                        if($objL->getCodIsento()!=''){
                            echo "Isento";
                        }else if($objL->getTotalPagamentos()>0){
                            echo "Pago";
                        }else if ($objL->getCodPerfil()==25){
                            echo "Convidado Isento";
                        }else if ($objL->getCodVoucher()!=''){
                            echo "Voucher";
                        }
                        ?>
                    </td>
                </tr>
                    <?php
                    if($objL->getCodOficina()) {
                    ?>
                    <tr style="font-size: 15px;">
                        <td colspan="2"></td>
                        <td colspan="3">
                            <?php
                            echo "Oficina: ".$objL->getOrdemOficina()."<br>";
                            echo $objL->getNomeOficina();
                            ?>
                        </td>
                         <td></td>
                    </tr>
                    <?php
                    }
                    
                }
            }
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=5 style='margin-top:15px;margin-botton:15px;'><h3>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</h3></td></tr>";
        }
        ?>
        </tbody></table>
        <?php
    }
    
    public function imprimirEtiqueta($fws_, $debug = false) {
        
    }
}
?>