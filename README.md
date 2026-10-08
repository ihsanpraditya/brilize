# README

## Docker

Saya develop aplikasinya di docker untuk menyamakan dengan ekosistem shared hosting sehingga aplikasi dapat menggapai banyak kalangan.

## Bun/Node

Saya pakai bun ya, untuk menjalankan nya jangan di docker.

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

### Login PSQL

```bash
docker exec -it brilize-database-1 psql -U app -d app
```

**Beberapa query psql yang sering dipakai**:

\dt : Menampilkan daftar tabel yang ada di database.

\l : Menampilkan daftar semua database.

\d nama_tabel : Melihat struktur/skema dari suatu tabel.

\q : Keluar dari CLI psql dan kembali ke terminal host.
