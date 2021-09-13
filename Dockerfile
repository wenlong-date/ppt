FROM php:7.4.23-cli

RUN  sed -i "s@http://deb.debian.org@http://mirrors.aliyun.com@g" /etc/apt/sources.list
RUN  sed -i "s@http://security.debian.org@http://mirrors.aliyun.com@g" /etc/apt/sources.list

RUN apt-get update && apt-get install -y vim nginx wget libzip-dev \
        && docker-php-ext-configure pcntl --enable-pcntl \
        && docker-php-ext-install pcntl zip

# composer
RUN wget https://mirrors.aliyun.com/composer/composer.phar -O /usr/bin/composer \
    && chmod +x /usr/bin/composer \
    && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

COPY . /var/www/html

RUN cd /var/www/html && composer install --optimize-autoloader --no-dev

ADD ./docker-entrypoint.sh /
RUN chmod +x /docker-entrypoint.sh
ENTRYPOINT [ "/docker-entrypoint.sh" ]