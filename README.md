# 🏥 Hospital Symfony - Gestión Médica y Auditoría de Seguridad

Este proyecto es una aplicación web para la gestión de historiales clínicos, desarrollada en **Symfony 7** (o 6.4). Se centra en la trazabilidad de datos sensibles y el cumplimiento de normativas de seguridad (RGPD) mediante un sistema de auditoría interna.

## 🚀 Requisitos previos

* **PHP:** 8.2 o superior
* **Composer:** 2.0+
* **Gestor de BD:** MySQL 8.0 o MariaDB
* **Symfony CLI:** Opcional (pero recomendado)

## 🛠️ Instalación y Configuración

1. **Clonar el repositorio:**

   ```bash
   git clone [https://github.com/tu-usuario/hospital-symfony.git](https://github.com/tu-usuario/hospital-symfony.git)
   cd hospital-symfony

2. **Instalar dependencias de PHP:**

   ```bash
   composer install

3. **Configurar el entorno:**

    Crea o edita el archivo .env.local y configura tu base de datos:
    DATABASE_URL="mysql://usuario:password@127.0.0.1:3306/hospital_db?serverVersion=8.0"

4. **Configurar el entorno:**

    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    php bin/console doctrine:fixtures:load

5. **Iniciar el servidor:**

   ```bash
   symfony server:start

## 6. Decisiones de Diseño de Seguridad

**Centralización de la Auditoría:** Se ha diseñado la entidad `AccessLog` como un registro inmutable. Al usar una relación `ManyToOne` con `User`, aseguramos la integridad referencial de quién realiza la acción.

**Gestión de Intentos Denegados:** La decisión de incluir el campo `granted (bool)` permite no solo auditar el uso legítimo, sino realizar análisis forense sobre intentos de intrusión.

**Optimización de Consultas (DQL):** En el panel de administración se utiliza `Join` para evitar el problema de las "N+1 consultas", optimizando el rendimiento del servidor bajo carga.

**Protección RGPD:** El sistema registra la dirección IP y el tipo de acción (ver/editar) sobre datos sensibles de salud, cumpliendo con la normativa vigente de trazabilidad de datos médicos.

## 7. Colección de Postman

La colección de pruebas se encuentra en la carpeta /postman del proyecto e incluye los siguientes endpoints clave:

1. **POST `/login`**: Autenticación: POST /login (Envío de credenciales para obtener sesión).
2. **GET `/admin/logs`**: Auditoría General: GET /admin/logs (Soporta filtros ?email=, ?action= y ?date=).
3. **GET `/admin/suspicious`**: Reporte de Riesgo: GET /admin/suspicious (Filtro automatizado de intentos fallidos en 24h).

## 8. Contenido Adicional en el Repositorio

**docs/captura_auditoria.png**: Captura de pantalla del log en funcionamiento.
**docs/decisiones_seguridad.pdf**: Documento detallado de diseño.
