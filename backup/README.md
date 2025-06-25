# Información del Respaldo - Base de Datos tienda_online

## Información General
- **Fecha de creación**: 2025-06-25 13:45:00
- **Base de datos**: tienda_online
- **Servidor**: localhost:3307
- **Charset**: utf8mb4

## Contenido del Respaldo

### Tablas incluidas:
1. **administradores** - 1 registro
2. **categorias** - 5 registros
3. **usuario** - 7 registros  
4. **producto** - 30 registros
5. **carrito** - 1 registro
6. **historial_compras** - 14 registros

### Características del respaldo:
✅ Estructura completa de todas las tablas
✅ Todos los datos actuales
✅ Claves primarias y foráneas
✅ Constraints e índices
✅ AUTO_INCREMENT configurado
✅ Configuración de charset

## Uso del respaldo

### Para restaurar la base de datos:
```sql
-- Conectar a MySQL
mysql -u root -p

-- Ejecutar el respaldo
source C:\xampp\htdocs\PAF\backup\tienda_online_backup_completo.sql
```

### Desde phpMyAdmin:
1. Crear nueva base de datos (si es necesario)
2. Ir a "Importar"
3. Seleccionar el archivo `tienda_online_backup_completo.sql`
4. Ejecutar

### Desde DataGrip:
1. Abrir el archivo SQL
2. Ejecutar todo el script

## Notas importantes:
- El respaldo incluye `DROP TABLE IF EXISTS` para evitar conflictos
- Se preservan todas las relaciones entre tablas
- Los AUTO_INCREMENT se configuran automáticamente
- Compatible con MySQL/MariaDB

## Estructura de directorios:
```
C:\xampp\htdocs\PAF\
└── backup\
    ├── tienda_online_backup_completo.sql
    └── README.md
```