#!/bin/sh

doctrine orm:schema-tool:create
mv database.sqlite db/testowa.db
chmod 666 db/testowa.db

doctrine orm:generate-proxies
