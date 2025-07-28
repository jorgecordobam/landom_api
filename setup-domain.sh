#!/bin/bash

# Script para configurar dominio y DNS para Landom API
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

print_header "🌐 CONFIGURACIÓN DE DOMINIO Y DNS"

# Variables
DOMAIN="landom-api.us-west-2.elasticbeanstalk.com"
EMAIL="nuovaiapps@gmail.com"
AWS_REGION="us-west-2"

print_message "Configurando dominio: $DOMAIN"
print_message "Email: $EMAIL"
print_message "Región AWS: $AWS_REGION"

# Verificar si AWS CLI está configurado
if ! aws sts get-caller-identity &> /dev/null; then
    print_error "AWS CLI no está configurado. Ejecuta setup-aws.sh primero."
    exit 1
fi

print_message "✅ AWS CLI configurado correctamente"

# Obtener IP pública de la instancia EC2
print_header "OBTENIENDO IP PÚBLICA"

# Buscar instancias EC2 con el tag Name que contenga "landom"
INSTANCE_ID=$(aws ec2 describe-instances \
    --region $AWS_REGION \
    --filters "Name=tag:Name,Values=*landom*" "Name=instance-state-name,Values=running" \
    --query 'Reservations[0].Instances[0].InstanceId' \
    --output text)

if [ "$INSTANCE_ID" == "None" ] || [ -z "$INSTANCE_ID" ]; then
    print_error "No se encontró una instancia EC2 para Landom API"
    print_warning "Asegúrate de que la instancia esté ejecutándose y tenga el tag Name con 'landom'"
    exit 1
fi

print_message "Instancia encontrada: $INSTANCE_ID"

# Obtener IP pública
PUBLIC_IP=$(aws ec2 describe-instances \
    --region $AWS_REGION \
    --instance-ids $INSTANCE_ID \
    --query 'Reservations[0].Instances[0].PublicIpAddress' \
    --output text)

print_message "IP pública: $PUBLIC_IP"

# Configurar Route 53 (si está disponible)
print_header "CONFIGURANDO DNS"

# Verificar si hay hosted zones en Route 53
HOSTED_ZONES=$(aws route53 list-hosted-zones --query 'HostedZones[0].Id' --output text 2>/dev/null || echo "")

if [ -n "$HOSTED_ZONES" ] && [ "$HOSTED_ZONES" != "None" ]; then
    print_message "Configurando DNS en Route 53..."
    
    # Crear registro A
    cat > dns-record.json << EOF
{
    "Changes": [
        {
            "Action": "UPSERT",
            "ResourceRecordSet": {
                "Name": "$DOMAIN",
                "Type": "A",
                "TTL": 300,
                "ResourceRecords": [
                    {
                        "Value": "$PUBLIC_IP"
                    }
                ]
            }
        }
    ]
}
EOF
    
    # Aplicar cambios DNS
    aws route53 change-resource-record-sets \
        --hosted-zone-id $HOSTED_ZONES \
        --change-batch file://dns-record.json
    
    print_message "✅ DNS configurado en Route 53"
    rm -f dns-record.json
else
    print_warning "No se encontró Route 53 configurado"
    print_message "Configuración manual de DNS requerida:"
    print_message "   Tipo: A"
    print_message "   Nombre: $DOMAIN"
    print_message "   Valor: $PUBLIC_IP"
    print_message "   TTL: 300"
fi

# Configurar certificado SSL
print_header "CONFIGURANDO SSL"

print_message "Instalando Certbot..."
ssh -i ~/.ssh/landom-key-pair.pem ubuntu@$PUBLIC_IP << 'EOF'
sudo apt-get update
sudo apt-get install -y certbot python3-certbot-nginx
EOF

print_message "Configurando certificado SSL..."
ssh -i ~/.ssh/landom-key-pair.pem ubuntu@$PUBLIC_IP "sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email $EMAIL"

print_message "Configurando renovación automática..."
ssh -i ~/.ssh/landom-key-pair.pem ubuntu@$PUBLIC_IP "sudo crontab -l 2>/dev/null | { cat; echo \"0 12 * * * /usr/bin/certbot renew --quiet\"; } | sudo crontab -"

print_header "✅ CONFIGURACIÓN COMPLETADA"

print_message "🌐 Dominio configurado: $DOMAIN"
print_message "🔒 SSL habilitado: https://$DOMAIN"
print_message "📧 Email de contacto: $EMAIL"
print_message "🌍 Región AWS: $AWS_REGION"

print_message ""
print_message "📋 Comandos útiles:"
print_message "   🔍 Ver logs: ssh -i ~/.ssh/landom-key-pair.pem ubuntu@$PUBLIC_IP 'docker-compose logs -f'"
print_message "   🔄 Reiniciar: ssh -i ~/.ssh/landom-key-pair.pem ubuntu@$PUBLIC_IP 'docker-compose restart'"
print_message "   💾 Backup: ssh -i ~/.ssh/landom-key-pair.pem ubuntu@$PUBLIC_IP './backup-script.sh'"

print_message ""
print_message "🎯 Tu API está lista en: https://$DOMAIN" 