//------------------------------------------------------------------------

function criticar() {


  if (document.form.COD_CONVIDADO.value == "-1"){
      alert ("É necessario assossiar o login a um convidado!");
      document.form.COD_CONVIDADO.focus();
      return false ;
  }

  if (document.form.login.value == ""){
      alert ("O campo Login não está preenchido!");
      document.form.login.focus();
      return false ;
  }

  if (document.form.senha.value == ""){
      alert ("O campo Senha não está preenchido!");
      document.form.senha.focus();
      return false ;
  }
}
// -->

