# Todo-List-ztp
Prosta aplikacja typu Lista zadań wykonana przy pomocy:
* PHP 8.1.3 
* Symfony 5.4.8.

## Instrukcja instalacji
1.Pobrać repozytorium

2.Wykonać polecenie 
```
$ composer install
```
3.skonfigurować dane dostępowe do bazy danych w pliku .env
```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name
```

4.stworzyć i wykonać migrację
```
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```

5.załadować fixtures
```
$ php bin/console doctrine:fixtures:load
```

login: admin0@example.com
hasło: admin1234
