<?php

class GerenciarBoletoWooCommerce {
    
    public function criarPedido($fws_, $debug=false){
        // Instanciar a classe de interação com a API
        $woocommerce_api = new WooCommerceAPIInteraction('seu_consumer_key', 'seu_consumer_secret', 'https://sua-loja.com');

        // Dados do pedido
        $order_data = [
            'status' => 'pending', // O status pode ser ajustado conforme necessário
            'billing' => [
                'first_name' => 'Nome do Cliente',
                'last_name' => 'Sobrenome do Cliente',
                'email' => 'email@example.com',
                'address_1' => 'Endereço do Cliente',
                'city' => 'Cidade',
                'state' => 'Estado',
                'postcode' => 'CEP',
                'country' => 'País',
                'phone' => 'Telefone do Cliente',
            ],
            'line_items' => [
                [
                    'product_id' => 456, // ID do produto
                    'quantity' => 1,
                ],
            ],
        ];

        // Criar o pedido na WooCommerce
        $result = $woocommerce_api->createOrder($order_data);

        if (isset($result['error'])) {
            echo 'Erro ao criar pedido: ' . $result['error'];
        } else {
            // Redirecionar o cliente para a página de checkout do WooCommerce
            $checkout_url = $result['_links']['checkout'][0]['href'];
            wp_redirect($checkout_url);
            //atualizar a url do pagamento do pedido
            exit;
        }
    }

    public function consultarPedido($fws_, $debug=false){
        // Instanciar a classe de interação com a API
        $woocommerce_api = new WooCommerceAPIInteraction('seu_consumer_key', 'seu_consumer_secret', 'https://sua-loja.com');

        // Consultar o status do pedido
        $order_id = 123; // Substitua pelo ID do pedido real
        $order_status = $woocommerce_api->getOrderStatus($order_id);
        //atualizar o status do pedido no sistema
        // Exibir informações de status
        echo 'Status do Pedido: ' . $order_status['order_status'] . '<br>';
        echo 'Situação de Pagamento: ' . $order_status['payment_status'];
    }
}


class WooCommerceAPI {
    private $consumer_key;
    private $consumer_secret;
    private $base_url;

    public function __construct($consumer_key, $consumer_secret, $base_url) {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->base_url = $base_url;
    }

    // Método para fazer uma requisição à API do WooCommerce
    private function makeRequest($endpoint, $method = 'GET', $data = array()) {
        // Inicializar a sessão cURL
        $ch = curl_init();

        // Configurar as opções da requisição
        $url = $this->base_url . $endpoint;
        $headers = array(
            'Authorization: Basic ' . base64_encode($this->consumer_key . ':' . $this->consumer_secret),
            'Content-Type: application/json',
        );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Executar a requisição e obter a resposta
        $response = curl_exec($ch);

        // Verificar se houve algum erro
        if ($response === false) {
            throw new Exception(curl_error($ch));
        }

        // Fechar a sessão cURL
        curl_close($ch);

        // Retornar a resposta decodificada como um array associativo
        return json_decode($response, true);
    }

    // Método para buscar um produto pelo nome
    private function findProductByName($product_name) {
        $endpoint = '/wp-json/wc/v3/products';
        $params = array(
            'search' => $product_name,
        );
        $response = $this->makeRequest($endpoint, 'GET', $params);
        return !empty($response) ? $response[0] : null;
    }

    // Método para criar um produto se ele não existir
    private function createProductIfNotExists($product_name, $product_data) {
        $existing_product = $this->findProductByName($product_name);
        if (!$existing_product) {
            $endpoint = '/wp-json/wc/v3/products';
            $product_data['name'] = $product_name;
            return $this->makeRequest($endpoint, 'POST', $product_data);
        }
        return $existing_product;
    }

    // Método para criar um pedido
    public function createOrder($customer_data, $order_data, $product_name, $product_data) {
        // Verificar se o produto existe, senão, criá-lo
        $product = $this->createProductIfNotExists($product_name, $product_data);
        $order_data['line_items'] = array(
            array(
                'product_id' => $product['id'],
                'quantity' => 1,
            ),
        );

        // Endpoint para criar um pedido
        $endpoint = '/wp-json/wc/v3/orders';

        // Fazer uma requisição POST para criar o pedido
        $order_response = $this->makeRequest($endpoint, 'POST', $order_data);

        // Obter o link de pagamento do pedido
        $payment_link = isset($order_response['checkout_url']) ? $order_response['checkout_url'] : '';

        return $payment_link;
    }

    // Adicione outros métodos conforme necessário para interagir com outros recursos da API
}

// Exemplo de uso:
$consumer_key = 'sua_chave_de_consumidor';
$consumer_secret = 'seu_segredo_do_consumidor';
$base_url = 'https://www.exemplo.com'; // URL base da sua loja WooCommerce

$woocommerce_api = new WooCommerceAPI($consumer_key, $consumer_secret, $base_url);

// Dados do cliente
$customer_data = array(
    'first_name' => 'João',
    'last_name' => 'Silva',
    'email' => 'joao@example.com',
    'phone' => '(11) 91234-5678',
    // Adicione mais informações do cliente conforme necessário
);

// Dados do pedido
$order_data = array(
    'customer_id' => 1, // ID do cliente
    'payment_method' => 'bacs', // Método de pagamento (transferência bancária)
    'payment_method_title' => 'Transferência Bancária',
    'set_paid' => true, // Definir como pago automaticamente
    // Adicione mais detalhes do pedido conforme necessário
);

// Dados do produto
$product_name = 'Ingresso VIP';
$product_data = array(
    'type' => 'simple',
    'regular_price' => '100.00', // ou deixe em branco se não precisar de preço
    'description' => 'Descrição do produto',
    // Adicione mais detalhes do produto conforme necessário
);

// Criar o pedido e obter o link de pagamento
$payment_link = $woocommerce_api->createOrder($customer_data, $order_data, $product_name, $product_data);

// Redirecionar o cliente para a tela de processar o pagamento
header("Location: $payment_link");
exit;
