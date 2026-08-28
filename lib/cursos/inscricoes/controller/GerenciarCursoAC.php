<?php

class GerenciarCursoAC {

    /** Cadastrar curso e' montagem: coordenador do EAD ou ADM_GERAL. */
    function __construct() {
        FWA_exigirPermissao(FWA_permissoesCoordenarEAD());
    }

    public function carregarCurso($fws_, $debug = false) {
        $obj = new Curso();
        $obj->setCodCurso($fws_['codCurso']);
        $obj->getCursoById($debug);
        return $obj;
    }

    public function editarCurso($fws_, $debug = false) {
        $obj = $this->carregarCurso($fws_, $debug);
        echo json_encode(array(
            'statusMessage'         => true,
            'codCurso'              => $obj->getCodCurso(),
            'titulo'                => $obj->getTitulo(),
            'descricao'             => $obj->getDescricao(),
            'ementa'                => $obj->getEmenta(),
            'cargaHoraria'          => $obj->getCargaHoraria(),
            'capaUrl'               => $obj->getCapaUrl(),
            'dtLiberacao'           => $obj->getDtLiberacao(),
            'dtEncerramento'        => $obj->getDtEncerramento(),
            'horaInicio'            => $obj->getHoraInicio(),
            'horaFim'               => $obj->getHoraFim(),
            'ordem'                 => $obj->getOrdem(),
            'status'                => $obj->getStatus(),
            'codPerfil'             => $obj->getCodPerfil(),
            'exibirPara'            => $obj->getExibirPara() ?: 'TODOS',
            'filtroStatus'          => $obj->getFiltroStatus(),
            'filtroStatusProcesso'  => $obj->getFiltroStatusProcesso(),
            'codOficinas'           => (new OficinaDAO())->getCodOficinasByCurso(COD_EVENTO, $obj->getCodCurso(), $debug),
        ));
        exit();
    }

    public function salvarCurso($fws_, $debug = false) {
        $ok = false;
        $codCurso = '';
        $message = '';
        try {
            $obj = $this->carregarCurso($fws_, $debug);
            $isForm = true;
            $obj->setAll($fws_, $isForm);
            $obj->setCodEvento(COD_EVENTO);
            if (!$obj->getStatus()) $obj->setStatus('ATIVADA');
            if (!$obj->getOrdem()) $obj->setOrdem(0);
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $codCurso = $obj->getCodCurso();
            $message = $e->getMessage();
            // Reconcilia o vinculo curso <-> oficinas (grava cod_curso em tab_opcoes_oficinas)
            $oficinasSel = isset($fws_['codOficinas']) ? $fws_['codOficinas'] : array();
            (new OficinaDAO())->vincularCursoNasOficinas(COD_EVENTO, $codCurso, $oficinasSel, $debug);
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => tratarJson($message),
            'codCurso'      => $codCurso,
        ));
    }

    public function excluirCurso($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $obj = new Curso();
            $obj->setCodCurso($fws_['codCurso']);
            $obj->excluir($debug);
        } catch (OKException $e) {
            $ok = true;
            $message = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => tratarJson($message),
            'codCurso'      => $fws_['codCurso'],
        ));
    }

    public function carregarTabelaCurso($fws_, $debug = false) {
        $obj = new Curso();
        try {
            $obj->carregarLista(false, $debug);
            $lista = $obj->getListaCurso();
            if (!$lista) {
                echo "<tr><td colspan='8' style='text-align:center; padding:30px; color:#999;'>Nenhum curso cadastrado ainda. Clique em <strong>+ Novo Curso</strong> para começar.</td></tr>";
                return;
            }
            $contadores = $obj->contarItens($debug);
            // Tipos "folha" (nao-capitulo) exibidos como badges, na ordem de relevancia.
            $labelTipo = array(
                'VIDEO'      => array('Vídeos',      'fa-play-circle'),
                'PDF'        => array('PDFs',        'fa-file-pdf-o'),
                'LINK'       => array('Links',       'fa-link'),
                'FORMULARIO' => array('Formulários', 'fa-list-alt'),
                'TEXTO'      => array('Textos',      'fa-file-text-o'),
                'SLIDES'     => array('Slides',      'fa-desktop'),
                'MEET'       => array('Meet',        'fa-video-camera'),
                'TAREFA'     => array('Tarefas',     'fa-tasks'),
            );
            $ii = 0;
            foreach ($lista as $c) {
                $ii++;
                $statusDot = ($c->getStatus() == 'ATIVADA') ? 'dot-green' : 'dot-red';
                $statusTxt = ($c->getStatus() == 'ATIVADA') ? 'Ativo' : 'Inativo';
                $hi = $c->getHoraInicio() ? substr($c->getHoraInicio(), 0, 5) : '--';
                $hf = $c->getHoraFim() ? substr($c->getHoraFim(), 0, 5) : '--';
                $datas = '';
                if ($c->getDtLiberacao()) $datas .= htmlspecialchars($c->getDtLiberacao()) . ' ';
                if ($c->getDtEncerramento()) $datas .= 'até ' . htmlspecialchars($c->getDtEncerramento());
                if (!$datas) $datas = '<span style="color:#999;">sem limite</span>';
                $ct     = isset($contadores[(int) $c->getCodCurso()]) ? $contadores[(int) $c->getCodCurso()] : array('total' => 0, 'tipos' => array());
                $tipos  = $ct['tipos'];
                $nCap   = isset($tipos['CAPITULO']) ? (int) $tipos['CAPITULO'] : 0;
                $nItens = (int) $ct['total'] - $nCap;
                ?>
                <tr id="<?php echo (int) $c->getCodCurso(); ?>">
                    <td>
                        <a class="btn-acao" href="index.php?content=view/inscricao/inscricao_congresso_curso.php&preview=1&cod_curso=<?php echo (int) $c->getCodCurso(); ?>" target="_blank" title="Pré-visualizar (assistir como aluno)"><i class="fa fa-eye"></i></a>
                        <span class="editar btn-acao" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="excluir btn-acao btn-acao-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>
                    <td><?php echo (int) $c->getOrdem(); ?></td>
                    <td class="col-nome"><?php echo htmlspecialchars($c->getTitulo()); ?></td>
                    <td>
                        <?php if ($ct['total'] == 0) { ?>
                            <span style="color:#bbb;">sem conteúdo</span>
                        <?php } else { ?>
                            <div class="conteudo-wrap">
                                <span class="conteudo-resumo"><strong><?php echo $nCap; ?></strong> cap. &middot; <strong><?php echo $nItens; ?></strong> itens</span>
                                <?php foreach ($labelTipo as $tp => $meta) { if (empty($tipos[$tp])) continue; ?>
                                    <span class="badge-conteudo" title="<?php echo $meta[0]; ?>"><i class="fa <?php echo $meta[1]; ?>"></i> <?php echo (int) $tipos[$tp]; ?></span>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </td>
                    <td><?php echo $hi . ' - ' . $hf; ?></td>
                    <td><?php echo $datas; ?></td>
                    <td><?php echo htmlspecialchars($c->getCargaHoraria() ?: '—'); ?></td>
                    <td><span class="badge-tipo"><span class="dot <?php echo $statusDot; ?>"></span><?php echo $statusTxt; ?></span></td>
                </tr>
                <?php
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='8' style='text-align:center; padding:30px; color:#c0392b;'>" . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
    }
}
