# 🛡️ NUPRO+ V2

> **Sistema de Proteção contra Violência à Mulher**  
> Projeto Acadêmico — Engenharia de Software | FATEC  
> PHP 8+ · MySQL · Docker · Lei Maria da Penha (Nº 11.340/2006)

---

## 📋 Índice

1. [Visão Geral](#-visão-geral)
2. [Funcionalidades](#-funcionalidades)
3. [Tecnologias](#-tecnologias)
4. [Requisitos](#-requisitos)
5. [Instalação e Execução](#-instalação-e-execução)
6. [Arquitetura do Sistema](#-arquitetura-do-sistema)
7. [Modelo de Banco de Dados](#-modelo-de-banco-de-dados)
8. [Fluxo de Autenticação](#-fluxo-de-autenticação)
9. [Controle de Acesso por Perfil](#-controle-de-acesso-por-perfil-rbac)
10. [Estrutura de Arquivos](#-estrutura-de-arquivos)
11. [Segurança](#-segurança)
12. [Logins de Demonstração](#-logins-de-demonstração)
13. [Próximos Passos](#-próximos-passos)

---

## 🎯 Visão Geral

O **NUPRO+** é um sistema de gestão local desenvolvido para apoiar Núcleos de Proteção à Mulher, integrando em uma única plataforma:

- Registro e acompanhamento de ocorrências de violência doméstica
- Gestão de vítimas com proteção de dados sensíveis (criptografia AES-256)
- Coordenação da rede de apoio multidisciplinar (saúde, psicologia, jurídico, segurança)
- Controle de medidas protetivas e alertas de emergência
- Dashboard analítico com gráficos e mapa de calor por região
- Trilha completa de auditoria para conformidade com LGPD

---

## ✅ Funcionalidades

| Módulo | Descrição |
|---|---|
| **Dashboard** | KPIs, gráficos de risco/região/horário, alertas de pânico recentes |
| **Vítimas** | Cadastro com endereço e telefone criptografados, flag de sigilo |
| **Ocorrências / B.O.** | Registro com nível de risco (A/B/C), geolocalização, formulário complementar |
| **Rede de Apoio** | Acompanhamento multidisciplinar por ocorrência |
| **Posto de Saúde** | Registro de passagens e encaminhamentos psicológicos |
| **Medidas Protetivas** | Controle de status, vara judicial e descumprimentos |
| **Botão de Pânico** | Registro de alertas de emergência com localização |
| **Mapa** | Visualização geográfica de ocorrências via Leaflet/OpenStreetMap |
| **Auditoria** | Trilha de todas as ações do sistema (restrito a admin/diretoria) |
| **Usuários** | Gestão de contas e perfis de acesso (restrito a admin) |

---

## 🛠️ Tecnologias

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8+ |
| Banco de Dados | MySQL 5.7+ / MariaDB |
| Servidor Local | (Docker + MySQL) |
| Frontend | HTML5, CSS3 (Custom Properties), JavaScript ES6 |
| Gráficos | [Chart.js](https://www.chartjs.org/) (via CDN) |
| Mapas | [Leaflet.js](https://leafletjs.com/) + OpenStreetMap (via CDN) |
| Criptografia | OpenSSL — AES-256-CBC |
| Autenticação | PHP Sessions + `password_hash()` (bcrypt) |

---

## 📦 Requisitos

- XAMPP com Apache + MySQL iniciados
- PHP 8.0 ou superior
- Extensão OpenSSL habilitada no PHP (`extension=openssl` no php.ini)
- Conexão com internet (para carregar Chart.js e Leaflet via CDN)

---

# NUPRO+ V2 — Guia de Instalação com Docker

> Projeto acadêmico | FATEC | PHP 8.2 + MySQL 8.0 + Docker

---

## 📋 Pré-requisitos

Você precisa ter instalado:

| Software | Versão mínima | Download |
|---|---|---|
| Docker Desktop | 4.x | https://www.docker.com/products/docker-desktop |
| Git (opcional) | qualquer | https://git-scm.com |

> **Não** precisa de XAMPP, PHP, Apache ou MySQL instalados na máquina.

---

## 🚀 Instalação passo a passo

### 1. Baixe / extraia o projeto

Se recebeu um `.zip`, extraia para uma pasta.  
Se clonou via Git:
```bash
git clone <url-do-repositorio> nupro-v2
cd nupro-v2
```

### 2. Inicie os containers

Abra o terminal **dentro da pasta do projeto** (onde está o `docker-compose.yml`) e execute:

```bash
docker compose up -d
```

Na primeira vez isso vai:
- Baixar as imagens do PHP e MySQL (~500MB)
- Criar o banco de dados automaticamente
- Executar o `sql/schema.sql` com todas as tabelas e dados de demonstração
- Iniciar o servidor Apache

⏳ Aguarde cerca de **30–60 segundos** na primeira execução.

### 3. Acesse o sistema

Abra o navegador em: **http://localhost:8080**

---

## 👤 Usuários de demonstração

Todos os usuários têm a senha: **`nupro123`**

| E-mail | Perfil | Acesso |
|---|---|---|
| `admin@nupro.local` | Administrador | Total |
| `coord@nupro.local` | Coordenação | Gerencial + auditoria |
| `pm@nupro.local` | Polícia Militar | Ocorrências + pânico |
| `saude@nupro.local` | Saúde | Atendimentos médicos |
| `psico@nupro.local` | Psicologia | Rede de apoio |
| `assistencia@nupro.local` | Assistência Social | Rede de apoio |
| `diretoria@nupro.local` | Diretoria | Dashboard analítico |

---

## 📁 Estrutura do projeto

```
nupro-v2/
├── docker-compose.yml       ← Orquestração Docker
├── sql/
│   └── schema.sql           ← Banco de dados + dados demo
├── includes/
│   ├── config.php           ← Core: DB, sessão, RBAC, crypto, audit
│   ├── header.php           ← Layout do sidebar + topbar
│   └── footer.php           ← Fechamento do HTML
├── index.php                ← Login
├── logout.php               ← Logout
├── dashboard.php            ← KPIs e gráficos
├── victims.php              ← Cadastro de vítimas
├── occurrences.php          ← Registro de ocorrências
├── protective_measures.php  ← Medidas protetivas
├── health_visits.php        ← Atendimentos de saúde
├── support_network.php      ← Rede de apoio
├── panic_button.php         ← Botão de pânico
├── map.php                  ← Mapa Leaflet
├── users.php                ← Gestão de usuários
└── audit.php                ← Trilha de auditoria
```

---

## 🛠️ Comandos úteis

### Ver logs do servidor
```bash
docker compose logs -f app
```

### Ver logs do banco
```bash
docker compose logs -f db
```

### Parar os containers
```bash
docker compose down
```

### Parar e apagar o banco (reset completo)
```bash
docker compose down -v
```

### Reiniciar após mudança de código
```bash
docker compose restart app
```

### Acessar o MySQL diretamente
```bash
docker exec -it nupro_db mysql -u nupro -pnupro123 nupro_v2
```

### Acessar o container PHP
```bash
docker exec -it nupro_app bash
```

---

## ⚙️ Variáveis de ambiente

Definidas no `docker-compose.yml`:

| Variável | Valor padrão | Descrição |
|---|---|---|
| `DB_HOST` | `db` | Host do MySQL (nome do serviço Docker) |
| `DB_NAME` | `nupro_v2` | Nome do banco |
| `DB_USER` | `nupro` | Usuário MySQL |
| `DB_PASS` | `nupro123` | Senha MySQL |
| `ENCRYPTION_KEY` | `nupro_v2_chave_...` | Chave AES-256 para criptografia |
| `APP_TIMEZONE` | `America/Sao_Paulo` | Fuso horário |

---

## 🔒 Segurança implementada

| Mecanismo | Implementação |
|---|---|
| Autenticação | Sessão PHP + `password_verify()` bcrypt |
| RBAC | `require_roles([...])` em cada página |
| SQL Injection | 100% prepared statements (`mysqli`) |
| XSS | `htmlspecialchars()` via `e()` em todo output |
| Criptografia | AES-256-CBC para endereço e telefone |
| Auditoria | `audit_log()` em todos os eventos |
| PRG Pattern | Post/Redirect/Get em todos os formulários |

---

## ❓ Solução de problemas

**O site não abre / "Connection refused"**
```bash
docker compose ps   # verifique se os containers estão "Up"
docker compose logs app  # veja erros do PHP/Apache
```

**Erro de banco de dados**
```bash
docker compose logs db  # veja se o MySQL subiu corretamente
# Aguarde mais alguns segundos e recarregue a página
```

**Mudei o código mas não refletiu**
- Edite os arquivos normalmente — o Docker monta a pasta local diretamente.
- Não precisa reiniciar para mudanças de PHP.

**Quero resetar o banco para o estado inicial**
```bash
docker compose down -v   # apaga o volume do banco
docker compose up -d     # recria tudo do zero
```

---

## 🎓 Referências do projeto

- PHP 8: https://www.php.net/manual/pt_BR/
- Chart.js: https://www.chartjs.org/docs/
- Leaflet: https://leafletjs.com/reference.html
- OWASP PHP: https://owasp.org/www-project-php-security-guide/
- Docker Compose: https://docs.docker.com/compose/
- Lei Maria da Penha: https://www.planalto.gov.br/ccivil_03/_ato2004-2006/2006/lei/l11340.htm

---

## 🏗️ Arquitetura do Sistema

### Diagrama de Componentes (Mermaid)

```mermaid
graph TB
    subgraph Cliente["🖥️ Cliente (Navegador)"]
        UI["Interface HTML/CSS/JS"]
        ChartJS["Chart.js (Gráficos)"]
        Leaflet["Leaflet.js (Mapa)"]
    end

    subgraph Docker["🐳 Docker Environment"]

        subgraph PHP["PHP Container"]
            direction TB

            Config["includes/config.php"]
            Header["includes/header.php"]
            Footer["includes/footer.php"]

            subgraph Modulos["Módulos da Aplicação"]
                Index["index.php"]
                Dashboard["dashboard.php"]
                Victims["victims.php"]
                Occ["occurrences.php"]
                Support["support_network.php"]
                Health["health_visits.php"]
                Protect["protective_measures.php"]
                Panic["panic_button.php"]
                MapP["map.php"]
                Audit["audit.php"]
                Users["users.php"]
            end
        end

        subgraph MySQL["🗄️ MySQL Container"]
            TUsers["users"]
            TVictims["victims"]
            TOcc["occurrences"]
            TSupport["support_followups"]
            THealth["health_visits"]
            TProtect["protective_measures"]
            TPanic["panic_alerts"]
            TAudit["audit_logs"]
        end
    end

    UI -->|HTTP Request| Modulos
    Config -->|PDO Connection| MySQL
    Header -->|require_once| Config
    Modulos -->|require_once| Header
    Modulos -->|require_once| Footer
    Modulos -->|Prepared Statements| MySQL
    UI --> ChartJS
    UI --> Leaflet
    Leaflet -->|Tiles| OSM["🌍 OpenStreetMap CDN"]
```

---

### Diagrama de Fluxo — MVC Simplificado (Mermaid)

```mermaid
flowchart LR
    Request(["HTTP Request"]) --> Router["PHP Page<br/>(Controller)"]
    Router -->|POST| Logic["Business Logic<br/>Validação"]
    Logic -->|INSERT/SELECT| DB[("MySQL")]
    DB --> Logic
    Logic -->|flash e redirect| Router
    Router -->|GET| View["HTML View<br/>PHP Template"]
    View --> Response(["HTTP Response"])
```

---

### Diagrama de Sequência — Login (Mermaid)

```mermaid
sequenceDiagram
    actor U as Usuário
    participant I as index.php
    participant C as config.php
    participant DB as MySQL

    U->>I: POST /index.php (email, senha)
    I->>C: post('email'), $_POST['password']
    I->>DB: SELECT WHERE email=? AND is_active=1
    DB-->>I: user row (com password_hash)
    I->>I: password_verify(senha, hash)
    alt Credenciais válidas
        I->>C: audit_log('LOGIN', 'auth', ...)
        C->>DB: INSERT audit_logs
        I->>U: 302 → dashboard.php
    else Inválidas
        I->>U: 200 + erro genérico
    end
```

---

### Arquitetura PlantUML

```plantuml
@startuml NUPRO_Architecture
!theme cerulean

package "NUPRO+ V2" {

  package "includes/" {
    [config.php] as cfg #lightblue
    [header.php] as hdr
    [footer.php] as ftr
  }

  package "Páginas Públicas" {
    [index.php] as login
  }

  package "Módulos Autenticados" {
    [dashboard.php]        as dash
    [victims.php]          as vic
    [occurrences.php]      as occ
    [support_network.php]  as sup
    [health_visits.php]    as hlt
    [protective_measures.php] as pro
    [panic_button.php]     as pan
    [map.php]              as map
    [audit.php]            as aud
    [users.php]            as usr
  }

  package "assets/" {
    [css/style.css]
    [img/]
  }

  package "sql/" {
    [database.sql] as sql
  }
}

database "MySQL\nnupro_v2" as DB {
  [users]
  [victims]
  [occurrences]
  [support_followups]
  [health_visits]
  [protective_measures]
  [panic_alerts]
  [audit_logs]
}

' Dependências
login --> cfg
dash --> hdr
vic  --> hdr
occ  --> hdr
sup  --> hdr
hlt  --> hdr
pro  --> hdr
pan  --> hdr
map  --> hdr
aud  --> hdr
usr  --> hdr

hdr --> cfg
cfg --> DB

sql ..> DB : "Cria estrutura"

@enduml
```

---

## 🗄️ Modelo de Banco de Dados

### Diagrama Entidade-Relacionamento (Mermaid)

```mermaid
erDiagram
    users {
        int id PK
        varchar name
        varchar email
        varchar password_hash
        varchar role
        tinyint is_active
        datetime created_at
    }

    victims {
        int id PK
        varchar name
        int age
        varchar race
        varchar gender
        text address_encrypted
        tinyint is_confidential
        text phone_encrypted
        tinyint uses_alcohol_drugs
        tinyint psychological_support
        varchar medication
        text notes
        datetime created_at
    }

    occurrences {
        int id PK
        int victim_id FK
        varchar title
        varchar bo_number
        varchar occurrence_type
        varchar risk_level
        varchar region
        varchar address_text
        text description_text
        text complementary_form
        datetime occurred_at
        decimal latitude
        decimal longitude
        int created_by FK
        datetime created_at
    }

    support_followups {
        int id PK
        int occurrence_id FK
        varchar support_type
        varchar professional_name
        varchar status_text
        text notes
        text next_step
        int created_by FK
        datetime created_at
    }

    health_visits {
        int id PK
        int occurrence_id FK
        varchar health_unit
        varchar professional_name
        varchar medication
        tinyint psychological_referral
        text notes
        int created_by FK
        datetime created_at
    }

    protective_measures {
        int id PK
        int occurrence_id FK
        varchar measure_type
        varchar court_name
        varchar status_text
        date decision_date
        tinyint breach_reported
        text notes
        int created_by FK
        datetime created_at
    }

    panic_alerts {
        int id PK
        int victim_id FK
        varchar region
        decimal latitude
        decimal longitude
        text notes
        int triggered_by FK
        datetime created_at
    }

    audit_logs {
        int id PK
        int user_id FK
        varchar action
        varchar module_name
        text details
        varchar ip_address
        datetime created_at
    }

    victims        ||--o{ occurrences        : "possui"
    occurrences    ||--o{ support_followups  : "gera"
    occurrences    ||--o{ health_visits      : "gera"
    occurrences    ||--o{ protective_measures: "gera"
    victims        ||--o{ panic_alerts       : "aciona"
    users          ||--o{ occurrences        : "cria"
    users          ||--o{ support_followups  : "registra"
    users          ||--o{ health_visits      : "registra"
    users          ||--o{ protective_measures: "registra"
    users          ||--o{ panic_alerts       : "aciona"
    users          ||--o{ audit_logs         : "gera"
```

---

## 🔐 Fluxo de Autenticação

```mermaid
flowchart TD
    A([Acesso à página]) --> B{Sessão ativa?}
    B -->|Não| C[Redireciona: index.php]
    B -->|Sim| D{Perfil autorizado\npara este módulo?}
    D -->|Não| E[HTTP 403\nviews_403.php]
    D -->|Sim| F[Executa o módulo]
    C --> G[Formulário de login]
    G -->|POST email + senha| H{password_verify?}
    H -->|Falha| I[Mensagem de erro genérica]
    H -->|OK| J[Cria $_SESSION user\naudit_log LOGIN]
    J --> K[Redireciona: dashboard.php]
    I --> G
```

---

## 👥 Controle de Acesso por Perfil (RBAC)

| Módulo | admin | pm | gm | diretoria | coordenacao | saude | psicologia | assistencia |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Vítimas | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Ocorrências | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Rede de Apoio | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Saúde | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Medidas Protetivas | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Botão de Pânico | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Mapa | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Auditoria** | ✅ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Usuários** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Dados Sigilosos** | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ |

---

## 📁 Estrutura de Arquivos

```
nupro_v2/
├── index.php                  # Página de login (entrada pública)
├── dashboard.php              # Painel principal com KPIs e gráficos
├── victims.php                # Cadastro e listagem de vítimas
├── occurrences.php            # Registro de ocorrências / B.O.
├── support_network.php        # Acompanhamento pela rede de apoio
├── health_visits.php          # Registro de atendimentos em saúde
├── protective_measures.php    # Controle de medidas protetivas
├── panic_button.php           # Registro de alertas de pânico
├── map.php                    # Mapa interativo de ocorrências
├── audit.php                  # Trilha de auditoria (admin/diretoria)
├── users.php                  # Gestão de usuários (admin)
├── logout.php                 # Encerramento de sessão
├── views_403.php              # Página de acesso negado
│
├── includes/
│   ├── config.php             # Configuração central, funções e DB
│   ├── header.php             # Layout: head, sidebar, topbar
│   └── footer.php             # Fechamento do layout HTML
│
├── assets/
│   ├── css/
│   │   └── style.css          # Folha de estilos principal
│   └── img/
│       ├── img-mulher.png     # Imagem de fundo do login
│       ├── as.png             # Logo Assistência Social
│       ├── ps.png             # Logo Psicologia
│       ├── oab.png            # Logo OAB
│       ├── ft.png             # Logo FATEC
│       ├── gm.png             # Logo Guarda Municipal
│       ├── pm.png             # Logo Polícia Militar
│       └── pj.png             # Logo Poder Judiciário
│
└── sql/
    └── database.sql           # Script de criação do banco e seed inicial
```

---

## 🔒 Segurança

| Mecanismo | Implementação | Finalidade |
|---|---|---|
| **Prepared Statements** | `mysqli::prepare()` em todos os INSERTs/SELECTs | Previne SQL Injection |
| **Hash de senhas** | `password_hash()` com `PASSWORD_DEFAULT` (bcrypt) | Protege senhas no banco |
| **Criptografia** | AES-256-CBC via OpenSSL (`encrypt_value()`) | Protege endereço e telefone das vítimas |
| **Escape de output** | `htmlspecialchars()` via `e()` em todo output | Previne XSS |
| **Controle de sessão** | `session_start()` + `session_destroy()` | Gerencia autenticação |
| **RBAC** | `require_roles()` em módulos restritos | Controle de acesso por perfil |
| **Trilha de auditoria** | `audit_log()` em todas as ações relevantes | Rastreabilidade e conformidade LGPD |
| **Mascaramento** | `mask_sensitive()` em dados sigilosos | Limita exposição de dados sensíveis |

> ⚠️ **Para produção:** substitua `ENCRYPTION_KEY` por variável de ambiente, ative HTTPS, configure `session.cookie_secure` e remova as credenciais padrão.

---

## 🔑 Logins de Demonstração

> Senha padrão para todos: **`123456`**

| E-mail | Perfil | Acesso |
|---|---|---|
| admin@nupro.local | Administrador | Total |
| pm@nupro.local | Polícia Militar | Operacional |
| gm@nupro.local | Guarda Municipal | Operacional |
| diretoria@nupro.local | Diretoria | Gerencial + Auditoria |
| saude@nupro.local | Saúde | Saúde + Dados sensíveis |
| psicologia@nupro.local | Psicologia | Psicológico + Dados sensíveis |
| coordenacao@nupro.local | Coordenação | Gerencial + Auditoria |

---

## 🚀 Próximos Passos

- [ ] Edição e exclusão de registros com confirmação
- [ ] Filtros por período, tipo de ocorrência e sazonalidade
- [ ] Exportação de relatórios anonimizados (PDF/CSV)
- [ ] Clusterização de marcadores no mapa por zoom
- [ ] Notificações em tempo real para alertas de pânico (WebSocket)
- [ ] API REST separada para integração mobile
- [ ] Testes automatizados (PHPUnit + Cypress)
- [ ] Conformidade total com LGPD (log de acesso por dado)
- [ ] Autenticação 2FA para perfis privilegiados
- [ ] Containerização com Docker para deploy em produção

---

*NUPRO+ V2 — Projeto acadêmico FATEC Mogi Mirim SP · Desenvolvido com 💜 pelo compromisso com a proteção da mulher*
