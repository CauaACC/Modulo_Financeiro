<?php

class GerenciarIsentoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }

    public function carregarIsento($fws_, $debug = false) {
        $obj = new Isento();
        $obj->setCodIsento($fws_['codIsento']);
        $obj->getIsentoById($debug);
        return $obj;
    }

    public function editarIsento($fws_, $debug = false) {
        $obj = $this->carregarIsento($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codIsento": "' . $obj->getCodIsento() . '",
                  "nomeIsento":"' . $obj->getNomeIsento() . '",
                  "cpf":          "' . $obj->getCpf() . '",
                  "email":        "' . $obj->getEmail() . '",
                  "organizacao":  "' . $obj->getOrganizacao() . '",
                  "departamento":  "' . $obj->getDepartamento() . '",
                  "cpfCnpjAbonador":  "' . $obj->getCpfCnpjAbonador() . '"
                 }';
        exit();
    }

    public function salvarIsento($fws_, $debug = false) {
        try {
            if ($fws_['novoCodIsento'] == 0) {
                $fws_['codIsento'] = NULL;
            }
            $isForm = true;
            $fws_['cpf'] = formatCpfInsert($fws_['cpf']);
            $obj = $this->carregarIsento($fws_);
            $obj->setAll($fws_, $isForm);
//            $obj->salvar($debug);
            $obj->salvarIsentoCodigo($debug);
        } catch (OkException $e) {
            
            /*aviso de cadastro de isento executado com sucesso*/
            $msgTo = $obj->getEMail();
            //$msgTo = "zakacc@gmail.com";
            if ($fws_['enviarConfirmacao'] == 1) {
                $msgSubject="".ABREV_EVENTO." - Inscrição Abonada: ".$obj->getNomeIsento()."";
                $msgBody=""
                        ."\r\n\r\nOla ".$obj->getNomeIsento()
                        ."\r\n\r\nSua inscrição no ".ABREV_EVENTO." - ".NOME_EVENTO
                        ."\r\n\r\nFoi ABANODA."
                        ."\r\n\r\nCaso ainda não se inscreveu no congresso, inscreva-se utilizando o link abaixo."
                        ."\r\n".URL_OFICIAL."/inscricoes/"
                        ."\r\n\r\nDúvidas: ".EMAIL_CONTATO
                        ."\r\nData: ".Date('d/m/Y H:i')
                        ."";
//                mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
                GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            }  
//            if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
//            /* Removendo funções que estavam setadas para a pessoa mas foram desativadas */
//            $this->removerFuncoes($fws_, $objPessoa, $debug);
//
//            /* Adicionando e atualizando funções */
//            $this->adicionarFuncoes($fws_, $objPessoa, $debug);
//            }
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson($e->getMessage()) . '",
        "codIsento":"' . $obj->getCodIsento() . '"
        }';
    }

    public function excluirIsento($fws_, $debug = false) {
        try {
            $obj = new Isento();
            $obj->setCodIsento($fws_['codIsento']);
            $obj->setCodInscrito($fws_['codInscrito']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codIsento":"' . $fws_['codIsento'] . '"
        }';
    }

    public function carregarTabelaIsentos($fws_, $debug = false) {
        $obj = new Isento();
        $condicoes['ordenar']= $fws_['ordenar'];
        try {
            $obj->carregarListaIsento($condicoes,$debug);
            $lista = $obj->getListaIsento();
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodIsento() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <img class="editar" 
                             src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar Isento" border="0">
                        <img class="excluir" 
                             src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Isento" border="0">                        
                        <?php //} ?>
                    </td>
                   
                    <td>
                        <?php
                        if($nomeAnterior == $objL->getNomeIsento()){
                            echo "<font color=red>".$objL->getNomeIsento()."</font><br>";
                        }else{
                            echo $objL->getNomeIsento().'<br>';
                        }
                        $nomeAnterior = $objL->getNomeIsento();
                        if($cpfAnterior == $objL->getCpf()){
                            echo "<font color=red>".$objL->getCpf()."</font><br>";
                        }else{
                            echo $objL->getCpf()."<br>";
                        }
                        $cpfAnterior = $objL->getCpf();
                        echo $objL->getEmail();
                        ?>
                    </td>
                    <td><?php
                        echo $objL->getJaInscrito()."<br>";
                        if($objL->getCodInscricao()){
                            echo "Cód.Inscrito:".$objL->getCodInscricao();
                            ?>
                            <a title='Logar Como' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $objL->getCpf()?>&cod_inscrito=<?php echo $objL->getCodInscricao()?>&cod_evento=<?php echo COD_EVENTO?>'>
                            <img src='<?php echo WB_URL_IMG?>/icone_preview.gif' border='0'></a>
                            <?php
                        }
                        if($objL->getJaInscrito()){
                            $totalJaInscrito++;
                        }
                        ?></td>
                    <td>
                        <?php echo $objL->getOrganizacao(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDepartamento(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getCpfCnpjAbonador(); ?>
                    </td>
                    <td>
                        <?php
                        if($objL->getCodInscricao()){
                            $fws_['cod_evento']               = $objL->getCodEvento();
                            $fws_['cod_inscrito']               = $objL->getCodInscricao();
                            $fws_['lista_perfil_administrativo'] = $objL->getListaPerfil();
                            $this->carregarListaPerfisCheckbox($fws_, $debug);
                        } else {
                            echo "Isento não cadastrado no evento";
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
                <br>Total de isentos cadastrados: <?php echo  $ii ?>.
                <br>Total de isentos já inscritos: <?php echo  $totalJaInscrito ?>.
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
    
    public function associarInscritoPerfilAdminstrativo($fws_, $debug = false) {
        try {
            $obj = new InscritoPerfil();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodPerfil($fws_['cod_perfil']);
            $existe = $obj->carregarInscritoPerfilById();

//            if ($obj->getStatus()=='ATIVADA') {
//                $msg = "Excluiu";
//                $obj->excluir();
//            } else {
//                $msg = "Associou";
//                $obj->salvar($debug);
//            }
            if($fws_['acao']=='true'){
                $msg = "Associou";
                $obj->salvar($debug);
            }else{
                $msg = "Excluiu";
                $obj->excluir();
            }
        }catch (OKException $exc) {
            $statusExcec = 1;
        }catch (Exception $exc) {
            $statusExcec = 0;
        } 
        echo '{
            "message":"<b>'.$msg.'</b>",
            "statusMessage":"'.$statusExcec.'"
            }';
        exit();
    }
    
    public function carregarListaPerfisCheckbox($fws_, $debug=false) {
        /*traduzindo lista de perfis em array de codigo perfil*/
        if($fws_['lista_perfil_administrativo']){
        foreach ($fws_['lista_perfil_administrativo'] as $obj) {
           $lista_perfil_inscrito[] = $obj->getCodPerfil();
        }}
        
        $obj = new Perfil();
        $obj->carregarListaPerfil();
        $i=0;
        foreach ($obj->getListaPerfil() as $objL) {
            if($objL->getReceberInscricao()!=1){
//                $checkedPresenca = $objL->getFlgPresenca() ? "checked" : "";
//                echo "<input id='presenca-".$objL->getCodTrabalho()."' class='presenca' style='float: left;'"
//                        . " onclick=\"alternarPresenca('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\""
//                        . $checkedPresenca
//                        . " name='presenca' style='display: inline-block;' type='checkbox'>";

                echo " <input id='associar-perfil-".$objL->getCodPerfil()."-".$fws_['cod_inscrito']."' class='perfil' ";
                echo " type='checkbox' name='lista_cod_perfil[]' value='{$objL->getCodPerfil()}' ";
                echo " title='Associar a grupos administrativo'";
                echo " onclick=\"associarInscritoPerfilAdministrativo('".$fws_['cod_inscrito']."','".$objL->getCodPerfil()."')\"";
                echo ' '.verifItemChecked($objL->getCodPerfil(), $lista_perfil_inscrito,$debug).' ';
                echo ' />&nbsp;';
                echo $objL->getTitulo()."&nbsp;&nbsp;&nbsp;";
                $i++;
                if($i>=5){
                    echo "<br>";
                    $i=0;
                }
                echo "<div id='carregando-".$objL->getCodInscrito()."' class='hidden' hidden='hidden'>"; 
                echo "<img src='".WB_URL_IMG."/loading-9.gif' width='20' height='20' border='0'>";
                echo "</div>";
            }
        }

    }       
}
?>