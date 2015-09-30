<h1><i class='ion-ios-gear'></i> Bootie</h1>
<h3>PHP 5 Micro Web Application Framework</h3>
<p>Based on Micromvc by David Pennington</p>

<p>Please consider clone this repository before for an <a href="https://github.com/martinfree/BootieProject">Example Project</a></p>

<h4>Improvments</h4>
<ul>
<li>Routing request method based.</li>
<li>Filters.</li>
<li>Dispatching method simplification.</li>
</ul>

<h4>Install</h4>
<p> Create an empty database and set your access credentials here</p>
<pre><code data-language="shell">$ nano config/config.php
</code></pre>

<p>With Micromvc migrations tools run</p>
<pre><code data-language="shell">$ php cli create
$ php cli restore
</code></pre>

<h4>Nginx</h4>
<p>Nginx suggested directive</p>
<pre data-language="shell"><code>server {

        root /var/www/bootie/public;

        index index.php index.html index.htm;

        server_name bootie.local;

        location / {
                try_files $uri $uri/ /index.php$is_args$args;
        }

        location ~ \.php$ {
                fastcgi_pass unix:/var/run/php5-fpm.sock;
                fastcgi_index index.php;
                include fastcgi_params;
        }
}

</code></pre>