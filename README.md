# StickerManager — Copa do Mundo 2026

Plataforma PHP para gestão de coleção de figurinhas da Copa do Mundo 2026.

## Stack
- PHP 8.3 (PDO MySQL)
- MySQL 5.7+
- Bootstrap 5.3 (via CDN)
- FPDF (incluso em `vendor_local/fpdf`)

## Instalação (XAMPP / Laragon)

1. Copie a pasta `stickermanager` para `htdocs/` (XAMPP) ou `www/` (Laragon).
2. Abra phpMyAdmin e importe `sql/schema.sql` (cria o banco `stickermanager` com todas as tabelas e seed).
3. Ajuste credenciais em `config/database.php` se necessário (padrão: `root` sem senha).
4. Acesse `http://localhost/stickermanager/public/`.

## Usuário de teste
- Email: `admin@stickermanager.com`
- Senha: `admin123`

## Estrutura
```
stickermanager/
├── config/database.php       # Conexão PDO
├── includes/                 # Header, footer, auth guard
├── public/                   # Front-controller (index.php) e assets
├── src/
│   ├── controllers/          # Lógica das telas
│   └── models/               # Acesso a dados
├── sql/schema.sql            # DDL + seed
└── vendor_local/fpdf/        # Biblioteca de geração de PDF
```

## Funcionalidades
- Autenticação por sessão PHP
- CRUD de figurinhas (admin)
- Checklist digital (marcar obtida / repetida)
- Filtros: país, posição, status
- Módulo de repetidas (trocas)
- Calculadora de progresso
- Relatório PDF (FPDF)
