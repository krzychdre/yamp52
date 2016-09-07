Requirements:
 - mod_rewrite on Apache
 - php_apc
 - php_sqlite
 - sqlite

Install Doctrine2:

pear channel-discover pear.doctrine-project.org
pear install pear.doctrine-project.org/DoctrineORM-2.0.3


Copy yamp52 to /var/www/htdocs

in apache2's virtual hosts definition (available-sites on Debian) add:

NameVirtualHost *:80
<VirtualHost *:80>
    ServerName your_server.domain
    DocumentRoot /var/www/htdocs/yamp52
</VirtualHost>

regenerate_db.sh script can create apropriate database schema based on models.
