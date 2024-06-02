# Export from teampass

Quick and dirty script for exporting data from Teampass to Bitwarden.

Upload the script to your Teampass server in `sources/export.php`, sign in as
the admin user, and open the script in your web browser (the URL should be
something like http://example.com/sources/export.php).

Copy the received JSON and import it into a Bitwarden organization (do not use
import into your own personal account!), using the `Bitwarden (JSON)` format.

The code is shit, it was hastily written for one-off use.

---

# Экспорт из teampass

Скрипт экспорта в teampass.

Заливаем на сервер в `sources/export.php`, логинимся в основной веб-морде, и
открываем страницу (типа https://example.com/sources/export.php).

Копируем полученный JSON и импортируем в импорте организации (не своём личном
импорте!), используя формат `Bitwarden (JSON)`.

Код дрянь, писался наспех на один раз.

