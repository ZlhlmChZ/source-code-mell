NYP SIT Cyber Range
=========
The NYP SIT Cyber Range is a CTF Platform, based on Mellivora (https://github.com/Nakiami/mellivora). It is written in PHP.
The Cyber Range CTF Platform is a major fork of the Mellivora platform, as well as deriving content from picoCTF (Toaster Wars). This platform has seen extensive modifications, and support a myriad of features, including session instancing.

### Requirements

- LAMP: PHP 5.3.7+, MySQL 5.5+, Apache 2.2+.
- Tomcat 8 Server with SSL enabled (For picoCTF custom application server)
- [Composer](https://getcomposer.org/)

### Dependencies
- MailGun + API Key (For platform-wide email service to be functional)
- CKEditor

### Installation

You can find more detailed setup instructions in "install/README.md". In short:

- Download to any directory, say: "/var/www/mellivora/".
- Run "composer install" in the "include/thirdparty/composer/" directory to install all required dependencies.
- Create an Apache VHost and point DocumentRoot to "htdocs/". An example Apache config can be found in the "install/" directory.
- Create a database and import the structure from "install/db.sql". Import country list from "install/countries.sql".
- In the "config/" directory: copy .example files to "config.inc.php" and "db.inc.php". Edit both to suit your setup.
- Make the "writable/" directory and its subdirectories writable by Apache.
- Create a user by signing up to the site. Make the user an administrator by manually changing its "class" value to "CONFIG_UC_MODERATOR" (default: 100) in the "users" table in the database.

### Performance
Mellivora is lightweight. And fast. Very fast. Want to run a large competition on an EC2 micro instance? No problem!? Benchmarks are hard and often unhelpful. Here are some of those. Benchmarks performed locally on Mellivora's heaviest page (scores.php), with 85 registered teams and 45 challenges. The WordPress instance used for comparison is an fresh out-of-box install. The WP page loaded is the default post-install home page. Tools: Xdebug 2.2.3, SIEGE 3.0.5 and ApacheBench 2.3.

### License

This software is licenced under the [GNU General Public License v3 (GPL-3)](http://www.tldrlegal.com/license/gnu-general-public-license-v3-%28gpl-3%29). The "include/thirdparty/" directory contains third party code. Please read their LICENSE files for information on the software availability and distribution.
