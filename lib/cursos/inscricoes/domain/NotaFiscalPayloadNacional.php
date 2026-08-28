<?php

/**
 * Traduz nosso emissor + nossa nota para o JSON do endpoint /v2/nfsen
 * (NFS-e padrao nacional) da Focus NFe.
 *
 * O mapeamento segue o DPS do padrao nacional:
 *   cLocEmi      -> codigo_municipio_emissora
 *   opSimpNac    -> codigo_opcao_simples_nacional
 *   regEspTrib   -> regime_especial_tributacao
 *   cLocPrestacao-> codigo_municipio_prestacao
 *   cTribNac     -> codigo_tributacao_nacional_iss
 *   xDescServ    -> descricao_servico
 *   vServ        -> valor_servico
 *   tribISSQN    -> tributacao_iss
 *   tpRetISSQN   -> tipo_retencao_iss
 */
class NotaFiscalPayloadNacional {

    public static function montar(NotaFiscalEmissor $emissor, NotaFiscal $nota) {

        $payload = array();

        // -------- Datas --------
        $payload['data_emissao'] = date('Y-m-d\TH:i:sP');
        $payload['data_competencia'] = $nota->getDataCompetencia()
            ? date('Y-m-d', strtotime($nota->getDataCompetencia()))
            : date('Y-m-d');

        // -------- Numeracao do DPS --------
        // Quem numera somos nos: serie propria por emissor evita colisao com
        // as notas emitidas por fora (Emissor Web do gov.br), que seguem a
        // serie delas. Sem serie configurada, os campos nao vao.
        if (trim((string) $emissor->getSerieDps()) != '' && $nota->getNumeroDps()) {
            $payload['serie_dps'] = (string) $emissor->getSerieDps();
            $payload['numero_dps'] = (string) $nota->getNumeroDps();
        }

        // -------- Prestador --------
        $payload['codigo_municipio_emissora'] = (int) $emissor->getCodigoMunicipio();
        $payload['cnpj_prestador'] = self::somenteDigitos($emissor->getCnpj());

        // Em municipios onde a prefeitura nao registrou a IM no ambiente nacional,
        // mandar o campo derruba a emissao — por isso so entra quando preenchido.
        if ($emissor->temInscricaoMunicipal()) {
            $payload['inscricao_municipal_prestador'] = self::somenteDigitos($emissor->getInscricaoMunicipal());
        }

        $payload['codigo_opcao_simples_nacional'] = (int) $emissor->getCodigoOpcaoSimplesNacional();
        $payload['regime_especial_tributacao'] = (int) $emissor->getRegimeEspecialTributacao();

        if (trim((string) $emissor->getTelefone()) != '') {
            $payload['telefone_prestador'] = self::somenteDigitos($emissor->getTelefone());
        }
        if (trim((string) $emissor->getEmail()) != '') {
            $payload['email_prestador'] = trim($emissor->getEmail());
        }

        // -------- Tomador --------
        $doc = self::somenteDigitos($nota->getTomadorCpfCnpj());
        if (strlen($doc) == 14) {
            $payload['cnpj_tomador'] = $doc;
        } else {
            $payload['cpf_tomador'] = $doc;
        }
        $payload['razao_social_tomador'] = self::limitar($nota->getTomadorNome(), 150);

        // ENDERECO DO TOMADOR: tudo-ou-nada, CEP e municipio inclusive.
        //
        // O schema nacional monta <end> a partir do municipio/CEP e entao EXIGE
        // xLgr + nro + xBairro dentro dele. Mandar so municipio e CEP devolve
        // "Element 'end': Missing child element(s)"; mandar bairro sem numero
        // devolve "Element 'xBairro': not expected". Nao existe meio-termo:
        // ou o endereco vai completo, ou nao vai nenhum campo dele.
        $logradouro = trim((string) $nota->getTomadorLogradouro());
        $bairro     = trim((string) $nota->getTomadorBairro());
        $cep        = self::somenteDigitos($nota->getTomadorCep());
        $municipio  = (int) $nota->getTomadorCodigoMunicipio();

        $enderecoCompleto = ($logradouro !== '' && $bairro !== '' && $cep !== '' && $municipio > 0);

        if ($enderecoCompleto) {
            $payload['codigo_municipio_tomador'] = $municipio;
            $payload['cep_tomador'] = $cep;
            $payload['logradouro_tomador'] = self::limitar($logradouro, 255);

            // ViaCEP nao traz numero: S/N e a convencao do documento fiscal.
            $numero = trim((string) $nota->getTomadorNumero());
            $payload['numero_tomador'] = ($numero !== '') ? self::limitar($numero, 20) : 'S/N';

            if (trim((string) $nota->getTomadorComplemento()) != '') {
                $payload['complemento_tomador'] = self::limitar($nota->getTomadorComplemento(), 100);
            }
            $payload['bairro_tomador'] = self::limitar($bairro, 100);
        }

        if (trim((string) $nota->getTomadorTelefone()) != '') {
            $payload['telefone_tomador'] = self::somenteDigitos($nota->getTomadorTelefone());
        }
        if (trim((string) $nota->getTomadorEmail()) != '') {
            $payload['email_tomador'] = trim($nota->getTomadorEmail());
        }

        // -------- Servico --------
        $payload['codigo_municipio_prestacao'] = (int) $emissor->getCodigoMunicipio();

        $cTribNac = $nota->getCodigoTributacaoNacionalIss()
            ? $nota->getCodigoTributacaoNacionalIss()
            : $emissor->getCodigoTributacaoNacionalIss();
        $payload['codigo_tributacao_nacional_iss'] = self::somenteDigitos($cTribNac);

        if (trim((string) $emissor->getCodigoNbs()) != '') {
            $payload['codigo_nbs'] = self::somenteDigitos($emissor->getCodigoNbs());
        }

        // Dois campos DISTINTOS no padrao nacional, com limites proprios:
        //   xDescServ -> 1.000 caracteres, o que foi prestado
        //   xInfComp  -> 2.000 caracteres, campo livre do contribuinte
        // Juntar os dois estourava o limite da descricao com texto que tem
        // lugar proprio.
        $payload['descricao_servico'] = self::limitar($nota->getDescricaoServico(), 1000);

        $infoCompl = trim((string) $nota->getInformacoesComplementares());
        if ($infoCompl !== '') {
            $payload['informacoes_complementares'] = self::limitar($infoCompl, 2000);
        }
        $payload['valor_servico'] = round((float) $nota->getValorServico(), 2);

        $payload['tributacao_iss'] = (int) $emissor->getTributacaoIss();
        $payload['tipo_retencao_iss'] = (int) $emissor->getTipoRetencaoIss();

        // Alguns municipios exigem a aliquota; MEI normalmente nao se aplica
        // (o ISS ja esta no DAS), por isso so vai quando cadastrada.
        if ($emissor->getPercentualAliquota() !== null && $emissor->getPercentualAliquota() !== '') {
            $payload['percentual_aliquota_relativa_municipio'] = (float) $emissor->getPercentualAliquota();
        }

        return $payload;
    }

    private static function somenteDigitos($valor) {
        return preg_replace('/[^0-9]/', '', (string) $valor);
    }

    private static function limitar($texto, $tamanho) {
        $texto = trim((string) $texto);
        if (function_exists('mb_substr')) {
            return mb_substr($texto, 0, $tamanho, 'UTF-8');
        }
        return substr($texto, 0, $tamanho);
    }

}
