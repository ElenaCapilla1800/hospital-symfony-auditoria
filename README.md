# 🏥 Hospital Symfony - Gestión Médica y Auditoría de Seguridad

Aplicación web para la gestión de historiales clínicos, desarrollada en **Symfony 7**. Se centra en la trazabilidad de datos sensibles y el cumplimiento de normativas de seguridad (RGPD) mediante un sistema de auditoría interna y control de acceso granular por roles.

## 🚀 Requisitos previos

- **Docker Desktop** (con WSL2 en Windows)
- **DDEV** v1.24+ ([instalación](https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/))
- **Git**

## 🛠️ Instalación y configuración

1. **Clonar el repositorio:**

```bash
   git clone https://github.com/ElenaCapilla1800/hospital-symfony-auditoria.git
   cd hospital-symfony-auditoria
```

1. **Levantar el entorno con DDEV:**

```bash
   ddev start
```

1. **Instalar dependencias:**

```bash
   ddev composer install
```

1. **Crear la base de datos y aplicar migraciones:**

```bash
   ddev exec php bin/console doctrine:migrations:migrate --no-interaction
```

1. **Cargar los datos de ejemplo (fixtures):**

```bash
   ddev exec php bin/console doctrine:fixtures:load --no-interaction
```

1. **Abrir el proyecto:**

```bash
   ddev launch
```

## 🐳 Alternativa: levantar con Docker Compose

El proyecto también incluye un `docker-compose.yml` estándar (PHP-FPM + Nginx + MariaDB), independiente de DDEV, para quien prefiera Docker puro:

1. **Levantar los contenedores:**

```bash
   docker compose up -d --build
```

1. **Instalar dependencias:**

```bash
   docker compose exec php composer install
```

1. **Generar las claves JWT:**

```bash
   docker compose exec php php bin/console lexik:jwt:generate-keypair
```

1. **Base de datos:**

```bash
   docker compose exec php php bin/console doctrine:database:create
   docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
   docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

1. Visita `http://localhost:8090`.

> **Nota:** DDEV y este `docker-compose.yml` no deben ejecutarse a la vez sobre el mismo checkout — ambos gestionan `DATABASE_URL` de forma distinta (DDEV genera un `.env.local` propio) y comparten el mismo puerto de base de datos. Para usar Docker Compose, detén DDEV primero (`ddev stop`).
>
> DDEV usa [Mutagen](https://ddev.readthedocs.io/) para sincronización de archivos optimizada en Windows/Mac, por lo que resulta notablemente más rápido en desarrollo diario. Este `docker-compose.yml` usa bind mounts estándar (más simple, pero más lento en Windows) — se incluye como referencia de containerización manual, no como entorno principal de desarrollo.

### Usuarios de prueba (fixtures)

| Email | Contraseña | Rol |
| --- | --- | --- |

| <doctor@test.com> | 123456 | ROLE_DOCTOR |

| <garcia@test.com> | 123456 | ROLE_DOCTOR |

| <admin@hospital.com> | admin123 | ROLE_ADMIN |

## ✅ Tests

El proyecto incluye tests funcionales sobre autenticación y control de acceso (Voter de autorización por roles):

```bash
ddev exec php bin/console doctrine:migrations:migrate --env=test --no-interaction
ddev exec php bin/console doctrine:fixtures:load --env=test --no-interaction
ddev exec php bin/phpunit
```

Cobertura actual: login (correcto/incorrecto), acceso no autenticado, y las reglas del `MedicalRecordVoter` (un médico solo accede a sus propios historiales; un administrador puede ver cualquiera pero no editar los ajenos).

## 🔒 Decisiones de diseño de seguridad

**Centralización de la auditoría:** la entidad `AccessLog` es un registro inmutable. La relación `ManyToOne` con `User` asegura la integridad referencial de quién realizó cada acción.

**Registro de intentos denegados:** el campo `granted` (bool) permite auditar tanto el uso legítimo como los intentos de acceso no autorizado, habilitando análisis forense básico.

**Optimización de consultas (DQL):** el panel de administración usa `Join` para evitar el problema de las N+1 consultas.

**Protección RGPD:** el sistema registra IP y tipo de acción (ver/editar) sobre datos sensibles de salud.

**Control de acceso por Voter:** la autorización granular (quién puede ver/editar cada historial concreto) se implementa mediante un `Voter` de Symfony, no solo mediante roles a nivel de ruta — permite reglas como "el admin ve todo pero solo el propietario edita".

## 📬 Colección de Postman

En `/postman`, incluye:

- `POST /login` — autenticación por formulario.
- `GET /admin/logs` — auditoría general (filtros por `?email=`, `?action=`, `?date=`).
- `GET /admin/suspicious` — intentos denegados en las últimas 24h.

## 📄 Contenido adicional

- `docs/captura_auditoria.png` — captura del log en funcionamiento.
- `docs/decisiones_seguridad.pdf` — documento detallado de diseño.
