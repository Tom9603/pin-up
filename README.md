This project was built with:

- Docker + Docker Compose for the development environment
  
- MySQL as the database (running in Docker)
  
- Symfony (backend framework)

- SCSS (styling)

- AssetMapper (asset management)

- EasyAdminBundle (administration panel)

- Symfony Mailer

It includes a full MVC structure, entity management, and a customizable back-office.

1) Clone the repository

```
git clone https://github.com/<YOUR-USERNAME>/<YOUR-PROJECT>.git
cd <YOUR-PROJECT>
```

2) Docker

```
docker-compose up -d
```

3) Install PHP dependencies

```
composer install
```

4) Install front-end dependencies
   
```
symfony console importmap:install
symfony console asset-map:compile
```

5) Create your .env.local file and configure your DATABASE_URL according to your Docker setup.

6) Create the database

```
symfony console doctrine:database:create

```

7) Run migrations

```
symfony console doctrine:migrations:migrate
```

8) Start the server

```
symfony server:start
```
