<?php

Function FWA_DireitoSobreSistema($cod_usuario, $cod_sistema, $cod_direito, $cod_evento=false) {
    $debug = false;
//    if($_SERVER['REMOTE_ADDR']=='200.203.142.25'){
////        echo '-'.$cod_evento.'-<br>';
//        $debug=true;
//    }
    if($debug){
        echo $cod_usuario."<-u<br>";
        echo $cod_direito."<-d<br>";
        echo $cod_sistema."<-s<br>";
    }
    if($cod_evento==false){
        echo $debug? __line__.'-'.$cod_evento.'-' : '';
        $cod_evento = COD_EVENTO; 
    }
    echo $debug? __line__.'-'.$cod_evento.'-' : '';

    if ((@$cod_usuario != "") AND (@$cod_sistema != "") AND (@$cod_direito)) {
        echo $debug? __line__.'-'.$cod_evento.'-' : '';

        #require("FWA_Conexao.php");
        #Fazendo conexão com o banco
        $Conexao_db = FWA_Conexao('OpcTec');
        // 'TODOS' = verificar permissao em qualquer evento (sem filtro de evento especifico)
        if ($cod_evento === 'TODOS') {
            $filtroEvento = "(lug.cod_evento = dgs.cod_evento) and";
        } else {
            $filtroEvento = "(lug.cod_evento = " . $cod_evento . ") and
       (lug.cod_evento = dgs.cod_evento) and";
        }

        $var_sql0 = "
     SELECT
       u.cod_usuario,
       g.cod_grupo,
       s.cod_sistema
     FROM
       sis_usuarios as u,
       sis_ligacao_usuarios_grupos as lug,
       sis_grupos as g,
       sis_direitos_grupos_sistemas as dgs,
       sis_sistemas as s
     WHERE
       (u.cod_usuario   = " . $cod_usuario . ")and
       (u.cod_usuario   = lug.cod_usuario) and
       " . $filtroEvento . "
       (lug.cod_grupo   = dgs.cod_grupo)   and
       (dgs.cod_direito = " . $cod_direito . ")and
       (dgs.cod_sistema = " . $cod_sistema . ")and
       (lug.cod_grupo   = g.cod_grupo)     and
       (dgs.cod_sistema = s.cod_sistema)
    ";

    // if($_SERVER['REMOTE_ADDR']=='200.203.142.25'){
    //    echo '-'.$cod_evento.'-<br>';
    //    $Conexao_db->debug=true;
    //    echo $var_sql0;
    //    exit();
    // }
        #Fim da definição do comando SQL
        #Executando a consulta no banco SQL
        $RS0 = $Conexao_db->Execute($var_sql0);

        if (!$RS0->EOF) {

            $_SESSION['_cod_usuario'] = $RS0->fields[0];
            $_SESSION['_cod_grupo'] = $RS0->fields[1];
            $_SESSION['_cod_sistema'] = $RS0->fields[2];

            #echo $_SESSION['_cod_usuario']."<br>";
            #echo $_SESSION['_cod_grupo']."<br>";
            #echo $_SESSION['_cod_sistema']."<br>";

            return true;
            #echo 1;
        } else {
            #echo "Usuário logado, porém não possui direito sobre este sistema!<br>";
            return false;
            #exit;
            #break;
        }
    } else {
        #echo "Os parâ¢mentros de cod do sistema e ou cod do direito não forma informados!<br>";
        return false;
        #exit;
        #break;
    }
}
