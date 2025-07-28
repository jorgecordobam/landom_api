# Pasos finales para completar el despliegue de Landom API
# Email: nuovaiapps@gmail.com

Write-Host "=================================" -ForegroundColor Blue
Write-Host "PASOS FINALES - LANDOM API" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

Write-Host "AWS CLI se esta instalando..." -ForegroundColor Yellow
Write-Host "IMPORTANTE: Necesitas reiniciar PowerShell despues de la instalacion" -ForegroundColor Red

Write-Host ""
Write-Host "PASO 1: REINICIAR POWERSHELL" -ForegroundColor Yellow
Write-Host "1. Cierra esta ventana de PowerShell" -ForegroundColor Cyan
Write-Host "2. Abre una nueva ventana de PowerShell" -ForegroundColor Cyan
Write-Host "3. Navega al directorio: cd 'C:\Users\Jorge Cordoba\Documents\ai\landom_api'" -ForegroundColor Cyan

Write-Host ""
Write-Host "PASO 2: VERIFICAR AWS CLI" -ForegroundColor Yellow
Write-Host "Ejecuta: aws --version" -ForegroundColor Cyan
Write-Host "Si funciona, continua al paso 3" -ForegroundColor Cyan

Write-Host ""
Write-Host "PASO 3: CONFIGURAR CREDENCIALES AWS" -ForegroundColor Yellow
Write-Host "1. Ve a AWS Console: https://console.aws.amazon.com/" -ForegroundColor Cyan
Write-Host "2. Inicia sesion con: nuovaiapps@gmail.com" -ForegroundColor Cyan
Write-Host "3. Ve a IAM - Users - Tu usuario - Security credentials" -ForegroundColor Cyan
Write-Host "4. Crea un Access Key" -ForegroundColor Cyan
Write-Host "5. Ejecuta: aws configure" -ForegroundColor Cyan
Write-Host "   - AWS Access Key ID: [tu access key]" -ForegroundColor Cyan
Write-Host "   - AWS Secret Access Key: [tu secret key]" -ForegroundColor Cyan
Write-Host "   - Default region name: us-west-2" -ForegroundColor Cyan
Write-Host "   - Default output format: json" -ForegroundColor Cyan

Write-Host ""
Write-Host "PASO 4: VERIFICAR CONFIGURACION" -ForegroundColor Yellow
Write-Host "Ejecuta: aws sts get-caller-identity" -ForegroundColor Cyan
Write-Host "Deberia mostrar tu informacion de cuenta AWS" -ForegroundColor Cyan

Write-Host ""
Write-Host "PASO 5: EJECUTAR DESPLIEGUE" -ForegroundColor Yellow
Write-Host "Una vez configurado, ejecuta:" -ForegroundColor Cyan
Write-Host ".\deploy-windows.ps1" -ForegroundColor Cyan

Write-Host ""
Write-Host "RESUMEN DE CONFIGURACION:" -ForegroundColor Blue
Write-Host "   Region: us-west-2" -ForegroundColor Cyan
Write-Host "   Key Pair: landom-key-pair" -ForegroundColor Cyan
Write-Host "   Instance Type: t2.micro" -ForegroundColor Cyan
Write-Host "   Domain: landom-api.us-west-2.elasticbeanstalk.com" -ForegroundColor Cyan
Write-Host "   Email: nuovaiapps@gmail.com" -ForegroundColor Cyan

Write-Host ""
Write-Host "RESULTADO FINAL:" -ForegroundColor Green
Write-Host "Tu API estara disponible en:" -ForegroundColor Cyan
Write-Host "   https://landom-api.us-west-2.elasticbeanstalk.com" -ForegroundColor Cyan

Write-Host ""
Write-Host "¿Quieres que te ayude con algun paso especifico?" -ForegroundColor Green
$response = Read-Host "¿Continuar con la instalacion manual de AWS CLI? (y/n)"

if ($response -eq "y" -or $response -eq "Y") {
    Write-Host "Descargando AWS CLI manualmente..." -ForegroundColor Yellow
    
    try {
        $awsCliUrl = "https://awscli.amazonaws.com/AWSCLIV2.msi"
        $installerPath = "$env:TEMP\AWSCLIV2.msi"
        
        Write-Host "Descargando desde: $awsCliUrl" -ForegroundColor Cyan
        Invoke-WebRequest -Uri $awsCliUrl -OutFile $installerPath
        
        Write-Host "Instalando AWS CLI..." -ForegroundColor Yellow
        Start-Process msiexec.exe -Wait -ArgumentList "/i $installerPath /quiet"
        Remove-Item $installerPath -Force
        
        Write-Host "AWS CLI instalado correctamente!" -ForegroundColor Green
        Write-Host "Por favor reinicia PowerShell y ejecuta: aws configure" -ForegroundColor Yellow
    } catch {
        Write-Host "Error al instalar AWS CLI" -ForegroundColor Red
        Write-Host "Por favor instala manualmente desde: https://aws.amazon.com/cli/" -ForegroundColor Yellow
    }
} else {
    Write-Host "Instalacion cancelada. Sigue los pasos manualmente." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "¡Exito! Tu API estara lista en unos minutos." -ForegroundColor Green 