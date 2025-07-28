# Script para completar el despliegue después de reiniciar PowerShell
# Email: nuovaiapps@gmail.com

Write-Host "=================================" -ForegroundColor Blue
Write-Host "DESPUES DE REINICIAR POWERSHELL" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "AWS CLI ya deberia estar instalado" -ForegroundColor Green
Write-Host "Verificando instalacion..." -ForegroundColor Yellow

# Verificar AWS CLI
if (Get-Command aws -ErrorAction SilentlyContinue) {
    Write-Host "AWS CLI instalado correctamente!" -ForegroundColor Green
    $awsVersion = aws --version
    Write-Host "Version: $awsVersion" -ForegroundColor Cyan
} else {
    Write-Host "AWS CLI no esta disponible" -ForegroundColor Red
    Write-Host "Por favor reinicia PowerShell nuevamente" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "=================================" -ForegroundColor Blue
Write-Host "CONFIGURANDO CREDENCIALES AWS" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Ahora necesitas configurar tus credenciales AWS:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Ve a AWS Console: https://console.aws.amazon.com/" -ForegroundColor Cyan
Write-Host "2. Inicia sesion con: nuovaiapps@gmail.com" -ForegroundColor Cyan
Write-Host "3. Ve a IAM - Users - Tu usuario - Security credentials" -ForegroundColor Cyan
Write-Host "4. Crea un Access Key" -ForegroundColor Cyan
Write-Host "5. Copia el Access Key ID y Secret Access Key" -ForegroundColor Cyan

Write-Host ""
$response = Read-Host "¿Ya tienes tus credenciales AWS? (y/n)"
if ($response -eq "y" -or $response -eq "Y") {
    Write-Host "Ejecutando configuracion AWS..." -ForegroundColor Yellow
    aws configure
} else {
    Write-Host "Por favor obtén tus credenciales primero" -ForegroundColor Yellow
    Write-Host "Luego ejecuta: aws configure" -ForegroundColor Cyan
    exit 1
}

Write-Host ""
Write-Host "Verificando configuracion..." -ForegroundColor Yellow
try {
    $result = aws sts get-caller-identity 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "AWS CLI configurado correctamente!" -ForegroundColor Green
        Write-Host $result -ForegroundColor Cyan
    } else {
        Write-Host "Error en la configuracion AWS" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "Error al verificar configuracion AWS" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "=================================" -ForegroundColor Blue
Write-Host "EJECUTANDO DESPLIEGUE" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Todo listo! Ejecutando despliegue..." -ForegroundColor Green
Write-Host "Este proceso puede tomar 10-15 minutos..." -ForegroundColor Yellow

try {
    .\completar-despliegue.ps1
} catch {
    Write-Host "Error durante el despliegue" -ForegroundColor Red
    Write-Host "Revisa los logs y ejecuta nuevamente" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "=================================" -ForegroundColor Blue
Write-Host "DESPLIEGUE COMPLETADO" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Tu API Laravel esta desplegada!" -ForegroundColor Green
Write-Host "URL: https://landom-api.us-west-2.elasticbeanstalk.com" -ForegroundColor Cyan
Write-Host "SSL: Configurado automaticamente" -ForegroundColor Cyan
Write-Host "Email: nuovaiapps@gmail.com" -ForegroundColor Cyan

Write-Host ""
Write-Host "Tu aplicacion esta lista para produccion!" -ForegroundColor Green 