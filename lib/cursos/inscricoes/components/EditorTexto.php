<?php 

class EditorTexto {
    
    public static function renderizar($tabId, $nomeCampo, $label, $objEvConf) {
        $nomeMetodo = 'get' . self::snakeCaseParaCamelCase($nomeCampo);
        $conteudo = $objEvConf->$nomeMetodo();
        
        echo "<div id='{$tabId}'>";
            echo "<label>{$label}</label>";
            echo "<ul>";
                echo "<li class='txtAba'><a href='#{$tabId}V'>Visualizar</a></li>";
                echo "<li class='txtAba'><a href='#{$tabId}E'>Editar</a></li>";
            echo "</ul>";

            echo "<div id='{$tabId}E' class='tab-content'>";
            echo "<textarea class='editor_padrao rows=5 cols=70' name='{$nomeCampo}' onBlur='trim(this);' class='textob form_control'>{$conteudo}</textarea>";
            echo "</div>";

            echo "<div id='{$tabId}V' class='tab-content'>";
            echo $conteudo ? $conteudo : "Nenhuma mensagem cadastrada";
            echo "</div>";

        echo "</div>";
    }

    private static function snakeCaseParaCamelCase($string) {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        $str[0] = strtolower($str[0]);
        return $str;
    }
}
