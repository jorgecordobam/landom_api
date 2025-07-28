# Script simple para verificar el estado del despliegue
# Email: nuovaiapps@gmail.com

Write-Host "=================================" -ForegroundColor Blue
Write-Host "VERIFICACION SIMPLE - LANDOM API" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

# Verificar archivos de despliegue
Write-Host ""
Write-Host "ARCHIVOS DE DESPLIEGUE:" -ForegroundColor Yellow
$files = @("deploy.sh", "deploy-windows.ps1", "ec2-config-personalized.sh")
foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "  ✅ $file" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $file" -ForegroundColor Red
    }
}

# Verificar archivos Docker
Write-Host ""
Write-Host "ARCHIVOS DOCKER:" -ForegroundColor Yellow
$dockerFiles = @("docker-compose.yml", "Dockerfile", "docker/nginx.conf", "docker/php.ini")
foreach ($file in $dockerFiles) {
    if (Test-Path $file) {
        Write-Host "  ✅ $file" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $file" -ForegroundColor Red
    }
}

# Verificar dominio
Write-Host ""
Write-Host "VERIFICANDO DOMINIO:" -ForegroundColor Yellow
$domain = "landom-api.us-west-2.elasticbeanstalk.com"

try {
    $response = Invoke-WebRequest -Uri "http://$domain" -TimeoutSec 5 -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        Write-Host "  ✅ HTTP funciona: http://$domain" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  HTTP responde con código: $($response.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "  ❌ HTTP no responde: http://$domain" -ForegroundColor Red
}

try {
    $response = Invoke-WebRequest -Uri "https://$domain" -TimeoutSec 5 -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        Write-Host "  ✅ HTTPS funciona: https://$domain" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  HTTPS responde con código: $($response.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "  ❌ HTTPS no responde: https://$domain" -ForegroundColor Red
}

Write-Host ""
Write-Host "=================================" -ForegroundColor Blue
Write-Host "ESTADO ACTUAL" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Configuracion:" -ForegroundColor Yellow
Write-Host "  Region: us-west-2" -ForegroundColor Cyan
Write-Host "  Domain: $domain" -ForegroundColor Cyan
Write-Host "  Email: nuovaiapps@gmail.com" -ForegroundColor Cyan

Write-Host ""
Write-Host "Para completar el despliegue:" -ForegroundColor Yellow
Write-Host "1. Espera a que AWS CLI termine de instalarse" -ForegroundColor Cyan
Write-Host "2. Reinicia PowerShell" -ForegroundColor Cyan
Write-Host "3. Ejecuta: aws configure" -ForegroundColor Cyan
Write-Host "4. Ejecuta: .\deploy-windows.ps1" -ForegroundColor Cyan

Write-Host ""
Write-Host "Tu API estara disponible en:" -ForegroundColor Green
Write-Host "  https://$domain" -ForegroundColor Cyan 