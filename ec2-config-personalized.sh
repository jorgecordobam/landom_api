#!/bin/bash

# Configuración personalizada para despliegue en AWS EC2
# Email: nuovaiapps@gmail.com
# Autor: Jorge Cordoba

# Configuración de AWS
export AWS_REGION="us-west-2"                    # Región de AWS (us-west-2)
export KEY_PAIR_NAME="landom-key-pair"           # Nombre del key pair (se creará automáticamente)
export EC2_INSTANCE_TYPE="t2.micro"              # Tipo de instancia EC2
export EC2_INSTANCE_ID=""                        # Dejar vacío para crear nueva instancia

# Configuración de la aplicación
export DOMAIN="landom-api.us-west-2.elasticbeanstalk.com"  # Dominio generado para la API
export APP_ENV="production"                      # Entorno de la aplicación
export APP_DEBUG="false"                         # Debug mode

# Configuración de base de datos
export DB_HOST="mysql"                           # Host de la base de datos
export DB_PORT="3306"                            # Puerto de la base de datos
export DB_DATABASE="landom_db"                   # Nombre de la base de datos
export DB_USERNAME="landom_user"                 # Usuario de la base de datos
export DB_PASSWORD="Land0m2024!"                 # Contraseña de la base de datos
export DB_ROOT_PASSWORD="Land0mR00t2024!"        # Contraseña root de MySQL

# Configuración de Redis
export REDIS_HOST="redis"                        # Host de Redis
export REDIS_PORT="6379"                         # Puerto de Redis

# Configuración de SSL (opcional)
export ENABLE_SSL="true"                         # Habilitar SSL con Let's Encrypt
export SSL_EMAIL="nuovaiapps@gmail.com"          # Email para certificados SSL

# Configuración de monitoreo
export ENABLE_MONITORING="true"                  # Habilitar monitoreo básico
export LOG_RETENTION_DAYS="30"                   # Días de retención de logs

# Configuración de backup
export ENABLE_BACKUP="true"                      # Habilitar backups automáticos
export BACKUP_SCHEDULE="0 2 * * *"              # Cron schedule para backups

# Configuración de seguridad
export ALLOWED_ORIGINS="*"                       # CORS allowed origins
export SESSION_SECURE="false"                    # Cookies seguras (true para HTTPS)

# Configuración de rendimiento
export PHP_MEMORY_LIMIT="512M"                   # Límite de memoria PHP
export PHP_MAX_EXECUTION_TIME="300"              # Tiempo máximo de ejecución
export UPLOAD_MAX_FILESIZE="100M"                # Tamaño máximo de archivos
export POST_MAX_SIZE="100M"                      # Tamaño máximo de POST

# Configuración de colas
export QUEUE_CONNECTION="redis"                  # Driver de colas
export QUEUE_WORKERS="2"                         # Número de workers de colas

# Configuración de caché
export CACHE_DRIVER="redis"                      # Driver de caché
export SESSION_DRIVER="redis"                    # Driver de sesiones

# Configuración de logs
export LOG_CHANNEL="stack"                       # Canal de logs
export LOG_LEVEL="info"                          # Nivel de logs

# Configuración de notificaciones
export MAIL_MAILER="smtp"                        # Driver de email
export MAIL_HOST="smtp.gmail.com"               # Host SMTP (Gmail)
export MAIL_PORT="587"                           # Puerto SMTP
export MAIL_USERNAME="nuovaiapps@gmail.com"      # Usuario SMTP
export MAIL_PASSWORD="tu-app-password"           # Contraseña SMTP (App Password de Gmail)
export MAIL_ENCRYPTION="tls"                     # Encriptación SMTP
export MAIL_FROM_ADDRESS="nuovaiapps@gmail.com"  # Email de origen
export MAIL_FROM_NAME="Landom API"               # Nombre de origen

# Configuración de Pusher (para WebSockets)
export PUSHER_APP_ID=""                          # App ID de Pusher (configurar si necesitas)
export PUSHER_APP_KEY=""                         # App Key de Pusher
export PUSHER_APP_SECRET=""                      # App Secret de Pusher
export PUSHER_HOST=""                            # Host de Pusher (vacío para usar Pusher.com)
export PUSHER_PORT="443"                         # Puerto de Pusher
export PUSHER_SCHEME="https"                     # Esquema de Pusher
export PUSHER_APP_CLUSTER="mt1"                  # Cluster de Pusher

# Configuración de almacenamiento
export FILESYSTEM_DISK="local"                   # Disco de almacenamiento
export AWS_ACCESS_KEY_ID=""                      # AWS Access Key (para S3)
export AWS_SECRET_ACCESS_KEY=""                  # AWS Secret Key (para S3)
export AWS_DEFAULT_REGION="$AWS_REGION"          # Región AWS para S3
export AWS_BUCKET=""                             # Bucket S3 (si usas S3)

# Configuración de análisis
export ANALYTICS_ENABLED="false"                 # Habilitar analytics
export GOOGLE_ANALYTICS_ID=""                    # Google Analytics ID

# Configuración de desarrollo
export DEV_MODE="false"                          # Modo desarrollo
export DEBUG_BAR="false"                         # Debug bar
export IDE_HELPER="false"                        # IDE Helper

echo "✅ Configuración personalizada cargada para nuovaiapps@gmail.com"
echo "📧 Email configurado: nuovaiapps@gmail.com"
echo "🌍 Región AWS: $AWS_REGION"
echo "🔑 Key Pair: $KEY_PAIR_NAME"
echo "🖥️  Tipo de instancia: $EC2_INSTANCE_TYPE"
echo "🌐 Dominio: $DOMAIN"
echo "🔒 SSL: Habilitado"
echo ""
echo "⚠️  IMPORTANTE: Modifica las siguientes variables según tus necesidades:"
echo "   - MAIL_PASSWORD: Configura tu App Password de Gmail"
echo "   - AWS_ACCESS_KEY_ID y AWS_SECRET_ACCESS_KEY: Si quieres usar S3" 