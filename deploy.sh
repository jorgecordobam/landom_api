#!/bin/bash

# Script de despliegue para Landom API
# Autor: Jorge Cordoba
# Repositorio: https://github.com/jorgecordobam/landom_api

set -e  # Salir si cualquier comando falla

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes con colores
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Variables de configuración
REPO_URL="https://github.com/jorgecordobam/landom_api.git"
REPO_NAME="landom_api"
APP_NAME="landom-api"
DOMAIN=""  # Sin dominio configurado (API)
EC2_INSTANCE_ID=""  # Configurar con tu instancia EC2
AWS_REGION="us-west-2"  # Región us-west-2
KEY_PAIR_NAME="landom-key-pair"  # Key pair para Landom API

# Verificar que estamos en el directorio correcto
if [ ! -f "deploy.sh" ]; then
    print_error "Este script debe ejecutarse desde el directorio raíz del proyecto"
    exit 1
fi

print_header "INICIANDO DESPLIEGUE DE LANDOM API"

# 1. CLONACIÓN DEL REPOSITORIO
print_header "PASO 1: CLONANDO REPOSITORIO"

if [ -d "$REPO_NAME" ]; then
    print_warning "El directorio $REPO_NAME ya existe. Eliminando..."
    rm -rf "$REPO_NAME"
fi

print_message "Clonando repositorio desde GitHub..."
git clone "$REPO_URL" "$REPO_NAME"
cd "$REPO_NAME"

print_message "Repositorio clonado exitosamente"

# 2. CREAR ARCHIVOS DOCKER
print_header "PASO 2: CONFIGURANDO DOCKER"

# Crear Dockerfile
print_message "Creando Dockerfile..."
cat > Dockerfile << 'EOF'
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . /var/www/html

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copiar configuración de PHP
COPY docker/php.ini /usr/local/etc/php/

# Copiar configuración de NGINX
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copiar configuración de Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Exponer puerto
EXPOSE 80

# Comando de inicio
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
EOF

# Crear directorio docker
mkdir -p docker

# Crear php.ini
print_message "Creando configuración de PHP..."
cat > docker/php.ini << 'EOF'
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 512M
max_execution_time = 300
max_input_vars = 3000
EOF

# Crear nginx.conf
print_message "Creando configuración de NGINX..."
cat > docker/nginx.conf << 'EOF'
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Crear supervisord.conf
print_message "Creando configuración de Supervisor..."
cat > docker/supervisord.conf << 'EOF'
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/php-fpm.err.log
stdout_logfile=/var/log/supervisor/php-fpm.out.log

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/nginx.err.log
stdout_logfile=/var/log/supervisor/nginx.out.log
EOF

# Crear docker-compose.yml
print_message "Creando docker-compose.yml..."
cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  app:
    build: .
    ports:
      - "80:80"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
    depends_on:
      - mysql
    networks:
      - landom-network

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: landom_db
      MYSQL_ROOT_PASSWORD: your_root_password
      MYSQL_USER: landom_user
      MYSQL_PASSWORD: your_password
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - landom-network

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
    networks:
      - landom-network

volumes:
  mysql_data:

networks:
  landom-network:
    driver: bridge
EOF

print_message "Archivos Docker creados exitosamente"

# 3. CONSTRUIR CON DOCKER
print_header "PASO 3: CONSTRUYENDO APLICACIÓN CON DOCKER"

print_message "Construyendo imagen Docker..."
docker-compose build

print_message "Imagen construida exitosamente"

# 4. DESPLIEGUE EN EC2
print_header "PASO 4: DESPLEGANDO EN EC2"

# Verificar si AWS CLI está instalado
if ! command -v aws &> /dev/null; then
    print_error "AWS CLI no está instalado. Por favor instálalo primero."
    exit 1
fi

# Verificar configuración de AWS
if ! aws sts get-caller-identity &> /dev/null; then
    print_error "AWS CLI no está configurado. Ejecuta 'aws configure' primero."
    exit 1
fi

print_message "Verificando instancia EC2..."

# Si no se especifica una instancia, crear una nueva
if [ -z "$EC2_INSTANCE_ID" ]; then
    print_message "Creando nueva instancia EC2..."
    
    # Crear security group
    SG_NAME="landom-api-sg"
    SG_DESCRIPTION="Security group for Landom API"
    
    SG_ID=$(aws ec2 create-security-group \
        --group-name "$SG_NAME" \
        --description "$SG_DESCRIPTION" \
        --region "$AWS_REGION" \
        --query 'GroupId' \
        --output text)
    
    print_message "Security group creado: $SG_ID"
    
    # Configurar reglas del security group
    aws ec2 authorize-security-group-ingress \
        --group-id "$SG_ID" \
        --protocol tcp \
        --port 22 \
        --cidr 0.0.0.0/0 \
        --region "$AWS_REGION"
    
    aws ec2 authorize-security-group-ingress \
        --group-id "$SG_ID" \
        --protocol tcp \
        --port 80 \
        --cidr 0.0.0.0/0 \
        --region "$AWS_REGION"
    
    aws ec2 authorize-security-group-ingress \
        --group-id "$SG_ID" \
        --protocol tcp \
        --port 443 \
        --cidr 0.0.0.0/0 \
        --region "$AWS_REGION"
    
    # Crear instancia EC2
    EC2_INSTANCE_ID=$(aws ec2 run-instances \
        --image-id ami-0c02fb55956c7d316 \
        --count 1 \
        --instance-type t2.micro \
        --key-name "$KEY_PAIR_NAME" \
        --security-group-ids "$SG_ID" \
        --region "$AWS_REGION" \
        --query 'Instances[0].InstanceId' \
        --output text)
    
    print_message "Instancia EC2 creada: $EC2_INSTANCE_ID"
    
    # Esperar a que la instancia esté running
    print_message "Esperando a que la instancia esté lista..."
    aws ec2 wait instance-running --instance-ids "$EC2_INSTANCE_ID" --region "$AWS_REGION"
fi

# Obtener IP pública de la instancia
PUBLIC_IP=$(aws ec2 describe-instances \
    --instance-ids "$EC2_INSTANCE_ID" \
    --region "$AWS_REGION" \
    --query 'Reservations[0].Instances[0].PublicIpAddress' \
    --output text)

print_message "IP pública de la instancia: $PUBLIC_IP"

# 5. CONFIGURAR SERVIDOR
print_header "PASO 5: CONFIGURANDO SERVIDOR"

# Crear script de configuración del servidor
print_message "Creando script de configuración del servidor..."
cat > server-setup.sh << 'EOF'
#!/bin/bash

# Actualizar sistema
sudo apt-get update
sudo apt-get upgrade -y

# Instalar Docker
sudo apt-get install -y apt-transport-https ca-certificates curl gnupg lsb-release
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Agregar usuario ubuntu al grupo docker
sudo usermod -aG docker ubuntu

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Crear directorio para la aplicación
sudo mkdir -p /opt/landom-api
sudo chown ubuntu:ubuntu /opt/landom-api

echo "Configuración del servidor completada"
EOF

# Copiar archivos al servidor
print_message "Copiando archivos al servidor..."
scp -i ~/.ssh/"$KEY_PAIR_NAME".pem -r . ubuntu@"$PUBLIC_IP":/opt/landom-api/
scp -i ~/.ssh/"$KEY_PAIR_NAME".pem server-setup.sh ubuntu@"$PUBLIC_IP":/home/ubuntu/

# Ejecutar configuración en el servidor
print_message "Ejecutando configuración en el servidor..."
ssh -i ~/.ssh/"$KEY_PAIR_NAME".pem ubuntu@"$PUBLIC_IP" "chmod +x /home/ubuntu/server-setup.sh && /home/ubuntu/server-setup.sh"

# 6. CONFIGURAR NGINX COMO PROXY INVERSO
print_header "PASO 6: CONFIGURANDO NGINX COMO PROXY INVERSO"

# Crear configuración de NGINX
print_message "Creando configuración de NGINX..."
cat > nginx-proxy.conf << 'EOF'
server {
    listen 80;
    server_name landom-api.us-west-2.elasticbeanstalk.com;  # Dominio de la API

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Configuración para archivos estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# Copiar configuración de NGINX al servidor
scp -i ~/.ssh/"$KEY_PAIR_NAME".pem nginx-proxy.conf ubuntu@"$PUBLIC_IP":/home/ubuntu/

# Configurar NGINX en el servidor
ssh -i ~/.ssh/"$KEY_PAIR_NAME".pem ubuntu@"$PUBLIC_IP" << 'EOF'
sudo apt-get install -y nginx
sudo cp /home/ubuntu/nginx-proxy.conf /etc/nginx/sites-available/landom-api
sudo ln -s /etc/nginx/sites-available/landom-api /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl enable nginx
EOF

# 7. DESPLEGAR APLICACIÓN
print_header "PASO 7: DESPLEGAR APLICACIÓN"

# Ejecutar aplicación en el servidor
print_message "Desplegando aplicación en el servidor..."
ssh -i ~/.ssh/"$KEY_PAIR_NAME".pem ubuntu@"$PUBLIC_IP" << 'EOF'
cd /opt/landom-api
sudo docker-compose up -d
sudo docker-compose exec app php artisan key:generate
sudo docker-compose exec app php artisan migrate --force
sudo docker-compose exec app php artisan config:cache
sudo docker-compose exec app php artisan route:cache
sudo docker-compose exec app php artisan view:cache
EOF

# 8. CONFIGURAR SSL (OPCIONAL)
print_header "PASO 8: CONFIGURANDO SSL"

if [ -n "$DOMAIN" ]; then
    print_message "Configurando SSL para dominio: $DOMAIN"
    
    # Instalar Certbot
    print_message "Instalando Certbot..."
    ssh -i ~/.ssh/"$KEY_PAIR_NAME".pem ubuntu@"$PUBLIC_IP" << 'EOF'
    sudo apt-get update
    sudo apt-get install -y certbot python3-certbot-nginx
EOF
    
    # Configurar certificado SSL
    print_message "Configurando certificado SSL..."
    ssh -i ~/.ssh/"$KEY_PAIR_NAME".pem ubuntu@"$PUBLIC_IP" "sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email nuovaiapps@gmail.com"
    
    # Configurar renovación automática
    print_message "Configurando renovación automática..."
    ssh -i ~/.ssh/"$KEY_PAIR_NAME".pem ubuntu@"$PUBLIC_IP" "sudo crontab -l 2>/dev/null | { cat; echo \"0 12 * * * /usr/bin/certbot renew --quiet\"; } | sudo crontab -"
    
    print_message "✅ SSL configurado exitosamente para $DOMAIN"
else
    print_message "Saltando configuración de SSL (sin dominio configurado)"
fi

print_header "DESPLIEGUE COMPLETADO"

print_message "La aplicación ha sido desplegada exitosamente!"
if [ -n "$DOMAIN" ]; then
    print_message "URL de la aplicación: https://$DOMAIN"
else
    print_message "URL de la aplicación: http://$PUBLIC_IP"
fi
print_message "Para verificar el estado: ssh -i ~/.ssh/$KEY_PAIR_NAME.pem ubuntu@$PUBLIC_IP"
print_message "Para ver logs: ssh -i ~/.ssh/$KEY_PAIR_NAME.pem ubuntu@$PUBLIC_IP 'cd /opt/landom-api && docker-compose logs -f'"

# Limpiar archivos temporales
rm -f server-setup.sh nginx-proxy.conf

print_message "Script de despliegue completado exitosamente!" 