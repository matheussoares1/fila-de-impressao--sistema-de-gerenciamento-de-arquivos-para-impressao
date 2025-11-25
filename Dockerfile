# Usa uma imagem base oficial do PHP com Apache
FROM php:8.2-apache

# Instala a extensão MySQL
RUN docker-php-ext-install pdo_mysql

# Copia os arquivos da sua aplicação
COPY src/ /var/www/html/

# Porta 80 é padrão, não precisa de EXPOSE explícito, mas ajuda na documentação
EXPOSE 80