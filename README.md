# gif_beukeboom
A Gif Generator for the day's Hockey Games

NOTE: To run, be sure to create an `.htaccess` file in the root directory that says:

```
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . /index.php [L]
```
