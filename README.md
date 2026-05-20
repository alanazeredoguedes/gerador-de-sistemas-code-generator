# GDS — Gerador de Sistemas (Code Generator)

[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.20316548.svg)](https://doi.org/10.5281/zenodo.20316548)

Motor de geração de código do **GDS — Gerador de Sistemas**, plataforma de *Model-Driven Development* desenvolvida como Trabalho de Conclusão de Curso em Sistemas de Informação no **Centro Universitário La Salle do Rio de Janeiro (Unilasalle-RJ)**, em 2022.

Este repositório contém o **serviço de geração** — recebe a modelagem objeto-relacional persistida no backend e produz uma aplicação web completa: código-fonte Symfony, container Docker, deploy em AWS EC2 e publicação automática do projeto gerado em um repositório GitHub.

## Sobre o sistema GDS

O GDS recebe a modelagem objeto-relacional de um sistema (classes, atributos, relacionamentos 1:1, 1:N, N:N) e gera automaticamente uma aplicação web full-stack com backend PHP/Symfony, API REST documentada (Swagger), autenticação JWT, painel admin Sonata, container Docker e deploy automatizado em AWS EC2.

O sistema é composto por **quatro componentes**:

| Componente | Responsabilidade | Repositório |
|---|---|---|
| Frontend | Editor visual de diagramas (Vue 3 SPA) | [gerador-de-sistemas-front](https://github.com/alanazeredoguedes/gerador-de-sistemas-front) |
| Backend | API REST, autenticação, persistência | [gerador-de-sistemas-back](https://github.com/alanazeredoguedes/gerador-de-sistemas-back) |
| **Code Generator (este repo)** | Motor de geração + deploy | — |
| Base | Template-base Symfony para apps gerados | [gerador-de-sistemas-base](https://github.com/alanazeredoguedes/gerador-de-sistemas-base) |

## Arquitetura

```
┌─────────────┐        ┌─────────────┐         ┌──────────────────┐
│ FRONTEND    │  HTTPS │ BACKEND     │ aciona  │ CODE GENERATOR   │
│ Vue 3 SPA   │ ─────► │ Symfony 6   │ ──────► │ (este repo)      │
│             │  REST  │ + JWT       │         │ + GitHub deploy  │
└─────────────┘        └─────────────┘         └────────┬─────────┘
                                                        │ clona e
                                                        │ adapta
                                                        ▼
                                               ┌──────────────────┐
                                               │ BASE             │
                                               │ template Symfony │
                                               │ + bundles core   │
                                               └──────────────────┘
```

## Stack

- **PHP 8.1**
- **Symfony 6**
- **Twig** (templates de geração de código)
- **AWS SDK for PHP** (deploy EC2)
- **GitHub Hub CLI** (publicação automática do código gerado)
- **Docker** + Docker Compose

## Componentes internos

- `Application/Generator/GeneratorBundle/Generator.php` — orquestra a geração
- `Application/Generator/GeneratorBundle/Maker/` — geração de scripts (`MakeStartProjectScript` etc.)
- `Application/Generator/GeneratorBundle/AwsHelper/` — provisionamento de instâncias EC2
- `Application/Generator/GeneratorBundle/Resources/skeleton/` — templates Twig do código gerado
- `gitDeploy.php` / `gitDeployDev.php` — rotinas de publicação no GitHub para projetos finalizados

## Como executar (local)

### Pré-requisitos

- Docker e Docker Compose
- Conta AWS com chaves de acesso (para o deploy de testes — opcional para desenvolvimento)
- Conta GitHub e personal access token (para publicação automática — opcional)

### Configuração

```bash
cp .env.base .env.local
# editar .env.local com seus segredos:
#   AWS_KEY, AWS_SECRET — chaves AWS para deploy
#   APP_SECRET — segredo Symfony
```

> **⚠️ NUNCA** comite `.env.local` nem coloque segredos reais em `.env.base`. Os arquivos `gitDeploy.php` e `gitDeployDev.php` esperam ler o token do ambiente — não embuta tokens no código-fonte.

### Subir o ambiente

```bash
docker compose build app
docker compose up -d
# disponível em http://localhost:9003
```

## Como citar

Se você usar este software em pesquisa, por favor cite-o:

> Guedes, A. A. (2022). *GDS — Gerador de Sistemas: Uma ferramenta de modelagem e geração de sistemas (Code Generator)*. Trabalho de Conclusão de Curso, Centro Universitário La Salle do Rio de Janeiro. DOI: 10.5281/zenodo.XXXXXXX

Veja também o arquivo [`CITATION.cff`](./CITATION.cff) para metadados estruturados.

## Sobre o TCC

- **Autor:** Alan Azeredo Guedes
- **Orientador:** Prof. Msc. Mario João Junior
- **Instituição:** Centro Universitário La Salle do Rio de Janeiro (Unilasalle-RJ)
- **Curso:** Sistemas de Informação
- **Defesa:** Dezembro de 2022

## Licença

Distribuído sob a licença **MIT**. Veja [`LICENSE`](./LICENSE) para detalhes.
