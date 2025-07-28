#!/bin/bash

# Configuración específica para despliegue en AWS EC2
# Modifica estas variables según tu configuración de AWS

# Configuración de AWS
export AWS_REGION="us-east-1"                    # Región de AWS
export KEY_PAIR_NAME="your-key-pair"             # Nombre de tu key pair
export EC2_INSTANCE_TYPE="t2.micro"              # Tipo de instancia EC2
export EC2_INSTANCE_ID=""                        # Dejar vacío para crear nueva instancia

# Configuración de la aplicación
export DOMAIN="your-domain.com"                  # Tu dominio
export APP_ENV="production"                      # Entorno de la aplicación
export APP_DEBUG="false"                         # Debug mode

# Configuración de base de datos
export DB_HOST="mysql"                           # Host de la base de datos
export DB_PORT="3306"                            # Puerto de la base de datos
export DB_DATABASE="landom_db"                   # Nombre de la base de datos
export DB_USERNAME="landom_user"                 # Usuario de la base de datos
export DB_PASSWORD="your_secure_password"        # Contraseña de la base de datos
export DB_ROOT_PASSWORD="your_secure_root_password" # Contraseña root de MySQL

# Configuración de Redis
export REDIS_HOST="redis"                        # Host de Redis
export REDIS_PORT="6379"                         # Puerto de Redis

# Configuración de SSL (opcional)
export ENABLE_SSL="false"                        # Habilitar SSL con Let's Encrypt
export SSL_EMAIL="your-email@domain.com"         # Email para certificados SSL

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
export MAIL_HOST="smtp.mailtrap.io"             # Host SMTP
export MAIL_PORT="2525"                          # Puerto SMTP
export MAIL_USERNAME="your_smtp_username"        # Usuario SMTP
export MAIL_PASSWORD="your_smtp_password"        # Contraseña SMTP
export MAIL_ENCRYPTION="tls"                     # Encriptación SMTP
export MAIL_FROM_ADDRESS="noreply@your-domain.com" # Email de origen
export MAIL_FROM_NAME="Landom API"               # Nombre de origen

# Configuración de Pusher (para WebSockets)
export PUSHER_APP_ID="your_pusher_app_id"        # App ID de Pusher
export PUSHER_APP_KEY="your_pusher_app_key"      # App Key de Pusher
export PUSHER_APP_SECRET="your_pusher_app_secret" # App Secret de Pusher
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

echo "Configuración de EC2 cargada. Modifica las variables según tus necesidades." 