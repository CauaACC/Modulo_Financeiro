<?php

require_once __DIR__ . '/../lib/vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class ContratoPlaceholderHelper {

    /**
     * Mapa de placeholders disponíveis para templates DOCX.
     * Cada entrada: 'PLACEHOLDER' => ['campo_banco', 'Label para exibição', 'tipo']
     * tipo: 'inscrito' = campo de tab_inscritos, 'composto' = montado a partir de outros, 'sistema' = gerado pelo sistema
     */
    public static function getMapaPlaceholders() {
        return array(
            'NOME'           => array('nome_inscrito',    'Nome',              'inscrito'),
            'CPF'            => array('cpf',              'CPF',               'inscrito'),
            'RG'             => array('rg',               'RG',                'inscrito'),
            'NASCIMENTO'     => array('data_nascimento',  'Data de Nascimento','inscrito'),
            'ESTADO_CIVIL'   => array('estado_civil',     'Estado Civil',      'inscrito'),
            'ENDERECO'       => array('endereco',         'Endereço',          'inscrito'),
            'NUMERO'         => array('numero',           'Número',            'inscrito'),
            'COMPLEMENTO'    => array('complemento',      'Complemento',       'inscrito'),
            'BAIRRO'         => array('bairro',           'Bairro',            'inscrito'),
            'CIDADE'         => array('cidade',           'Cidade',            'inscrito'),
            'ESTADO'         => array('estado',           'Estado (UF)',       'inscrito'),
            'CEP'            => array('cep',              'CEP',               'inscrito'),
            'TELEFONE'       => array('fone_contato',     'Telefone',          'inscrito'),
            'DDD'            => array('fone_contato_ddd', 'DDD',               'inscrito'),
            'EMAIL'          => array('e_mail',           'E-mail',            'inscrito'),
            'NOME_MAE'       => array('nome_mae',         'Nome da Mãe',      'inscrito'),
            'NOME_PAI'       => array('nome_pai',         'Nome do Pai',       'inscrito'),
            'INSTITUICAO'    => array('instituicao',      'Instituição',       'inscrito'),
            'CARGO_ATUAL'    => array('cargo_atual',      'Cargo Atual',       'inscrito'),
            'GRAU_ESCOLARIDADE' => array('grau_escolaridade', 'Grau de Escolaridade', 'inscrito'),
            // Compostos - montados a partir de mais de um campo
            'PROFISSAO'      => array(null,               'Profissão',         'composto'),
            'CIDADE_UF'      => array(null,               'Cidade-UF',         'composto'),
            'TELEFONE_COMPLETO' => array(null,            'Telefone com DDD',  'composto'),
            // Sistema - gerados automaticamente
            'DATA_LOCAL'     => array(null,               'Data e Local',      'sistema'),
            'DATA_ATUAL'     => array(null,               'Data Atual',        'sistema'),
        );
    }

    /**
     * Detecta quais placeholders um DOCX contém
     */
    public static function detectarPlaceholders($caminhoDocx) {
        $tp = new TemplateProcessor($caminhoDocx);
        return $tp->getVariables();
    }

    /**
     * Retorna os campos do inscrito que são obrigatórios baseado nos placeholders do DOCX
     */
    public static function getCamposObrigatoriosFromPlaceholders($placeholders) {
        $mapa = self::getMapaPlaceholders();
        $campos = array();
        foreach ($placeholders as $ph) {
            if (isset($mapa[$ph])) {
                $info = $mapa[$ph];
                if ($info[2] == 'inscrito') {
                    $campos[] = $ph;
                } elseif ($info[2] == 'composto') {
                    // Compostos dependem de campos do inscrito
                    if ($ph == 'PROFISSAO') {
                        $campos[] = 'PROFISSAO';
                    } elseif ($ph == 'CIDADE_UF') {
                        if (!in_array('CIDADE', $campos)) $campos[] = 'CIDADE';
                        if (!in_array('ESTADO', $campos)) $campos[] = 'ESTADO';
                    } elseif ($ph == 'TELEFONE_COMPLETO') {
                        if (!in_array('DDD', $campos)) $campos[] = 'DDD';
                        if (!in_array('TELEFONE', $campos)) $campos[] = 'TELEFONE';
                    }
                }
            }
        }
        return $campos;
    }

    /**
     * Verifica quais campos obrigatórios o inscrito ainda não preencheu
     * Retorna array de labels legíveis dos campos faltantes
     */
    public static function getCamposFaltantes($objInscrito, $camposObrigatorios) {
        $mapa = self::getMapaPlaceholders();
        $faltantes = array();
        foreach ($camposObrigatorios as $ph) {
            if (!isset($mapa[$ph])) continue;
            $info = $mapa[$ph];

            if ($info[2] == 'inscrito') {
                $valor = self::getValorCampoInscrito($objInscrito, $info[0]);
                if (is_null($valor) || trim($valor) === '') {
                    $faltantes[] = $info[1];
                }
            } elseif ($info[2] == 'composto') {
                $valor = self::getValorComposto($ph, $objInscrito);
                if (is_null($valor) || trim($valor) === '') {
                    $faltantes[] = $info[1];
                }
            }
        }
        return $faltantes;
    }

    /**
     * Preenche os placeholders do template com dados do inscrito
     */
    public static function preencherTemplate($caminhoTemplate, $caminhoSaida, $objInscrito, $cidadeEvento = 'Londrina, PR') {
        $tp = new TemplateProcessor($caminhoTemplate);
        $placeholders = $tp->getVariables();
        $mapa = self::getMapaPlaceholders();

        foreach ($placeholders as $ph) {
            if (!isset($mapa[$ph])) {
                $tp->setValue($ph, '');
                continue;
            }

            $info = $mapa[$ph];
            $valor = '';

            if ($info[2] == 'inscrito') {
                $valor = self::getValorCampoInscrito($objInscrito, $info[0]);
                // Formatar data de nascimento
                if ($ph == 'NASCIMENTO' && $valor != '') {
                    $valor = self::formatarData($valor);
                }
            } elseif ($info[2] == 'composto') {
                $valor = self::getValorComposto($ph, $objInscrito);
            } elseif ($info[2] == 'sistema') {
                $valor = self::getValorSistema($ph, $cidadeEvento);
            }

            $tp->setValue($ph, $valor !== null ? $valor : '');
        }

        $tp->saveAs($caminhoSaida);
        return true;
    }

    /**
     * Obtém valor de um campo do inscrito via getter mágico
     */
    private static function getValorCampoInscrito($objInscrito, $campo) {
        // Converter nome_campo para NomeCampo para o getter
        $partes = explode('_', $campo);
        $getter = 'get';
        foreach ($partes as $p) {
            $getter .= ucfirst($p);
        }
        if (method_exists($objInscrito, $getter) || method_exists($objInscrito, '__call')) {
            return $objInscrito->$getter();
        }
        return null;
    }

    /**
     * Monta valores compostos
     */
    private static function getValorComposto($placeholder, $objInscrito) {
        switch ($placeholder) {
            case 'PROFISSAO':
                $profissao = self::getValorCampoInscrito($objInscrito, 'profissao');
                if (!$profissao || trim($profissao) === '') {
                    $profissao = self::getValorCampoInscrito($objInscrito, 'profissao_atuacao');
                }
                return $profissao ?: '';

            case 'CIDADE_UF':
                $cidade = self::getValorCampoInscrito($objInscrito, 'cidade');
                $estado = self::getValorCampoInscrito($objInscrito, 'estado');
                if ($cidade && $estado) {
                    return $cidade . '-' . $estado;
                }
                return $cidade ?: $estado ?: '';

            case 'TELEFONE_COMPLETO':
                $ddd = self::getValorCampoInscrito($objInscrito, 'fone_contato_ddd');
                $fone = self::getValorCampoInscrito($objInscrito, 'fone_contato');
                if ($ddd && $fone) {
                    return '(' . $ddd . ') ' . $fone;
                }
                return $fone ?: '';

            default:
                return '';
        }
    }

    /**
     * Valores gerados pelo sistema
     */
    private static function getValorSistema($placeholder, $cidadeEvento = 'Londrina, PR') {
        $meses = array(
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        );
        $dia = date('j');
        $mes = $meses[(int)date('n')];
        $ano = date('Y');

        switch ($placeholder) {
            case 'DATA_LOCAL':
                return $cidadeEvento . ', ' . $dia . ' de ' . $mes . ' de ' . $ano . '.';
            case 'DATA_ATUAL':
                return $dia . ' de ' . $mes . ' de ' . $ano;
            default:
                return '';
        }
    }

    /**
     * Formata data de Y-m-d para d/m/Y
     */
    private static function formatarData($data) {
        if (strpos($data, '/') !== false) {
            return $data; // Já formatada
        }
        $dt = DateTime::createFromFormat('Y-m-d', $data);
        if ($dt) {
            return $dt->format('d/m/Y');
        }
        return $data;
    }

    /**
     * ---------------------------------------------------------------------------
     * MODO ANTIGO DE GERAR PDF — PHPWord + mPDF. DESATIVADO, mantido de proposito.
     * ---------------------------------------------------------------------------
     *
     * Este era o caminho usado ate 08/2026. Nao e uma conversao de verdade: o
     * PHPWord rebaixa o DOCX para um modelo interno simplificado e reescreve o
     * conteudo em HTML, que o mPDF entao imprime. No caminho se perdem cabecalho
     * (logo), rodape, imagens, largura de colunas e paginacao — no contrato da
     * Ortodontia, 5 paginas viravam 25.
     *
     * Foi substituido por converterParaPDF(), que usa o Gotenberg (LibreOffice).
     *
     * Fica aqui porque tem uma vantagem que o novo nao tem: NAO depende de
     * servico externo. Se algum dia for preciso gerar PDF sem o Gotenberg no ar
     * — documento simples, so texto, onde o layout nao importa — basta
     * descomentar e chamar este metodo no lugar do outro.
     *
     * public static function converterParaPDFComMPdf($caminhoDocx, $caminhoPdf) {
     *     Settings::setPdfRendererName(Settings::PDF_RENDERER_MPDF);
     *     Settings::setPdfRendererPath(dirname(__DIR__) . '/lib/vendor/mpdf/mpdf');
     *
     *     $phpWord = IOFactory::load($caminhoDocx);
     *     $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
     *     $pdfWriter->save($caminhoPdf);
     *
     *     return true;
     * }
     */

    /**
     * Converte DOCX para PDF pelo Gotenberg (LibreOffice em container).
     *
     * Quem converte e o proprio LibreOffice, entao o PDF sai igual ao que o Word
     * mostra — mantem logo do cabecalho, rodape, tabelas e paginacao.
     *
     * Exige as constantes WB_GOTENBERG_* no env.php.
     * Lanca Exception se o servico nao responder — quem chama decide o fallback.
     */
    public static function converterParaPDF($caminhoDocx, $caminhoPdf) {
        if (!defined('WB_GOTENBERG_URL') || WB_GOTENBERG_URL == '') {
            throw new Exception("Servico de conversao para PDF nao configurado (WB_GOTENBERG_URL).");
        }

        $url = rtrim(WB_GOTENBERG_URL, '/') . '/forms/libreoffice/convert';
        $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'files' => new CURLFile($caminhoDocx, $mime, basename($caminhoDocx))
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        if (defined('WB_GOTENBERG_USER') && WB_GOTENBERG_USER != '') {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, WB_GOTENBERG_USER . ':' . WB_GOTENBERG_SENHA);
        }

        $resposta = curl_exec($ch);
        $codigo   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erroCurl = curl_error($ch);
        curl_close($ch);

        if ($resposta === false || $erroCurl != '') {
            throw new Exception("Falha ao contatar o servico de conversao: " . $erroCurl);
        }
        if ($codigo == 401) {
            throw new Exception("Servico de conversao recusou as credenciais (HTTP 401).");
        }
        if ($codigo != 200) {
            throw new Exception("Servico de conversao respondeu HTTP " . $codigo . ".");
        }
        // resposta de erro do Gotenberg vem como texto, nao como PDF
        if (substr($resposta, 0, 5) !== '%PDF-') {
            throw new Exception("Servico de conversao nao devolveu um PDF valido.");
        }

        if (file_put_contents($caminhoPdf, $resposta) === false) {
            throw new Exception("Nao foi possivel gravar o PDF convertido.");
        }

        return true;
    }
}
?>