# Despliegue de Landom API en AWS EC2

Este script automatiza el despliegue completo de la aplicación Laravel Landom API en una instancia EC2 de AWS.

## 📋 Requisitos Previos

### 1. Configuración de AWS
- Cuenta de AWS con acceso a EC2
- AWS CLI configurado localmente
- Key pair creado en AWS
- Permisos para crear instancias EC2, security groups, etc.

### 2. Configuración Local
- AWS CLI instalado y configurado
- Git instalado
- Acceso SSH configurado

### 3. Dominio (Opcional)
- Dominio registrado
- DNS configurado para apuntar a la instancia EC2

## 🚀 Instalación Rápida

### Paso 1: Preparar el entorno local

```bash
# Clonar el repositorio
git clone https://github.com/jorgecordobam/landom_api.git
cd landom_api

# Dar permisos de ejecución al script
chmod +x deploy.sh
chmod +x ec2-config.sh
```

### Paso 2: Configurar AWS CLI

```bash
# Configurar AWS CLI
aws configure

# Verificar configuración
aws sts get-caller-identity
```

### Paso 3: Configurar variables

Edita las variables en `ec2-config.sh`:

```bash
# Variables principales a modificar
AWS_REGION="us-east-1"                    # Tu región de AWS
KEY_PAIR_NAME="your-key-pair"             # Nombre de tu key pair
EC2_INSTANCE_TYPE="t2.micro"              # Tipo de instancia
DOMAIN="your-domain.com"                  # Tu dominio
```

### Paso 4: Ejecutar el despliegue

```bash
# Cargar configuración
source ec2-config.sh

# Ejecutar despliegue
./deploy.sh
```

## ⚙️ Configuración Detallada

### Variables de Configuración

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `AWS_REGION` | Región de AWS | `us-east-1`, `us-west-2` |
| `KEY_PAIR_NAME` | Nombre del key pair | `my-key-pair` |
| `EC2_INSTANCE_TYPE` | Tipo de instancia EC2 | `t2.micro`, `t3.small` |
| `EC2_INSTANCE_ID` | ID de instancia existente | `i-1234567890abcdef0` |
| `DOMAIN` | Dominio de la aplicación | `api.tudominio.com` |

### Tipos de Instancia Recomendados

| Uso | Tipo de Instancia | RAM | vCPU | Costo/Mes |
|-----|------------------|-----|------|-----------|
| Desarrollo | t2.micro | 1GB | 1 | ~$8 |
| Producción pequeña | t3.small | 2GB | 2 | ~$15 |
| Producción media | t3.medium | 4GB | 2 | ~$30 |
| Producción grande | t3.large | 8GB | 2 | ~$60 |

## 🔧 Servicios Desplegados

### Stack Completo
- **EC2 Instance**: Ubuntu 20.04 LTS
- **Docker & Docker Compose**: Contenedores
- **NGINX**: Proxy inverso
- **MySQL 8.0**: Base de datos
- **Redis**: Caché y sesiones
- **PHP 8.2-FPM**: Procesador PHP

### Configuraciones Automáticas
- Security Groups con puertos 22, 80, 443
- Configuración de Docker optimizada
- NGINX como proxy inverso
- SSL con Let's Encrypt (opcional)
- Backups automáticos
- Monitoreo de servicios

## 📊 Monitoreo y Mantenimiento

### Scripts Automáticos

1. **Backup Diario** (2:00 AM)
   - Archivos de la aplicación
   - Base de datos
   - Retención: 7 días

2. **Monitoreo** (cada 5 minutos)
   - Estado de servicios Docker
   - Uso de recursos
   - Reinicio automático si es necesario

### Comandos Útiles

```bash
# Conectar a la instancia EC2
ssh -i ~/.ssh/your-key.pem ubuntu@IP-PUBLICA

# Ver logs de la aplicación
docker-compose logs -f app

# Hacer backup manual
./backup-script.sh

# Ver estado de servicios
docker-compose ps

# Reiniciar servicios
docker-compose restart
```

## 🔒 Seguridad

### Configuraciones Implementadas
- Security Groups con acceso restringido
- Headers de seguridad NGINX
- Configuración segura de Docker
- Denegación de acceso a archivos sensibles

### Recomendaciones Adicionales
1. Configurar AWS WAF
2. Habilitar CloudTrail
3. Configurar alertas de CloudWatch
4. Usar IAM roles en lugar de access keys

## 🚨 Solución de Problemas

### Problemas Comunes

#### 1. Error de AWS CLI
```bash
# Verificar configuración
aws configure list

# Verificar permisos
aws sts get-caller-identity
```

#### 2. Error de conexión SSH
```bash
# Verificar key pair
chmod 600 ~/.ssh/your-key.pem

# Conectar manualmente
ssh -i ~/.ssh/your-key.pem ubuntu@IP-PUBLICA
```

#### 3. Error de Docker
```bash
# En la instancia EC2
sudo systemctl status docker
sudo docker-compose down
sudo docker-compose up -d
```

#### 4. Error de NGINX
```bash
# Verificar configuración
sudo nginx -t

# Ver logs
sudo tail -f /var/log/nginx/error.log
```

### Logs Importantes
- **Docker**: `docker-compose logs -f`
- **NGINX**: `/var/log/nginx/`
- **Aplicación**: `storage/logs/laravel.log`
- **Sistema**: `/var/log/syslog`

## 📈 Optimización

### Rendimiento
- Contenedores Docker optimizados
- Caché de configuración Laravel
- Compresión gzip
- Headers de caché para archivos estáticos

### Escalabilidad
- Configuración para múltiples instancias
- Load balancer preparado
- Auto-scaling groups
- Base de datos externa (RDS)

## 💰 Costos Estimados

### Instancia t2.micro (1 año)
- **EC2**: ~$96/año
- **EBS Storage**: ~$12/año
- **Data Transfer**: ~$10/año
- **Total**: ~$118/año

### Instancia t3.small (1 año)
- **EC2**: ~$180/año
- **EBS Storage**: ~$12/año
- **Data Transfer**: ~$10/año
- **Total**: ~$202/año

## 🔄 Actualizaciones

### Actualizar la aplicación
```bash
# En la instancia EC2
cd /opt/landom-api
git pull origin main
docker-compose down
docker-compose build
docker-compose up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan config:cache
```

### Actualizar dependencias del sistema
```bash
sudo apt-get update && sudo apt-get upgrade -y
sudo systemctl restart docker
docker-compose restart
```

## 📞 Soporte

Si encuentras problemas:

1. Revisar logs de Docker
2. Verificar configuración de AWS
3. Comprobar conectividad SSH
4. Verificar permisos de archivos

### Información de Contacto
- **Repositorio**: https://github.com/jorgecordobam/landom_api
- **Autor**: Jorge Cordoba

## 📝 Notas Importantes

1. **Costos**: Monitorea el uso de AWS para evitar costos inesperados
2. **Backup**: Configura backups automáticos en S3
3. **SSL**: Configura SSL para producción
4. **Monitoreo**: Usa CloudWatch para monitoreo avanzado
5. **Seguridad**: Mantén las instancias actualizadas

## 🎯 Próximos Pasos

1. **Configurar dominio** y DNS
2. **Habilitar SSL** con Let's Encrypt
3. **Configurar backups** en S3
4. **Configurar monitoreo** con CloudWatch
5. **Optimizar rendimiento** según necesidades

---

**¡Tu aplicación Laravel está lista para producción en AWS EC2!** 🚀 