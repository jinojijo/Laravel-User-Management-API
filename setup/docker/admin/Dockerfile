FROM php:8.1.3-fpm

RUN apt-get update && apt-get install -y \
	supervisor \
	build-essential \
	libpng-dev \
	libzip-dev \
	libjpeg62-turbo-dev \
	libfreetype6-dev \
	locales \
	zip \
	vim \
	nano \
	unzip \
	git \
	curl 

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql zip exif pcntl mysqli

RUN docker-php-ext-install gd
RUN mkdir -p /var/log/supervisor
COPY supervisord.conf /etc/supervisor/supervisord.conf

RUN cd /tmp \
	&& curl -o ioncube.tar.gz https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz \
    && tar -xvvzf ioncube.tar.gz \
    && mv ioncube/ioncube_loader_lin_8.1.so /usr/local/lib/php/extensions/no-debug-non-zts-20210902/ \
    && rm -Rf ioncube.tar.gz ioncube \
    && echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20210902/ioncube_loader_lin_8.1.so" > /usr/local/etc/php/conf.d/00_docker-php-ext-ioncube_loader_lin_8.1.ini

RUN pecl install redis && docker-php-ext-enable redis \
	# && docker-php-ext-install -j$(nproc) iconv opcache \
		# && docker-php-ext-install -j$(nproc) opcache \
		# && docker-php-ext-enable iconv opcache \
		# && docker-php-ext-enable opcache \
		&& apt-get autoremove -y


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer