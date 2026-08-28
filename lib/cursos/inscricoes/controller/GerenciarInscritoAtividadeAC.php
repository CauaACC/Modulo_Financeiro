<?php

/**
 * Exclusao e recuperacao das atividades do inscrito (aba Atividades).
 *
 * Antes: o botao "Excluir" era um link GET direto para
 * inscritos-oficina-cadastro-excluir-grava.php -- sem pergunta, sem motivo,
 * sem quem clicou, e apagando pelo par inscrito+atividade, o que levava junto
 * TODAS as relacoes do inscrito naquela atividade (Participante,
 * Participante 2, Ministrante...). Nao havia como ver nem desfazer.
 *
 * Agora: modal com motivo obrigatorio, exclusao de UMA relacao (tipo_relacao
 * entra no WHERE) e tela de recuperacao, no mesmo padrao de Pedidos e
 * Pagamentos. Todo o SQL fica no InscritoOficinaDAO.
 */
class GerenciarInscritoAtividadeAC {

    /** Mesmo gate das demais telas do inscrito (view/admin/inscritos-seguranca.php). */
    public function autorizado() {
        $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : null;
        if(empty($codUsuario)){
            return false;
        }
        $codMsg       = false;
        $redirecionar = false;
        $codSistema   = defined('enumSistema::SIS_GERENCIAR_INSCRITO') ? enumSistema::SIS_GERENCIAR_INSCRITO : '13';
        return (@FWA_Autenticacao($codUsuario, $codSistema, 5, $codMsg, $redirecionar)===true)
            || (@FWA_Autenticacao($codUsuario, '13', 5, $codMsg, $redirecionar)===true);
    }

    public function guard() {
        if(!$this->autorizado()){
            $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
            $url = WB_URL_VIEW . "?content=view/admin/login.php";
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
            exit();
        }
    }

    /**
     * Exclusao vinda do modal da aba Atividades.
     * Rota: WB_URL_COMMAND & c=GerenciarInscritoAtividade & m=excluir (POST).
     */
    public function excluir($fws_, $debug=false) {
        // O config.php ja passou addslashes em tudo que veio por POST; o DAO
        // escapa de novo na hora de montar o SQL, entao aqui o valor volta ao cru.
        $codInscrito = (int)(isset($fws_['cod_inscrito']) ? $fws_['cod_inscrito'] : 0);
        $codOficina  = (int)(isset($fws_['cod_oficina'])  ? $fws_['cod_oficina']  : 0);
        $tipoRelacao = isset($fws_['tipo_relacao'])     ? stripslashes($fws_['tipo_relacao'])     : '';
        $motivo      = isset($fws_['motivo_exclusao'])  ? trim(stripslashes($fws_['motivo_exclusao'])) : '';

        $URL = WB_URL_VIEW . "?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=" . $codInscrito;

        // A ficha do inscrito imprime a MSG trocando so '--' por quebra de linha:
        // a frase vai com espacos normais, e '--' separa titulo de detalhe.
        $tipoMSG = 'erro';
        if(!$this->autorizado()){
            $MSG = "Erro--Usuário sem permissão para excluir atividades.";
        }elseif($codInscrito<=0 || $codOficina<=0){
            $MSG = "Erro--Atividade ou inscrito não informado.";
        }elseif($motivo==='' || mb_strlen($motivo)<5){
            $MSG = "Erro--Motivo da exclusão é obrigatório (mínimo 5 caracteres).";
        }else{
            // O evento e' o do inscrito; COD_EVENTO cobre o acesso pela raiz do evento.
            $codEvento = $this->codEventoDoInscrito($codInscrito);

            $dao = new InscritoOficinaDAO();
            $resultado = $dao->excluirComAuditoria($codEvento, $codInscrito, $codOficina, $tipoRelacao, $motivo, $debug);

            $tipoMSG = $resultado['ok'] ? 'sucesso' : 'erro';
            $MSG = ($resultado['ok'] ? "Operação executada com sucesso--" : "Erro--")
                 . $resultado['msg'];
        }

        if($debug){
            $_SESSION['MSG'] = $MSG;
            echo preg_replace('/--/', '<br>', $MSG);
            echo "<br><a href='{$URL}'>Continuar</a><br>";
            return;
        }

        // Post/Redirect/Get: a resposta do POST e' o redirecionamento, nao uma
        // tela de espera com o endereco a mostra.
        redirecionar($URL, $MSG, $tipoMSG);
    }

    /** Lista das atividades excluidas (tela de recuperacao). */
    public function listar($codEvento=null, $codInscrito=null, $debug=false) {
        $this->guard();
        $dao = new InscritoOficinaDAO();
        return $dao->listarExcluidos($codEvento, $codInscrito, $debug);
    }

    /** Quantas ainda podem voltar -- usado para so mostrar o botao quando ha algo. */
    public function contar($codEvento=null, $codInscrito=null, $debug=false) {
        $dao = new InscritoOficinaDAO();
        return $dao->contarExcluidos($codEvento, $codInscrito, $debug);
    }

    /** Devolve a atividade ao cadastro do inscrito. */
    public function restaurar($idExclusao, $debug=false) {
        $this->guard();
        $dao = new InscritoOficinaDAO();
        return $dao->restaurarExcluido($idExclusao, $debug);
    }

    /**
     * Evento do inscrito (fallback para COD_EVENTO), mesmo criterio usado na
     * exclusao de pedidos.
     */
    private function codEventoDoInscrito($codInscrito) {
        try {
            $objI = new Inscrito();
            $objI->setCodInscrito($codInscrito);
            $objI->getInscritoByIdSemEvento();
            if($objI->getCodEvento()!=''){
                return (int)$objI->getCodEvento();
            }
        } catch (Exception $e) { /* cai no COD_EVENTO */ }
        return (int)COD_EVENTO;
    }
}
