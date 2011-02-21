# Wiki

[http://dev.twitter-collage.quodis.com/](http://dev.twitter-collage.quodis.com/)

[http://swap.quodis.com/collage/v1.html](http://swap.quodis.com/collage/v1.html)
...
[http://swap.quodis.com/collage/v8.html](http://swap.quodis.com/collage/v8.html)

## execute

### color-import.php

uses the icon in 

config/<logoFile>

analyses it and imports the pixel colors into a file as in

config/<configFile>

*the following 3 php scripts should be cron'ed*

### twitter-search.php

* fetch tweets from twitter API,
* randomize each new tweet position in current page
* inserts new tweets into 'tweet' table

<pre><code>
	php twitter-search.php
</code></pre>
 
* downloads tweet images
* processes them according to page position 
* MAX 1000 each execution
* files stored under
** /server/cache/twitter-collage/original
** /server/cache/twitter-collage/processed

<pre><code>
	php make-images.php
</code></pre>

* updates current page json file with new tweets (with images already processed)
* page files stored under
** /server/cache/twitter-collage/pages

<pre><code>
	php collage-build.php
</code></pre>

## update 

<pre><code>
	cd /servers/develop/twitter-collage
	git pull
	php configure.php

# dropa a base-de-dados
	php reset-all.php

# este só apaga as imagens/páginas geradas
	php reset-pages.php
</code></pre>

## install

### cache dir

<pre><code>
	mkdir /servers/cache/twitter-collage
	chmod 777 /servers/cache/twitter-collage
</code></pre>

### log dir

<pre><code>
	mkdir /var/log/twitter-collage
	chmod 777 /var/log/twitter-collage
</code></pre>

### code dir

<pre><code>
	mkdir /servers/develop
	cd /servers/develop
	git clone git@github.com:quodis/Twitter-Collage.git twitter-collage
</code></pre>

### configure

<pre><code>
	php configure.php
</code></pre>

### database

<pre><code>
	mysql> \. schema/db.sql
	mysql> \. schema/tables.sql
</code></pre>