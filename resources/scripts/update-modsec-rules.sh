#!/bin/bash
# use this in a cronjob every night
git clone --progress https://github.com/coreruleset/coreruleset /tmp/modsecurity-crs
cp -a /tmp/modsecurity-crs/rules /usr/share/modsecurity-crs/
rm -r /tmp/modsecurity-crs
