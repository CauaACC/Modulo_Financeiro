<?php
/*
Plugin Name: Anais do Congresso
Description: Plugin para exibir a lista de anais na página anais/8congresso e como JSON.
Version: 1.1
Author: Seu Nome
*/

function anais_shortcode() {
    ob_start();
    $pdf_dir = plugins_url('pdfs', __FILE__);
    ?>
    <!-- HTML e JavaScript da página de anais (mantém o conteúdo atual) -->
    <?php
    return ob_get_clean();
}
add_shortcode('anais_congresso', 'anais_shortcode');

// Cria uma URL personalizada para o JSON
function anais_register_json_endpoint() {
    add_rewrite_rule('^anais/8congresso/json/?$', 'index.php?anais_json=1', 'top');
}
add_action('init', 'anais_register_json_endpoint');

function anais_json_template_redirect() {
    if (get_query_var('anais_json') == 1) {
        header('Content-Type: application/json');
        $pdf_dir = plugins_url('pdfs', __FILE__);
        
        // Dados dos artigos (você pode expandir isso dinamicamente)
        $data = [
            "Eixo 1" => [
                [
                    "titulo" => "Título do Artigo 1",
                    "autores" => "Autor 1, Co-autor 1, Co-autor 2",
                    "palavras_chave" => ["educação", "tecnologia"],
                    "link" => "$pdf_dir/artigo1.pdf"
                ],
                [
                    "titulo" => "Título do Artigo 3",
                    "autores" => "Autor 3, Co-autor 4",
                    "palavras_chave" => ["saúde", "bem-estar"],
                    "link" => "$pdf_dir/artigo3.pdf"
                ]
            ],
            "Eixo 2" => [
                [
                    "titulo" => "Título do Artigo 2",
                    "autores" => "Autor 2, Co-autor 3",
                    "palavras_chave" => ["inovação", "sustentabilidade"],
                    "link" => "$pdf_dir/artigo2.pdf"
                ]
            ]
        ];
        
        echo json_encode($data);
        exit;
    }
}
add_action('template_redirect', 'anais_json_template_redirect');

// Registra a query var personalizada para o JSON
function anais_register_query_vars($vars) {
    $vars[] = 'anais_json';
    return $vars;
}
add_filter('query_vars', 'anais_register_query_vars');
?>
