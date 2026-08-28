<?php
/*
Plugin Name: Anais do Congresso
Description: Plugin para exibir a lista de anais na página anais/8congresso
Version: 1.0
Author: Seu Nome
*/

function anais_shortcode() {
    ob_start();
    $pdf_dir = plugins_url('pdfs', __FILE__); // Diretório dos PDFs
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Anais do Evento</title>
        <style>
            body { font-family: Arial, sans-serif; }
            #searchInput, #eixoInput { margin-bottom: 20px; padding: 10px; width: 100%; max-width: 400px; }
            ul { list-style-type: none; padding: 0; }
            li { margin: 5px 0; }
            a { text-decoration: none; color: #0073e6; }
            a:hover { text-decoration: underline; }
            .eixo { margin-bottom: 40px; }
            .eixo h3 { margin-top: 20px; font-size: 1.5em; color: #333; }
        </style>
    </head>
    <body>
        <div>
            <h2>Anais do Evento</h2>
            <input type="text" id="searchInput" onkeyup="searchArticles()" placeholder="Buscar artigos...">
            <select id="eixoInput" onchange="searchArticles()">
                <option value="">Todos os Eixos</option>
                <option value="Eixo 1">Eixo 1</option>
                <option value="Eixo 2">Eixo 2</option>
                <option value="Eixo 3">Eixo 3</option>
            </select>
            <div id="articlesContainer">
                <div class="eixo" data-eixo="Eixo 1">
                    <h3>Eixo 1: Nome do Eixo</h3>
                    <ul>
                        <li><a href="<?php echo $pdf_dir; ?>/artigo1.pdf" target="_blank">Título do Artigo 1</a><p>Autor 1, Co-autor 1, Co-autor 2</p><p>Palavras-chave: educação, tecnologia</p></li>
                        <li><a href="<?php echo $pdf_dir; ?>/artigo3.pdf" target="_blank">Título do Artigo 3</a><p>Autor 3, Co-autor 4</p><p>Palavras-chave: saúde, bem-estar</p></li>
                    </ul>
                </div>
                <div class="eixo" data-eixo="Eixo 2">
                    <h3>Eixo 2: Nome do Eixo</h3>
                    <ul>
                        <li><a href="<?php echo $pdf_dir; ?>/artigo2.pdf" target="_blank">Título do Artigo 2</a><p>Autor 2, Co-autor 3</p><p>Palavras-chave: inovação, sustentabilidade</p></li>
                    </ul>
                </div>
            </div>
        </div>
        <script>
            function searchArticles() {
                var input = document.getElementById('searchInput').value.toUpperCase();
                var eixoInput = document.getElementById('eixoInput').value;
                var articlesContainer = document.getElementById("articlesContainer");
                var eixos = articlesContainer.getElementsByClassName('eixo');

                for (var i = 0; i < eixos.length; i++) {
                    var eixo = eixos[i].getAttribute('data-eixo');
                    var artigos = eixos[i].getElementsByTagName('li');
                    var eixoMatch = eixoInput === "" || eixo === eixoInput;
                    var hasVisibleArticle = false;

                    for (var j = 0; j < artigos.length; j++) {
                        var text = artigos[j].innerText.toUpperCase();
                        if (text.indexOf(input) > -1 && eixoMatch) {
                            artigos[j].style.display = "";
                            hasVisibleArticle = true;
                        } else {
                            artigos[j].style.display = "none";
                        }
                    }
                    eixos[i].style.display = hasVisibleArticle ? "" : "none";
                }
            }
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean(); 
}
add_shortcode('anais_congresso', 'anais_shortcode');

// Adiciona a URL personalizada
function anais_rewrite_rule() {
    add_rewrite_rule('^anais/8congresso/?$', 'index.php?pagename=anais-congresso', 'top');
}
add_action('init', 'anais_rewrite_rule');

// Cria a página de anais do congresso
function anais_create_page() {
    if (!get_page_by_path('anais-congresso')) {
        $page_id = wp_insert_post([
            'post_title' => 'Anais do Congresso',
            'post_name' => 'anais-congresso',
            'post_content' => '[anais_congresso]', // Shortcode
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);
    }
}
register_activation_hook(__FILE__, 'anais_create_page');
