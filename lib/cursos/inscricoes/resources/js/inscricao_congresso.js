
//------------------------------------------------------------------------
 
function criticar_tudo() {

  if (document.form.cod_oficina.value==""){
    if (confirm ("Nenhuma Oficina foi selecionada, deseja continuar.")){
      //alert("Continuando")
    }else{
      //alert("Parando")
      redireciona_url_selecionar_oficina();
      return false ;
    }
  } 
      criticar_pessoais();
  
      alert("Após a confirmação de seus dados, os mesmos não poderão ser alterados.");

      if (confirm("Clique em OK para confirmar sua inscrição.")){

      }else{
        return false ;
      }  
  
 
}//criticar_tudo()

function redireciona_url_selecionar_oficina(){
  window.location.href="index.php?content=inscricao_congresso_seleciona_oficina.php";
}

function redireciona_url_dados_pessoais(){
  window.location.href="index.php?content=inscricao_congresso_cadastro_dados_pessoais.php";
}

function criticar_pessoais() {

  if (document.form.nome_inscrito.value==""){
      alert ("O campo Nome de seus dados pessoais, não está preenchido corretamente!");
      redireciona_url_dados_pessoais();
      //document.form.nome_inscrito.focus();
      return false ;
  }

  // testa se o cpf foi preenchido e se está no formato correto
  if (!isCpf(document.form.cpf.value)) {
    alert ("O CPF não foi preenchido ou não possui um formato valido.");
    redireciona_url_dados_pessoais();
    //document.form.cpf.focus();
    return false;
  }

  if (document.form.rg.value==""){
      alert ("O campo RG não está preenchido!");
      redireciona_url_dados_pessoais();
      //document.form.rg.focus();
      return false ;
  }

  // testa se o email foi preenchido e se está no formato correto
  if (!isEmail(document.form.e_mail.value)) {
    alert ("O email deve ser preenchido no formato correto (usuário@dominio).");
    redireciona_url_dados_pessoais();
    document.form.e_mail.focus();
    return false;
  }

  if (document.form.data_nascimento.value==""){
      alert ("O campo Data de Nascimento não está preenchido!");
      redireciona_url_dados_pessoais();
      document.form.data_nascimento.focus();
      return false ;
  }

  if (document.form.sexo.value==""){
    alert ("É campo Sexo deve ser preenchido.");
    redireciona_url_dados_pessoais();
    document.form.sexo[0].focus();
    return false;
  }

  if (document.form.endereco.value==""){
      alert ("O campo Rua não está preenchido!");
      redireciona_url_dados_pessoais();
      document.form.endereco.focus();
      return false ;
  }

  if (document.form.numero.value==""){
      alert ("O campo Número não está preenchido!");
      redireciona_url_dados_pessoais();
      document.form.numero.focus();
      return false ;
  }
  if (document.form.bairro.value==""){
      alert ("O campo Bairro não está preenchido!");
      redireciona_url_dados_pessoais();
      document.form.bairro.focus();
      return false ;
  }
  if (document.form.cidade.value== ""){
      alert ("O campo Cidade não está preenchido!");
      redireciona_url_dados_pessoais();
      document.form.cidade.focus();
      return false ;
  }
  if (document.form.estado.value=="-1"){
      alert ("O campo Estado não está preenchido!");
      redireciona_url_dados_pessoais();
      document.form.estado.focus();
      return false ;
  }
  if (document.form.fone_residencial.value==""){
      alert ("O campo Telefone não está preenchido!");
      redireciona_url_dados_pessoais();
      document.form.fone_residencial.focus();
      return false ;
  }
}

// -->

