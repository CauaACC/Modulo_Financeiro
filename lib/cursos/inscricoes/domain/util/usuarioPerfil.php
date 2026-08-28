<?php
/*
 * FIM DO VERIFICA PERFIL VIA SISTEMA UEL
 * /
/*
require_once("class/perfil.php");
$perfil = array(array());
$perfil['cod_usuario']  = $_SESSION['_cod_usuario'];
$perfil['sig_sistema']  = "ERGON";
$perfil['sig_subsist']  = "HE";
$perfil['nom_aplicacao']= "HEINI01";
$conn = fwaDBConnect();
//$conn->debug=true;
$per = new Perfil();
if($per->validaPerfil($perfil,$conn)!=""){
    echo $per->validaPerfil($perfil,$conn);
    exit;
}
unset($per);
$conn->close;
*/

/*
 * VERIFICA PERFIL VIA CÃDIGO PHP
 */
/*
 * 2600715 Zaqueu
 * 0302538 Carlinhos
 * 0603178 Fabio Cotrim
 * 1605127 PEDRO HENRIQUE LAPERUTA
 * 0304574 Cristiane Medina
 * 2203725 Tina
 * 0113620 Cida
 * 0305826 Cristianne Cordeiro
 *
 * 1329622 Marcelo Altafin
 * 1907940 Sandra Quadros
 * 2206751 Vinicius Oliveira
 * 1208820 Leonardo Mota
 * 189.90.65.11 Leticia
 *
 * 1313511 Elizabeth do SEBEC
 *
 * Grupos:
 * ps: crei grupos e os associe chapas a estes conforme o tipo de acesso, separando-as por hifen
 * $fwsWeb             = "-0-";
 * $fwsSomenteLeitura  = "-0-";
 * $fwsPublico         = "-0-"
 * $fwsGads            = "-0-"
 * $fwsSuporte         = "-0-";
 * $fwsGerentes        = "-0-";
 * $fwsAdministradores = "-0-";
 * $fwsGeral           = "-0-";
*/

# $fwsDes = Analistas Desenvolvedor
# $fwsAdm = Administradores do Sistema
# $fwsUsu = Usuários Utilizadores do Sistema
/*
 * Para bloquear acesso pelo ip, cadastre o número aqui.
 */
$FWS_IP_LIBERADO = "189.90.70.566-189.90.70.51-0";

//Defini modo de manutenção
$FWS_MANUTENCAO = false;
/*Avaliadores de proposta*/
$fwsAval = "";
/*Coordenadores Responsáveis pelo Evento/Curso*/
$fwsCoordResp = "";
if($fws_['Logado']['Logged']){
    /*
     * Verifica todas as categorias para verificar se existe a categoria 1
     * (Docente)
     */
//    if($IP=='189.90.70.56'){
//        $fws_['Logado']['categorias']="3,1";
//        echo $fws_['Logado']['categorias']."<hr>";
//    }
    $listaCategorias = explode(",", $fws_['Logado']['categorias']);
//    if($IP=='189.90.70.56'){
//        echo $listaCategorias."<hr>";
//    }
     foreach ($listaCategorias as $categoria) {
//        if($IP=='189.90.70.56'){
//            echo __LINE__."<br>";
//        }
        if($categoria == "1") {
//        if($IP=='189.90.70.56'){
//            echo __LINE__."<br>";
//        }

            $fwsCoordResp= "-".$fws_['Logado']['chapa']."-";
        }
    }

    /*Verificando se o usuario logado é responsável por algun evento*/
    if((!$fwsChapasFuncionariosResp)and(!$fwsCoordResp)){
        $objPerfilCoord = new InscCoordenadores();
        $objPerfilCoord->setCdFuncionarioResp($fws_['Logado']['chapa']);
        $coordenador = $objPerfilCoord->verificaPerfilCoordenador();
        if($coordenador){
            $fwsChapasFuncionariosResp = "-".$objPerfilCoord->getCdFuncionarioResp()."-";
        }
        unset($objPerfilCoord);
        $fwsCoordResp = $fwsChapasFuncionariosResp;
    }
    /*Verificando se o usuario logado é avaliador de alguma instancia*/
    if(!$fwsChapaAvaliador){
        $objAv = new InscAvaliador();
        $objAv->setCdFuncionario($fws_['Logado']['chapa']);
        $objAv->verificaPerfilAvaliador(false);
        if($objAv->getAvaliador()=='1'){
            $fwsAval = "-".$objAv->getCdFuncionario()."-";
        }
    }
}
/*Desenvolvedores*/
$fwsDes = "-1329622-1907940-5004147-2600715-";
/*Supervisor Administrativo do Setor*/
$fwsSupAdm = "-1802604-0302538-1017170-2203725-2600715-";
/*Coordenadores de eventos com diretor a administrar dados e inscritos e outros*/
$fwsCoordRespPlus = "-1810308-2205421-0802500-";
/*Tecnico Administrativo do Setor*/
$fwsTecAdm = "-1912550-1605127-0603178-0304574-0701622-";
/*Usuarios apenas consultas*/
$fwsUsu = "-";

/*
 * Definindo chapas por perfil, ou seja, grupos de usuario,
 *  por agrupamento de sub-grupos de chapas.
 */
if($FWS_MANUTENCAO){
    //Privilégios liberados durante a manutenção
    $ChapasTestarSistema      = $fwsDes;
    $ChapasDesenvolvedores    = $fwsDes;
    $ChapasAdministrarSistema = $fwsDes;
    $ChapasGerenciarEventos   = $fwsDes;
    $ChapasCoordenadoresPlus      = $fwsDes;
    $ChapasCoordenadores      = $fwsDes;
    $ChapasAvaliadores        = $fwsDes;
    $ChapasConsultarEventos   = $fwsDes;
    $ChapasLogarComo          = $fwsDes;
    $FWS_LIBERADO_POR_CHAPA_PARA_MSG_DEBUG = "-2600715-0-";
}else{
    //Privilégios em modo normal
    $ChapasTestarSistema      = $fwsDes;
    $ChapasDesenvolvedores    = $fwsDes;
    $ChapasAdministrarSistema = $fwsDes."%".$fwsSupAdm;
    $ChapasGerenciarEventos   = $fwsDes."%".$fwsSupAdm."%".$fwsTecAdm;
    $ChapasCoordenadoresPlus      = $fwsCoordRespPlus."%".$fwsSupAdm;
    $ChapasCoordenadores      = $fwsCoordResp."%".$fwsCoordRespPlus."%".$fwsSupAdm;
    $ChapasAvaliadores        = $fwsAval;
    $ChapasConsultarEventos   = $fwsDes."%".$fwsSupAdm."%".$fwsTecAdm."%".$fwsUsu;
    $ChapasLogarComo          = $fwsDes."%".$fwsSupAdm."%".$fwsTecAdm;
}
/*Para chapa diferente da chapa do Carlinhos/Proex*/
//if(($fwsLogado['chapa']!='0302538')and($fwsLogado['chapa']!='0305826')){
//    if(($IP!='189.90.70.56')and($IP!='10.90.10.85')and($IP!='10.90.12.96')){
//        $ChapasAvaliadores   = "";
//        $ChapasCoordenadores = "";
//    }
//}
//if($IP=='189.90.70.56'){
//    $ChapasCoordenadores = $ChapasCoordenadores."-1313511-";
////    echo $ChapasCoordenadores."<hr>";
////    echo $fwsLogado['chapa'];
//}

/*
 * Removendo dois ifens concatenados, isso pode permitir acesso indevido a direitos
 * de perfis.
 * Ã bom trocar essa sistematica por arrays, assim evita esse problema.
 * $loc = "--";
 * $sub = "-";
 * str_replace($loc, $subs, $ChapasFuncionarioResp);
 */

$loc = "--";
$sub = "-";

$ChapasTestarSistema      = str_replace($loc, $sub, $ChapasTestarSistema);
$ChapasDesenvolvedores    = str_replace($loc, $sub, $ChapasDesenvolvedores);
$ChapasAdministrarSistema = str_replace($loc, $sub, $ChapasAdministrarSistema);
$ChapasGerenciarEventos   = str_replace($loc, $sub, $ChapasGerenciarEventos);
$ChapasCoordenadores      = str_replace($loc, $sub, $ChapasCoordenadores);
$ChapasCoordenadoresPlus      = str_replace($loc, $sub, $ChapasCoordenadoresPlus);
$ChapasAvaliadores        = str_replace($loc, $sub, $ChapasAvaliadores);
$ChapasConsultarEventos   = str_replace($loc, $sub, $ChapasConsultarEventos);
$ChapasLogarComo          = str_replace($loc, $sub, $ChapasLogarComo);


//Nega todos privilégios
$testarSistema      = false;
$desenvolvedorSistema = false;
$administrarSistema = false;
$gerenciarEventos   = false;
$coordenarEventos   = false;
$coordenarEventosPlus   = false;
$avaliarEventos     = false;
$consultarEventos   = false;
$consultarLogarComo = false;

$_SESSION['priv']= 
array ('testarSistema' => false,
       'desenvolvedorSistema' => false,
       'administrarSistema' => false,
       'gerenciarEventos' => false,
       'coordenarEventos' => false,
       'coordenarEventosPlus' => false,
       'avaliarEventos' => false,
       'consultarEventos' => false,
       'consultarLogarComo' => false
    );

/*
 * Verifica diretos de acesso, testando se a chapa do logado esta contida
 *  na lista de chapas por perfil
 */
if($fwsLogado["Logged"]){
    if(stristr($ChapasTestarSistema, "-".$fwsLogado['chapa']."-")){
        $testarSistema=true;
    }
    if(stristr($ChapasDesenvolvedores, "-".$fwsLogado['chapa']."-")){
        $desenvolvedorSistema=true;
    }
    if(stristr($ChapasAdministrarSistema, "-".$fwsLogado['chapa']."-")){
        $administrarSistema=true;
    }
    if(stristr($ChapasGerenciarEventos, "-".$fwsLogado['chapa']."-")){
        $gerenciarEventos=true;
    }
    if(stristr($ChapasCoordenadores, "-".$fwsLogado['chapa']."-")){
        $coordenarEventos=true;
    }
    if(stristr($ChapasCoordenadoresPlus, "-".$fwsLogado['chapa']."-")){
        $coordenarEventosPlus=true;
    }
    if(stristr($ChapasAvaliadores, "-".$fwsLogado['chapa']."-")){
        $avaliarEventos=true;
    }
    if(stristr($ChapasConsultarEventos, "-".$fwsLogado['chapa']."-")){
        $consultarEventos=true;
    }
    if(stristr($ChapasLogarComo, "-".$_SESSION['_cod_usuario']."-")){
        $consultarLogarComo=true;
    }

    /**
     * Seta um array na sessão com as permissões
     * do usuário logado no sistema;
     */
    $_SESSION['priv']= 
    array ('testarSistema' => $testarSistema,
           'desenvolvedorSistema' => $desenvolvedorSistema,
           'administrarSistema' => $administrarSistema,
           'gerenciarEventos' => $gerenciarEventos,
           'coordenarEventos' => $coordenarEventos ,
           'coordenarEventosPlus' => $coordenarEventosPlus ,
           'avaliarEventos' => $avaliarEventos ,
           'consultarEventos' => $consultarEventos,
           'consultarLogarComo' => $consultarLogarComo
            );

    /*
     * Carregando privilegios do usuario logado
     * A partir do banco de dados mysql
     */
    //$_SESSION['priv'] = NULL;
    try{
        $objPU = new SisPerfilUsuario();
        $objPU->setCodVinculo($fwsLogado['chapa']);
        $objPU->setStatus('ATIVADO');
        $lazy = true;
        $objPU->carregarListaPerfilUsuarioLogado($debug,$lazy);
        $lista = $objPU->getListaPerfilUsuarioLogado();
        foreach ($lista as $objL) {
            $arrayRecursos = explode(',', $objL->getNomeRecurso());
            foreach ($arrayRecursos as $recurso) {
                $_SESSION['priv'][$recurso] = '1';
                $$recurso = 1;
            }
        }
        
        // se codigo perfil = administrador (2), guardar o código da subunidade.
        $objPU->setCodPerfil(2);
        //$debug = true;
        $objPU->carregarListaSubunidUsuarioByIdPerfil($objPU, $debug);
    } catch (Exception $e) { }
}
?>