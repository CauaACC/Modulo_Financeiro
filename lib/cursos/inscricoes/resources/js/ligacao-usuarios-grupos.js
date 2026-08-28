//------------------------------------------------------------------------

function criticar() {


  if (document.form.COD_USUARIO.value == "-1"){
      alert ("É necessario selecionar um LOGIN!");
      document.form.COD_USUARIO.focus();
      return false ;
  }

  if (document.form.COD_GRUPO.value == "-1"){
      alert ("É necessario associar uma GRUPO!");
      document.form.COD_GRUPO.focus();
      return false ;
  }

}
// -->

