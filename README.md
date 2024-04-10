Endpoint Placeholder
====================

This project stores every HTTP request together with headers to a local file repository.

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
