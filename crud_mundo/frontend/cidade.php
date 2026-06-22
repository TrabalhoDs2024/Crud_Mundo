<?php include "../backend/conexao.php"; ?>
<?php

$mensagem = "";
$tipoMsg = "";
$editando = null;

// ---------- EXCLUIR ----------
if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];
    $conn->query("DELETE FROM cidades WHERE id_cidade = $id");
    $mensagem = "Cidade excluída com sucesso.";
    $tipoMsg = "sucesso";
}

// ---------- CARREGAR PARA EDIÇÃO ----------
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $res = $conn->query("SELECT * FROM cidades WHERE id_cidade = $id");
    $editando = $res->fetch_assoc();
}

// ---------- SALVAR (INSERIR OU ATUALIZAR) ----------
if (isset($_POST['salvar'])) {
    $nome       = trim($_POST['nome']);
    $pais       = $_POST['pais'];
    $pop        = $_POST['pop'];
    $area       = $_POST['area'];
    $clima      = trim($_POST['clima']);
    $governante = $_POST['governante'] !== '' ? (int) $_POST['governante'] : null;
    $data       = $_POST['data'];
    $id_edicao  = $_POST['id_cidade'] ?? '';

    if ($nome === '' || $pais === '' || $pop === '' || $area === '' || $clima === '' || $data === '') {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipoMsg = "erro";
    } else {
        if ($id_edicao !== '') {
            // UPDATE
            $stmt = $conn->prepare(
                "UPDATE cidades SET nome_cidade=?, id_pais=?, populacao=?, area=?, clima=?, id_governante=?, data_fundacao=?
                 WHERE id_cidade=?"
            );
            $stmt->bind_param(
                "siddsisi",
                $nome, $pais, $pop, $area, $clima, $governante, $data, $id_edicao
            );
            $mensagemOk = "Cidade atualizada com sucesso.";
        } else {
            // INSERT
            $stmt = $conn->prepare(
                "INSERT INTO cidades (nome_cidade, id_pais, populacao, area, clima, id_governante, data_fundacao)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "siddsis",
                $nome, $pais, $pop, $area, $clima, $governante, $data
            );
            $mensagemOk = "Cidade cadastrada com sucesso.";
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
$paises = $conn->query("SELECT id_pais, nome_pais FROM paises ORDER BY nome_pais");
$governantes = $conn->query("SELECT id_governante, nome FROM governantes ORDER BY nome");

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cidades - Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>MUNDO</h1>
</header>

<main class="container">

    <h1>CIDADES</h1>

    <?php if ($mensagem): ?>
        <div class="msg <?= $tipoMsg ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <h2><?= $editando ? "Editar Cidade" : "Nova Cidade" ?></h2>

    <?php if ($paises->num_rows === 0): ?>
        <div class="msg erro">Cadastre um país antes de adicionar cidades.</div>
    <?php endif; ?>

    <form method="POST" class="cadastro">

        <?php if ($editando): ?>
            <input type="hidden" name="id_cidade" value="<?= $editando['id_cidade'] ?>">
        <?php endif; ?>

        <label>Nome
            <input type="text" name="nome" placeholder="Nome da cidade" required
                   value="<?= $editando ? htmlspecialchars($editando['nome_cidade']) : '' ?>">
        </label>

        <label>País
            <select name="pais" required>
                <option value="">Selecione...</option>
                <?php
                $paises->data_seek(0);
                while ($p = $paises->fetch_assoc()) {
                    $sel = ($editando && $editando['id_pais'] == $p['id_pais']) ? "selected" : "";
                    echo "<option value='{$p['id_pais']}' $sel>" . htmlspecialchars($p['nome_pais']) . "</option>";
                }
                ?>
            </select>
        </label>

        <label>População
            <input type="number" name="pop" placeholder="População" required min="0"
                   value="<?= $editando ? $editando['populacao'] : '' ?>">
        </label>

        <label>Área (km²)
            <input type="number" name="area" placeholder="Área em km²" step="0.01" required min="0"
                   value="<?= $editando ? $editando['area'] : '' ?>">
        </label>

        <label>Clima
            <input type="text" name="clima" placeholder="Clima" required
                   value="<?= $editando ? htmlspecialchars($editando['clima']) : '' ?>">
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

        <label>Data de Fundação
            <input type="date" name="data" required
                   value="<?= $editando ? $editando['data_fundacao'] : '' ?>">
        </label>

        <button name="salvar"><?= $editando ? "Atualizar" : "Salvar" ?></button>

        <?php if ($editando): ?>
            <a href="cidade.php" class="btn btn-cancelar" style="text-align:center; line-height:45px; grid-column: 1 / -1;">Cancelar edição</a>
        <?php endif; ?>

    </form>

    <h2>Tabela de Cidades</h2>

    <div class="busca">
        <input type="text" placeholder="Buscar cidade pelo nome..." data-tabela="tabelaCidades">
    </div>

    <div class="tabela-wrapper">
    <table id="tabelaCidades">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>País</th>
            <th>População</th>
            <th>Área (km²)</th>
            <th>Clima</th>
            <th>Governante</th>
            <th>Fundação</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT ci.*, p.nome_pais, g.nome AS nome_governante
                FROM cidades ci
                LEFT JOIN paises p ON ci.id_pais = p.id_pais
                LEFT JOIN governantes g ON ci.id_governante = g.id_governante
                ORDER BY ci.nome_cidade";
        $res = $conn->query($sql);

        while ($r = $res->fetch_assoc()) {
            $gov = $r['nome_governante'] ? htmlspecialchars($r['nome_governante']) : '-';
            $dataFmt = date("d/m/Y", strtotime($r['data_fundacao']));
            echo "<tr>
                <td>{$r['id_cidade']}</td>
                <td>" . htmlspecialchars($r['nome_cidade']) . "</td>
                <td>" . htmlspecialchars($r['nome_pais']) . "</td>
                <td>" . number_format($r['populacao'], 0, ',', '.') . "</td>
                <td>" . number_format($r['area'], 2, ',', '.') . "</td>
                <td>" . htmlspecialchars($r['clima']) . "</td>
                <td>$gov</td>
                <td>$dataFmt</td>
                <td class='acoes'>
                    <a class='btn-editar' href='cidade.php?editar={$r['id_cidade']}'>Editar</a>
                    <a class='btn-excluir' href='cidade.php?excluir={$r['id_cidade']}' data-nome='" . htmlspecialchars($r['nome_cidade']) . "'>Excluir</a>
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
