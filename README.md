# README

## Deployment

Setting route aplikasi ini ditujukan menggunakan subdomain /brilize. Sehingga
jika diupload ke server akan menjadi namadomain.com/brilize.

Webserver yang digunakan adalah apache httpd dengan ditaruh di folder dengan
nama brilize. Perlu override htaccess

```
# public_html/.htaccess

<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{REQUEST_URI} !^/brilize/public/ 
  RewriteRule ^brilize/(.*)$ brilize/public/$1 [L,QSA]
</IfModule>
```

```
# public_html/brilize/public/.htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /brilize/

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```
