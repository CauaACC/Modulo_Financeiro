<?php

class GerenciarEventoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    /**
     * Duplica um projeto inteiro. O sucesso do Evento::duplicar() chega aqui
     * como OKException -- e nao como retorno --, por isso os dois catch.
     *
     * A resposta e SEMPRE JSON, inclusive quando o erro e' um Error do PHP:
     * a tela que chama fica com "Duplicando..." ate receber alguma coisa, e
     * ficar sem resposta era o sintoma relatado em 2026-08-20.
     */
    public function duplicarEvento($fws_, $debug=false){
        $statusExcec = 0;
        $codEvento   = '';
        $mensagem    = 'Não foi possível duplicar o projeto.';
        try{
            if(trim($fws_['codEvento'])==''){
                throw new Exception("Código do projeto não informado");
            }
            /* Lista de cod_usuario que a modal manteve marcados. Vazia = so
             * quem duplicou. Chega como "1,2,3" para caber num POST simples. */
            $escolhidos = array();
            if (isset($fws_['usuariosImportar']) && trim($fws_['usuariosImportar']) !== '') {
                foreach (explode(',', $fws_['usuariosImportar']) as $cod) {
                    $cod = (int) trim($cod);
                    if ($cod > 0) { $escolhidos[] = $cod; }
                }
            }
            $obj = new Evento();
            $obj->setCodEvento($fws_['codEvento']);
            $obj->duplicar($debug, $escolhidos);
        }catch (OKException $exc) {
            $statusExcec = 1;
            $mensagem    = $exc->getMessage();
            $codEvento   = $obj->getCodEvento();
        }catch (Throwable $exc) {
            /* Throwable, e nao Exception: TypeError e Error tambem precisam
             * virar resposta. Capturados por Exception, escapavam para o
             * CommandAC e a tela nunca saia do "Duplicando...". */
            $statusExcec = 0;
            $mensagem    = $exc->getMessage();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'codEvento'     => (string) $codEvento,
            'message'       => '<b>'.$mensagem.'</b>',
            'statusMessage' => (string) $statusExcec
        ));
        exit();
    }

    /**
     * Lista quem PODE ser trazido do projeto atual para a copia.
     *
     * A modal chama antes de duplicar, para o usuario conferir nome e e-mail e
     * desmarcar quem nao deve ir.
     */
    public function listarUsuariosImportacao($fws_, $debug=false){
        $lista = array();
        $statusExcec = 0;
        $mensagem = '';
        try{
            $cod = (int) $fws_['codEvento'];
            if ($cod <= 0) {
                throw new Exception("Código do projeto não informado");
            }
            $objU = new UsuarioEvento();
            $lista = $objU->listarCandidatosImportacao($cod, $debug);
            $statusExcec = 1;
        }catch (Throwable $exc) {
            $statusExcec = 0;
            $mensagem = $exc->getMessage();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => (string) $statusExcec,
            'message'       => $mensagem,
            'usuarios'      => $lista
        ));
        exit();
    }

    /**
     * Importa a configuracao de OUTRO projeto para o projeto atual.
     *
     * O destino nao vem do POST de proposito: e sempre COD_EVENTO, o projeto em
     * que a tela esta aberta. Assim uma requisicao forjada nao consegue
     * sobrescrever a configuracao de um projeto qualquer.
     */
    public function importarConfiguracao($fws_, $debug=false){
        $statusExcec = 0;
        $mensagem    = 'Não foi possível importar a configuração.';
        try{
            $codOrigem = trim($fws_['codEventoOrigem']);
            if($codOrigem==''){
                throw new Exception("Escolha o projeto de origem.");
            }

            /* A origem tem de ser do mesmo gestor. O <select> da tela ja lista
             * so esses, mas quem manda o POST nao e obrigado a usar o select. */
            $objEvOrigem = new Evento();
            $objEvOrigem->setCodEvento($codOrigem);
            $objEvOrigem->getEventoById();
            if(strtolower($objEvOrigem->getGestor()) != strtolower(WB_GRUPO)){
                throw new Exception("Projeto de origem não pertence a este gestor.");
            }

            $obj = new Evento();
            $obj->setCodEvento(COD_EVENTO);
            $obj->importarConfiguracaoDe($codOrigem, $debug);
        }catch (OKException $exc) {
            $statusExcec = 1;
            $mensagem    = $exc->getMessage();
        }catch (Throwable $exc) {
            $statusExcec = 0;
            $mensagem    = $exc->getMessage();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'message'       => '<b>'.$mensagem.'</b>',
            'statusMessage' => (string) $statusExcec
        ));
        exit();
    }
}
