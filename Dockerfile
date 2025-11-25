# Usa a imagem oficial do PHP 8.2 com Apache
FROM php:8.2-apache

# Define o diretório de trabalho dentro do contêiner
WORKDIR /var/www/html

# Instala a extensão mysqli para a conexão com o MySQL
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Instala o netcat-traditional, que é necessário para o script de espera
RUN apt-get update && apt-get install -y netcat-traditional

# Copia o script de espera e o torna executável
COPY wait-for-db.sh /usr/local/bin/wait-for-db.sh
RUN chmod +x /usr/local/bin/wait-for-db.sh

# CORREÇÃO: Copia a configuração de buffer de saída para o PHP
COPY output_buffering.ini /usr/local/etc/php/conf.d/output_buffering.ini

# Copia todos os arquivos do projeto para o diretório de trabalho do contêiner
# IMPORTANTE: Este passo é mantido para que a imagem seja construída corretamente,
# mas o acesso em tempo real será feito pelo Volume do docker-compose.yml
COPY Projeto_siga/ .

# Usa o script de espera para iniciar o Apache
CMD ["wait-for-db.sh", "apache2-foreground"]