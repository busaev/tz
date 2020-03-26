FROM php:7.4.2-fpm-buster
WORKDIR "/application"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
         git curl gnupg make zip libicu-dev gzip vim \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libsqlite3-dev \
        libbz2-dev \
        libmcrypt-dev \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        libonig-dev \
        graphviz \
        libtidy-dev \
        libxslt-dev \
        libmemcached-dev \
        libmemcached11 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql pdo_sqlite bz2 intl mbstring soap tidy xsl

RUN pecl install redis-5.1.1 \
    && pecl install xdebug-2.9.1 \
    && pecl install memcached-3.1.5 \
    && docker-php-ext-enable redis xdebug memcached

#composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && chmod +x composer.phar \
    && mv composer.phar /usr/local/bin/composer

#symfony
RUN  curl -sS https://get.symfony.com/cli/installer | bash \
    && ln -s /root/.symfony/bin/symfony /usr/local/bin/symfony

#cs-fixer
RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v2.phar -o php-cs-fixer \
    && chmod a+x php-cs-fixer \
    && mv php-cs-fixer /usr/local/bin/php-cs-fixer


#nodejs
RUN curl -sL https://deb.nodesource.com/setup_13.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && node -v \
    && npm -v  \
    && npm install yarn -g