# Organize

Aplicação web de **gestão de finanças pessoais** construída com Laravel e Livewire. Permite registar receitas e despesas, categorizar lançamentos, acompanhar períodos (dia a ano), gerar parcelas automaticamente e visualizar resumos por mês — com regras de negócio para manter despesas “pagas” alinhadas ao saldo disponível.

## Stack

| Camada | Tecnologia |
|--------|------------|
| Backend | PHP 8.3+, Laravel 13 |
| Frontend | Livewire 4, Flux UI (gratuito), Tailwind CSS 4, Vite |
| Autenticação | Laravel Fortify (login, registo, 2FA, perfil, etc.) |
| Testes | Pest 4 |
| Formatação PHP | Laravel Pint |

## Funcionalidades principais

- **Receitas e despesas** com categorias personalizadas por tipo
- **Parcelamento**: compras parceladas geram lançamentos nos meses seguintes
- **Estados** (pago / pendente) com marcação rápida na listagem
- **Resumos por período** e filtros por mês
- **Saldo para despesas pagas**: marcar ou criar despesa como paga só é permitido com saldo suficiente (receitas recebidas − despesas pagas, visão global)
- Interface em **português (Brasil)** com pacote de localização `lucascudo/laravel-pt-br-localization`
- **Landing page** pública na rota `/` e área autenticada com layout Flux

## Requisitos

- PHP 8.3 ou superior (extensões habituais do Laravel: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- Composer 2
- Node.js 20+ e npm (para assets com Vite)
- SQLite (padrão no `.env.example`) ou MySQL/PostgreSQL

## Instalação

1. Clonar o repositório e entrar na pasta do projeto.

2. Instalar dependências PHP e Node, ambiente e base de dados:

   ```bash
   composer setup
   ```

   O script `setup` executa: `composer install`, cópia do `.env` se necessário, `key:generate`, `migrate`, `npm install` e `npm run build`.

3. (Opcional) Ajustar `.env` — por exemplo `APP_NAME`, `APP_URL`, `DB_*` e localização:

   ```env
   APP_LOCALE=pt_BR
   APP_FALLBACK_LOCALE=en
   ```

4. Iniciar o ambiente de desenvolvimento (servidor HTTP, fila, logs e Vite em paralelo):

   ```bash
   composer run dev
   ```

   Em alternativa, em terminais separados: `php artisan serve`, `npm run dev`.

5. Aceder à aplicação (por defeito `http://127.0.0.1:8000` se usar `php artisan serve`).

## Testes e qualidade

```bash
# Toda a suíte
php artisan test --compact

# Apenas um ficheiro ou filtro
php artisan test --compact tests/Feature/ExampleTest.php
php artisan test --compact --filter=nomeDoTeste
```

```bash
# Formatar PHP alterado (recomendado antes de commit)
vendor/bin/pint --dirty
```

## Estrutura útil

- `routes/web.php` — rotas web (início, dashboard, finanças, definições)
- `resources/views/pages/` — páginas Livewire (ficheiros `⚡*.blade.php`)
- `app/Actions/` — ações de domínio (ex.: criação de transações e parcelas)
- `app/Support/FinancePeriodSummary.php` — agregados por período
- `tests/Feature/` — testes de funcionalidade e fluxos HTTP/Livewire

## Licença

Defina a licença do projeto num ficheiro `LICENSE` na raiz, se aplicável (o starter Laravel costuma usar MIT).
