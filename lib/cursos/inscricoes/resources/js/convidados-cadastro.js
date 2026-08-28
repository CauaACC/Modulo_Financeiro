//------------------------------------------------------------------------

function criticarConvidado() {

//  if (document.form.cod_atuacao_convidado.value == ""){
//      alert ("O campo Área de Atuação não está preenchido!");
//      document.form.cod_atuacao_convidado.focus();
//      return false ;
//  }

  if (document.form.nome_convidado.value == ""){
      alert ("O campo Nome não está preenchido!");
      document.form.nome_convidado.focus();
      return false ;
  }

  // testa se o cpf foi preenchido e se está no formato correto
  if (!isCpf(document.form.cpf.value)) {
    alert ("O CPF não foi preenchido ou não possui um formato valido.");
    document.form.cpf.focus();
    return false;
  }

//  if (document.form.rg.value == ""){
//      alert ("O campo RG não está preenchido!");
//      document.form.rg.focus();
//      return false ;
//  }

  // testa se o email foi preenchido e se está no formato correto
  if (!isEmail(document.form.e_mail.value)) {
    alert ("O email deve ser preenchido no formato correto (usuário@dominio).");
    document.form.e_mail.focus();
    return false;
  }

//  if (document.form.data_nascimento.value == ""){
//      alert ("O campo Data de Nascimento não está preenchido!");
//      document.form.data_nascimento.focus();
//      return false ;
//  }
//
//  if (getCheckedRadioButton(document.form.sexo) == -1){
//    alert ("É campo Sexo deve ser preenchido.");
//    document.form.sexo[0].focus();
//    return false;
//  }
//
//  if (document.form.endereco.value == ""){
//      alert ("O campo Rua não está preenchido!");
//      document.form.endereco.focus();
//      return false ;
//  }
//  if (document.form.numero.value == ""){
//      alert ("O campo Número não está preenchido!");
//      document.form.numero.focus();
//      return false ;
//  }
//  if (document.form.bairro.value == ""){
//      alert ("O campo Bairro não está preenchido!");
//      document.form.bairro.focus();
//      return false ;
//  }
//  if (document.form.cidade.value== ""){
//      alert ("O campo Cidade não está preenchido!");
//      document.form.cidade.focus();
//      return false ;
//  }
//  if (document.form.cep.value== ""){
//      alert ("O campo Cep não está preenchido!");
//      document.form.cep.focus();
//      return false ;
//  }
//  if (document.form.estado.value== "-1"){
//      alert ("O campo Estado não está preenchido!");
//      document.form.estado.focus();
//      return false ;
//  }
  if (document.form.fone_residencial.value== ""){
      alert ("O campo Telefone não está preenchido!");
      document.form.fone_residencial.focus();
      return false ;
  }
}
// -->

