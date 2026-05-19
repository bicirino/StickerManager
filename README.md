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
## Estrutura do projeto 🚧
```
stickermanager/
├── buscar.php          # Tela com os 3 filtros avançados de busca (País, Posição, Status)
├── conexao.php         # Script de conexão com o banco MySQL (PDO ou mysqli)
├── editar.php          # Formulário e processamento para ATUALIZAR figurinhas 
├── excluir.php         # Script de processamento para EXCLUIR figurinhas/registros 
├── index.php           # Dashboard principal e LEITURA do checklist/inventário 
├── inserir.php         # Formulário e processamento para CADASTRAR novas figurinhas 
├── logar.php           # Tela de autenticação e processamento do formulário de Login
├── logout.php          # Script que destrói a Session e desloga o usuário de forma segura
├── relatorio.php       # Script que roda a query consolidada e gera o arquivo PDF para download
├── script.sql          # Script para o banco de dados (Criação das 6 tabelas + Carga Inicial/Seed)
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
