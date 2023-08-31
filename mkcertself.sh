#!/bin/sh

DIR=./ssl
DAYS=3652

# If the variable is not already set, assign a default value
[ -z "$DOMAIN" ] && DOMAIN=www.example.com

if [ ! -f $DIR/private.key ]
then
	openssl genpkey -algorithm RSA -out $DIR/private.key
fi

if [ ! -f $DIR/public.crt ]
then
	openssl req -new -x509 -key $DIR/private.key -out $DIR/public.crt -days $DAYS -subj "/C=/ST=/L=/O=/CN=$DOMAIN"
fi

openssl x509 -in $DIR/public.crt -text -noout
