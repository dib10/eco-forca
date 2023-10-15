<!DOCTYPE html>
<html>
<head>
    <title>Ecoforca</title>
    <link rel="icon" href="img/monkey.png" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="estilo/css.css">
    <meta charset="UTF-8">
</head>
<body>
<h1 class="ecoforca-title">EcoForca</h1>

<div id="jogo" class="game">
    <?php
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
    session_start(); // Inicializa a sessão para rastrear o estado do jogo

    // Array associativo de palavras relacionadas ao meio ambiente e suas dicas
    $palavrasComDicas = array(
        "desmatamento" => "Destruição de florestas",
        "fauna" => "Conjunto de animais em uma região",
        "aquecimento" => "Aumento da temperatura global",
        "reciclagem" => "Reutilização de materiais",
        "energia" => "Fluxo de Vida",
        "sustentabilidade" => "Preservação dos recursos naturais",
        "biodiversidade" => "Variedade de vida na Terra",
        "lixo" => "Resíduos indesejados",
        "flora" => "Conjunto de plantas em uma região",
        "carbono" => "Elemento químico presente em combustíveis fósseis"
    );

    // Inicialização das variáveis de sessão
    if (!isset($_SESSION['nivel'])) {
        session_destroy();
        session_start();
        $_SESSION['nivel'] = 1;
        $_SESSION['erros'] = 0;
        $_SESSION['letrasAdivinhadas'] = array();
        $_SESSION['palavrasUsadas'] = array();
        gerarPalavraENovaDica($palavrasComDicas);
    }

    // Verifica se é o início do jogo
    if ($_SESSION['erros'] == 0) {
        $_SESSION['erros'] = 1; // Define o número de erros para 1 no início do jogo
    }

    // Função para verificar se a letra está na palavra
    function letraEstaNaPalavra($letra, $palavraSecreta) {
        return mb_stripos($palavraSecreta, $letra, 0, 'UTF-8') !== false;
    }

    // Função para gerar uma nova palavra e dica
    function gerarPalavraENovaDica(&$palavrasComDicas) {
        do {
            $palavraComDica = array_rand($palavrasComDicas);
        } while (in_array($palavraComDica, $_SESSION['palavrasUsadas']));

        $_SESSION['palavraSecreta'] = $palavraComDica;
        $_SESSION['dica'] = $palavrasComDicas[$palavraComDica];
        $_SESSION['palavrasUsadas'][] = $palavraComDica;
    }

    // Exibir a imagem forca1 no início do jogo
    $numErros = min($_SESSION['erros'], 5);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['letra'])) {
            $letra = mb_strtolower($_POST['letra'], 'UTF-8'); // Converte a letra para minúscula com suporte a caracteres acentuados
            if (preg_match('/^[a-z]$/', $letra)) {
                if (!in_array($letra, $_SESSION['letrasAdivinhadas'])) {
                    $_SESSION['letrasAdivinhadas'][] = $letra;
                    if (!letraEstaNaPalavra($letra, $_SESSION['palavraSecreta'])) {
                        $_SESSION['erros']++;
                    }
                }
            }
        }
    }

    $palavraSecreta = htmlspecialchars($_SESSION['palavraSecreta'], ENT_QUOTES, 'UTF-8');
    $letrasAdivinhadas = $_SESSION['letrasAdivinhadas'];
    $erros = $_SESSION['erros'];
    $nivel = $_SESSION['nivel'];

    if ($erros >= 6) {
        // O jogador perdeu
        session_destroy();
        echo "<p>Você perdeu! A palavra era: $palavraSecreta</p>";
        
        // Inserir a imagem
        echo "<img src='img/forca6.png' alt='Imagem de derrota' width='300' height='300' style='margin-left: 82px;'>";
        echo"<br>";
        echo "<a href='index.html' style='background-color: hsl(51, 91%, 9%); color: #fff; font-size: 20px; border: none; border-radius: 5px; padding: 10px 20px; cursor: pointer; text-decoration: none;'>Voltar para a Página Inicial</a>"; // no css n tava pegando o estilo
    }
    
        else {
        // O jogo continua
        echo "<div class='palavra-container'>";
        echo '<a href="index.html" style="text-decoration: none; margin-right: 20px;">';
        echo '</a>';
        
        echo "<p class='nivel' style='margin-left: 700px; display: inline-block;'>Nível: $nivel</p>";
        echo "<p>Palavra: " . atualizarPalavraExibida($palavraSecreta, $letrasAdivinhadas) . "</p>";

        // Mostrar as letras erradas

        // Adicione a animação da forca aqui
        $numErros = min($erros, 5); // Certifique-se de que não ultrapasse 5

        // Exibe a dica com um balão de dica
        echo "<p>Dica: " . $_SESSION['dica'] . "</p>";

        // Mostrar as letras erradas
        echo "<p>Letras erradas: ";
        foreach ($_SESSION['letrasAdivinhadas'] as $letraAdivinhada) {
            if (!letraEstaNaPalavra($letraAdivinhada, $_SESSION['palavraSecreta'])) {
                echo strtoupper($letraAdivinhada) . " ";
            }
        }
        echo "</p>";
        echo "<p>Erros: " . max(0, $erros - 1) . "/5</p>";
        echo '<div style="margin-left: 70px;">';
        echo "<img src='img/forca{$numErros}.png' alt='Forca' width='300' height='300'>";
        echo '</div>';

        if (atualizarPalavraExibida($palavraSecreta, $letrasAdivinhadas) === $palavraSecreta) {
            // O jogador acertou a palavra atual
            if ($nivel == 1) {
                // Mensagem especial para o nível 1
            } else {
                echo "<p>Parabéns! Você completou o nível $nivel.</p>";
            }

            if ($nivel < 10) {
                // Avançar para o próximo nível
                $_SESSION['nivel']++;
                $_SESSION['erros'] = 0;
                gerarPalavraENovaDica($palavrasComDicas);
                $_SESSION['letrasAdivinhadas'] = array();
                echo "<form method='post'>";
                echo "<input type='submit' value='Próximo Nível'>";
                echo "</form>";
            } elseif ($nivel === 10) {
                // O jogador completou o último nível
                session_destroy();
                echo "<p>Parabéns! Você concluiu a Ecoforca!</p>";
                echo "<form method='post'>";
                echo "<input type='submit' value='Reiniciar Jogo'>";
                echo "</form>";
            }
        } else {
            // O jogo continua
            echo "<form method='post'>";
            echo "<label for='letra'>Adivinhe uma letra:</label> ";
            echo "<input type='text' name='letra' id='letra' maxlength='1'>";
            echo "<input type='submit' value='Adivinhar'>";
            echo "</form>";
            echo "<br>";
            echo "<a href='index.html' style='background-color: hsl(51, 91%, 9%); color: #fff; font-size: 20px; border: none; border-radius: 5px; padding: 10px 20px; cursor: pointer; text-decoration: none;'>Menu Inicial</a>"; // no css n tava pegando o estilo


        }

        echo "</div>";
    }

    // Função para atualizar a palavra exibida com letras adivinhadas
    function atualizarPalavraExibida($palavraSecreta, $letrasAdivinhadas) {
        $palavraExibida = '';
        foreach (preg_split('//u', $palavraSecreta, -1, PREG_SPLIT_NO_EMPTY) as $letra) {
            if (in_array(mb_strtolower($letra, 'UTF-8'), $letrasAdivinhadas)) {
                $palavraExibida .= $letra;
            } else {
                $palavraExibida .= ' _ ';
            }
        }
        return rtrim($palavraExibida);
    }
    ?>
</div>

<script>
    // Define uma função para manter a posição do scroll no jogo e focar no centro
    function manterScrollNoJogo() {
        var jogoDiv = document.getElementById("jogo");
        var jogoTop = jogoDiv.getBoundingClientRect().top;
        window.scrollTo({
            top: window.scrollY + jogoTop,
            behavior: "smooth" // Isso fará com que a rolagem seja suave
        });
    }

    // Chama a função para manter o scroll no jogo após o carregamento da página
    window.onload = manterScrollNoJogo;

      
</script>

</body>
</html>
