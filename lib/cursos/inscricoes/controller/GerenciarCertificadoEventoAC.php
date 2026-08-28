<?php

class GerenciarCertificadoEventoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }

    public function carregarCertificadoEvento($fws_, $debug = false) {
        $obj = new CertificadoEvento();
        $obj->setCodCertificadoEvento($fws_['codCertificadoEvento']);
        $obj->getCertificadoEventoById($debug);
        return $obj;
    }

    public function editarCertificadoEvento($fws_, $debug = false) {
        $obj = $this->carregarCertificadoEvento($fws_,$debug);
        if($obj->getEntregue()!=''){
            echo '{
            "statusMessage":"0",
            "message":      "Vouchers entregue não pode ser Editado",
            "codCertificadoEvento":"' . $obj->getCodCertificadoEvento() . '"
            }';
        exit();
        }

        echo '{
                  "statusMessage":"1",
                  "message":      "' . $obj->getMessage() . '",
                  "codCertificadoEvento":"' . $obj->getCodCertificadoEvento() . '",
                  "nome": "' . $obj->getNomeResponsavel() . '",
                  "foneContato":  "' . $obj->getFoneContato() . '",
                  "patrocinador": "' . $obj->getPatrocinador() . '",
                  "sigla": "' . $obj->getSigla() . '",
                  "emailContato ": "' . $obj->getEmailContato() . '",
                  "dataValidade":"' . formatDateView($obj->getDataValidade(),false) . '",
                  "quantidade":  "' . $obj->getQuantidade() . '",
                  "valor":       "' . $obj->getValor() . '",
                  "porcentagem": "' . $obj->getPorcentagem() . '",
                  "descricao":   "' . $obj->getDescricao() . '",
                  "categoria":   "' . $obj->getCategoria() . '",
                  "descricao":   "' . tratarJson($obj->getDescricao()) . '"
                 }';
        exit();
    }

    public function salvarCertificadoEvento($fws_, $debug = false) {
        try {
            if ($fws_['novoCodCertificadoEvento'] == 0) {
                $fws_['codCertificadoEvento'] = NULL;
            }       
            $isForm = true;
            $obj = $this->carregarCertificadoEvento($fws_); 
            if($obj->getImpresso()!=""){
                throw new Exception("Grupo de vouchers impresso não pode ser alterado.");
            }

            $obj->setAll($fws_, true, $debug);
            $obj->salvar();
        } catch (OkException $e) {
            $obj->carregarListaVouchersByCodCertificadoEvento($debug);
            if(!$obj->getListaVouchers()){
                $obj->criarListaVouchers();
            }
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codCertificadoEvento":"' . $obj->getCodCertificadoEvento() . '"
        }';
    }

    public function excluirCertificadoEvento($fws_, $debug = false) {
        try {
            $obj = $this->carregarCertificadoEvento($fws_,$debug);
            if($obj->getImpresso()!=""){
                throw new Exception("CertificadoEvento Quitado não pode ser excluido.");
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
        "codCertificadoEvento":"' . $fws_['codCertificadoEvento'] . '",
        "codInscrito":"' . $fws_['codInscrito'] . '"
        }';
    }

    public function desativarCertificadoEvento($fws_, $debug = false) {
        try {
            $obj = $this->carregarCertificadoEvento($fws_,$debug);
            if($obj->getImpresso()!=""){
                throw new Exception("CertificadoEvento Quitado não pode ser desativado.");
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
        "codCertificadoEvento":"' . $fws_['codCertificadoEvento'] . '",
        "codInscrito":"' . $fws_['codInscrito'] . '"
        }';
    }
    
    public function enviarEmailAvisoPadrao($fws_, $debug = false) {
        $enviarEmail = new GerenciarEmailAC();
        $enviarEmail->enviarEmailAvisoPadraoCertificadoEvento($fws_,$debug);
    }

    public function carregarTabelaCertificadoEventos($fws_, $debug = false) {
        $obj = new CertificadoEvento();
        $obj->carregarListaCertificadoEventos($debug);
        try {
            $lista = $obj->getListaCertificadoEventos($debug);
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodCertificadoEvento() ?>">
                    <td>
                        <!--Cód.: <?php echo  $objL->getCodCertificadoEvento() ?><br>-->
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
                             src="<?php echo  WB_PATH ?>/resources/image/fig-desativar.gif" alt="Desativar Grupo de Voucher" title="Desativar CertificadoEvento" border="0">
<!--                       <img class="enviarAvisoCertificadoEvento" 
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
                    <td><?php echo  $objL->getQuantidade()?>
                    <a class='link' href='<?php echo WB_URL_ACTION; ?>?content=view/voucher/voucher/voucher.php&cod_voucher_evento=<?php echo $objL->getCodCertificadoEvento() ?>&intervalo=<?php echo $i ?>'
                               target='_blank'><b>Gerar PDF</b></a>
                    </td>
                    <td>
                        <?php echo  formatDateView($objL->getDataValidade()) ?>
                    </td>
                    <td><?php echo  $objL->getValorDesconto()?></td>
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