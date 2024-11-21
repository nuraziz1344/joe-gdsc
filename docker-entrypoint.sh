if [ -n "$PORT" ]; then
  sed -i "s|listen = 127.0.0.1:9000|listen = 0.0.0.0:$PORT|" /usr/local/etc/php-fpm.d/www.conf
  echo "Updated PHP-FPM to listen on port $PORT"
fi
