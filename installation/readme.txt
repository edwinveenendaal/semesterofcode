In this directory, you will find files to be used after the Drupal basis installation is finished
Steps to follow:
1. Unzip Drupal in the right directory
2. get the module vals-soc and put it in the directory sites/all/modules(/vals_soc)
3. Login as admin and enable the vals_soc module
4. Place the settings.php file in sites/[default|<hostname of the site>]/settings.php
Make sure that you merge this file with the standard settings.php delivered by Drupal. Make sure you 
fill in the details about the database (server) you are going to use and the last lines following the
marker CUSTOM
5. Place the files in media under sites/all/themes/media
6. Place the page--front.tpl.html file under themes/bartik/templates (it replaces the template file
that is installed by the Drupal core.