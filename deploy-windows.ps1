# Script de despliegue para Landom API en EC2 (Windows)
# Email: nuovaiapps@gmail.com
# Autor: Jorge Cordoba

Write-Host "=================================" -ForegroundColor Blue
Write-Host "DESPLIEGUE DE LANDOM API EN EC2" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

# Verificar AWS CLI
if (!(Get-Command aws -ErrorAction SilentlyContinue)) {
    Write-Host "AWS CLI no esta instalado. Ejecutando setup..." -ForegroundColor Red
    .\setup-aws-windows.ps1
    exit 1
}

# Verificar configuracion AWS
try {
    $result = aws sts get-caller-identity 2>$null
    if ($LASTEXITCODE -ne 0) {
        Write-Host "AWS CLI no esta configurado correctamente" -ForegroundColor Red
        Write-Host "Por favor ejecuta: .\setup-aws-windows.ps1" -ForegroundColor Yellow
        exit 1
    }
    Write-Host "AWS CLI configurado correctamente" -ForegroundColor Green
    Write-Host $result -ForegroundColor Cyan
} catch {
    Write-Host "Error al verificar configuracion AWS" -ForegroundColor Red
    exit 1
}

# Variables de configuracion
$AWS_REGION = "us-east-2"
$KEY_PAIR_NAME = "landom-api-key-2"
$INSTANCE_TYPE = "t2.micro"
$DOMAIN = "landom-api.us-east-2.elasticbeanstalk.com"

Write-Host "Configuracion:" -ForegroundColor Yellow
Write-Host "  Region: $AWS_REGION" -ForegroundColor Cyan
Write-Host "  Key Pair: $KEY_PAIR_NAME" -ForegroundColor Cyan
Write-Host "  Instance Type: $INSTANCE_TYPE" -ForegroundColor Cyan
Write-Host "  Domain: $DOMAIN" -ForegroundColor Cyan

# Preguntar confirmacion
$response = Read-Host "¿Continuar con el despliegue? (y/n)"
if ($response -ne "y" -and $response -ne "Y") {
    Write-Host "Despliegue cancelado" -ForegroundColor Yellow
    exit 0
}

Write-Host "Iniciando despliegue..." -ForegroundColor Green

# Paso 1: Crear key pair
Write-Host "Paso 1: Creando key pair..." -ForegroundColor Yellow
try {
    aws ec2 create-key-pair --key-name $KEY_PAIR_NAME --region $AWS_REGION --query 'KeyMaterial' --output text > "$KEY_PAIR_NAME.pem"
    Write-Host "Key pair creado: $KEY_PAIR_NAME.pem" -ForegroundColor Green
} catch {
    Write-Host "Error al crear key pair" -ForegroundColor Red
    exit 1
}

# Paso 2: Crear security group
Write-Host "Paso 2: Creando security group..." -ForegroundColor Yellow
try {
    $SG_ID = aws ec2 create-security-group --group-name "landom-api-sg" --description "Security group for Landom API" --region $AWS_REGION --query 'GroupId' --output text
    Write-Host "Security group creado: $SG_ID" -ForegroundColor Green
    
    # Configurar reglas
    aws ec2 authorize-security-group-ingress --group-id $SG_ID --protocol tcp --port 22 --cidr 0.0.0.0/0 --region $AWS_REGION
    aws ec2 authorize-security-group-ingress --group-id $SG_ID --protocol tcp --port 80 --cidr 0.0.0.0/0 --region $AWS_REGION
    aws ec2 authorize-security-group-ingress --group-id $SG_ID --protocol tcp --port 443 --cidr 0.0.0.0/0 --region $AWS_REGION
    Write-Host "Reglas de security group configuradas" -ForegroundColor Green
} catch {
    Write-Host "Error al crear security group" -ForegroundColor Red
    exit 1
}

# Paso 3: Crear instancia EC2
Write-Host "Paso 3: Creando instancia EC2..." -ForegroundColor Yellow
try {
    # Ubuntu Server 22.04 LTS (HVM), SSD Volume Type, us-east-2: ami-0e83be366243f524a (julio 2025)
    $INSTANCE_ID = aws ec2 run-instances --image-id ami-0e83be366243f524a --count 1 --instance-type $INSTANCE_TYPE --key-name $KEY_PAIR_NAME --security-group-ids $SG_ID --region $AWS_REGION --tag-specifications "ResourceType=instance,Tags=[{Key=Name,Value=landom-api}]" --query 'Instances[0].InstanceId' --output text
    Write-Host "Instancia creada: $INSTANCE_ID" -ForegroundColor Green
    
    # Esperar a que la instancia este running
    Write-Host "Esperando a que la instancia este lista..." -ForegroundColor Yellow
    aws ec2 wait instance-running --instance-ids $INSTANCE_ID --region $AWS_REGION
    Write-Host "Instancia lista" -ForegroundColor Green
} catch {
    Write-Host "Error al crear instancia EC2" -ForegroundColor Red
    exit 1
}

# Paso 4: Obtener IP publica
Write-Host "Paso 4: Obteniendo IP publica..." -ForegroundColor Yellow
try {
    $PUBLIC_IP = aws ec2 describe-instances --instance-ids $INSTANCE_ID --region $AWS_REGION --query 'Reservations[0].Instances[0].PublicIpAddress' --output text
    Write-Host "IP publica: $PUBLIC_IP" -ForegroundColor Green
} catch {
    Write-Host "Error al obtener IP publica" -ForegroundColor Red
    exit 1
}

# Paso 5: Configurar servidor
Write-Host "Paso 5: Configurando servidor..." -ForegroundColor Yellow
Write-Host "Esperando 60 segundos para que el servidor este completamente listo..." -ForegroundColor Yellow
Start-Sleep -Seconds 60

# Crear script de configuracion del servidor
$serverScript = @"
#!/bin/bash
# Actualizar sistema
sudo apt-get update
sudo apt-get upgrade -y

# Instalar Docker
sudo apt-get install -y apt-transport-https ca-certificates curl gnupg lsb-release
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu `$(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Agregar usuario ubuntu al grupo docker
sudo usermod -aG docker ubuntu

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-`$(uname -s)-`$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Crear directorio para la aplicacion
sudo mkdir -p /opt/landom-api
sudo chown ubuntu:ubuntu /opt/landom-api

echo "Configuracion del servidor completada"
"@

$serverScript | Out-File -FilePath "server-setup.sh" -Encoding UTF8

Write-Host "Script de configuracion del servidor creado" -ForegroundColor Green

# Paso 6: Copiar archivos al servidor
Write-Host "Paso 6: Copiando archivos al servidor..." -ForegroundColor Yellow
Write-Host "NOTA: Este paso requiere que tengas SSH configurado" -ForegroundColor Yellow
Write-Host "IP del servidor: $PUBLIC_IP" -ForegroundColor Cyan
Write-Host "Key file: $KEY_PAIR_NAME.pem" -ForegroundColor Cyan

Write-Host "=================================" -ForegroundColor Blue
Write-Host "DESPLIEGUE INICIADO" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Instancia EC2 creada: $INSTANCE_ID" -ForegroundColor Green
Write-Host "IP publica: $PUBLIC_IP" -ForegroundColor Green
Write-Host "Key pair: $KEY_PAIR_NAME.pem" -ForegroundColor Green
Write-Host "Region: $AWS_REGION" -ForegroundColor Green

Write-Host ""
Write-Host "Proximos pasos manuales:" -ForegroundColor Yellow
Write-Host "1. Conectate al servidor: ssh -i $KEY_PAIR_NAME.pem ubuntu@$PUBLIC_IP" -ForegroundColor Cyan
Write-Host "2. Ejecuta el script de configuracion: ./server-setup.sh" -ForegroundColor Cyan
Write-Host "3. Clona el repositorio: git clone https://github.com/jorgecordobam/landom_api.git" -ForegroundColor Cyan
Write-Host "4. Configura la aplicacion Laravel" -ForegroundColor Cyan

Write-Host ""
Write-Host "Tu servidor EC2 esta listo!" -ForegroundColor Green 