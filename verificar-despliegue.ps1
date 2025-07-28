# Script para verificar el estado del despliegue de Landom API
# Email: nuovaiapps@gmail.com

Write-Host "=================================" -ForegroundColor Blue
Write-Host "VERIFICACION DE DESPLIEGUE" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Verificando estado del despliegue..." -ForegroundColor Yellow

# 1. Verificar si AWS CLI está instalado
Write-Host ""
Write-Host "1. VERIFICANDO AWS CLI" -ForegroundColor Yellow
if (Get-Command aws -ErrorAction SilentlyContinue) {
    Write-Host "✅ AWS CLI está instalado" -ForegroundColor Green
    $awsVersion = aws --version
    Write-Host "   Versión: $awsVersion" -ForegroundColor Cyan
} else {
    Write-Host "❌ AWS CLI no está instalado" -ForegroundColor Red
    Write-Host "   Necesitas reiniciar PowerShell después de la instalación" -ForegroundColor Yellow
}

# 2. Verificar configuración AWS
Write-Host ""
Write-Host "2. VERIFICANDO CONFIGURACION AWS" -ForegroundColor Yellow
try {
    $result = aws sts get-caller-identity 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ AWS CLI configurado correctamente" -ForegroundColor Green
        Write-Host "   Cuenta: $result" -ForegroundColor Cyan
    } else {
        Write-Host "❌ AWS CLI no está configurado" -ForegroundColor Red
        Write-Host "   Ejecuta: aws configure" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Error al verificar configuración AWS" -ForegroundColor Red
}

# 3. Verificar instancias EC2
Write-Host ""
Write-Host "3. VERIFICANDO INSTANCIAS EC2" -ForegroundColor Yellow
try {
    $instances = aws ec2 describe-instances --region us-west-2 --filters "Name=tag:Name,Values=*landom*" --query 'Reservations[].Instances[]' --output json 2>$null
    if ($LASTEXITCODE -eq 0 -and $instances -ne "[]") {
        Write-Host "✅ Instancia EC2 encontrada" -ForegroundColor Green
        $instanceData = $instances | ConvertFrom-Json
        foreach ($instance in $instanceData) {
            Write-Host "   ID: $($instance.InstanceId)" -ForegroundColor Cyan
            Write-Host "   Estado: $($instance.State.Name)" -ForegroundColor Cyan
            Write-Host "   IP: $($instance.PublicIpAddress)" -ForegroundColor Cyan
            Write-Host "   Tipo: $($instance.InstanceType)" -ForegroundColor Cyan
        }
    } else {
        Write-Host "❌ No se encontraron instancias EC2 para Landom" -ForegroundColor Red
        Write-Host "   El despliegue aún no se ha ejecutado" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Error al verificar instancias EC2" -ForegroundColor Red
}

# 4. Verificar dominio
Write-Host ""
Write-Host "4. VERIFICANDO DOMINIO" -ForegroundColor Yellow
$domain = "landom-api.us-west-2.elasticbeanstalk.com"
try {
    $response = Invoke-WebRequest -Uri "http://$domain" -TimeoutSec 10 -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ Dominio responde correctamente" -ForegroundColor Green
        Write-Host "   URL: http://$domain" -ForegroundColor Cyan
    } else {
        Write-Host "⚠️  Dominio responde con código: $($response.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Dominio no responde" -ForegroundColor Red
    Write-Host "   URL: http://$domain" -ForegroundColor Cyan
}

# 5. Verificar HTTPS
Write-Host ""
Write-Host "5. VERIFICANDO HTTPS" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "https://$domain" -TimeoutSec 10 -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ HTTPS funciona correctamente" -ForegroundColor Green
        Write-Host "   URL: https://$domain" -ForegroundColor Cyan
    } else {
        Write-Host "⚠️  HTTPS responde con código: $($response.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ HTTPS no responde" -ForegroundColor Red
    Write-Host "   URL: https://$domain" -ForegroundColor Cyan
}

# 6. Verificar archivos de despliegue
Write-Host ""
Write-Host "6. VERIFICANDO ARCHIVOS DE DESPLIEGUE" -ForegroundColor Yellow
$files = @("deploy.sh", "deploy-windows.ps1", "ec2-config-personalized.sh", "docker-compose.yml", "Dockerfile")
foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "✅ $file existe" -ForegroundColor Green
    } else {
        Write-Host "❌ $file no existe" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=================================" -ForegroundColor Blue
Write-Host "RESUMEN DEL ESTADO" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Para completar el despliegue:" -ForegroundColor Yellow
Write-Host "1. Reinicia PowerShell" -ForegroundColor Cyan
Write-Host "2. Ejecuta: aws configure" -ForegroundColor Cyan
Write-Host "3. Ejecuta: .\deploy-windows.ps1" -ForegroundColor Cyan

Write-Host ""
Write-Host "URLs para verificar:" -ForegroundColor Green
Write-Host "   HTTP: http://$domain" -ForegroundColor Cyan
Write-Host "   HTTPS: https://$domain" -ForegroundColor Cyan

Write-Host ""
Write-Host "¿Quieres que te ayude con algún paso específico?" -ForegroundColor Green 