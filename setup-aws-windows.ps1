# Script para configurar AWS CLI en Windows
# Email: nuovaiapps@gmail.com
# Autor: Jorge Cordoba

Write-Host "=================================" -ForegroundColor Blue
Write-Host "CONFIGURACION DE AWS CLI EN WINDOWS" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

# Verificar si AWS CLI esta instalado
if (Get-Command aws -ErrorAction SilentlyContinue) {
    Write-Host "AWS CLI ya esta instalado" -ForegroundColor Green
} else {
    Write-Host "AWS CLI no esta instalado. Instalando..." -ForegroundColor Red
    
    # Descargar e instalar AWS CLI
    Write-Host "Descargando AWS CLI..." -ForegroundColor Yellow
    $awsCliUrl = "https://awscli.amazonaws.com/AWSCLIV2.msi"
    $installerPath = "$env:TEMP\AWSCLIV2.msi"
    
    try {
        Invoke-WebRequest -Uri $awsCliUrl -OutFile $installerPath
        Write-Host "Instalando AWS CLI..." -ForegroundColor Yellow
        Start-Process msiexec.exe -Wait -ArgumentList "/i $installerPath /quiet"
        Remove-Item $installerPath -Force
        Write-Host "AWS CLI instalado correctamente" -ForegroundColor Green
    } catch {
        Write-Host "Error al instalar AWS CLI" -ForegroundColor Red
        Write-Host "Por favor instala manualmente desde: https://aws.amazon.com/cli/" -ForegroundColor Yellow
        exit 1
    }
}

# Crear directorio de configuracion AWS
$awsConfigDir = "$env:USERPROFILE\.aws"
if (!(Test-Path $awsConfigDir)) {
    New-Item -ItemType Directory -Path $awsConfigDir -Force
}

# Crear archivo de configuracion AWS
$awsCredentialsPath = "$awsConfigDir\credentials"
$awsConfigPath = "$awsConfigDir\config"

# Crear archivo credentials
@"
[default]
aws_access_key_id = YOUR_ACCESS_KEY_ID
aws_secret_access_key = YOUR_SECRET_ACCESS_KEY
"@ | Out-File -FilePath $awsCredentialsPath -Encoding UTF8

# Crear archivo config
@"
[default]
region = us-west-2
output = json
"@ | Out-File -FilePath $awsConfigPath -Encoding UTF8

Write-Host "Archivos de configuracion AWS creados" -ForegroundColor Green
Write-Host "IMPORTANTE: Necesitas obtener tus Access Key ID y Secret Access Key" -ForegroundColor Yellow
Write-Host "1. Ve a AWS Console: https://console.aws.amazon.com/" -ForegroundColor Yellow
Write-Host "2. Inicia sesion con: nuovaiapps@gmail.com" -ForegroundColor Yellow
Write-Host "3. Ve a IAM - Users - Tu usuario - Security credentials" -ForegroundColor Yellow
Write-Host "4. Crea un Access Key y reemplaza en $awsCredentialsPath" -ForegroundColor Yellow

# Verificar configuracion
Write-Host "Verificando configuracion..." -ForegroundColor Yellow
try {
    $result = aws sts get-caller-identity 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "AWS CLI configurado correctamente" -ForegroundColor Green
        Write-Host $result -ForegroundColor Cyan
    } else {
        Write-Host "AWS CLI no esta configurado correctamente" -ForegroundColor Red
        Write-Host "Por favor configura tus credenciales en $awsCredentialsPath" -ForegroundColor Yellow
    }
} catch {
    Write-Host "Error al verificar configuracion AWS" -ForegroundColor Red
}

Write-Host "=================================" -ForegroundColor Blue
Write-Host "PROXIMOS PASOS" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "1. Configura tus credenciales AWS en $awsCredentialsPath" -ForegroundColor Green
Write-Host "2. Ejecuta: .\quick-start-windows.ps1" -ForegroundColor Green

Write-Host "Configuracion inicial completada!" -ForegroundColor Green 