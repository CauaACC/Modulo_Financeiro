<?php
/**
 * default.php — VERSAO EM AVALIACAO da tela "Menu Geral" (view/admin/default.php)
 *
 * PRINCIPIO: nenhum link foi removido.
 * O mesmo destino aparece com nomes diferentes DE PROPOSITO — cada funcao
 * (secretaria, comissao cientifica, financeiro...) procura a mesma tela pelo
 * nome do seu proprio vocabulario. Como a permissao ja esconde os blocos que
 * nao sao do usuario, na pratica cada pessoa ve so o nome que faz sentido pra ela.
 * Quem ve os apelidos repetidos e o admin geral — e para ele a tela agora marca,
 * automaticamente, quando dois itens levam ao mesmo lugar.
 *
 * O que muda em relacao ao default.php:
 *   1. Busca/filtro no topo, com sinonimos (JS puro)
 *   2. Faixa de atalhos rapidos
 *   3. Cards em grade no lugar da pilha vertical
 *   4. Legenda de uma linha em cada link
 *   5. Marca "mesma tela de X" calculada sozinha, comparando as URLs
 *
 * As REGRAS DE PERMISSAO sao identicas as do default.php.
 *
 * Para comparar:  index.php?content=view/admin/default.php
 */
require_once(dirname(__FILE__) . "/../../include/config.php");
// Validar sessao admin (session_token) — invalida se senha foi trocada em outra sessao
FWA_ValidarSessaoAdmin();
// Verificar expiracao do "logar como" (30 minutos)
if ($_SESSION['_logadoComo'] == 'administrativo' && !empty($_SESSION['_logadoComoExpira'])) {
    if (time() > $_SESSION['_logadoComoExpira']) {
        $_SESSION['_login']         = $_SESSION['userLogado']['_login'];
        $_SESSION['_cod_usuario']   = $_SESSION['userLogado']['_cod_usuario'];
        $_SESSION['_cod_convidado'] = $_SESSION['userLogado']['_cod_convidado'];
        $_SESSION['_nome_usuario']  = $_SESSION['userLogado']['_nome_usuario'];
        $_SESSION['_logadoComo']    = '';
        unset($_SESSION['_logadoComoExpira']);
        $_SESSION['MSG'] = 'Sessao "logar como" expirou. Voce voltou ao seu usuario original.';
    }
}

$COD_SIS_MASTER = 0;
if ($_SESSION['_cod_usuario'] == 668) {
    $COD_SIS_MASTER = 1;
}

/* Constantes de sistema — mesmas do default.php */
@$COD_SIS_ADM_SISTEMA                                 = 1;
@$COD_SIS_CONTROLE_ATIVIDADES                         = 2;
@$COD_SIS_AVALIACAO_TRABALHO                          = 3;
@$COD_SIS_ADM_AVALIACAO_TRABALHOS                     = 4;
@$COD_SIS_AVALIACAO_TRABALHO_PREMIADOS                = 5;
@$COD_SIS_ADM_AVALIACAO_TRABALHOS_DEFINIR_PREMIADOS   = 20;
@$COD_SIS_ADM_CONFIGURACAO                            = 6;
@$COD_SIS_RECEPCAO_PARTICIPANTES                      = 11;
@$COD_SIS_LISTAR_INSCRICOES                           = 12;
$COD_SIS_ADM_VOUCHERS                                 = 14;

if (!$_SESSION['_logado']) {
    require_once(dirname(__FILE__) . "/../../view/admin/login.php");
    return;
}

/* Auto-redirect: admin logou sem evento selecionado (identico ao default.php) */
if (($COD_EVENTO == '' || $COD_EVENTO == '0' || $COD_EVENTO == 'COD_EVENTO') && $_SESSION['_administrativo'] == 1) {
    $objEvRedir = new Evento();
    $objEvRedir->setCodUsuario($_SESSION['_cod_usuario']);
    try {
        $listaEventos = $objEvRedir->carregarListaEventosByIdLogin($debug);
        $totalEventos = is_array($listaEventos) ? count($listaEventos) : 0;

        if ($totalEventos == 1) {
            $evUnico    = $listaEventos[0];
            $urlPrefixo = $evUnico->getUrlPrefixo();
            if ($urlPrefixo == '' || $urlPrefixo == '/') {
                $urlPrefixo = $evUnico->getGestor() . '/' . $evUnico->getCodEvento();
            }
            $urlRedir = 'https://' . FWA_Dominio() . '/' . $urlPrefixo . '/inscricoes/index.php?content=view/admin/default.php';
            echo "<script>window.location.href='" . $urlRedir . "';</script>";
            return;
        } elseif ($totalEventos > 1) {
            $urlListagem = UrlViewAdmin($organizacao, $debug) . '/index.php?content=view/admin/evento-cadastro-listar.php';
            echo "<script>window.location.href='" . $urlListagem . "';</script>";
            return;
        }
    } catch (Exception $e) {
    }
}

/* ------------------------------------------------------------------
 * Permissoes — avaliadas UMA vez cada (no default.php a mesma
 * permissao e consultada ate 6 vezes na mesma renderizacao)
 * ------------------------------------------------------------------ */
if (!function_exists('dnPode')) {
    function dnPode($codSistema, $codEvento = false) {
        return (bool) FWA_Autenticacao($_SESSION['_cod_usuario'], $codSistema, 5, false, false, $codEvento);
    }
}
/* Monta um item de menu.
 *   $tags = sinonimos que so existem para a BUSCA (nao aparecem na tela).
 *           E aqui que entram os nomes que outras funcoes dao para a mesma tela. */
if (!function_exists('dnItem')) {
    function dnItem($nome, $url, $sub = '', $tags = '', $alvo = '') {
        return array('nome' => $nome, 'url' => $url, 'sub' => $sub, 'tags' => $tags, 'alvo' => $alvo);
    }
}

// Quando COD_EVENTO='0' (admin sem evento), verificar permissao em qualquer evento via 'TODOS'
$codEvCheck = (COD_EVENTO == '0' && @$_SESSION['_administrativo']) ? 'TODOS' : false;

$podeAdmGeral       = dnPode(enumSistema::SIS_GERENCIAR_ADM_GERAL);
$podeAdmSistema     = dnPode($COD_SIS_ADM_SISTEMA, $codEvCheck);
$podeListarInscr    = dnPode($COD_SIS_LISTAR_INSCRICOES, $codEvCheck);
$podeConfiguracao   = dnPode($COD_SIS_ADM_CONFIGURACAO);
$podeRecepcao       = dnPode($COD_SIS_RECEPCAO_PARTICIPANTES);
$podeFinanceiro     = dnPode(enumSistema::SIS_CONTROLE_FINANCEIRO);
$podeVoucher        = dnPode(enumSistema::SIS_CONTROLE_VOUCHER);
$podePainelBordo    = dnPode(enumSistema::SIS_CONSULTAR_PAINEL_BORDO);
$podeListarTrabs    = dnPode(enumSistema::SIS_LISTAR_TRABALHOS);
$podeGerInscrito    = dnPode(enumSistema::SIS_GERENCIAR_INSCRITO);
$podeGerAvalTrabs   = dnPode(enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS);
$podeAvalInscrito   = dnPode(enumSistema::SIS_AVALIACAO_INSCRITO);
$podeInscritoApto   = dnPode(enumSistema::SIS_INSCRITO_APTO);
/* SIS_GERENCIAR_CERTIFICADO = 30 ("GERENCIAR CERTIFICADO" em sis_sistemas).
   ESTRITO de proposito: nao vale o 13 (Gerenciar Inscrito) nem o 14 (Voucher).
   Quem cuida de certificado tem o 30; quem so cadastra inscrito nao precisa
   ver o Midia Upload. */
$podeCertificado    = dnPode(enumSistema::SIS_GERENCIAR_CERTIFICADO);
$podeAvalTrabalho   = dnPode($COD_SIS_AVALIACAO_TRABALHO);
$podeAvalPremiados  = dnPode($COD_SIS_AVALIACAO_TRABALHO_PREMIADOS);
$podeDefPremiados   = dnPode($COD_SIS_ADM_AVALIACAO_TRABALHOS_DEFINIR_PREMIADOS);
$podeCargaTransp    = FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_CARGA_TRANSPARENCIA, 5, false, false);

$isPedCoordenador   = dnPode(enumSistema::SIS_PEDAGOGICO);
$isPedGerente       = dnPode(enumSistema::SIS_PEDAGOGICO_GERENTE);
$isPedSupervisor    = dnPode(enumSistema::SIS_PEDAGOGICO_SUPERVISOR);
$isPedAdmin         = $isPedCoordenador || $isPedGerente;

$podeInscricoes     = $podeAdmSistema || $podeListarInscr;
$temSubmissaoTrab   = ($objEvConf->getTrabSubmissao() == 1);
$porCandidato       = ($objEvConf->getTrabAvaliacaoPor() == 'CANDIDATO');
$gateway            = $objEvConf->getGatewayPagamento();
$ehHutec            = ($organizacao->getSigla() == 'HUTEC');

$objEv = new Evento();
if ($COD_EVENTO != '' and $COD_EVENTO != 'COD_EVENTO') {
    $objEv->setCodEvento($COD_EVENTO);
    $objEv->getEventoById();
}

$urlAdmin  = UrlViewAdmin($organizacao, $debug);
$tipoAtiv  = $objEvConf->getAtividadeTipo() ? $objEvConf->getAtividadeTipo() : 'Atividades';
$tipoTrab  = $objEvConf->getTrabAnexoTipo() ? $objEvConf->getTrabAnexoTipo() : 'Trabalhos';

/* ------------------------------------------------------------------
 * Telas que mais de uma funcao acessa.
 * URL + NOME GENERICO + os apelidos que cada funcao usa (so p/ busca).
 * O nome generico e usado em TODOS os blocos onde a tela aparece, entao
 * e impossivel a mesma tela ganhar dois nomes. Quem procura pelo nome
 * antigo ("distribuir", "gerenciar avaliacao") acha pela busca.
 * ------------------------------------------------------------------ */
$U_LISTAR_INSCRITOS = 'index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php';
$U_PAINEL_BORDO     = 'index.php?content=view/admin/consulta-dados-congresso-listagem.php';

$U_AVAL_FASES       = 'index.php?content=view/admin/AvaliacaoFases.php';
$N_AVAL_FASES       = 'Avaliação de Inscritos';
$T_AVAL_FASES       = 'gerenciar avaliacao inscrito fases edital candidato etapa nota selecao';

$U_AVAL_TRAB_GER    = 'index.php?content=view/admin/consulta-avaliacao-trabalhos-listagem.php';
$N_AVAL_TRAB_GER    = 'Avaliação de ' . $tipoTrab . ' (gerenciar)';
$T_AVAL_TRAB_GER    = 'gerenciar distribuir avaliador parecer nota comissao';

$U_TRAB_LISTAGEM    = 'index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php';
$N_TRAB_LISTAGEM    = 'Listar e Distribuir ' . $tipoTrab;
$T_TRAB_LISTAGEM    = 'listar distribuir submissao artigo resumo autor situacao avaliador';

$U_TRAB_GRAFICOS    = 'index.php?content=view/admin/consulta-dados-congresso-graficos-trabalhos.php';
$N_TRAB_GRAFICOS    = 'Painel de Bordo de ' . $tipoTrab;
$T_TRAB_GRAFICOS    = 'dashboard grafico estatistica area tematica bi';

/* ------------------------------------------------------------------
 * Atalhos rapidos — o que se usa todo dia. Maximo 6.
 * ------------------------------------------------------------------ */
/* A faixa NASCE enxuta: so Listar Inscritos e Painel de Bordo. O resto dos
   lugares fica vago e vai sendo ocupado pelas telas que a pessoa mais abre.
   Os demais itens da lista abaixo sao FALLBACK: so entram para quem nao tem
   acesso aos dois primeiros (ex: supervisor pedagogico). */
$atalhos = array();
if ($podeInscricoes)   $atalhos[] = array('Listar Inscritos',   $U_LISTAR_INSCRITOS, 'fa-users');
if ($podePainelBordo)  $atalhos[] = array('Painel de Bordo',    $U_PAINEL_BORDO,     'fa-line-chart');
if ($isPedAdmin || $isPedSupervisor) {
    $atalhos[] = array('Alunos',    'index.php?content=view/admin/GerenciarAlunos.php', 'fa-graduation-cap');
    $atalhos[] = array('Registros', 'index.php?content=view/admin/ConsultarRegistrosPedagogicosAluno.php', 'fa-pencil-square-o');
}
if ($podeRecepcao)     $atalhos[] = array('Recepcionar',        'index.php?content=view/recepcao/GerenciarPresenca.php', 'fa-check-circle');
if ($podeGerInscrito)  $atalhos[] = array('Cadastrar Inscrito', 'index.php?content=view/admin/inscritos-cadastro-incluir.php', 'fa-user-plus');
if ($podeConfiguracao) $atalhos[] = array('Configurar Projeto', 'index.php?content=view/admin/evento-configurar-alterar.php&cod_evento=' . $COD_EVENTO, 'fa-sliders');
if ($podeFinanceiro)   $atalhos[] = array('Financeiro',         'index.php?content=view/admin/GerenciarPagamentoUpload.php', 'fa-money');

/* Quantos atalhos de cada tipo. Mudar aqui muda a faixa inteira. */
$DN_QTD_FIXOS      = 2;   // os de sempre, por permissao
$DN_QTD_APRENDIDOS = 4;   // vagas que o uso preenche
$atalhos = array_slice($atalhos, 0, $DN_QTD_FIXOS);

/* ------------------------------------------------------------------
 * Secoes — mesmos blocos e mesmos itens do default.php
 * ------------------------------------------------------------------ */
$secoes = array();

/* --- Gerenciar Projetos ------------------------------------------- */
$itens = array();
if ($podeInscricoes) {
    $itens[] = dnItem('Listar Eventos/Cursos', $urlAdmin . '/index.php?content=view/admin/evento-cadastro-listar.php',
                      'trocar de projeto', 'mudar evento selecionar');
    /* Formulario atual (ÁREA ADMINISTRATIVA, com o submenu de Projetos). O menu
       antigo apontava para view/coordenador/GerenciarProjetos.php, que e a tela
       velha da "área do coordenador". */
    $itens[] = dnItem('Cadastrar Evento/Projeto', 'index.php?content=view/admin/evento-cadastro-incluir.php',
                      'criar novo', 'incluir cadastrar abrir projeto evento');
}
/* Ficha do Projeto: so admin geral, o mesmo gate do GerenciarProjetoAC. Esconder
   o link nao e protecao -- e para nao oferecer um caminho que termina em erro.
   Quem barra de verdade e o gate da propria tela. */
if ($podeAdmGeral) {
    $itens[] = dnItem('Ficha do Projeto', 'index.php?content=view/admin/GerenciarProjeto.php',
                      'dados institucionais do projeto', 'projeto ficha coordenador sigla programacao publico alvo');
}
if ($itens) $secoes[] = array('Gerenciar Projetos', 'fa-folder-open', $itens);

/* --- Comparar cadastros de projeto (SOMENTE LOCAL) -------------------
 * Card temporario para olhar lado a lado as duas telas de cadastro de projeto
 * por fase. Preso a eventos.test (mesma marca de ambiente usada no config.php):
 * mesmo que este arquivo suba por engano, nao aparece em producao.
 * Pode ser removido quando a comparacao terminar. */
if (@$_SERVER['HTTP_HOST'] == 'eventos.test' && $podeAdmGeral) {
    $secoes[] = array('Comparar cadastro de projeto', 'fa-clone', array(
        dnItem('Cadastro por fases — Admin', 'index.php?content=view/admin/GerenciarProjeto.php',
               '3 abas (Campo01 a 03) · exige admin geral', 'projeto fase aba comparar admin'),
        dnItem('Cadastro por fases — Coordenador', 'index.php?content=view/coordenador/GerenciarProjetos.php',
               'tela antiga da área do coordenador (Campo01 a 09)', 'projeto fase aba comparar coordenador antigo'),
        dnItem('Cadastrar Evento/Projeto (atual)', 'index.php?content=view/admin/evento-cadastro-incluir.php',
               'o formulário que o menu usa hoje', 'projeto evento incluir atual comparar'),
    ));
}

/* --- Configuracoes ------------------------------------------------ */
if ($podeConfiguracao) {
    $secoes[] = array('Configurações', 'fa-cog', array(
        dnItem('Editar Projeto', 'index.php?content=view/admin/evento-cadastro-alterar.php&cod_evento=' . $COD_EVENTO,
               'nome, datas, local, arte', 'alterar evento dados'),
        dnItem('Configurar Projeto', 'index.php?content=view/admin/evento-configurar-alterar.php&cod_evento=' . $COD_EVENTO,
               'regras, módulos, pagamento', 'configuracao parametro gateway certificado'),
        dnItem('Gerenciar Grupos de Participantes', 'index.php?content=view/admin/perfil-cadastro-ok.php',
               'perfis e valores', 'categoria preco lote'),
        dnItem('Gerenciar ' . $tipoAtiv, 'index.php?content=view/admin/oficina-cadastro-ok.php',
               'oficinas, cursos, atividades', 'minicurso palestra sala vaga'),
    ));
    /* Feriado e Calendario Base: coordenador ou admin geral (mesmo gate dos
       controllers). Ficavam soltos no bloco de Configuracoes (direito 6): quem
       configurava o evento via o link e levava erro ao clicar. */
    if ($podeAdmGeral || $isPedCoordenador) {
        $secoes[count($secoes) - 1][2][] = dnItem('Gerenciar Feriado',
               'index.php?content=view/admin/GerenciarFeriado.php',
               'calendário', 'data ponto feriado');
        $secoes[count($secoes) - 1][2][] = dnItem('Calendário Base',
               'index.php?content=view/admin/GerenciarCalendarioBase.php',
               'calendário do ano', 'data ponto calendario base');
    }
}

/* --- Inscricoes ---------------------------------------------------- */
$itens = array();
if ($podeInscricoes) {
    if ($podePainelBordo) {
        $itens[] = dnItem('Painel de Bordo, Dados do Congresso (BI)', $U_PAINEL_BORDO,
                          'números e gráficos do evento', 'dashboard relatorio estatistica bi');
        if ($temSubmissaoTrab) {
            $itens[] = dnItem($N_TRAB_GRAFICOS, $U_TRAB_GRAFICOS, 'gráficos por área temática', $T_TRAB_GRAFICOS);
        }
    }
    if ($podeListarTrabs && $temSubmissaoTrab && $objEvConf->getCodEvento() != 107) {
        $itens[] = dnItem($N_TRAB_LISTAGEM, $U_TRAB_LISTAGEM, 'situação, autores, distribuição', $T_TRAB_LISTAGEM);
    }
    $itens[] = dnItem('Listar Inscritos', $U_LISTAR_INSCRITOS,
                      'busca, filtros, exportar', 'participante aluno relacao planilha excel');
    if ($podeGerInscrito) {
        $itens[] = dnItem('Cadastrar Inscritos', 'index.php?content=view/admin/inscritos-cadastro-incluir.php',
                          'inscrição manual', 'incluir novo participante');
    }
    if ($podeVoucher) {
        $itens[] = dnItem('Gerenciar Desconto', 'index.php?content=view/admin/GerenciarDesconto.php',
                          'cupom, abatimento', 'promocao valor');
        if ($objEvConf->getEmitirVoucher() == 1) {
            $itens[] = dnItem('Gerenciar Vouchers', 'index.php?content=view/admin/GerenciarVoucherEvento.php',
                              'cortesia, isenção', 'gratuidade convite');
        }
    }
    if ($podeGerAvalTrabs) {
        $itens[] = dnItem($N_AVAL_FASES, $U_AVAL_FASES, 'fases da seleção', $T_AVAL_FASES);
        $itens[] = dnItem($N_AVAL_TRAB_GER, $U_AVAL_TRAB_GER, 'distribuição e notas', $T_AVAL_TRAB_GER);
    }
    if ($objEvConf->getAtividadeConteudoLiberar() == 1 && $podeAdmSistema) {
        $itens[] = dnItem('Gerenciar Conteúdo para Atividades', 'index.php?content=view/admin/GerenciarArquivoAtividade.php',
                          'material de apoio', 'arquivo pdf aula');
    }
    if ($objEvConf->getRegistroDiario()) {
        $itens[] = dnItem('Registro de Atividades (Avaliação)', 'index.php?content=view/admin/GerenciarRegistroAtividadeAvaliacao.php',
                          'diário de horas, validação', 'registro diario carga horaria');
    }
}
if ($itens) $secoes[] = array('Inscrições', 'fa-users', $itens);

/* --- Avaliacao ----------------------------------------------------- */
$itens = array();
if ($podeAvalInscrito && $porCandidato) {
    $itens[] = dnItem($N_AVAL_FASES, $U_AVAL_FASES, 'fases da seleção', $T_AVAL_FASES);
}
if ($podeGerAvalTrabs && $objEvConf->getTrabAvaliacao() == 1 && !$porCandidato) {
    $itens[] = dnItem($N_AVAL_TRAB_GER, $U_AVAL_TRAB_GER, 'distribuição e notas', $T_AVAL_TRAB_GER);
}
if ($podeGerAvalTrabs) {
    $itens[] = dnItem('Painel de Avaliação', 'index.php?content=view/admin/DashboardAvaliacao.php',
                      'acompanhamento', 'dashboard progresso pendente');
    $itens[] = dnItem('Etapas de Avaliação', 'index.php?content=view/admin/GerenciarEtapa.php',
                      'configurar fases', 'criterio peso');
    if ($porCandidato) {
        $itens[] = dnItem('Gerenciar Avaliadores', 'index.php?content=view/admin/GerenciarAvaliadores.php',
                          'quem avalia', 'banca parecerista');
    }
}
if ($itens) $secoes[] = array('Avaliação', 'fa-check-square-o', $itens);

/* --- Comissao Cientifica ------------------------------------------- */
if ($temSubmissaoTrab && ($podeAvalTrabalho || $podeAvalPremiados)) {
    $itens = array();
    if ($podeListarTrabs) {
        $itens[] = dnItem($N_TRAB_GRAFICOS, $U_TRAB_GRAFICOS, 'gráficos por área temática', $T_TRAB_GRAFICOS);
    }
    if ($podeAvalTrabalho) {
        $itens[] = dnItem('Avaliação de ' . $tipoTrab, 'index.php?content=view/avaliacao/avaliacao-trabalhos-apresentacao.php',
                          'sou avaliador', 'avaliar nota parecer');
    }
    if ($podeAvalPremiados) {
        $itens[] = dnItem('Avaliação de ' . $tipoTrab . ' para Premiação', 'index.php?content=view/avaliacao_premiacao/avaliacao-trabalhos-apresentacao.php',
                          'sou avaliador de prêmio', 'premiacao melhor');
    }
    if ($podeListarTrabs || $podeGerAvalTrabs) {
        // "Listar" e "Distribuir" eram dois itens para esta MESMA tela — viraram um nome so
        $itens[] = dnItem($N_TRAB_LISTAGEM, $U_TRAB_LISTAGEM, 'situação, autores, distribuição', $T_TRAB_LISTAGEM);
    }
    if ($podeListarTrabs) {
        $itens[] = dnItem('Listar ' . $tipoTrab . ' Aprovados - Anais', 'index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos-aprovados-anais.php',
                          'publicação', 'anais aprovado');
    }
    if ($podeAdmSistema && $COD_SIS_MASTER == 1) {
        $itens[] = dnItem('Distribuir ' . $tipoTrab . ' para Avaliadores', 'index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos-avaliadores.php',
                          'por avaliador', 'sorteio');
        $itens[] = dnItem($N_AVAL_TRAB_GER, $U_AVAL_TRAB_GER, 'notas e pareceres', $T_AVAL_TRAB_GER);
        $itens[] = dnItem('Sistema Comunicado de Aprovação/Reprovação', 'index.php?content=view/admin/comunicado-avaliacao-trabalhos-listagem.php',
                          'e-mail de resultado', 'comunicar aprovado reprovado');
    }
    if ($podeListarTrabs) {
        if ($COD_SIS_MASTER == 1) {
            $itens[] = dnItem('Relatório do Ensalamento', 'index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos-ensalados.php',
                              'salas de apresentação', 'ensalamento sala horario');
        }
        $itens[] = dnItem('Pesquisar Anais', 'index.php?content=view/anais/anais_pesquisa.php', 'busca pública', 'anais consulta');
        $itens[] = dnItem('Cadastro Áreas Temáticas', 'index.php?content=view/admin/area-tematica-cadastro-ok.php', 'eixos', 'tema eixo');
    }
    if ($podeDefPremiados) {
        $itens[] = dnItem('Cadastro Premiações Científicas', 'index.php?content=view/admin/premiacoes-cientifica-cadastro-ok.php', 'categorias de prêmio', 'premio');
        $itens[] = dnItem('Definir Premiações', 'index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos-premiacao.php', 'escolher vencedores', 'premiado melhor');
    }
    if ($itens) $secoes[] = array('Comissão Científica', 'fa-file-text-o', $itens);
}

/* --- Usuarios ------------------------------------------------------ */
if ($podeConfiguracao) {
    $itens = array();
    if ($temSubmissaoTrab) {
        $itens[] = dnItem('Ligar Convidados a Áreas-Temáticas (p/ Avaliador)', 'index.php?content=view/admin/ligacao-convidados-areas-tematicas-listagem.php',
                          'quem avalia o que', 'avaliador area tema vinculo');
    }
    $itens[] = dnItem('Gerenciar Acessos', 'index.php?content=view/admin/GerenciarGestaoAcessos.php',
                      'login, permissão, senha', 'usuario grupo direito trocar senha');
    if ($podeGerInscrito) {
        $itens[] = dnItem('Gerenciar Carga Pessoas', 'index.php?content=view/admin/GerenciarCargaPessoaUpload.php',
                          'importar planilha', 'carga importacao excel lote');
    }
    $secoes[] = array('Usuários', 'fa-key', $itens);
}

/* --- Financeiro ---------------------------------------------------- */
if ($podeFinanceiro) {
    $itens = array();
    if (in_array($gateway, array('UNIPRIME', 'SISPRIME')) or $objEvConf->getCodEvento() == 45) {
        $itens[] = dnItem('Carregar Boletos Pagos', 'index.php?content=view/admin/carregarBoletosPagos.php',
                          'retorno, baixa', 'pagamento conciliacao arquivo');
        $itens[] = dnItem('Gerar Arquivo Remessa', 'index-action.php?content=controller/CommandAC.php&c=GerenciarBoletoRemessa&m=gerarArquivoRemessa',
                          'envio ao banco', 'remessa cnab', '_blank');
    }
    if ($gateway == 'PAGSEGURO') {
        $itens[] = dnItem('Carregar PagSeguro', 'index.php?content=view/admin/GerenciarPagamentoUpload.php',
                          'retorno, baixa', 'pagamento csv conciliacao');
    }
    if ($gateway == 'ITAU-SHOPLINE') {
        $itens[] = dnItem('Carregar Itaushopline', 'index.php?content=view/admin/GerenciarItaushoplineUpload.php',
                          'retorno, baixa', 'pagamento conciliacao');
    }
    if ($gateway == 'ITAU-BOLECODE') {
        $itens[] = dnItem('Carregar Itau BoleCode', 'index.php?content=view/admin/GerenciarItaushoplineUpload.php',
                          'retorno, baixa', 'pagamento pix conciliacao');
    }
    if ($gateway == 'PAY-PAL') {
        $itens[] = dnItem('Carregar Pay-Pal', 'index.php?content=view/admin/GerenciarPagamentoUpload.php',
                          'retorno, baixa', 'pagamento conciliacao');
    }
    if ($ehHutec) {
        $itens[] = dnItem('Carregar Retorno HUTEC', 'index.php?content=view/admin/GerenciarRetornoHutecUpload.php',
                          'retorno, baixa', 'xls conciliacao critica');
        $itens[] = dnItem('Consulta Transparência', WB_URL_DOMINIO . '/' . strtolower($objEv->getGestor()) . '/atividades/inscricoes/index.php?content=view/transparencia/localizar_projetos.php',
                          'portal público', 'transparencia projeto');
        if ($podeFinanceiro || $podeCargaTransp) {
            $itens[] = dnItem('Carga Transparência', WB_URL_VIEW . '?content=view/admin/GerenciarCargaTransparencia.php',
                              'importar', 'carga transparencia');
        }
    }
    if ($itens) $secoes[] = array('Financeiro', 'fa-money', $itens);
}

/* --- Recepcao ------------------------------------------------------ */
if ($podeRecepcao) {
    $secoes[] = array('Recepção', 'fa-qrcode', array(
        dnItem('Recepcionar Participantes', 'index.php?content=view/recepcao/GerenciarPresenca.php',
               'presença, credenciamento', 'check-in crachá frequencia qrcode'),
    ));
}

/* --- Comunicados --------------------------------------------------- */
if ($podeConfiguracao) {
    $secoes[] = array('Comunicados', 'fa-envelope-o', array(
        dnItem('Gerenciar Email Templates', 'index.php?content=view/admin/GerenciarEmailTemplate.php',
               'modelos de mensagem', 'email template automatico'),
        dnItem('Gerenciar Avisos', 'index.php?content=view/admin/GerenciarMsgAviso.php',
               'recado na tela do inscrito', 'aviso mensagem'),
    ));
}

/* --- Academico ----------------------------------------------------- */
if ($isPedCoordenador || $isPedGerente || $isPedSupervisor) {
    $itens = array();
    if ($isPedAdmin) {
        $itens[] = dnItem('Colégios', 'index.php?content=view/admin/GerenciarColegios.php', 'escolas', 'escola instituicao');
    }
    $itens[] = dnItem('Alunos', 'index.php?content=view/admin/GerenciarAlunos.php', 'cadastro', 'aluno estudante');
    $itens[] = dnItem('Gestão por Agente', 'index.php?content=view/admin/GerenciarGestaoAgente.php',
                      'vínculo supervisor/aluno', 'agente supervisor vinculo');
    $itens[] = dnItem('Registros', 'index.php?content=view/admin/ConsultarRegistrosPedagogicosAluno.php',
                      'orientações e atendimentos', 'registro orientacao atendimento');
    if ($isPedAdmin) {
        $itens[] = dnItem('Laudos', 'index.php?content=view/admin/GerenciarLaudosAluno.php', 'documentos do aluno', 'laudo documento');
        $itens[] = dnItem('Supervisores', 'index.php?content=view/admin/GerenciarSupervisores.php', 'logins', 'supervisor acesso');
        $itens[] = dnItem('Relatórios', 'index.php?content=view/admin/DashboardPedagogico.php', 'dashboard pedagógico', 'relatorio indicador');
    }
    $secoes[] = array('Acadêmico', 'fa-graduation-cap', $itens);
}

/* --- Outros --------------------------------------------------------
 * O bloco aparece se a pessoa tiver acesso a PELO MENOS UM item dele.
 * Associados/Interessados seguem em SIS_CONTROLE_VOUCHER (herdado do menu
 * antigo); Midia Upload saiu do voucher e foi para certificado/configuracao. */
if ($podeVoucher || $podeCertificado || $podeConfiguracao) {
    $itens = array();
    if ($podeVoucher) {
        $itens[] = dnItem('Cadastrar Associados', 'index.php?content=view/admin/GerenciarAssociado.php', 'cadastro', 'associado socio');
        if ($podeInscritoApto) {
            $itens[] = dnItem('Cadastrar Inscrito Apto', 'index.php?content=view/admin/GerenciarInscritoApto.php', 'lista prévia', 'apto habilitado');
        }
        $itens[] = dnItem('Cadastrar Interessados', 'index.php?content=view/admin/GerenciarInteressado.php', 'lead, contato', 'interessado contato');
    }
    /* Midia Upload NAO sai por voucher: quem sobe arte, logo e assinatura e
       quem cuida do certificado ou quem configura o evento. */
    if ($podeCertificado || $podeConfiguracao) {
        $itens[] = dnItem('Gerenciar Mídia Upload', 'index.php?content=view/admin/GerenciarMidiaUpload.php', 'imagens e arquivos', 'midia banner foto upload certificado arte assinatura');
    }
    if ($itens) $secoes[] = array('Outros', 'fa-database', $itens);
}

/* ------------------------------------------------------------------
 * "Mesma tela": acha sozinho os itens que apontam para a MESMA url e
 * anota o apelido usado nos outros lugares. Assim o apelido por funcao
 * continua existindo, mas quem ve tudo entende que e um destino so.
 * ------------------------------------------------------------------ */
/* 1) dentro de um mesmo bloco, a mesma url nunca aparece duas vezes */
foreach ($secoes as $i => $sec) {
    $vistas = array();
    $limpos = array();
    foreach ($sec[2] as $it) {
        if (isset($vistas[$it['url']])) continue;
        $vistas[$it['url']] = true;
        $limpos[] = $it;
    }
    $secoes[$i][2] = $limpos;
}

/* 2) a mesma url em blocos diferentes: marca onde mais aparece.
 *    Como o nome generico e unico, o aviso e "tambem em X" — nao "outro nome". */
$porUrl = array();
foreach ($secoes as $i => $sec) {
    foreach ($sec[2] as $j => $it) {
        $porUrl[$it['url']][] = array($i, $j, $sec[0], $it['nome']);
    }
}
$totalItens = 0;
foreach ($secoes as $i => $sec) {
    foreach ($sec[2] as $j => $it) {
        $totalItens++;
        $iguais = $porUrl[$it['url']];
        if (count($iguais) < 2) continue;
        $outrosLocais = array();
        foreach ($iguais as $ig) {
            if ($ig[0] == $i && $ig[1] == $j) continue;
            $outrosLocais[] = $ig[2];
            // apelido usado no outro bloco (se houver) tambem acha pela busca
            $secoes[$i][2][$j]['tags'] .= ' ' . $ig[3];
        }
        $secoes[$i][2][$j]['mesma'] = 'Esta mesma tela também aparece em: ' . implode(', ', $outrosLocais);
    }
}

/* ------------------------------------------------------------------
 * Atalhos aprendidos — completa a faixa com as telas que ESTE login
 * mais abriu NESTE evento (tab_menu_acesso).
 * Regras: nunca repete um fixo, e so entra tela que o usuario ainda
 * tem permissao de ver hoje (checagem = estar no $indice).
 * ------------------------------------------------------------------ */
if (!function_exists('dnChave')) {
    // chave de contagem = o "content=" da url (mesma coisa que o JS envia)
    function dnChave($url) {
        return preg_match('/content=([^&]+)/', $url, $m) ? urldecode($m[1]) : '';
    }
}

$indice = array();
foreach ($secoes as $sec) {
    foreach ($sec[2] as $it) {
        $k = dnChave($it['url']);
        if ($k && !isset($indice[$k])) {
            $indice[$k] = array($it['nome'], $it['url'], $sec[1], $sec[0]);
        }
    }
}

$jaNaFaixa = array();
foreach ($atalhos as $a) {
    $k = dnChave($a[1]);
    if ($k) $jaNaFaixa[$k] = true;
}

/* Throwable (nao Exception): se as classes novas ou a tabela ainda nao
   estiverem no servidor, a faixa fica so com os fixos em vez de derrubar
   a tela inteira com Fatal error. */
$maisUsados = array();
if (class_exists('MenuAcesso')) {
    try {
        $objMenuAcesso = new MenuAcesso();
        $objMenuAcesso->setCodUsuario($_SESSION['_cod_usuario']);
        $objMenuAcesso->setCodEvento(COD_EVENTO);
        // pede folga: alguns dos mais usados podem ja ser fixos ou ter saido do menu
        $maisUsados = $objMenuAcesso->listarMaisUsados($DN_QTD_APRENDIDOS + 8);
    } catch (Throwable $e) {
        $maisUsados = array();
    }
}

$qtdAprendidos = 0;
foreach ($maisUsados as $k) {
    if ($qtdAprendidos >= $DN_QTD_APRENDIDOS) break;
    if (isset($jaNaFaixa[$k])) continue;
    if (!isset($indice[$k]))  continue;
    $atalhos[] = array($indice[$k][0], $indice[$k][1], $indice[$k][2], true);
    $jaNaFaixa[$k] = true;
    $qtdAprendidos++;
}
?>
<style>
.dn-wrap { margin-bottom: 28px; }
/* mesmas regras de bootstrap3-compat.css — repetidas aqui porque aquele
   arquivo so e carregado quando $USAR_BOOTSTRAP_5 = true */
.header-area { margin-bottom:16px; }
.header-area-titulo { color:#7a869a; font-size:16px; font-weight:400; margin:0 0 2px; }
.header-area-evento { font-size:20px; font-weight:600; margin:2px 0 0; }

.dn-busca-box { position:relative; margin-bottom:18px; }
.dn-busca-box .fa-search { position:absolute; left:12px; top:20px; transform:translateY(-50%); color:#9ca3af; }
.dn-busca { width:100%; padding:10px 34px 10px 34px; border:1px solid #d1d5db; border-radius:6px;
            font-size:15px; background:#fff; box-shadow:none; }
.dn-busca:focus { outline:none; border-color:#6b93c6; box-shadow:0 0 0 3px rgba(107,147,198,.18); }
.dn-limpar { position:absolute; right:8px; top:20px; transform:translateY(-50%); border:0; background:transparent;
             color:#9ca3af; font-size:16px; line-height:1; padding:4px 8px; cursor:pointer; display:none; }
.dn-dica { font-size:12px; color:#9ca3af; margin:6px 2px 0; }

.dn-atalhos { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:22px; }
.dn-atalho { display:flex; align-items:center; gap:9px; padding:11px 16px; border:1px solid #d8dee6;
             border-radius:6px; background:#fff; text-decoration:none; color:#1f2937; font-weight:600;
             font-size:14px; transition:all .12s; }
.dn-atalho:hover, .dn-atalho:focus { text-decoration:none; color:#111827; border-color:#7f9fc4;
             box-shadow:0 2px 6px rgba(0,0,0,.09); }
.dn-atalho .fa { color:#5b7fa6; font-size:16px; }
/* atalho que entrou pelo uso, nao pela lista fixa */
.dn-atalho.dn-aprendido { border-style:dashed; border-color:#c3cede; }
.dn-atalho.dn-aprendido:hover { border-style:solid; }

/* colunas fluidas (nao grid): cards de alturas diferentes se encaixam
   sem deixar vao vazio embaixo dos blocos curtos */
.dn-grid { columns:3 300px; column-gap:16px; }
.dn-card { border:1px solid #e3e7ec; border-radius:7px; background:#fff; overflow:hidden;
           break-inside:avoid; -webkit-column-break-inside:avoid; page-break-inside:avoid;
           display:inline-block; width:100%; margin-bottom:16px; }
.dn-card-head { display:flex; align-items:center; gap:9px; padding:11px 14px; background:#f6f8fa;
                border-bottom:1px solid #e9edf2; font-weight:600; font-size:14px; color:#374151; }
.dn-card-head .fa { color:#5b7fa6; width:16px; text-align:center; }
.dn-card ul { list-style:none; margin:0; padding:6px 0; }
.dn-card li a { display:block; padding:8px 14px; text-decoration:none; font-size:14px; line-height:1.35; }
.dn-card li a:hover, .dn-card li a:focus { background:#f2f6fb; text-decoration:none; }
.dn-card li a small { display:block; color:#9ca3af; font-size:11.5px; font-weight:400; }
.dn-mesma { color:#b9c2cc; font-size:11px; margin-left:5px; cursor:help; }

.dn-vazio { display:none; padding:26px; text-align:center; color:#6b7280;
            border:1px dashed #d1d5db; border-radius:7px; }
.dn-oculto { display:none !important; }

@media (max-width:600px) {
  .dn-grid { columns:1; }
  .dn-atalho { flex:1 1 100%; }
}
</style>

<div class="dn-wrap">

    <?php
    /* Mesmo cabecalho das demais telas: "Area Administrativa" + nome do evento
       + icone de trocar evento (modal com filtro). O default e uma das poucas
       telas que pode abrir SEM evento — por isso o fallback abaixo. */
    if (!isset($cod_evento)) { $cod_evento = COD_EVENTO; }
    if ($_SESSION['_administrativo']) {
        require(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
    }
    ?>

    <?php if (!empty($fws_['msg'])) { FWA_Mostrar_Mensagem($fws_['msg']); } ?>

    <?php if (!$_SESSION['_administrativo']) {
        $_SESSION['_logado'] = null; ?>
        <div class="panel panel-default">
            <div class="panel-body">
                Para acessar o sistema &eacute; necess&aacute;rio estar logado.<br><br>
                <a href="index.php?content=view/admin/login.php">&Aacute;rea administrativa</a><br><br>
                <a href="index.php?content=view/inscricao/inscricao_01.php#inscricao">&Aacute;rea inscrito</a>
            </div>
        </div>
    <?php } elseif (!$secoes) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">Aviso</div>
            <div class="panel-body">
                <a href="<?php echo WB_URL_VIEW ?>?content=view/admin/evento-cadastro-listar.php">Clique aqui para selecionar um evento/curso</a>
                <?php if ($ehHutec) { ?>
                    <br><br>
                    <a href="<?php echo WB_URL_ATIVIDADES ?>?content=view/transparencia/localizar_projetos.php">Consulta Transpar&ecirc;ncia</a>
                <?php } ?>
            </div>
        </div>
    <?php } else { ?>

        <div class="dn-busca-box">
            <i class="fa fa-search"></i>
            <input type="text" id="dnBusca" class="dn-busca" autocomplete="off"
                   placeholder="Buscar no menu... (ex: inscrito, boleto, avaliador, senha)">
            <button type="button" class="dn-limpar" id="dnLimpar" title="Limpar">&times;</button>
            <p class="dn-dica"><?php echo $totalItens; ?> op&ccedil;&otilde;es dispon&iacute;veis &mdash; tecle <b>/</b> para buscar</p>
        </div>

        <?php if ($atalhos) { ?>
            <div class="dn-atalhos" id="dnAtalhos">
                <?php foreach ($atalhos as $a) {
                    $ehAprendido = !empty($a[3]); ?>
                    <a class="dn-atalho<?php echo $ehAprendido ? ' dn-aprendido' : ''; ?>"
                       href="<?php echo $a[1]; ?>"
                       data-k="<?php echo htmlspecialchars(dnChave($a[1]), ENT_QUOTES); ?>"
                       <?php if ($ehAprendido) { ?>title="Atalho que apareceu aqui porque voc&ecirc; usa bastante"<?php } ?>>
                        <i class="fa <?php echo $a[2]; ?>"></i><?php echo $a[0]; ?>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="dn-grid" id="dnGrid">
            <?php foreach ($secoes as $sec) { ?>
                <div class="dn-card" data-card>
                    <div class="dn-card-head">
                        <i class="fa <?php echo $sec[1]; ?>"></i><?php echo $sec[0]; ?>
                    </div>
                    <ul>
                        <?php foreach ($sec[2] as $it) {
                            $alvo  = $it['alvo'] ? ' target="' . $it['alvo'] . '"' : '';
                            $busca = strtolower($sec[0] . ' ' . $it['nome'] . ' ' . $it['sub'] . ' ' . $it['tags']);
                            ?>
                            <li data-item data-busca="<?php echo htmlspecialchars($busca, ENT_QUOTES); ?>">
                                <a href="<?php echo $it['url']; ?>"<?php echo $alvo; ?>
                                   data-k="<?php echo htmlspecialchars(dnChave($it['url']), ENT_QUOTES); ?>">
                                    <?php echo $it['nome']; ?><?php if (!empty($it['mesma'])) { ?><i
                                        class="fa fa-link dn-mesma" title="<?php echo htmlspecialchars($it['mesma'], ENT_QUOTES); ?>"></i><?php } ?>
                                    <?php if ($it['sub']) { ?><small><?php echo $it['sub']; ?></small><?php } ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
        </div>

        <div class="dn-vazio" id="dnVazio">
            Nada encontrado para <b id="dnTermo"></b>.
        </div>

        <script>
        (function () {
            var campo   = document.getElementById('dnBusca');
            var limpar  = document.getElementById('dnLimpar');
            var atalhos = document.getElementById('dnAtalhos');
            var vazio   = document.getElementById('dnVazio');
            var termoEl = document.getElementById('dnTermo');
            var cards   = [].slice.call(document.querySelectorAll('[data-card]'));
            if (!campo) return;

            function semAcento(s) {
                // remove diacriticos: buscar "inscricao" acha "Inscrição"
                return s.normalize ? s.normalize('NFD').replace(RegExp('[\\u0300-\\u036f]', 'g'), '') : s;
            }

            function filtrar() {
                var termo = semAcento(campo.value.toLowerCase().trim());
                limpar.style.display = termo ? 'block' : 'none';
                if (atalhos) atalhos.className = 'dn-atalhos' + (termo ? ' dn-oculto' : '');

                var achou = 0;
                cards.forEach(function (card) {
                    var visiveis = 0;
                    [].slice.call(card.querySelectorAll('[data-item]')).forEach(function (li) {
                        var bate = !termo || semAcento(li.getAttribute('data-busca')).indexOf(termo) !== -1;
                        li.className = bate ? '' : 'dn-oculto';
                        if (bate) { visiveis++; achou++; }
                    });
                    card.className = 'dn-card' + (visiveis === 0 ? ' dn-oculto' : '');
                });

                vazio.style.display = (termo && achou === 0) ? 'block' : 'none';
                termoEl.textContent = campo.value;
            }

            campo.addEventListener('input', filtrar);
            campo.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { campo.value = ''; filtrar(); }
                if (e.key === 'Enter') {   // abre o primeiro resultado visivel
                    var link = document.querySelector('[data-card]:not(.dn-oculto) [data-item]:not(.dn-oculto) a');
                    if (link) link.click();
                }
            });
            limpar.addEventListener('click', function () { campo.value = ''; filtrar(); campo.focus(); });

            // Conta o clique. sendBeacon nao bloqueia a navegacao: o navegador
            // entrega em segundo plano mesmo com a pagina ja saindo.
            var URL_CONTA = 'index-action.php?content=controller/CommandAC.php&c=GerenciarMenuAcesso&m=registrar';
            document.addEventListener('click', function (e) {
                var a = e.target.closest ? e.target.closest('a[data-k]') : null;
                if (!a) return;
                var k = a.getAttribute('data-k');
                if (!k || !navigator.sendBeacon) return;
                try {
                    var fd = new FormData();
                    fd.append('k', k);
                    navigator.sendBeacon(URL_CONTA, fd);
                } catch (err) { /* contador nunca atrapalha a navegacao */ }
            });

            document.addEventListener('keydown', function (e) {   // "/" foca a busca
                var tag = (e.target.tagName || '').toUpperCase();
                if (e.key === '/' && tag !== 'INPUT' && tag !== 'TEXTAREA' && tag !== 'SELECT') {
                    e.preventDefault();
                    campo.focus();
                }
            });
        })();
        </script>

    <?php } ?>

<?php if (!empty($_SESSION['_logado'])) { ?>
<!-- Selo SiteLock: verificacao de seguranca do site (rodape do conteudo, so admin). Clique para conferir o status. -->
<div class="sitelock-selo" style="margin:20px 0 8px; padding-top:12px; border-top:1px solid #eee; text-align:center;">
    <a href="#" onclick="window.open('https://www.sitelock.com/verify.php?site=opctec.com.br','SiteLock','width=600,height=600,left=160,top=170'); return false;" title="Verificar seguranca do site (SiteLock)">
        <img class="img-fluid" alt="SiteLock" title="SiteLock" src="https://shield.sitelock.com/shield/opctec.com.br" style="max-height:54px;" />
    </a>
    <div><small style="color:#888;">Selo de seguran&ccedil;a &mdash; clique para verificar o status</small></div>
</div>
<?php } ?>

</div></div></div>
