<!--

//------------------------------------------------------------------------
// Testa os campos

function criticar() {

  //alert (document.form.NUMERO_FICHA.value);

  //se o trabalho foi avaliado
  if (getCheckedRadioButton(document.form.avaliacao)==-1){
    alert("Você deve avaliar o trabalho, selecionando um dos itens: Aprovado ou Reprovado.");
    document.form.avaliacao[0].focus();
    return false;
  }
  
  if(document.form.avaliacao[0].checked){
    var_avaliacao = "Aprovação";
  }
  if(document.form.avaliacao[1].checked){
    var_avaliacao = "Reprovação";
    if (document.form.observacao.value == ""){
        alert ("Em caso de "+var_avaliacao+" a justificativa deve ser preenchida.");
        document.form.observacao.focus();
        return false ;  }
  }
  if(document.form.avaliacao[2].checked){
    var_avaliacao = "Devolução";
    if (document.form.observacao.value == ""){
        alert ("Em caso de "+var_avaliacao+" a justificativa deve ser preenchida.");
        document.form.observacao.focus();
        return false ;  }
  }

  if (confirm("Clique em OK para confirmar a "+ var_avaliacao +" deste trabalho.")){

  }else{
     return false ;
  }  
}

// -->

