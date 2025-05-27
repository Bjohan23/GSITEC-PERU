# GSITEC PERU - Tienda Online 🚀

Una moderna tienda online de componentes para computadoras, juegos y accesorios, construida con PHP, MySQL y TailwindCSS.

## ✨ Características Principales

- 🎨 **Diseño Moderno**: Interfaz completamente renovada con TailwindCSS
- 🌓 **Modo Oscuro/Claro**: Cambio dinámico entre temas con persistencia
- 📱 **Completamente Responsivo**: Optimizado para dispositivos móviles y desktop
- 🎯 **Paleta Tech Blue**: Colores profesionales para tecnología
- ⚡ **Rendimiento Optimizado**: Carga rápida y transiciones suaves
- 🔒 **Sistema de Autenticación**: Login y registro de usuarios
- 🛒 **Carrito de Compras**: Gestión completa de productos
- 👑 **Panel de Administración**: Para usuarios super admin

## 🎨 Paleta de Colores

### Tech Blue - Paleta Principal
- **Primario**: `#2563eb` (techblue-600)
- **Acento**: `#22d3ee` (cyan-400)
- **Fondo Claro**: `#eff6ff` (techblue-50)
- **Fondo Oscuro**: `#1e3a8a` (techblue-900)
- **Texto**: Dinámico según el modo

## 🛠️ Tecnologías Utilizadas

- **Frontend**: 
  - TailwindCSS 3.x (CDN)
  - JavaScript ES6+
  - HTML5 semántico
  - CSS3 personalizado

- **Backend**: 
  - PHP 7.4+
  - MySQL 8.0
  - Sessions para autenticación

- **Herramientas**:
  - XAMPP para desarrollo local
  - Bootstrap 3.x (solo para carrusel)
  - Font Awesome para iconos

## 📦 Estructura del Proyecto

```
PAF/
├── css/
│   ├── custom-styles.css     # Estilos personalizados
│   ├── normalize.css         # CSS reset
│   └── styles.css           # Estilos legacy (backup)
├── config/
│   └── config.php           # Configuración de base de datos
├── img/
│   ├── carrusel/           # Imágenes del carrusel
│   └── productos/          # Imágenes de productos
├── php/
│   ├── head_html.php       # Configuración HTML común
│   ├── iniciar_sesion.php  # Página de login
│   ├── registro.php        # Página de registro
│   └── [otros archivos PHP]
├── index.php               # Página principal
└── README.md              # Documentación
```

## 🚀 Instalación y Configuración

### Prerrequisitos
- XAMPP con PHP 7.4+ y MySQL 8.0+
- Navegador web moderno

### Pasos de Instalación

1. **Clonar el proyecto**
   ```bash
   cd C:\xampp\htdocs\
   # Colocar archivos del proyecto en carpeta PAF
   ```

2. **Configurar base de datos**
   - Importar `tienda_online.sql` en phpMyAdmin
   - Verificar configuración en `config/config.php`:
   ```php
   $db_hostname="localhost:3307";  // Puerto 3307 configurado
   $db_username="root";
   $db_password="root";
   $db_name="tienda_online";
   ```

3. **Iniciar servicios**
   - Apache en puerto 80
   - MySQL en puerto 3307

4. **Acceder al sitio**
   - URL: `http://localhost/PAF/`

## 🎯 Funcionalidades Principales

### Para Usuarios
- ✅ **Navegación intuitiva** con modo oscuro/claro
- ✅ **Catálogo de productos** con filtros y búsqueda
- ✅ **Sistema de registro** con validación completa
- ✅ **Carrito de compras** dinámico
- ✅ **Perfil de usuario** personalizable
- ✅ **Historial de compras**

### Para Administradores
- 👑 **Panel de administración** completo
- 📊 **Gestión de productos** (CRUD)
- 📈 **Consultar historial** de ventas
- 👥 **Gestión de usuarios**

## 🌐 Modo Oscuro/Claro

El sitio incluye un sistema completo de temas:

### Características del Modo Oscuro
- 🌙 **Activación**: Botón en la navegación principal
- 💾 **Persistencia**: Se guarda la preferencia en localStorage
- 🎨 **Colores optimizados**: Paleta específica para cada modo
- 👁️ **Legibilidad**: Contraste optimizado para accesibilidad

### Implementación Técnica
```javascript
// Cambiar modo
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', 
        document.documentElement.classList.contains('dark') ? 'true' : 'false'
    );
}
```

## 📱 Responsividad

El diseño se adapta a diferentes tamaños de pantalla:

- **Mobile**: < 640px - Menú colapsable, grid de 1 columna
- **Tablet**: 640px - 1024px - Grid de 2-3 columnas
- **Desktop**: > 1024px - Grid completo de 5 columnas
- **4K**: > 1920px - Contenido centrado con márgenes

## 🎨 Personalización de Estilos

### TailwindCSS Configuration
```javascript
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'techblue': {
                    50: '#eff6ff',
                    // ... gama completa
                    900: '#1e3a8a',
                },
                'cyan': {
                    400: '#22d3ee',
                    500: '#06b6d4',
                    600: '#0891b2',
                }
            }
        }
    }
}
```

### CSS Personalizado
El archivo `custom-styles.css` incluye:
- Transiciones suaves
- Efectos hover avanzados
- Estilos para carrusel de Bootstrap
- Scrollbar personalizado
- Utilidades de accesibilidad

## 🔧 Configuración de Base de Datos

### Puerto Personalizado MySQL
El proyecto está configurado para MySQL en puerto 3307:

```php
// config/config.php
$db_hostname="localhost:3307";  // Puerto específico
```

### Estructura de Tablas Principales
- `usuario` - Información de usuarios
- `producto` - Catálogo de productos
- `carrito` - Elementos del carrito
- `pedido` - Historial de compras

## 🚀 Optimizaciones de Rendimiento

- **CDN**: TailwindCSS desde CDN para carga rápida
- **Imágenes**: Lazy loading y fallback para productos
- **CSS**: Minificación y combinación de archivos
- **JavaScript**: Carga diferida de funcionalidades no críticas
- **Cache**: Headers de cache para recursos estáticos

## 🔐 Seguridad

- ✅ **Validación de entrada**: Sanitización de datos PHP
- ✅ **Prevención SQL Injection**: Consultas preparadas
- ✅ **Autenticación**: Sistema de sesiones seguro
- ✅ **Autorización**: Roles de usuario (normal/admin)
- ✅ **HTTPS Ready**: Preparado para certificados SSL

## 🐛 Solución de Problemas Comunes

### Error de Conexión MySQL
```
mysqli_sql_exception: No se puede establecer una conexión
```
**Solución**: Verificar que MySQL esté en puerto 3307 y actualizar `config.php`

### Estilos no se Cargan
**Solución**: Verificar que TailwindCSS CDN esté disponible y `custom-styles.css` exista

### Modo Oscuro no Persiste
**Solución**: Verificar que localStorage esté habilitado en el navegador

## 📈 Mejoras Futuras

- [ ] **API REST**: Para aplicaciones móviles
- [ ] **PWA**: Funcionalidad offline
- [ ] **WebSockets**: Chat en tiempo real
- [ ] **Pagos**: Integración con pasarelas de pago
- [ ] **SEO**: Optimización para buscadores
- [ ] **Analytics**: Dashboard de métricas

## 👥 Contribución

Este proyecto fue desarrollado por el **Grupo 04** como parte del curso de desarrollo web.

### Equipo de Desarrollo
- Desarrollo Frontend y Backend
- Diseño UI/UX con TailwindCSS
- Implementación de base de datos
- Testing y optimización

## 📄 Licencia

Este proyecto es de uso educativo y fue desarrollado para el curso de programación web.

---

**GSITEC PERU** - Tu tienda de tecnología de confianza 🚀

*Desarrollado con ❤️ usando PHP, MySQL y TailwindCSS*