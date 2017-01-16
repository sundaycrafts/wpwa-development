#!/bin/bash
docker run -p 80:80 -v /usr/share/nginx/www --name docker-wordpress-nginx -d eugeneware/docker-wordpress-nginx
echo 'up docker named docker-wordpress-nginx'

