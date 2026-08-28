<?php

class GerenciarInteressadoAC
{

    function __construct()
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    public function carregarObjInteressados($fws_, $debug = false)
    {
        $obj = new Interessado();
        if ($fws_['codInteressado']) {
            $obj->setCodInteressado($fws_['codInteressado']);
            $obj->getInteressadoById($debug);
        }
        return $obj;
    }

    public function salvarInteressados($fws_, $debug = false)
    {
        $ok = false;
        $e = null;

        try {
            if ($fws_['updateInteressados'] == 0) {
                $fws_['codInteressado'] = NULL;
            }
            $obj = $this->carregarObjInteressados($fws_);
            $obj->setAll($fws_, true);
            $obj->salvar();
            $ok = true;
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        echo '{
    "statusMessage":"' . $ok . '",
    "message":      "' . ($ex ? $ex->getMessage() : '') . '",
    "codInteressado":"' . ($obj ? $obj->getCodInteressado() : '') . '"
    }';
    }

    public function insertInteressado($fws_, $debug = false)
    {
        $ok = false;
        $e = null;

        try {
            if ($fws_['updateInteressados'] == 0) {
                $fws_['codInteressado'] = NULL;
            }
            $obj = new Interessado();
            if ($fws_['codInteressado']) {
                $obj->setCodInteressado($fws_['codInteressado']);
                $obj->getInteressadoById($debug);
            }
            $obj->setAll($fws_, true);
            $obj->insertLigacaoInscritoAviso();
            $ok = true;
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . ($ex ? $ex->getMessage() : '') . '",
        "codInteressado":"' . ($obj ? $obj->getCodInteressado() : '') . '"
        }';
    }

    public function editarInteressados($fws_, $debug = false)
    {
        $ok = 0;
        $obj = $this->carregarObjInteressados($fws_, $debug);
        if($obj->getCodInteressado()){
            $ok = 1;
        }
        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codInteressado": "' . $obj->getCodInteressado() . '",
                  "nomeInteressado":"' . $obj->getNomeInteressado() . '",
                  "email":        "' . $obj->getEmail() . '",
                  "telefone":        "' . $obj->getTelefone() . '"
                 }';
        exit();
    }

    public function carregarInteressados($fws_, $debug = false)
    {
        $ok = 0;
        $obj = $this->carregarObjInteressados($fws_, $debug);
        if($obj->getCodInteressado()){
            $ok = 1;
        }
        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codInteressado": "' . $obj->getCodInteressado() . '",
                  "nomeInteressado":"' . $obj->getNomeInteressado() . '",
                  "email":        "' . $obj->getEmail() . '",
                  "telefone":        "' . $obj->getTelefone() . '"
                 }';
        exit();
    }

    public function excluirInteressados($fws_, $debug = false)
    {
        try {
            $obj = $this->carregarObjInteressados($fws_);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
            "statusMessage":"' . $ok . '",
            "message":"' . $e->getMessage() . '",
            "codInteressado":"' . $fws_['codInteressado'] . '",
            }';
        exit();
    }
    
    public function carregarTabelaInteressados($fws_, $debug = false)
    {
        $obj = new Interessado();
        $lista = [];
        try {
            $obj->carregarListaInteressadosByIdEvento($debug);
            $lista = $obj->getListaInteressados($debug);
        } catch (Exception $e) {}
        $ii = 0;
        $totalJaInscrito = 0;
        if (!empty($lista)) {
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo  $objL->getCodInteressado() ?>">
                    <td>
                        <span class="btn-acao editar" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>

                    <td><?php echo  $objL->getNomeInteressado() ?></td>
                    <td><?php echo  $objL->getEmail() ?></td>
                    <td><?php echo  $objL->getTelefone() ?></td>
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
        }
        if ($ii === 0) {
            echo '<tr><td colspan="6" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum interessado encontrado.</td></tr>';
        }
    }
}