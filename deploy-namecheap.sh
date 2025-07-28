#!/bin/bash

# Script de despliegue para Landom API en Namecheap
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

# Variables de configuración - MODIFICA ESTAS VARIABLES
SERVER_IP="your-server-ip"                    # IP de tu servidor Namecheap
SERVER_USER="your-username"                    # Usuario SSH (root, cpanel, etc.)
SSH_KEY_PATH="~/.ssh/your-key"                # Ruta a tu clave SSH (si usas)
DOMAIN="your-domain.com"                       # Tu dominio en Namecheap
APP_DIR="/home/$SERVER_USER/public_html"       # Directorio de la aplicación
BACKUP_DIR="/home/$SERVER_USER/backups"        # Directorio de backups

# Verificar que estamos en el directorio correcto
if [ ! -f "deploy-namecheap.sh" ]; then
    print_error "Este script debe ejecutarse desde el directorio raíz del proyecto"
    exit 1
fi

print_header "INICIANDO DESPLIEGUE DE LANDOM API EN NAMECHEAP"

# 1. CLONACIÓN DEL REPOSITORIO
print_header "PASO 1: CLONANDO REPOSITORIO"

REPO_URL="https://github.com/jorgecordobam/landom_api.git"
REPO_NAME="landom_api"

if [ -d "$REPO_NAME" ]; then
    print_warning "El directorio $REPO_NAME ya existe. Eliminando..."
    rm -rf "$REPO_NAME"
fi

print_message "Clonando repositorio desde GitHub..."
git clone "$REPO_URL" "$REPO_NAME"
cd "$REPO_NAME"

print_message "Repositorio clonado exitosamente"

# 2. PREPARAR ARCHIVOS PARA DESPLIEGUE
print_header "PASO 2: PREPARANDO ARCHIVOS"

# Crear archivo .env de producción
print_message "Creando archivo .env de producción..."
cat > .env.production << 'EOF'
APP_NAME="Landom API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landom_db
DB_USERNAME=landom_user
DB_PASSWORD=your_secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
EOF

# Crear script de instalación en el servidor
print_message "Creando script de instalación para el servidor..."
cat > server-install.sh << 'EOF'
#!/bin/bash

# Script de instalación para servidor Namecheap
set -e

echo "Iniciando instalación en servidor Namecheap..."

# Actualizar sistema
sudo apt-get update
sudo apt-get upgrade -y

# Instalar dependencias del sistema
sudo apt-get install -y \
    nginx \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-xml \
    php8.2-curl \
    php8.2-gd \
    php8.2-mbstring \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-redis \
    mysql-server \
    redis-server \
    git \
    curl \
    unzip \
    composer

# Configurar MySQL
sudo mysql_secure_installation

# Crear base de datos y usuario
sudo mysql -e "CREATE DATABASE IF NOT EXISTS landom_db;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'landom_user'@'localhost' IDENTIFIED BY 'your_secure_password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON landom_db.* TO 'landom_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Configurar PHP
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/post_max_size = 8M/post_max_size = 100M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini

# Reiniciar servicios
sudo systemctl restart php8.2-fpm
sudo systemctl restart mysql
sudo systemctl restart redis

echo "Instalación de dependencias completada"
EOF

# Crear configuración de NGINX
print_message "Creando configuración de NGINX..."
cat > nginx-site.conf << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /home/your-username/public_html/public;
    index index.php index.html index.htm;

    # Configuración de seguridad
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Configuración de archivos estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Configuración de Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Configuración de PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Denegar acceso a archivos ocultos
    location ~ /\. {
        deny all;
    }

    # Configuración de logs
    access_log /var/log/nginx/landom-api.access.log;
    error_log /var/log/nginx/landom-api.error.log;
}
EOF

print_message "Archivos de configuración creados exitosamente"

# 3. CONECTAR AL SERVIDOR Y PREPARAR ENTORNO
print_header "PASO 3: CONFIGURANDO SERVIDOR"

# Función para ejecutar comandos SSH
ssh_exec() {
    if [ -n "$SSH_KEY_PATH" ]; then
        ssh -i "$SSH_KEY_PATH" "$SERVER_USER@$SERVER_IP" "$1"
    else
        ssh "$SERVER_USER@$SERVER_IP" "$1"
    fi
}

# Función para copiar archivos
scp_copy() {
    if [ -n "$SSH_KEY_PATH" ]; then
        scp -i "$SSH_KEY_PATH" "$1" "$SERVER_USER@$SERVER_IP:$2"
    else
        scp "$1" "$SERVER_USER@$SERVER_IP:$2"
    fi
}

print_message "Conectando al servidor $SERVER_IP..."

# Verificar conexión SSH
if ! ssh_exec "echo 'Conexión SSH exitosa'" &> /dev/null; then
    print_error "No se puede conectar al servidor. Verifica la IP, usuario y clave SSH."
    exit 1
fi

print_message "Conexión SSH establecida"

# Crear directorios necesarios
print_message "Creando directorios en el servidor..."
ssh_exec "mkdir -p $APP_DIR $BACKUP_DIR"

# 4. INSTALAR DEPENDENCIAS EN EL SERVIDOR
print_header "PASO 4: INSTALANDO DEPENDENCIAS"

# Copiar script de instalación
scp_copy "server-install.sh" "/home/$SERVER_USER/"

# Ejecutar instalación
print_message "Ejecutando instalación de dependencias..."
ssh_exec "chmod +x /home/$SERVER_USER/server-install.sh && /home/$SERVER_USER/server-install.sh"

# 5. DESPLEGAR APLICACIÓN
print_header "PASO 5: DESPLEGANDO APLICACIÓN"

# Crear backup si existe instalación previa
print_message "Creando backup de instalación previa..."
ssh_exec "if [ -d '$APP_DIR' ] && [ \"\$(ls -A $APP_DIR)\" ]; then tar -czf $BACKUP_DIR/backup_\$(date +%Y%m%d_%H%M%S).tar.gz -C $APP_DIR .; fi"

# Limpiar directorio de aplicación
ssh_exec "rm -rf $APP_DIR/*"

# Copiar archivos de la aplicación
print_message "Copiando archivos de la aplicación..."
if [ -n "$SSH_KEY_PATH" ]; then
    scp -i "$SSH_KEY_PATH" -r . "$SERVER_USER@$SERVER_IP:$APP_DIR/"
else
    scp -r . "$SERVER_USER@$SERVER_IP:$APP_DIR/"
fi

# Configurar permisos
print_message "Configurando permisos..."
ssh_exec "cd $APP_DIR && chmod -R 755 storage bootstrap/cache && chown -R $SERVER_USER:$SERVER_USER ."

# 6. CONFIGURAR APLICACIÓN
print_header "PASO 6: CONFIGURANDO APLICACIÓN"

# Copiar archivo .env
scp_copy ".env.production" "$APP_DIR/.env"

# Instalar dependencias de Composer
print_message "Instalando dependencias de Composer..."
ssh_exec "cd $APP_DIR && composer install --no-dev --optimize-autoloader"

# Generar clave de aplicación
print_message "Generando clave de aplicación..."
ssh_exec "cd $APP_DIR && php artisan key:generate"

# Ejecutar migraciones
print_message "Ejecutando migraciones..."
ssh_exec "cd $APP_DIR && php artisan migrate --force"

# Optimizar aplicación
print_message "Optimizando aplicación..."
ssh_exec "cd $APP_DIR && php artisan config:cache && php artisan route:cache && php artisan view:cache"

# 7. CONFIGURAR NGINX
print_header "PASO 7: CONFIGURANDO NGINX"

# Copiar configuración de NGINX
scp_copy "nginx-site.conf" "/home/$SERVER_USER/"

# Configurar NGINX
print_message "Configurando NGINX..."
ssh_exec << 'EOF'
# Reemplazar variables en la configuración
sed -i "s/your-domain.com/$DOMAIN/g" /home/$SERVER_USER/nginx-site.conf
sed -i "s/your-username/$SERVER_USER/g" /home/$SERVER_USER/nginx-site.conf

# Copiar configuración
sudo cp /home/$SERVER_USER/nginx-site.conf /etc/nginx/sites-available/landom-api

# Habilitar sitio
sudo ln -sf /etc/nginx/sites-available/landom-api /etc/nginx/sites-enabled/

# Deshabilitar sitio por defecto
sudo rm -f /etc/nginx/sites-enabled/default

# Verificar configuración
sudo nginx -t

# Reiniciar NGINX
sudo systemctl restart nginx
sudo systemctl enable nginx
EOF

# 8. CONFIGURAR SSL (OPCIONAL)
print_header "PASO 8: CONFIGURANDO SSL"

read -p "¿Quieres configurar SSL con Let's Encrypt? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_message "Instalando Certbot..."
    ssh_exec "sudo apt-get install -y certbot python3-certbot-nginx"
    
    print_message "Configurando certificado SSL..."
    ssh_exec "sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email your-email@domain.com"
    
    print_message "Configurando renovación automática..."
    ssh_exec "sudo crontab -l 2>/dev/null | { cat; echo \"0 12 * * * /usr/bin/certbot renew --quiet\"; } | sudo crontab -"
fi

# 9. CONFIGURAR MONITOREO Y BACKUPS
print_header "PASO 9: CONFIGURANDO MONITOREO Y BACKUPS"

# Crear script de backup
print_message "Creando script de backup..."
cat > backup-script.sh << 'EOF'
#!/bin/bash
# Script de backup automático
BACKUP_DIR="/home/$SERVER_USER/backups"
APP_DIR="/home/$SERVER_USER/public_html"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup de archivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $APP_DIR .

# Backup de base de datos
mysqldump -u landom_user -p'your_secure_password' landom_db > $BACKUP_DIR/database_$DATE.sql

# Comprimir backup de base de datos
gzip $BACKUP_DIR/database_$DATE.sql

# Eliminar backups antiguos (mantener últimos 7 días)
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete

echo "Backup completado: $DATE"
EOF

# Copiar y configurar script de backup
scp_copy "backup-script.sh" "/home/$SERVER_USER/"
ssh_exec "chmod +x /home/$SERVER_USER/backup-script.sh"

# Configurar cron para backups diarios
print_message "Configurando backups automáticos..."
ssh_exec "crontab -l 2>/dev/null | { cat; echo \"0 2 * * * /home/$SERVER_USER/backup-script.sh\"; } | crontab -"

# 10. CONFIGURAR MONITOREO
print_header "PASO 10: CONFIGURANDO MONITOREO"

# Crear script de monitoreo
cat > monitor-script.sh << 'EOF'
#!/bin/bash
# Script de monitoreo básico
APP_DIR="/home/$SERVER_USER/public_html"
LOG_FILE="/home/$SERVER_USER/monitor.log"

# Verificar servicios
if ! systemctl is-active --quiet nginx; then
    echo "$(date): NGINX no está ejecutándose" >> $LOG_FILE
    systemctl restart nginx
fi

if ! systemctl is-active --quiet php8.2-fpm; then
    echo "$(date): PHP-FPM no está ejecutándose" >> $LOG_FILE
    systemctl restart php8.2-fpm
fi

if ! systemctl is-active --quiet mysql; then
    echo "$(date): MySQL no está ejecutándose" >> $LOG_FILE
    systemctl restart mysql
fi

# Verificar espacio en disco
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 90 ]; then
    echo "$(date): Uso de disco crítico: ${DISK_USAGE}%" >> $LOG_FILE
fi

# Verificar memoria
MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.0f", $3/$2 * 100.0)}')
if [ $MEMORY_USAGE -gt 90 ]; then
    echo "$(date): Uso de memoria crítico: ${MEMORY_USAGE}%" >> $LOG_FILE
fi
EOF

# Copiar y configurar script de monitoreo
scp_copy "monitor-script.sh" "/home/$SERVER_USER/"
ssh_exec "chmod +x /home/$SERVER_USER/monitor-script.sh"

# Configurar cron para monitoreo cada 5 minutos
ssh_exec "crontab -l 2>/dev/null | { cat; echo \"*/5 * * * * /home/$SERVER_USER/monitor-script.sh\"; } | crontab -"

print_header "DESPLIEGUE COMPLETADO"

print_message "¡La aplicación ha sido desplegada exitosamente!"
print_message "URL de la aplicación: http://$DOMAIN"
print_message "Para verificar el estado: ssh $SERVER_USER@$SERVER_IP"
print_message "Para ver logs: ssh $SERVER_USER@$SERVER_IP 'tail -f /var/log/nginx/landom-api.error.log'"
print_message "Para hacer backup manual: ssh $SERVER_USER@$SERVER_IP './backup-script.sh'"

# Limpiar archivos temporales
rm -f server-install.sh nginx-site.conf backup-script.sh monitor-script.sh

print_message "Script de despliegue completado exitosamente!" 