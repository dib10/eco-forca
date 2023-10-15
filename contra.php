<!DOCTYPE html>
<html>
<head>
    <title>Ecoforca</title>
    <link rel="icon" href="img/monkey.png" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="estilo/css.css">
    <meta charset="UTF-8">
</head>
<body>
<script>
    // Define uma função para manter a posição do scroll no jogo
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
<h1 class="ecoforca-title">EcoForca</h1>

    <div class="game">
        
        <?php

    
        session_start(); // Inicializa a sessão para rastrear o estado do jogo

        // Array associativo de palavras relacionadas ao meio ambiente e suas dicas
        $palavrasComDicas = array(
            "sol" => "Estrela que é a fonte primária de luz e calor do nosso sistema solar",
            "solo" => "Camada superior da Terra, composta por minerais e matéria orgânica",
            "queimadas" => "Processo de incêndio deliberado em áreas naturais ou agrícolas",
            "atmosfera" => "Camada gasosa que envolve a Terra e é vital para a vida",
            "poluente" => "Substância que causa poluição do ar, água ou solo",
            "hidrografia" => "Estudo e mapeamento das águas da Terra, incluindo rios, lagos e oceanos",
            "bioma" => "Grande ecossistema com características climáticas e geográficas específicas",
            "macaco" => "Tipo de primata",
            "musgo" => "Tipo de planta que cresce em áreas úmidas e tem aparência esponjosa",
            "riacho" => "Curso de água pequeno e de fluxo contínuo",
            "selva" => "Tipo de floresta densa e exuberante, geralmente localizada em regiões tropicais"
        );

        // Inicialização das variáveis de sessão
        if (!isset($_SESSION['nivel'])) {
            $_SESSION['nivel'] = 1;
            $_SESSION['letrasAdivinhadas'] = array();
            $_SESSION['palavrasUsadas'] = array();
            $_SESSION['jogadorAtual'] = 1; // Inicializa o jogador 1
            gerarPalavraENovaDica($palavrasComDicas);
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['letra'])) {
                $letra = mb_strtolower($_POST['letra'], 'UTF-8'); // Converte a letra para minúscula com suporte a caracteres acentuados
                if (preg_match('/^[a-z]$/', $letra)) {
                    if (!in_array($letra, $_SESSION['letrasAdivinhadas'])) {
                        $_SESSION['letrasAdivinhadas'][] = $letra;
                    }
                    
                    // Verificar se todas as letras foram adivinhadas
                    if (atualizarPalavraExibida($_SESSION['palavraSecreta'], $_SESSION['letrasAdivinhadas']) === $_SESSION['palavraSecreta']) {
                        // O jogador adivinhou todas as letras da palavra atual
                        echo "<p>Jogador {$_SESSION['jogadorAtual']} completou o nível {$_SESSION['nivel']}.</p>";
                        
                        // Adicione pontos ao jogador atual por adivinhar a palavra
                        $_SESSION['pontuacao_jogador_' . $_SESSION['jogadorAtual']] = isset($_SESSION['pontuacao_jogador_' . $_SESSION['jogadorAtual']]) ? ($_SESSION['pontuacao_jogador_' . $_SESSION['jogadorAtual']] + 1) : 1;
                        
                        // Avançar para o próximo nível, mas não mais do que o nível 11
                        if ($_SESSION['nivel'] < 11) {
                            $_SESSION['nivel']++;
                        }
                        gerarPalavraENovaDica($palavrasComDicas);
                        $_SESSION['letrasAdivinhadas'] = array();
                    } else {
                        // Alternar para o próximo jogador apenas quando uma letra é adivinhada
                        $_SESSION['jogadorAtual'] = ($_SESSION['jogadorAtual'] == 1) ? 2 : 1;
                    }
                }
            } elseif (isset($_POST['palpite'])) {
                $palpite = mb_strtolower($_POST['palpite'], 'UTF-8');
                if ($palpite === $_SESSION['palavraSecreta']) {
                    // O jogador acertou a palavra completa
                    echo "<p>Jogador {$_SESSION['jogadorAtual']} acertou a palavra: {$_SESSION['palavraSecreta']}</p>";
                    
                    // Adicione pontos ao jogador atual por adivinhar a palavra
                    $_SESSION['pontuacao_jogador_' . $_SESSION['jogadorAtual']] = isset($_SESSION['pontuacao_jogador_' . $_SESSION['jogadorAtual']]) ? ($_SESSION['pontuacao_jogador_' . $_SESSION['jogadorAtual']] + 1) : 1;
                    
                    // Avançar para o próximo nível, mas não mais do que o nível 11
                    if ($_SESSION['nivel'] < 11) {
                        $_SESSION['nivel']++;
                    }
                    gerarPalavraENovaDica($palavrasComDicas);
                    $_SESSION['letrasAdivinhadas'] = array();
                } else {
                    // Se o palpite estiver errado, avance para o próximo jogador
                    $_SESSION['jogadorAtual'] = ($_SESSION['jogadorAtual'] == 1) ? 2 : 1;
                }
            }
        }
        $palavraSecreta = htmlspecialchars($_SESSION['palavraSecreta'], ENT_QUOTES, 'UTF-8');
        $letrasAdivinhadas = $_SESSION['letrasAdivinhadas'];
        $nivel = $_SESSION['nivel'];
        $jogadorAtual = $_SESSION['jogadorAtual'];

        echo "<div class='palavra-container'>";
        echo "<p class='nivel' style='margin-left: 700px; display: inline-block;'>Nível: $nivel</p>";
        echo "<p>Jogador Atual: $jogadorAtual</p>";
        echo "<p>Palavra: " . atualizarPalavraExibida($palavraSecreta, $letrasAdivinhadas) . "</p>";

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

        if ($nivel >= 11) {
            // Calcule a pontuação final de ambos os jogadores
            $pontuacaoJogador1 = isset($_SESSION['pontuacao_jogador_1']) ? $_SESSION['pontuacao_jogador_1'] : 0;
            $pontuacaoJogador2 = isset($_SESSION['pontuacao_jogador_2']) ? $_SESSION['pontuacao_jogador_2'] : 0;
        
            // Determine o vencedor ou se há um empate
if ($pontuacaoJogador1 > $pontuacaoJogador2) {
    $vencedor = "Jogador 1";
    $pontuacaoVencedor = $pontuacaoJogador1;
} elseif ($pontuacaoJogador2 > $pontuacaoJogador1) {
    $vencedor = "Jogador 2";
    $pontuacaoVencedor = $pontuacaoJogador2;
} else {
    $vencedor = "Empate";
    $pontuacaoVencedor = $pontuacaoJogador1; // Você pode escolher qualquer uma das pontuações, já que são iguais
}

echo "<hr>";  // Inserindo a regra horizontal
echo "<h2>Pontuação</h2>";
echo "<p>Pontuação final do Jogador 1: $pontuacaoJogador1</p>";
echo "<p>Pontuação final do Jogador 2: $pontuacaoJogador2</p>";

if ($vencedor == "Empate") {
    echo "<p>O jogo terminou em empate com $pontuacaoVencedor pontos para ambos os jogadores.</p>";
} else {
    echo "<p>O vencedor é: $vencedor com $pontuacaoVencedor pontos</p>";
}


        
            // Botão "Reiniciar Jogo"
            echo "<form method='post'>";
            echo "<input type='submit' name='reiniciarJogo' value='Reiniciar Jogo'>";
            echo "</form>";
        
            if (isset($_POST['reiniciarJogo'])) {
                // Limpe a sessão para reiniciar o jogo
                session_unset();
                session_destroy();
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
        
            // Encerre o script aqui para evitar qualquer saída adicional
            exit;
        } elseif (atualizarPalavraExibida($palavraSecreta, $letrasAdivinhadas) === $palavraSecreta) {
            // O jogador acertou a palavra atual
            if ($nivel == 1) {
                // Mensagem especial para o nível 1
                echo "<p>Jogador $jogadorAtual completou o nível $nivel.</p>";
            } else {
                // Mostrar opção "Próximo Nível"
                echo "<form method='post'>";
                echo "<input type='submit' name='proximoNivel' value='Próximo Nível'>";
                echo "</form>";
            }
        } else {
            // O jogo continua
            echo "<form method='post'>";
            echo "<label for='letra'>Adivinhe uma letra:</label> ";
            echo "<input type='text' name='letra' id='letra' maxlength='1'>";
            echo "<input type='submit' value='Adivinhar'>";
            echo "</form>";

            // Adicione a opção de adivinhar a palavra completa
            echo "<form method='post'>";
            echo "<label for='palpite'>Adivinhe a palavra:</label> ";
            echo "<input type='text' name='palpite' id='palpite' style='width: 300px;'>"; // Ajuste a largura conforme necessário
            echo "<input type='submit' value='Palpite'>";
            echo "</form>";
            echo "<br>";
            echo "<a href='index.html' style='background-color: hsl(51, 91%, 9%); color: #fff; font-size: 20px; border: none; border-radius: 5px; padding: 10px 20px; cursor: pointer; text-decoration: none;'>Menu Inicial</a>"; // no css n tava pegando o estilo
        }

        echo "</div>";

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
    <div class="pontuacao">
        <p>
            <span>Pontuação do Jogador 1:</span>
            <?php echo isset($_SESSION['pontuacao_jogador_1']) ? $_SESSION['pontuacao_jogador_1'] : 0; ?>
        </p>
        <p>
            <span>Pontuação do Jogador 2:</span>
            <?php echo isset($_SESSION['pontuacao_jogador_2']) ? $_SESSION['pontuacao_jogador_2'] : 0; ?>
        </p>
    </div>
</body>
</html>
