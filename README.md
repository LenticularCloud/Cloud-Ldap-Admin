LenticularCloudAdmin
================

!!THIS SOFTWARE IS NOT READY FOR USE YET!!

Summary
-------

This is a tool to administrate a ldap database. It is concept to have multiple tables for different services.

Example:
Your domain is `example.org` so it creates the organisation units:
`ou=users,dc=example,dc=org`
`ou=users,dc=service,dc=example,dc=org`
`ou=users,dc=service2,dc=example,dc=org`

You can have also multiple passwords for this services or ONE global password for simple usage.
The idea behind that, is to give not my password for the whole system to a creepy chat program.

A user can also disable each service and remove himself from a organisation unit.

INSTALL
-------

### Download project

`git clone ....`

### Install Composer

https://getcomposer.org/download/

### Get dependents and configure

```Shell
cd cloud_ldap_admin
composer install #this comand also offers you to set your configuration you can also do this later
```

### Configure

The main config file is at `app/config/paramters.yml`.

### Inital ldap server

Make a backup before you exceute this command. it will change the database schema!

`./app/console cloud:update`
`php app/console doctrine:cache:clear-query -e prod`
`php app/console doctrine:schema:update --force`
`php app/console cache:clear -e prod`
`php app/console assetic:dump -e prod`

###  Configure webserver

#### Nginx

```
server {
  listen  13.37.42.23:80;
  listen  13.37.42.23:443 ssl;
  server_name www.example.com example.com;
  root /var/www/cloud_ldap_admin/web/;


  # strip app.php/ prefix if it is present
  rewrite ^/app\.php/?(.*)$ /$1 permanent;

  location / {
    index app.php;
    try_files $uri @rewriteapp;
  }

  location @rewriteapp {
    rewrite ^(.*)$ /app.php/$1 last;
  }

   # pass the PHP scripts to FastCGI server from upstream phpfcgi
  location ~ ^/(app)\.php(/|$) {
    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    include fastcgi_params;
    fastcgi_pass unix:/run/php-fpm.sock;
  }
}
```
