<?php

/**
 * VO do Modelo da FRENTE do certificado.
 *
 * Guarda a arte de fundo e a posicao dos blocos (texto, assinaturas, selo, QR,
 * data) em MILIMETROS medidos da borda da pagina -- a mesma unidade da regua e
 * do PDF. Antes esses valores eram px chumbados no CSS dos modelos.
 *
 * Pagina de referencia: A4 PAISAGEM = 297mm x 210mm.
 */
class CertificadoModeloVO extends PadraoVO
{
    /** Largura util da pagina em mm (A4 paisagem). */
    const LARGURA_MM = 297;
    /** Altura util da pagina em mm (A4 paisagem). */
    const ALTURA_MM  = 210;

    public function getAlinhamentosValidos()
    {
        return array('left', 'center', 'right');
    }

    /**
     * Clamp de uma coordenada horizontal (mm da esquerda).
     * Fora do papel nao existe: valor invalido volta para o default.
     */
    private function mmH($valor, $default)
    {
        if ($valor === '' || $valor === null) { return $default; }
        $v = (int) $valor;
        return ($v >= 0 && $v <= self::LARGURA_MM) ? $v : $default;
    }

    /** Clamp de uma coordenada vertical (mm do topo). */
    private function mmV($valor, $default)
    {
        if ($valor === '' || $valor === null) { return $default; }
        $v = (int) $valor;
        return ($v >= 0 && $v <= self::ALTURA_MM) ? $v : $default;
    }

    /** Clamp de tamanho de fonte em pt. */
    private function pt($valor, $default, $min = 5, $max = 60)
    {
        $v = (int) $valor;
        return ($v >= $min && $v <= $max) ? $v : $default;
    }

    /* ---------------- Bloco do TEXTO principal ---------------- */

    public function getTextoTopo()          { return $this->mmV($this->textoTopo, 60); }
    public function getTextoEsquerda()      { return $this->mmH($this->textoEsquerda, 25); }
    public function getTextoLargura()       { return $this->mmH($this->textoLargura, 247); }
    public function getTextoFonteTamanho()  { return $this->pt($this->textoFonteTamanho, 12); }
    public function getTextoAlturaLinha()   { return $this->pt($this->textoAlturaLinha, 18, 5, 90); }

    public function getTextoAlinhamento()
    {
        $a = $this->textoAlinhamento;
        return in_array($a, $this->getAlinhamentosValidos()) ? $a : 'center';
    }

    /* ---------------- Assinaturas ---------------- */

    public function getAss1Topo()     { return $this->mmV($this->ass1Topo, 150); }
    public function getAss1Esquerda() { return $this->mmH($this->ass1Esquerda, 60); }
    public function getAss1Largura()  { return $this->mmH($this->ass1Largura, 55); }

    public function getAss2Topo()     { return $this->mmV($this->ass2Topo, 150); }
    public function getAss2Esquerda() { return $this->mmH($this->ass2Esquerda, 180); }
    public function getAss2Largura()  { return $this->mmH($this->ass2Largura, 55); }

    /* ---------------- Selo / logo suplementar ---------------- */

    public function getSeloTopo()     { return $this->mmV($this->seloTopo, 15); }
    public function getSeloEsquerda() { return $this->mmH($this->seloEsquerda, 240); }
    public function getSeloLargura()  { return $this->mmH($this->seloLargura, 40); }

    /* ---------------- QR code ---------------- */

    public function getQrTopo()     { return $this->mmV($this->qrTopo, 175); }
    public function getQrEsquerda() { return $this->mmH($this->qrEsquerda, 15); }
    public function getQrLargura()  { return $this->mmH($this->qrLargura, 25); }

    /* ---------------- Data de emissao ---------------- */

    public function getDataTopo()          { return $this->mmV($this->dataTopo, 190); }
    public function getDataEsquerda()      { return $this->mmH($this->dataEsquerda, 180); }
    public function getDataLargura()       { return $this->mmH($this->dataLargura, 100); }
    public function getDataFonteTamanho()  { return $this->pt($this->dataFonteTamanho, 10); }

    /** true quando o modelo tem arte de fundo definida. */
    public function temArteFundo()
    {
        return trim((string) $this->getArteFundo()) !== '';
    }

    public function validatorObj()
    {
        $message = "";
        $return  = true;

        if ($this->isNullOrEmpty($this->getCodEvento())) {
            $message .= "Codigo do evento nao informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty(trim((string) $this->getTitulo()))) {
            $message .= "Informe um nome para o modelo (ex.: Padrao 2026).<br>";
            $return = false;
        }
        // O bloco de texto e o unico obrigatorio: sem ele nao ha certificado.
        // Largura zero renderiza uma faixa invisivel e o coordenador nao
        // entenderia por que o PDF saiu "vazio".
        if ($this->getTextoLargura() <= 0) {
            $message .= "A largura do bloco de texto precisa ser maior que zero.<br>";
            $return = false;
        }
        if (($this->getTextoEsquerda() + $this->getTextoLargura()) > self::LARGURA_MM) {
            $message .= "O bloco de texto passa da borda direita da pagina ("
                . $this->getTextoEsquerda() . "mm + " . $this->getTextoLargura()
                . "mm > " . self::LARGURA_MM . "mm).<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorExcluir()
    {
        if ($this->isNullOrEmpty($this->getCodEvento()) || $this->isNullOrEmpty($this->getCodCertificadoModelo())) {
            throw new Exception("Codigo do evento e do modelo sao obrigatorios para excluir.");
        }
        return true;
    }
}
