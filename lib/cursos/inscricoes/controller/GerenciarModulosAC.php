<?php
class GerenciarModulosAC
{
    function __construct()
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $this->EventoId = COD_EVENTO;
    }

    public function carregarTabelaModulos($fws_, $debug = false)
    {
        try {
            $codEvento = isset($fws_['cod_evento']) ? intval($fws_['cod_evento']) : intval(COD_EVENTO);

            $obj = new GrupoSistema();
            $lista = $obj->carregarListaModulosByEvento($codEvento, $debug);

            if (!$lista) {
                echo "<tr><td colspan='6'>Nenhum m&oacute;dulo configurado para este evento.</td></tr>";
                return;
            }

            $ii = 0;
            foreach ($lista as $row) {
                $ii++;
                $codGrupo    = intval($row['cod_grupo']);
                $codSistema  = intval($row['cod_sistema']);
                $codDireito  = intval($row['cod_direito']);
                $nomeGrupo   = htmlspecialchars($row['nome_grupo']);
                $nomeSistema = htmlspecialchars($row['nome_sistema']);
                $nomeDireito = htmlspecialchars($row['nome_direito']);
                ?>
                <tr>
                    <td class="text-muted"><?php echo $codGrupo; ?></td>
                    <td><?php echo $nomeGrupo; ?></td>
                    <td class="text-muted"><?php echo $codSistema; ?></td>
                    <td><?php echo $nomeSistema; ?></td>
                    <td><?php echo $nomeDireito; ?></td>
                    <td style="text-align:center;">
                        <a href="javascript:void(0);" onclick="excluirModulo(<?php echo $codGrupo; ?>, <?php echo $codSistema; ?>, <?php echo $codDireito; ?>)" title="Excluir" class="btn btn-xs btn-danger">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php
            }
            echo "<tr class='totalRow'><td colspan='6'>Total de registros: $ii</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='6'>";
            echo $e->getMessage() ? $e->getMessage() : "Erro ao carregar m&oacute;dulos.";
            echo "</td></tr>";
        }
    }

    public function adicionarModulo($fws_, $debug = false)
    {
        ob_clean();
        $ok = false;
        try {
            $codEvento  = intval($fws_['cod_evento']);
            $codGrupo   = intval($fws_['cod_grupo']);
            $codSistema = intval($fws_['cod_sistema']);
            $codDireito = intval($fws_['cod_direito']);

            if (!$codEvento || !$codGrupo || !$codSistema || !$codDireito) {
                throw new Exception("Todos os campos s&atilde;o obrigat&oacute;rios.");
            }

            $obj = new GrupoSistema();

            // Verificar duplicidade
            if ($obj->existeAssociacao($codEvento, $codGrupo, $codSistema, $codDireito, $debug)) {
                throw new Exception("Esta associa&ccedil;&atilde;o j&aacute; existe para este evento.");
            }

            $obj->setCodEvento($codEvento);
            $obj->setCodGrupo($codGrupo);
            $obj->setCodSistema($codSistema);
            $obj->setCodDireito($codDireito);
            $obj->inserir($debug);
            $ok = true;
            $MSG = 'Permiss&atilde;o adicionada com sucesso.';
        } catch (Exception $ex) {
            $MSG = $ex->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $MSG
        ));
        exit();
    }

    public function excluirModulo($fws_, $debug = false)
    {
        ob_clean();
        $ok = false;
        try {
            $codEvento  = intval($fws_['cod_evento']);
            $codGrupo   = intval($fws_['cod_grupo']);
            $codSistema = intval($fws_['cod_sistema']);
            $codDireito = intval($fws_['cod_direito']);

            if (!$codEvento || !$codGrupo || !$codSistema || !$codDireito) {
                throw new Exception("Dados insuficientes para exclus&atilde;o.");
            }

            $obj = new GrupoSistema();
            $obj->setCodEvento($codEvento);
            $obj->setCodGrupo($codGrupo);
            $obj->setCodSistema($codSistema);
            $obj->setCodDireito($codDireito);
            $obj->excluir($debug);
            $ok = true;
            $MSG = 'Permiss&atilde;o exclu&iacute;da com sucesso.';
        } catch (Exception $ex) {
            $MSG = $ex->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $MSG
        ));
        exit();
    }

    public function copiarModulosEvento($fws_, $debug = false)
    {
        ob_clean();
        $ok = false;
        try {
            $codEventoOrigem  = intval($fws_['cod_evento_origem']);
            $codEventoDestino = intval($fws_['cod_evento_destino']);
            $copiarGrupos  = isset($fws_['copiar_grupos'])  && $fws_['copiar_grupos']  == '1';
            $copiarModulos = isset($fws_['copiar_modulos']) && $fws_['copiar_modulos'] == '1';

            if (!$codEventoOrigem || !$codEventoDestino) {
                throw new Exception("Selecione os eventos de origem e destino.");
            }
            if ($codEventoOrigem == $codEventoDestino) {
                throw new Exception("Evento de origem e destino n&atilde;o podem ser iguais.");
            }
            if (!$copiarGrupos && !$copiarModulos) {
                throw new Exception("Selecione ao menos uma op&ccedil;&atilde;o para copiar.");
            }

            $obj = new GrupoSistema();
            $partes = array();

            // 1. Copiar grupos
            if ($copiarGrupos) {
                $listaGrupos = $obj->carregarGruposByEvento($codEventoOrigem, $debug);
                $gruposInseridos = 0;
                $gruposIgnorados = 0;

                if ($listaGrupos) {
                    foreach ($listaGrupos as $row) {
                        if ($obj->existeGrupoNoEvento($row['cod_grupo'], $codEventoDestino, $debug)) {
                            $gruposIgnorados++;
                        } else {
                            $obj->inserirGrupoNoEvento(
                                $row['cod_grupo'], $row['nome_grupo'], $codEventoDestino,
                                $row['enum_grupo'], $row['ordem'], $row['categoria'], $debug
                            );
                            $gruposInseridos++;
                        }
                    }
                }
                $partes[] = "Grupos: " . $gruposInseridos . " copiado(s), " . $gruposIgnorados . " j&aacute; existia(m)";
            }

            // 2. Copiar modulos (direitos)
            if ($copiarModulos) {
                $listaOrigem = $obj->carregarModulosByEvento($codEventoOrigem, $debug);
                $modulosInseridos = 0;
                $modulosIgnorados = 0;

                if ($listaOrigem) {
                    foreach ($listaOrigem as $row) {
                        if ($obj->existeAssociacao($codEventoDestino, $row['cod_grupo'], $row['cod_sistema'], $row['cod_direito'], $debug)) {
                            $modulosIgnorados++;
                        } else {
                            $objNovo = new GrupoSistema();
                            $objNovo->setCodEvento($codEventoDestino);
                            $objNovo->setCodGrupo($row['cod_grupo']);
                            $objNovo->setCodSistema($row['cod_sistema']);
                            $objNovo->setCodDireito($row['cod_direito']);
                            $objNovo->inserir($debug);
                            $modulosInseridos++;
                        }
                    }
                }
                $partes[] = "M&oacute;dulos: " . $modulosInseridos . " copiado(s), " . $modulosIgnorados . " j&aacute; existia(m)";
            }

            $ok = true;
            $msg = implode('. ', $partes) . '.';
        } catch (Exception $ex) {
            $msg = $ex->getMessage();
        }

        echo '{
            "statusMessage":"' . $ok . '",
            "message":"' . (isset($msg) ? addslashes($msg) : '') . '"
        }';
        exit();
    }

    public function carregarSelectEventos($fws_, $debug = false)
    {
        try {
            $obj = new GrupoSistema();
            $lista = $obj->carregarSelectEventos($debug);

            echo '<option value="">Selecione um evento</option>';
            if ($lista) {
                foreach ($lista as $row) {
                    // $selected = ($row['cod_evento'] == COD_EVENTO) ? ' selected' : '';
                    echo '<option value="' . $row['cod_evento'] . '"' . $selected . '>'
                        . $row['cod_evento'] . ' - ' . htmlspecialchars($row['titulo'])
                        . '</option>';
                }
            }
        } catch (Exception $e) {
            echo '<option value="">Erro ao carregar eventos</option>';
        }
    }

    public function carregarSelectGrupos($fws_, $debug = false)
    {
        try {
            $obj = new GrupoSistema();
            $lista = $obj->carregarSelectGrupos($debug);

            echo '<option value="">Selecione um grupo</option>';
            if ($lista) {
                foreach ($lista as $row) {
                    echo '<option value="' . $row['cod_grupo'] . '">'
                        . '[' . $row['cod_grupo'] . '] ' . htmlspecialchars($row['nome_grupo'])
                        . '</option>';
                }
            }
        } catch (Exception $e) {
            echo '<option value="">Erro ao carregar grupos</option>';
        }
    }

    public function carregarSelectSistemas($fws_, $debug = false)
    {
        try {
            $obj = new GrupoSistema();
            $lista = $obj->carregarSelectSistemas($debug);

            echo '<option value="">Selecione um sistema</option>';
            if ($lista) {
                foreach ($lista as $row) {
                    echo '<option value="' . $row['cod_sistema'] . '">'
                        . '[' . $row['cod_sistema'] . '] ' . htmlspecialchars($row['nome_sistema'])
                        . '</option>';
                }
            }
        } catch (Exception $e) {
            echo '<option value="">Erro ao carregar sistemas</option>';
        }
    }
}
