<h1><i class='ion-ios-gear'></i> Bootie</h1>
<h3>PHP 5 Micro Web Application Framework</h3>
<h5>Based on Micromvc by David Pennington</h5>
<p>This is the Bootie Framework Library</p>
<p>You can see an <a href="http://bootie.devmeta.net">online demo of this project</a></p>
<p>Please consider clone this repository before for an <a href="https://github.com/martinfree/BootieProject">Example Project</a></p>
<p>You can see a <a href="https://github.com/martinfree/BootieREST">REST Example</a></p>
<p>You can also clone a <a href="https://github.com/martinfree/BootieScheleton">Scheleton Project</a></p>

<h4>Improvments</h4>
<ul>
<li>Dispatching method simplification</li>
<li>Routing request method based</li>
<li>Filters</li>
<li>Speed Cache</li>
<li>Model pagination</li>
<li>Flash messages</li>
</ul>

<h4>Install</h4>
<p> Create an empty database and set your access credentials here</p>
<pre><code data-language="shell">$ cat config/config.sample.php > config/config.php
$ nano config/config.php
</code></pre>


<p>With Micro migrations tools run</p>
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