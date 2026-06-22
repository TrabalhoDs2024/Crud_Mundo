# CRUD Mundo — Países, Cidades, Continentes e Governantes

## Estrutura de pastas

```
projeto/
├── backend/
│   └── conexao.php          # conexão com o banco (mysqli)
├── frontend/
│   ├── index.php             # página inicial
│   ├── continente.php        # CRUD de continentes
│   ├── pais.php               # CRUD de países
│   ├── cidade.php             # CRUD de cidades
│   ├── governante.php         # CRUD de governantes
│   ├── estatisticas.php       # estatísticas (desafio extra)
│   ├── style.css
│   └── script.js
└── sql/
    └── bd_mundo.sql           # script de criação do banco
```

## Como instalar

1. Importe `sql/bd_mundo.sql` no MySQL (cria o banco `bd_mundo`, tabelas, triggers e dados de exemplo).
2. Ajuste as credenciais em `backend/conexao.php` se necessário (usuário, senha).
3. Para o `include` funcionar, mantenha as pastas `frontend` e `backend` no mesmo nível (uma ao lado da outra) dentro do diretório servido pelo Apache/XAMPP/Laragon (ex.: `htdocs/projeto/`).
4. Acesse `frontend/index.php` no navegador.

## Funcionalidades implementadas

- **Continentes**: inserir, listar, editar, excluir (bloqueia exclusão se houver países vinculados). `total_paises` é atualizado automaticamente via triggers.
- **Países**: inserir, listar, editar, excluir (bloqueia exclusão se houver cidades vinculadas). Associação com continente e governante via `<select>`.
- **Cidades**: inserir, listar, editar, excluir. Associadas a um país (obrigatório) e a um governante (opcional). Exclusão em cascata se o país for removido.
- **Governantes**: inserir, listar, editar, excluir (bloqueia exclusão se vinculado a país ou cidade).
- **Validações**: campos obrigatórios verificados em JS antes do envio; confirmação de exclusão via `confirm()`.
- **Busca dinâmica**: campo de busca em cada tabela filtra linhas em tempo real via JS.
- **Estatísticas**: cidade mais populosa por país, total de cidades por continente, contagem geral de registros.

## Segurança

Todas as queries de INSERT/UPDATE usam `prepared statements` (`bind_param`), evitando SQL Injection — diferente da versão original que concatenava `$_POST` diretamente na query.
