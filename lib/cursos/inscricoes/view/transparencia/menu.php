<?php function ativo($pagina,$content){ return $content==$pagina ? 'active' : ''; } ?>

<a href="<?php echo WB_URL_VIEW?>?content=view/transparencia/localizar_projetos.php" 
    class="btn btn-default <?php echo ativo('view/transparencia/localizar_projetos.php',$content); ?>">Projetos</a>
<a href="<?php echo WB_URL_VIEW?>?content=view/transparencia/localizar_pagamentos.php" 
    class="btn btn-default <?php echo ativo('view/transparencia/localizar_pagamentos.php',$content); ?>">Pagamentos</a>