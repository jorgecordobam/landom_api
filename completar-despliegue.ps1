# Script para completar el despliegue de Landom API
# Email: nuovaiapps@gmail.com

Write-Host "=================================" -ForegroundColor Blue
Write-Host "COMPLETANDO DESPLIEGUE - LANDOM API" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Verificando estado actual..." -ForegroundColor Yellow

# Verificar AWS CLI
Write-Host ""
Write-Host "1. VERIFICANDO AWS CLI" -ForegroundColor Yellow
if (Get-Command aws -ErrorAction SilentlyContinue) {
    Write-Host "  ✅ AWS CLI está instalado" -ForegroundColor Green
    $awsVersion = aws --version
    Write-Host "  Versión: $awsVersion" -ForegroundColor Cyan
} else {
    Write-Host "  ❌ AWS CLI no está instalado" -ForegroundColor Red
    Write-Host "  Por favor reinicia PowerShell y ejecuta: aws --version" -ForegroundColor Yellow
    exit 1
}

# Verificar configuración AWS
Write-Host ""
Write-Host "2. VERIFICANDO CONFIGURACION AWS" -ForegroundColor Yellow
try {
    $result = aws sts get-caller-identity 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✅ AWS CLI configurado correctamente" -ForegroundColor Green
        Write-Host "  Cuenta: $result" -ForegroundColor Cyan
    } else {
        Write-Host "  ❌ AWS CLI no está configurado" -ForegroundColor Red
        Write-Host "  Ejecuta: aws configure" -ForegroundColor Yellow
        Write-Host "  Ve a AWS Console y crea un Access Key" -ForegroundColor Yellow
        exit 1
    }
} catch {
    Write-Host "  ❌ Error al verificar configuración AWS" -ForegroundColor Red
    exit 1
}

# Verificar archivos
Write-Host ""
Write-Host "3. VERIFICANDO ARCHIVOS" -ForegroundColor Yellow
$files = @("deploy-windows.ps1", "docker-compose.yml", "Dockerfile", "docker/nginx.conf", "docker/php.ini", "docker/supervisord.conf")
$allFilesExist = $true

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "  ✅ $file" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $file" -ForegroundColor Red
        $allFilesExist = $false
    }
}

if (-not $allFilesExist) {
    Write-Host "  ❌ Faltan archivos necesarios" -ForegroundColor Red
    exit 1
}

Write-Host "  ✅ Todos los archivos están listos" -ForegroundColor Green

# Preguntar confirmación
Write-Host ""
Write-Host "=================================" -ForegroundColor Blue
Write-Host "CONFIGURACION FINAL" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Region: us-west-2" -ForegroundColor Cyan
Write-Host "Domain: landom-api.us-west-2.elasticbeanstalk.com" -ForegroundColor Cyan
Write-Host "Email: nuovaiapps@gmail.com" -ForegroundColor Cyan
Write-Host "Costo estimado: ~$10/mes" -ForegroundColor Cyan

Write-Host ""
$response = Read-Host "¿Quieres continuar con el despliegue? (y/n)"
if ($response -ne "y" -and $response -ne "Y") {
    Write-Host "Despliegue cancelado" -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "🚀 INICIANDO DESPLIEGUE..." -ForegroundColor Green
Write-Host "Este proceso puede tomar 10-15 minutos..." -ForegroundColor Yellow

# Ejecutar despliegue
try {
    .\deploy-windows.ps1
} catch {
    Write-Host "❌ Error durante el despliegue" -ForegroundColor Red
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
Write-Host "Comandos utiles:" -ForegroundColor Yellow
Write-Host "  Ver logs: docker-compose logs -f" -ForegroundColor Cyan
Write-Host "  Reiniciar: docker-compose restart" -ForegroundColor Cyan
Write-Host "  Backup: ./backup-script.sh" -ForegroundColor Cyan

Write-Host ""
Write-Host "IMPORTANTE:" -ForegroundColor Yellow
Write-Host "  - Monitorea los costos en AWS Console" -ForegroundColor Red
Write-Host "  - Configura alertas de CloudWatch" -ForegroundColor Red
Write-Host "  - Haz backups regulares" -ForegroundColor Red

Write-Host ""
Write-Host "Tu aplicacion esta lista para produccion!" -ForegroundColor Green 