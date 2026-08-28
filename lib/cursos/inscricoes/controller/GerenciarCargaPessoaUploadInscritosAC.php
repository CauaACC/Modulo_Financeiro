<?php
class GerenciarCargaPessoaUploadInscritosAC {

    function __construct() {
//        ControleAcesso::ifUserInRole('gerenciarEventos,coordenarEventos', 'url');
    }

    public static function carregarInscrito($arrayColunas, $objConf, $objAr, $objEv){

        if($arrayColunas[0]==''){
            throw new Exception("Nome não informado");
        }
        if($arrayColunas[1]==''){
            throw new Exception("CPF não informado");
        }
        $email = trim($arrayColunas[2]);
        if($email==''){
            throw new Exception("E-mail não informado");
        }      
        if(!validarEmail($email)){
            throw new Exception("E-mail inválido");
        }
        
        /*Definindo codigo da forma de cargapessoa a ser associado*/
        if($objAr->getTipoArquivo()=='INSCRITOS'){
            /*criar inscrito para a cargapessoa*/
            $objI = new Inscrito();
            $objI->setCodEvento($objAr->getCodEvento()); 
            $objI->setCodArquivo($objAr->getCodArquivo());
            $objI->setCodInscrito(NULL);
            $objI->setCodPerfil($objAr->getCodPerfil());
            $objI->setCpf(formatCpfInsert(limparCpf($arrayColunas[1])));
            $objI->setNomeInscrito(trim($arrayColunas[0]));
            $objI->setEMail($email);
            $objI->setStatus('ATIVADA');
            /* Sem isto o registro nasce com origem_cadastro VAZIO e o inscrito
               nao consegue logar: a consulta de login exige
               origem_cadastro='INSCRICAO' (InscritoDAO::efetuarLogin).
               O DEFAULT da coluna nao resolve, porque o insert e montado com
               todas as colunas (GetInsertSQL) e manda o campo vazio explicito.
               salvarCarga() usa validatorObjCarga(), que -- ao contrario do
               validatorObj() da inscricao normal -- nao preenche este campo. */
            $objI->setOrigemCadastro('INSCRICAO');
            $objI->salvarCarga();
        }
        
    }
    
    public static function associarCargaPessoa($arrayColunas, $objConf, $objAr,$debug=false){

        /**QUANDO NÃO PRECISA ASSOCIAR A NADA o restante do código não executa*/
        throw new OKException("Não é preciso associar");

        // if($arrayColunas[0]==''){
        //     throw new Exception("Nome não informado");
        // }
        // if($arrayColunas[1]==''){
        //     throw new Exception("CPF não informado");
        // }
        // $email = trim($arrayColunas[2]);
        // if($email==''){
        //     throw new Exception("E-mail não informado");
        // }      
        // if(!validarEmail($email)){
        //     throw new Exception("E-mail inválido");
        // }
        // /*Definindo codigo da forma de cargapessoa a ser associado*/
        // if($objAr->getTipoArquivo()=='LOGIN'){
        //     /*criar inscrito para a cargapessoa*/
        //     $objI = new Inscrito();
        //     $objI->setCodEvento($objAr->getCodEvento()); 
        //     $objI->setCodArquivo($objAr->getCodArquivo());
        //     $objI->setEMail($email);
        //     if($objI->getByEmailExistePopular($debug)){
        //         #-----------------------------------
        //         try {
        //             $objLogin = new Usuario();
        //             $objLogin->setLogin($arrayColunas[2]);
        //             $objLogin->setSenha(geraSenha());
        //             $objLogin->setNome($arrayColunas[0]);
        //             $objLogin->getUsuarioByLogin($debug);
        //             if ($objLogin->getCodUsuario()==''){
        //                 echo $debug? 'Login não existe-' : '';
        //                 $objLogin->salvar($debug);
        //             }else{
        //                 echo $debug? 'Login existe-('.$objLogin->getCodUsuario().')' : '';
        //                 throw new OKException('Login já cadastrado');
        //             }
        //         } catch (OKException $exc) {
        //             echo $debug? 'verificar associacao-' : '';
        //             /*veriricar se usuario associado a evento*/
        //             $objUE = new UsuarioEvento();
        //             $objUE->setCodEvento(COD_EVENTO);
        //             $objUE->setCodUsuario($objLogin->getCodUsuario());
        //             $objUE->getUsuarioEventoById($debug);
        //             if($objUE->getStatus()=='ATIVADA'){
        //                 /*já esta associado*/
        //                 echo $debug? 'já esta associado-' : '';
        //                 $MSG .= 'Login '.$objLogin->getLogin().' já esta associado ao evento-';
        //                 @$FWA_Execucao = true;
        //             }else{
        //                 echo $debug? 'não esta associado-' : '';
        //                 try {
        //                     echo $debug? 'adicionando ao evento-' : '';
        //                     $objUE->adicionarUsuarioEvento($debug);
        //                 } catch (OKException $exc) {
        //                     echo $debug? 'adicionado '.$objLogin->getLogin().'-' : '';
        //                 }
        //             }
        //             try {
        //                 /*criando ou associando a convidado*/
        //                 $MSG .= $exc->getMessage();
        //                 $objConv = new Convidado();
        //                 $objConv->setEMail($objLogin->getLogin());
        //                 /**@todo aqui é preciso na verdade verificar se o cod_usuario tem ligação com a tabela convidado (dados pessoais) se não tiver criar um convidado;
        //                  * também precisaria desativar os cadastros
        //                  * é bom que todos os convidados encontrados com e-mail sejam atualizados para o mesmo usuario ou desativados para inserir um novo
        //                  */
        //                 // if($objConv->getCodConvidado()!=''){
        //                 /**Exite um convidado com e-mail do login */
        //                 if($objConv->getConvidadoByEmail($debug)){
        //                     echo $debug? 'Email '.$objLogin->getLogin().' já cadastrado como convidado - ' : '';

        //                     if($objConv->getCodUsuario()=='' or $objConv->getCodUsuario()=='0'){
        //                         echo $debug? 'atualizando dados pessoias '.$objLogin->getLogin().'-' : '';
        //                         $objConv->setCodUsuario($objLogin->getCodUsuario());
        //                         $objConv->salvar($debug);
        //                     }
        //                     if($objConv->getCodUsuario()==$objLogin->getCodUsuario()){
        //                         echo $debug? 'já login já possui dados pessoias '.$objLogin->getLogin().'-' : '';
        //                         throw new OKException('Email '.$objLogin->getLogin().' já cadastrado como convidado');
        //                     }else{
        //                         throw new OKException('Email '.$objLogin->getLogin().' já criado mas não associado');
        //                     }
        //                 }else{
        //                     echo $debug? 'insere dados pessoias '.$objLogin->getLogin().'-' : '';
        //                     $objConv->setNomeConvidado($objLogin->getNome());
        //                     $objConv->setCodUsuario($objLogin->getCodUsuario());
        //                     $objConv->salvar($debug);
        //                 }
        //             } catch (OKException $exc) {
        //                 /** @todo ajustar para acrescentar em  grupos somente quando solicitado na carga
        //                  * inserindo no grupo de avaliadores */
        //                 try {
        //                     $objGU = new UsuarioGrupo();
        //                     $objGU->setCodUsuario($objLogin->getCodUsuario());
        //                     /** grupo 4 para avaliadores */
        //                     $objGU->setCodGrupo(4);
        //                     // $objGU->removerUsuarioGrupo();
        //                     $objGU->adicionarUsuarioGrupo();
        //                 } catch (OKException $exc) { }
        //                 @$FWA_Execucao = true;
        //                 $MSG .= $exc->getMessage();
        //             } catch (Exception $exc) {
        //                 $MSG .=$exc->getMessage();
        //                 $FWA_Execucao = false;
        //             }
        //         } catch (Exception $exc) {
        //             $MSG .=$exc->getMessage();
        //             $FWA_Execucao = false;
        //         }
        //         if($FWA_Execucao==true){
        //             /*Enviar email com a senha*/
        //             if ($fws_['enviar_aviso'] =='ON' and $objLogin->getCodConvidado()!=''){
        //                 $objEmail = new GerenciarEmailAC();
        //                 $fws_['cod_convidado'] = $objConv->getCodConvidado();
        //                 $objEmail->enviarEmailLoginConvidado($fws_, $debug);
        //             }
        //         }
        //     }else{
        //         throw new Exception("E-mail ".$objI->getEMail()." não encontrado");
        //     }
    }
}

