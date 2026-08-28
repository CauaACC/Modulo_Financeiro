<?php

class enumSistema {

    // Constantes fixas (estaveis)
    const SIS_INCLUIR_EVENTO                ="0";
    const SIS_GERENCIAR_EVENTO              ="1";
    const SIS_GERENCIAR_AREAS_TEMATICAS     ="2";
    const SIS_AVALIACAO_TRABALHO            ="3";
    const SIS_GERENCIAR_AVALIACAO_TRABALHOS ="4";
    const SIS_AVALIACAO_TRABALHO_PREMIADO   ="5";
    const SIS_CONTROLE_CONFIGURACAO         ="6";
    const SIS_CONTROLE_FINANCEIRO           ="7";
    const SIS_GERENCIAR_ATIVIDADES          ="10";
    const SIS_CREDENCIAMENTO_RECEPCAO       ="11";
    const SIS_CONSULTAR_INSCRITOS           ="12";
    const SIS_GERENCIAR_INSCRITO            ="13";
    // 30 = "GERENCIAR CERTIFICADO" em sis_sistemas. Ficou "13" por muito tempo,
    // que e "Gerenciar Inscrito" -- ou seja, o direito 30 existia no banco, podia
    // ser concedido, e nao abria porta nenhuma. Onde o 13 tambem deve valer, ele
    // continua listado separadamente no gate.
    const SIS_GERENCIAR_CERTIFICADO         ="30";
    const SIS_CONTROLE_VOUCHER              ="14";
    const SIS_CONSULTAR_PAINEL_BORDO        ="15";
    const SIS_EMITIR_DOCUMENTOS             ="16";
    const SIS_LISTAR_TRABALHOS              ="17";
    const SIS_ASSOCIAR_PERFIS               ="18";
    const SIS_GERENCIAR_CONVIDADOS          ="19";
    const SIS_GERENCIAR_ADM_GERAL           ="999";
    const SIS_AVALIACAO_INSCRITO            ="22";
    const SIS_ENVIAR_MENSAGEM               ="23";
    const SIS_INSCRITO_APTO                 ="24";
    const SIS_LOGAR_COMO                    ="13";
    const SIS_PEDAGOGICO                    ="25";
    const SIS_CONTROLE_ACESSO               ="26";
    const SIS_CARGA_TRANSPARENCIA           ="27";
    const SIS_PEDAGOGICO_GERENTE            ="28";
    const SIS_PEDAGOGICO_SUPERVISOR         ="29";

    // Dinamicos (novos sistemas cadastrados via CRUD) - carregados do banco sob demanda
    private static $cache = null;

    public static function cod($enum) {
        if (defined('self::' . $enum)) {
            return constant('self::' . $enum);
        }
        if (self::$cache === null) {
            self::carregar();
        }
        return isset(self::$cache[$enum]) ? self::$cache[$enum] : null;
    }

    private static function carregar() {
        ConexaoDB::getInstance();
        $conex = ConexaoDB::getConexaoDB();
        $rs = $conex->Execute("SELECT cod_sistema, enum_sistema FROM sis_sistemas WHERE enum_sistema IS NOT NULL AND enum_sistema != ''");
        self::$cache = array();
        while ($rs && !$rs->EOF) {
            self::$cache[$rs->fields['enum_sistema']] = $rs->fields['cod_sistema'];
            $rs->MoveNext();
        }
    }
}
