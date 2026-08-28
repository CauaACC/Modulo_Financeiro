<?php

class GerenciarInscritoPerfilAC {

    function __construct() {
        // Gravacoes desta tela (editar carga, cadastrar inscrito, liberar perfil/geral)
        // exigem quem GERENCIA -- alinhado com o gate da propria tela, que ja libera
        // SIS_GERENCIAR_INSCRITO. ADM_GERAL continua passando (OR). Nao entra o
        // SIS_CONSULTAR_INSCRITOS (so leitura). Aqui NAO entra SIS_GERENCIAR_CERTIFICADO
        // (hoje 30): esta tela grava dado de inscrito, nao e tela de certificado.
        FWA_exigirPermissao(array(
            enumSistema::SIS_GERENCIAR_INSCRITO,
            enumSistema::SIS_GERENCIAR_ADM_GERAL,
        ));
    }

    public function carregarInscritoPerfil($fws_, $debug = false) {
        $obj = new InscritoPerfil();
        $obj->setCodInscritoPerfil($fws_['codInscritoPerfil']);
        $obj->getInscritoPerfilById($debug);
        return $obj;
    }

    public function editarInscritoPerfil($fws_, $debug = false) {
        $obj = $this->carregarInscritoPerfil($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codInscritoPerfil": "' . $obj->getCodInscritoPerfil() . '",
                  "nomeInscritoPerfil":"' . $obj->getNomeInscritoPerfil() . '",
                  "cpf":          "' . $obj->getCpf() . '",
                  "email":        "' . $obj->getEmail() . '",
                  "organizacao":  "' . $obj->getOrganizacao() . '",
                  "departamento":  "' . $obj->getDepartamento() . '",
                  "cpfCnpjAbonador":  "' . $obj->getCpfCnpjAbonador() . '"
                 }';
        exit();
    }

    public function salvarInscritoPerfil($fws_, $debug = false) {
        try {
            if ($fws_['novoCodInscritoPerfil'] == 0) {
                $fws_['codInscritoPerfil'] = NULL;
            }
            $isForm = true;
            $fws_['cpf'] = formatCpfInsert($fws_['cpf']);
            $obj = $this->carregarInscritoPerfil($fws_);
            $obj->setAll($fws_, $isForm);
            $obj->salvar($debug);
        } catch (OkException $e) {
            
            /*aviso de cadastro de InscritoPerfil executado com sucesso*/
            $msgTo = $obj->getEMail();
            //$msgTo = "zakacc@gmail.com";
            if ($fws_['enviarConfirmacao'] == 1) {
                $msgSubject="".ABREV_EVENTO." - Inscrição Abonada: ".$obj->getNomeInscritoPerfil()."";
                $msgBody=""
                        ."\r\n\r\nOla ".$obj->getNomeInscritoPerfil()
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
        "codInscritoPerfil":"' . $obj->getCodInscritoPerfil() . '"
        }';
    }

    public function excluirInscritoPerfil($fws_, $debug = false) {
        // ControleAcesso::ifUserInRole('gerenciarEventos,coordenarEventos', 'url');
        try {
            $obj = new InscritoPerfil();
            $obj->setCodInscritoPerfil($fws_['codInscritoPerfil']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codInscritoPerfil":"' . $fws_['codInscritoPerfil'] . '"
        }';
    }

    /**
     * Campo de texto do POST, ou NULL quando vazio.
     * NULL importa: nas colunas de carga horaria, NULL significa "usa o padrao
     * do perfil". Gravar string vazia mudaria esse sentido.
     */
    private function vazioParaNull($fws_, $campo) {
        $v = isset($fws_[$campo]) ? trim($fws_[$campo]) : '';
        return $v === '' ? NULL : $v;
    }

    /**
     * Cadastro SIMPLES de inscrito direto na listagem.
     *
     * Serve para lancar quem participou mas nao se inscreveu pelo site (lista
     * de presenca em papel, convidado, ministrante externo). So o NOME e
     * obrigatorio.
     *
     * Por que da para gravar sem e-mail: o validatorObj() do inscrito exige
     * e-mail, mas o salvarCarga() usa validatorObjCarga(), que cobra apenas o
     * cod_evento -- e o mesmo caminho que a carga de "inscritos para
     * certificado" ja usa. origem_cadastro=CERTIFICADO deixa registrado que a
     * pessoa entrou por aqui, e nao por inscricao.
     *
     * A PRESENCA e gravada em dois lugares, de proposito:
     *   tab_inscritos.flg_presenca_inscrito  -> presenca no evento
     *   tab_lig_inscritos_perfis.flg_presenca -> presenca naquele papel
     *
     * A CARGA HORARIA nao existe solta no inscrito; ela mora nas ligacoes, que
     * sao de onde o certificado le:
     *   tab_lig_inscritos_perfis   -> a pessoa naquele perfil (vence o padrao
     *                                 do perfil, carga_horaria_certificado)
     *   tab_lig_inscritos_oficinas -> a pessoa naquela atividade
     * Vazio grava NULL, e NULL significa "usa o padrao".
     */
    public function adicionarInscritoSimples($fws_, $debug = false) {
        $ok  = false;
        $msg = '';
        $codInscrito = 0;
        try {
            $nome = isset($fws_['nome_inscrito']) ? trim($fws_['nome_inscrito']) : '';
            if ($nome == '') {
                throw new Exception("Informe o nome da pessoa.");
            }
            $codPerfil = isset($fws_['cod_perfil']) ? (int) $fws_['cod_perfil'] : 0;
            if ($codPerfil <= 0) {
                // cod_perfil e NOT NULL em tab_inscritos: sem perfil nao ha cadastro.
                throw new Exception("Escolha o perfil da pessoa.");
            }

            $obj = new Inscrito();
            $obj->setCodEvento(COD_EVENTO);
            $obj->setCodInscrito(NULL);
            $obj->setCodPerfil($codPerfil);
            $obj->setNomeInscrito($nome);
            $obj->setCpf(formatCpfInsert(isset($fws_['cpf']) ? $fws_['cpf'] : ''));
            $obj->setEMail(isset($fws_['e_mail']) ? trim($fws_['e_mail']) : '');
            $obj->setOrigemCadastro('CERTIFICADO');
            $obj->setStatus('ATIVADA');
            // Presenca do evento ja no cadastro: e o motivo de existir a tela.
            $obj->setFlgPresencaInscrito(!empty($fws_['presente']) ? '1' : NULL);

            try {
                $obj->salvarCarga($debug);
            } catch (OKException $e) {
                // salvarCarga sinaliza sucesso via OKException
            }
            $codInscrito = (int) $obj->getCodInscrito();
            if ($codInscrito <= 0) {
                throw new Exception("O inscrito foi gravado mas o codigo nao voltou.");
            }

            // Vinculo com o PERFIL (tab_lig_inscritos_perfis). E a linha que esta
            // tela lista, e agora carrega presenca e carga horaria da pessoa
            // NAQUELE papel -- mesmo esquema da ligacao com atividade.
            $ligP = new InscritoPerfil();
            $ligP->setCodEvento(COD_EVENTO);
            $ligP->setCodInscrito($codInscrito);
            $ligP->setCodPerfil($codPerfil);
            $ligP->setFlgPresenca(!empty($fws_['presente']) ? 1 : NULL);
            $ligP->setCargaHoraria($this->vazioParaNull($fws_, 'perfil_carga_horaria'));
            $ligP->setCargaHorariaTeorica($this->vazioParaNull($fws_, 'perfil_carga_horaria_teorica'));
            $ligP->setCargaHorariaPratica($this->vazioParaNull($fws_, 'perfil_carga_horaria_pratica'));
            $ligP->setStatus('ATIVADA');
            try {
                $ligP->salvar($debug);
            } catch (OKException $e) {
                // ok
            }

            // Vinculo com a atividade (opcional) -- e onde a carga horaria cabe.
            $codOficina = isset($fws_['cod_oficina']) ? (int) $fws_['cod_oficina'] : 0;
            if ($codOficina > 0) {
                $lig = new InscritoOficina();
                $lig->setCodEvento(COD_EVENTO);
                $lig->setCodInscrito($codInscrito);
                $lig->setCodOficina($codOficina);
                $lig->setTipoRelacao(isset($fws_['tipo_relacao']) ? $fws_['tipo_relacao'] : '');
                $lig->setFlgPresenca(!empty($fws_['presente']) ? '1' : '');
                $lig->setCargaHoraria(isset($fws_['carga_horaria']) ? trim($fws_['carga_horaria']) : '');
                $lig->setCargaHorariaTeorica(isset($fws_['carga_horaria_teorica']) ? trim($fws_['carga_horaria_teorica']) : '');
                $lig->setCargaHorariaPratica(isset($fws_['carga_horaria_pratica']) ? trim($fws_['carga_horaria_pratica']) : '');
                $lig->setStatus('ATIVADA');
                try {
                    $lig->salvar($debug);
                } catch (OKException $e) {
                    // ok
                }
            }

            $ok  = true;
            $msg = "Inscrito cadastrado: " . $nome . " (cod. " . $codInscrito . ")";
        } catch (OKException $e) {
            $ok  = true;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $msg,
            'cod_inscrito'  => $codInscrito,
        ));
        exit;
    }

    /**
     * Mapa cod_inscrito => [pagamentos, isento] para a listagem.
     *
     * Por que nao calcular na query da propria listagem: a regra e complexa e
     * mora em InscritoDAO -- isencao vem de TRES fontes (tab_isentos por CPF,
     * tab_isentos_codigo por inscrito e perfil_isento=1, direto ou via
     * tab_lig_inscritos_perfis) e pagamento tem filtro por forma_pagamento.
     * Reescrever aqui era garantia de divergir da regra que o certificado usa.
     *
     * Entao chama UMA vez a consulta canonica (a mesma da tela de inscritos) e
     * cruza em memoria. Nunca lanca: sem isso a listagem some por um detalhe.
     */
    private function mapaPagamentoIsencao($debug = false) {
        $mapa = array();
        try {
            $objL = new ListarInscritosAC();
            $condicoes = $objL->carregarCondicoes(array(), $debug);
            $condicoes['cod_evento']       = COD_EVENTO;
            $condicoes['mostrarInscritos'] = 1;
            $condicoes['perspectiva']      = 'default';

            $objInscrito = new Inscrito();
            $objInscrito->carregarListaInscritos($condicoes, $debug);
            $lista = $objInscrito->getListaInscritos();
            if (!$lista) {
                return $mapa;
            }
            foreach ($lista as $i) {
                $mapa[(int) $i->getCodInscrito()] = array(
                    'pagamentos' => (int) $i->getTotalPagamentos(),
                    // getDesIsento() ja resolve as tres fontes e devolve o rotulo
                    // ('Isento', 'Isento por Perfil' ou vazio).
                    'isento'     => (string) $i->getDesIsento(),
                    'voucher'    => $i->getCodVoucher() ? 1 : 0,
                );
            }
        } catch (Exception $e) {
            // segue sem a coluna preenchida
        }
        return $mapa;
    }

    public function carregarTabelaInscritoPerfils($fws_, $debug = false) {
        $mapaPag = $this->mapaPagamentoIsencao($debug);
        $obj = new InscritoPerfil();
        $condicoes['ordenar']= $fws_['ordenar'];
        if($fws_['cod_inscrito']){
            $condicoes['cod_inscrito']= $fws_['cod_inscrito'];
        }
        if($fws_['nome_inscrito']){
            $condicoes['nome_inscrito']= $fws_['nome_inscrito'];
        }
        if($fws_['cpf']){
            $condicoes['cpf']= $fws_['cpf'];
        }
        if($fws_['cod_perfil']){
            $condicoes['cod_perfil']= $fws_['cod_perfil'];
        }
        $condicoes['ordenar']= $fws_['ordenar'];

        // Filtros de ESTADO (aplicados aqui, em memoria): pagamento/isencao vem do
        // mapa canonico (mapaPag), e "liberado" depende do GROUP_CONCAT de perfis --
        // por isso nao entram no WHERE da query.
        $fLib = isset($fws_['filtro_liberado'])  ? $fws_['filtro_liberado']  : '';
        $fPag = isset($fws_['filtro_pagamento']) ? $fws_['filtro_pagamento'] : '';
        $fGer = isset($fws_['filtro_geral'])     ? $fws_['filtro_geral']     : '';

        // Inicia zerado: com filtro que nao casa nenhum, o total mostra 0 (nao vazio).
        $ii = 0;
        $totalJaInscrito = 0;

        try {
            $obj->carregarListaInscritoPerfil($condicoes,$debug);
            $lista = $obj->getListaInscritoPerfil();
            foreach ($lista as $objL) {
                // ---------------- Filtros de estado ----------------
                // Certificado ESPECIAL liberado = tem >=1 papel administrativo associado.
                $temPapel = trim((string) $objL->getListaPerfis()) !== '';
                if ($fLib === 'sim' && !$temPapel) { continue; }
                if ($fLib === 'nao' &&  $temPapel) { continue; }
                // Pagamento / isencao (mapa canonico, mesma regra da coluna).
                $codIf = (int) $objL->getCodInscricao();
                $temPag = false;
                if ($codIf && isset($mapaPag[$codIf])) {
                    $pg = $mapaPag[$codIf];
                    $temPag = ((int) $pg['pagamentos'] > 0) || ($pg['isento'] !== '') || !empty($pg['voucher']);
                }
                if ($fPag === 'pago' && !$temPag) { continue; }
                if ($fPag === 'sem'  &&  $temPag) { continue; }
                // Certificado GERAL: bloqueado x recebe.
                $bloq = $objL->getFlgBloquearCertificadoGeral() ? true : false;
                if ($fGer === 'bloqueado' && !$bloq) { continue; }
                if ($fGer === 'recebe'    &&  $bloq) { continue; }

                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodInscritoPerfil() ?>">
                    <td>
                        <?php
                        if($nomeAnterior == $objL->getNomeInscrito()){
                            echo "<font color=red>".$objL->getNomeInscrito()."</font><br>";
                        }else{
                            echo $objL->getNomeInscrito().'<br>';
                        }
                        $nomeAnterior = $objL->getNomeInscrito();
                        echo $objL->getJaInscrito();
                        if($objL->getCodInscricao()){
                            ?>
                            <a title='Logar Como' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $objL->getCpf()?>&cod_inscrito=<?php echo $objL->getCodInscrito()?>&cod_evento=<?php echo COD_EVENTO?>'>
                                <img src='<?php echo WB_URL_IMG?>/icone_preview.gif' border='0'></a>
                            <?php
                            echo " Cód.Inscrito:".$objL->getCodInscricao();
                        }
                        echo " - ".$objL->getCpf();
                        echo " - ".$objL->getEMail();
                        if($objL->getJaInscrito()){
                            $totalJaInscrito++;
                        }
                        $fws_['e_mail'] = $objL->getEMail();
                        //GerenciarInscritoPerfilAC::carregarListaPerfisAdministrativos($fws_, $debug);
                        ?></td>
                    <td style="white-space:nowrap; font-size:12px;">
                        <?php
                        // Pagamento e isencao: junto com a presenca, e o que decide se
                        // a pessoa consegue emitir certificado
                        // (ver GerenciarAtendimentoAC::verificarCertificado).
                        $codI = (int) $objL->getCodInscricao();
                        if ($codI && isset($mapaPag[$codI])) {
                            $pg  = $mapaPag[$codI];
                            $qtd = (int) $pg['pagamentos'];

                            if ($qtd > 0) {
                                echo "<span style='color:#1e7e34;'>&#10003; " . $qtd
                                   . ($qtd == 1 ? " pagamento" : " pagamentos") . "</span>";
                            } else {
                                echo "<span style='color:#999;'>sem pagamento</span>";
                            }
                            if ($pg['isento'] != '') {
                                echo "<br><span style='display:inline-block; padding:1px 7px; border-radius:10px;"
                                   . " background:#eef3f8; color:#3d6b95; font-weight:600;'>"
                                   . htmlspecialchars($pg['isento']) . "</span>";
                            }
                            if ($pg['voucher']) {
                                echo "<br><span style='display:inline-block; padding:1px 7px; border-radius:10px;"
                                   . " background:#fdf4e6; color:#b07a1c; font-weight:600;'>voucher</span>";
                            }
                            // Sem pagamento, sem isencao e sem voucher = a emissao do
                            // certificado e bloqueada. Melhor avisar aqui do que a
                            // pessoa descobrir na hora de emitir.
                            if ($qtd == 0 && $pg['isento'] == '' && !$pg['voucher']) {
                                echo "<br><span style='color:#c00;' title='Sem pagamento, isen&ccedil;&atilde;o ou voucher:"
                                   . " a emiss&atilde;o do certificado fica bloqueada'>&#9888; sem certificado</span>";
                            }
                        } else {
                            echo "<span style='color:#bbb;'>&mdash;</span>";
                        }
                        ?>
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                        <?php
                        // Presenca do inscrito (tab_inscritos.flg_presenca_inscrito): checkbox
                        // que alterna presente/ausente pelo mesmo metodo ja usado nas
                        // listagens (ListarInscritos::alternarPresencaInscrito).
                        if($objL->getCodInscricao()){
                            $presente = $objL->getFlgPresencaInscrito() ? true : false;
                            ?>
                            <?php /* Coluna MESTRE de liberacao. O mecanismo continua sendo a
                                     presenca (flg_presenca_inscrito) -- desmarcar marca AUSENTE de
                                     verdade (afeta relatorios/declaracao). Rotulos Liberado/Bloqueado
                                     porque, na pratica, presenca = libera/bloqueia TODOS os certificados. */ ?>
                            <label style="font-weight:normal; margin:0; cursor:pointer;" title="Liberação geral (presença no evento) — libera/bloqueia TODOS os certificados. Desmarcar marca a pessoa como ausente.">
                                <input type="checkbox"
                                       id="presencaInscrito-<?php echo $objL->getCodInscricao() ?>"
                                       onclick="alternarPresencaInscrito(<?php echo $objL->getCodInscricao() ?>)"
                                       <?php echo $presente ? 'checked' : '' ?>>
                                <span id="statusPresencaInscrito-<?php echo $objL->getCodInscricao() ?>"
                                      style="color:<?php echo $presente ? 'green' : 'red' ?>;"><?php echo $presente ? 'Liberado' : 'Bloqueado' ?></span>
                            </label>
                            <?php
                        } else {
                            echo "<span style='color:#bbb;'>&mdash;</span>";
                        }
                        ?>
                    </td>
                    <?php
                    // Coluna "Certificado geral de participacao" -- so aparece em evento
                    // que emite o geral (certificado_congressista=1). Opt-out por pessoa:
                    // flg_bloquear_certificado_geral NULL/0 = recebe (verde); 1 = bloqueado.
                    if (!empty($fws_['mostrar_coluna_geral'])) {
                        ?>
                        <td style="white-space:nowrap; font-size:12px;">
                            <?php
                            if ($objL->getCodInscricao()) {
                                $codIG      = (int) $objL->getCodInscricao();
                                $bloqueado  = $objL->getFlgBloquearCertificadoGeral() ? true : false;
                                ?>
                                <?php /* Interruptor de liberacao do certificado GERAL, irmao dos
                                         checkboxes de perfil ao lado (mesma regra: marcado = libera).
                                         Vem marcado por padrao (NULL = libera); o coordenador
                                         DESMARCA quem nao deve receber o geral. Presenca (coluna
                                         mestre) libera/bloqueia TODOS -- por isso NAO repetimos
                                         presenca aqui, o que antes confundia. Desmarcado fica em
                                         vermelho para a excecao saltar aos olhos. */ ?>
                                <label id="lblGeral-<?php echo $codIG ?>" style="font-weight:normal; margin:0; cursor:pointer; display:inline-flex; align-items:center; gap:6px;<?php echo $bloqueado ? ' color:#b3261e;' : '' ?>"
                                       title="Marcado = libera o certificado de participação geral. Desmarque para esta pessoa NÃO receber o geral (a presença continua liberando/bloqueando todos).">
                                    <input type="checkbox"
                                           id="bloqGeral-<?php echo $codIG ?>"
                                           onclick="alternarBloqueioCertificadoGeral(<?php echo $codIG ?>)"
                                           <?php echo $bloqueado ? '' : 'checked' ?>>
                                    <span>Participação</span>
                                </label>
                                <?php
                            } else {
                                echo "<span style='color:#bbb;'>&mdash;</span>";
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <td>
                        <?php
                        if($objL->getCodInscricao()){
                            $fws_['cod_evento']               = $objL->getCodEvento();
                            $fws_['cod_inscrito']               = $objL->getCodInscricao();
                            $fws_['lista_perfil_administrativo'] =  explode(',', $objL->getListaPerfis());
                            $this->carregarListaPerfisCheckbox($fws_, $debug);
                        } else {
                            echo "InscritoPerfil não cadastrado no evento";
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
                <br>Total de InscritoPerfils cadastrados: <?php echo  $ii ?>.
                <br>Total de InscritoPerfils já inscritos: <?php echo  $totalJaInscrito ?>.
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan=4>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }
    /**@todo implementar e testar tratamentos de segurança */
    public function associarInscritoPerfilAdminstrativo($fws_, $debug = false) {
        // ControleAcesso::ifUserInRole('gerenciarEventos,coordenarEventos', 'url');
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
    
    /**
     * Dados para o modal EDITAR a carga horaria de uma pessoa NAQUELE perfil
     * (relacao-perfil, tab_lig_inscritos_perfis).
     *
     * "A relacao vence o padrao": quando a ligacao ja tem qualquer carga propria
     * (total/teorica/pratica), o modal deve mostrar a carga da RELACAO no lugar da
     * carga padrao do perfil. Sem carga na ligacao, mostra o padrao do perfil
     * (carga_horaria_certificado) como base. A decisao de qual usar fica no JS.
     */
    public function carregarCargaPerfil($fws_, $debug = false) {
        $codInscrito = isset($fws_['cod_inscrito']) ? (int) $fws_['cod_inscrito'] : 0;
        $codPerfil   = isset($fws_['cod_perfil'])   ? (int) $fws_['cod_perfil']   : 0;

        // Pessoa (nome/cpf/email/presenca) -- so leitura; o modal trava esses campos.
        $insc = new Inscrito();
        $insc->setCodEvento(COD_EVENTO);
        $insc->setCodInscrito($codInscrito);
        $insc->getInscritoById($debug);

        // Perfil (titulo + carga padrao).
        $perfil = new Perfil();
        $perfil->setCodEvento(COD_EVENTO);
        $perfil->setCodPerfil($codPerfil);
        $perfil->getPerfilById($debug);

        // Ligacao inscrito<->perfil: carga PERSONALIZADA que vence o padrao.
        $lig = new InscritoPerfil();
        $lig->setCodEvento(COD_EVENTO);
        $lig->setCodInscrito($codInscrito);
        $lig->setCodPerfil($codPerfil);
        $lig->getInscritoPerfilById($debug);

        // Banco em ISO-8859-1; json_encode exige UTF-8 (senao volta vazio nos acentos).
        $u = function ($s) { return mb_convert_encoding((string) $s, 'UTF-8', 'ISO-8859-1'); };

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage'    => '1',
            'cod_inscrito'     => $codInscrito,
            'cod_perfil'       => $codPerfil,
            'nome_inscrito'    => $u($insc->getNomeInscrito()),
            'cpf'              => (string) $insc->getCpf(),
            'e_mail'           => (string) $insc->getEMail(),
            'presente'         => $insc->getFlgPresencaInscrito() ? 1 : 0,
            'perfil_titulo'    => $u($perfil->getTitulo()),
            'perfil_ch_padrao' => (string) $perfil->getCargaHorariaCertificado(),
            'lig_ch'           => (string) $lig->getCargaHoraria(),
            'lig_ch_teorica'   => (string) $lig->getCargaHorariaTeorica(),
            'lig_ch_pratica'   => (string) $lig->getCargaHorariaPratica(),
        ));
        exit;
    }

    /**
     * Grava a carga horaria personalizada da pessoa NAQUELE perfil
     * (tab_lig_inscritos_perfis). Alimenta o certificado administrativo/congressista.
     * Vazio grava NULL -> volta a usar o padrao do perfil.
     */
    public function salvarCargaPerfil($fws_, $debug = false) {
        $ok  = 0;
        $msg = '';
        try {
            $codInscrito = isset($fws_['cod_inscrito']) ? (int) $fws_['cod_inscrito'] : 0;
            $codPerfil   = isset($fws_['cod_perfil'])   ? (int) $fws_['cod_perfil']   : 0;
            if ($codInscrito <= 0 || $codPerfil <= 0) {
                throw new Exception("Inscrito ou perfil não informado.");
            }

            $lig = new InscritoPerfil();
            $lig->setCodEvento(COD_EVENTO);
            $lig->setCodInscrito($codInscrito);
            $lig->setCodPerfil($codPerfil);
            $lig->setCargaHoraria($this->vazioParaNull($fws_, 'perfil_carga_horaria'));
            $lig->setCargaHorariaTeorica($this->vazioParaNull($fws_, 'perfil_carga_horaria_teorica'));
            $lig->setCargaHorariaPratica($this->vazioParaNull($fws_, 'perfil_carga_horaria_pratica'));

            // Editar = a ligacao existe -> UPDATE direcionado (PK composta, sem salvar()).
            // Se por acaso nao existir, cria (INSERT) para nao perder o registro.
            if ($lig->inscritoPerfilExiste($debug)) {
                $lig->atualizarCarga($debug);
            } else {
                $lig->setStatus('ATIVADA');
                $lig->salvar($debug);
            }
        } catch (OKException $e) {
            $ok  = 1;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $ok  = 0;
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => (string) $ok, 'message' => $msg));
        exit;
    }

    /**
     * Liga/desliga o bloqueio do certificado GERAL de participacao de uma pessoa.
     * Opt-out: 'bloquear'=1 grava 1 (nao recebe o geral); vazio grava 0 (recebe).
     * O certificado ESPECIAL do papel (liberado pelos checkboxes) nao e afetado.
     */
    public function alternarBloqueioCertificadoGeral($fws_, $debug = false) {
        $ok  = 0;
        $msg = '';
        $bloqueado = 0;
        try {
            $codInscrito = isset($fws_['cod_inscrito']) ? (int) $fws_['cod_inscrito'] : 0;
            if ($codInscrito <= 0) {
                throw new Exception("Inscrito não informado.");
            }
            // 'bloquear' = estado desejado apos o clique (checkbox marcado = bloquear).
            $bloqueado = !empty($fws_['bloquear']) ? 1 : 0;

            $obj = new Inscrito();
            $obj->setCodEvento(COD_EVENTO);
            $obj->setCodInscrito($codInscrito);
            $obj->setFlgBloquearCertificadoGeral($bloqueado);
            $obj->bloquearCertificadoGeral($debug);

            $ok  = 1;
            $msg = $bloqueado ? "Certificado geral bloqueado" : "Certificado geral liberado";
        } catch (Exception $e) {
            $ok  = 0;
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => (string) $ok,
            'bloqueado'     => $bloqueado,
            'message'       => $msg,
        ));
        exit;
    }

    public function carregarListaPerfisCheckbox($fws_, $debug=false) {
        $lista_perfil_inscrito = $fws_['lista_perfil_administrativo'];
        $obj = new Perfil();
        $obj->setCodEvento($fws_['cod_evento']);
        $orderBy = ' order by op.titulo ';
        $obj->carregarListaPerfil($orderBy,$debug);

        // Carga PERSONALIZADA da pessoa por perfil (tab_lig_inscritos_perfis).
        // Sao os 3 campos do modal: carga_horaria (total), _teorica e _pratica.
        // QUALQUER um dos 3 preenchido = carga propria -> vence o padrao do perfil
        // (op.carga_horaria_certificado). Mesma regra do certificado (GerenciarCertificadoAC).
        $mapaCargaLig = array();
        try {
            $objLig = new InscritoPerfil();
            $objLig->setCodEvento($fws_['cod_evento']);
            $objLig->setCodInscrito($fws_['cod_inscrito']);
            $objLig->carregarListaPerfilByIdInscrito($debug);
            foreach ($objLig->getListaPerfil() as $lp) {
                $t   = trim((string) $lp->getCargaHoraria());
                $teo = trim((string) $lp->getCargaHorariaTeorica());
                $pra = trim((string) $lp->getCargaHorariaPratica());
                if ($t !== '' || $teo !== '' || $pra !== '') {
                    $mapaCargaLig[(int) $lp->getCodPerfil()] = array('total' => $t, 'teo' => $teo, 'pra' => $pra);
                }
            }
        } catch (Exception $e) {
            // sem ligacoes com carga propria -> usa o padrao do perfil
        }

        $i=0;
        foreach ($obj->getListaPerfil() as $objL) {
            // if($objL->getReceberInscricao()!=1 and $objL->getLiberarCertificado()==1){
            if($objL->getLiberarCertificado()==1){
//
                echo " <div><input id='associar-perfil-".$objL->getCodPerfil()."-".$fws_['cod_inscrito']."' class='perfil' ";
                echo " type='checkbox' name='lista_cod_perfil[]' value='{$objL->getCodPerfil()}' ";
                echo " title='Associar a grupos administrativo'";
                echo " onclick=\"associarInscritoPerfilAdministrativo('".$fws_['cod_inscrito']."','".$objL->getCodPerfil()."')\"";
                echo ' '.verifItemChecked($objL->getCodPerfil(), $lista_perfil_inscrito,$debug).' ';
                echo ' />&nbsp;';
                // Carga personalizada da ligacao (qualquer dos 3 campos) vence o padrao do perfil.
                $cargaLig = isset($mapaCargaLig[(int) $objL->getCodPerfil()]) ? $mapaCargaLig[(int) $objL->getCodPerfil()] : null;
                if ($cargaLig !== null) {
                    // Formata cada valor: se veio so numero acrescenta 'h'; ja escapa p/ HTML.
                    $fmt = function ($v) {
                        $v = trim((string) $v);
                        if ($v === '') { return ''; }
                        $v = preg_match('/[a-zA-Z]/', $v) ? $v : $v . 'h';
                        return htmlspecialchars($v);
                    };
                    $total = $fmt($cargaLig['total']);
                    $teo   = $fmt($cargaLig['teo']);
                    $pra   = $fmt($cargaLig['pra']);
                    $partes = array();
                    if ($teo !== '') { $partes[] = $teo . ' te&oacute;r.'; }
                    if ($pra !== '') { $partes[] = $pra . ' pr&aacute;t.'; }
                    if ($total !== '') {
                        $texto = $total;
                        if ($partes) { $texto .= ' (' . implode(' + ', $partes) . ')'; }
                    } else {
                        $texto = implode(' + ', $partes);
                    }
                    // Nome do perfil na frente, carga horaria atras.
                    echo $objL->getTitulo()
                       . " - <span title='Carga hor&aacute;ria personalizada nesta pessoa &mdash; vence a carga padr&atilde;o do perfil' style='font-weight:600; color:#1e7e34;'>"
                       . $texto . "</span>";
                } else {
                    echo $objL->getTitulo() ." - ". $objL->getCargaHorariaCertificado() ."h.";
                }
                // Lapis para editar a carga horaria da pessoa NAQUELE perfil (relacao-perfil).
                // So aparece quando a pessoa ja tem o perfil vinculado (ha ligacao para editar).
                $temPerfil = is_array($lista_perfil_inscrito) && in_array($objL->getCodPerfil(), $lista_perfil_inscrito);
                // O lapis tem id proprio: o JS de associacao o cria/remove na hora
                // que marca/desmarca o perfil, sem precisar de reload.
                if ($temPerfil) {
                    echo " <a id='pencilPerfil-".$objL->getCodPerfil()."-".$fws_['cod_inscrito']."' href='javascript:void(0)' title='Editar carga horária desta pessoa neste perfil'"
                       . " onclick=\"editarCargaPerfil('".$fws_['cod_inscrito']."','".$objL->getCodPerfil()."')\""
                       . " style='text-decoration:none; color:#2e7d32;'><i class='fa fa-pencil'></i></a>";
                }
                echo "&nbsp;&nbsp;&nbsp;</div>";
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
    
    public static function carregarListaPerfisAdministrativos($fws_, $debug=false) {
        echo "<h4>Associar Grupos/Perfis</h4>";

        #echo $COD_CONVIDADO."<br>";
        $objConvidado = new Convidado();
        if($fws_['cod_convidado']!=''){
            $objConvidado->setCodConvidado($fws_['cod_convidado']);
            $objConvidado->getConvidadoById($debug);
        }else if($fws_['e_mail']!=''){
            $objConvidado->setEMail($fws_['e_mail']);
            $objConvidado->getConvidadoByEmail($debug);
        }else{
            $objConvidado->setCpf($fws_['cpf']);
            $objConvidado->getConvidadoByCpf($debug);
        }
        // if($_SERVER['REMOTE_ADDR']=='177.82.0.28'){
        //     $debug = 1;
        // }
        $objLogin = $objConvidado->getObjLoginByIdUsuario($debug);
        if ($objLogin->getStatus()=='ATIVADA') {
            $objLogin->carregarListaGruposByIdUsuario($debug);
            if(count($objLogin->getListaGrupos() ?? [])>0){
                foreach ($objLogin->getListaGrupos() as $objLG) {
                   $array_grupo_login[] = $objLG->getCodGrupo();
                }
            }
            $objG = new GrupoSistema();
            $objG->carregarListaGruposByIdEvento($debug);
            if(count($objG->getListaGrupos() ?? [])>0){
                foreach ($objG->getListaGrupos() as $objL) {
                    echo '<input '. verifItemChecked($objL->getCodGrupo(), $array_grupo_login).' type = "checkbox"'
                            . ' onclick="alternarGrupoUsuario(\''.$objL->getCodGrupo().'\',\''.$objLogin->getCodUsuario().'\',this);" href="javascript:void(0);"'
                            . ' name = "cod_grupo[]" value = "ON"/>'
                            . ' - '. strtoupper($objL->getNomeGrupo()).'<br>';
                }
            }
        }else{
            echo "Convidado nao possui login cadastrado.";
        }
    }
}
