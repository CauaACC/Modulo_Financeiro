<?php

class GerenciarInscritoAptoAC {

    function __construct() {
        
    }

    public function carregarInscritoApto($fws_, $debug = false) {
        $obj = new InscritoApto();
        $obj->setCodInscritoApto($fws_['codInscritoApto']);
        $obj->carregarInscritoAptoById($debug);
        return $obj;
    }

    public function editarInscritoApto($fws_, $debug = false) {
        $obj = $this->carregarInscritoApto($fws_,$debug);
        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codInscritoApto": "' . $obj->getCodInscritoApto() . '",
                  "nomeInscritoApto":"' . $obj->getNomeInscritoApto() . '",
                  "cpf":          "' . $obj->getCpf() . '",
                  "email":        "' . $obj->getEmail() . '",
                  "codPerfil":        "' . $obj->getCodPerfil() . '",
                  "status":        "' . $obj->getStatus() . '",
                  "telefone":        "' . $obj->getTelefone() . '"
                 }';
        exit();
    }

    public function salvarInscritoApto($fws_, $debug = false) {
        try {
            if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_INSCRITO_APTO, 5, false, false)){
                throw new Exception("Você não tem permissão para realizar esta operação.");
            }
            if ($fws_['novoCodInscritoApto'] == 0) {
                $fws_['codInscritoApto'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarInscritoApto($fws_);
            $obj->setAll($fws_, $isForm);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codInscritoApto":"' . $obj->getCodInscritoApto() . '"
        }';
    }

    public function excluirInscritoApto($fws_, $debug = false) {
        try {
            if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_INSCRITO_APTO, 5, false, false)){
                throw new Exception("Você não tem permissão para realizar esta operação.");
            }
            $obj = new InscritoApto();
            $obj->setCodInscritoApto($fws_['codInscritoApto']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codInscritoApto":"' . $fws_['codInscritoApto'] . '"
        }';
    }
    public function desativarInscritoApto($fws_, $debug = false) {
        try {
            if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_INSCRITO_APTO, 5, false, false)){
                throw new Exception("Você não tem permissão para realizar esta operação.");
            }
            $obj = new InscritoApto();
            $obj->setCodInscritoApto($fws_['codInscritoApto']);

            $obj->desativar();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codInscritoApto":"' . $fws_['codInscritoApto'] . '"
        }';
    }

    public function carregarTabelaInscritoAptos($fws_, $debug = false) {
        $obj = new InscritoApto();
        try {
            $lista = $obj->getListaInscritoAptosByIdEvento($debug);
            $totalAtivada    = 0;
            $totalAtivadaInscritos = 0;
            $totalAtivadaParticipando = 0;
            $totalDesativada = 0;
            $totalJaInscrito = 0;
            $totalAtivadaNaoRecadastrado = 0;
            $ii = 0;
            foreach ($lista as $objL) {
                if($objL->getStatus()=='ATIVADA'){
                    $totalAtivada++;
                }
                if($objL->getStatus()=='ATIVADA' and $objL->getStatusProcesso()=='PARTICIPANDO'){
                    $totalAtivadaParticipando++;
                }
                if($objL->getStatus()=='ATIVADA' and $objL->getStatusProcesso()=='INSCRITO'){
                    $totalAtivadaInscritos++;
                }
                if($objL->getStatus()=='ATIVADA' and $objL->getStatusProcesso()==''){
                    $totalAtivadaNaoRecadastrado++;
                }
                if($objL->getStatus()=='DESATIVADA'){
                    $totalDesativada++;
                }
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodInscritoApto() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <img class="editar" title="<?php echo "Editar: ".$objL->getCodInscritoApto() ?>"
                             src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar InscritoApto" border="0">
                        <img class="excluir" title="<?php echo "Excluir: ".$objL->getCodInscritoApto() ?>"
                             src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir InscritoApto" border="0">
                        <img class="desativar" title="<?php echo "Desativar: ".$objL->getCodInscritoApto() ?>"
                             src="<?php echo  WB_PATH ?>/resources/image/fig-desativar.gif" alt="Desativar InscritoApto" border="0">
                        <?php //} ?>
                    </td>
                   
                    <td><?php echo  $objL->getNomeInscritoApto() ?></td>
                    <td><?php echo  $objL->getCpf()?></td>
                    <td><?php echo  $objL->getEmail() ?></td>
                    <td><?php echo  $objL->getTelefone() ?></td>
                    <td><?php 
                        echo  $objL->getNomePerfil()? $objL->getNomePerfil() : "";
                        echo  $objL->getNomeOficina()? $objL->getNomeOficina() : "";
                        ?></td>
                    <td><?php echo $objL->getStatus(); ?></td>
                    <td>    
                        <?php
                        echo $objL->getJaInscrito();
                        if($objL->getJaInscrito()){
                            $totalJaInscrito++;
                            ?>
                            <a title='Logar Como' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $objL->getCpf()?>&cod_inscrito=<?php echo $objL->getCodInscricao()?>&cod_evento=<?php echo COD_EVENTO?>'>
                            <img src='<?php echo WB_URL_IMG?>/icone_preview.gif' border='0'></a>
                            <?php
                        }
                        ?></td>
                </tr>
                <?php
            }
            ?>
                <br>Total de inscritos aptos listados: <?php echo  $ii ?>.
                <br>Total de inscritos aptos registrados (Participando): <?php echo  $totalAtivadaParticipando ?>.
                <br>Total de inscritos aptos registrados (Inscritos/Recadastrando):     <?php echo  $totalAtivadaInscritos ?>.
                <br>Total de inscritos aptos Ativos Não Recadastrados:     <?php echo  $totalAtivadaNaoRecadastrado ?>.
                <br>Total de inscritos aptos Desativados:  <?php echo  $totalDesativada ?>.
            <?php
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