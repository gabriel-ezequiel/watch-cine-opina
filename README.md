# Watch Cine Opina

Aplicação web para recomendação de filmes e séries, desenvolvida utilizando Laravel e livewire.

A aplicação permite que usuários criem publicações solicitando opiniões sobre filmes ou séries. Outros usuários podem recomendar, não recomendar ou simplesmente acompanhar uma publicação.

Este projeto foi desenvolvido como parte de um teste técnico para Desenvolvedor Júnior.

---

## Tecnologias utilizadas

- PHP
- Laravel
- Livewire
- Alpine.js
- Tailwind CSS
- MySQL
- Laravel Sail
- PHPUnit

---

## Requisitos

Para executar o projeto localmente, é necessário ter instalado:

- Git
- Docker
- Docker Compose

O projeto utiliza Laravel Sail para facilitar a configuração do ambiente de desenvolvimento.

---

# Como instalar

Clone o repositório:

```
git clone https://github.com/gabriel-ezequiel/watch-cine-opina.git
```

Entre na pasta do projeto:

```
cd watch-cine-opina
```

Instale as dependências do PHP:

```
composer install
```

Copie o arquivo de ambiente:

```
cp .env.example .env
```

Suba os containers do Laravel Sail:

```
./vendor/bin/sail up -d
```

Gere a chave da aplicação:

```
./vendor/bin/sail artisan key:generate
```

Instale as dependências:
```
./vendor/bin/sail npm install
```

```
./vendor/bin/sail npm run dev
```

Gera as migrations:

```
./vendor/bin/sail artisan migrate
```

---

# Configuração do .env

O projeto utiliza MySQL através do Laravel Sail.

Depois de copiar o `.env.example`, confira principalmente as configurações do banco de dados.

Exemplo:

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=watch_cine_opina
DB_USERNAME=sail
DB_PASSWORD=password
```

O valor de `DB_HOST` deve ser `mysql` quando a aplicação estiver sendo executada dentro dos containers do Laravel Sail.

A configuração exata pode variar de acordo com o ambiente local.

---

# Migrations

Depois de iniciar os containers, execute as migrations:

```
./vendor/bin/sail artisan migrate
```

As migrations criam as principais estruturas utilizadas pela aplicação, incluindo:

- usuários
- publicações
- votos
- acompanhamentos

Para recriar o banco de dados durante o desenvolvimento:

```
./vendor/bin/sail artisan migrate:fresh
```

---

# Como executar a aplicação

Com os containers iniciados:

```
./vendor/bin/sail up -d
```

A aplicação pode ser acessada pelo endereço:

```
http://localhost
```

Para parar os containers:

```
./vendor/bin/sail down
```

Para acompanhar os logs:

```
./vendor/bin/sail logs -f
```

---

# Funcionalidades

## Autenticação

A aplicação possui:

- cadastro de usuários
- login
- logout
- identificação do usuário autenticado

---

## Publicações

Um usuário autenticado pode criar uma publicação informando:

- título
- tipo: filme ou série
- descrição

Cada publicação possui um usuário responsável e uma situação.

As publicações podem estar abertas ou concluídas.

---

## Feed

O feed apresenta as publicações disponíveis para interação.

Cada publicação apresenta:

- título
- tipo
- descrição
- usuário responsável
- quantidade de recomendações
- quantidade de pessoas que não recomendam
- situação da publicação

O feed possui tres abas:

- Todos
- Seguindo
- Fechadas

A atualização das interações utiliza Livewire, evitando recarregamentos completos da página.

---

## Votação

O usuário pode votar em uma publicação como:

- Recomendo;
- Não recomendo.

O mesmo usuário não pode possuir mais de um voto válido para a mesma publicação.

Também é possível alterar o voto.

Ao votar, o usuário passa automaticamente a acompanhar a publicação.

O voto pode ser retirado enquanto a publicação estiver aberta.

---

## Acompanhamento

O usuário também pode acompanhar uma publicação sem votar.

Quando uma publicação está aberta, o usuário pode:

- começar a acompanhar
- deixar de acompanhar

Quando o usuário possui um voto, ele continua acompanhando a publicação mesmo que retire o voto.

---

## Encerramento

O autor de uma publicação pode marcá-la como concluída.

Depois que a publicação é concluída:

- novos votos não podem ser registrados
- novos acompanhamentos não podem ser criados
- os votos existentes continuam disponíveis
- os acompanhamentos existentes continuam disponíveis
- a publicação fica visualmente identificada como concluída.

---

## Exclusão

O autor pode excluir sua própria publicação somente quando ela ainda não possuir interações.

Uma publicação que já possui votos ou acompanhamentos não pode ser excluída.

Essa regra é validada no backend e não depende apenas da interface.

Também não é possível excluir a publicação de outro usuário.

---

# Testes

O projeto utiliza PHPUnit.

Para executar todos os testes:

```
./vendor/bin/sail artisan test
```

Para executar especificamente os testes relacionados às interações das publicações:

```
./vendor/bin/sail artisan test --filter=PublicationInteractionTest
```

Atualmente os testes cobrem regras importantes do sistema, incluindo:

- voto cria automaticamente um acompanhamento
- publicação concluída não recebe novos votos
- publicação com interação não pode ser excluída pelo autor

Os testes utilizam `RefreshDatabase` para manter o banco de testes isolado entre os cenários.

---

# Decisões técnicas

## Laravel

Laravel foi utilizado como framework principal por fornecer recursos importantes para o projeto, como:

- Eloquent ORM
- migrations
- autenticação
- validação
- relacionamentos
- testes
- suporte a transações

---

## Livewire

Livewire foi utilizado para as interações do feed.

Operações como:

- votar
- alterar voto
- retirar voto
- acompanhar
- deixar de acompanhar
- concluir publicação
- excluir publicação

podem atualizar o estado da interface sem exigir um recarregamento completo da página.

Isso também evita a necessidade de criar uma API separada apenas para essas interações.

---

## Tailwind CSS

Tailwind CSS foi utilizado para construir a interface da aplicação.

A prioridade foi manter uma interface simples, consistente e utilizável, já que o objetivo principal do teste é demonstrar domínio das tecnologias e das regras de negócio.

---

## Eloquent

Os relacionamentos entre usuários, publicações, votos e acompanhamentos são representados através dos Models do Laravel.

A publicação possui relacionamentos com:

- usuário responsável
- votos
- acompanhamentos

Os votos e acompanhamentos também possuem relacionamentos com o usuário e a publicação.

---

## Enums

Foram utilizados enums para representar valores controlados pela aplicação.

Entre eles:

- `PublicationStatus`
- `PublicationType`
- `VoteType`

Isso evita espalhar valores arbitrários pela aplicação e deixa as regras de negócio mais explícitas.

---

## Transações

As operações de voto e acompanhamento são realizadas utilizando transações quando as duas operações precisam acontecer juntas.

Por exemplo, quando um usuário vota, o voto e o acompanhamento devem ser persistidos de forma consistente.

A utilização de uma transação reduz o risco de uma operação ser salva enquanto a outra falha.

---

# Estrutura principal

As principais entidades da aplicação são:

## User

Representa os usuários cadastrados.

Um usuário pode:

- criar publicações
- votar
- acompanhar publicações

## Publication

Representa uma solicitação de recomendação.

Possui:

- usuário responsável
- título
- tipo
- descrição
- situação
- data de criação

## Vote

Representa a opinião de um usuário sobre uma publicação.

Pode ser:

- `recommend`
- `not_recommend`

## Follow

Representa o acompanhamento de uma publicação por um usuário.

Um acompanhamento não exige que o usuário tenha realizado um voto.

---

# Histórico de desenvolvimento

O projeto foi desenvolvido de forma incremental, utilizando commits separados por funcionalidade.

Entre as etapas principais estão:

- configuração inicial do Laravel
- configuração do Livewire
- implementação de autenticação
- criação dos Models, migrations e enums
- criação de publicações
- implementação de votação
- implementação de acompanhamento
- alteração de votos
- encerramento de publicações
- regras de exclusão
- criação de Factory
- implementação de testes automatizados

---

# Melhorias futuras

Caso houvesse mais tempo, algumas melhorias poderiam ser implementadas.

## Mensagems de feedback

Mensagems de feedback após criar, votar, acompanhar, encerrar ou excluir uma publicação.

## Paginação

Adicionar paginação ao feed para evitar carregar todas as publicações de uma vez.

## Busca

Permitir que o usuário pesquise publicações pelo título.

## Filtros

Adicionar filtros por:

- filme
- série
- situação
- publicações acompanhadas

## Implementação de mais seeders

Adicionar seeders para popular o banco de dados com mais publicações, votos e acompanhamentos, permitindo testar a aplicação com mais dados.

## Mais testes

Adicionar testes para outras regras, principalmente:

- usuário não pode excluir publicação de outro usuário
- usuário não pode concluir publicação de outro usuário
- publicação concluída não aceita novo acompanhamento
- usuário pode alterar seu voto
- usuário pode retirar seu voto
- acompanhamento sem voto pode ser removido enquanto a publicação estiver aberta

## Testes de componentes Livewire

Aumentar a cobertura dos componentes Livewire para verificar também o comportamento da interface e do estado dos componentes.

## Melhorias de interface

Melhorar a experiência visual da aplicação e adicionar estados de carregamento mais detalhados para as interações Livewire.

---

# Considerações finais

O projeto foi desenvolvido buscando manter uma arquitetura simples e compreensível, adequada ao tamanho da aplicação.

A prioridade foi implementar corretamente as regras de negócio, manter as responsabilidades organizadas e utilizar os recursos principais do Laravel e livewire sem adicionar abstrações desnecessárias.

O objetivo não foi criar uma arquitetura complexa, mas demonstrar uma implementação consistente que possa ser facilmente entendida, testada e modificada.