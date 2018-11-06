# Freshdesk solutions fetcher
CLI приложение для скачивания статей из портала Freshdesk.

### С чего начать.
```angular2html
$ git clone https://github.com/dryyyyy/freshdesk_solutions_fetcher.git
```
### Пример использования
Запустить приложение командой:
```angular2html
php bin/console app:fetch
```
Следовать инструкциям (ввести логин, пароль для аккаунта freshdesk, задать язык статей, имя файла на выходе).

Вывод: .xlsx файл с колонками Topic, Question, Answer, HTML.