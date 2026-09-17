# SIM-EMAPAP 🏢💧

> **Sistema Integrado de Gestión - EMAPA**  
> Plataforma empresarial integral desarrollada con **Laravel 12**, **Vue.js**, **PostgreSQL** y empaquetada para despliegue automatizado en **Dokploy** y **Docker**.

---

## 🚀 Stack Tecnológico

* **Backend:** PHP 8.3+ | Laravel 12 | Sanctum & JWT Auth
* **Frontend:** Vue.js (Vuetify) | Laravel Mix | ApexCharts | Flexmonster
* **Base de Datos:** PostgreSQL 14+
* **Generación de Reportes:** Snappy (`wkhtmltopdf` / `wkhtmltoimage`), FPDF, Maatwebsite Excel
* **Facturación Electrónica:** Módulo SIAT con firma digital XML (`xmldsig`)
* **Infraestructura:** Docker (Multi-stage build) | Nginx | Supervisor | Dokploy (PaaS)

---

## 📋 Requisitos para Desarrollo Local

* **PHP:** >= 8.3 (Extensiones: `pdo_pgsql`, `pgsql`, `gd`, `bcmath`, `soap`, `intl`, `zip`, `xml`, `mbstring`)
* **Composer:** 2.x
* **Node.js:** >= 20.0.0 <= 22.x y **npm** >= 10.0.0
* **PostgreSQL:** 14+
* **Docker & Docker Compose** *(opcional para entorno contenerizado)*

---

## 💻 Instalación y Configuración Local

### 1. Clonar el repositorio
```bash
git clone https://github.com/David-E2020/sim_emapap.git
cd sim_emapap
```

### 2. Configurar variables de entorno
```bash
cp .env.example .env
```
Edita `.env` con las credenciales de tu base de datos PostgreSQL local:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sim_emapap
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

### 3. Instalar dependencias backend y frontend
```bash
# Dependencias PHP
composer install

# Dependencias Node.js
npm install --legacy-peer-deps
```

### 4. Generar claves de seguridad
```bash
php artisan key:generate
php artisan jwt:secret
```

### 5. Enlace de almacenamiento y migraciones
```bash
php artisan storage:link
php artisan migrate --seed
```

### 6. Compilar assets y levantar el servidor
```bash
# Terminal 1: Compilación de assets en tiempo real
npm run watch

# Terminal 2: Servidor Laravel
php artisan serve
```

---

## 🐳 Despliegue en Dokploy (Paso a Paso)

Este proyecto está optimizado con un **Dockerfile multi-stage** (Node 22 + PHP 8.3 + Nginx + Supervisor + wkhtmltopdf) listo para producción en **Dokploy**.

### Paso 1: Crear la Base de Datos PostgreSQL
1. En tu panel de Dokploy, ve a tu proyecto y selecciona **Add Database** > **PostgreSQL**.
2. Asigna un nombre (ej. `sim-emapap-db`), define el usuario, contraseña y nombre de base de datos.
3. Copia el **Internal Hostname** o nombre del servicio (generalmente el nombre que le diste, ej. `sim-emapap-db`).

### Paso 2: Crear la Aplicación
1. En el proyecto, selecciona **Add Service** > **Application**.
2. Nómbrala `sim-emapap-app`.
3. En **Source Type**, selecciona **GitHub** y enlaza el repositorio `David-E2020/sim_emapap`.
4. Selecciona la rama `main`.
5. Activa la casilla **Auto Deploy** (para que cada `git push` a `main` despliegue automáticamente).

### Paso 3: Configurar Build Type
1. En la pestaña **General** / **Build**, selecciona **Build Type: Dockerfile**.
2. **Dockerfile Path:** `/Dockerfile`
3. **Context Path:** `/`

### Paso 4: Variables de Entorno (`Environment`)
En la pestaña **Environment**, define las siguientes variables:

```env
APP_NAME=SIM-EMAPA
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:COPIA_AQUI_LA_LLAVE_DE_TU_ENV_O_EJECUTA_KEY_GENERATE
JWT_SECRET=COPIA_AQUI_TU_JWT_SECRET_O_EJECUTA_JWT_SECRET
APP_URL=https://tu-dominio.com

DB_CONNECTION=pgsql
DB_HOST=sim-emapap-db
DB_PORT=5432
DB_DATABASE=nombre_de_tu_bd
DB_USERNAME=usuario_de_tu_bd
DB_PASSWORD=password_de_tu_bd

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_DRIVER=file
FILESYSTEM_DISK=public

# Ejecutar migraciones automáticamente al iniciar el contenedor
RUN_MIGRATIONS=true

# Poblar datos iniciales y usuario administrador (solo la primera vez)
RUN_SEEDERS=false
```

### Paso 5: Volúmenes Persistentes (`Volumes`)
Para que los archivos subidos por los usuarios no se pierdan al desplegar nuevas versiones:
1. En la pestaña **Volumes / Mounts**, agrega un nuevo montaje:
   * **Host Path / Volume Name:** `sim_emapap_storage`
   * **Mount Path:** `/var/www/html/storage/app/public`

### Paso 6: Dominio y Certificado SSL
1. En la pestaña **Domains**, añade tu dominio o subdominio (ej. `sistema.emapa.gob.bo` o tu dominio de prueba).
2. **Container Port:** `80`
3. Activa la opción **HTTPS / SSL** (Let's Encrypt).
4. Haz clic en **Deploy**. Dokploy compilará el frontend con Node 22, levantará PHP 8.3 + Nginx y publicará el sistema con SSL automático.

---

## 💻 Pruebas Locales con Docker Compose

Para validar todo el stack localmente antes de enviar cambios al repositorio o desplegar en producción:

```bash
# 1. Levantar y compilar contenedores en local
docker compose up -d --build

# 2. Verificar logs de inicialización, migraciones y seeders
docker compose logs -f app

# 3. Acceder al sistema en el navegador
# URL: http://localhost:8080
# Usuario inicial: admin
# Contraseña inicial: admin123456

# 4. Detener contenedores
docker compose down
```

---

## 🛠️ Comandos de Mantenimiento Frecuentes

```bash
# Limpiar y regenerar cachés de configuración y rutas
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Regenerar enlace simbólico de almacenamiento
php artisan storage:link

# Revertir y volver a correr migraciones con datos iniciales
php artisan migrate:fresh --seed
```

---

## 📄 Licencia

Este proyecto está bajo la licencia [MIT](LICENSE).