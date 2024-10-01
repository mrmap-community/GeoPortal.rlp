#!/bin/sh
RED='\033[0;31m'
GREEN='\033[0;32m'
NOCOLOR='\033[0m'
/opt/geoportal/docker/geoportal/wait_db.sh

if [[ $(hostname -s) = *geoportal* ]]; then 
  echo "doing django migrate"
  echo "$(pwd)"
  python manage.py migrate

  if [ $? != 0 ]; 
  then
    exit 1
    printf "${RED}failed to migrate database${NOCOLOR}\n"
  else
    printf "${GREEN}database migrations applied${NOCOLOR}\n"
  fi
fi


echo 'GeoPortal is ready. Application server is starting now.'

exec "$@"