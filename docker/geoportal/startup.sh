#!/bin/sh
source /opt/venv/bin/activate
gunicorn -b 0.0.0.0:8001 --workers=4 --log-level=info --timeout=0 GeoPortal.wsgi:application