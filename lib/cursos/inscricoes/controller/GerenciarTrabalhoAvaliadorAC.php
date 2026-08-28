<?php
class GerenciarTrabalhoAvaliadorAC {
    
    public function adminIndicarAvaliador($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        $objGerAval = new GerenciarAvaliacaoAC();
        $objGerAval->adminIndicarAvaliador($fws_, $debug);
    }
    
    public function carregarObjTrabalho($fws_, $debug = false){
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodTrabalho($fws_['codTrabalho']);
        $objTrabalho->carregarTrabalhoByIdTrabalho($debug);
        return $objTrabalho;
    }

    public function carregarSelectBoxAvaliadores($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            $objAvaliador = new Avaliador();
            $objAvaliador->carregarListaAvaliadoresByIdEvento($debug);
            foreach ($objAvaliador->getListaAvaliadores() as $objAval) {
                ?>
                <option value="<?php echo  $objAval->getCodUsuario() ?>">Trab.Designados: <?php echo $objAval->getTotalTrabDesignados()?> - <?php echo  $objAval->getNomeConvidado() ?></option>
                <?php
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    function carregarTabelaAvaliadorTrabalho($fws_, $excluir=true,$debug = false) {
        try {
            $lista = $this->carregarListaTabelaAvaliadorByIdTrabalho($fws_, $debug);
            if ($lista) {
                foreach ($lista as $objP) {
                    ?>  
                    <tr id="<?php echo  $objP->getCodCurso() ?>">
                        <td> 
                            <?php if($excluir){?>
                            <img class="excluirAssociacaoCursoMinistrante" 
                                 src="<?php echo  WB_PATH ?>/view/image/fig-excluir.gif" title="Excluir Ministrante" border="0">                        
                            <?php }?>
                        </td>
                        <td><?php echo  $objP->getCodCurso() ?></td>
                        <td>
                            <a href="<?php echo  WB_URL_VIEW ?>&pagina=view/admin/GerenciarMinistrante.php&codEvento=<?php echo  $objP->getCodEvento() ?>&codCurso=<?php echo  $objP->getCodCurso() ?>">
                            <?php echo  $objP->getTituloCurso() ?></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                throw new Exception("Inscrito removido com sucesso.");
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>";
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