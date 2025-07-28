#!/bin/bash

# Script de inicio rápido para despliegue de Landom API en EC2
# Email: nuovaiapps@gmail.com
# Autor: Jorge Cordoba

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

print_header "🚀 INICIO RÁPIDO - LANDOM API EN EC2"

print_message "Email configurado: nuovaiapps@gmail.com"
print_message "Repositorio: https://github.com/jorgecordobam/landom_api"

# Verificar que estamos en el directorio correcto
if [ ! -f "deploy.sh" ]; then
    print_error "Este script debe ejecutarse desde el directorio raíz del proyecto"
    exit 1
fi

# Paso 1: Configurar AWS CLI
print_header "PASO 1: CONFIGURANDO AWS CLI"

if ! command -v aws &> /dev/null; then
    print_error "AWS CLI no está instalado. Ejecutando setup..."
    chmod +x setup-aws.sh
    ./setup-aws.sh
else
    print_message "AWS CLI ya está instalado"
fi

# Paso 2: Verificar configuración AWS
print_header "PASO 2: VERIFICANDO CONFIGURACIÓN AWS"

if aws sts get-caller-identity &> /dev/null; then
    print_message "✅ AWS CLI configurado correctamente"
    aws sts get-caller-identity
else
    print_error "❌ AWS CLI no está configurado"
    print_warning "Por favor ejecuta: ./setup-aws.sh"
    exit 1
fi

# Paso 3: Cargar configuración personalizada
print_header "PASO 3: CARGANDO CONFIGURACIÓN"

if [ -f "ec2-config-personalized.sh" ]; then
    source ec2-config-personalized.sh
    print_message "✅ Configuración personalizada cargada"
else
    print_error "❌ Archivo de configuración no encontrado"
    exit 1
fi

# Paso 4: Verificar variables críticas
print_header "PASO 4: VERIFICANDO CONFIGURACIÓN"

echo "📋 Configuración actual:"
echo "   🌍 Región AWS: $AWS_REGION"
echo "   🔑 Key Pair: $KEY_PAIR_NAME"
echo "   🖥️  Tipo de instancia: $EC2_INSTANCE_TYPE"
echo "   🌐 Dominio: $DOMAIN"
echo "   📧 Email SSL: $SSL_EMAIL"

# Preguntar si quiere continuar
read -p "¿Quieres continuar con esta configuración? (y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Por favor edita ec2-config-personalized.sh y ejecuta este script nuevamente"
    exit 1
fi

# Paso 5: Dar permisos de ejecución
print_header "PASO 5: PREPARANDO SCRIPTS"

chmod +x deploy.sh
chmod +x ec2-config.sh
chmod +x ec2-config-personalized.sh

print_message "✅ Scripts con permisos de ejecución"

# Paso 6: Ejecutar despliegue
print_header "PASO 6: INICIANDO DESPLIEGUE"

print_message "🚀 Iniciando despliegue de Landom API en EC2..."
print_message "⏱️  Este proceso puede tomar 10-15 minutos..."

./deploy.sh

# Paso 7: Configurar dominio y SSL
print_header "PASO 7: CONFIGURANDO DOMINIO Y SSL"

print_message "Configurando dominio y SSL..."
chmod +x setup-domain.sh
./setup-domain.sh

print_header "🎉 DESPLIEGUE COMPLETADO"

print_message "✅ Tu aplicación Laravel está desplegada en EC2!"
print_message "🌐 URL: https://$DOMAIN"
print_message "🔒 SSL: Configurado automáticamente"
print_message "📧 Email de contacto: nuovaiapps@gmail.com"

print_message ""
print_message "📋 Comandos útiles:"
print_message "   🔍 Ver logs: ssh -i ~/.ssh/$KEY_PAIR_NAME.pem ubuntu@IP 'docker-compose logs -f'"
print_message "   🔄 Reiniciar: ssh -i ~/.ssh/$KEY_PAIR_NAME.pem ubuntu@IP 'docker-compose restart'"
print_message "   💾 Backup: ssh -i ~/.ssh/$KEY_PAIR_NAME.pem ubuntu@IP './backup-script.sh'"

print_message ""
print_message "📞 Soporte:"
print_message "   📧 Email: nuovaiapps@gmail.com"
print_message "   📚 Documentación: README-DEPLOY-EC2.md"

print_message ""
print_warning "⚠️  IMPORTANTE:"
print_warning "   - Monitorea los costos en AWS Console"
print_warning "   - Configura alertas de CloudWatch"
print_warning "   - Haz backups regulares"
print_warning "   - Mantén la instancia actualizada"

print_message "🎯 ¡Tu aplicación está lista para producción!" 