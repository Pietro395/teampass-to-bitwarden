# Export from teampass

Quick and dirty script for exporting data from Teampass to Bitwarden.

Upload the script to your Teampass server in `sources/export.php`, sign in as
the admin user, and open the script in your web browser (the URL should be
something like http://example.com/sources/export.php).

Copy the received JSON and import it into a Bitwarden organization (do not use
import into your own personal account!), using the `Bitwarden (JSON)` format.

The code is shit, it was hastily written for one-off use.

---

This script has been adapted to work in version 2.1.27.36 of teampass 2 from the original code.
It is necessary to write the DB password into the code by replacing `DBPASSWORD`.

It is also necessary to comment the following part of code in the file `sources/main.functions.php`:

```
//if (!isset($_SESSION['CPM']) || $_SESSION['CPM'] != 1) {
//    die('Hacking attempt...');
//}
```

---

This script has now been adapted to now work correctly on the conversion of the TeamPass Folders into the Bitwarden Collections,
it now respects the hierarchy of each content and uses the correct paths, as well as assigning the Items into the right Collections.

