FROM php:7.3.27-apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/example
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install xdebug-2.6.1 \
	&& docker-php-ext-enable xdebug
RUN a2enmod rewrite
RUN curl -L https://phar.phpunit.de/phpunit.phar -o /usr/local/bin/phpunit && chmod +x /usr/local/bin/phpunit

#RUN php /var/www/html/artisan migrate --seed

# add xdebug configurations
RUN { \
        echo '[xdebug]'; \
        echo 'zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20160303/xdebug.so'; \
        echo 'xdebug.remote_handler=dbgp'; \
        echo 'xdebug.remote_enable=on'; \
        echo 'xdebug.remote_autostart=on'; \
        echo 'xdebug.remote_port=9000'; \
		echo 'xdebug.profiler_output_dir="/var/www/html"'; \
		echo 'xdebug.remote_host=localhost'; \
    } > /usr/local/etc/php/conf.d/xdebug.ini