//------------------------------------------------------------------------

function criticar() {


  if (document.form.COD_CONVIDADO.value == "-1"){
      alert ("É necessario selecionar um CONVIDADO!");
      document.form.COD_CONVIDADO.focus();
      return false ;
  }

  if (document.form.COD_AREA_TEMATICA.value == "-1"){
      alert ("É necessario associar uma AREA-TEMATICA!");
      document.form.COD_AREA_TEMATICA.focus();
      return false ;
  }

}
// -->

