# Contribuindo

Obrigado por considerar contribuir com o **Smart Inventory AI**.

## Ambiente de desenvolvimento

- **PHP:** 8.3+ (alinhado ao [Dockerfile](Dockerfile) e ao CI).
- **Composer e Node.js:** para instalação e build de assets.
- **MySQL 8** local ou via [docker-compose.yml](docker-compose.yml).
- **Python 3.12:** serviço de previsão na pasta `python/`.

## Comandos úteis

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan test
./vendor/bin/pint
```

### Serviço de previsão (Python)

```bash
cd python
pip install -r requirements.txt
pytest -q
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

No `.env` do Laravel, aponte `PREDICTOR_URL` para a URL do uvicorn (ex.: `http://127.0.0.1:8000`).

## Padrões

- **PHP:** formatação com [Laravel Pint](https://laravel.com/docs/pint) (`./vendor/bin/pint`).
- **Testes:** `php artisan test` (Pest) e `pytest` no diretório `python/`.
- **Pull requests:** descreva a mudança e, se possível, adicione ou ajuste testes relacionados.

## Screenshots da documentação

As imagens em [docs/](docs/) podem ser substituídas por capturas reais da aplicação para o README; mantenha os nomes de arquivo referenciados no [README.md](README.md) ou atualize as referências em conjunto.
