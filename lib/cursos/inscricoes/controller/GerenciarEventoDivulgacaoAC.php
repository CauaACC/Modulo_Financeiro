<?php

/**
 * Divulgacao do evento — pagina de apresentacao do evento.
 *
 * O destino PRINCIPAL e o proprio InscriTec: quem se inscreve direto pelo
 * sistema ve esta pagina, sem precisar de nenhum site externo. A publicacao
 * em WordPress e OPCIONAL (um "plus") — so para quem tiver site proprio,
 * configurado na aba "Config WordPress".
 */
class GerenciarEventoDivulgacaoAC
{

    const LOCAL_MIDIA_ARTE = 'ARTE-DIVULGACAO';

    /** Campos do form = colunas de tab_evento_divulgacao = campos ACF do WP. */
    private $camposConteudo = array(
        'titulo', 'periodo_de_inscricoes', 'publico_alvo', 'periodo_do_curso',
        'vagas', 'local', 'carga_horaria', 'investimento', 'descricao',
        'programacao', 'outras_informacoes', 'link_inscricoes',
    );

    /** Campos que existem so no InscriTec (nao vao no bloco acf do WP). */
    private $camposLocais = array('tags');

    function __construct()
    {
        FWA_exigirPermissao(array(enumSistema::SIS_GERENCIAR_EVENTO, enumSistema::SIS_GERENCIAR_ADM_GERAL));
        // Por ora a tela e exclusiva do super admin (mesmo criterio do
        // "definir senha manual" do GerenciarTrocaSenhaInscrito). Quando for
        // liberar para coordenadores, basta remover este bloco.
        if ((int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0) !== 668) {
            throw new Exception("Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar a Divulga&ccedil;&atilde;o do Evento.");
        }
    }

    /**
     * Carrega o registro do evento corrente (ou as sugestoes, se ainda nao
     * existe) + situacao da arte e da publicacao. Resposta JSON.
     */
    public function carregar($fws_, $debug = false)
    {
        $obj = new EventoDivulgacao();
        $existe = $obj->carregarPorEvento($debug);
        $obj->sugerirCampos($debug);

        $campos = array();
        foreach (array_merge($this->camposConteudo, $this->camposLocais) as $campo) {
            $valor = $obj->getPadrao($campo);
            $campos[$campo] = $valor !== null ? stripslashes($valor) : '';
        }

        $arte = $this->localizarArte($debug);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => '1',
            'existe'        => $existe ? '1' : '',
            'campos'        => $campos,
            'wpPostId'      => $obj->getWpPostId(),
            'statusPublicacao' => $obj->getStatusPublicacao(),
            // Conteudo ATIVADA/INATIVADA: controla se a pagina aparece no InscriTec.
            'statusConteudo' => $obj->getStatus() ? $obj->getStatus() : 'ATIVADA',
            // Status do post no WordPress (publish/draft), quando ja enviado.
            'wpStatus'      => $obj->getWpStatus() ? $obj->getWpStatus() : '',
            'arteArquivo'   => $arte ? $arte->getArquivoGerado() : '',
            // Config do WordPress de destino. A senha de aplicativo NUNCA volta
            // ao navegador — so o flag de que existe.
            'wpUrl'             => $obj->getWpUrl() ? stripslashes($obj->getWpUrl()) : '',
            'wpUser'            => $obj->getWpUser() ? stripslashes($obj->getWpUser()) : '',
            'wpAppPassDefinida' => $obj->getWpAppPass() != '' ? '1' : '',
            'fallbackEnv'       => (defined('WB_HUTEC_WP_USER') && WB_HUTEC_WP_USER != '') ? '1' : '',
        ));
    }

    public function salvar($fws_, $debug = false)
    {
        $ok = false;
        $e = null;
        try {
            $obj = $this->montarObj($fws_, $debug);
            $obj->salvar($debug);
        } catch (OKException $ex) {
            $ok = true;
            $e = $ex;
        } catch (Exception $ex) {
            $e = $ex;
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $e ? $e->getMessage() : '',
        ));
    }

    /**
     * Upload da arte de divulgacao (quadrada 1:1, minimo 1080x1080, PNG/JPG).
     * Reusa a infra do MidiaUpload (tab_arquivo + logn/eventos/{cod}/media/).
     */
    public function salvarArte($fws_, $debug = false)
    {
        $ok = false;
        $e = null;
        try {
            if (empty($_FILES['arquivo']['name'])) {
                throw new Exception("Selecione o arquivo da arte.");
            }

            require_once(dirname(__FILE__) . '/../include/functions/validarTipoArquivoUpload.php');
            validarTipoArquivoUpload($_FILES['arquivo']['tmp_name'], $_FILES['arquivo']['name'],
                array('png', 'jpg', 'jpeg'));

            $dim = @getimagesize($_FILES['arquivo']['tmp_name']);
            if (!$dim || empty($dim[0]) || empty($dim[1])) {
                throw new Exception("Nao foi possivel ler as dimensoes da imagem.");
            }
            if ($dim[0] != $dim[1] || $dim[0] < 1080) {
                throw new Exception("A arte deve ser QUADRADA (proporcao 1:1) com no minimo "
                    . "1080 x 1080 pixels. A imagem enviada tem " . $dim[0] . " x " . $dim[1] . " pixels.");
            }

            $obj = new MidiaUpload();
            // Se ja existe uma arte, atualiza o mesmo registro (quantidade = 1).
            $atual = $this->localizarArte($debug);
            if ($atual) {
                $obj->setCodArquivo($atual->getCodArquivo());
                $obj->getMidiaUploadById($debug);
            }
            $obj->setLocalMidia(self::LOCAL_MIDIA_ARTE);
            $obj->setTipoArquivo(self::LOCAL_MIDIA_ARTE);

            $objUpload = new GerenciarMidiaUploadAC();
            $obj = $objUpload->executarUpload($obj, $fws_, $debug);
            $obj->salvar();
            $ok = true;
        } catch (OKException $ex) {
            $ok = true;
            $e = $ex;
        } catch (Exception $ex) {
            $e = $ex;
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $e ? $e->getMessage() : '',
        ));
    }

    /** Serve a arte atual inline (para o preview <img> da tela). */
    public function baixarArte($fws_, $debug = false)
    {
        $arte = $this->localizarArte($debug);
        if (!$arte) {
            throw new Exception("Nenhuma arte enviada para este evento.");
        }
        $real = $this->resolverCaminhoArte($arte);
        if ($real === null) {
            throw new Exception("Arquivo da arte n&atilde;o encontrado no servidor.");
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($ext == 'png' ? 'image/png' : 'image/jpeg'));
        header('Content-Length: ' . filesize($real));
        header('Cache-Control: no-cache');
        readfile($real);
        exit;
    }

    /**
     * Salva os campos e cria/atualiza o post "curso" no WordPress como
     * RASCUNHO (com a arte como imagem destacada). Nao publica no site:
     * a publicacao final e do administrador, no WP-admin.
     */
    public function publicarNoWordPress($fws_, $debug = false)
    {
        $ok = false;
        $e = null;
        $linkRevisao = '';
        try {
            $obj = $this->montarObj($fws_, $debug);
            $obj->gravar($debug);

            $wp = $this->criarClienteWp($obj);

            // Arte -> biblioteca de midia do WP (uma vez; re-envio sob demanda).
            $arte = $this->localizarArte($debug);
            $wpMediaId = (int) $obj->getWpMediaId();
            $reenviarArte = isset($fws_['reenviarArte']) && $fws_['reenviarArte'] == '1';
            if ($arte && ($wpMediaId <= 0 || $reenviarArte)) {
                $real = $this->resolverCaminhoArte($arte);
                if ($real !== null) {
                    $resposta = $wp->enviarMidia($real, $arte->getArquivoGerado());
                    $erro = $wp->descreverErro($resposta);
                    if ($erro != '') {
                        throw new Exception("Falha ao enviar a arte: " . $erro);
                    }
                    $wpMediaId = (int) $resposta['corpo']['id'];
                    $obj->setWpMediaId($wpMediaId);
                    $obj->setCodArquivoArte($arte->getCodArquivo());
                }
            }

            $acf = $obj->getCamposAcf();
            $tituloPost = trim(strtok($acf['titulo'], "\r\n"));
            $payload = array(
                'title'      => $tituloPost != '' ? $tituloPost : 'Evento ' . $obj->getCodEvento(),
                'categories' => array(HutecWpClient::CATEGORIA_EVENTOS_ABERTOS),
                'acf'        => $acf,
            );
            if ($wpMediaId > 0) {
                $payload['featured_media'] = $wpMediaId;
            }

            $wpPostId = (int) $obj->getWpPostId();
            if ($wpPostId > 0) {
                $resposta = $wp->atualizarCurso($wpPostId, $payload);
                if ($resposta['http'] == 404) {
                    // Post apagado no WP: recomeca como rascunho novo.
                    $wpPostId = 0;
                }
            }
            if ($wpPostId <= 0) {
                $resposta = $wp->criarCurso($payload);
            }
            $erro = $wp->descreverErro($resposta);
            if ($erro != '') {
                throw new Exception($erro);
            }

            $wpPostId = (int) $resposta['corpo']['id'];
            $obj->setWpPostId($wpPostId);
            $obj->setStatusPublicacao('ENVIADO_WP');
            // Espelha o status real do post — um post ja publicado continua
            // publicado no update; um novo nasce rascunho.
            $obj->setWpStatus(isset($resposta['corpo']['status']) ? $resposta['corpo']['status'] : 'draft');
            $obj->gravar($debug);

            $linkRevisao = $wp->getUrlBase() . '/wp-admin/post.php?post=' . $wpPostId . '&action=edit';
            $ok = true;
            $mensagem = "Enviado para o site da HUTEC como RASCUNHO (post " . $wpPostId . "). "
                . "O administrador revisa e publica no WP-admin.";
        } catch (Exception $ex) {
            $e = $ex;
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $ok ? $mensagem : ($e ? $e->getMessage() : ''),
            'linkRevisao'   => $linkRevisao,
        ));
    }

    /**
     * Testa a credencial do WordPress configurado (sem publicar nada).
     * Chama um endpoint que exige autenticacao: 200 = credencial boa.
     */
    public function testarConexao($fws_, $debug = false)
    {
        $ok = false;
        $mensagem = '';
        try {
            $url  = isset($fws_['wp_url']) ? trim(stripslashes($fws_['wp_url'])) : '';
            $user = isset($fws_['wp_user']) ? trim(stripslashes($fws_['wp_user'])) : '';
            $pass = isset($fws_['wp_app_pass']) ? trim(stripslashes($fws_['wp_app_pass'])) : '';

            // Senha em branco no form = usar a que ja esta guardada (cifrada).
            if ($pass == '') {
                $obj = new EventoDivulgacao();
                $obj->carregarPorEvento($debug);
                if ($obj->getWpAppPass() != '') {
                    $pass = CriptografiaHelper::descriptografar(stripslashes($obj->getWpAppPass()));
                }
                if ($url == '' && $obj->getWpUrl() != '') {
                    $url = stripslashes($obj->getWpUrl());
                }
                if ($user == '' && $obj->getWpUser() != '') {
                    $user = stripslashes($obj->getWpUser());
                }
            }

            $wp = new HutecWpClient($url, $user, $pass);
            $resposta = $wp->testarCredencial();
            if ($resposta['http'] == 200) {
                $ok = true;
                $nome = isset($resposta['corpo']['name']) ? $resposta['corpo']['name'] : '';
                $mensagem = "Conex&atilde;o OK com " . $wp->getUrlBase()
                    . ($nome != '' ? " (usu&aacute;rio: " . htmlspecialchars($nome) . ")" : "") . ".";
            } elseif ($resposta['http'] == 401 || $resposta['http'] == 403) {
                $mensagem = "Usu&aacute;rio ou senha de aplicativo recusados por " . $wp->getUrlBase()
                    . " (HTTP " . $resposta['http'] . ").";
            } else {
                $mensagem = $wp->descreverErro($resposta);
            }
        } catch (Exception $ex) {
            $mensagem = $ex->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $mensagem));
    }

    /**
     * Publica / despublica o post no WordPress (status publish x draft), sem
     * reenviar o conteudo. So faz sentido depois de um envio.
     */
    public function alterarStatusWp($fws_, $debug = false)
    {
        $ok = false;
        $mensagem = '';
        $novoStatus = '';
        try {
            $obj = new EventoDivulgacao();
            if (!$obj->carregarPorEvento($debug) || (int) $obj->getWpPostId() <= 0) {
                throw new Exception("Este evento ainda n&atilde;o foi enviado ao WordPress.");
            }
            $novoStatus = (isset($fws_['wp_novo_status']) && $fws_['wp_novo_status'] == 'publish')
                ? 'publish' : 'draft';

            $wp = $this->criarClienteWp($obj);
            $resposta = $wp->atualizarCurso($obj->getWpPostId(), array('status' => $novoStatus));
            $erro = $wp->descreverErro($resposta);
            if ($erro != '') {
                throw new Exception($erro);
            }

            $obj->setWpStatus($novoStatus);
            $obj->gravar($debug);
            $ok = true;
            $mensagem = $novoStatus == 'publish'
                ? "P&aacute;gina PUBLICADA no WordPress."
                : "P&aacute;gina DESPUBLICADA no WordPress (voltou a rascunho).";
        } catch (Exception $ex) {
            $mensagem = $ex->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $mensagem,
            'wpStatus'      => $ok ? $novoStatus : '',
        ));
    }

    /**
     * HTML da previa da pagina do evento — o mesmo bloco que o inscrito ve no
     * InscriTec. Recebe os campos do form (previa do que esta na tela, salvo
     * ou nao).
     */
    public function preview($fws_, $debug = false)
    {
        $arte = $this->localizarArte($debug);
        $urlArte = $arte
            ? WB_URL_COMMAND . '&c=GerenciarEventoDivulgacao&m=baixarArte&_submit_check=1&t=' . uniqid()
            : '';

        $v = array();
        foreach (array_merge($this->camposConteudo, $this->camposLocais) as $campo) {
            $v[$campo] = isset($fws_[$campo]) ? stripslashes($fws_[$campo]) : '';
        }

        $blocos = array(
            'periodo_de_inscricoes' => 'Inscri&ccedil;&otilde;es',
            'periodo_do_curso'      => 'Quando',
            'local'                 => 'Onde',
            'carga_horaria'         => 'Carga hor&aacute;ria',
            'vagas'                 => 'Vagas',
            'publico_alvo'          => 'P&uacute;blico-alvo',
        );
        ?>
        <div class="ed-preview" style="max-width:900px; margin:0 auto; font-family:inherit; color:#333;">
            <?php if ($urlArte != '') { ?>
                <div style="text-align:center; margin-bottom:18px;">
                    <img src="<?php echo $urlArte ?>" alt="" style="max-width:360px; width:100%; border-radius:10px;">
                </div>
            <?php } ?>
            <h2 style="text-align:center; font-size:20px; line-height:1.35; margin:0 0 18px;">
                <?php echo nl2br(htmlspecialchars($v['titulo'])) ?>
            </h2>
            <?php if (trim($v['tags']) != '') { ?>
                <div style="text-align:center; margin-bottom:16px;">
                    <?php foreach (array_filter(array_map('trim', explode(',', $v['tags']))) as $tag) { ?>
                        <span style="display:inline-block; background:#eef4f8; color:#2980b9; border-radius:20px; padding:3px 12px; font-size:12px; margin:2px;">
                            <?php echo htmlspecialchars($tag) ?>
                        </span>
                    <?php } ?>
                </div>
            <?php } ?>

            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
                <?php foreach ($blocos as $campo => $rotulo) {
                    if (trim(strip_tags($v[$campo])) == '') { continue; } ?>
                    <div style="flex:1 1 240px; background:#f7f9fa; border:1px solid #eceff1; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#8a959c; margin-bottom:4px;"><?php echo $rotulo ?></div>
                        <div style="font-size:14px;"><?php echo $v[$campo] ?></div>
                    </div>
                <?php } ?>
            </div>

            <?php
            $secoes = array(
                'descricao'          => 'Sobre o evento',
                'investimento'       => 'Investimento',
                'programacao'        => 'Programa&ccedil;&atilde;o',
                'outras_informacoes' => 'Outras informa&ccedil;&otilde;es',
            );
            foreach ($secoes as $campo => $rotulo) {
                if (trim(strip_tags($v[$campo])) == '') { continue; } ?>
                <div style="margin-bottom:22px;">
                    <h3 style="font-size:16px; border-bottom:2px solid #eceff1; padding-bottom:6px; margin:0 0 10px;"><?php echo $rotulo ?></h3>
                    <div style="font-size:14px; line-height:1.6;"><?php echo $v[$campo] ?></div>
                </div>
            <?php } ?>

            <?php if (trim($v['link_inscricoes']) != '') { ?>
                <div style="text-align:center; margin:26px 0 10px;">
                    <a href="<?php echo htmlspecialchars($v['link_inscricoes']) ?>" target="_blank" rel="noopener"
                       style="display:inline-block; background:#2d6a4f; color:#fff; text-decoration:none; border-radius:8px; padding:12px 34px; font-size:15px;">
                        Inscreva-se
                    </a>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    /** Cliente WP do evento (credencial propria cifrada ou fallback do env.php). */
    private function criarClienteWp(EventoDivulgacao $obj)
    {
        return new HutecWpClient(
            $obj->getWpUrl() ? stripslashes($obj->getWpUrl()) : '',
            $obj->getWpUser() ? stripslashes($obj->getWpUser()) : '',
            $obj->getWpAppPass() ? CriptografiaHelper::descriptografar(stripslashes($obj->getWpAppPass())) : ''
        );
    }

    /**
     * Sets explicitos campo a campo — NAO usar setAll($fws_) aqui: o setAll
     * aborta no campo _submit_check do form e deixa o objeto zerado (armadilha
     * documentada na memoria do projeto).
     */
    private function montarObj($fws_, $debug = false)
    {
        $obj = new EventoDivulgacao();
        $obj->carregarPorEvento($debug);
        foreach (array_merge($this->camposConteudo, $this->camposLocais) as $campo) {
            $valor = isset($fws_[$campo]) ? $fws_[$campo] : '';
            $valor = $this->codificarSuplementares($valor);
            $metodo = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $campo)));
            $obj->$metodo($valor != '' ? stripslashes($valor) : NULL);
        }

        // Conteudo ativo/inativo no InscriTec.
        if (isset($fws_['status_conteudo'])) {
            $obj->setStatus($fws_['status_conteudo'] == 'INATIVADA' ? 'INATIVADA' : 'ATIVADA');
        }

        // Config do WordPress de destino (por evento). A senha de aplicativo so
        // e trocada quando o form manda uma nova — campo em branco mantem a atual.
        if (isset($fws_['wp_url'])) {
            $obj->setWpUrl(trim($fws_['wp_url']) != '' ? stripslashes(trim($fws_['wp_url'])) : NULL);
        }
        if (isset($fws_['wp_user'])) {
            $obj->setWpUser(trim($fws_['wp_user']) != '' ? stripslashes(trim($fws_['wp_user'])) : NULL);
        }
        if (isset($fws_['wp_app_pass']) && trim($fws_['wp_app_pass']) != '') {
            // Nunca em claro no banco: AES-256 com chave do env.php (fora do BD).
            $obj->setWpAppPass(CriptografiaHelper::criptografar(stripslashes(trim($fws_['wp_app_pass']))));
        }
        return $obj;
    }

    /**
     * Converte caracteres fora do BMP (emoji etc., 4 bytes em UTF-8) para
     * entidades HTML numericas. A conexao e o banco do InscriTec sao utf8 de
     * 3 bytes — um emoji cru TRUNCA a coluna no primeiro emoji. Como o destino
     * e HTML, a entidade renderiza identico no site.
     */
    private function codificarSuplementares($texto)
    {
        if ($texto == '') {
            return $texto;
        }
        return preg_replace_callback('/[\x{10000}-\x{10FFFF}]/u', function ($m) {
            $cp = hexdec(bin2hex(mb_convert_encoding($m[0], 'UTF-32BE', 'UTF-8')));
            return '&#' . $cp . ';';
        }, $texto);
    }

    /** Registro da arte (tab_arquivo, tipo ARTE-DIVULGACAO) ou null. */
    private function localizarArte($debug = false)
    {
        $obj = new MidiaUpload();
        $obj->setArrayTipoArquivo(array(self::LOCAL_MIDIA_ARTE));
        $lista = $obj->carregarListaMidiasUploadByMidias($debug);
        if (!$lista) {
            return null;
        }
        return $lista[0];
    }

    /**
     * Caminho fisico da arte, confinado em logn/eventos/{COD_EVENTO}/ (anti
     * path-traversal — mesmo criterio do GerenciarMidiaUploadAC).
     */
    private function resolverCaminhoArte(MidiaUpload $obj)
    {
        if ($obj->getLocalMidia() == '') {
            $obj->setLocalMidia($obj->getTipoArquivo());
        }
        $nomeArquivo = basename($obj->getArquivoGerado());
        if ($nomeArquivo === '' || $nomeArquivo === '.' || $nomeArquivo === '..') {
            return null;
        }
        $caminho = rtrim($obj->getMidiaCaminho(), '/\\') . DIRECTORY_SEPARATOR . $nomeArquivo;
        $real = realpath($caminho);
        $baseReal = realpath(WB_PATH_LOG);
        if ($real === false || $baseReal === false
            || strpos($real, $baseReal . DIRECTORY_SEPARATOR) !== 0
            || !is_file($real)
        ) {
            return null;
        }
        return $real;
    }
}
