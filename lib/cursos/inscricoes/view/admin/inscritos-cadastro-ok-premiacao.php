<?php
#---- Busca Premiação ------------------------------------
/*
 * O <fieldset><legend>Premiacao</legend> estava DENTRO do laco: com duas
 * monografias saiam dois fieldsets aninhados, cada um com sua legenda. Agora o
 * bloco abre uma vez e cada monografia e um cartao.
 */
if($objEvConf->getTrabSubmissao()==1){
    $var_sql = "
    SELECT
     top.titulo
    ,ti.cod_inscrito
    ,ti.nome_inscrito
    ,tm.autor_nome
    ,tm.autor_e_mail
    ,tm.autor_pseudonimo
    ,tm.instituicao_monografia
    ,tm.titulo_monografia
    ,tm.arquivo_upload
    FROM tab_monografias tm, tab_inscritos ti, tab_opcoes_premiacoes_cientifica top
    WHERE (tm.cod_evento               = ".$cod_evento.")AND
          (tm.cod_inscrito             = ".$cod_inscrito.")AND
          (tm.cod_inscrito             = ti.cod_inscrito)AND
          (tm.cod_premiacao_cientifica = top.cod_premiacao_cientifica)
    ORDER BY top.titulo
    ";

    If ($RS_P=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)) {
      If (!$RS_P->EOF) {
        echo "<fieldset><legend> Premiação </legend>";
        While (!$RS_P->EOF) {
          #Adicionando nas variaveis, os valores dos campos retirados dos registros
          $titulo                 = $RS_P->fields[0];
          $cod_inscrito           = $RS_P->fields[1];
          $nome_inscrito          = $RS_P->fields[2];
          $autor_nome             = $RS_P->fields[3];
          $autor_e_mail           = $RS_P->fields[4];
          $autor_pseudonimo       = $RS_P->fields[5];
          $instituicao_monografia = $RS_P->fields[6];
          $titulo_monografia      = $RS_P->fields[7];
          $arquivo_upload         = $RS_P->fields[8];

          $Var_caminho_arquivo = "upload/monografia/".$cod_evento."-".$cod_inscrito.".doc";

          echo "<div class='card-item'>";

          echo "<div class='card-cab'>";
          echo   "<span class='card-titulo'>" . htmlspecialchars($titulo_monografia, ENT_QUOTES, 'UTF-8') . "</span>";
          echo   "<span class='card-badge card-badge-info'>" . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . "</span>";
          echo "</div>";

          echo "<div class='card-grid'>";
          echo montarCampo('Premiação',           $titulo);
          echo montarCampo('Nome do autor',       $autor_nome);
          echo montarCampo('E-mail do autor',     $autor_e_mail);
          echo montarCampo('Pseudônimo do autor', $autor_pseudonimo);
          echo montarCampo('Instituição',         $instituicao_monografia);
          echo montarCampo('Título da monografia',$titulo_monografia, false, true);
          echo montarCampo('Arquivo anexado',     $arquivo_upload, false, true);
          echo "</div>";

          echo "<div class='card-acoes'>";
          echo   "<a class='btn btn-sm btn-primary' href='".$Var_caminho_arquivo."' target='_blank'>"
               . "<i class='glyphicon glyphicon-file'></i> Abrir arquivo da monografia</a>";
          echo "</div>";

          echo "</div>";  // .card-item
          #Movendo para o proximo registro
          $RS_P->MoveNext();
        }
        echo "</fieldset>";
      }
    }
}
