<?php
// LEGADO — substituido por GerenciarPedidoAC::excluir() (rota WB_URL_COMMAND&c=GerenciarPedido&m=excluir).
// Mantido vazio para que algum link antigo nao quebre o servidor; pode ser deletado.
//
// require_once(dirname(__FILE__)."/../../include/config.php");
// require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
// #-------------------------------------------
// $FWS_Debug = false;
// $FWS_ = processPostArray($FWS_Debug);
// # Aceita também GET (compat) para cod_pedido / cod_inscrito; motivo SEMPRE vem por POST do modal
// if (empty($FWS_['cod_pedido']) && isset($_GET['cod_pedido'])) {
//     $FWS_['cod_pedido']   = $_GET['cod_pedido'];
//     $FWS_['cod_inscrito'] = $_GET['cod_inscrito'] ?? '';
// }
// #-------------------------------------------
// $cod_pedido   = (int)($FWS_['cod_pedido']   ?? 0);
// $cod_inscrito = (int)($FWS_['cod_inscrito'] ?? 0);
// $motivo       = trim($FWS_['motivo_exclusao'] ?? '');
// #-------------------------------------------
// $objI = new Inscrito();
// $objI->setCodInscrito($cod_inscrito);
// $objI->getInscritoByIdSemEvento();
// if($objI->getCodEvento()!=''){
//     $cod_evento = (int)$objI->getCodEvento();
// }else{
//     $cod_evento = (int)COD_EVENTO;
// }
//
// If (!$Conexao_db = FWA_Conexao("OpcTec")) {
//     $MSG     = "Erro-de-Conexao";
//     $_SESSION['MSG']=$MSG;
//     $CONTENT = "view/admin/inscritos-cadastro-ok.php";
//     $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;
//
// }ElseIf ($cod_pedido<=0) {
//     $MSG     = "Erro--Pedido-nao-informado";
//     $_SESSION['MSG']=$MSG;
//     $CONTENT = "view/admin/inscritos-cadastro-ok.php";
//     $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;
//
// }ElseIf ($motivo==='' || mb_strlen($motivo)<5) {
//     $MSG     = "Motivo-da-exclusao-obrigatorio--Minimo-5-caracteres";
//     $_SESSION['MSG']=$MSG;
//     $CONTENT = "view/admin/inscritos-cadastro-ok.php";
//     $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;
//
// }Else{
//
//     # Lista colunas de pedidos para INSERT explicito (pedidos_atualizados ganhou
//     # colunas extras de auditoria e o antigo `INSERT...SELECT *` deixou de bater)
//     $colunas = array();
//     $rsCols  = FWA_Executa_SQL($FWS_Debug,$Conexao_db,"SHOW COLUMNS FROM pedidos");
//     if($rsCols){
//         foreach($rsCols as $rowCol){
//             $colunas[] = $rowCol['Field'];
//         }
//     }
//
//     if(empty($colunas)){
//         $MSG     = "Erro-de-Execucao-ERRO-SQL-Listagem-Colunas-Pedidos";
//         $_SESSION['MSG']=$MSG;
//         $CONTENT = "view/admin/inscritos-cadastro-ok.php";
//         $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_pedido=".$cod_pedido."&cod_inscrito=".$cod_inscrito;
//     }else{
//
//         $listaCol     = '`'.implode('`,`', $colunas).'`';
//         $usuario_acao = (int)($_SESSION['_cod_usuario'] ?? 0);
//         $ip_cliente   = addslashes($_SERVER['REMOTE_ADDR'] ?? '');
//         $motivoEsc    = addslashes($motivo);
//
//         $var_sql = "
//                    INSERT INTO pedidos_atualizados ($listaCol, tipo_acao, motivo_acao, usuario_acao, ip_cliente_acao, data_acao)
//                    SELECT $listaCol, 'EXCLUSAO', '$motivoEsc', $usuario_acao, '$ip_cliente', NOW()
//                    FROM pedidos
//                    WHERE (id=$cod_pedido) and (evento_id=$cod_evento)
//                  ";
//
//         If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
//             $FWS_Execucao = true;
//         }Else{
//             $FWS_Execucao=false;
//             $MSG     = "Erro-de-Execucao-ERRO-SQL-Backup-Pedido";
//             $_SESSION['MSG']=$MSG;
//             $CONTENT = "view/admin/inscritos-cadastro-ok.php";
//             $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pedido=".$cod_pedido."&cod_inscrito=".$cod_inscrito;
//         }
//
//         If($FWS_Execucao==true){
//             $var_sql = "
//                       DELETE FROM pedidos
//                       WHERE (id=$cod_pedido) and (evento_id=$cod_evento)
//                      ";
//
//             If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
//                 $MSG     = "Operação-Executada-com-Sucesso";
//                 $_SESSION['MSG']=$MSG;
//                 $CONTENT = "view/admin/inscritos-cadastro-ok.php";
//                 $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pedido=".$cod_pedido."&cod_inscrito=".$cod_inscrito;
//             }Else{
//                 $MSG     = "Erro-de-Execucao-ERRO-SQL-Delete-Pedido";
//                 $_SESSION['MSG']=$MSG;
//                 $CONTENT = "view/admin/inscritos-cadastro-ok.php";
//                 $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_pedido=".$cod_pedido."&cod_inscrito=".$cod_inscrito;
//             }
//         }
//     }
//
// }
// #--------------------------------------------------------------
// $MSG_Imprimir = $MSG;
//
// $loc   = "/--/";
// $subst = "<br>";
// $MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
//
// $loc   = "/-/";
// $subst = " ";
// $MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
// #--------------------------------------------------------------
// If($FWS_Debug==true){
//   echo $MSG_Imprimir;
//   echo "<br><a href='{$URL}'>{$URL}</a><br>";
// }Else{
//   echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
//   echo "<br>Gravando seus dados no sistema<br>";
//   echo "<br>Este procedimento demora poucos segundos.<br>";
//   echo "<br><a href='{$URL}'>{$URL}</a><br>";
//   echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
// }
