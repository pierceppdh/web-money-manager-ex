FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www/html

COPY WebApp/ .
COPY docker/entrypoint.sh /usr/local/bin/webmmx-entrypoint.sh

RUN mv htaccess.txt .htaccess \
    && if [ -f attachments/htaccess.txt ]; then mv attachments/htaccess.txt attachments/.htaccess; fi \
    && chmod +x /usr/local/bin/webmmx-entrypoint.sh \
    && mkdir -p /data/attachments \
    && chown -R www-data:www-data /var/www/html /data

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/webmmx-entrypoint.sh"]
CMD ["apache2-foreground"]
