<?php
If($Selecionou_Curso == 1){

  #--------------------------------------------------------------
  #Inicio Dados Trabalho
  echo "<fieldset width='500'><legend> Dados do Trabalho </legend>";

  If ((@$_SESSION['titulo_trabalho']!="")and(@$_SESSION['titulo_trabalho']!=" ")){

  #echo "<blockquote>";

  echo <<<corpo
      <br>
      <TABLE WIDTH='500' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
        <TR><TD VALIGN='top'>
              Nome do Apresentador: <b>{$_SESSION['autor_apresentador_nome']}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              E-mail do Apresentador: <b>{$_SESSION['autor_apresentador_e_mail']}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Nomes dos Co-Autores:
          </TD>  
        </TR>
        <TR><TD VALIGN='top'>1º - Co-autor: <b>{$_SESSION['nome_co_autor_1']}</b></TD></TR>
        <TR><TD VALIGN='top'>2º - Co-autor: <b>{$_SESSION['nome_co_autor_2']}</b></TD></TR>
        <TR><TD VALIGN='top'>3º - Co-autor: <b>{$_SESSION['nome_co_autor_3']}</b></TD></TR>
        <TR><TD VALIGN='top'>4º - Co-autor: <b>{$_SESSION['nome_co_autor_4']}</b></TD></TR>
        <TR><TD VALIGN='top'>5º - Co-autor: <b>{$_SESSION['nome_co_autor_5']}</b></TD></TR>
        <TR><TD VALIGN='top'>6º - Co-autor: <b>{$_SESSION['nome_co_autor_6']}</b></TD></TR>
        
        <TR><TD VALIGN='top'>
              Instituição: <b>{$_SESSION['instituicao_trabalho']}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Titulo do Trabalho: <b>{$_SESSION['titulo_trabalho']}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Palavras-chave: <b>{$_SESSION['palavras_chave']}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Resumo do Trabalho:<br><br>

              <b>{$_SESSION['resumo_trabalho']}</b>
        </TD></TR>
      </TABLE><br>
corpo;

      if($_SESSION['content']!="view/inscricao/inscricao_trabalhos_confirmar.php"){
      echo "
      <blockquote>
      <a href='index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php' style='TEXT-DECORATION: none'  >
      <!--img src='img/back.gif'  border=0--> Para alterar Dados do Trabalho, clique aqui</a>
      </blockquote>
      <br>
      ";
      }


}Else{#session['nome_trabalho']
  if($_SESSION['content']!="view/inscricao/inscricao_trabalhos_confirmar.php"){
  echo "<blockquote><a href='index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php' style='TEXT-DECORATION: none'  >
      <!--img src='img/back.gif'  border=0-->Para Cadastrar Dados do Trabalho, clique aqui</a><br><br></blockquote>";
  }
}
  echo "</fieldset>";

}#Selecionou =1
?>