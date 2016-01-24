cloud_ldap_admin
================

!!THIS SOFTWARE IS NOT READY FOR USE YET!!



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

Configure
---------

The main config file is at `app/config/paramters.yml`

