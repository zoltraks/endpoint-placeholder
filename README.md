Endpoint Placeholder
====================

This simple web application stores every HTTP request together with headers to a local file repository.

It returns payload body from request as a response.

By default it exposes 6443 for HTTPS and 6080 for HTTP traffic.

Generate self signed certificate using ``mkcertself.sh`` in ``ssl/`` directory if not exists.

```
DOMAIN=mydomain.example.org bash ./mkcertself.sh | less
```

Use ``docker-compose`` to start container stack.

```
docker-compose up -d
```

Optionally specify ``docker-compose.yml`` file.

```
docker-compose -f docker-compose.yml up -d
```

If subdirectory can't be created in ``upload`` you'll receive "500 Internal Server Error" response.

In that case check permissions for ``log`` and ``upload`` directories.

Watch for log entries.

```
tail -F log/php/error.log
```

Remove container stack.

```
docker-compose down
``` 

If you make any changes in ``Dockerfile`` or ``docker-compose.yml`` files you must rebuild containers.

```
docker-compose up -d --build
```

Security
--------

If request content length exceeds size limit, HTTP 500 status code will be returned.

```
500 Internal Server Error: Request body size exceeds the limit
```

If not specified, default request size limit is 32 KB.

Internal error details such as file paths and stack traces are written to the log file only and never appear in responses.

Web server
----------

You may host ``app`` directory enabling PHP runtime.

In this approach default settings will surely not work.

So copy example configuration file ``app/config.example.php`` as ``app/config.php`` and edit it to suit your needs.

Here is an example site configuration for ``nginx`` running on **Ubuntu Linux** using **PHP** version 7.

```nginx
server {
	listen 8443;
	server_name endpoint-placeholder.local;

	root /var/www/endpoint-placeholder/app;
	index index.php;

	ssl on;
	ssl_certificate /var/www/endpoint-placeholder/ssl/public.crt;
	ssl_certificate_key /var/www/endpoint-placeholder/ssl/private.key;

	ssl_session_timeout 5m;

	ssl_protocols SSLv3 TLSv1 TLSv1.1 TLSv1.2;
	ssl_ciphers "HIGH:!aNULL:!MD5 or HIGH:!aNULL:!MD5:!3DES";
	ssl_prefer_server_ciphers on;

	gzip on;

	location / {
		try_files $uri $uri/ /index.php$is_args$args;
	}

	location ~ \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
                fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		fastcgi_index index.php;
		include fastcgi_params;
	}

	location ~ \.ht {
		deny all;
	}

	location ~ ~$ {
		deny all;
	}

	location ~ \.conf {
		deny all;
	}
}
```

PHP built-in web server
-----------------------

You can also run the application directly with the PHP built-in web server, without Docker or nginx.

From the repository root:

```
php -S 0.0.0.0:6080 app/index.php
```

From the ``app`` directory:

```
cd app
php -S 0.0.0.0:6080 index.php
```

This works on Windows, Linux and macOS - the POSIX extension is not required.

``index.php`` acts as a router, so the auxiliary endpoints ``time.php``, ``remote.php``, ``failure.php`` and ``phpinfo.php`` stay reachable at ``/time.php`` and so on. Every other request hits the capture endpoint.

Files outside the ``app`` directory are never served, even when the document root is the repository root.

Configuration
-------------

If you are running application as docker container, please remember they are relative to a container not the machine.

You probably don't need to set any locations when using ``docker-compose.yml``. 
Parameters may be left empty, default values will be used in that case.

Set ``UPLOAD`` parameter to the upload directory. Program will create subdirectories based on current date automatically.

Also remember to set ``LOG`` to log filename. File will be created automatically if not exists.

Give proper permissions, so files can be written by PHP runtime in specified directories.

Relative paths in ``config.php`` are resolved against the ``app`` directory, regardless of the directory the server was launched from.

``app/config.example.php`` contains example values, not defaults. The defaults are:

- ``UPLOAD``: ``/home/upload`` when it exists, otherwise the ``upload`` directory next to ``app``.
- ``PERMISSIONS``: ``0777``.
- ``LOG``: the ``error_log`` setting from ``php.ini``, then ``/var/log/php/error.log`` when that directory exists, otherwise ``log/php/error.log`` next to ``app``.
- ``LIMIT``: ``32768`` bytes (32 KB).
- ``PHPINFO``: ``0`` (disabled).

Here is an example of my configuration.

```php
<?php

// Upload directory location
$GLOBALS['UPLOAD'] = '../upload';

// Upload directory permissions
$GLOBALS['PERMISSIONS'] = 0777;

// Output log file
// If not specified or empty then /var/log/php/error.log will be used
// unless error_log parameter is set in php.ini
$GLOBALS['LOG'] = '../log/php/error.log';

// Request size limit
$GLOBALS['LIMIT'] = 4096;

// Enable phpinfo.php endpoint
$GLOBALS['PHPINFO'] = 0;

```

