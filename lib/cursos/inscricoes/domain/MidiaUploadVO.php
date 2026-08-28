<?php
class MidiaUploadVO extends PadraoVO
{
    
    public function getListaAtributosMidia()
    {
        return [
            "MIDIA" => [
                "tipo"      => "image/png",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "media"
            ],
            "LOGO-DO-EVENTO-BARRA" => [
                "tipo"      => "image/png",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "media"
            ],
            "ARTE-DIVULGACAO" => [
                "tipo"      => "image/png",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "media"
            ],
            "FAVICON" => [
                "tipo"      => "image/x-icon",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   =>  "media"
            ],
            "BANNER-DE-OFICINA" => [
                "tipo"      => "image/png",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   =>  "media"
            ],
            "BANNER-DE-PERFIL" => [
                "tipo"      => "image/png",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   =>  "media"
            ],
            "ETIQUETA-DO-VOUCHER" => [
                "tipo"      => "image/png",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "media"
            ],            
            "ASSINATURA-DO-PRESIDENTE" => [
                "tipo"      => "image/jpg",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "assinaturas"
            ],
            "ASSINATURA-DO-COORDENADOR-GERAL" => [
                "tipo"      => "image/jpg",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "assinaturas"
            ],
            "ASSINATURA-DO-COORDENADOR-DE-ATIVIDADE" => [
                "tipo"      => "image/jpg",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "assinaturas"
            ],            
            "ASSINATURA-DO-COORDENADOR-DE-COMITE-CIENTIFICO" => [
                "tipo"      => "image/jpg",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "assinaturas"
            ],
            "PROGRAMA" => [
                "tipo"      => "application/pdf",
                "tamanho"   => "30mb",
                "quantidade" => "",
                "caminho"   => "certificados/verso"
            ],            
            "CERTIFICADO-BANNER" => [
                "tipo"      => "image/jpg",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "media"
            ],
            "CERTIFICADO-RODAPE" => [
                "tipo"      => "image/jpg",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "media"
            ]
            ,"CERTIFICADO-FRENTE" => [
                "tipo"      => "image/jpeg",
                "tamanho"   => "30mb",
                "quantidade" => "",
                "caminho"   => "midia"
            ]
            ,"CERTIFICADO-VERSO-FUNDO" => [
                "tipo"      => "image/jpeg",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "media"
            ]
            ,"MODELO-DO-RESUMO" => [
                "tipo"      => "application/msword",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "media"
            ]
            ,"CERTIFICADO-VERSO-FUNDO-PDF" => [
                "tipo"      => "application/pdf",
                "tamanho"   => "30mb",
                "quantidade" => "1",
                "caminho"   => "media"
            ]
        ];
    }
    /**Os metodos getLocalMedia não são definidos, mas para serem referenciados devem ser definidos usando o indice do array */
    public function getLocalMidia(){
        return $this->localMidia;
    }
    public function setLocalMidia($indiceArrayListaAtributosMidia){
        $this->localMidia = $indiceArrayListaAtributosMidia;
    }
    
    public function gerarNomeArquivo()
    {

        //$fws_['arquivo_gerado']  = $obj->getLocalMidia().'_'. numberPad($obj->getCodArquivo(), 6)  ."_" . $obj->getVersao() . $extensao;
        if ($this->getLocalMidia() == 'ETIQUETA-DO-VOUCHER') {
            return "etiqueta_10x56mm_" . $this->getCodEvento() . ".png";
        }
        if ($this->getLocalMidia() == 'LOGO-DO-EVENTO-BARRA') {
            return "logo-60X235pxN-" . $this->getCodEvento() . ".png";
        }
        if ($this->getLocalMidia() == 'ARTE-DIVULGACAO') {
            /* Arte de divulgacao do evento no site da HUTEC (quadrada 1080x1080).
             * Mantem a extensao enviada: PNG e JPG sao ambos aceitos. */
            $extensao = strtolower(pathinfo($this->getArquivoUpload(), PATHINFO_EXTENSION));
            $extensao = in_array($extensao, array('png', 'jpg', 'jpeg')) ? '.' . $extensao : '.png';
            return "arte-divulgacao-" . $this->getCodEvento() . $extensao;
        }
        if ($this->getLocalMidia() == 'FAVICON') {
            return "favicon-" . $this->getCodEvento() . ".ico";
        }
        if ($this->getLocalMidia() == 'PROGRAMA') {
            $complementoNome = '';
            if ($this->getCodPerfil() != '') {
                $complementoNome .= '-' . numberPad($this->getCodPerfil(), 7);
            }
            if ($this->getCodOficina() != '') {
                $complementoNome .= '-' . numberPad($this->getCodPerfil(), 7);
            }
            return "programa" . $complementoNome . ".pdf";
        }
        if ($this->getLocalMidia() == 'ASSINATURA-DO-PRESIDENTE') {
            return "assinatura-presidente.jpg";
        }
        if ($this->getLocalMidia() == 'ASSINATURA-DO-COORDENADOR-GERAL') {
            return "coordenador-0000000.jpg";
        }
        if ($this->getLocalMidia() == 'ASSINATURA-DO-COORDENADOR-DE-COMITE-CIENTIFICO') {
            return "assinatura-coordenador-comite-cientifico.jpg";
        }
        if ($this->getLocalMidia() == 'ASSINATURA-DO-COORDENADOR-DE-ATIVIDADE') {
            $complementoNome = '';
            // if ($this->getCodPerfil() != '') {
            //     $complementoNome .= '-' . numberPad($this->getCodPerfil(), 7);
            // }
            if ($this->getCodOficina() != '') {
                $complementoNome .= '-' . numberPad($this->getCodPerfil(), 7);
            }
            return "coordenador" . $complementoNome . ".jpg";
        }
        if ($this->getLocalMidia() == 'PROGRAMA') {
            $complementoNome = '';
            if ($this->getCodPerfil() != '') {
                $complementoNome .= '-' . numberPad($this->getCodPerfil(), 7);
            }
            if ($this->getCodOficina() != '') {
                $complementoNome .= '-' . numberPad($this->getCodPerfil(), 7);
            }
            return "programa" . $complementoNome . ".pdf";
        }
        if ($this->getLocalMidia() == 'BANNER-DE-PERFIL') {
            return "banner-perfil-" . $this->getCodEvento() . "-" . $this->getCodPerfil() . ".png";
        }
        if ($this->getLocalMidia() == 'BANNER-DE-OFICINA') {
            return "banner-oficina-" . $this->getCodEvento() . "-" . $this->getCodOficina() . ".png";
        }
        if ($this->getLocalMidia() == 'CERTIFICADO-FRENTE') {
            $complementoNome = '';
            if ($this->getCodPerfil() != '') {
                $complementoNome .= '-' . numberPad($this->getCodPerfil(), 7);
            }
            if ($this->getCodOficina() != '') {
                $complementoNome .= '-' . numberPad($this->getCodPerfil(), 7);
            }
            return "certificado-frente-unica" . $complementoNome . ".jpg";
        }
        if ($this->getLocalMidia() == 'CERTIFICADO-VERSO-FUNDO') {
            return "certificado-verso-fundo-" . $this->getCodEvento() . ".jpg";
        }
        if ($this->getLocalMidia() == 'CERTIFICADO-VERSO-FUNDO-PDF') {
            return "certificado-verso-fundo-" . $this->getCodEvento() . ".pdf";
        }
        if ($this->getLocalMidia() == 'CERTIFICADO-BANNER') {
            return "certificado-banner-" . $this->getCodEvento() . ".jpg";
        }
        if ($this->getLocalMidia() == 'MODELO-DO-RESUMO') {
            /* Mantem a extensao do arquivo enviado (docx, doc, pdf, rtf): o modelo
             * do resumo nao tem formato unico como as imagens dos outros locais. */
            $extensao = strtolower(pathinfo($this->getArquivoUpload(), PATHINFO_EXTENSION));
            $extensao = $extensao != '' ? '.' . $extensao : '.docx';
            return "modelo-resumo-" . $this->getCodEvento() . $extensao;
        }
        if ($this->getLocalMidia() == 'CERTIFICADO-RODAPE') {
            return "certificado-rodape-" . $this->getCodEvento() . ".jpg";
        }
        return false;
    }

    public function getMidiaMineType()
    {
        $mapMidia = $this->getListaAtributosMidia();
        return  $mapMidia[$this->getLocalMidia()]["tipo"];
    }

    public function getMidiaTamanho()
    {
        $mapMidia = $this->getListaAtributosMidia();
        return  $mapMidia[$this->getLocalMidia()]["tamanho"];
    }

    public function getMidiaQuantidade()
    {
        $mapMidia = $this->getListaAtributosMidia();
        return  $mapMidia[$this->getLocalMidia()]["quantidade"];
    }

    public function getMidiaCaminho(){
        $mapMidia = $this->getListaAtributosMidia();
        $midiaCaminho = $mapMidia[$this->getLocalMidia()]["caminho"];
        return WB_PATH_LOG.$midiaCaminho.'/';
    }
    // public function getLocalMidia(){
    //     $mapMidia = $this->getListaAtributosMidia();
    //     $mediaCaminho = $mapMidia[$this->getLocalMidia()]["caminho"];
    //     return WB_PATH_LOG.$mediaCaminho;
    // }
    public function getTipoArquivo()
    {
        if ($this->tipoArquivo != '') {
            return $this->tipoArquivo;
        }
        if ($this->getLocalMidia() != '') {
            return $this->getLocalMidia();
        }
        return "MIDIA";
    }

    public function isValidLocalMidia()
    {
        $validLocaisMidia = array_keys($this->getListaAtributosMidia());
        return in_array($this->getLocalMidia(), $validLocaisMidia);
    }

    public function validatorObj()
    {
        $message = "";
        $return  = true;

        if ($this->isNullOrEmpty($this->getLocalMidia())) {
            $message .= "Nenhum local de mídia foi selecionado.<br>";
            $return = false;
        }
        if (!$this->isValidLocalMidia()) {
            $message .= "Local de mídia selecionado não é válido.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($_FILES["arquivo"]["name"])) {
            $message .= "É necessário selecionar um arquivo de imagem.<br>";
            $return = false;
        }

        // $mapMidia = $this->getListaAtributosMidia();
        // $tipoLocalMidia = $mapMidia[$this->getLocalMidia()]["tipo"];
        // if ($_FILES['arquivo']['type'] != $tipoLocalMidia) {
        //     $message .= "Extensão  do arquivo não permitida para a mídia local selecionada.<br>";
        //     $return = false;
        // }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorExcluir()
    {
        $message = "";
        $return  = true;

        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodArquivo()) or $this->getCodArquivo() == "") {
            $message .= "Cód. Arquivo não foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function getLocalMidiaView()
    {
        $localMidia = $this->getTipoArquivo();
        $localMidia = str_replace("-", " ", $localMidia);
        $localMidia = ucwords($localMidia);
        // $localMidia = removerHifenToEspaco(textoMaiusculoMinusculo($this->getlocalMidia()));
        return $localMidia;
    }

}
