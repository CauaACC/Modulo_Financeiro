<?php

/**
 * Equipe padrao do gestor: qual evento serve de molde para os projetos novos.
 *
 * Nao tem VO nem tabela propria -- guarda so uma coluna em sis_gestor
 * (cod_evento_modelo) e consulta a equipe do evento apontado. Por isso fala
 * direto com a conexao, sem DAO gerado.
 */
class EquipePadraoGestor {

    private $conex;

    function __construct() {
        ConexaoDB::getInstance();
        $this->conex = ConexaoDB::getConexaoDB();
    }

    /** Gestores ativos, com o modelo atual de cada um. */
    public function listarGestores($debug = false) {
        $sql = "SELECT g.cod_gestor, g.nome_gestor, g.cod_evento_modelo,
                       e.titulo AS titulo_modelo
                  FROM sis_gestor g
                  LEFT JOIN tab_eventos e ON e.cod_evento = g.cod_evento_modelo
                 WHERE g.status = 'ATIVADO'
                 ORDER BY g.nome_gestor";
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        $lista = array();
        while ($rs && !$rs->EOF) {
            $lista[] = array(
                'cod_gestor'        => (int) $rs->fields['cod_gestor'],
                'nome_gestor'       => $rs->fields['nome_gestor'],
                'cod_evento_modelo' => $rs->fields['cod_evento_modelo'],
                'titulo_modelo'     => $rs->fields['titulo_modelo'],
            );
            $rs->MoveNext();
        }
        return $lista;
    }

    /**
     * Eventos ATIVADOS do gestor, mais novos primeiro.
     *
     * O vinculo entre sis_gestor e tab_eventos e' por NOME (tab_eventos.gestor
     * guarda texto, nao codigo), e a caixa varia entre 'HUTEC' e 'hutec' --
     * por isso a comparacao com UPPER dos dois lados.
     */
    public function listarEventosDoGestor($nomeGestor, $debug = false) {
        $sql = "SELECT cod_evento, titulo, data_ini
                  FROM tab_eventos
                 WHERE UPPER(gestor) = UPPER(" . $this->conex->qstr($nomeGestor) . ")
                   AND status = 'ATIVADO'
                 ORDER BY cod_evento DESC";
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        $lista = array();
        while ($rs && !$rs->EOF) {
            $lista[] = array(
                'cod_evento' => (int) $rs->fields['cod_evento'],
                'titulo'     => $rs->fields['titulo'],
                'data_ini'   => $rs->fields['data_ini'],
            );
            $rs->MoveNext();
        }
        return $lista;
    }

    /**
     * Quem tem acesso ATIVADO ao evento, com quantos grupos cada um.
     *
     * Identifica pelo login (e-mail): nome_usuario e' NULL em boa parte da base.
     */
    public function listarEquipeDoEvento($codEvento, $debug = false) {
        $codEvento = (int) $codEvento;
        $sql = "SELECT u.cod_usuario, u.nome_usuario, u.login,
                       (SELECT COUNT(*) FROM sis_ligacao_usuarios_grupos g
                         WHERE g.cod_evento = " . $codEvento . " AND g.cod_usuario = u.cod_usuario) AS qtd_grupos
                  FROM sis_ligacao_usuarios_eventos v
                  JOIN sis_usuarios u ON u.cod_usuario = v.cod_usuario
                 WHERE v.cod_evento = " . $codEvento . "
                   AND v.status = 'ATIVADA'
                 ORDER BY u.login";
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        $lista = array();
        while ($rs && !$rs->EOF) {
            $lista[] = array(
                'cod_usuario' => (int) $rs->fields['cod_usuario'],
                'nome'        => $rs->fields['nome_usuario'],
                'login'       => $rs->fields['login'],
                'qtd_grupos'  => (int) $rs->fields['qtd_grupos'],
            );
            $rs->MoveNext();
        }
        return $lista;
    }

    /** Grava (ou limpa, com NULL) o evento modelo do gestor. */
    public function salvarModelo($codGestor, $codEvento, $debug = false) {
        $codGestor = (int) $codGestor;
        $valor = ($codEvento === null) ? "NULL" : (int) $codEvento;
        $sql = "UPDATE sis_gestor SET cod_evento_modelo = " . $valor
             . " WHERE cod_gestor = " . $codGestor;
        $this->conex->debug = $debug;
        $this->conex->Execute($sql);
        return true;
    }
}
