# Script para ejecutar tests de LandonPro API en Windows
# Este script ejecuta diferentes grupos de tests y genera reportes

Write-Host "🚀 Iniciando Tests de LandonPro API" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Green

# Configurar entorno de testing
Write-Host "📋 Configurando entorno de testing..." -ForegroundColor Yellow
Copy-Item -Path ".env" -Destination ".env.backup" -ErrorAction SilentlyContinue
if (Test-Path ".env.testing") {
    Copy-Item -Path ".env.testing" -Destination ".env"
} else {
    Write-Host "Archivo .env.testing no encontrado, usando .env actual" -ForegroundColor Yellow
}

# Ejecutar migraciones en base de datos de testing
Write-Host "🗄️  Ejecutando migraciones en base de datos de testing..." -ForegroundColor Yellow
php artisan migrate:fresh --seed --env=testing

Write-Host ""
Write-Host "🧪 EJECUTANDO TESTS UNITARIOS" -ForegroundColor Cyan
Write-Host "=============================" -ForegroundColor Cyan

# Tests de modelos
Write-Host "📦 Tests de Modelos..." -ForegroundColor White
php artisan test tests/Unit/Models --env=testing

Write-Host ""
Write-Host "🔌 EJECUTANDO TESTS DE INTEGRACIÓN" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan

# Tests de autenticación
Write-Host "🔐 Tests de Autenticación..." -ForegroundColor White
php artisan test tests/Feature/Auth --env=testing

# Tests de gestión de proyectos
Write-Host "🏗️  Tests de Gestión de Proyectos..." -ForegroundColor White
php artisan test tests/Feature/ProjectManagement --env=testing

# Tests de inversiones
Write-Host "💰 Tests de Inversiones..." -ForegroundColor White
php artisan test tests/Feature/Investor --env=testing

# Tests de administración
Write-Host "👑 Tests de Administración..." -ForegroundColor White
php artisan test tests/Feature/Admin --env=testing

# Tests de comunidad
Write-Host "👥 Tests de Comunidad..." -ForegroundColor White
php artisan test tests/Feature/Community --env=testing

Write-Host ""
Write-Host "🔄 EJECUTANDO TESTS DE CICLO COMPLETO" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan

# Tests de integración completa
Write-Host "🔗 Tests de Integración Completa..." -ForegroundColor White
php artisan test tests/Feature/Integration --env=testing

# Tests de configuración del sistema
Write-Host "⚙️  Tests de Configuración del Sistema..." -ForegroundColor White
php artisan test tests/Feature/SystemConfigurationTest --env=testing

Write-Host ""
Write-Host "📊 EJECUTANDO TODOS LOS TESTS CON COBERTURA" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan

# Ejecutar todos los tests con cobertura
php artisan test --coverage --env=testing

Write-Host ""
Write-Host "✅ RESUMEN DE CRITERIOS DE ACEPTACIÓN" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Green

Write-Host "Los tests verifican los siguientes criterios de aceptación:" -ForegroundColor White
Write-Host ""
Write-Host "🔐 AUTENTICACIÓN Y AUTORIZACIÓN:" -ForegroundColor Yellow
Write-Host "  ✓ Registro de usuarios con diferentes roles" -ForegroundColor Green
Write-Host "  ✓ Login/logout con validación de credenciales" -ForegroundColor Green
Write-Host "  ✓ Verificación de email y estados de usuario" -ForegroundColor Green
Write-Host "  ✓ Control de acceso basado en roles" -ForegroundColor Green
Write-Host ""
Write-Host "🏗️  GESTIÓN DE PROYECTOS:" -ForegroundColor Yellow
Write-Host "  ✓ Creación y edición de proyectos por constructores" -ForegroundColor Green
Write-Host "  ✓ Control de acceso a proyectos propios" -ForegroundColor Green
Write-Host "  ✓ Seguimiento de progreso y tareas" -ForegroundColor Green
Write-Host "  ✓ Estados del proyecto y transiciones" -ForegroundColor Green
Write-Host ""
Write-Host "💰 SISTEMA DE INVERSIONES:" -ForegroundColor Yellow
Write-Host "  ✓ Visualización de oportunidades de inversión" -ForegroundColor Green
Write-Host "  ✓ Cálculo de retornos y ROI" -ForegroundColor Green
Write-Host "  ✓ Proceso de inversión con validaciones" -ForegroundColor Green
Write-Host "  ✓ Seguimiento de inversiones del usuario" -ForegroundColor Green
Write-Host ""
Write-Host "👑 ADMINISTRACIÓN DE PLATAFORMA:" -ForegroundColor Yellow
Write-Host "  ✓ Gestión de usuarios y estados" -ForegroundColor Green
Write-Host "  ✓ Verificación de empresas" -ForegroundColor Green
Write-Host "  ✓ Supervisión de proyectos" -ForegroundColor Green
Write-Host "  ✓ Configuración del sistema" -ForegroundColor Green
Write-Host ""
Write-Host "👥 SISTEMA DE COMUNIDAD:" -ForegroundColor Yellow
Write-Host "  ✓ Publicación de posts y comentarios" -ForegroundColor Green
Write-Host "  ✓ Sistema de likes y reacciones" -ForegroundColor Green
Write-Host "  ✓ Control de privacidad de contenido" -ForegroundColor Green
Write-Host "  ✓ Búsqueda y filtrado de contenido" -ForegroundColor Green
Write-Host ""
Write-Host "🔧 CASOS LÍMITE Y ROBUSTEZ:" -ForegroundColor Yellow
Write-Host "  ✓ Validación de datos de entrada" -ForegroundColor Green
Write-Host "  ✓ Manejo de errores y excepciones" -ForegroundColor Green
Write-Host "  ✓ Prevención de operaciones concurrentes" -ForegroundColor Green
Write-Host "  ✓ Limites de inversión y presupuesto" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 INTEGRACIÓN CON FLUTTER:" -ForegroundColor Yellow
Write-Host "  ✓ APIs REST con formato JSON consistente" -ForegroundColor Green
Write-Host "  ✓ Headers CORS para aplicaciones móviles" -ForegroundColor Green
Write-Host "  ✓ Autenticación con tokens Sanctum" -ForegroundColor Green
Write-Host "  ✓ Paginación y búsqueda en endpoints" -ForegroundColor Green

# Restaurar archivo de entorno original
Write-Host ""
Write-Host "🔄 Restaurando configuración original..." -ForegroundColor Yellow
if (Test-Path ".env.backup") {
    Move-Item -Path ".env.backup" -Destination ".env" -Force
}

Write-Host ""
Write-Host "🎉 Tests completados!" -ForegroundColor Green
Write-Host "===================" -ForegroundColor Green
