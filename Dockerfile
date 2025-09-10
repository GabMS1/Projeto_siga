# Usa a imagem oficial do PHP 8.2 com Apache
FROM php:8.2-apache

# Define o diretório de trabalho dentro do contêiner
WORKDIR /var/www/html

# Instala a extensão mysqli para a conexão com o MySQL
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copia todos os arquivos do projeto para o diretório de trabalho do contêiner
COPY Projeto_siga/ .

# Define o arquivo de entrada para a aplicação
CMD ["apache2-foreground"]