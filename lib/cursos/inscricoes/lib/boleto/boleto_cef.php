<?php
// +----------------------------------------------------------------------+
// | BoletoPhp - Vers�o Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo est� dispon�vel sob a Licen�a GPL dispon�vel pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Voc� deve ter recebido uma c�pia da GNU Public License junto com     |
// | esse pacote; se n�o, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colabora��es de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de Jo�o Prado Maia e Pablo Martins F. Costa                |
// |                                                                      |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordena��o Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto CEF: Elizeu Alcantara                         |
// +----------------------------------------------------------------------+


// ------------------------- DADOS DIN�MICOS DO SEU CLIENTE PARA A GERA��O DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formul�rio c/ POST, GET ou de BD (MySql,Postgre,etc)	//

// DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento = 3;
$taxa_boleto = 0;//2.95;
$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias  OU  informe data: "13/04/2006"  OU  informe "" se Contra Apresentacao;
$valor_cobrado = "5,00"; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(",", ".",$valor_cobrado);
$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

$dadosboleto["inicio_nosso_numero"] = "24";           // 24 - Padr�o da Caixa Economica Federal
$dadosboleto["nosso_numero"]        = "00000001";     // Nosso numero sem o DV - REGRA: M�ximo de 8 caracteres!
$dadosboleto["numero_documento"]    = "01.003189.01"; // Num do pedido ou do documento
$dadosboleto["data_vencimento"]     = $data_venc;     // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"]      = date("d/m/Y");  // Data de emiss�o do Boleto
$dadosboleto["data_processamento"]  = date("d/m/Y");  // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"]        = $valor_boleto;  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"]    = "Zaqueu Costeski Crosati";   
$dadosboleto["endereco1"] = "Av. Madre Leonia Milito 2000";
$dadosboleto["endereco2"] = "Cidade - Estado -  CEP: 00000-000";

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = "Pagamento de Inscrição em Congresso";
$dadosboleto["demonstrativo2"] = "Referente a inscrição - R$ ".number_format($taxa_boleto, 2, ',', '');
$dadosboleto["demonstrativo3"] = "";;

// INSTRU��ES PARA O CAIXA
$dadosboleto["instrucoes1"] = "- Sr. Caixa, não cobrar multa ou juros após o vencimento";
$dadosboleto["instrucoes2"] = "- Receber até 2 dias após o vencimento";
$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: boletos@opctec.com.br";
$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema de inscrição - www.opctec.com.br";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"]     = "1";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"]         = "A";		
$dadosboleto["especie"]        = "R$";
$dadosboleto["especie_doc"]    = "";
// ---------------------- DADOS FIXOS DE CONFIGURA��O DO SEU BOLETO --------------- //
// DADOS DA SUA CONTA - CEF
$dadosboleto["agencia"]  = "3076"; // Num da agencia, sem digito
$dadosboleto["conta"]    = "3288";   // Num da conta, sem digito
$dadosboleto["conta_dv"] = "4";   // Digito do Num da conta

// DADOS PERSONALIZADOS - CEF
//$dadosboleto["conta_cedente"] = "87000000414"; // ContaCedente do Cliente, sem digito (Somente N�meros)
$dadosboleto["conta_cedente"]   = "00100003288"; // ContaCedente do Cliente, sem digito (Somente N�meros)
//$dadosboleto["conta_cedente"] = "00000841993"; // ContaCedente do Cliente, sem digito (Somente N�meros)
$dadosboleto["conta_cedente_dv"]= ""; // Digito da ContaCedente do Cliente
$dadosboleto["carteira"] = "SR";  // C�digo da Carteira: pode ser SR (Sem Registro) ou CR (Com Registro) - (Confirmar com gerente qual usar)

// SEUS DADOS
$dadosboleto["identificacao"] = "Congresso Brasileiro de Melhoramento de Plantas";
$dadosboleto["cpf_cnpj"]      = "01603004955";
$dadosboleto["endereco"]      = "Coloque o endereço da sua empresa aqui";
$dadosboleto["cidade_uf"]     = "Cidade / Estado";
$dadosboleto["cedente"]       = "Congresso Brasileiro de Melhoramento de Plantas";

// N�O ALTERAR!
include("include/funcoes_cef.php"); 
include("include/layout_cef.php");
?>
<script>
    window.focus();
</script>