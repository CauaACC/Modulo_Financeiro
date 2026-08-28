<?php

class enumGrupo {

    // Constantes fixas (estaveis)
    const GR_INCLUIR_EVENTO = "0";
    const GR_ADMINISTRADORES = "1";
    const GR_COORDENADORES = "2";
    const GR_USUARIOS = "3";
    const GR_AVALIADORES_DE_TRABALHOS = "4";
    const GR_ADMINISTRACAO_DA_AVALIACOES_DE_TRABALHOS = "5";
    const GR_AVALIADORES_DE_TRABALHOS_PREMIADOS = "6";
    const GR_CONFIGURACAO_SISTEMA = "7";
    const GR_FINANCEIRO = "8";
    const GR_ADMINISTRADOR_AVALIADORES = "9";
    const GR_COORDENACAO_DE_ATIVIDADES = "10";
    const GR_RECEPCAO_DE_PARTICIPANTES = "11";
    const GR_LISTAR_INSCRICOES = "12";
    const GR_GERENCIAR_INSCRITO = "13";
    const GR_GERENCIAR_VOUCHER = "14";
    const GR_CONSULTAR_PAINEL_DE_BORDO = "15";
    const GR_EMITIR_DOCUMENTOS = "16";
    const GR_LISTAR_TRABALHOS = "17";
    const GR_ASSOCIAR_PERFIS = "18";
    const GR_GERENCIAR_CONVIDADOS = "19";
    const GR_LOGAR_COMO = "20";
    const GR_CARGA_BALANCETE_HUTEC = "21";
    const GR_AVALIADORES_DE_INSCRITOS = "22";
    const GR_ENVIAR_MENSAGEM = "23";
    const GR_INSCRITOS_APTOS = "24";
    const GR_GESTAO_ADEMICA = "25";
    const GR_CONTROLE_DE_ACESSO = "26";
    const GR_CARGA_TRANSPARENCIA = "27";
    const GR_PEDAGOGICO_GERENTE = "28";
    const GR_PEDAGOGICO_SUPERVISOR = "29";
    const GR_ADMINISTRADOR_GERAL = "999";

    // Dinamicos (novos grupos cadastrados via CRUD) - carregados do banco sob demanda
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

    /**
     * Grupos que contam como ADMINISTRACAO do projeto.
     *
     * Usado ao duplicar um projeto, quando se escolhe levar "so os
     * administradores" da origem. Tudo que NAO esta aqui e' tratado como
     * operacional (inscricoes, financeiro, documentos, recepcao, avaliadores).
     *
     * Linha definida com o usuario em 2026-08-20. Mudar o criterio e' editar
     * esta lista -- nao ha SQL com codigo de grupo espalhado.
     */
    public static function gruposAdministracao() {
        return array(
            self::GR_ADMINISTRADORES,                              // 1
            self::GR_COORDENADORES,                                // 2
            self::GR_ADMINISTRACAO_DA_AVALIACOES_DE_TRABALHOS,     // 5
            self::GR_CONFIGURACAO_SISTEMA,                         // 7
            self::GR_ADMINISTRADOR_AVALIADORES,                    // 9
            self::GR_COORDENACAO_DE_ATIVIDADES,                    // 10
            self::GR_CONTROLE_DE_ACESSO,                           // 26
            "999",                                                 // Administrador Geral
        );
    }

    private static function carregar() {
        ConexaoDB::getInstance();
        $conex = ConexaoDB::getConexaoDB();
        $rs = $conex->Execute("SELECT cod_grupo, enum_grupo FROM sis_grupos WHERE enum_grupo IS NOT NULL AND enum_grupo != ''");
        self::$cache = array();
        while ($rs && !$rs->EOF) {
            self::$cache[$rs->fields['enum_grupo']] = $rs->fields['cod_grupo'];
            $rs->MoveNext();
        }
    }
}
