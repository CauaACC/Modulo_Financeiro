<?php

/**
 * DAO do Modelo da FRENTE do certificado.
 * Todo o SQL/acesso ao banco mora aqui. Tabela: tab_certificado_modelo.
 */
class CertificadoModeloDAO extends DAO
{
    public function __construct()
    {
        $this->tableName = "tab_certificado_modelo";
        parent::__construct();
    }

    public function getByCod(CertificadoModelo $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=%d AND cod_certificado_modelo=%d LIMIT 1',
                (int) $obj->getCodEvento(), (int) $obj->getCodCertificadoModelo()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            $obj->setAll($rs->fields);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function listar(CertificadoModelo $obj, $debug = false)
    {
        try {
            $sql = sprintf(
                'SELECT cod_certificado_modelo, titulo, arte_fundo, dat_incl, dat_alter '
                . 'FROM ' . $this->tableName . ' '
                . "WHERE cod_evento=%d AND status='ATIVADA' ORDER BY titulo",
                (int) $obj->getCodEvento()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs) {
                return array();
            }
            $lista = array();
            while (!$rs->EOF) {
                $lista[] = $rs->fields;
                $rs->MoveNext();
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Resolucao em 3 niveis: perfil -> evento -> nenhum.
     * Devolve cod_certificado_modelo ou false.
     *
     * So considera modelo ATIVADA: se o modelo apontado foi excluido, cai para
     * o nivel seguinte em vez de emitir certificado sem layout.
     */
    public function resolverModelo(CertificadoModelo $obj, $codPerfil = null, $debug = false)
    {
        try {
            $codEvento = (int) $obj->getCodEvento();
            $this->conex->debug = $debug;

            // 1) Excecao por perfil
            if ((int) $codPerfil > 0) {
                $sql = sprintf(
                    'SELECT m.cod_certificado_modelo '
                    . 'FROM tab_opcoes_perfis p '
                    . 'INNER JOIN ' . $this->tableName . " m ON m.cod_certificado_modelo = p.modelo_certificado AND m.status='ATIVADA' "
                    . 'WHERE p.cod_perfil=%d AND p.modelo_certificado IS NOT NULL LIMIT 1',
                    (int) $codPerfil
                );
                $rs = $this->conex->Execute($sql);
                if ($rs && !$rs->EOF) {
                    return $rs->fields['cod_certificado_modelo'];
                }
            }

            // 2) Padrao do evento
            $sql = sprintf(
                'SELECT m.cod_certificado_modelo '
                . 'FROM tab_eventos_configuracao c '
                . 'INNER JOIN ' . $this->tableName . " m ON m.cod_certificado_modelo = c.modelo_certificado AND m.status='ATIVADA' "
                . 'WHERE c.cod_evento=%d AND c.modelo_certificado IS NOT NULL LIMIT 1',
                $codEvento
            );
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                return $rs->fields['cod_certificado_modelo'];
            }

            // 3) Nada configurado: chamador mantem o comportamento de hoje.
            return false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(CertificadoModelo $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM $this->tableName WHERE cod_certificado_modelo=0 ";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodCertificadoModelo($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * UPDATE direcionado, coluna a coluna.
     *
     * Nao usa GetUpdateSQL: o objeto vem do POST do editor e um POST parcial
     * gravaria colunas vazias (foi assim que o status do verso ficou em branco
     * e os registros "sumiram" das consultas). Aqui os getters do VO ja
     * devolvem valor validado, entao o UPDATE e sempre completo e coerente.
     */
    public function update(CertificadoModelo $obj, $debug = false)
    {
        try {
            $q = array($this->conex, 'qstr');

            $sets = array(
                'titulo='               . call_user_func($q, $obj->getTitulo()),
                'arte_fundo='           . call_user_func($q, $obj->getArteFundo()),

                'texto_topo='           . (int) $obj->getTextoTopo(),
                'texto_esquerda='       . (int) $obj->getTextoEsquerda(),
                'texto_largura='        . (int) $obj->getTextoLargura(),
                'texto_fonte_tamanho='  . (int) $obj->getTextoFonteTamanho(),
                'texto_altura_linha='   . (int) $obj->getTextoAlturaLinha(),
                'texto_alinhamento='    . call_user_func($q, $obj->getTextoAlinhamento()),

                'ass1_topo='            . (int) $obj->getAss1Topo(),
                'ass1_esquerda='        . (int) $obj->getAss1Esquerda(),
                'ass1_largura='         . (int) $obj->getAss1Largura(),
                'ass2_topo='            . (int) $obj->getAss2Topo(),
                'ass2_esquerda='        . (int) $obj->getAss2Esquerda(),
                'ass2_largura='         . (int) $obj->getAss2Largura(),

                'selo_topo='            . (int) $obj->getSeloTopo(),
                'selo_esquerda='        . (int) $obj->getSeloEsquerda(),
                'selo_largura='         . (int) $obj->getSeloLargura(),

                'qr_topo='              . (int) $obj->getQrTopo(),
                'qr_esquerda='          . (int) $obj->getQrEsquerda(),
                'qr_largura='           . (int) $obj->getQrLargura(),

                'data_topo='            . (int) $obj->getDataTopo(),
                'data_esquerda='        . (int) $obj->getDataEsquerda(),
                'data_largura='         . (int) $obj->getDataLargura(),
                'data_fonte_tamanho='   . (int) $obj->getDataFonteTamanho(),

                'versao='               . (((int) $obj->getVersao()) + 1),
                'dat_alter='            . call_user_func($q, date('Y-m-d H:i:s')),
                'res_alter='            . (int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0),
                'ip_alter='             . call_user_func($q, $_SERVER['REMOTE_ADDR']),
            );

            $sql = 'UPDATE ' . $this->tableName . ' SET ' . implode(', ', $sets)
                . ' WHERE cod_evento=' . (int) $obj->getCodEvento()
                . ' AND cod_certificado_modelo=' . (int) $obj->getCodCertificadoModelo();

            $this->conex->debug = $debug;
            return $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Exclusao logica: preserva a linha para quem ainda aponta para ela. */
    public function desativar(CertificadoModelo $obj, $debug = false)
    {
        try {
            $q = array($this->conex, 'qstr');
            $sql = 'UPDATE ' . $this->tableName
                . " SET status='EXCLUIDA'"
                . ', dat_alter=' . call_user_func($q, date('Y-m-d H:i:s'))
                . ', res_alter=' . (int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0)
                . ', ip_alter='  . call_user_func($q, $_SERVER['REMOTE_ADDR'])
                . ' WHERE cod_evento=' . (int) $obj->getCodEvento()
                . ' AND cod_certificado_modelo=' . (int) $obj->getCodCertificadoModelo();
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
