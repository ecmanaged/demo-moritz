#!/bin/bash

# Launch demo
pm2 start /opt/demo-moritz/server.js -i 0 > /dev/null

# Wait for it...
sleep 2

# Check it
http_code=$(curl --write-out %{http_code} --silent --output /dev/null http://localhost)
[ $http_code -eq 200 ] && { echo "Test passed!!"; exit 0; } || { echo "Test failed!!"; exit 1; }
exit 1

