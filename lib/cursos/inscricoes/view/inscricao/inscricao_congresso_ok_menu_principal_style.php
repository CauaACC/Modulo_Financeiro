<style>
    #tela-inicial-container {
        background-color: #f5f5f5;
    }
    #tela-inicial-container .section {
        display: none;
        background: white;
        border-radius: 10px;
        margin: auto;
        text-align: left;
        padding: 0; /* Sem padding para telas menores */
    }

    /* Estilo para telas maiores que 400px */
    @media (min-width: 400px) {
        #tela-inicial-container .section {
            padding-left: 20px; /* Padding para telas maiores */
            padding-right: 20px; /* Padding para telas maiores */
        }
    }
    #tela-inicial-container .home {
        display: block;
    }
    #tela-inicial-container .menu-option {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        width: 100%;
        height: 120px;
        margin: 10px 0;
        border: 2px solid #ddd;
        border-radius: 15px;
        cursor: pointer;
        transition: all 0.3s;
        background-color: white;
        padding: 10px;
    }
    #tela-inicial-container .menu-option:hover {
        border-color: #4A90E2;
    }
    #tela-inicial-container .menu-option i {
        font-size: 18px;
        color: #4A90E2;
        margin-bottom: 10px;
    }
    #tela-inicial-container .menu-text {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    #tela-inicial-container .menu-text span {
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }
    #tela-inicial-container .menu-text p {
        font-size: 12px;
        color: #666;
        margin: 3px 0 0 0;
    }
    #tela-inicial-container .back-btn {
        margin-top: 20px;
        display: block;
        text-align: center;
        background-color: #4A90E2;
        color: white;
        padding: 10px;
        border-radius: 8px;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.3s;
    }
    #tela-inicial-container .back-btn:hover {
        background-color: #357ABD;
    }

    /* Modernização dos títulos e texto */
    .section h2 {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        display: flex;
        align-items: center;
    }
    .section h2 i {
        font-size: 20px;
        color: #4A90E2;
        margin-right: 10px;
    }
    .section p {
        font-size: 16px;
        color: #555;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    .section ul {
        padding-left: 20px;
        font-size: 14px;
        color: #555;
    }
    .section ul li {
        margin-bottom: 10px;
    }
    .section ul li a {
        color: #4A90E2;
        text-decoration: none;
    }
    .section ul li a:hover {
        text-decoration: underline;
    }

    /* Botão de voltar discreto */
    .section .back-btn {
        /* position: absolute; */
        bottom: 20px;
        /* width: 100%; */
        text-align: center;
        font-size: 14px;
        padding: 8px;
    }

    /* Seção de Avisos */
    .aviso-box {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .aviso-box h3 {
        color: #856404;
        font-size: 18px;
        margin-bottom: 10px;
    }
    .aviso-box p {
        color: #856404;
        font-size: 14px;
    }
</style>

<script>
    function showSection(sectionId) {
        $("#tela-inicial-container .section").hide();
        $("#" + sectionId).fadeIn();
    }
</script>
