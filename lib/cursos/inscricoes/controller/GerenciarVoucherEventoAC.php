<?php

class GerenciarVoucherEventoAC {

    function __construct() {
        
    }

    public function carregarVoucherEvento($fws_, $debug = false) {
        $obj = new VoucherEvento();
        $obj->setCodVoucherEvento($fws_['codVoucherEvento']);
        $obj->getVoucherEventoById($debug);
        return $obj;
    }

    public function editarVoucherEvento($fws_, $debug = false) {
        $obj = $this->carregarVoucherEvento($fws_,$debug);
        if($obj->getEntregue()!=''){
            echo '{
            "statusMessage":"0",
            "message":      "Vouchers entregue não pode ser Editado",
            "codVoucherEvento":"' . $obj->getCodVoucherEvento() . '"
            }';
        exit();
        }

        echo '{
                  "statusMessage":"1",
                  "message":      "' . $obj->getMessage() . '",
                  "codVoucherEvento":"' . $obj->getCodVoucherEvento() . '",
                  "nome": "' . $obj->getNome() . '",
                  "foneContato":  "' . $obj->getFoneContato() . '",
                  "patrocinador": "' . $obj->getPatrocinador() . '",
                  "sigla": "' . $obj->getSigla() . '",
                  "emailContato": "' . $obj->getEmailContato() . '",
                  "dataValidade":"' . $obj->getDataValidade() . '",
                  "quantidade":  "' . $obj->getQuantidade() . '",
                  "valor":       "' . $obj->getValor() . '",
                  "porcentagem": "' . $obj->getPorcentagem() . '",
                  "descricao":   "' . $obj->getDescricao() . '",
                  "categoria":   "' . $obj->getCategoria() . '",
                  "descricao":   "' . tratarJson($obj->getDescricao()) . '"
                 }';
        exit();
    }

    public function salvarVoucherEvento($fws_, $debug = false) {
        try {
            if ($fws_['novoCodVoucherEvento'] == 0) {
                $fws_['codVoucherEvento'] = NULL;
                $fws_['_atualizar_lista'] = NULL;
            }   
            $isForm = true;
            $obj = $this->carregarVoucherEvento($fws_); 
            if($obj->getImpresso()!=""){
                throw new Exception("Grupo de vouchers impresso não pode ser alterado.");
            }

            $obj->setAll($fws_, true, $debug);
            $obj->setMaxUtilizacao('1');
            // $obj->setDescricao(addslashesopctec($fws_['descricao']));
            // $obj->setCategoria(addslashesopctec($fws_['categoria']));
            $obj->salvar($debug);
        } catch (OKException $e) {
            $obj->carregarListaVouchersByCodVoucherEvento($debug);
            if((!$obj->getListaVouchers()) or ($fws_['_atualizar_lista']==1)){
                $obj->criarListaVouchers();
            }
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codVoucherEvento":"' . $obj->getCodVoucherEvento() . '"
        }';
        exit();
    }

    public function excluirVoucherEvento($fws_, $debug = false) {
        try {
            $obj = $this->carregarVoucherEvento($fws_,$debug);
            if($obj->getImpresso()!=""){
                throw new Exception("VoucherEvento Quitado não pode ser excluido.");
            }
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codVoucherEvento":"' . $fws_['codVoucherEvento'] . '",
        "codInscrito":"' . $fws_['codInscrito'] . '"
        }';
    }

    public function desativarVoucherEvento($fws_, $debug = false) {
        try {
            $obj = $this->carregarVoucherEvento($fws_,$debug);
            if($obj->getImpresso()!=""){
                throw new Exception("VoucherEvento Quitado não pode ser desativado.");
            }
            $obj->desativar();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codVoucherEvento":"' . $fws_['codVoucherEvento'] . '",
        "codInscrito":"' . $fws_['codInscrito'] . '"
        }';
    }
    
    public function enviarEmailAvisoPadrao($fws_, $debug = false) {
        $enviarEmail = new GerenciarEmailAC();
        $enviarEmail->enviarEmailAvisoPadraoVoucherEvento($fws_,$debug);
    }

    public function carregarTabelaVoucherEventos($fws_, $debug = false) {
        try {
        $obj = new VoucherEvento();
        $obj->carregarListaVoucherEventos($debug);
            $lista = $obj->getListaVoucherEventos($debug);
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodVoucherEvento() ?>">
                    <td>
                        <!--Cód.: <?php echo  $objL->getCodVoucherEvento() ?><br>-->
                        <?php 
                        //if ($objEv->controlarAcessoProposta('boolean')) {
//                        if (($objL->getExtra()==1) and ($objL->getFlgRemessa()!= 1) and ($objL->getFlgPagamento() != 1)){
                        ?>
                       <img class="editar" 
                             src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar Grupo de Voucher" border="0">
                       <!--<img class="excluir" 
                             src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Grupo de Voucher" border="0">-->
                        <?php // }else{ ?>
                       <!--Edição bloqueada-->
                        <?php // } ?>
                       <img class="desativar" 
                             src="<?php echo  WB_PATH ?>/resources/image/fig-desativar.gif" alt="Desativar Grupo de Voucher" title="Desativar VoucherEvento" border="0">
<!--                       <img class="enviarAvisoVoucherEvento" 
                            src="<?php echo  WB_PATH ?>/resources/image/email-1.jpg" alt="Enviar e-mail avisando" title="Enviar e-mail avisando" border="0" width="22px">-->                   
                    </td>
                    <td>
                    <?php
                        echo $objL->getNomeResponsavel()? $objL->getNomeResponsavel()."<br>" : "";
                        echo $objL->getFoneContato()?     $objL->getFoneContato()."<br>"     : "";
                        echo $objL->getEmailContato()?    $objL->getEmailContato()."<br>"    : "";
                    ?>
                       
                    </td>
                    <td>
                        <?php echo $objL->getDescricao(); ?>
                        <br>Data Geração<?php echo  formatDateView($objL->getCreatedAt()) ?>
                    </td>
                    <td><?php echo  $objL->getPatrocinador() ?></td>
                    <td><?php echo  $objL->getTotalUtilizado().'/'.$objL->getQuantidade() ?>
                    <a class='link' href='<?php echo WB_URL_ACTION; ?>?content=view/voucher/voucher/voucher.php&cod_voucher_evento=<?php echo $objL->getCodVoucherEvento() ?>&intervalo=<?php echo $i ?>'
                               target='_blank'><b>Gerar PDF</b></a>
                    </td>
                    <td>
                        <?php echo  formatDateView($objL->getDataValidade()) ?>
                    </td>
                    <td>
                        <?php
                            if(intval($objL->getPorcentagem())!=0){
                                echo $objL->getPorcentagem();
                            }else if(intval($objL->getValorDesconto())!=0){
                                echo $objL->getValorDesconto();
                            }else if(intval($objL->getValorInscricao())!=0){
                                echo $objL->getValorInscricao();
                            }
                        ?>
                    </td>
                    <td>
                        <?php
                        if($objL->getImpresso()!=''){
                            echo $objL->getImpresso();
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=3>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }
}
?>