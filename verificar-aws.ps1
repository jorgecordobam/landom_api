# Script para verificar AWS CLI
# Email: nuovaiapps@gmail.com

Write-Host "=================================" -ForegroundColor Blue
Write-Host "VERIFICANDO AWS CLI" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

# Verificar si AWS CLI está disponible
if (Get-Command aws -ErrorAction SilentlyContinue) {
    Write-Host "AWS CLI está instalado y funcionando!" -ForegroundColor Green
    $awsVersion = aws --version
    Write-Host "Versión: $awsVersion" -ForegroundColor Cyan
    
    Write-Host ""
    Write-Host "=================================" -ForegroundColor Blue
    Write-Host "PROXIMOS PASOS" -ForegroundColor Blue
    Write-Host "=================================" -ForegroundColor Blue
    
    Write-Host "1. Configura tus credenciales AWS:" -ForegroundColor Yellow
    Write-Host "   aws configure" -ForegroundColor Cyan
    
    Write-Host "2. Verifica la configuración:" -ForegroundColor Yellow
    Write-Host "   aws sts get-caller-identity" -ForegroundColor Cyan
    
    Write-Host "3. Ejecuta el despliegue:" -ForegroundColor Yellow
    Write-Host "   .\pasos-despues-reinicio.ps1" -ForegroundColor Cyan
    
} else {
    Write-Host "AWS CLI no está instalado o no está en el PATH" -ForegroundColor Red
    Write-Host ""
    Write-Host "Para instalar AWS CLI:" -ForegroundColor Yellow
    Write-Host "1. Ve a: https://aws.amazon.com/cli/" -ForegroundColor Cyan
    Write-Host "2. Descarga el instalador para Windows" -ForegroundColor Cyan
    Write-Host "3. Ejecuta el archivo .msi descargado" -ForegroundColor Cyan
    Write-Host "4. Reinicia PowerShell" -ForegroundColor Cyan
    Write-Host "5. Ejecuta este script nuevamente" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "¡Listo!" -ForegroundColor Green 