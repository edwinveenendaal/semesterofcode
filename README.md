# semesterofcode
The code repository for the Virtual Alliances for Learning Society

vals_soc
========

VALS Drupal module

Install the following Drupal Module Dependencies...

[Admin Menus] (https://drupal.org/project/admin_menu)

[Date Module](https://drupal.org/project/date)

[SMTP Module](https://drupal.org/project/smtp)

[CKEditor Module](https://drupal.org/project/ckeditor)

[Ctools Module] (https://drupal.org/project/ctools)

Switch on the frontpage module and make sure the page--front template from the dev directory is copied to themes/bartik/templates.

Copy the file initial.php to the root of the installation. Due to a bug in Drupal it needs the base url before it can 
do a bootstrap in ajax. The bootstrap from the index doesn't suffer from that. Probably because Drupal does not derive
the base url (based on assumptions about the position of index.php if it is already defined. Instead we derive it ourselves
from a location that is known before the bootstrap. All urls served pass either the (/vals)/index.php or the location (/vals)/sites/all/modules/vals_soc/actions.

Copy the file index.php to the root of the installation. Also due to a combination of Drupal and server configuration
the index needed a bug fix.

In dev
To update locally, we can use the Drupal update mechanism once logged in as admin
Core updates can be done with downloading from the Drupal site and check the md5 hash with a 
md5 hash checker.

Currently the Vals-soc module is built upon Drupal Core: 7.41
