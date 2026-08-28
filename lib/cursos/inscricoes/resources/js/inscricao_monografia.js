//------------------------------------------------------------------------

function criticar() {

  if (document.form.autor_nome.value == ""){
      alert ("O campo Nome do Autor não está preenchido!");
      document.form.autor_nome.focus();
      return false ;
  }

  // testa se o email foi preenchido e se está no formato correto
  if (!isEmail(document.form.autor_e_mail.value)) {
    alert ("O email deve ser preenchido no formato correto (usuário@dominio).");
    document.form.autor_e_mail.focus();
    return false;
  }
  
  if (document.form.autor_pseudonimo.value == ""){
      alert ("O campo Pseudônimo do Autor não está preenchido!");
      document.form.autor_pseudonimo.focus();
      return false ;
  }



  if (document.form.instituicao_monografia.value == ""){
      alert ("O campo Instituição da Monografia não está preenchido!");
      document.form.instituicao_monografia.focus();
      return false ;
  }

  if (document.form.titulo_monografia.value == ""){
      alert ("O campo Titulo Monografia não está preenchido!");
      document.form.titulo_monografia.focus();
      return false ;
  }

  if (document.form.arquivo_upload.value == ""){
      alert ("É necessário anexar o arquivo da monografia!");
      document.form.arquivo_upload.focus();
      return false ;
  }

}
// -->