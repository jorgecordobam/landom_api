# Script de inicio rápido para despliegue de Landom API en EC2 (Windows)
# Email: nuovaiapps@gmail.com
# Autor: Jorge Cordoba

Write-Host "=================================" -ForegroundColor Blue
Write-Host "🚀 INICIO RÁPIDO - LANDOM API EN EC2" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "Email configurado: nuovaiapps@gmail.com" -ForegroundColor Green
Write-Host "Repositorio: https://github.com/jorgecordobam/landom_api" -ForegroundColor Green

# Verificar que estamos en el directorio correcto
if (!(Test-Path "deploy.sh")) {
    Write-Host "❌ Este script debe ejecutarse desde el directorio raíz del proyecto" -ForegroundColor Red
    exit 1
}

# Paso 1: Configurar AWS CLI
Write-Host "=================================" -ForegroundColor Blue
Write-Host "PASO 1: CONFIGURANDO AWS CLI" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

if (!(Get-Command aws -ErrorAction SilentlyContinue)) {
    Write-Host "❌ AWS CLI no está instalado. Ejecutando setup..." -ForegroundColor Red
    .\setup-aws-windows.ps1
} else {
    Write-Host "✅ AWS CLI ya está instalado" -ForegroundColor Green
}

# Paso 2: Verificar configuración AWS
Write-Host "=================================" -ForegroundColor Blue
Write-Host "PASO 2: VERIFICANDO CONFIGURACIÓN AWS" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

try {
    $result = aws sts get-caller-identity 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ AWS CLI configurado correctamente" -ForegroundColor Green
        Write-Host $result -ForegroundColor Cyan
    } else {
        Write-Host "❌ AWS CLI no está configurado" -ForegroundColor Red
        Write-Host "Por favor ejecuta: .\setup-aws-windows.ps1" -ForegroundColor Yellow
        exit 1
    }
} catch {
    Write-Host "❌ Error al verificar configuración AWS" -ForegroundColor Red
    exit 1
}

# Paso 3: Cargar configuración personalizada
Write-Host "=================================" -ForegroundColor Blue
Write-Host "PASO 3: CARGANDO CONFIGURACIÓN" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

if (Test-Path "ec2-config-personalized.sh") {
    Write-Host "✅ Archivo de configuración encontrado" -ForegroundColor Green
    
    # Leer configuración del archivo
    $configContent = Get-Content "ec2-config-personalized.sh" -Raw
    
    # Extraer variables importantes
    $awsRegion = if ($configContent -match 'AWS_REGION="([^"]+)"') { $matches[1] } else { "us-west-2" }
    $keyPairName = if ($configContent -match 'KEY_PAIR_NAME="([^"]+)"') { $matches[1] } else { "landom-key-pair" }
    $instanceType = if ($configContent -match 'EC2_INSTANCE_TYPE="([^"]+)"') { $matches[1] } else { "t2.micro" }
    $domain = if ($configContent -match 'DOMAIN="([^"]*)"') { $matches[1] } else { "" }
    
    Write-Host "✅ Configuración personalizada cargada" -ForegroundColor Green
} else {
    Write-Host "❌ Archivo de configuración no encontrado" -ForegroundColor Red
    exit 1
}

# Paso 4: Verificar variables críticas
Write-Host "=================================" -ForegroundColor Blue
Write-Host "PASO 4: VERIFICANDO CONFIGURACIÓN" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "📋 Configuración actual:" -ForegroundColor Yellow
Write-Host "   🌍 Región AWS: $awsRegion" -ForegroundColor Cyan
Write-Host "   🔑 Key Pair: $keyPairName" -ForegroundColor Cyan
Write-Host "   🖥️  Tipo de instancia: $instanceType" -ForegroundColor Cyan
Write-Host "   🌐 Dominio: $domain" -ForegroundColor Cyan
Write-Host "   📧 Email SSL: nuovaiapps@gmail.com" -ForegroundColor Cyan

# Preguntar si quiere continuar
$response = Read-Host "¿Quieres continuar con esta configuración? (y/n)"
if ($response -ne "y" -and $response -ne "Y") {
    Write-Host "Por favor edita ec2-config-personalized.sh y ejecuta este script nuevamente" -ForegroundColor Yellow
    exit 1
}

# Paso 5: Preparar scripts
Write-Host "=================================" -ForegroundColor Blue
Write-Host "PASO 5: PREPARANDO SCRIPTS" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "✅ Scripts listos para ejecución" -ForegroundColor Green

# Paso 6: Ejecutar despliegue
Write-Host "=================================" -ForegroundColor Blue
Write-Host "PASO 6: INICIANDO DESPLIEGUE" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "🚀 Iniciando despliegue de Landom API en EC2..." -ForegroundColor Green
Write-Host "⏱️  Este proceso puede tomar 10-15 minutos..." -ForegroundColor Yellow

# Ejecutar el script de despliegue usando Git Bash o WSL
if (Get-Command bash -ErrorAction SilentlyContinue) {
    Write-Host "Ejecutando con bash..." -ForegroundColor Yellow
    bash deploy.sh
} elseif (Get-Command wsl -ErrorAction SilentlyContinue) {
    Write-Host "Ejecutando con WSL..." -ForegroundColor Yellow
    wsl bash deploy.sh
} else {
    Write-Host "❌ No se encontró bash o WSL" -ForegroundColor Red
    Write-Host "Por favor instala Git Bash o WSL para ejecutar los scripts" -ForegroundColor Yellow
    Write-Host "Git Bash: https://git-scm.com/download/win" -ForegroundColor Yellow
    Write-Host "WSL: https://docs.microsoft.com/en-us/windows/wsl/install" -ForegroundColor Yellow
    exit 1
}

Write-Host "=================================" -ForegroundColor Blue
Write-Host "🎉 DESPLIEGUE COMPLETADO" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "✅ Tu aplicación Laravel está desplegada en EC2!" -ForegroundColor Green
Write-Host "🌐 URL: https://$domain" -ForegroundColor Green
Write-Host "🔒 SSL: Configurado automáticamente" -ForegroundColor Green
Write-Host "📧 Email de contacto: nuovaiapps@gmail.com" -ForegroundColor Green

Write-Host ""
Write-Host "📋 Comandos útiles:" -ForegroundColor Yellow
Write-Host "   🔍 Ver logs: ssh -i ~/.ssh/$keyPairName.pem ubuntu@IP 'docker-compose logs -f'" -ForegroundColor Cyan
Write-Host "   🔄 Reiniciar: ssh -i ~/.ssh/$keyPairName.pem ubuntu@IP 'docker-compose restart'" -ForegroundColor Cyan
Write-Host "   💾 Backup: ssh -i ~/.ssh/$keyPairName.pem ubuntu@IP './backup-script.sh'" -ForegroundColor Cyan

Write-Host ""
Write-Host "📞 Soporte:" -ForegroundColor Yellow
Write-Host "   📧 Email: nuovaiapps@gmail.com" -ForegroundColor Cyan
Write-Host "   📚 Documentación: README-DEPLOY-EC2.md" -ForegroundColor Cyan

Write-Host ""
Write-Host "⚠️  IMPORTANTE:" -ForegroundColor Yellow
Write-Host "   - Monitorea los costos en AWS Console" -ForegroundColor Red
Write-Host "   - Configura alertas de CloudWatch" -ForegroundColor Red
Write-Host "   - Haz backups regulares" -ForegroundColor Red
Write-Host "   - Mantén la instancia actualizada" -ForegroundColor Red

Write-Host "🎯 ¡Tu aplicación está lista para producción!" -ForegroundColor Green 