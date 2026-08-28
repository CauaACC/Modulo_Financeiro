<?php
class GerenciarGruposSistemasAC
{
    function __construct()
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $this->EventoId = COD_EVENTO;
    }

    /* ================================================================
       GRUPOS
       ================================================================ */

    public function carregarTabelaGrupos($fws_, $debug = false)
    {
        try {
            $objGrupo = new Grupo();
            $lista = $objGrupo->getListaGrupos($debug);

            if (!$lista) {
                echo "<tr><td colspan='4'>Nenhum grupo cadastrado.</td></tr>";
                return;
            }

            foreach ($lista as $row) {
                $codGrupo      = intval($row['cod_grupo']);
                $nomeGrupo     = htmlspecialchars($row['nome_grupo']);
                $enumGrupo     = htmlspecialchars($row['enum_grupo']);
                $totalVinculos = intval($row['total_vinculos']);
                ?>
                <tr>
                    <td><?php echo $codGrupo; ?></td>
                    <td>
                        <span class="valor-nome" data-cod="<?php echo $codGrupo; ?>"><?php echo $nomeGrupo; ?></span>
                    </td>
                    <td><code style="font-size:11px;"><?php echo $enumGrupo; ?></code></td>
                    <td style="text-align:center;">
                        <a href="javascript:void(0);" onclick="editarGrupo(<?php echo $codGrupo; ?>, '<?php echo addslashes($nomeGrupo); ?>', '<?php echo addslashes($enumGrupo); ?>')" title="Editar" class="btn btn-xs btn-warning">
                            <i class="fa fa-pencil"></i>
                        </a>
                        <?php if ($totalVinculos == 0) { ?>
                        <a href="javascript:void(0);" onclick="excluirGrupo(<?php echo $codGrupo; ?>, '<?php echo addslashes($nomeGrupo); ?>')" title="Excluir" class="btn btn-xs btn-danger">
                            <i class="fa fa-trash"></i>
                        </a>
                        <?php } else { ?>
                        <span class="text-muted" title="N&atilde;o &eacute; poss&iacute;vel excluir: grupo em uso (<?php echo $totalVinculos; ?> v&iacute;nculo(s))">
                            <i class="fa fa-lock"></i>
                        </span>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>Erro: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
    }

    public function salvarGrupo($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $obj = new Grupo();
            $codGrupo = intval($fws_['cod_grupo']);
            if ($codGrupo > 0) {
                $obj->setCodGrupo($codGrupo);
                $obj->getGrupoById($debug);
            }
            $obj->setNomeGrupo(trim($fws_['nome_grupo']));
            $obj->setEnumGrupo(trim(isset($fws_['enum_grupo']) ? $fws_['enum_grupo'] : ''));
            if ($codGrupo == 0) {
                $obj->setCodGrupo(null);
                $obj->setCodEvento(0);
                $obj->setOrdem(0);
            }
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $MSG = $e->getMessage();
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $MSG
        ));
        exit();
    }

    public function excluirGrupo($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $obj = new Grupo();
            $obj->setCodGrupo(intval($fws_['cod_grupo']));
            $obj->excluir($debug);
        } catch (OKException $e) {
            $ok = true;
            $MSG = $e->getMessage();
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $MSG
        ));
        exit();
    }

    /* ================================================================
       SISTEMAS
       ================================================================ */

    public function carregarTabelaSistemas($fws_, $debug = false)
    {
        try {
            $objSistema = new Sistema();
            $lista = $objSistema->getListaSistemas($debug);

            if (!$lista) {
                echo "<tr><td colspan='4'>Nenhum sistema cadastrado.</td></tr>";
                return;
            }

            foreach ($lista as $row) {
                $codSistema     = intval($row['cod_sistema']);
                $nomeSistema    = htmlspecialchars($row['nome_sistema']);
                $enumSistema    = htmlspecialchars($row['enum_sistema']);
                $totalVinculos  = intval($row['total_vinculos']);
                ?>
                <tr>
                    <td><?php echo $codSistema; ?></td>
                    <td>
                        <span class="valor-nome-sis" data-cod="<?php echo $codSistema; ?>"><?php echo $nomeSistema; ?></span>
                    </td>
                    <td><code style="font-size:11px;"><?php echo $enumSistema; ?></code></td>
                    <td style="text-align:center;">
                        <a href="javascript:void(0);" onclick="editarSistema(<?php echo $codSistema; ?>, '<?php echo addslashes($nomeSistema); ?>', '<?php echo addslashes($enumSistema); ?>')" title="Editar" class="btn btn-xs btn-warning">
                            <i class="fa fa-pencil"></i>
                        </a>
                        <?php if ($totalVinculos == 0) { ?>
                        <a href="javascript:void(0);" onclick="excluirSistema(<?php echo $codSistema; ?>, '<?php echo addslashes($nomeSistema); ?>')" title="Excluir" class="btn btn-xs btn-danger">
                            <i class="fa fa-trash"></i>
                        </a>
                        <?php } else { ?>
                        <span class="text-muted" title="N&atilde;o &eacute; poss&iacute;vel excluir: sistema em uso (<?php echo $totalVinculos; ?> v&iacute;nculo(s))">
                            <i class="fa fa-lock"></i>
                        </span>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>Erro: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
    }

    public function salvarSistema($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $obj = new Sistema();
            $codSistema = intval($fws_['cod_sistema']);
            if ($codSistema > 0) {
                $obj->setCodSistema($codSistema);
                $obj->getSistemaById($debug);
            }
            $obj->setNomeSistema(trim($fws_['nome_sistema']));
            $obj->setEnumSistema(trim(isset($fws_['enum_sistema']) ? $fws_['enum_sistema'] : ''));
            if ($codSistema == 0) {
                $obj->setCodSistema(null);
            }
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $MSG = $e->getMessage();
            // Criar grupo correspondente ao novo sistema
            if ($codSistema == 0) {
                try {
                    $obj->criarGrupoCorrespondente($debug);
                } catch (Exception $ex) {
                    $MSG .= ' (Aviso grupo: ' . $ex->getMessage() . ')';
                }
            }
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $MSG
        ));
        exit();
    }

    public function excluirSistema($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $obj = new Sistema();
            $obj->setCodSistema(intval($fws_['cod_sistema']));
            $obj->excluir($debug);
        } catch (OKException $e) {
            $ok = true;
            $MSG = $e->getMessage();
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $MSG
        ));
        exit();
    }
}
