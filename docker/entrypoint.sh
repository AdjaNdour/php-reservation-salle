#!/bin/sh

set -e

echo "======================================"
echo " Initialisation de la base de données"
echo "======================================"

php kiki migrate

php kiki seed

echo "======================================"
echo " Seed terminé"
echo "======================================"

exec apache2-foreground