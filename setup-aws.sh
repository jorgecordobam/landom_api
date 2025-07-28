#!/bin/bash

# Script para configurar AWS CLI con las credenciales proporcionadas
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

print_header "CONFIGURACIÓN DE AWS CLI"

# Verificar si AWS CLI está instalado
if ! command -v aws &> /dev/null; then
    print_error "AWS CLI no está instalado. Instalando..."
    
    # Detectar sistema operativo
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        # Linux
        print_message "Instalando AWS CLI en Linux..."
        curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
        unzip awscliv2.zip
        sudo ./aws/install
        rm -rf aws awscliv2.zip
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        print_message "Instalando AWS CLI en macOS..."
        curl "https://awscli.amazonaws.com/AWSCLIV2.pkg" -o "AWSCLIV2.pkg"
        sudo installer -pkg AWSCLIV2.pkg -target /
        rm AWSCLIV2.pkg
    elif [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "cygwin" ]]; then
        # Windows
        print_message "Por favor instala AWS CLI manualmente en Windows desde: https://aws.amazon.com/cli/"
        exit 1
    fi
fi

print_message "AWS CLI instalado correctamente"

# Configurar AWS CLI
print_message "Configurando AWS CLI..."

# Crear archivo de configuración AWS
mkdir -p ~/.aws

cat > ~/.aws/credentials << 'EOF'
[default]
aws_access_key_id = YOUR_ACCESS_KEY_ID
aws_secret_access_key = YOUR_SECRET_ACCESS_KEY
EOF

cat > ~/.aws/config << 'EOF'
[default]
region = us-east-1
output = json
EOF

print_message "Archivos de configuración AWS creados"
print_warning "IMPORTANTE: Necesitas obtener tus Access Key ID y Secret Access Key"
print_warning "1. Ve a AWS Console: https://console.aws.amazon.com/"
print_warning "2. Inicia sesión con: nuovaiapps@gmail.com"
print_warning "3. Ve a IAM > Users > Tu usuario > Security credentials"
print_warning "4. Crea un Access Key y reemplaza en ~/.aws/credentials"

# Verificar configuración
print_message "Verificando configuración..."
if aws sts get-caller-identity &> /dev/null; then
    print_message "✅ AWS CLI configurado correctamente"
    aws sts get-caller-identity
else
    print_error "❌ AWS CLI no está configurado correctamente"
    print_warning "Por favor configura tus credenciales en ~/.aws/credentials"
fi

print_header "PRÓXIMOS PASOS"

print_message "1. Configura tus credenciales AWS en ~/.aws/credentials"
print_message "2. Edita las variables en ec2-config.sh"
print_message "3. Ejecuta: source ec2-config.sh && ./deploy.sh"

print_message "Configuración inicial completada!" 