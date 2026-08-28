<?php

#---- Busca Trabalhos------------------------------------
/*
 * Documentos / trabalhos do inscrito.
 *
 * Antes: blockquote corrido em que os rotulos eram o NOME DA COLUNA do banco
 * ("Nome_co_autor_1", "Autor_apresentador_e_mail"), com os icones .gif
 * repetidos em cada documento.
 *
 * Agora: um cartao por documento, rotulos em portugues, autores agrupados e
 * acoes numa barra unica. O resumo e a justificativa aparecem INTEIROS.
 */
 if($objEvConf->getTrabSubmissao()==1){
    $debug = false;

        $var_sql ="SELECT
         toa.titulo
        ,ti.cod_inscrito
        ,ti.nome_inscrito
        ,tt.cod_trabalho
        ,tt.autor_apresentador_nome
        ,tt.autor_apresentador_e_mail
        ,tt.nome_co_autor_1
        ,tt.nome_co_autor_2
        ,tt.nome_co_autor_3
        ,tt.nome_co_autor_4
        ,tt.nome_co_autor_5
        ,tt.nome_co_autor_6
        ,tt.instituicao_trabalho
        ,tt.titulo_trabalho
        ,tt.palavras_chave
        ,tt.resumo_trabalho
        ,tt.arquivo_upload
        ,tt.arquivo_gerado
        ,tt.flg_enviado_avaliacao
        ,tt.premiacao
        ,tt.status
        ,tt.pontos_total
        ,tt.pontos_total_avaliado
        ,tt.pontos_total_confirmado
        ,tt.versao
        ,tt.tipo_documento
        ,tt.data_inclusao
        ,tt.data_alteracao
        ,tt.flg_assinado
        ,tt.flg_permitir_alterar
        ,tt.flg_permitir_alterar_data_fim
        ,tt.periodo_referencia
        FROM tab_trabalhos tt
        INNER JOIN tab_inscritos ti ON (tt.cod_inscrito      = ti.cod_inscrito)
        LEFT JOIN tab_opcoes_areas_tematicas toa  ON (tt.cod_area_tematica = toa.cod_area_tematica)
        WHERE (tt.cod_evento        = ".$cod_evento.")and
              (tt.cod_inscrito      = ".$cod_inscrito.")
        ORDER BY toa.titulo
        ";

         echo "<fieldset><legend> ".$objEvConf->getTrabAnexoTipo()." </legend>";

         // Acao do bloco: incluir sai do laco e aparece uma vez.
         echo "<div class='card-barra-topo'>";
         echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/inscritos-trabalho-cadastro-incluir.php"
              . "&cod_inscrito=".$cod_inscrito."&cod_evento=".$cod_evento."'>"
              . "<i class='glyphicon glyphicon-plus'></i> Incluir ".htmlspecialchars($objEvConf->getTrabAnexoTipo(), ENT_QUOTES, 'UTF-8')."</a>";
         echo "</div>";

         If ($RS_T=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)) {
          If (!$RS_T->EOF) {
            $totDocumentos = 0;
            While (!$RS_T->EOF) {
              #Adicionando nas variaveis, os valores dos campos retirados dos registros
              $titulo                     = $RS_T->fields[0];
              $cod_inscrito               = $RS_T->fields[1];
              $nome_inscrito              = $RS_T->fields[2];
              $cod_trabalho               = $RS_T->fields[3];
              $autor_apresentador_nome    = $RS_T->fields[4];
              $autor_apresentador_e_mail  = $RS_T->fields[5];
              $nome_co_autor_1            = $RS_T->fields[6];
              $nome_co_autor_2            = $RS_T->fields[7];
              $nome_co_autor_3            = $RS_T->fields[8];
              $nome_co_autor_4            = $RS_T->fields[9];
              $nome_co_autor_5            = $RS_T->fields[10];
              $nome_co_autor_6            = $RS_T->fields[11];
              $instituicao_trabalho       = $RS_T->fields[12];
              $titulo_trabalho            = $RS_T->fields[13];
              $palavras_chave             = $RS_T->fields[14];
              $resumo_trabalho            = $RS_T->fields[15];
              $arquivo_upload             = $RS_T->fields[16];
              $arquivo_gerado             = $RS_T->fields[17];
              $flg_enviado_avaliacao      = $RS_T->fields[18];
              $premiacao = $RS_T->fields[19];
              $statusT   = $RS_T->fields[20];
              $pontos_total = $RS_T->fields[21];
              $pontos_total_avaliado   = $RS_T->fields[22];
              $pontos_total_confirmado = $RS_T->fields[23];
              $versao = $RS_T->fields[24];
              $tipo_documento = $RS_T->fields[25];
              if ($tipo_documento == 'COMPROVANTE_PERFIL') { /*comprovante anexado nos dados pessoais (config do perfil)*/
                  $tipo_documento = 'Comprovante do perfil';
              }
              $data_inclusao = $RS_T->fields['data_inclusao'];
              $data_alteracao = $RS_T->fields['data_alteracao'];

              $flg_assinado = $RS_T->fields['flg_assinado'];
              $flg_permitir_alterar = $RS_T->fields['flg_permitir_alterar'];
              $flg_permitir_alterar_data_fim = $RS_T->fields['flg_permitir_alterar_data_fim'];
              $periodo_referencia = $RS_T->fields['periodo_referencia'];

              /**@todo bloqueando acesso a outros documentos */
              // evento 108: só pode ver Currículo
            $liberarAcessoDocumentos = true;
            if ($objEv->getCodEvento() == 108 && $tipo_documento != 'Currículo') {
                // aqui você BLOQUEIA o acesso
                // ex: return; die(); redirect; etc.
                $liberarAcessoDocumentos = false;
            }
            if($liberarAcessoDocumentos){
                $totDocumentos++;

                if($flg_enviado_avaliacao){
                    $descFlgEnviadoAvaliacao = "SIM";
                }  else {
                    $descFlgEnviadoAvaliacao = "NÃO";
                }

                $var_sql = "select ta.avaliacao,ta.observacao "
                        . "from tab_trabalhos_avaliacao ta "
                        . "where (ta.cod_trabalho=".$cod_trabalho.")";
                If ($RS_A=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)) {
                    $avaliacao = $RS_A->fields[0];
                    $observacao= $RS_A->fields[1];
                }

                $assinado  = ($flg_assinado == 1);
                $enviadoAv = (bool) $flg_enviado_avaliacao;

                echo "<div class='card-item" . ($assinado ? " card-ok" : "") . "'>";

                // --- cabecalho: o que e o documento ---
                // O titulo do documento vem primeiro; antes so aparecia bem
                // depois do bloco de autores.
                $tituloCartao = $titulo_trabalho != ''
                              ? $titulo_trabalho
                              : ($tipo_documento != '' ? $tipo_documento : $objEvConf->getTrabAnexoTipo() . ' ' . $cod_trabalho);
                // Quando o proprio tipo virou titulo (documento sem titulo
                // proprio), a badge de tipo repetiria a mesma palavra ao lado.
                $tituloVeioDoTipo = ($titulo_trabalho == '' && $tipo_documento != '');
                echo "<div class='card-cab'>";
                echo   "<span class='card-titulo'>" . htmlspecialchars($tituloCartao, ENT_QUOTES, 'UTF-8') . "</span>";
                if ($tipo_documento != '' && !$tituloVeioDoTipo) {
                    echo "<span class='card-badge card-badge-neutro'>" . htmlspecialchars($tipo_documento, ENT_QUOTES, 'UTF-8') . "</span>";
                }
                if ($statusT != '') {
                    echo "<span class='card-badge card-badge-info'>" . htmlspecialchars($statusT, ENT_QUOTES, 'UTF-8') . "</span>";
                }
                if ($assinado) {
                    echo "<span class='card-badge card-badge-ok'>ASSINADO</span>";
                }
                if ($objEvConf->getTrabAvaliacao()) {
                    echo "<span class='card-badge " . ($enviadoAv ? "card-badge-ok" : "card-badge-espera") . "'>"
                       . ($enviadoAv ? "ENVIADO P/ AVALIAÇÃO" : "NÃO ENVIADO P/ AVALIAÇÃO") . "</span>";
                }
                echo "</div>";

                // --- metadados: os mesmos campos da lista antiga, com rotulo ---
                // "Cód. <TrabAnexoTipo>" saia como "Cód. Documentos" — o rotulo
                // configuravel esta no plural e nao cabe aqui.
                $metaPrincipal = array('Código' => $cod_trabalho);
                // Tipo so entra quando ja nao e o titulo do cartao.
                if (!$tituloVeioDoTipo) {
                    $metaPrincipal['Tipo'] = $tipo_documento;
                }
                $metaPrincipal['Versão']     = $versao;
                $metaPrincipal['Referência'] = ($periodo_referencia != '' ? $periodo_referencia : 'Não informado');
                $metaPrincipal['Assinado']   = ($assinado ? 'Sim' : 'Não informado');
                echo montarMeta($metaPrincipal);
                $metaSubst = array(
                    'Permitir substituição' => ($flg_permitir_alterar ? 'Sim' : 'Não'),
                );
                if ($flg_permitir_alterar_data_fim) {
                    $metaSubst['Substituir até'] = formatDateView($flg_permitir_alterar_data_fim);
                }
                $metaSubst['Inclusão']  = formatData($data_inclusao);
                $metaSubst['Alteração'] = formatData($data_alteracao);
                echo montarMeta($metaSubst);

                // --- classificacao ---
                if ($titulo != '' || $palavras_chave != '' || $instituicao_trabalho != '') {
                    echo montarSecao('Classificação');
                    echo "<div class='card-grid'>";
                    echo montarCampo('Área temática', $titulo, false, true);
                    echo montarCampo('Instituição',   $instituicao_trabalho, false, true);
                    echo montarCampo('Palavras-chave',$palavras_chave, false, true);
                    echo "</div>";
                }

                // --- autores ---
                // Os seis co-autores continuam todos aqui; o que mudou e o
                // rotulo, que era o nome cru da coluna do banco.
                echo montarSecao('Autores');
                echo "<div class='card-grid'>";
                echo montarCampo('Autor apresentador',           $autor_apresentador_nome);
                echo montarCampo('E-mail do autor apresentador', $autor_apresentador_e_mail);
                echo montarCampo('Co-autor 1', $nome_co_autor_1);
                echo montarCampo('Co-autor 2', $nome_co_autor_2);
                echo montarCampo('Co-autor 3', $nome_co_autor_3);
                echo montarCampo('Co-autor 4', $nome_co_autor_4);
                echo montarCampo('Co-autor 5', $nome_co_autor_5);
                echo montarCampo('Co-autor 6', $nome_co_autor_6);
                echo "</div>";

                // --- conteudo: resumo INTEIRO, sem corte ---
                // Titulo so como texto proprio quando NAO virou o titulo do
                // cartao — senao aparece duas vezes na mesma tela.
                if ($titulo_trabalho == '') {
                    echo montarTexto('Título', $titulo_trabalho);
                }
                echo montarTexto('Resumo', $resumo_trabalho);

                // --- arquivos anexados, uma linha por versao ---
                if ($arquivo_gerado != ''){
                    // Nome do último arquivo conhecido
                    $ultimo_arquivo = $arquivo_gerado;
                    preg_match('/_(\d+)\.(pdf|txt|doc|docx|xls|xlsx|jpg|jpeg|png)$/', $ultimo_arquivo, $matches);
                    $ultima_versao = isset($matches[1]) ? intval($matches[1]) : 0;
                    $extensao = isset($matches[2]) ? "." . $matches[2] : "";
                    $nome_arquivo_base = preg_replace('/_\d+\.(pdf|txt|doc|docx|xls|xlsx|jpg|jpeg|png)$/', '', $ultimo_arquivo);

                    echo montarSecao('Arquivos anexados');
                    echo "<div class='card-arquivos'>";
                    for ($i = $ultima_versao; $i >= 1; $i--) {
                        $arquivo_gerado = $nome_arquivo_base . "_" . $i . $extensao;
                        echo "<div class='card-arquivo'>";
                        echo "<a href=\"javascript: visualiza('$arquivo_gerado');\" title='Visualizar Resumo'>";
                        echo retornarIconeExtensao($arquivo_gerado);
                        echo " Arquivo versão: ".$i."</a>";
                        echo $data_inclusao?  " <span style='color:#999;'>&middot; inclusão ".formatData($data_inclusao)."</span>" : '';
                        echo $data_alteracao? " <span style='color:#999;'>&middot; alteração ".formatData($data_alteracao)."</span>" : '';
                        echo "</div>";
                    }
                    echo "</div>";
                }

                // --- avaliacao ---
                if ($objEvConf->getTrabAvaliacao()){
                    echo montarSecao('Avaliação');
                    // "Enviado para avaliacao" nao se repete aqui: ja e a badge
                    // do cabecalho, que aparece sempre que ha avaliacao no evento.
                    echo "<div class='card-grid'>";
                    echo montarCampo('Avaliação',              @$avaliacao);
                    echo montarCampo('Pontos informados',      $pontos_total);
                    echo montarCampo('Pontos avaliados',       $pontos_total_avaliado);
                    echo montarCampo('Pontos confirmados',     $pontos_total_confirmado);
                    echo "</div>";
                    // Justificativa inteira: e o texto que explica uma reprovacao.
                    echo montarTexto('Justificativa (exigida somente em caso de reprovação)', @$observacao);
                }

                // --- acoes: todas juntas, destrutiva por ultimo ---
                echo "<div class='card-acoes'>";
                echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/inscritos-trabalho-cadastro-alterar.php"
                     . "&cod_trabalho=".$cod_trabalho."&cod_evento=".$cod_evento."'>Alterar</a>";
                if ((FWA_Autenticacao($_SESSION['_cod_usuario'], 999, 5, false, false))) {
                    echo "<a class='btn btn-sm btn-link card-excluir' href='view/admin/inscritos-trabalho-cadastro-excluir-grava.php"
                       . "?cod_inscrito=".$cod_inscrito."&cod_trabalho=".$cod_trabalho."&cod_evento=".$cod_evento."'"
                       . " title='Excluir este documento'>Excluir</a>";
                }
                echo "</div>";

                echo "</div>";  // .card-item

                if($ipLiberado and $cod_evento==1){
                    $objTrabalho= new Trabalho();
                    if($cod_trabalho and $cod_inscrito){
                        $objTrabalho->setCodInscrito($cod_inscrito);
                        $objTrabalho->setCodTrabalho($cod_trabalho);
                        $objTrabalho->getTrabalhoById();
                    }
                    ?>
                    <div class="card-item">
                        <div class="card-secao">Prêmio Jovens Melhoristas</div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <label for="premiacao">Deseja concorrer ao <b>Prêmio Jovens Melhoristas</b>? </label>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <input type='radio' name='premiacao' value='NAO'         <?php echo verifItemChecked($objTrabalho->getPremiacao(), 'NAO') ?> >  Não desejo participar.
                            <!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='premiacao' value='1-PREMIACAO' <?php echo verifItemChecked($objTrabalho->getPremiacao(), '1-PREMIACAO') ?> >-->
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <input type='radio' name='premiacao' value='2-PREMIACAO' <?php echo verifItemChecked($objTrabalho->getPremiacao(), '2-PREMIACAO') ?> >
                            desejo participar na categoria Iniciação Científica.
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <input type='radio' name='premiacao' value='3-PREMIACAO' <?php echo verifItemChecked($objTrabalho->getPremiacao(), '3-PREMIACAO') ?> >
                            desejo participar na categoria Dissertação de Mestrado.
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <input type='radio' name='premiacao' value='4-PREMIACAO' <?php echo verifItemChecked($objTrabalho->getPremiacao(), '4-PREMIACAO') ?> >
                            desejo participar na categoria Tese de Doutorado.
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                <?php }?>
<?php
              }
              #Movendo para o proximo registro
              $RS_T->MoveNext();
            }
            if ($totDocumentos > 0) {
                echo "<div class='card-total'><span>Documentos <b>".$totDocumentos."</b></span></div>";
            } else {
                echo montarVazio('Nenhum documento disponível para visualização.');
            }
          }Else{
            echo montarVazio('Nenhum '.$objEvConf->getTrabAnexoTipo().' cadastrado para este inscrito.');
          }
        }
        echo "</fieldset>";
 }
 #--------------------------------------------------------
#echo  "<br><br>";
#---- Busca Rpa ------------------------------------

#---- Busca Boleto ------------------------------------
#---- Busca Contrato ------------------------------------
if($objEvConf->getContratoAnexar()==1){
    $objAnexContrato = new ArquivoContrato();
    $objAnexContrato->setCodTrabalho($cod_trabalho);
    $objAnexContrato->setCodInscrito($cod_inscrito);
    $txtBotaoAnexo = "Enviar";
    ?>
    <fieldset>
    <legend> Arquivo para o Contrato </legend>
    <div class="card-item">
        <div class="card-meta">Extensões permitidas: <b>pdf</b> &middot; tamanho máximo <b>25MB</b></div>
        <div class="card-arquivos">
    <?php
    try {
        // if(!$cod_trabalho){
        //     throw new Exception("Não foi possível localizar contratos.(cód.12)");
        // }
        /** @todo Cadastro de Contrato extra, para cadastrar qualquer tipo de documento extra, é necessário cadatrar um registro na tab_trabalhos antes, é preciso ajustar para que ao anexar avulsos, já cadastrar em tab_trabalhos quando o registro não existir */
        $objAnexContrato->carregarListaArquivoContratoBy('cod_trabalho');
        $ContratoAnexada = false;
        foreach ($objAnexContrato->getListaArquivoContrato() as $objListaAnexo) {
            if (file_exists(WB_PATH_LOG_CONTRATO.$objAnexContrato->getArquivoGerado())){
                $ContratoAnexada = true;
                echo "<div class='card-arquivo'>";
                retornarIconeExtensao($objAnexContrato->getArquivoGerado());
                echo "<a href=\"javascript:visualizarContrato('".$objAnexContrato->getArquivoGerado()."');\"
                title='Visualizar Contrato ".$objAnexContrato->getArquivoGerado()."'
                style='color:green;'>
                Visualizar Arquivo Contrato</a>";
                echo $objAnexContrato->getDataInclusao()? " <span style='color:#999;'>&middot; dt.alteração ".formatDateView($objAnexContrato->getDataInclusao())."</span>" : "";
                echo $objAnexContrato->getDataAlteracao()? " <span style='color:#999;'>&middot; dt.inclusão ".formatDateView($objAnexContrato->getDataAlteracao())."</span>" : "";
                echo "</div>";
                $txtBotaoAnexo = "Substituir";
            }else{
                echo "<div class='card-arquivo' style='color:#b71c1c;'>Arquivo ".$objAnexContrato->getArquivoGerado()." não encontrado, entre em contato com a administração.</div>";
            }
        }
    } catch (Exception $exc) {
        echo "<div class='card-arquivo' style='color:#999;'>".$exc->getMessage()."</div>";
    }
    // if($OP_PERMITIR_ANEXAR_CONTRATO){
        ?>
        </div>
        <form name="form-<?php echo $cod_trabalho?>" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoContrato();" enctype="multipart/form-data"  >
            <input type="hidden" name="c" value="GerenciarInscricao">
            <input type="hidden" name="m" value="salvarAnexoContrato">
            <input type="hidden" name="cod_trabalho" value="<?php echo $cod_trabalho?>">
            <input type="hidden" name="cod_inscrito" value="<?php echo $cod_inscrito?>">
            <input type="hidden" name="perspectiva" value="admin">
            <div class="card-acoes">
                <input type="file" name="arquivo" id="arquivo-<?php echo $cod_trabalho?>" value=""
                       placeholder="Inserir arquivo"  class="btn-xs" required="">
                <input type="submit" class="btn btn-sm btn-primary"
                role="button" value="<?php echo $txtBotaoAnexo; ?> arquivo referente ao Contrato: <?php echo $cod_trabalho?>">
            </div>
        </form>
    <?php
    // }
    //}#catch ?>
    <?php if($OP_ANEXAR_CONTRATO_ENCERRADO){ ?>
        <div class="card-meta" style="margin-top:8px;">
            Período de Envio de Contrato Encerrado: <b><?php echo formatDateView($objEvConf->getContratoAnexarDatFim()) ?></b>
        </div>
    <?php } ?>
    </div>

    <form name="getcontrato" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirContrato">
        <input type="hidden" value="" name="cod">
    </form>
    <script language="JavaScript" type="text/javascript">
        function visualizarContrato(cod){
            document.getcontrato.cod.value=cod;
            document.getcontrato.submit();
        }
    </script>
    </fieldset>
    <?php
    }
#---- Busca Boleto ------------------------------------
if($objEvConf->getBoletoAnexar()==1){
    $objAnexBoleto = new ArquivoBoleto();
    $objAnexBoleto->setCodTrabalho($cod_trabalho);
    $txtBotaoAnexo = "Enviar";
    ?>
    <fieldset>
    <legend> Arquivo para o Boleto </legend>
    <div class="card-item">
        <div class="card-meta">Extensões permitidas: <b>pdf</b> &middot; tamanho máximo <b>25MB</b></div>
        <div class="card-arquivos">
    <?php
    try {
        $objAnexBoleto->carregarListaArquivoBoletoBy('cod_trabalho');
        $BoletoAnexada = false;
        foreach ($objAnexBoleto->getListaArquivoBoleto() as $objListaAnexo) {
            if (file_exists(WB_PATH_LOG_BOLETO.$objAnexBoleto->getArquivoGerado())){
                $BoletoAnexada = true;
                echo "<div class='card-arquivo'>";
                retornarIconeExtensao($objAnexBoleto->getArquivoGerado());
                echo "<a href=\"javascript:void(0);\"
                onclick=\"visualizarBoleto('".$objAnexBoleto->getArquivoGerado()."');\"
                title='Visualizar Boleto ".$objAnexBoleto->getArquivoGerado()."'
                style='color:green;'>
                Visualizar Arquivo Boleto</a>";
                echo "</div>";
                $txtBotaoAnexo = "Substituir";
            }else{
                echo "<div class='card-arquivo' style='color:#b71c1c;'>Arquivo ".$objAnexBoleto->getArquivoGerado()." não encontrado, entre em contato com a administração.</div>";
            }
        }
    } catch (Exception $exc) { }
    // if($OP_PERMITIR_ANEXAR_BOLETO){
        ?>
        </div>
        <form name="form-<?php echo $cod_trabalho?>" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoBoleto();" enctype="multipart/form-data"  >
            <input type="hidden" name="c" value="GerenciarInscricao">
            <input type="hidden" name="m" value="salvarAnexoBoleto">
            <input type="hidden" name="cod_trabalho" value="<?php echo $cod_trabalho?>">
            <input type="hidden" name="cod_inscrito" value="<?php echo $cod_inscrito?>">
            <input type="hidden" name="perspectiva" value="admin">
            <div class="card-acoes">
                <input type="file" name="arquivo" id="arquivo-<?php echo $cod_trabalho?>" value=""
                       placeholder="Inserir arquivo da apresentação"  class="btn-xs" required="">
                <input type="submit" class="btn btn-sm btn-primary"
                role="button" value="<?php echo $txtBotaoAnexo; ?> arquivo referente ao Boleto: <?php echo $cod_trabalho?>">
            </div>
        </form>
    <?php
    // }
    //}#catch ?>
    <?php //if($OP_ANEXAR_BOLETO_ENCERRADO){ ?>
        <div class="card-meta" style="margin-top:8px;">
            Período de Envio de Boleto Encerrado: <b><?php echo formatDateView($objEvConf->getBoletoAnexarDatFim()) ?></b>
        </div>
    <?php //} ?>
    </div>

    <form name="getboleto" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirBoleto">
        <input type="hidden" value="" name="cod">
    </form>
    <script language="JavaScript" type="text/javascript">
        function visualizarBoleto(cod){
            document.getboleto.cod.value=cod;
            document.getboleto.submit();
        }
    </script>
    </fieldset>
    <?php
    }
