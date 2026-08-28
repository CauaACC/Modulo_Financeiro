<?php
require_once 'Image/QRCode.php'; // Inclua o caminho correto da biblioteca

function gerarPixQrCode($chavePix, $nomeRecebedor, $cidadeRecebedor, $valor = 0.0, $txid = null, $nomePagador = null)
{
    // Truncar o nome do pagador para 25 caracteres, caso seja passado
    if ($nomePagador) {
        $nomePagador = substr($nomePagador, 0, 25);
    }
    
    // Formato do padrão do QR Code do PIX
    $pixData = "000201"
             . "010211" // Payload Format Indicator e Merchant Account Information
             . "26580014BR.GOV.BCB.PIX0114" . $chavePix // Chave PIX
             . "52040000" // Transação tipo 0
             . "5303986" // Moeda BRL
             . "5802BR" // País BR
             . "5913" . $nomeRecebedor // Nome do recebedor
             . "6009" . $cidadeRecebedor // Cidade do recebedor
             . "62070503***" // Código de transação (opcional)
             . ($valor > 0 ? "5405" . str_pad(number_format($valor, 2, '', ''), 13, '0', STR_PAD_LEFT) : "") // Valor opcional
             . ($txid ? "6212$txid" : "") // Transaction ID opcional
             . ($nomePagador ? "6262" . $nomePagador : "") // Nome do pagador incluído no campo `txid` opcionalmente
             . "6304"; // CRC16 calculado depois

    // Função para calcular o CRC16 do PIX
    $pixData .= crc16($pixData);

    // Gerando o QR Code com o payload PIX usando a biblioteca Image_QRCode
    $qrCode = new Image_QRCode();
    $options = array(
        'image_type' => 'png', // Definindo para gerar um PNG
        'output_type' => 'inline', // Exibe diretamente no navegador
        'error_correct' => 'H', // Correção de erro máxima (PIX requer alta correção)
        'module_size' => 4 // Tamanho dos módulos do QR Code
    );
    
    header('Content-Type: image/png');
    $qrCode->makeCode($pixData, $options); // Gera o QR Code com o payload PIX
}

// Função para calcular o CRC16 para PIX
function crc16($data)
{
    $polynomial = 0x1021;
    $crc = 0xFFFF;

    for ($i = 0; $i < strlen($data); $i++) {
        $crc ^= (ord($data[$i]) << 8);
        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ $polynomial;
            } else {
                $crc <<= 1;
            }
        }
    }

    return strtoupper(dechex($crc & 0xFFFF));
}

// Exemplo de uso:
$chavePix = 'seu-email@dominio.com';
$nomeRecebedor = 'Fulano de Tal';
$cidadeRecebedor = 'SAO PAULO';
$valor = 100.50;
$txid = 'ID0001'; // Opcional
$nomePagador = 'João Silva'; // Nome do pagador opcional

gerarPixQrCode($chavePix, $nomeRecebedor, $cidadeRecebedor, $valor, $txid, $nomePagador);





// URL da API de consulta PIX Gerencianet
$url = "https://api-pix.gerencianet.com.br/v2/cob/txid-do-pix";

// Autenticação na API
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {SEU_TOKEN}",
    "Content-Type: application/json"
]);

// Faz a requisição para consultar o pagamento
$response = curl_exec($ch);
curl_close($ch);

// Decodifica a resposta
$data = json_decode($response, true);

// Verifica se o PIX foi pago
if (isset($data['status']) && $data['status'] == 'CONCLUIDA') {
    echo "O PIX foi pago.";
} else {
    echo "O PIX ainda não foi pago.";
}
