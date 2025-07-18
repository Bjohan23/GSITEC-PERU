# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

GSITEC PERU is a PHP-based e-commerce platform for computer components and accessories. The application uses MySQL for data storage and TailwindCSS for styling, with a modern responsive design supporting both light and dark modes.

## Development Setup

### Prerequisites
- XAMPP with PHP 7.4+ and MySQL 8.0+
- Database configured on port 3307 (custom MySQL port)

### Database Configuration
- Import `tienda_online.sql` into phpMyAdmin
- Database connection configured in `config/config.php`:
  - Host: `localhost:3307`
  - Database: `tienda_online`
  - Username: `root`
  - Password: `root`

### Local Development
- Access application at: `http://localhost/PAF/`
- Apache on port 80, MySQL on port 3307

## Architecture

### File Structure
- `config/config.php` - Database configuration and BASE_URL definition
- `php/head_html.php` - Common HTML head section with TailwindCSS and dependencies
- `php/admin_nav.php` - Reusable admin navigation component
- `index.php` - Main storefront page
- `php/` - All PHP application files (login, registration, cart, admin panels)
- `img/` - Product images and static assets

### Key Components

#### Session Management
- User sessions: `$_SESSION['sesion_personal']`
- Admin sessions: `$_SESSION['sesion_admin']`
- Super admin level indicated by `$_SESSION['sesion_admin']['nivel'] == 2`

#### Database Tables
- `producto` - Product catalog
- `usuario` - User accounts
- `carrito` - Shopping cart items
- `pedido` - Order history
- `categorias` - Product categories with colors and icons

#### Frontend Framework
- TailwindCSS 3.x via CDN with custom color palette
- Custom "techblue" color scheme (50-900 shades)
- Cyan accent colors (400, 500, 600)
- Dark mode support with `class` strategy

### Admin System
- Two-level admin system (regular admins and super admins)
- Admin navigation in `php/admin_nav.php` shows different options based on admin level
- Admin pages include: products, categories, sales, analytics, customer management

## Development Guidelines

### Adding New Features
1. Follow existing PHP structure and session handling patterns
2. Use the admin navigation component for admin pages
3. Implement proper error handling and SQL injection prevention
4. Follow TailwindCSS utility-first approach for styling

### Database Queries
- Use prepared statements to prevent SQL injection
- Connection pattern: `mysqli_connect($db_hostname, $db_username, $db_password, $db_name)`
- Always check connection with `mysqli_connect_errno()`

### Styling Conventions
- Use TailwindCSS utility classes
- Follow dark mode patterns: `class="bg-white dark:bg-gray-800"`
- Use custom color palette: `techblue-600`, `cyan-400`, etc.
- Maintain responsive design with mobile-first approach

### Security Considerations
- All user input is sanitized with `htmlspecialchars()`
- Session-based authentication for both users and admins
- File upload security for product images
- Admin-only access controls for sensitive operations