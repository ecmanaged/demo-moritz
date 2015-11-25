#!/bin/bash

# Launch demo
service php5-fpm start
nginx &

# Wait for it...
sleep 2

# Check it
http_code=$(curl --write-out %{http_code} --silent --output /dev/null http://localhost)
[ $http_code -eq 200 ] && { echo "Test passed!!"; exit 0; } || { echo "Test failed!!"; exit 1; }
exit 1

