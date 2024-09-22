# Proyecto Laravel - Ecommerce

## Configuración del Proyecto

Para configurar el proyecto Laravel en tu entorno local, sigue los siguientes pasos:

### 1. Enlace simbólico de almacenamiento

Ejecuta el siguiente comando para crear el enlace simbólico necesario entre la carpeta de almacenamiento y la carpeta pública:

```bash
php artisan storage:link
```

### 2. Migrar la base de datos a Mysql

Aplica las migraciones y carga los datos de ejemplo usando el comando:

```bash
php artisan migrate:fresh --seed
```

### 3. Usuario por defecto

Las credenciales por defecto al ejecutar el seeds son:

```bash
Email: admin@dev.com
Contraseña: password
```
