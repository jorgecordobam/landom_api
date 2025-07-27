#!/bin/bash

# Script para ejecutar tests de LandonPro API
# Este script ejecuta diferentes grupos de tests y genera reportes

echo "🚀 Iniciando Tests de LandonPro API"
echo "=================================="

# Configurar entorno de testing
echo "📋 Configurando entorno de testing..."
cp .env .env.backup 2>/dev/null || true
cp .env.testing .env 2>/dev/null || echo "Archivo .env.testing no encontrado, usando .env actual"

# Ejecutar migraciones en base de datos de testing
echo "🗄️  Ejecutando migraciones en base de datos de testing..."
php artisan migrate:fresh --seed --env=testing

echo ""
echo "🧪 EJECUTANDO TESTS UNITARIOS"
echo "============================="

# Tests de modelos
echo "📦 Tests de Modelos..."
php artisan test tests/Unit/Models --env=testing

echo ""
echo "🔌 EJECUTANDO TESTS DE INTEGRACIÓN"
echo "=================================="

# Tests de autenticación
echo "🔐 Tests de Autenticación..."
php artisan test tests/Feature/Auth --env=testing

# Tests de gestión de proyectos
echo "🏗️  Tests de Gestión de Proyectos..."
php artisan test tests/Feature/ProjectManagement --env=testing

# Tests de inversiones
echo "💰 Tests de Inversiones..."
php artisan test tests/Feature/Investor --env=testing

# Tests de administración
echo "👑 Tests de Administración..."
php artisan test tests/Feature/Admin --env=testing

# Tests de comunidad
echo "👥 Tests de Comunidad..."
php artisan test tests/Feature/Community --env=testing

echo ""
echo "🔄 EJECUTANDO TESTS DE CICLO COMPLETO"
echo "===================================="

# Tests de integración completa
echo "🔗 Tests de Integración Completa..."
php artisan test tests/Feature/Integration --env=testing

# Tests de configuración del sistema
echo "⚙️  Tests de Configuración del Sistema..."
php artisan test tests/Feature/SystemConfigurationTest --env=testing

echo ""
echo "📊 EJECUTANDO TODOS LOS TESTS CON COBERTURA"
echo "==========================================="

# Ejecutar todos los tests con cobertura
php artisan test --coverage --env=testing

echo ""
echo "✅ RESUMEN DE CRITERIOS DE ACEPTACIÓN"
echo "===================================="

echo "Los tests verifican los siguientes criterios de aceptación:"
echo ""
echo "🔐 AUTENTICACIÓN Y AUTORIZACIÓN:"
echo "  ✓ Registro de usuarios con diferentes roles"
echo "  ✓ Login/logout con validación de credenciales"
echo "  ✓ Verificación de email y estados de usuario"
echo "  ✓ Control de acceso basado en roles"
echo ""
echo "🏗️  GESTIÓN DE PROYECTOS:"
echo "  ✓ Creación y edición de proyectos por constructores"
echo "  ✓ Control de acceso a proyectos propios"
echo "  ✓ Seguimiento de progreso y tareas"
echo "  ✓ Estados del proyecto y transiciones"
echo ""
echo "💰 SISTEMA DE INVERSIONES:"
echo "  ✓ Visualización de oportunidades de inversión"
echo "  ✓ Cálculo de retornos y ROI"
echo "  ✓ Proceso de inversión con validaciones"
echo "  ✓ Seguimiento de inversiones del usuario"
echo ""
echo "👑 ADMINISTRACIÓN DE PLATAFORMA:"
echo "  ✓ Gestión de usuarios y estados"
echo "  ✓ Verificación de empresas"
echo "  ✓ Supervisión de proyectos"
echo "  ✓ Configuración del sistema"
echo ""
echo "👥 SISTEMA DE COMUNIDAD:"
echo "  ✓ Publicación de posts y comentarios"
echo "  ✓ Sistema de likes y reacciones"
echo "  ✓ Control de privacidad de contenido"
echo "  ✓ Búsqueda y filtrado de contenido"
echo ""
echo "🔧 CASOS LÍMITE Y ROBUSTEZ:"
echo "  ✓ Validación de datos de entrada"
echo "  ✓ Manejo de errores y excepciones"
echo "  ✓ Prevención de operaciones concurrentes"
echo "  ✓ Limites de inversión y presupuesto"
echo ""
echo "🌐 INTEGRACIÓN CON FLUTTER:"
echo "  ✓ APIs REST con formato JSON consistente"
echo "  ✓ Headers CORS para aplicaciones móviles"
echo "  ✓ Autenticación con tokens Sanctum"
echo "  ✓ Paginación y búsqueda en endpoints"

# Restaurar archivo de entorno original
echo ""
echo "🔄 Restaurando configuración original..."
mv .env.backup .env 2>/dev/null || true

echo ""
echo "🎉 Tests completados!"
echo "==================="
