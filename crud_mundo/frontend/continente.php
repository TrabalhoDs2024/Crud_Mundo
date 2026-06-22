<?php include "../backend/conexao.php"; ?>
<?php

$mensagem = "";
$tipoMsg = "";
$editando = null;

// ---------- EXCLUIR ----------
if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];

    $check = $conn->query("SELECT COUNT(*) AS total FROM paises WHERE id_continente = $id");
    $row = $check->fetch_assoc();

    if ($row['total'] > 0) {
        $mensagem = "Não é possível excluir: existem países vinculados a este continente.";
        $tipoMsg = "erro";
    } else {
        $conn->query("DELETE FROM continentes WHERE id_continente = $id");
        $mensagem = "Continente excluído com sucesso.";
        $tipoMsg = "sucesso";
    }
}

// ---------- CARREGAR PARA EDIÇÃO ----------
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $res = $conn->query("SELECT * FROM continentes WHERE id_continente = $id");
    $editando = $res->fetch_assoc();
}

// ---------- SALVAR (INSERIR OU ATUALIZAR) ----------
if (isset($_POST['salvar'])) {
    $nome = trim($_POST['nome']);
    $populacao = $_POST['populacao'];
    $area = $_POST['area'];
    $id_edicao = $_POST['id_continente'] ?? '';

    if ($nome === '' || $populacao === '' || $area === '') {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipoMsg = "erro";
    } else {
        $stmt = null;

        if ($id_edicao !== '') {
            // UPDATE
            $stmt = $conn->prepare(
                "UPDATE continentes SET nome = ?, populacao = ?, area = ? WHERE id_continente = ?"
            );
            $stmt->bind_param("sddi", $nome, $populacao, $area, $id_edicao);
            $mensagemOk = "Continente atualizado com sucesso.";
        } else {
            // INSERT
            $stmt = $conn->prepare(
                "INSERT INTO continentes (nome, populacao, area, total_paises) VALUES (?, ?, ?, 0)"
            );
            $stmt->bind_param("sdd", $nome, $populacao, $area);
            $mensagemOk = "Continente cadastrado com sucesso.";
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

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Continentes - Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>MUNDO</h1>
</header>

<main class="container">

    <h1>CONTINENTES</h1>

    <?php if ($mensagem): ?>
        <div class="msg <?= $tipoMsg ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <h2><?= $editando ? "Editar Continente" : "Novo Continente" ?></h2>

    <form method="POST" class="cadastro">

        <?php if ($editando): ?>
            <input type="hidden" name="id_continente" value="<?= $editando['id_continente'] ?>">
        <?php endif; ?>

        <label>Nome
            <input type="text" name="nome" placeholder="Nome do continente" required
                   value="<?= $editando ? htmlspecialchars($editando['nome']) : '' ?>">
        </label>

        <label>População
            <input type="number" name="populacao" placeholder="População" required min="0"
                   value="<?= $editando ? $editando['populacao'] : '' ?>">
        </label>

        <label>Área (km²)
            <input type="number" name="area" placeholder="Área em km²" step="0.01" required min="0"
                   value="<?= $editando ? $editando['area'] : '' ?>">
        </label>

        <button name="salvar"><?= $editando ? "Atualizar" : "Salvar" ?></button>

        <?php if ($editando): ?>
            <a href="continente.php" class="btn btn-cancelar" style="text-align:center; line-height:45px; grid-column: 1 / -1;">Cancelar edição</a>
        <?php endif; ?>

    </form>

    <h2>Tabela de Continentes</h2>

    <div class="busca">
        <input type="text" placeholder="Buscar continente pelo nome..." data-tabela="tabelaContinentes">
    </div>

    <div class="tabela-wrapper">
    <table id="tabelaContinentes">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>População</th>
            <th>Área (km²)</th>
            <th>Total de Países</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $res = $conn->query("SELECT * FROM continentes ORDER BY nome");

        while ($r = $res->fetch_assoc()) {
            echo "<tr>
                <td>{$r['id_continente']}</td>
                <td>" . htmlspecialchars($r['nome']) . "</td>
                <td>" . number_format($r['populacao'], 0, ',', '.') . "</td>
                <td>" . number_format($r['area'], 2, ',', '.') . "</td>
                <td>{$r['total_paises']}</td>
                <td class='acoes'>
                    <a class='btn-editar' href='continente.php?editar={$r['id_continente']}'>Editar</a>
                    <a class='btn-excluir' href='continente.php?excluir={$r['id_continente']}' data-nome='" . htmlspecialchars($r['nome']) . "'>Excluir</a>
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
