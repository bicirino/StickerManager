# StickerManager 🗂

## O quê é? 🤔
StickerManager é uma plataforma de gestão inteligente e estratégica voltada para colecionadores de álbuns de figurinhas (focada inicialmente na Copa do Mundo). O projeto funciona como um ecossistema digital que substitui as obsoletas listas de papel por um controle de inventário em tempo real, focado em alta performance para trocas físicas. A escolha deste sistema advém da necessidade de solucionar problemas logísticos e financeiros comuns ao hobby de colecionismo em larga escala. 

As **funcionalidades** centrais incluem:
- **Checklist Digital Dinâmico**: Interface otimizada para marcação rápida de figurinhas
por número e seção (Seleções, Mascotes, Legends), permitindo que o usuário
visualize instantaneamente o estado da sua coleção;

- **Gestão de Excedentes (Foco em Trocas)**: Um repositório específico para
figurinhas repetidas, desenhado para facilitar a consulta rápida em "pontos de troca"
(banquinhas). O usuário consegue identificar em segundos se possui o item que
outra pessoa procura;

- **Filtros Inteligentes**: Capacidade de filtrar a coleção por país, posição do jogador e
status (obtidas vs. faltantes), essencial para a organização estratégica do
colecionador.

O StickerManager atua diretamente na: 
  - **Otimização de Recursos**: O sistema ajuda o usuário a decidir quando parar de
comprar pacotes e focar em trocas, economizando dinheiro através da "Calculadora
de Progresso";

-  **Agilidade em Ambiente Físico**: Em encontros de colecionadores (como na
"banquinha"), o tempo é escasso. O módulo de repetidas digital elimina a
necessidade de carregar o álbum físico ou listas rasuradas, evitando erros de troca e
duplicidade;

- **Confiabilidade de Dados**: A centralização do inventário em um banco de dados
relacional (MySQL) garante que a informação esteja sempre disponível e correta,
facilitando o gerenciamento das mais de 900 figurinhas simultaneamente

--- 

## Requisitos do projeto 🚀

Para atender integralmente ao escopo da proposta, o sistema contempla:
- [ ] **Domínio Complexo:** Modelagem dividida em 6 tabelas relacionais (*"Usuários", "Minha_Colecao","Figurinhas", "Selecoes", "Posicao", "Categoria"*);   
- [ ] **CRUD Completo:** Criação, leitura, atualização e exclusão de dados de figurinhas e coleções; 
- [ ] **Módulo de Autenticação:** Controle de acesso seguro utilizando escopo de `Sessões` em PHP;
- [ ] **Busca Avançada:** Mapeamento de 3 filtros (País, Posição, Status) com tipagens de dados distintas;
- [ ] **Emissão de Relatório:** Geração de documento consolidado nativo em formato PDF;
- [ ] **Carga Inicial de Dados:** Script SQL contendo a estrutura e dados padrão de teste;
- [ ] **Interface Responsiva:** Uso extensivo de componentes visuais do Bootstrap.

---
## Tecnologias ⚙ 

- **Backend**: PHP 8.3 ;
- **Banco de Dados (Persistência de Dados)**: MySQL;
- **Interface do Usuário**: Bootstrap 5.3;
- **Motor de Relatórios (Geração de PDF)**: FPDF ou Dompdf (Bibliotecas PHP leves e ideais que permitem converter consultas SQL diretamente em arquivos PDF);

---
## 🚀 Guia de Início Rápido

### Pré-requisitos
- **PHP 8.3+** com extensão `mysqli` habilitada
- **MySQL 5.7+** ou **MariaDB** instalado e em execução
- **Servidor Web** (Apache/Nginx com suporte a PHP)
- **Navegador Web** moderno

**💡 Recomendação**: Use **XAMPP** (ambiente acadêmico padrão) - inclui Apache, MySQL e PHP pré-configurados. [Baixe aqui](https://www.apachefriends.org/)

### Passo 1: Preparar o Projeto

1. Copie a pasta do projeto para: `C:\xampp\htdocs\StickerManager\`
2. Inicie o painel de controle do XAMPP
3. Ative os serviços: **Apache** e **MySQL**


### Passo 2: Configurar o Banco de Dados

1. Abra seu navegador e acesse: `http://localhost/phpmyadmin`
2. Clique em **"Novo"** na barra lateral esquerda
3. Crie uma nova base de dados com o nome: `sticker_manager`
4. Selecione a codificação: **utf8mb4_unicode_ci**
5. Clique em **"Criar"**
6. Abra o arquivo `db/script.sql` com um editor de texto
7. Copie todo o conteúdo do arquivo
8. No phpMyAdmin, clique na aba **"SQL"**
9. Cole o conteúdo e clique em **"Executar"**

### Passo 3: Configurar Credenciais do Banco de Dados
1. Abra o arquivo `db/conexao.php` em um editor de texto
2. Localize as linhas com as credenciais (ao redor da linha 12-16):
   ```php
   $DB_HOST = 'localhost';    // Host do seu MySQL
   $DB_USER = 'root';         // Seu usuário MySQL
   $DB_PASS = '';             // Sua senha MySQL (deixe em branco se não tiver)
   $DB_NAME = 'sticker_manager';
   $DB_PORT = 3306;
   ```
3. Ajuste com suas credenciais reais do MySQL
4. Salve o arquivo

### Passo 4: Iniciar o Servidor Web

O servidor já está pré-configurado no XAMPP! Basta garantir que **Apache** e **MySQL** estão ativados no painel de controle do XAMPP. Nenhuma configuração adicional necessária.

### Passo 5: Acessar a Aplicação

1. Abra seu navegador
2. Acesse: `http://localhost/StickerManager/`
3. Você será redirecionado para a página de login
4. Use as credenciais de teste:
   - **Usuário**: `usuario_teste`
   - **Senha**: `teste123`

### Passo 6: Explorar a Aplicação
- **Dashboard**: Visualize seu checklist de figurinhas
- **Inserir**: Adicione novas figurinhas à sua coleção
- **Buscar**: Use os filtros avançados por País, Posição e Status
- **Editar**: Atualize informações de figurinhas
- **Excluir**: Remova figurinhas da coleção
- **Relatório**: Gere um PDF com o resumo da sua coleção
- **Logout**: Saia da aplicação com segurança

### ✅ Pronto!
Sua aplicação StickerManager está rodando! Comece a gerenciar sua coleção de figurinhas.

---
## Estrutura do projeto 🚧
```
stickermanager/
├── buscar.php          # Tela com os 3 filtros avançados de busca (País, Posição, Status)
├── db/                 # NOVA PASTA: Centraliza toda a camada e configuração de dados
│   ├── conexao.php     # Script centralizado de instância de conexão com o banco MySQL
│   └── script.sql      # Script do banco de dados (Criação das 6 tabelas + Carga Inicial/Seed)
├── editar.php          # Formulário e processamento para ATUALIZAR figurinhas 
├── excluir.php         # Script de processamento para EXCLUIR figurinhas/registros 
├── index.php           # Dashboard principal e LEITURA do checklist/inventário 
├── inserir.php         # Formulário e processamento para CADASTRAR novas figurinhas 
├── logar.php           # Tela de autenticação e processamento do formulário de Login
├── logout.php          # Script que destrói a Session e desloga o usuário de forma segura
├── relatorio.php       # Script que roda a query consolidada e gera o arquivo PDF para download
├── sessao.php          # Arquivo que contém a trava de segurança (valida se o usuário está logado)
├── LICENSE             # Arquivo contendo os termos da Licença MIT do projeto
└── README.md           # Documentação completa do projeto
```
*obs: estrutura sujeita a alterações* 

--- 

## 📄 Licença

Este projeto está licenciado sob a **Licença MIT**. Isso significa que você pode modificar, distribuir e usar o código livremente para fins acadêmicos ou comerciais, desde que mantenha os créditos originais.

```text
MIT License

Copyright (c) 2026 [Seu Nome] & [Nome do Parceiro]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
