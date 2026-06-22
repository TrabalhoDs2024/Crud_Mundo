<?php include "../backend/conexao.php"; ?>
<?php

$mensagem = "";
$tipoMsg = "";
$editando = null;

// ---------- EXCLUIR ----------
if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];

    $check1 = $conn->query("SELECT COUNT(*) AS total FROM paises WHERE id_governante = $id");
    $check2 = $conn->query("SELECT COUNT(*) AS total FROM cidades WHERE id_governante = $id");
    $row1 = $check1->fetch_assoc();
    $row2 = $check2->fetch_assoc();

    if ($row1['total'] > 0 || $row2['total'] > 0) {
        $mensagem = "Não é possível excluir: este governante está vinculado a um país ou cidade.";
        $tipoMsg = "erro";
    } else {
        $conn->query("DELETE FROM governantes WHERE id_governante = $id");
        $mensagem = "Governante excluído com sucesso.";
        $tipoMsg = "sucesso";
    }
}

// ---------- CARREGAR PARA EDIÇÃO ----------
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $res = $conn->query("SELECT * FROM governantes WHERE id_governante = $id");
    $editando = $res->fetch_assoc();
}

// ---------- SALVAR (INSERIR OU ATUALIZAR) ----------
if (isset($_POST['salvar'])) {
    $nome           = trim($_POST['nome']);
    $partido        = trim($_POST['partido']);
    $nascimento     = $_POST['nascimento'];
    $idade          = $_POST['idade'];
    $inicioMandato  = $_POST['inicio'];
    $fimMandato     = $_POST['fim'] !== '' ? $_POST['fim'] : null;
    $id_edicao      = $_POST['id_governante'] ?? '';

    if ($nome === '' || $partido === '' || $nascimento === '' || $idade === '' || $inicioMandato === '') {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipoMsg = "erro";
    } else {
        if ($id_edicao !== '') {
            // UPDATE
            $stmt = $conn->prepare(
                "UPDATE governantes SET nome=?, partido_politico=?, data_nascimento=?, idade=?, data_inicio_mandato=?, data_final_mandato=?
                 WHERE id_governante=?"
            );
            $stmt->bind_param(
                "sssisis",
                $nome, $partido, $nascimento, $idade, $inicioMandato, $fimMandato, $id_edicao
            );
            $mensagemOk = "Governante atualizado com sucesso.";
        } else {
            // INSERT
            $stmt = $conn->prepare(
                "INSERT INTO governantes (nome, partido_politico, data_nascimento, idade, data_inicio_mandato, data_final_mandato)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssiss",
                $nome, $partido, $nascimento, $idade, $inicioMandato, $fimMandato
            );
            $mensagemOk = "Governante cadastrado com sucesso.";
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
    <title>Governantes - Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>MUNDO</h1>
</header>

<main class="container">

    <h1>GOVERNANTES</h1>

    <?php if ($mensagem): ?>
        <div class="msg <?= $tipoMsg ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <h2><?= $editando ? "Editar Governante" : "Novo Governante" ?></h2>

    <form method="POST" class="cadastro">

        <?php if ($editando): ?>
            <input type="hidden" name="id_governante" value="<?= $editando['id_governante'] ?>">
        <?php endif; ?>

        <label>Nome
            <input type="text" name="nome" placeholder="Nome" required
                   value="<?= $editando ? htmlspecialchars($editando['nome']) : '' ?>">
        </label>

        <label>Partido Político
            <input type="text" name="partido" placeholder="Partido político" required
                   value="<?= $editando ? htmlspecialchars($editando['partido_politico']) : '' ?>">
        </label>

        <label>Data de Nascimento
            <input type="date" name="nascimento" required
                   value="<?= $editando ? $editando['data_nascimento'] : '' ?>">
        </label>

        <label>Idade
            <input type="number" name="idade" placeholder="Idade" required min="0"
                   value="<?= $editando ? $editando['idade'] : '' ?>">
        </label>

        <label>Início do Mandato
            <input type="date" name="inicio" required
                   value="<?= $editando ? $editando['data_inicio_mandato'] : '' ?>">
        </label>

        <label>Fim do Mandato
            <input type="date" name="fim"
                   value="<?= $editando && $editando['data_final_mandato'] ? $editando['data_final_mandato'] : '' ?>">
        </label>

        <button name="salvar"><?= $editando ? "Atualizar" : "Salvar" ?></button>

        <?php if ($editando): ?>
            <a href="governante.php" class="btn btn-cancelar" style="text-align:center; line-height:45px; grid-column: 1 / -1;">Cancelar edição</a>
        <?php endif; ?>

    </form>

    <h2>Tabela de Governantes</h2>

    <div class="busca">
        <input type="text" placeholder="Buscar governante pelo nome..." data-tabela="tabelaGovernantes">
    </div>

    <div class="tabela-wrapper">
    <table id="tabelaGovernantes">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Partido</th>
            <th>Nascimento</th>
            <th>Idade</th>
            <th>Início Mandato</th>
            <th>Fim Mandato</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $res = $conn->query("SELECT * FROM governantes ORDER BY nome");

        while ($r = $res->fetch_assoc()) {
            $nasc = date("d/m/Y", strtotime($r['data_nascimento']));
            $inicio = date("d/m/Y", strtotime($r['data_inicio_mandato']));
            $fim = $r['data_final_mandato'] ? date("d/m/Y", strtotime($r['data_final_mandato'])) : '-';

            echo "<tr>
                <td>{$r['id_governante']}</td>
                <td>" . htmlspecialchars($r['nome']) . "</td>
                <td>" . htmlspecialchars($r['partido_politico']) . "</td>
                <td>$nasc</td>
                <td>{$r['idade']}</td>
                <td>$inicio</td>
                <td>$fim</td>
                <td class='acoes'>
                    <a class='btn-editar' href='governante.php?editar={$r['id_governante']}'>Editar</a>
                    <a class='btn-excluir' href='governante.php?excluir={$r['id_governante']}' data-nome='" . htmlspecialchars($r['nome']) . "'>Excluir</a>
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
