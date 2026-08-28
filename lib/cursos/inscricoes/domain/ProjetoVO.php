
<?php

class ProjetoVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
//      INICÍO ABA 1

//            if (is_null($this->getTituloEvento()) or $this->getTituloEvento() == "") {
//                $message .= "Titúlo do Evento não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getEmailCoordenador()) or $this->getEmailCoordenador() == "") {
//                $message .= "E-mail do coordenador não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getPais()) or $this->getPais() == "") {
//                $message .= "País não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getTelefone()) or $this->getTelefone() == "") {
//                $message .= "Telefone/WhatsApp não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getNomeCoordenador()) or $this->getNomeCoordenador() == "") {
//                $message .= "Nome do coordenador não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getSigla()) or $this->getSigla() == "") {
//                $message .= "Sigla/Abreviação do evento não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getExpectativa()) or $this->getExpectativa() == "") {
//                $message .= "Quantidade de público esperado não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getTipo()) or $this->getTipo() == "") {
//                $message .= "Tipo do evento não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getCategoria()) or $this->getCategoria() == "") {
//                $message .= "Categoria do evento não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getDataInicioEvento()) or $this->getDataInicioEvento() == "") {
//                $message .= "Data de início do evento não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getDataFimInscricoes()) or $this->getDataFimInscricoes() == "") {
//                $message .= "Data do fim do evento não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getDataInicioInscricoes()) or $this->getDataInicioInscricoes() == "") {
//                $message .= "Data de Início do evento não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getDataFimInscricoes()) or $this->getDataFimInscricoes() == "") {
//                $message .= "Data de fim de Inscrições não foi informado.<br>";
//                $return = false;
//            }
//
////      FIM    ABA 1
////      INÍCIO ABA 2
//
//            if (is_null($this->getTipoProjeto()) or $this->getTipoProjeto() == "") {
//                $message .= "Tipo do Projeto não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getTrabEnvioTrabalhos()) or $this->getTrabEnvioTrabalhos() == "") {
//                $message .= "Servições do evento não foi informado.<br>";
//                $return = false;
//            }
//    //        if (is_null($this->getDescricaoProjeto()) or $this->getDescricaoProjeto() == "") {
//    //            $message .= "Descrição do projeto não foi informado.<br>";
//    //            $return = false;
//    //        }
//            if (is_null($this->getDescricaoValoresInscricao()) or $this->getDescricaoValoresInscricao() == "") {
//                $message .= "Descrição dos valores da inscrição não foi informado.<br>";
//                $return = false;
//            }
//
//    //      FIM    ABA 2
//    //      INICÍO ABA 3
//
//            if (is_null($this->getDescricaoTrabalhosArtigos()) or $this->getDescricaoTrabalhosArtigos() == "") {
//                $message .= "Descrição de trabalhos não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getPublicoAlvo()) or $this->getPublicoAlvo() == "") {
//                $message .= "Publico alvo não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getQuantidadeVagas) or $this->QuantidadeVagas() == "") {
//                $message .= "Quantidade de vagas do projeto não foi informado.<br>";
//                $return = false;
//            }
//            if (is_null($this->getProgramacao()) or $this->getProgramacao() == "") {
//                $message .= "Programação do não foi informado.<br>";
//                $return = false;
//            }
//    //        if (is_null($this->getOutrasInformacoes()) or $this->getOutrasInformacoes() == "") {
//    //            $message .= "Outras informações não foi informado.<br>";
//    //            $return = false;
//    //        }
//    //        if (is_null($this->getUrlProjeto()) or $this->getUrlProjeto() == "") {
//    //            $message .= "URL do Projeto não foi informado.<br>";
//    //            $return = false;
//    //        }
//    //        if (is_null($this->getLinkInstagram) or $this->getLinkInstagram() == "") {
//    //            $message .= "Link do Instagram não foi informado.<br>";
//    //            $return = false;
//    //        }
//    //        if (is_null($this->getLinkFacebook()) or $this->getLinkFacebook() == "") {
//    //            $message .= "Link do Facebook não foi informado.<br>";
//    //            $return = false;
//    //        }
//            if (is_null($this->getAceitaTermos()) or $this->getAceitaTermos() == "") {
//                $message .= "Para finalização é necessário aceite os Termos de compromisso.<br>";
//                $return = false;
//            }
//            if ($return == false) {
//                throw new Exception($message);
//                return false;
//            }
            return true;
        }
//      FIM    ABA 3
    public function validatorExcluir() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodProjeto()) or $this->getCodProjeto() == "") {
            $message .= "Código do desconto não foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }
}
?>