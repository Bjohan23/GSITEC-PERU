<?php
// admin_nav.php - Navegación estándar para todas las páginas de admin
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Navigation Bar -->
<nav class="bg-techblue-600 dark:bg-techblue-800 shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">
            <div class="flex items-center">
                <button class="md:hidden text-white mr-3" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <a href="./panel_admin.php" class="text-white text-xl font-bold">GSITEC ADMIN</a>
            </div>

            <div class="hidden md:flex items-center space-x-6">
                <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'panel_admin.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    🏠 Dashboard
                </a>
                <a href="./gestion_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'gestion_productos.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    📦 Productos
                </a>
                <a href="./categorias_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'categorias_admin.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    🏷️ Categorías
                </a>
                <a href="./historial_ventas.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'historial_ventas.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    📊 Ventas
                </a>
                <a href="./reportes_avanzados.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'reportes_avanzados.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    📈 Analytics
                </a>
                <a href="./lista_clientes.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'lista_clientes.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    👥 Clientes
                </a>
                <a href="../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">
                    🛍️ Ver Tienda
                </a>
                
                <!-- Dark Mode Toggle -->
                <button onclick="toggleDarkMode()" class="text-white hover:text-cyan-400 transition-colors duration-200 p-2 rounded-lg">
                    <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 716.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                    </svg>
                </button>

                <div class="flex items-center space-x-4">
                    <span class="text-white">
                        👑 Admin: <span class="font-semibold text-cyan-400"><?= htmlspecialchars($_SESSION['sesion_admin']['nombre']) ?></span>
                        <?php if(isset($_SESSION['sesion_admin']['nivel']) && $_SESSION['sesion_admin']['nivel'] == 2): ?>
                            <span class="text-yellow-300">⭐ Super</span>
                        <?php endif; ?>
                    </span>
                    <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden hidden pb-4">
            <div class="flex flex-col space-y-3">
                <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'panel_admin.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    🏠 Dashboard
                </a>
                <a href="./gestion_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'gestion_productos.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    📦 Productos
                </a>
                <a href="./categorias_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'categorias_admin.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    🏷️ Categorías
                </a>
                <a href="./historial_ventas.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'historial_ventas.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    📊 Ventas
                </a>
                <a href="./reportes_avanzados.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'reportes_avanzados.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    📈 Analytics
                </a>
                <a href="./lista_clientes.php" class="text-white hover:text-cyan-400 transition-colors duration-200 <?= $current_page == 'lista_clientes.php' ? 'text-cyan-400 font-semibold' : '' ?>">
                    👥 Clientes
                </a>
                <a href="../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">
                    🛍️ Ver Tienda
                </a>
                <span class="text-cyan-400">👑 <?= htmlspecialchars($_SESSION['sesion_admin']['nombre']) ?></span>
                <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- JavaScript para navegación (incluir solo una vez) -->
<script>
    function toggleMobileMenu() {
        document.getElementById('mobileMenu').classList.toggle('hidden');
    }

    function toggleDarkMode() {
        const html = document.documentElement;
        html.classList.toggle('dark');
        localStorage.setItem('darkMode', html.classList.contains('dark') ? 'true' : 'false');
    }

    // Cargar preferencia de modo oscuro
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    });
</script>