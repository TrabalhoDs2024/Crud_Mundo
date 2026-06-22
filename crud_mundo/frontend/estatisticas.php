<?php include "../backend/conexao.php"; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>MUNDO</h1>
</header>

<main class="container">

    <h1>ESTATÍSTICAS</h1>

    <div class="estatisticas">
        <h2>Cidade mais populosa de cada país</h2>
        <ul>
        <?php
        $sql = "SELECT p.nome_pais, c.nome_cidade, c.populacao
                FROM cidades c
                INNER JOIN paises p ON c.id_pais = p.id_pais
                WHERE c.populacao = (
                    SELECT MAX(c2.populacao) FROM cidades c2 WHERE c2.id_pais = c.id_pais
                )
                ORDER BY p.nome_pais";
        $res = $conn->query($sql);

        if ($res->num_rows === 0) {
            echo "<li>Nenhuma cidade cadastrada.</li>";
        }

        while ($r = $res->fetch_assoc()) {
            echo "<li><strong>" . htmlspecialchars($r['nome_pais']) . "</strong>: "
                . htmlspecialchars($r['nome_cidade']) . " ("
                . number_format($r['populacao'], 0, ',', '.') . " habitantes)</li>";
        }
        ?>
        </ul>
    </div>

    <div class="estatisticas">
        <h2>Total de cidades cadastradas por continente</h2>
        <ul>
        <?php
        $sql = "SELECT co.nome AS continente, COUNT(ci.id_cidade) AS total_cidades
                FROM continentes co
                LEFT JOIN paises p ON p.id_continente = co.id_continente
                LEFT JOIN cidades ci ON ci.id_pais = p.id_pais
                GROUP BY co.id_continente, co.nome
                ORDER BY co.nome";
        $res = $conn->query($sql);

        while ($r = $res->fetch_assoc()) {
            echo "<li><strong>" . htmlspecialchars($r['continente']) . "</strong>: "
                . $r['total_cidades'] . " cidade(s)</li>";
        }
        ?>
        </ul>
    </div>

    <div class="estatisticas">
        <h2>Resumo Geral</h2>
        <ul>
        <?php
        $totPaises = $conn->query("SELECT COUNT(*) AS t FROM paises")->fetch_assoc()['t'];
        $totCidades = $conn->query("SELECT COUNT(*) AS t FROM cidades")->fetch_assoc()['t'];
        $totGovernantes = $conn->query("SELECT COUNT(*) AS t FROM governantes")->fetch_assoc()['t'];
        $totContinentes = $conn->query("SELECT COUNT(*) AS t FROM continentes")->fetch_assoc()['t'];
        ?>
            <li>Total de continentes: <?= $totContinentes ?></li>
            <li>Total de países: <?= $totPaises ?></li>
            <li>Total de cidades: <?= $totCidades ?></li>
            <li>Total de governantes: <?= $totGovernantes ?></li>
        </ul>
    </div>

    <a href="index.php" class="voltar">&larr; Voltar</a>

</main>

</body>
</html>
