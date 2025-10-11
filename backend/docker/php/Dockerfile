FROM php:8.2-fpm

# Step 1: Install dependencies
RUN \
  apt-get update \
  && apt-get install -y \
    git-core

RUN apt-get update
RUN apt-get install -y curl
RUN apt-get install -y git
RUN apt-get install -y wget
RUN apt-get install -y ca-certificates
RUN apt-get install -y unzip
RUN apt-get install -y lsb-release
RUN apt-get install -y libicu-dev
RUN apt-get install -y libpq-dev
RUN apt-get install -y libzip-dev
RUN apt-get install -y postgresql-client
RUN apt-get install -y gnupg

# Step 2: Add PostgreSQL repository
RUN wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - && \
    echo "deb http://apt.postgresql.org/pub/repos/apt/ $(lsb_release -cs)-pgdg main" | tee /etc/apt/sources.list.d/pgdg.list

# Step 3: Install Node.js
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Step 4: Install PHP extensions
RUN pecl install redis && \
    docker-php-ext-enable redis && \
    docker-php-ext-configure pgsql --with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install \
    intl \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    bcmath \
    pcntl \
    exif

# Step 5: Install Composer
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

# Step 6: Clean up
RUN apt-get -y autoremove && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    chown -R www-data:www-data /var/www

# Step 7: Install additional PHP extensions using docker-php-extension-installer
RUN curl -sSL https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o /usr/local/bin/install-php-extensions
RUN chmod +x /usr/local/bin/install-php-extensions
RUN install-php-extensions gd
RUN install-php-extensions sodium

# Step 8: Set working directory and user
WORKDIR /var/www/app

USER www-data
