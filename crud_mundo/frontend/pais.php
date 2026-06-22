<?php include "../backend/conexao.php"; ?>
<?php

$mensagem = "";
$tipoMsg = "";
$editando = null;

// ---------- EXCLUIR ----------
if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];

    $check = $conn->query("SELECT COUNT(*) AS total FROM cidades WHERE id_pais = $id");
    $row = $check->fetch_assoc();

    if ($row['total'] > 0) {
        $mensagem = "Não é possível excluir: existem cidades vinculadas a este país.";
        $tipoMsg = "erro";
    } else {
        $conn->query("DELETE FROM paises WHERE id_pais = $id");
        $mensagem = "País excluído com sucesso.";
        $tipoMsg = "sucesso";
    }
}

// ---------- CARREGAR PARA EDIÇÃO ----------
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $res = $conn->query("SELECT * FROM paises WHERE id_pais = $id");
    $editando = $res->fetch_assoc();
}

// ---------- SALVAR (INSERIR OU ATUALIZAR) ----------
if (isset($_POST['salvar'])) {
    $nome        = trim($_POST['nome']);
    $continente  = $_POST['continente'];
    $populacao   = $_POST['populacao'];
    $area        = $_POST['area'];
    $idioma      = trim($_POST['idioma']);
    $governante  = $_POST['governante'] !== '' ? (int) $_POST['governante'] : null;
    $clima       = trim($_POST['clima']);
    $regime      = trim($_POST['regime']);
    $moeda       = trim($_POST['moeda']);
    $id_edicao   = $_POST['id_pais'] ?? '';

    if ($nome === '' || $continente === '' || $populacao === '' || $area === '' ||
        $idioma === '' || $clima === '' || $regime === '' || $moeda === '') {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipoMsg = "erro";
    } else {
        if ($id_edicao !== '') {
            // UPDATE
            $stmt = $conn->prepare(
                "UPDATE paises SET nome_pais=?, id_continente=?, populacao_pais=?, area_pais=?,
                 idioma=?, id_governante=?, clima=?, regime_politico=?, moeda=?
                 WHERE id_pais=?"
            );
            $stmt->bind_param(
                "siddsisssi",
                $nome, $continente, $populacao, $area, $idioma, $governante, $clima, $regime, $moeda, $id_edicao
            );
            $mensagemOk = "País atualizado com sucesso.";
        } else {
            // INSERT
            $stmt = $conn->prepare(
                "INSERT INTO paises (nome_pais, id_continente, populacao_pais, area_pais, idioma, id_governante, clima, regime_politico, moeda)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "siddsisss",
                $nome, $continente, $populacao, $area, $idioma, $governante, $clima, $regime, $moeda
            );
            $mensagemOk = "País cadastrado com sucesso.";
        }

        if ($stmt->execute()) {
            $mensagem = $mensagemOk;
            $tipoMsg = "sucesso";
        } else {
            $mensagem = "Erro ao salvar: " . $conn->error;
            $tipoMsg = "erro";
        }

        $stmt->close();
    }
}

// ---------- DADOS PARA OS SELECTS ----------
$continentes = $conn->query("SELECT id_continente, nome FROM continentes ORDER BY nome");
$governantes = $conn->query("SELECT id_governante, nome FROM governantes ORDER BY nome");

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Países - Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>MUNDO</h1>
</header>

<main class="container">

    <h1>PAÍSES</h1>

    <?php if ($mensagem): ?>
        <div class="msg <?= $tipoMsg ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <h2><?= $editando ? "Editar País" : "Novo País" ?></h2>

    <form method="POST" class="cadastro">

        <?php if ($editando): ?>
            <input type="hidden" name="id_pais" value="<?= $editando['id_pais'] ?>">
        <?php endif; ?>

        <label>Nome
            <input type="text" name="nome" placeholder="Nome do país" required
                   value="<?= $editando ? htmlspecialchars($editando['nome_pais']) : '' ?>">
        </label>

        <label>Continente
            <select name="continente" required>
                <option value="">Selecione...</option>
                <?php
                $continentes->data_seek(0);
                while ($c = $continentes->fetch_assoc()) {
                    $sel = ($editando && $editando['id_continente'] == $c['id_continente']) ? "selected" : "";
                    echo "<option value='{$c['id_continente']}' $sel>" . htmlspecialchars($c['nome']) . "</option>";
                }
                ?>
            </select>
        </label>

        <label>População
            <input type="number" name="populacao" placeholder="População" required min="0"
                   value="<?= $editando ? $editando['populacao_pais'] : '' ?>">
        </label>

        <label>Área (km²)
            <input type="number" name="area" placeholder="Área em km²" step="0.01" required min="0"
                   value="<?= $editando ? $editando['area_pais'] : '' ?>">
        </label>

        <label>Idioma
            <input type="text" name="idioma" placeholder="Idioma" required
                   value="<?= $editando ? htmlspecialchars($editando['idioma']) : '' ?>">
        </label>

        <label>Governante
            <select name="governante">
                <option value="">Nenhum</option>
                <?php
                $governantes->data_seek(0);
                while ($g = $governantes->fetch_assoc()) {
                    $sel = ($editando && $editando['id_governante'] == $g['id_governante']) ? "selected" : "";
                    echo "<option value='{$g['id_governante']}' $sel>" . htmlspecialchars($g['nome']) . "</option>";
                }
                ?>
            </select>
        </label>

        <label>Clima
            <input type="text" name="clima" placeholder="Clima" required
                   value="<?= $editando ? htmlspecialchars($editando['clima']) : '' ?>">
        </label>

        <label>Regime Político
            <input type="text" name="regime" placeholder="Regime político" required
                   value="<?= $editando ? htmlspecialchars($editando['regime_politico']) : '' ?>">
        </label>

        <label>Moeda
            <input type="text" name="moeda" placeholder="Moeda" required
                   value="<?= $editando ? htmlspecialchars($editando['moeda']) : '' ?>">
        </label>

        <button name="salvar"><?= $editando ? "Atualizar" : "Salvar" ?></button>

        <?php if ($editando): ?>
            <a href="pais.php" class="btn btn-cancelar" style="text-align:center; line-height:45px; grid-column: 1 / -1;">Cancelar edição</a>
        <?php endif; ?>

    </form>

    <hr>

    <h2>Tabela de Países</h2>

    <div class="busca">
        <input type="text" placeholder="Buscar país pelo nome..." data-tabela="tabelaPaises">
    </div>

    <div class="tabela-wrapper">
    <table id="tabelaPaises">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Continente</th>
            <th>População</th>
            <th>Área (km²)</th>
            <th>Idioma</th>
            <th>Governante</th>
            <th>Clima</th>
            <th>Regime</th>
            <th>Moeda</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT p.*, c.nome AS nome_continente, g.nome AS nome_governante
                FROM paises p
                LEFT JOIN continentes c ON p.id_continente = c.id_continente
                LEFT JOIN governantes g ON p.id_governante = g.id_governante
                ORDER BY p.nome_pais";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $gov = $row['nome_governante'] ? htmlspecialchars($row['nome_governante']) : '-';
            echo "<tr>
                <td>{$row['id_pais']}</td>
                <td>" . htmlspecialchars($row['nome_pais']) . "</td>
                <td>" . htmlspecialchars($row['nome_continente']) . "</td>
                <td>" . number_format($row['populacao_pais'], 0, ',', '.') . "</td>
                <td>" . number_format($row['area_pais'], 2, ',', '.') . "</td>
                <td>" . htmlspecialchars($row['idioma']) . "</td>
                <td>$gov</td>
                <td>" . htmlspecialchars($row['clima']) . "</td>
                <td>" . htmlspecialchars($row['regime_politico']) . "</td>
                <td>" . htmlspecialchars($row['moeda']) . "</td>
                <td class='acoes'>
                    <a class='btn-editar' href='pais.php?editar={$row['id_pais']}'>Editar</a>
                    <a class='btn-excluir' href='pais.php?excluir={$row['id_pais']}' data-nome='" . htmlspecialchars($row['nome_pais']) . "'>Excluir</a>
                </td>
            </tr>";
        }
        ?>
        </tbody>
    </table>
    </div>

    <a href="index.php" class="voltar">&larr; Voltar</a>

</main>

<script src="script.js"></script>
</body>
</html>
