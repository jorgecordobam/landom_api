# Despliegue de Landom API en Namecheap

Este script automatiza el despliegue completo de la aplicación Laravel Landom API en un servidor de Namecheap.

## 📋 Requisitos Previos

### 1. Servidor Namecheap
- VPS o Dedicated Server con acceso SSH
- Sistema operativo Ubuntu 20.04+ o CentOS 8+
- Acceso root o sudo
- Mínimo 1GB RAM, 20GB almacenamiento

### 2. Dominio
- Dominio registrado en Namecheap
- DNS configurado apuntando al servidor

### 3. Acceso SSH
- Clave SSH configurada
- Usuario con permisos sudo

## 🚀 Instalación Rápida

### Paso 1: Preparar el entorno local

```bash
# Clonar el repositorio
git clone https://github.com/jorgecordobam/landom_api.git
cd landom_api

# Dar permisos de ejecución al script
chmod +x deploy-namecheap.sh
```

### Paso 2: Configurar variables

Edita las variables en el script `deploy-namecheap.sh`:

```bash
# Variables principales a modificar
SERVER_IP="tu-ip-del-servidor"           # IP de tu servidor Namecheap
SERVER_USER="tu-usuario-ssh"              # Usuario SSH (root, cpanel, etc.)
SSH_KEY_PATH="~/.ssh/tu-clave"           # Ruta a tu clave SSH (opcional)
DOMAIN="tu-dominio.com"                   # Tu dominio en Namecheap
```

### Paso 3: Ejecutar el despliegue

```bash
./deploy-namecheap.sh
```

## ⚙️ Configuración Detallada

### Variables de Configuración

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `SERVER_IP` | IP del servidor Namecheap | `192.168.1.100` |
| `SERVER_USER` | Usuario SSH | `root` o `cpanel` |
| `SSH_KEY_PATH` | Ruta a clave SSH | `~/.ssh/id_rsa` |
| `DOMAIN` | Dominio de la aplicación | `api.tudominio.com` |
| `APP_DIR` | Directorio de la aplicación | `/home/usuario/public_html` |
| `BACKUP_DIR` | Directorio de backups | `/home/usuario/backups` |

### Configuración de Base de Datos

El script creará automáticamente:
- Base de datos: `landom_db`
- Usuario: `landom_user`
- Contraseña: `your_secure_password` (cambiar en el script)

### Configuración de SSL

El script puede configurar SSL automáticamente con Let's Encrypt:
- Certificados gratuitos
- Renovación automática
- Soporte para www y no-www

## 📁 Estructura del Despliegue

```
/home/usuario/
├── public_html/          # Aplicación Laravel
│   ├── app/
│   ├── public/
│   ├── storage/
│   └── .env
├── backups/              # Backups automáticos
│   ├── files_*.tar.gz
│   └── database_*.sql.gz
└── monitor.log          # Logs de monitoreo
```

## 🔧 Servicios Instalados

### Servicios Principales
- **NGINX**: Servidor web y proxy inverso
- **PHP 8.2-FPM**: Procesador PHP
- **MySQL 8.0**: Base de datos
- **Redis**: Caché y sesiones

### Configuraciones Automáticas
- Optimización de PHP (memoria, tiempo de ejecución)
- Configuración de seguridad NGINX
- Headers de seguridad
- Compresión gzip
- Caché de archivos estáticos

## 📊 Monitoreo y Mantenimiento

### Scripts Automáticos

1. **Backup Diario** (2:00 AM)
   - Archivos de la aplicación
   - Base de datos
   - Retención: 7 días

2. **Monitoreo** (cada 5 minutos)
   - Estado de servicios
   - Uso de disco y memoria
   - Reinicio automático si es necesario

### Comandos Útiles

```bash
# Conectar al servidor
ssh usuario@ip-servidor

# Ver logs de la aplicación
tail -f /var/log/nginx/landom-api.error.log

# Hacer backup manual
./backup-script.sh

# Ver estado de servicios
systemctl status nginx php8.2-fpm mysql redis

# Reiniciar servicios
sudo systemctl restart nginx php8.2-fpm mysql redis
```

## 🔒 Seguridad

### Configuraciones Implementadas
- Headers de seguridad NGINX
- Denegación de acceso a archivos ocultos
- Configuración segura de PHP
- Firewall básico (si está disponible)

### Recomendaciones Adicionales
1. Cambiar contraseñas por defecto
2. Configurar firewall
3. Actualizar regularmente el sistema
4. Monitorear logs de seguridad

## 🚨 Solución de Problemas

### Problemas Comunes

#### 1. Error de conexión SSH
```bash
# Verificar conexión
ssh -i ~/.ssh/tu-clave usuario@ip-servidor

# Verificar permisos de clave
chmod 600 ~/.ssh/tu-clave
```

#### 2. Error de permisos
```bash
# En el servidor
sudo chown -R usuario:usuario /home/usuario/public_html
sudo chmod -R 755 /home/usuario/public_html/storage
```

#### 3. Error de base de datos
```bash
# Verificar MySQL
sudo systemctl status mysql
sudo mysql -u root -p

# Crear usuario manualmente
CREATE USER 'landom_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON landom_db.* TO 'landom_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 4. Error de NGINX
```bash
# Verificar configuración
sudo nginx -t

# Ver logs
sudo tail -f /var/log/nginx/error.log
```

### Logs Importantes
- **NGINX**: `/var/log/nginx/landom-api.error.log`
- **PHP**: `/var/log/php8.2-fpm.log`
- **MySQL**: `/var/log/mysql/error.log`
- **Aplicación**: `/home/usuario/public_html/storage/logs/laravel.log`

## 📈 Optimización

### Rendimiento
- Caché de configuración Laravel
- Caché de rutas
- Caché de vistas
- Compresión gzip
- Headers de caché para archivos estáticos

### Escalabilidad
- Configuración para múltiples workers PHP-FPM
- Redis para sesiones y caché
- Backups automáticos
- Monitoreo de recursos

## 🔄 Actualizaciones

### Actualizar la aplicación
```bash
# En el servidor
cd /home/usuario/public_html
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Actualizar dependencias del sistema
```bash
sudo apt-get update && sudo apt-get upgrade -y
sudo systemctl restart nginx php8.2-fpm mysql redis
```

## 📞 Soporte

Si encuentras problemas:

1. Revisar logs de error
2. Verificar configuración de variables
3. Comprobar conectividad SSH
4. Verificar permisos de archivos

### Información de Contacto
- **Repositorio**: https://github.com/jorgecordobam/landom_api
- **Autor**: Jorge Cordoba

## 📝 Notas Importantes

1. **Backup**: Siempre haz backup antes de desplegar
2. **SSL**: Configura SSL para producción
3. **Monitoreo**: Revisa logs regularmente
4. **Actualizaciones**: Mantén el sistema actualizado
5. **Seguridad**: Cambia contraseñas por defecto

---

**¡Tu aplicación Laravel está lista para producción!** 🎉 