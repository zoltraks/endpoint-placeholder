Endpoint Placeholder
====================

This project saves every HTTP request together with headers to a local file repository.

It returns payload body from request as a response.

By default it exposes 6443 for HTTPS and 6080 for HTTP traffic.

```
docker-compose up
```

Optionally specify ``docker-compose.yml`` file.

```
docker-compose -f docker-compose.yml up
```

Generate self signed certificate using ``mkcertself.sh`` in ``ssl/`` directory if not exists.

```
DOMAIN=mydomain.example.org ./mkcertself.sh | less
```
