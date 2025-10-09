# Sistema de Backups Automáticos

Sistema de backups automáticos implementado con `spatie/laravel-backup` v9.3.4.

## 📋 Resumen

- **Package:** spatie/laravel-backup v9.3.4
- **Destino:** Solo almacenamiento local (storage/app/private/Laravel/)
- **Frecuencia:** Diaria automática (2:00 AM)
- **Qué se respalda:** Base de datos + archivos importantes
- **Limpieza:** Automática de backups antiguos

---

## 🎯 Qué se Respalda

### Base de Datos
- ✅ Toda la base de datos (SQLite)

### Archivos
- ✅ `storage/app/` - Archivos subidos, documentos
- ✅ `public/uploads/` - Archivos públicos
- ✅ `public/logo_costeno_LP.svg` - Logo de la aplicación
- ✅ `.env` - Archivo de configuración

### Archivos Excluidos
- ❌ `storage/app/backups` - Backups previos
- ❌ `storage/framework` - Cache de Laravel
- ❌ `storage/logs` - Logs
- ❌ `public/css` y `public/js` - Assets compilados

---

## ⏰ Programación Automática

Los backups se ejecutan automáticamente vía Laravel Scheduler:

| Tarea | Horario | Comando |
|-------|---------|---------|
| **Backup de BD** | 2:00 AM diario | `backup:run --only-db` |
| **Limpieza** | 1:00 AM diario | `backup:clean` |
| **Monitoreo** | 3:00 AM diario | `backup:monitor` |

**Nota:** Se requiere cron job configurado en el servidor:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📁 Ubicación de Backups

Los backups se guardan en:
```
storage/app/private/Laravel/
```

**Formato del nombre:**
```
2025-10-09-01-58-32.zip
```

---

## 🔧 Comandos Manuales

### Crear Backup Manual

**Solo base de datos:**
```bash
php artisan backup:run --only-db
```

**Base de datos + archivos:**
```bash
php artisan backup:run
```

**Solo archivos:**
```bash
php artisan backup:run --only-files
```

### Listar Backups

```bash
php artisan backup:list
```

### Limpiar Backups Antiguos

```bash
php artisan backup:clean
```

### Monitorear Estado de Backups

```bash
php artisan backup:monitor
```

---

## 🗂️ Política de Retención

Los backups se mantienen según esta política:

| Período | Retención |
|---------|-----------|
| **Últimos 7 días** | Todos los backups |
| **Últimos 16 días** | 1 backup diario |
| **Últimas 8 semanas** | 1 backup semanal |
| **Últimos 4 meses** | 1 backup mensual |
| **Últimos 2 años** | 1 backup anual |

**Límite de espacio:** 5 GB

Cuando se alcanza el límite, los backups más antiguos se eliminan automáticamente.

---

## 📧 Notificaciones por Email

Las notificaciones se envían a la dirección configurada en `.env`:

```env
BACKUP_NOTIFICATION_EMAIL=admin@tu-dominio.com
```

**Se notifica cuando:**
- ✅ Backup exitoso
- ❌ Backup fallido
- ⚠️ Backup no saludable (muy antiguo o muy grande)
- 🧹 Limpieza exitosa
- ❌ Limpieza fallida

---

## 🔒 Seguridad

### Encriptación (Opcional)

Puedes encriptar los backups agregando una contraseña en `.env`:

```env
BACKUP_ARCHIVE_PASSWORD=tu_contraseña_segura
```

**Nota:** Sin esta variable, los backups NO están encriptados.

### Permisos

Asegúrate de que los archivos de backup tengan permisos restrictivos:

```bash
chmod 600 storage/app/private/Laravel/*.zip
```

---

## 🔍 Monitoreo

El sistema verifica automáticamente:

- **Antigüedad máxima:** 1 día
  - Si el último backup tiene más de 1 día, se notifica
- **Tamaño máximo:** 5000 MB
  - Si los backups exceden 5 GB, se notifica

---

## 🚀 Restaurar Backup

### 1. Localizar Backup

```bash
php artisan backup:list
```

### 2. Descargar/Extraer Backup

```bash
unzip storage/app/private/Laravel/2025-10-09-01-58-32.zip -d /tmp/restore
```

### 3. Restaurar Base de Datos

**Para SQLite:**
```bash
cp /tmp/restore/db-dumps/database.sql database/database.sqlite
```

**Para MySQL:**
```bash
mysql -u usuario -p nombre_bd < /tmp/restore/db-dumps/database.sql
```

### 4. Restaurar Archivos

```bash
cp -r /tmp/restore/storage/app/* storage/app/
cp -r /tmp/restore/public/uploads/* public/uploads/
cp /tmp/restore/.env .env
```

---

## 📊 Verificar Estado

### Ver último backup

```bash
php artisan backup:list
```

### Ver estadísticas

```bash
ls -lh storage/app/private/Laravel/
```

### Verificar programación

```bash
php artisan schedule:list
```

---

## ⚙️ Configuración Avanzada

Todas las opciones se encuentran en:
```
config/backup.php
```

**Opciones principales:**

- `backup.source.files.include` - Archivos a incluir
- `backup.source.files.exclude` - Archivos a excluir
- `backup.source.databases` - Bases de datos a respaldar
- `backup.destination.disks` - Discos de destino
- `backup.destination.compression_level` - Nivel de compresión (1-9)
- `cleanup.default_strategy` - Política de retención

---

## 🔧 Troubleshooting

### El backup no se ejecuta automáticamente

1. Verifica que el cron job esté configurado:
   ```bash
   crontab -l
   ```

2. Verifica que el scheduler funcione:
   ```bash
   php artisan schedule:list
   ```

3. Ejecuta manualmente para ver errores:
   ```bash
   php artisan backup:run --only-db
   ```

### El backup es muy grande

Considera usar solo backups de base de datos:
```php
// En routes/console.php
Schedule::command('backup:run --only-db')
```

### No se reciben notificaciones

1. Verifica configuración de email en `.env`
2. Prueba enviar email manualmente:
   ```bash
   php artisan tinker
   Mail::raw('Test', fn($m) => $m->to('admin@example.com')->subject('Test'));
   ```

### Error de permisos

```bash
chmod -R 775 storage/app/private
chown -R www-data:www-data storage/app/private
```

---

## 📝 Mejoras Futuras (Opcionales)

### Agregar Almacenamiento en la Nube

Para agregar OneDrive, Google Drive, S3, etc., consulta:
- `EMAIL_CONFIGURATION_RESEARCH_AND_PLAN.md` (sección OneDrive)
- [Documentación Oficial](https://spatie.be/docs/laravel-backup)

### Múltiples Destinos

Puedes configurar múltiples destinos simultáneamente:

```php
// config/backup.php
'destination' => [
    'disks' => [
        'local',
        's3',      // Agregar S3
        'dropbox', // Agregar Dropbox
    ],
],
```

---

## 📚 Referencias

- **Documentación Spatie:** https://spatie.be/docs/laravel-backup/v9
- **GitHub:** https://github.com/spatie/laravel-backup
- **Packagist:** https://packagist.org/packages/spatie/laravel-backup

---

## ✅ Checklist Post-Instalación

- [x] Package instalado
- [x] Configuración publicada
- [x] Backup configurado (solo local)
- [x] Scheduler configurado
- [x] Backup manual probado
- [ ] Cron job configurado en servidor de producción
- [ ] Email de notificaciones configurado
- [ ] Primer backup automático verificado

---

**Última actualización:** 8 de Octubre, 2025
**Versión:** 1.0
