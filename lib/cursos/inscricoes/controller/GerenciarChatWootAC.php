<?php

class GerenciarChatWootAC
{
    public function __construct()
    {
        // Webhook /chatwoot/localizarcontato roda via router; nesse caso o gate e pulado.
        if (!defined('FWA_API_EXEC')) { FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL); }
        // Inicialização da classe
    }

//     public function localizarContato($fws_, $debug = false)
//     {
//         
// <!DOCTYPE html>
// <html>
// <head>
//   <meta charset="UTF-8">
//   <title>Redirecionando...</title>
// </head>
// <body>
//   <h2>Dados recebidos do Chatwoot</h2>
//   <pre id="output">Aguardando dados...</pre>

//   <script>
//     window.addEventListener("message", function(event) {
//       try {
//         const context = JSON.parse(event.data);
//         document.getElementById("output").innerText = JSON.stringify(context, null, 2);
//       } catch (e) {
//         document.getElementById("output").innerText = "Erro ao processar os dados";
//       }
//     });
//   </script>
// </body>
// </html>

//         
//     }

    public function localizarContato($fws_, $debug = false)
    {
        ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Disparar Formulário com Dados do Chatwoot</title>
  <script>
window.addEventListener("message", function(event) {
  try {
    const context = JSON.parse(event.data);
    // console.log("📥 Dados recebidos do Chatwoot:", context);

    const dados = context.data || {};
    const telefoneRaw = dados.contact?.phone_number || '';
    const nome = dados.contact?.name || '';
    // console.log("📞 Telefone bruto:", telefoneRaw);

    const telefoneLimpo = limparTelefone(telefoneRaw);
    // console.log("✅ Telefone limpo:", telefoneLimpo);

    // Exibe nome e telefone na página
    document.getElementById("info").innerText = `👤 ${nome}\n📞 ${telefoneRaw}`;

    // Cria o formulário
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'https://opctec.net.br/hutec/admin/inscricoes/index.php?content=view/admin/evento-cadastro-listar.php#rsltdoListaInscritos';
    form.target = '_blank';
    form.style.display = 'none';
    form.id = 'formAuto';

    // Cria o campo de telefone
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'fone_contato';
    input.value = telefoneLimpo;

    form.appendChild(input);
    document.body.appendChild(form);

    // Ativa botão
    document.getElementById("btnEnviar").disabled = false;

  } catch (e) {
    // console.error("❌ Erro ao processar os dados:", e);
  }
});

function limparTelefone(fone) {
  let apenasNumeros = fone.replace(/\D/g, '');
  if (apenasNumeros.startsWith('55')) {
    apenasNumeros = apenasNumeros.slice(2);
  }
  if (apenasNumeros.length > 9) {
    apenasNumeros = apenasNumeros.slice(2);
  }
  return apenasNumeros;
}

function enviarFormulario() {
  document.getElementById("formAuto").submit();
}
  </script>
</head>
<body>
  <p id="info">Carregando integração...</p>
  <button id="btnEnviar" onclick="enviarFormulario()" disabled>🔍 Localizar inscrições</button>
</body>
</html>


        <?php
    }


}