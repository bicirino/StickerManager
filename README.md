# 🏆 StickerManager — Copa do Mundo 2026

Plataforma PHP para gestão de coleção de figurinhas da Copa do Mundo 2026.

---

## 💻 Stack Tecnológico
* **PHP 8.3** (PDO MySQL)
* **MySQL 5.7+**
* **Bootstrap 5.3** (via CDN)
* **FPDF** (incluso em `vendor_local/fpdf`)

---

## 🚀 Instalação (XAMPP / Laragon)

1. Navegue até a pasta `htdocs/` do seu XAMPP (geralmente em `C:\xampp\htdocs`) pelo terminal e clone o repositório:
   ```bash
   git clone https://github.com/bicirino/StickerManager.git
   ```
2. No painel do XAMPP, clique em **"Start"** para iniciar os serviços do **Apache** e **MySQL**.
3. Abra o phpMyAdmin no seu navegador (`http://localhost/phpmyadmin/`) e clique em **"Novo"**, no menu lateral esquerdo.
4. Dê o nome de **`stickermanager`** e clique em **"Criar"**.
5. Em seguida, clique na aba **"Importar"** -> **"Escolher arquivo"** e selecione o arquivo `sql/schema.sql` (que está na pasta do projeto). Desça a tela e clique no botão **"Importar"**.
6. Ajuste as credenciais no arquivo `config/database.php`, se necessário (o padrão do XAMPP é usuário `root` sem senha).
7. Acesse o sistema pelo navegador: `http://localhost/StickerManager/public/`.

---

## 🛠️ Solução de Problemas (Conflito de Portas no XAMPP)

Se o seu MySQL do XAMPP fechar inesperadamente (erro de porta ocupada) ou o phpMyAdmin exibir erros de conexão (`Access denied`), siga o passo a passo abaixo para rodar o projeto na porta alternativa **3307**:

### 1. Alterar a porta do MySQL no XAMPP
1. No painel de controle do XAMPP, clique em **Config** (na linha do MySQL) e escolha **my.ini**.
2. Aperte `CTRL + F` para buscar por `3306` (geralmente aparece em dois lugares).
3. Mude ambos os valores para `3307`.
4. Salve o arquivo e clique em **Start** no MySQL.

### 2. Ajustar o phpMyAdmin para respeitar a nova porta
1. Abra o arquivo `C:\xampp\phpMyAdmin\config.inc.php`.
2. Altere a configuração do `host` de `localhost` para `127.0.0.1` (isso força o uso da porta TCP):
   ```php
   $cfg['Servers'][$i]['host'] = '127.0.0.1';
   ```
3. Adicione a linha definindo a nova porta logo abaixo:
   ```php
   $cfg['Servers'][$i]['port'] = '3307';
   ```
4. Salve o arquivo e atualize o navegador em `http://localhost/phpmyadmin/`.

### 3. Atualizar a Conexão no Projeto
1. Abra o arquivo `config/database.php` na pasta do seu projeto.
2. Altere a constante ou variável de `host` para incluir a porta `3307`:
   ```php
   const DB_HOST = 'localhost;port=3307';
   ```

---

## 👤 Usuário de teste
* **Email:** `admin@stickermanager.com`
* **Senha:** `admin123`

---

## 📁 Estrutura de Arquivos
```text
StickerManager/
├── config/database.php       # Conexão PDO
├── includes/                 # Header, footer, auth guard
├── public/                   # Front-controller (index.php) e assets
├── src/
│   ├── controllers/          # Lógica das telas
│   └── models/               # Acesso a dados
├── sql/schema.sql            # DDL + seed
└── vendor_local/fpdf/        # Biblioteca de geração de PDF
```

---

## ✨ Funcionalidades
* Autenticação por sessão PHP.
* CRUD completo de figurinhas (painel admin).
* Checklist digital (marcar figurinhas como obtidas / repetidas).
* Filtros avançados: busca por país, posição e status.
* Módulo de repetidas (organização para trocas).
* Calculadora de progresso da coleção.
* Relatório de figurinhas faltantes em PDF (FPDF).
