// ============================================
// VALIDAÇÃO DE FORMULÁRIOS DE CADASTRO/EDIÇÃO
// ============================================
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll("form.cadastro").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            let valido = true;

            form.querySelectorAll("[required]").forEach(function (campo) {
                campo.classList.remove("campo-erro");

                if (!campo.value.trim()) {
                    campo.classList.add("campo-erro");
                    valido = false;
                }

                if (campo.type === "number" && campo.value !== "" && Number(campo.value) < 0) {
                    campo.classList.add("campo-erro");
                    valido = false;
                }
            });

            if (!valido) {
                e.preventDefault();
                alert("Por favor, preencha todos os campos obrigatórios corretamente.");
            }
        });
    });

    // ============================================
    // CONFIRMAÇÃO DE EXCLUSÃO
    // ============================================
    document.querySelectorAll(".btn-excluir").forEach(function (botao) {
        botao.addEventListener("click", function (e) {
            const nome = botao.getAttribute("data-nome") || "este registro";
            if (!confirm("Tem certeza que deseja excluir " + nome + "? Esta ação não pode ser desfeita.")) {
                e.preventDefault();
            }
        });
    });

    // ============================================
    // BUSCA DINÂMICA EM TABELAS
    // ============================================
    document.querySelectorAll(".busca input[data-tabela]").forEach(function (input) {
        input.addEventListener("keyup", function () {
            const termo = input.value.toLowerCase();
            const tabelaId = input.getAttribute("data-tabela");
            const tabela = document.getElementById(tabelaId);

            if (!tabela) return;

            tabela.querySelectorAll("tbody tr").forEach(function (linha) {
                const texto = linha.textContent.toLowerCase();
                linha.style.display = texto.includes(termo) ? "" : "none";
            });
        });
    });

});
