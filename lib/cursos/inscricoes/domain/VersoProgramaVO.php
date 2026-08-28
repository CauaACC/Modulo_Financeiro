<?php

/**
 * VO do Editor de Programa (verso do certificado).
 * Guarda as opcoes editaveis do programa. O PDF gerado a partir daqui vai
 * para certificados/verso/ e e registrado em tab_arquivo como PROGRAMA.
 */
class VersoProgramaVO extends PadraoVO
{
    /** Modos de fundo aceitos para o verso. */
    public function getModosFundoValidos()
    {
        return array('completo', 'branco');
    }

    public function getMargemBaixo()
    {
        $m = $this->margemBaixo;
        return ($m === '' || $m === null) ? 15 : (int) $m;
    }

    public function getModoFundo()
    {
        $m = $this->modoFundo;
        return in_array($m, $this->getModosFundoValidos()) ? $m : 'branco';
    }

    public function getNumColunas()
    {
        $n = (int) $this->numColunas;
        return ($n >= 1 && $n <= 3) ? $n : 1;
    }

    /**
     * Fonte do CORPO em pt. Padrao 10: com titulo e rodape ocupando
     * faixas fixas no A4, sobra menos altura para o conteudo.
     */
    public function getFonteTamanho()
    {
        $f = (int) $this->fonteTamanho;
        return ($f >= 6 && $f <= 40) ? $f : 10;
    }

    /**
     * Fonte do titulo (pt). Titulo NAO segue a fonte do corpo.
     * Sem valor salvo, cai no comportamento historico: fonte do corpo + 3.
     */
    public function getFonteTitulo()
    {
        $f = (int) $this->fonteTitulo;
        return ($f >= 6 && $f <= 60) ? $f : ($this->getFonteTamanho() + 3);
    }

    /** Fonte do rodape (pt). Rodape NAO segue a fonte do corpo. */
    public function getFonteRodape()
    {
        $f = (int) $this->fonteRodape;
        return ($f >= 6 && $f <= 40) ? $f : 9;
    }

    /**
     * Margem superior do CONTEUDO (mm da borda de cima). Absoluta: e o mesmo
     * numero que se le na Regua (mm). Antes isso era um espaco relativo ao
     * titulo, o que obrigava o usuario a fazer conta e gerava valores absurdos.
     */
    public function getMargemConteudoTopo()
    {
        $m = (int) $this->margemConteudoTopo;
        return ($m >= 0 && $m <= 190) ? $m : 25;
    }

    /** Margem inferior do CONTEUDO (mm da borda de baixo). Absoluta. */
    public function getMargemConteudoBaixo()
    {
        $m = (int) $this->margemConteudoBaixo;
        return ($m >= 0 && $m <= 190) ? $m : 25;
    }

    /** true quando ha titulo para renderizar (titulo e opcional). */
    public function temTitulo()
    {
        return trim((string) $this->getTitulo()) !== '';
    }

    /** true quando ha rodape para renderizar (rodape e opcional, igual ao titulo). */
    public function temRodape()
    {
        return trim((string) $this->getRodape()) !== '';
    }

    /**
     * Nome do arquivo PDF do verso, no MESMO padrao que o
     * GerenciarCertificadoAC espera ao anexar o verso:
     *   geral   -> programa.pdf
     *   oficina -> programa-{cod_oficina 7 digitos}.pdf
     */
    public function gerarNomeArquivoPrograma()
    {
        $codOficina = (int) $this->getCodOficina();
        if ($codOficina > 0) {
            return 'programa-' . numberPad($codOficina, 7) . '.pdf';
        }
        return 'programa.pdf';
    }

    public function validatorObj()
    {
        $message = "";
        $return  = true;

        if ($this->isNullOrEmpty($this->getCodEvento())) {
            $message .= "Codigo do evento nao informado.<br>";
            $return = false;
        }
        if (!in_array($this->modoFundo, $this->getModosFundoValidos())) {
            $message .= "Modo de fundo invalido.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty(trim(strip_tags($this->getConteudoHtml())))) {
            $message .= "O conteudo do programa esta vazio.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorExcluir()
    {
        if ($this->isNullOrEmpty($this->getCodEvento()) || $this->isNullOrEmpty($this->getCodVersoPrograma())) {
            throw new Exception("Codigo do evento e do programa sao obrigatorios para excluir.");
        }
        return true;
    }
}
