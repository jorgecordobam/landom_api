# Script para agregar AWS CLI al PATH de Windows
# Email: nuovaiapps@gmail.com

Write-Host "=================================" -ForegroundColor Blue
Write-Host "AGREGANDO AWS CLI AL PATH" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

# Verificar si AWS CLI está instalado en ubicaciones comunes
$awsPaths = @(
    "C:\Program Files\Amazon\AWSCLIV2\aws.exe",
    "C:\Program Files (x86)\Amazon\AWSCLIV2\aws.exe",
    "$env:LOCALAPPDATA\Programs\Amazon\AWSCLIV2\aws.exe"
)

$awsFound = $false
$awsPath = ""

foreach ($path in $awsPaths) {
    if (Test-Path $path) {
        Write-Host "AWS CLI encontrado en: $path" -ForegroundColor Green
        $awsFound = $true
        $awsPath = Split-Path $path -Parent
        break
    }
}

if (-not $awsFound) {
    Write-Host "AWS CLI no encontrado en ubicaciones comunes" -ForegroundColor Red
    Write-Host "Reinstalando AWS CLI..." -ForegroundColor Yellow
    
    # Descargar e instalar AWS CLI
    try {
        $awsCliUrl = "https://awscli.amazonaws.com/AWSCLIV2.msi"
        $installerPath = "$env:TEMP\AWSCLIV2.msi"
        
        Write-Host "Descargando AWS CLI..." -ForegroundColor Yellow
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
    
    # Verificar nuevamente después de la instalación
    foreach ($path in $awsPaths) {
        if (Test-Path $path) {
            Write-Host "AWS CLI encontrado en: $path" -ForegroundColor Green
            $awsFound = $true
            $awsPath = Split-Path $path -Parent
            break
        }
    }
}

if ($awsFound) {
    Write-Host ""
    Write-Host "Agregando AWS CLI al PATH..." -ForegroundColor Yellow
    
    # Agregar al PATH del usuario actual
    $userPath = [Environment]::GetEnvironmentVariable("PATH", "User")
    
    if ($userPath -notlike "*$awsPath*") {
        $newPath = "$userPath;$awsPath"
        [Environment]::SetEnvironmentVariable("PATH", $newPath, "User")
        Write-Host "AWS CLI agregado al PATH del usuario" -ForegroundColor Green
    } else {
        Write-Host "AWS CLI ya está en el PATH del usuario" -ForegroundColor Green
    }
    
    # Actualizar PATH en la sesión actual
    $env:PATH = [Environment]::GetEnvironmentVariable("PATH", "User") + ";" + [Environment]::GetEnvironmentVariable("PATH", "Machine")
    
    Write-Host ""
    Write-Host "Verificando instalación..." -ForegroundColor Yellow
    
    # Verificar que AWS CLI funciona
    try {
        $awsVersion = & "$awsPath\aws.exe" --version 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "AWS CLI funciona correctamente!" -ForegroundColor Green
            Write-Host "Versión: $awsVersion" -ForegroundColor Cyan
        } else {
            Write-Host "Error al ejecutar AWS CLI" -ForegroundColor Red
        }
    } catch {
        Write-Host "Error al verificar AWS CLI" -ForegroundColor Red
    }
    
    Write-Host ""
    Write-Host "=================================" -ForegroundColor Blue
    Write-Host "PROXIMOS PASOS" -ForegroundColor Blue
    Write-Host "=================================" -ForegroundColor Blue
    
    Write-Host "1. Reinicia PowerShell para que los cambios tomen efecto" -ForegroundColor Yellow
    Write-Host "2. Ejecuta: aws --version" -ForegroundColor Cyan
    Write-Host "3. Si funciona, ejecuta: .\pasos-despues-reinicio.ps1" -ForegroundColor Cyan
    
} else {
    Write-Host "No se pudo encontrar AWS CLI" -ForegroundColor Red
    Write-Host "Por favor instala manualmente desde: https://aws.amazon.com/cli/" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "¡Listo! Reinicia PowerShell y continúa con la configuración." -ForegroundColor Green 