<?php

/**
 * Configuracao do Certificado (tela dedicada dentro do hub de Certificados).
 *
 * Reaproveita o MESMO formulario que ja existe dentro de "Configurar campos do
 * evento" (evento-configurar-campos-certificados.php, intocado), mas com um
 * salvar DIRECIONADO: grava so os campos de certificado via
 * EventoConfiguracao::gravarConfiguracaoCertificado(), sem passar pelo salvar
 * geral que regrava a tabela inteira e quebra em MySQL estrito (local).
 *
 * Seguranca: metodos publicos passam por autenticar(), pois a rota CommandAC
 * e acessivel direto por POST.
 */
class GerenciarConfiguracaoCertificadoAC
{
    public $codEvento;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
    }

    private function autenticar()
    {
        FWA_exigirPermissao(array(
            enumSistema::SIS_GERENCIAR_ADM_GERAL,
            enumSistema::SIS_GERENCIAR_CERTIFICADO,    // 30 — direito de certificado
            enumSistema::SIS_GERENCIAR_INSCRITO,       // 13 — quem ja entrava por aqui continua entrando
            enumSistema::SIS_GERENCIAR_AREAS_TEMATICAS,
            enumSistema::SIS_CONSULTAR_PAINEL_BORDO,
            enumSistema::SIS_CONSULTAR_INSCRITOS,
        ));
    }

    /**
     * Objeto de configuracao ja carregado, para a view popular o formulario.
     * A view chama isto direto (nao e AJAX): $objEvConf = ...->carregar().
     */
    public function carregar($debug = false)
    {
        $this->autenticar();

        $obj = new EventoConfiguracao();
        $obj->setCodEvento($this->codEvento);
        $obj->getEventoConfiguracaoById($debug);
        return $obj;
    }

    /**
     * Grava SOMENTE os campos de certificado. Devolve JSON.
     * Carrega o registro atual, sobrescreve so os campos do form e chama o
     * update direcionado.
     */
    public function gravar($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            $obj = new EventoConfiguracao();
            $obj->setCodEvento($this->codEvento);
            $obj->getEventoConfiguracaoById($debug);

            // Grava SO os campos que vieram no POST. Campo ausente permanece
            // como esta no banco.
            //
            // Isto e o que permite dividir a configuracao em telas separadas
            // (Regras, Textos, Assinaturas): antes o codigo aplicava TODOS os
            // setters e o campo ausente virava string vazia — salvar uma tela
            // apagaria os campos das outras.
            foreach (self::camposDoCertificado() as $nome => $setter) {
                if (array_key_exists($nome, $fws_)) {
                    $obj->$setter(trim($fws_[$nome]));
                }
            }

            $obj->gravarConfiguracaoCertificado($debug);
        } catch (OKException $e) {
            $ok = true;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $msg));
        exit;
    }

    /**
     * Campos de certificado da configuracao do evento: coluna => setter.
     * Uma lista so, usada pelo gravar de qualquer uma das telas.
     */
    private static function camposDoCertificado()
    {
        return array(
            // --- Regras ---
            'emitir_certificado'                     => 'setEmitirCertificado',
            'certificado_dat_ini'                    => 'setCertificadoDatIni',
            'certificado_data_emissao'               => 'setCertificadoDataEmissao',
            'liberar_avaliar_evento'                 => 'setLiberarAvaliarEvento',
            'url_avaliar_evento'                     => 'setUrlAvaliarEvento',
            'certificado_congressista'               => 'setCertificadoCongressista',
            'certificado_trabalho_nao_apresentado'   => 'setCertificadoTrabalhoNaoApresentado',
            // --- Aparencia (fica junto das Assinaturas) ---
            'certificado_selo'                       => 'setCertificadoSelo',
            'certificado_assinatura_dupla'           => 'setCertificadoAssinaturaDupla',
            'certificado_promovido_por'              => 'setCertificadoPromovidoPor',
            // --- Texto padrao ---
            'certificado_exibir_qualidade'           => 'setCertificadoExibirQualidade',
            'certificado_texto_qualidade'            => 'setCertificadoTextoQualidade',
            'certificado_texto_inicial'              => 'setCertificadoTextoInicial',
            'certificado_texto_inicial_trabalho'     => 'setCertificadoTextoInicialTrabalho',
            'certificado_texto_no'                   => 'setCertificadoTextoNo',
            'certificado_texto_do'                   => 'setCertificadoTextoDo',
            'certificado_texto_reallizado'           => 'setCertificadoTextoReallizado',
            'certificado_congressista_qualidade'     => 'setCertificadoCongressistaQualidade',
            'certificado_congressista_carga_horaria' => 'setCertificadoCongressistaCargaHoraria',
        );
    }
}
