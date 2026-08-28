<?php
/*
Plugin Name: Opctec - Anais 8 Congresso CRESS
Description: Plugin para exibir uma lista de trabalhos em PDF do 8º Congresso CRESS.
Version: 1.0
Author: Opctec
Author URI: https://opctec.net.br
Plugin URI: https://opctec.net.br/plugins/opctec-anais-8-congresso
*/

// Impede acesso direto
if (!defined('ABSPATH')) exit;

// Função para criar a pasta de uploads ao ativar o plugin
function opctec_criar_pasta_upload() {
    $upload_dir = wp_upload_dir(); 
    $congresso_dir = $upload_dir['basedir'] . '/8-congresso';

    if (!file_exists($congresso_dir)) {
        mkdir($congresso_dir, 0755, true);
    }
}

// Registra a função de ativação do plugin
register_activation_hook(__FILE__, 'opctec_criar_pasta_upload');

// Função para carregar o conteúdo do HTML e os scripts
function opctec_anais_8_congresso_shortcode() {
    ob_start();
    ?>
    <div id="opctec-conteudo">
        <h2>Anais do 8º Congresso Paranaense de Assistentes Sociais: antirracismo e anticapacitismo</h2>
        <div id="lista-documentos">
            <!-- Carrega o conteúdo do arquivo HTML -->
            <?php include plugin_dir_path(__FILE__) . '/listar_documentos.html'; ?>
        </div>
    </div>
    <script>
    function getVisualizarArquivo(nomeArquivo) {
        var caminhoArquivo = '/wp-content/uploads/2024/10/' + nomeArquivo; // Altere conforme necessário
        window.open(caminhoArquivo, '_blank');
}   </script>
    <?php
    return ob_get_clean();
}

// Registra o shortcode
add_shortcode('opctec_anais_8_congresso', 'opctec_anais_8_congresso_shortcode');
