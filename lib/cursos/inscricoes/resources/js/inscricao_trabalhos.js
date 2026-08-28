
//------------------------------------------------------------------------

function criticar_tudo() {

  if (document.form.cod_area_tematica.value==""){
    alert("Para submeter trabalho é necessário selecionar uma Área Temática.")
    redireciona_url_selecionar_area_tematica();
    return false ;
  }else{
    criticar_trabalho();
  }

  alert("Após a submissão de seu trabalho os dados do mesmo não poderão ser alterados.");

  if (confirm("Clique em OK para confirmar a submissão de seu trabalho.")){

  }else{
    return false ;
  }

}//criticar_tudo()



function redireciona_url_selecionar_area_tematica(){
  window.location.href="index.php?content=inscricao_seleciona_area_tematica.php";
}

function redireciona_url_dados_trabalho(){
  window.location.href="index.php?content=inscricao_cadastro_dados_trabalho.php";
}

function criticar_trabalho() {

  if (document.form.autor_apresentador_nome.value==""){
      alert ("O campo Nome Autor Apresentador não está preenchido!");
      redireciona_url_dados_trabalho()
      document.form.autor_apresentador_nome.focus();
      return false ;
  }

  // testa se o email foi preenchido e se está no formato correto
  if (!isEmail(document.form.autor_apresentador_e_mail.value)) {
    alert ("O E-mail deve ser preenchido no formato correto (usuário@dominio).");
    redireciona_url_dados_trabalho()
    document.form.autor_apresentador_e_mail.focus();
    return false;
  }

  if (document.form.instituicao_trabalho.value==""){
      alert ("O campo Instituição Trabalho não está preenchido!");
      redireciona_url_dados_trabalho()
      document.form.instituicao_trabalho.focus();
      return false ;
  }

  if (document.form.titulo_trabalho.value==""){
      alert ("O campo Titulo Trabalho não está preenchido!");
      redireciona_url_dados_trabalho()
      document.form.titulo_trabalho.focus();
      return false ;
  }

  if (document.form.palavras_chave.value==""){
      alert ("O campo Palavras-Chave não está preenchido!");
      redireciona_url_dados_trabalho()
      document.form.palavras_chave.focus();
      return false ;
  }

  if (document.form.resumo_trabalho.value==""){
      alert ("O campo Resumo Trabalho não está preenchido!");
      redireciona_url_dados_trabalho()
      document.form.resumo_trabalho.focus();
      return false ;
  }


}
function criticarTrabalho9CBMP() {

    if (document.form.titulo_trabalho.value==""){
      alert ("O campo Titulo Trabalho não está preenchido!");
      document.form.titulo_trabalho.focus();
      return false ;
  }

  if (document.form.palavras_chave.value==""){
      alert ("O campo Palavras-Chave não está preenchido!");
      document.form.palavras_chave.focus();
      return false ;
  }

  if (document.form.arquivo.value==""){
      //alert ("O campo Arquivo do Trabalho não foi anexado !");
      if(confirm('Deseja continuar alteração sem anexar novo arquivo?')){
          return true;
      }else{
          document.form.arquivo.focus();
          return false ;
       }
  }


}
// -->

  