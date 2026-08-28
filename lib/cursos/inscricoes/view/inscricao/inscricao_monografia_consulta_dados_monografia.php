<?php
If($Selecionou_Curso == 1){
  #echo $Selecionou_Curso;
  #--------------------------------------------------------------
  #Inicio Dados Monografia
  echo "<fieldset width='500'><legend> Dados da Monografia </legend>";

  If ((@$_SESSION['titulo_monografia']!="")and(@$_SESSION['titulo_monografia']!=" ")){

  #echo "<blockquote>";

echo <<<corpo
      <br>
      <TABLE WIDTH='500' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
        <TR><TD VALIGN='top'>
              Nome do Autor: <b>{$_SESSION['autor_nome']}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              E-mail do Autor: <b>{$_SESSION['autor_e_mail']}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              PseudÃ´nimo do Autor: <b>{$_SESSION['autor_pseudonimo']}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Instituição: <b>{$_SESSION['instituicao_monografia']}</b>
        </TD></TR>
        <TR><TD VALIGN='top' class='textop'>
              Titulo da Monografia: <b>{$_SESSION['titulo_monografia']}</b>
        </TD></TR>
        <TR><TD VALIGN='top' class='textop'>
              Nome do Arquivo da Monografia Anexada: <b>{$arquivo_name}</b>
        </TD></TR>
      </TABLE><br>
corpo;

      if($_SESSION['content']!="view/inscricao/inscricao_monografia_confirmar.php"){
      echo "
      <blockquote>
      <a href='index.php?content=view/inscricao/inscricao_monografia_cadastro_dados_monografia.php' style='TEXT-DECORATION: none'  ><!--img src='img/back.gif'  border=0--> Para Alterar Monografia, clique aqui.</a>
      </blockquote>
      <br>
      ";
      }


}Else{#session['titulo_monografia']
  if($_SESSION['content']!="view/inscricao/inscricao_monografia_confirmar.php"){
  echo "<blockquote><a href='index.php?content=view/inscricao/inscricao_monografia_cadastro_dados_monografia.php' style='TEXT-DECORATION: none'  ><!--img src='img/back.gif'  border=0--> Para Cadastrar Monografia, clique aqui</a><br><br></blockquote>";
  }
}
  echo "</fieldset>";

}#Selecionou =1
?>