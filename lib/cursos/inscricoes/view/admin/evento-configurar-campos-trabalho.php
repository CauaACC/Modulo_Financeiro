<div id="tabsTrabalho">
    <ul>
        <li class="txtAba"><a href="#trabConfiguracao">Configuração Geral</a></li>
        <li class="txtAba"><a href="#trabAvaliacao">Avaliação</a></li>
        <li class="txtAba"><a href="#trabAnexosExtras">Anexos Extras</a></li>
        <li class="txtAba"><a href="#trabMensagem-1">Msg. Formulário</a></li>
        <li class="txtAba"><a href="#trabMensagem-2">Msg. Área Trabalho </a></li>
        <li class="txtAba"><a href="#trabMensagem-3">Msg. Área Recurso</a></li>
        <li class="txtAba"><a href="#trabMensagem-4">Msg. Após Anexar</a></li>
    </ul>

    <!-- Conteúdo das abas -->
    <div id="trabConfiguracao" class="tab-content">

        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração de Envio de Artigos/Trabalhos/Documentos</span></td>
            </tr>
            <tr>
                <td>Permitir submissão </td>
                <td>
                    <?php espelhoSimNao('trab_submissao', $objEvConf->getTrabSubmissao()) ?>
                </td>
            </tr>
            <?php
            // Sem perfil marcado com "verificar_envio_trabalho", a area de envio nao
            // aparece para ninguem — e isso ficava so na tela de Grupos de Participantes,
            // sem nenhuma pista aqui. O bloco abaixo mostra o estado e permite ajustar.
            $objPerfilEnvio = new Perfil();
            $objPerfilEnvio->setCodEvento(COD_EVENTO);
            $listaPerfilEnvio = array();
            try {
                $objPerfilEnvio->carregarListaPerfil();
                $listaPerfilEnvio = $objPerfilEnvio->getListaPerfil() ?: array();
            } catch (Exception $e) {
            }
            $habilitados = array();
            foreach ($listaPerfilEnvio as $p) {
                if ($p->getVerificarEnvioTrabalho() == 1) {
                    $habilitados[] = $p->getTitulo();
                }
            }
            ?>
            <tr>
                <td>Perfis que podem enviar</td>
                <td>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <span id="perfisEnvioResumo">
                            <?php if ($habilitados) { ?>
                                <?php foreach ($habilitados as $tituloPerfil) { ?>
                                    <span class="rotuloPerfilEnvio"><?php echo htmlspecialchars($tituloPerfil) ?></span>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="rotuloPerfilEnvio vazio">Nenhum &mdash; ningu&eacute;m ver&aacute; a &aacute;rea de envio</span>
                            <?php } ?>
                        </span>
                        <button type="button" id="btnPerfisEnvio" class="btn btn-sm abriModal">
                            <i class="fa fa-users"></i> Escolher perfis que podem enviar
                        </button>
                    </div>
                    <span style="font-size:11px; color:#777;">
                        Definido em cada grupo de participantes; aqui &eacute; s&oacute; um atalho.
                    </span>
                </td>
            </tr>
            <tr>
                <td>Tipo de Documento a ser anexado</td>
                <td>
                    <?php
                    // Agrupado por natureza do documento. Os VALORES sao os mesmos de
                    // sempre — o que ja esta gravado continua selecionando normalmente.
                    // Unico ajuste: "Artigo" tinha value='Arquivo' enquanto a comparacao
                    // era com 'Artigo', entao a opcao nunca ficava marcada. Nenhum evento
                    // usa esses dois valores hoje, entao foi padronizado para 'Artigo'.
                    $tiposDocumento = array(
                        'Contratos e termos' => array(
                            'Contrato de Prestação de Serviço',
                            'Termo de Concessão de Bolsa',
                        ),
                        'Documentos do inscrito' => array(
                            'Documentos',
                            'Documento',
                            'Currículo',
                            'Requerimento de Isenção',
                            'Solicitação de Impugnação de Edital',
                            'Processo',
                        ),
                        'Trabalho acadêmico' => array(
                            'Trabalho',
                            'Artigo',
                            'Resumo',
                            'Projeto TCC',
                            'Projeto do Aluno',
                            'Plano de Trabalho',
                        ),
                        'Outros' => array(
                            'Música',
                        ),
                    );
                    $tipoAtual = $objEvConf->getTrabAnexoTipo();
                    ?>
                    <select name="trab_anexo_tipo" id="trab_anexo_tipo">
                        <option value="">— selecione —</option>
                        <?php foreach ($tiposDocumento as $grupo => $tipos) { ?>
                            <optgroup label="<?php echo $grupo ?>">
                                <?php foreach ($tipos as $tipo) { ?>
                                    <option value="<?php echo htmlspecialchars($tipo) ?>"
                                        <?= verifItemSelected($tipoAtual, $tipo, false) ?>><?php echo $tipo ?></option>
                                <?php } ?>
                            </optgroup>
                        <?php } ?>
                        <?php
                        // valor gravado que nao esta em nenhum grupo nao pode se perder
                        $conhecidos = call_user_func_array('array_merge', array_values($tiposDocumento));
                        if ($tipoAtual != '' && !in_array($tipoAtual, $conhecidos)) { ?>
                            <optgroup label="Cadastrado neste projeto">
                                <option value="<?php echo htmlspecialchars($tipoAtual) ?>" selected><?php echo htmlspecialchars($tipoAtual) ?></option>
                            </optgroup>
                        <?php } ?>
                    </select>
                    <br><small style="color:#888;">Define como o documento &eacute; chamado na &aacute;rea do inscrito.</small>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span style="font-size:15px;font-weight: bold;">Período de Submissão</span><br>
                    Inicio <input type='datetime-local' name='trab_submissao_dat_ini' value='<?php echo $objEvConf->getTrabSubmissaoDatIni() ?>' onBlur="trim(this);">
                    Fim <input type='datetime-local' name='trab_submissao_dat_fim' value='<?php echo $objEvConf->getTrabSubmissaoDatFim() ?>' onBlur="trim(this);">
                </td>
            </tr>
            <tr>
                <td>Qtd trabalhos: </td>
                <td> <input type='text' name='trab_submissao_qtd' value='<?php echo $objEvConf->getTrabSubmissaoQtd() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td>Qtd no perfil: </td>
                <td> <input type='text' name='trab_submissao_qtd_perfil' value='<?php echo $objEvConf->getTrabSubmissaoQtdPerfil() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td colspan="2">Aceitar resumo por arquivo:
                    <input type="radio" name="trab_resumo_arquivo" value="1" <?= verifItemChecked($objEvConf->getTrabResumoArquivo(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_resumo_arquivo" value="0" <?= verifItemChecked($objEvConf->getTrabResumoArquivo(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Extensões de arquivos permitidos: </td>
                <td>
                    <?php
                    // O campo e apenas informativo: aparece para o inscrito como
                    // "Extensoes de arquivo permitidas sao: ..." e nao valida upload
                    // (a checagem no formulario do trabalho esta comentada). Por isso
                    // da para trocar a digitacao livre por marcacao, sem risco.
                    $extensoesComuns = array('PDF', 'DOC', 'DOCX', 'JPG', 'JPEG', 'PNG', 'PPTX', 'XLSX', 'ZIP');
                    $valorExt = trim($objEvConf->getTrabArquivoExtensao());
                    $marcadas = array();
                    foreach (preg_split('/[,;]/', $valorExt) as $pedaco) {
                        $pedaco = strtoupper(trim($pedaco, " \t.\n\r"));
                        if ($pedaco !== '' && in_array($pedaco, $extensoesComuns)) {
                            $marcadas[] = $pedaco;
                        }
                    }
                    ?>
                    <div id="blocoExtensoes">
                        <?php foreach ($extensoesComuns as $ext) { ?>
                            <label class="chipExtensao">
                                <input type="checkbox" class="chkExtensao" value="<?php echo $ext ?>"
                                    <?php echo in_array($ext, $marcadas) ? 'checked' : '' ?>>
                                <?php echo $ext ?>
                            </label>
                        <?php } ?>
                        <!-- o campo real continua sendo este; so a forma de preencher mudou -->
                        <input type="hidden" name="trab_arquivo_extensao" id="trab_arquivo_extensao"
                               value="<?php echo htmlspecialchars($valorExt) ?>">
                        <small style="color:#888;">Aparece para o inscrito na tela de envio.</small>
                    </div>
                </td>
            </tr>
            <!-- ============================================================ -->
            <!-- BOTÕES NA ÁREA DO INSCRITO                                     -->
            <!-- ============================================================ -->
            <tr>
                <td colspan="2">
                    <br><span style="font-size:15px;font-weight: bold;">Botões na Área do Inscrito</span>
                    <br><small style="color:#666;">Controla quais ações o inscrito pode realizar na área de trabalhos submetidos.
                    <br>Estes botões são automaticamente bloqueados quando o período de submissão encerra ou quando o trabalho entra em avaliação.</small>
                </td>
            </tr>
            <tr>
                <td>Permitir enviar anexo:</td>
                <td>
                    <input type="radio" name="trab_permitir_btn_enviar" value="1" <?= verifItemChecked($objEvConf->getTrabPermitirBtnEnviar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_permitir_btn_enviar" value="0" <?= verifItemChecked($objEvConf->getTrabPermitirBtnEnviar(), array('', 0, false), false) ?> /> Não
                    <br><small style="color:#888;">Exibe o botão para o inscrito enviar/submeter um novo trabalho</small>
                </td>
            </tr>
            <tr>
                <td>Permitir visualizar anexo após submissão:</td>
                <td>
                    <input type="radio" name="trab_permitir_btn_visualizar" value="1" <?= verifItemChecked($objEvConf->getTrabPermitirBtnVisualizar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_permitir_btn_visualizar" value="0" <?= verifItemChecked($objEvConf->getTrabPermitirBtnVisualizar(), array('', 0, false), false) ?> /> Não
                    <br><small style="color:#888;">Permite ao inscrito visualizar o arquivo que ele anexou</small>
                </td>
            </tr>
            <tr>
                <td>Permitir editar após submissão:</td>
                <td>
                    <input type="radio" name="trab_permitir_btn_editar" value="1" <?= verifItemChecked($objEvConf->getTrabPermitirBtnEditar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_permitir_btn_editar" value="0" <?= verifItemChecked($objEvConf->getTrabPermitirBtnEditar(), array('', 0, false), false) ?> /> Não
                    <br><small style="color:#888;">Exibe o botão Editar. Bloqueado automaticamente quando: o período encerra, ou o trabalho está em avaliação (SUBMETIDO/SELECIONADO/APROVADO/REPROVADO). Liberado novamente se DEVOLVIDO para correção.</small>
                </td>
            </tr>
            <tr>
                <td>Permitir excluir após submissão:</td>
                <td>
                    <input type="radio" name="trab_permitir_btn_excluir" value="0" <?= verifItemChecked($objEvConf->getTrabPermitirBtnExcluir(), array('', 0, false), false) ?> /> Não
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_permitir_btn_excluir" value="1" <?= verifItemChecked($objEvConf->getTrabPermitirBtnExcluir(), '1', false) ?> /> Sim, enquanto não estiver em avaliação
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_permitir_btn_excluir" value="2" <?= verifItemChecked($objEvConf->getTrabPermitirBtnExcluir(), '2', false) ?> /> Sim, mesmo em avaliação
                    <br><small style="color:#888;">Controla o botão Excluir na área do inscrito. O período de submissão encerrado bloqueia todos os botões.
                    <br><b>Não</b>: botão nunca aparece.
                    <br><b>Sim, enquanto não estiver em avaliação</b>: aparece até que alguém pegue ou receba o trabalho para avaliar.
                    <br><b>Sim, mesmo em avaliação</b>: aparece sempre, mas exige justificativa obrigatória quando em avaliação.</small>
                </td>
            </tr>

            <!-- ============================================================ -->
            <!-- PERSONALIZAÇÃO DOS TEXTOS DOS BOTÕES                          -->
            <!-- ============================================================ -->
            <tr>
                <td colspan="2">
                    <br><span style="font-size:15px;font-weight: bold;">Personalização dos Textos dos Botões</span>
                </td>
            </tr>
            <tr>
                <td>Texto do Botão Enviar Trabalho: </td>
                <td> <input type='text' name='txt_btn_enviar_trabalho' value='<?php echo $objEvConf->getTxtBtnEnviarTrabalho() ?>' maxlength='255' size='60' onBlur="trim(this);">
                    <br><small style="color:#888;">Botão para submeter um novo trabalho/documento</small>
                </td>
            </tr>
            <tr>
                <td>Texto do Botão Salvar Trabalho: </td>
                <td> <input type='text' name='txt_btn_salvar_trabalho' value='<?php echo $objEvConf->getTxtBtnSalvarTrabalho() ?>' maxlength='255' size='60' onBlur="trim(this);">
                    <br><small style="color:#888;">Botão no formulário de cadastro. Se "Disponibilizar para avaliação ao salvar" estiver ativo, o texto muda para "Salvar e Enviar para Avaliação"</small>
                </td>
            </tr>
            <tr>
                <td>Texto do Botão Editar Trabalho: </td>
                <td> <input type='text' name='txt_btn_editar_trabalho' value='<?php echo $objEvConf->getTxtBtnEditarTrabalho() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>

            <!-- ============================================================ -->
            <!-- ESPECIFICOS DE TRABALHO ACADEMICO                             -->
            <!-- Estavam soltos embaixo de "Personalizacao dos Textos dos      -->
            <!-- Botoes", sem titulo proprio.                                  -->
            <!-- ============================================================ -->
            <tr>
                <td colspan="2">
                    <br><span style="font-size:15px;font-weight: bold;">Trabalho Acadêmico</span>
                    <br><small style="color:#888;">Resumo, ensalamento, premiação, eixo temático e coautoria
                        &mdash; usados em congressos e eventos com submissão de artigos.</small>
                </td>
            </tr>
            <tr>
                <td colspan="2">Habilitar envio de resumo em arquivo sem identificação:
                    <input type="radio" name="trab_resumo_arquivo_sem_identificacao" value="1" <?= verifItemChecked($objEvConf->getTrabResumoArquivoSemIdentificacao(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_resumo_arquivo_sem_identificacao" value="0" <?= verifItemChecked($objEvConf->getTrabResumoArquivoSemIdentificacao(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">Aceitar resumo por formulario:
                    <input type="radio" name="trab_resumo_formulario" value="1" <?= verifItemChecked($objEvConf->getTrabResumoFormulario(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_resumo_formulario" value="0" <?= verifItemChecked($objEvConf->getTrabResumoFormulario(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Mensagem após encerrar submissão </td>
                <td> <input type='text' name='trab_submissao_fim_msg' value='<?php echo $objEvConf->getTrabSubmissaoFimMsg() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td>Mensagem inicio submissão </td>
                <td> <input type='text' name='trab_submissao_ini_msg' value='<?php echo $objEvConf->getTrabSubmissaoIniMsg() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>

            <tr>
                <td colspan="2">Número de Co-autores: <br>
                    <select name="trab_num_coautores">
                        <option></option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '', false) ?> value='Arquivo'>Nenhum</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '1', false) ?> value='1'>1</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '2', false) ?> value='2'>2</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '3', false) ?> value='3'>3</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '4', false) ?> value='4'>4</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '5', false) ?> value='5'>5</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '6', false) ?> value='6'>6</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '7', false) ?> value='7'>7</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '8', false) ?> value='8'>8</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '9', false) ?> value='9'>9</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '10', false) ?> value='10'>10</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '11', false) ?> value='11'>11</option>
                        <option <?= verifItemSelected($objEvConf->getTrabNumCoautores(), '12', false) ?> value='12'>12</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Possui Premiação:</td>
                <td>
                    <?php espelhoSimNao('trab_premiacao', $objEvConf->getTrabPremiacao()) ?>
                </td>
            </tr>
            <tr>
                <td>Titulo Premiação:</td>
                <td> <input type='text' name='trab_premiacao_titulo' value='<?php echo $objEvConf->getTrabPremiacaoTitulo() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td>Eixo Temático:</td>
                <td>
                    <input type="radio" name="trab_eixo_tematico" value="1" <?= verifItemChecked($objEvConf->getTrabEixoTematico(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_eixo_tematico" value="0" <?= verifItemChecked($objEvConf->getTrabEixoTematico(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Informar Resumo Referências</td>
                <td>
                    <input type="radio" name="trab_resumo_referencia" value="1" <?= verifItemChecked($objEvConf->getTrabResumoReferencia(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_resumo_referencia" value="0" <?= verifItemChecked($objEvConf->getTrabResumoReferencia(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Informar Palavras-chave</td>
                <td>
                    <input type="radio" name="trab_palavras_chave" value="1" <?= verifItemChecked($objEvConf->getTrabPalavrasChave(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_palavras_chave" value="0" <?= verifItemChecked($objEvConf->getTrabPalavrasChave(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Informar Instituição</td>
                <td>
                    <input type="radio" name="trab_instituicao" value="1" <?= verifItemChecked($objEvConf->getTrabInstituicao(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_instituicao" value="0" <?= verifItemChecked($objEvConf->getTrabInstituicao(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Informar Titulo</td>
                <td>
                    <input type="radio" name="trab_titulo" value="1" <?= verifItemChecked($objEvConf->getTrabTitulo(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_titulo" value="0" <?= verifItemChecked($objEvConf->getTrabTitulo(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Informar Formato de Texto</td>
                <td>
                    <input type="radio" name="trab_formato_texto" value="1" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_formato_texto" value="0" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Informar Forma de Apresentação</td>
                <td>
                    <input type="radio" name="trab_formato_apresentacao" value="1" <?= verifItemChecked($objEvConf->getTrabFormatoApresentacao(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_formato_apresentacao" value="0" <?= verifItemChecked($objEvConf->getTrabFormatoApresentacao(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>

            <!-- Ensalamento fecha a secao: so faz sentido depois que o trabalho
                 foi submetido e avaliado (evento presencial). -->
            <tr>
                <td>Divulgar Ensalamento</td>
                <td>
                    <input type="radio" name="trab_ensalamento_ini" value="1" <?= verifItemChecked($objEvConf->getTrabEnsalamentoIni(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_ensalamento_ini" value="0" <?= verifItemChecked($objEvConf->getTrabEnsalamentoIni(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Data divulgação ensalamento </td>
                <td> <input type='datetime-local' name='trab_ensalamento_dat_div' value='<?php echo $objEvConf->getTrabEnsalamentoDatDiv() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>

            <!-- ============================================================ -->
            <!-- DOWNLOAD EM LOTE - movido para o fim: e acao da area          -->
            <!-- administrativa, estava no meio dos botoes do inscrito.        -->
            <!-- ============================================================ -->
            <tr>
                <td colspan="2">
                    <br><span style="font-size:15px;font-weight: bold;">Download em Lote (Área Administrativa)</span>
                </td>
            </tr>
            <tr>
                <td>Permitir download ZIP dos trabalhos:</td>
                <td>
                    <input type="radio" name="trab_download_zip" value="1" <?= verifItemChecked($objEvConf->getTrabDownloadZip(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_download_zip" value="0" <?= verifItemChecked($objEvConf->getTrabDownloadZip(), array('', 0, false), false) ?> /> Não
                    <br><small style="color:#888;">Exibe o botão "Exportar ZIP" na listagem de trabalhos da área administrativa, permitindo baixar todos os arquivos enviados em um único ZIP.</small>
                </td>
            </tr>
        </table>
    </div>
    <div id="trabAvaliacao" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td><span style="font-size:15px;font-weight: bold;">Avaliação Via Sistema:</span> </td>
                <td>
                    <?php espelhoSimNao('trab_avaliacao', $objEvConf->getTrabAvaliacao()) ?>
                    <br><small style="color:#888;">Habilita o módulo de avaliação de trabalhos para este evento</small>
                </td>
            </tr>
            <tr>
                <td>Avaliação Por: </td>
                <td>
                    <input type="radio" name="trab_avaliacao_por" value="CANDIDATO" <?= verifItemChecked($objEvConf->getTrabAvaliacaoPor(), 'CANDIDATO', false) ?> /> Candidato
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_por" value="ARQUIVO" <?= verifItemChecked($objEvConf->getTrabAvaliacaoPor(), 'ARQUIVO', false) ?> /> Arquivo
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_por" value="" <?= verifItemChecked($objEvConf->getTrabAvaliacaoPor(), '', false) ?> /> Nenhum
                </td>
            </tr>

            <!-- ============================================================ -->
            <!-- FLUXO DE ENVIO PARA AVALIAÇÃO                                -->
            <!-- ============================================================ -->
            <tr>
                <td colspan="2">
                    <br><span style="font-size:15px;font-weight: bold;">Fluxo de Envio para Avaliação</span>
                    <br><small style="color:#666;">Define como o trabalho chega até o avaliador e o que acontece com os botões do inscrito.</small>
                </td>
            </tr>
            <tr>
                <td>Disponibilizar para avaliação ao salvar:</td>
                <td>
                    <input type="radio" name="trab_avaliacao_ao_salvar" value="1" <?= verifItemChecked($objEvConf->getTrabAvaliacaoAoSalvar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_ao_salvar" value="0" <?= verifItemChecked($objEvConf->getTrabAvaliacaoAoSalvar(), array('', 0, false), false) ?> /> Não
                    <br><small style="color:#888;">
                        <b>Sim</b>: Ao salvar, o trabalho fica disponível para o coordenador científico distribuir ou para o avaliador pegar pela área temática.
                        O inscrito pode continuar editando até que alguém pegue o trabalho para avaliar. O botão "Enviar para Avaliação" <b>não aparece</b> para o inscrito.
                        <br><b>Não</b>: O inscrito precisa clicar "Enviar para Avaliação" manualmente. Ao enviar, a edição e exclusão são bloqueadas imediatamente.
                    </small>
                </td>
            </tr>

            <!-- ============================================================ -->
            <!-- CRITÉRIOS DE AVALIAÇÃO                                        -->
            <!-- ============================================================ -->
            <tr>
                <td colspan="2">
                    <br><span style="font-size:15px;font-weight: bold;">Critérios de Avaliação</span>
                </td>
            </tr>
            <tr>
                <td>Avaliação com critérios complementares: </td>
                <td>
                    <input type="radio" name="trab_avaliacao_complementar" value="1" <?= verifItemChecked($objEvConf->getTrabAvaliacaoComplementar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_complementar" value="0" <?= verifItemChecked($objEvConf->getTrabAvaliacaoComplementar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Quantidade de critérios complementares: </td>
                <td> <input type='text' name='trab_avaliacao_criterio_qtd' value='<?php echo $objEvConf->getTrabAvaliacaoCriterioQtd() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>

            <!-- ============================================================ -->
            <!-- TRAMITAÇÃO E HISTÓRICO                                        -->
            <!-- ============================================================ -->
            <tr>
                <td colspan="2">
                    <br><span style="font-size:15px;font-weight: bold;">Tramitação e Histórico</span>
                </td>
            </tr>
            <tr>
                <td>Mostrar histórico da avaliação: </td>
                <td>
                    <input type="radio" name="trab_avaliacao_mostrar_historico" value="1" <?= verifItemChecked($objEvConf->getTrabAvaliacaoMostrarHistorico(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_mostrar_historico" value="0" <?= verifItemChecked($objEvConf->getTrabAvaliacaoMostrarHistorico(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Permitir que o avaliador devolva para correção</td>
                <td>
                    <input type="radio" name="trab_avaliacao_permitir_retorno" value="1" <?= verifItemChecked($objEvConf->getTrabAvaliacaoPermitirRetorno(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_permitir_retorno" value="0" <?= verifItemChecked($objEvConf->getTrabAvaliacaoPermitirRetorno(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Permitir aprovar/reprovar direto: </td>
                <td>
                    <input type="radio" name="trab_aprovar_direto" value="1" <?= verifItemChecked($objEvConf->getTrabAprovarDireto(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_aprovar_direto" value="0" <?= verifItemChecked($objEvConf->getTrabAprovarDireto(), array('', 0, false), false) ?> /> Não
                    <br><small style="color:#888;">Mostra os botões Aprovar e Reprovar na listagem de trabalhos, para a comissão decidir sem passar pelo avaliador</small>
                </td>
            </tr>
            <tr>
                <td>Qtd tramitação </td>
                <td> <input type='text' name='trab_avaliacao_qtd_retorno' value='<?php echo $objEvConf->getTrabAvaliacaoQtdRetorno() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td>Data inicio tramitação </td>
                <td> <input type='datetime-local' name='trab_tramitacao_dat_fim' value='<?php echo $objEvConf->getTrabTramitacaoDatFim() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td>Data fim avaliação </td>
                <td> <input type='datetime-local' name='trab_avaliacao_dat_fim' value='<?php echo $objEvConf->getTrabAvaliacaoDatFim() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <!-- ============================================================ -->
            <!-- DIVULGAÇÃO DE RESULTADOS                                      -->
            <!-- ============================================================ -->
            <tr>
                <td colspan="2">
                    <br><span style="font-size:15px;font-weight: bold;">Divulgação de Resultados</span>
                    <br><small style="color:#666;">Controla o que o inscrito pode ver sobre a avaliação do seu trabalho.</small>
                </td>
            </tr>
            <tr>
                <td>Divulgar Avaliação:</td>
                <td>
                    <input type="radio" name="trab_avaliacao_divulgar" value="1" <?= verifItemChecked($objEvConf->getTrabAvaliacaoDivulgar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_divulgar" value="0" <?= verifItemChecked($objEvConf->getTrabAvaliacaoDivulgar(), array('', 0, false), false) ?> /> Não
                    <br><small style="color:#888;">Exibe o resultado (Aprovado/Reprovado) na área do inscrito</small>
                </td>
            </tr>
            <tr>
                <td>Divulgar Parecer da Avaliação:</td>
                <td>
                    <input type="radio" name="trab_avaliacao_parecer_divulgar" value="1" <?= verifItemChecked($objEvConf->getTrabAvaliacaoParecerDivulgar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_parecer_divulgar" value="0" <?= verifItemChecked($objEvConf->getTrabAvaliacaoParecerDivulgar(), array('', 0, false), false) ?> /> Não
                    <br><small style="color:#888;">Exibe o texto do parecer do avaliador na área do inscrito</small>
                </td>
            </tr>

            <!-- ============================================================ -->
            <!-- RECURSO                                                       -->
            <!-- ============================================================ -->
            <tr>
                <td colspan="2">
                    <br><span style="font-size:15px;font-weight: bold;">Recurso</span>
                </td>
            </tr>
            <tr>
                <td>Permitir recurso: </td>
                <td>
                    <input type="radio" name="trab_avaliacao_permitir_recurso" value="1" <?= verifItemChecked($objEvConf->getTrabAvaliacaoPermitirRecurso(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_permitir_recurso" value="0" <?= verifItemChecked($objEvConf->getTrabAvaliacaoPermitirRecurso(), array('', 0, false), false) ?> /> Não
                    <br><small style="color:#888;">Permite ao inscrito solicitar recurso após avaliação</small>
                </td>
            </tr>
            <!-- <tr>
                <td>Permitir recurso de habilitados: </td>
                <td>
                    <input type="radio" name="trab_avaliacao_permitir_recurso_habilitados" value="1" <?= verifItemChecked($objEvConf->getTrabAvaliacaoPermitirRecursoHabilitados(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_avaliacao_permitir_recurso_habilitados" value="0" <?= verifItemChecked($objEvConf->getTrabAvaliacaoPermitirRecursoHabilitados(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>                       -->
            <tr>
                <td colspan="2">
                    <span style="font-size:15px;font-weight: bold;">Período de Solicitação Recurso</span><br>
                    Inicio <input type='datetime-local' name='trab_recurso_dat_ini' value='<?php echo $objEvConf->getTrabRecursoDatIni() ?>' onBlur="trim(this);">
                    Fim <input type='datetime-local' name='trab_recurso_dat_fim' value='<?php echo $objEvConf->getTrabRecursoDatFim() ?>' onBlur="trim(this);">
                </td>
            </tr>
            <tr>
                <td>Personalizar Texto do Botão Solicitar Recurso: </td>
                <td> <input type='text' name='txt_btn_solicitar_recurso' value='<?php echo $objEvConf->getTxtBtnSolicitarRecurso() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td>Divulgar Parecer da Avaliação do Recurso:</td>
                <td>
                    <input type="radio" name="trab_recurso_parecer_divulgar" value="1" <?= verifItemChecked($objEvConf->getTrabRecursoParecerDivulgar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_recurso_parecer_divulgar" value="0" <?= verifItemChecked($objEvConf->getTrabRecursoParecerDivulgar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>            
            <tr>
                <td>Data divulgação aprovados </td>
                <td> <input type='datetime-local' name='trab_aprovado_dat_div' value='<?php echo $objEvConf->getTrabAprovadoDatDiv() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td>Data divulgação reprovados </td>
                <td> <input type='datetime-local' name='trab_reprovado_dat_div' value='<?php echo $objEvConf->getTrabReprovadoDatDiv() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
        </table>
    </div>
    <div id="trabAnexosExtras" class="tab-content">
        <?php require_once('evento-configurar-campos-trabalho-anexos.php'); ?>
        
    </div>
    <div id="trabMensagem-1" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td colspan="2"><br><span style="font-size:15px;font-weight: bold;">Configurar Mensagens:</span></td>
            </tr>
            <tr>
                <td>Mostrar Complemento de Formulário</td>
                <td>
                    <input type="radio" name="trab_complemento_form" value="1" <?= verifItemChecked($objEvConf->getTrabComplementoForm(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="trab_complemento_form" value="0" <?= verifItemChecked($objEvConf->getTrabComplementoForm(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="trab_complemento_form_text">Complemento de Fomulário:</label>
                    <div id="tabsMsgComplementoFormTexto">
                        <ul>
                            <li class="txtAba"><a href="#trabMsgTrabalhoTextoFT1">Visualizar</a></li>
                            <li class="txtAba"><a href="#trabMsgTrabalhoTextoFT2">Editar</a></li>
                        </ul>
                        <div id="trabMsgTrabalhoTextoFT2" class="tab-content">
                        <textarea class=editor_padrao rows=5 cols=70 name='trab_complemento_form_text' onBlur='trim(this);' class='textob form_control'><?php echo $objEvConf->getTrabComplementoFormText() ?></textarea>
                        </div>
                        <div id="trabMsgTrabalhoTextoFT1" class="tab-content">
                            <?php
                            //echo $objEvConf->getTrabComplementoFormText()? $objEvConf->getTrabComplementoFormText() : "Nenhum aviso cadastrado.";
                            ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div id="trabMensagem-2" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td>Ocultar Mensagem Padrão da Área do Trabalho: </td>
                <td>
                    <input type="radio" name="msg_trabalho_padrao_ocultar" value="1" <?= verifItemChecked($objEvConf->getMsgTrabalhoPadraoOcultar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="msg_trabalho_padrao_ocultar" value="0" <?= verifItemChecked($objEvConf->getMsgTrabalhoPadraoOcultar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>Ativar Mensagem abaixo da Área do Trabalho: </td>
                <td>
                    <input type="radio" name="msg_trabalho_ativar" value="1" <?= verifItemChecked($objEvConf->getMsgTrabalhoAtivar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="msg_trabalho_ativar" value="0" <?= verifItemChecked($objEvConf->getMsgTrabalhoAtivar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td>E Ocultar Mensagem abaixo da Área do Trabalho após Anexar: </td>
                <td>
                    <input type="radio" name="msg_trabalho_apos_anexar_ocultar" value="1" <?= verifItemChecked($objEvConf->getMsgTrabalhoAposAnexarOcultar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="msg_trabalho_apos_anexar_ocultar" value="0" <?= verifItemChecked($objEvConf->getMsgTrabalhoAposAnexarOcultar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="msg_trabalho_texto">Mensagem na Área do Trabalho:</label>
                    <div id="tabsMsgTrabalhoTexto">
                        <ul>
                            <li class="txtAba"><a href="#trabMsgTrabalhoTexto1">Visualizar</a></li>
                            <li class="txtAba"><a href="#trabMsgTrabalhoTexto2">Editar</a></li>
                        </ul>
                        <div id="trabMsgTrabalhoTexto2" class="tab-content">
                            <textarea class='editor_padrao resizable' rows='5' cols='70' name='msg_trabalho_texto' onBlur='trim(this);' class='textob form_control'><?php echo $objEvConf->getMsgTrabalhoTexto() ?></textarea>
                        </div>
                        <div id="trabMsgTrabalhoTexto1" class="tab-content">
                            <?php
                            echo $objEvConf->getMsgTrabalhoTexto()? $objEvConf->getMsgTrabalhoTexto() : "Nenhum aviso cadastrado.";
                            ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php
                    /* Modelo do resumo: arquivo que o inscrito baixa na area do trabalho,
                     * logo abaixo desta mensagem. O upload reusa o modulo de midias
                     * (local de midia MODELO-DO-RESUMO, tabela tab_arquivo). */
                    $objModeloResumo = GerenciarMidiaUploadAC::localizarModeloResumo();
                    ?>
                    <label for="modeloResumoArquivo">Modelo do resumo (arquivo para download):</label>
                    <div style="padding:8px 0;">
                        <span id="modeloResumoAtual" style="font-size:13px;">
                            <?php if ($objModeloResumo) { ?>
                                <i class="fa fa-paperclip"></i> <?php echo htmlspecialchars($objModeloResumo->getArquivoUpload()) ?>
                            <?php } else { ?>
                                <span style="color:#888;">Nenhum modelo enviado.</span>
                            <?php } ?>
                        </span>
                        &nbsp;&nbsp;
                        <button type="button" class="btn btn-sm" onclick="abrirModalModeloResumo();">
                            <i class="fa fa-upload"></i> Enviar modelo
                        </button>
                        <br><small style="color:#888;">Aparece como link para o inscrito abaixo da mensagem acima, na area de envio do trabalho. Extensoes: doc, docx, pdf, rtf.</small>
                    </div>
                    <input type="hidden" id="modeloResumoCodArquivo" value="<?php echo $objModeloResumo ? $objModeloResumo->getCodArquivo() : '' ?>">
                </td>
            </tr>
        </table>
    </div>
    <div id="trabMensagem-3" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td>Ativar Mensagem para Recurso: </td>
                <td>
                    <input type="radio" name="msg_trabalho_recurso_ativar" value="1" <?= verifItemChecked($objEvConf->getMsgTrabalhoRecursoAtivar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="msg_trabalho_recurso_ativar" value="0" <?= verifItemChecked($objEvConf->getMsgTrabalhoRecursoAtivar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="msg_trabalho_recurso_texto">Mensagem na Recurso:</label>
                    <div id="tabsMsgTrabalhoRecursoTexto">
                        <ul>
                            <li class="txtAba"><a href="#trabMsgTrabalhoRecursoTexto1">Visualizar</a></li>
                            <li class="txtAba"><a href="#trabMsgTrabalhoRecursoTexto2">Editar</a></li>
                        </ul>
                        <div id="trabMsgTrabalhoRecursoTexto2" class="tab-content">
                        <textarea class='editor_padrao resizable' rows='5' cols='70' name='msg_trabalho_recurso_texto' onBlur='trim(this);' class='textob form_control'><?php echo $objEvConf->getMsgTrabalhoRecursoTexto() ?></textarea>
                        </div>
                        <div id="trabMsgTrabalhoRecursoTexto1" class="tab-content">
                            <?php
                            echo $objEvConf->getMsgTrabalhoRecursoTexto()? $objEvConf->getMsgTrabalhoRecursoTexto() : "Nenhum aviso cadastrado.";
                            ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div id="trabMensagem-4" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td>Ativar Mensagem para após anexar: </td>
                <td>
                    <input type="radio" name="msg_trabalho_apos_anexar_ativar" value="1" <?= verifItemChecked($objEvConf->getMsgTrabalhoAposAnexarAtivar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="msg_trabalho_apos_anexar_ativar" value="0" <?= verifItemChecked($objEvConf->getMsgTrabalhoAposAnexarAtivar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="msg_trabalho_apos_anexar_texto">Mensagem na Após Anexar:</label>
                    <div id="tabsMsgTrabalhoAposAnexarTexto">
                        <ul>
                            <li class="txtAba"><a href="#trabMsgTrabalhoAposAnexarTexto1">Visualizar</a></li>
                            <li class="txtAba"><a href="#trabMsgTrabalhoAposAnexarTexto2">Editar</a></li>
                        </ul>
                        <div id="trabMsgTrabalhoAposAnexarTexto2" class="tab-content">
                        <textarea class='editor_padrao resizable' rows='5' cols='70' name='msg_trabalho_apos_anexar_texto' onBlur='trim(this);' class='textob form_control'><?php echo $objEvConf->getMsgTrabalhoAposAnexarTexto() ?></textarea>
                        </div>
                        <div id="trabMsgTrabalhoAposAnexarTexto1" class="tab-content">
                            <?php
                            echo $objEvConf->getMsgTrabalhoAposAnexarTexto()? $objEvConf->getMsgTrabalhoAposAnexarTexto() : "Nenhum aviso cadastrado.";
                            ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<!--
  Perfis que podem enviar documento.
  Fica FORA do <form> da configuracao de proposito: a aba Trabalho divide o mesmo
  form com as outras 9 abas, entao um campo com "name" aqui entraria no POST do
  "Alterar" como se fosse configuracao do evento. A gravacao e feita por AJAX no
  GerenciarPerfil::toggleCampo, que ja existe e ja limita o UPDATE a
  cod_evento + cod_perfil.
-->
<!-- overlay proprio: esta tela carrega jQuery, mas nao o JS do Bootstrap
     ($(...).modal nao existe aqui), entao nada de modal do Bootstrap -->
<div id="modalPerfisEnvio"
     style="display:none; position:fixed; inset:0; z-index:20000;
            background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:10px; width:380px; max-width:92%;
                box-shadow:0 6px 26px rgba(0,0,0,.28); overflow:hidden;">
        <div class="modalCabecalho" style="padding:12px 18px; display:flex;
                    justify-content:space-between; align-items:center;">
            <h4 class="modal-title" style="margin:0; font-size:15px;">
                <i class="fa fa-users"></i> Perfis que podem enviar
            </h4>
            <span id="btnFecharPerfisEnvio" style="cursor:pointer; font-size:22px;
                  line-height:1; opacity:.7;">&times;</span>
        </div>
        <div style="padding:14px 18px;">
                <p style="font-size:12px; color:#666;">
                    Marque quem ver&aacute; a &aacute;rea de envio de documento neste projeto.
                </p>
                <?php if ($listaPerfilEnvio) { ?>
                    <?php foreach ($listaPerfilEnvio as $p) { ?>
                        <div class="itemPerfilEnvio">
                            <label>
                                <input type="checkbox" class="chkPerfilEnvio"
                                       data-cod_perfil="<?php echo $p->getCodPerfil() ?>"
                                       data-titulo="<?php echo htmlspecialchars($p->getTitulo()) ?>"
                                    <?php echo ($p->getVerificarEnvioTrabalho() == 1) ? 'checked' : '' ?>>
                                <span><?php echo htmlspecialchars($p->getTitulo()) ?></span>
                            </label>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="text-muted">Este projeto ainda n&atilde;o tem grupos de participantes.</p>
                <?php } ?>
                <div id="perfisEnvioMsg" style="margin-top:10px; font-size:12px;"></div>
        </div>
        <div class="modalRodape" style="padding:10px 18px; text-align:right;">
            <button type="button" id="btnFecharPerfisEnvio2"
                    class="btn btn-default modalBtnCancelar">Fechar</button>
        </div>
    </div>
</div>

<style>
    /* rotulos dos perfis: mesma leitura rapida dos status usados no resto do admin */
    .rotuloPerfilEnvio {
        display: inline-block;
        padding: 3px 10px;
        margin-right: 4px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        background: #e8f5ee;
        color: #2d6a4f;
        border: 1px solid #cfe8da;
    }
    .rotuloPerfilEnvio.vazio {
        background: #fdecea;
        color: #c0392b;
        border-color: #f5c6c0;
        font-weight: normal;
    }
    /* linha do bloco de perfis respirando um pouco mais que as demais */
    #perfisEnvioResumo { line-height: 2; }

    #modalPerfisEnvio .itemPerfilEnvio {
        padding: 8px 4px;
        border-bottom: 1px solid #f0f0f0;
    }
    #modalPerfisEnvio .itemPerfilEnvio:last-child { border-bottom: none; }
    #modalPerfisEnvio .itemPerfilEnvio label {
        font-weight: normal;
        cursor: pointer;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* -------------------------------------------------------------------
       Leitura da aba Trabalho. Tudo escopado em #tabsTrabalho para nao
       vazar para as outras abas nem para outras telas de configuracao.
       A estrutura de <table>/<tr>/<td> permanece como esta - aqui so muda
       contraste, espacamento e largura de campo.
       ------------------------------------------------------------------- */

    /* respiro e zebra: hoje as linhas se confundem uma na outra */
    #tabsTrabalho table.dataTable > tbody > tr > td { padding: 9px 12px; vertical-align: middle; }
    #tabsTrabalho table.dataTable > tbody > tr:nth-child(odd) > td { background-color: #fbfbfb; }
    #tabsTrabalho table.dataTable > tbody > tr:hover > td { background-color: #f2f7f4; }

    /* textos de ajuda: eram cinza-claro sobre fundo cinza */
    #tabsTrabalho small {
        color: #5b5b5b !important;
        font-size: 11.5px;
        line-height: 1.55;
        display: inline-block;
        margin-top: 4px;
    }

    /* Titulos de secao ganham uma regua, separando os blocos.
       O seletor exige td[colspan] de proposito: titulo de secao ocupa a linha
       inteira, enquanto rotulos como "Avaliacao Via Sistema:" usam a mesma
       marcacao (font-size:15px bold) numa td comum da primeira coluna - esses
       nao podem virar cabecalho. */
    #tabsTrabalho td[colspan] span[style*="font-size:15px"] {
        display: block;
        margin: 10px 0 4px;
        padding-bottom: 5px;
        border-bottom: 2px solid #2d6a4f;
        color: #2d6a4f;
    }

    /* campo de numero nao precisa de 450px */
    #tabsTrabalho input[name="trab_submissao_qtd"],
    #tabsTrabalho input[name="trab_submissao_qtd_perfil"],
    #tabsTrabalho input[name="trab_num_coautores"],
    #tabsTrabalho input[name="trab_avaliacao_criterio_qtd"],
    #tabsTrabalho input[name="trab_avaliacao_qtd_retorno"] {
        width: 90px;
    }

    /* campos com a mesma carinha: o select nativo destoava do resto */
    #tabsTrabalho select,
    #tabsTrabalho input[type="text"],
    #tabsTrabalho input[type="datetime-local"] {
        height: 32px;
        padding: 3px 8px;
        border: 1px solid #ccc;
        border-radius: 6px;
        background-color: #fff;
        font-size: 13px;
        color: #333;
        box-sizing: border-box;
    }
    #tabsTrabalho select:focus,
    #tabsTrabalho input[type="text"]:focus,
    #tabsTrabalho input[type="datetime-local"]:focus {
        border-color: #2d6a4f;
        outline: none;
        box-shadow: 0 0 0 2px rgba(45,106,79,.12);
    }
    #tabsTrabalho #trab_anexo_tipo { min-width: 300px; font-weight: 600; }
    #tabsTrabalho optgroup { font-style: normal; color: #777; font-weight: 600; }
    #tabsTrabalho optgroup option { font-weight: normal; color: #333; padding-left: 6px; }

    /* rotulo da primeira coluna sem quebrar no meio da frase */
    #tabsTrabalho table.dataTable > tbody > tr > td:first-child { width: 32%; }

    /* extensoes permitidas: marcar em vez de digitar */
    #blocoExtensoes .chipExtensao {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        margin: 0 5px 5px 0;
        border: 1px solid #d5d5d5;
        border-radius: 14px;
        background: #fff;
        font-weight: normal;
        font-size: 12px;
        cursor: pointer;
        user-select: none;
    }
    #blocoExtensoes .chipExtensao:hover { border-color: #2d6a4f; }
    #blocoExtensoes .chipExtensao input { margin: 0; }

    /* opcoes Sim/Nao lado a lado sem grudar no rotulo */
    #tabsTrabalho input[type="radio"] { margin-right: 3px; }
    #tabsTrabalho input[type="radio"] + label,
    #tabsTrabalho label { margin-right: 12px; font-weight: normal; }
</style>

<script>
    $(function () {
        function abrirPerfisEnvio() {
            $('#perfisEnvioMsg').html('');
            $('#modalPerfisEnvio').css('display', 'flex');
        }
        function fecharPerfisEnvio() {
            $('#modalPerfisEnvio').hide();
        }

        // --- Extensoes permitidas -------------------------------------------
        // O <input name="trab_arquivo_extensao"> virou hidden: o admin marca os
        // formatos e o valor e remontado aqui. So reescreve quando ha interacao,
        // para nao alterar o texto ja gravado de quem apenas abriu a tela.
        function montarExtensoes() {
            var lista = [];
            $('.chkExtensao:checked').each(function () {
                lista.push($(this).val());
            });
            $('#trab_arquivo_extensao').val(lista.join(', '));
        }

        $('.chkExtensao').on('change', montarExtensoes);

        $('#btnPerfisEnvio').on('click', abrirPerfisEnvio);
        $('#btnFecharPerfisEnvio, #btnFecharPerfisEnvio2').on('click', fecharPerfisEnvio);
        $('#modalPerfisEnvio').on('click', function (e) {
            if (e.target === this) { fecharPerfisEnvio(); }   // clique fora fecha
        });

        function atualizarResumoPerfisEnvio() {
            var nomes = [];
            $('.chkPerfilEnvio:checked').each(function () {
                nomes.push($(this).data('titulo'));
            });
            if (nomes.length) {
                var html = '';
                for (var i = 0; i < nomes.length; i++) {
                    html += '<span class="rotuloPerfilEnvio">' + nomes[i] + '</span>';
                }
                $('#perfisEnvioResumo').html(html);
            } else {
                $('#perfisEnvioResumo').html('<span class="rotuloPerfilEnvio vazio">'
                    + 'Nenhum &mdash; ninguém verá a área de envio</span>');
            }
        }

        $('.chkPerfilEnvio').on('change', function () {
            var chk = $(this);
            var desejado = chk.is(':checked');
            chk.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '<?php echo WB_URL_COMMAND ?>',
                dataType: 'json',
                data: {
                    'codPerfil': chk.data('cod_perfil'),
                    'campo': 'verificar_envio_trabalho',
                    'c': 'GerenciarPerfil',
                    'm': 'toggleCampo',
                    '_submit_check': '1'
                },
                success: function (retorno) {
                    if (retorno.statusMessage == '1') {
                        // toggleCampo inverte o valor; confere se ficou como o usuario pediu
                        var ficou = (retorno.novoValor == 1);
                        chk.prop('checked', ficou);
                        if (ficou !== desejado) {
                            $('#perfisEnvioMsg').html('<span style="color:#c0392b;">O valor estava'
                                + ' diferente do exibido. A tela foi corrigida.</span>');
                        } else {
                            $('#perfisEnvioMsg').html('<span style="color:#2d6a4f;">Salvo.</span>');
                        }
                    } else {
                        chk.prop('checked', !desejado);
                        $('#perfisEnvioMsg').html('<span style="color:#c0392b;">'
                            + (retorno.message || 'Não foi possível salvar.') + '</span>');
                    }
                    atualizarResumoPerfisEnvio();
                },
                error: function () {
                    chk.prop('checked', !desejado);
                    $('#perfisEnvioMsg').html('<span style="color:#c0392b;">Falha de comunicação.</span>');
                },
                complete: function () {
                    chk.prop('disabled', false);
                }
            });
        });
    });
</script>

<!-- Modal: envio do modelo do resumo (reusa GerenciarMidiaUpload).
     Overlay proprio em jQuery: a tela de configuracao NAO carrega o bootstrap.js,
     entao $().modal() nao existe aqui. -->
<div id="modalModeloResumo" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:9999;">
    <div style="background:#fff;max-width:520px;margin:120px auto;border-radius:6px;box-shadow:0 4px 18px rgba(0,0,0,.3);">
        <div style="padding:12px 18px;border-bottom:1px solid #eee;font-weight:bold;">
            Modelo do resumo
            <span style="float:right;cursor:pointer;" onclick="fecharModalModeloResumo();">&times;</span>
        </div>
        <div style="padding:18px;">
            <label for="modeloResumoArquivo">Arquivo (doc, docx, pdf, rtf):</label>
            <input type="file" id="modeloResumoArquivo" name="modeloResumoArquivo" class="form-control" accept=".doc,.docx,.pdf,.rtf">
            <div id="modeloResumoMsg" style="font-size:13px;margin-top:10px;"></div>
        </div>
        <div style="padding:12px 18px;border-top:1px solid #eee;text-align:right;">
            <button type="button" class="btn btn-sm" onclick="fecharModalModeloResumo();">Fechar</button>
            <button type="button" class="btn btn-sm btn-primary" id="btnEnviarModeloResumo">Enviar</button>
        </div>
    </div>
</div>
<script type="text/javascript">
function abrirModalModeloResumo() {
    $('#modeloResumoMsg').html('');
    $('#modalModeloResumo').show();
}
function fecharModalModeloResumo() {
    $('#modalModeloResumo').hide();
}
$(function () {
    /* O bloco desta aba vive dentro de uma div de abas com display:none — o modal
       precisa sair de la, senao nasce invisivel junto com a aba. */
    $('#modalModeloResumo').appendTo(document.body);

    $('#btnEnviarModeloResumo').on('click', function () {
        var input = document.getElementById('modeloResumoArquivo');
        if (!input.files.length) {
            $('#modeloResumoMsg').html('<span style="color:#a94442;">Selecione um arquivo.</span>');
            return;
        }
        var codArquivo = $('#modeloResumoCodArquivo').val();
        var formData = new FormData();
        formData.append('c', 'GerenciarMidiaUpload');
        formData.append('m', 'salvarMidiaUpload');
        formData.append('_submit_check', '1');
        formData.append('localMidia', 'MODELO-DO-RESUMO');
        formData.append('codArquivo', codArquivo);
        /* novoCodArquivo=0 zera o codArquivo no controller (registro novo);
           com codigo em maos mandamos 1 para ATUALIZAR o registro existente. */
        formData.append('novoCodArquivo', codArquivo ? '1' : '0');
        formData.append('codPerfil', '');
        formData.append('codOficina', '');
        formData.append('arquivo', input.files[0]);

        $('#modeloResumoMsg').html('Enviando...');
        $.ajax({
            type: 'POST',
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            cache: false,
            success: function (data) {
                if (data.statusMessage) {
                    $('#modeloResumoCodArquivo').val(data.codArquivo);
                    $('#modeloResumoAtual').html('<i class="fa fa-paperclip"></i> ' + input.files[0].name);
                    $('#modeloResumoMsg').html('<span style="color:#3c763d;">Modelo enviado.</span>');
                    setTimeout(function () { fecharModalModeloResumo(); }, 900);
                } else {
                    $('#modeloResumoMsg').html('<span style="color:#a94442;">' + (data.message || 'Nao foi possivel enviar.') + '</span>');
                }
            },
            error: function () {
                $('#modeloResumoMsg').html('<span style="color:#a94442;">Erro ao enviar o arquivo.</span>');
            }
        });
    });
});
</script>
