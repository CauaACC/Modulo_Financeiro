<?php
// LEGADO — a exclusao de atividade do inscrito passou para
// GerenciarInscritoAtividadeAC::excluir() (rota WB_URL_COMMAND&c=GerenciarInscritoAtividade&m=excluir),
// chamada pelo modal de confirmacao da aba Atividades.
//
// Este arquivo era um link GET: bastava a URL para apagar a ligacao, sem
// pergunta e sem motivo — e apagava pelo par inscrito+atividade, levando junto
// TODAS as relacoes do inscrito naquela atividade (Participante, Participante 2,
// Ministrante...), porque o tipo_relacao ficava de fora do WHERE.
//
// Continua existindo so para link antigo/favorito nao virar erro de servidor:
// nao exclui nada, apenas devolve o usuario para a ficha do inscrito.

ob_start();
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");

$cod_inscrito = isset($fwsGet_['cod_inscrito']) ? (int)$fwsGet_['cod_inscrito'] : 0;

$URL = WB_URL_VIEW."?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=".$cod_inscrito;

redirecionar(
    $URL,
    "Aviso--A exclusão de atividade agora é feita pelo botão Excluir da aba Atividades, que pede confirmação e o motivo. Nada foi excluído.",
    'aviso'
);
