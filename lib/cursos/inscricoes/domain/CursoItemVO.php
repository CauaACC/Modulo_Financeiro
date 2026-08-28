<?php

class CursoItemVO extends PadraoVO {

    public function validatorSalvar() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getTitulo()) or $this->getTitulo() == "") {
            $message .= "Título não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getTipo()) or $this->getTipo() == "") {
            $message .= "Tipo não foi informado.<br>";
            $return = false;
        }
        $tiposValidos = array('CAPITULO','VIDEO','PDF','MEET','SLIDES','TEXTO','TAREFA','LINK','FORMULARIO');
        if (!in_array($this->getTipo(), $tiposValidos)) {
            $message .= "Tipo inválido.<br>";
            $return = false;
        }
        if ($this->getHoraInicio() && $this->getHoraFim()
            && strtotime($this->getHoraInicio()) >= strtotime($this->getHoraFim())) {
            $message .= "Hora de início deve ser menor que a hora de fim.<br>";
            $return = false;
        }
        $intMin = (int) $this->getIntervaloConfirmacaoMin();
        $intMax = (int) $this->getIntervaloConfirmacaoMax();
        if ($intMin > 0 && $intMax > 0 && $intMin > $intMax) {
            $message .= "Intervalo mínimo de confirmação deve ser menor ou igual ao máximo.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorExcluir() {
        if (is_null($this->getCodCursoItem()) or $this->getCodCursoItem() == "") {
            throw new Exception("Código do item não informado.");
        }
        return true;
    }

    public function estaDentroDaJanela($agora = null) {
        if ($agora === null) {
            $agora = time();
        }
        $hoje = date('Y-m-d', $agora);
        $horaAtual = date('H:i:s', $agora);
        if ($this->getDtLiberacao() && $this->getDtLiberacao() > $hoje) {
            return false;
        }
        if ($this->getHoraInicio() && $horaAtual < $this->getHoraInicio()) {
            return false;
        }
        if ($this->getHoraFim() && $horaAtual > $this->getHoraFim()) {
            return false;
        }
        return true;
    }

    public function getDescricaoTipo() {
        $mapa = array(
            'VIDEO'      => 'Vídeo',
            'PDF'        => 'PDF',
            'MEET'       => 'Reunião',
            'SLIDES'     => 'Slides',
            'TEXTO'      => 'Texto',
            'TAREFA'     => 'Tarefa',
            'LINK'       => 'Link',
            'FORMULARIO' => 'Formulário',
        );
        $t = $this->getTipo();
        return isset($mapa[$t]) ? $mapa[$t] : $t;
    }

    public function detectarTipoEmbed() {
        $url = $this->getUrl();
        if (!$url) return null;
        if (stripos($url, 'vimeo.com') !== false) return 'vimeo';
        if (stripos($url, 'youtube.com') !== false || stripos($url, 'youtu.be') !== false) return 'youtube';
        return 'outro';
    }

    public function getUrlEmbed() {
        $url = trim((string) $this->getUrl());
        if ($url === '') return '';
        $tipo = $this->detectarTipoEmbed();
        if ($tipo === 'vimeo') {
            if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
                return 'https://player.vimeo.com/video/' . $m[1];
            }
        }
        if ($tipo === 'youtube') {
            if (preg_match('~youtu\.be/([A-Za-z0-9_-]+)~', $url, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1] . '?enablejsapi=1';
            }
            if (preg_match('~youtube\.com/.*[?&]v=([A-Za-z0-9_-]+)~', $url, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1] . '?enablejsapi=1';
            }
            if (preg_match('~youtube\.com/embed/([A-Za-z0-9_-]+)~', $url, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1] . '?enablejsapi=1';
            }
        }
        return $url;
    }
}
