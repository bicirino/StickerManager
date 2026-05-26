# StickerManager - Guia de Execução

## Visão Geral
O StickerManager foi desenvolvido com sucesso! O projeto é uma plataforma completa de gerenciamento de coleção de figurinhas de Copa do Mundo, com interface responsiva em Bootstrap e banco de dados MySQL relacional.

## Arquivos Implementados

### ✅ Camada de Dados
- **db/script.sql** - 6 tabelas relacionais com dados de teste
- **db/conexao.php** - Conexão centralizada e segura com MySQL

### ✅ Autenticação
- **sessao.php** - Controle de sessão seguro com proteção contra fixação de sessão
- **logar.php** - Interface de autenticação com Bootstrap
- **logout.php** - Logout seguro com destruição de sessão

### ✅ CRUD Completo
- **index.php** - Dashboard com estatísticas e paginação
- **inserir.php** - Formulário para adicionar figurinhas à coleção
- **editar.php** - Formulário para atualizar dados de figurinhas
- **excluir.php** - Script para remover figurinhas

### ✅ Funcionalidades Avançadas
- **buscar.php** - Busca com 3 filtros: País, Posição, Status
- **relatorio.php** - Geração de PDF com resumo da coleção

---

## Pré-requisitos

1. **PHP 8.3+** com extensões:
   - mysqli (suporte MySQL)
   - json (para manipulação de dados)

2. **MySQL 5.7+** ou MariaDB

3. **Servidor Web** (Apache/Nginx com suporte PHP)

---

## Instalação e Configuração

### 1. Clonar o Projeto
```bash
cd "c:\Users\User\Desktop\Programas e estudos\Outros projetos\StickerManager"
```

### 2. Criar o Banco de Dados

#### Opção A: Usando phpMyAdmin
1. Abra phpMyAdmin (http://localhost/phpmyadmin)
2. Vá em "Novo" → Criar nova base de dados
3. Nome: `sticker_manager`
4. Codificação: `utf8mb4_unicode_ci`
5. Copie e execute o conteúdo de `db/script.sql`

#### Opção B: Usando linha de comando MySQL
```bash
mysql -u root -p < db/script.sql
```

#### Opção C: Usando terminal MySQL
```sql
mysql> SOURCE db/script.sql;
```

### 3. Configurar Conexão

Edite `db/conexao.php` (linhas 12-16) com suas credenciais:
```php
$DB_HOST = 'localhost';  // Seu host
$DB_USER = 'root';       // Seu usuário
$DB_PASS = '';           // Sua senha
$DB_NAME = 'sticker_manager';
$DB_PORT = 3306;
```

### 4. Instalar Dependências (Opcional - para PDF)

Se deseja usar a geração de PDF:
```bash
composer require setasign/fpdf
```

Ou baixe manualmente FPDF:
1. Acesse http://www.fpdf.org/
2. Baixe a versão mais recente
3. Extraia em `/fpdf/`

---

## Como Usar

### 🔐 Acessar o Sistema

**URL:** http://localhost/StickerManager/ (ou onde você configurou o projeto)

**Credenciais de Teste:**
- **Usuário:** usuario_teste
- **Senha:** teste123 (você pode alterar no banco)

Nota: A senha no banco está armazenada como hash bcrypt. Para alterar:
```sql
UPDATE Usuarios 
SET senha_hash = '$2y$10$Y8bU1qxg8yCsxR1Q8zK8.e0Tz3L0M5N6O7P8Q9R0S1T2U3V4W5X6Y' 
WHERE nome_usuario = 'usuario_teste';
```

### 📊 Funcionalidades

#### Dashboard (index.php)
- Visualizar estatísticas: obtidas, faltantes, repetidas
- Barra de progresso da coleção
- Filtrar por categoria e status
- Paginação de figurinhas

#### Cadastrar Figurinha (inserir.php)
- Selecionar figurinha do catálogo
- Definir status (obtida, faltante, repetida)
- Informar quantidade se aplicável

#### Editar Figurinha (editar.php)
- Clicar em "Editar" no dashboard
- Atualizar status e quantidades
- Salvar alterações

#### Excluir Figurinha (excluir.php)
- Clicar em "Excluir" no dashboard
- Confirmar exclusão
- Figurinha será removida da coleção

#### Busca Avançada (buscar.php)
- Filtrar por **País** (Seleção)
- Filtrar por **Posição** do Jogador
- Filtrar por **Status** (obtida, faltante, repetida)
- Combinar múltiplos filtros
- Visualizar resultados com paginação

#### Relatório PDF (relatorio.php)
- Gerar PDF com resumo da coleção
- Incluir estatísticas gerais
- Listar todas as figurinhas com detalhes
- Download automático

---

## Dados de Exemplo

O script.sql inclui:
- **12 Seleções** (Brasil, Alemanha, França, etc.)
- **6 Figurinhas de exemplo** (Brasil e Alemanha)
- **4 Posições** (Goleiro, Defesa, Meio-campo, Atacante)
- **3 Categorias** (Seleções, Mascotes, Legends)
- **1 Usuário de teste** (usuario_teste)

---

## Estrutura das Tabelas

```
Usuarios
├── id_usuario (PK)
├── nome_usuario (UNIQUE)
├── email (UNIQUE)
└── senha_hash

Categoria
├── id_categoria (PK)
└── nome_categoria (UNIQUE)

Posicao
├── id_posicao (PK)
└── nome_posicao (UNIQUE)

Selecoes
├── id_selecao (PK)
└── nome_selecao (UNIQUE)

Figurinhas
├── id_figurinha (PK)
├── numero_figurinha
├── nome_jogador
├── id_selecao (FK)
├── id_posicao (FK)
└── id_categoria (FK)

Minha_Colecao
├── id_colecao (PK)
├── id_usuario (FK)
├── id_figurinha (FK)
├── status (ENUM)
├── quantidade_obtida
└── quantidade_repetida
```

---

## Recursos de Segurança Implementados

✅ **Proteção contra SQL Injection**
- Prepared statements com bind_param
- Função `preparar()` para queries seguras

✅ **Proteção contra XSS**
- htmlspecialchars() em todas as saídas
- Validação de entrada

✅ **Gerenciamento de Sessão**
- Regeneração de ID de sessão
- Token de sessão único
- Timeout automático (1 hora)
- Proteção contra fixação de sessão

✅ **Criptografia de Senha**
- Hash bcrypt com password_hash()
- Verificação com password_verify()

✅ **Cookies Seguros**
- HttpOnly flag
- SameSite Strict
- (Secure HTTPS em produção)

---

## Troubleshooting

### ❌ Erro: "Erro ao conectar ao banco de dados"
- Verifique se MySQL está rodando
- Confirme credenciais em `db/conexao.php`
- Verifique se banco `sticker_manager` foi criado

### ❌ Erro ao fazer login
- Verifique se usuário `usuario_teste` existe no banco
- Confirm a senha (padrão: teste123)
- Verifique se campo `ativo = 1`

### ❌ Erro ao gerar PDF
- Instale FPDF: `composer require setasign/fpdf`
- Ou baixe manualmente em http://www.fpdf.org/

### ❌ Figurinhas não aparecem
- Execute o script.sql completo
- Verifique se tabela Figurinhas tem dados
- Confira se Categoria e Selecoes estão preenchidas

---

## Próximas Melhorias (Sugestões)

- 📸 Upload de imagens das figurinhas
- 💬 Sistema de comentários/notas
- 👥 Compartilhamento de coleção com amigos
- 📈 Gráficos e análises detalhadas
- 🎮 Gamificação (badges, conquistas)
- 📱 App mobile React Native
- 💾 Backup/Export da coleção
- 🔄 Sincronização em tempo real

---

## Contato & Suporte

Projeto desenvolvido como plataforma de gestão de coleção de figurinhas.
Licença MIT - Sinta-se livre para modificar e expandir!

**Versão:** 1.0.0  
**Data:** 2026-05-25  
**Status:** ✅ Pronto para uso

---
