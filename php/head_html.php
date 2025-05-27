<?php
echo "<meta charset=\"UTF-8\">";
echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">";
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";

// TailwindCSS CDN
echo "<script src=\"https://cdn.tailwindcss.com\"></script>";

// TailwindCSS Configuration
echo "<script>";
echo "tailwind.config = {";
echo "    darkMode: 'class',";
echo "    theme: {";
echo "        extend: {";
echo "            colors: {";
echo "                'techblue': {";
echo "                    50: '#eff6ff',";
echo "                    100: '#dbeafe',";
echo "                    200: '#bfdbfe',";
echo "                    300: '#93c5fd',";
echo "                    400: '#60a5fa',";
echo "                    500: '#3b82f6',";
echo "                    600: '#2563eb',";
echo "                    700: '#1d4ed8',";
echo "                    800: '#1e40af',";
echo "                    900: '#1e3a8a',";
echo "                },";
echo "                'cyan': {";
echo "                    400: '#22d3ee',";
echo "                    500: '#06b6d4',";
echo "                    600: '#0891b2',";
echo "                }";
echo "            }";
echo "        }";
echo "    }";
echo "}";
echo "</script>";

// Bootstrap JS solo para funcionalidades específicas como carrusel
echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js\"></script>";
echo "<script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js\"></script>";

// Font Awesome para iconos
echo "<link href=\"//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css\" rel=\"stylesheet\">";

// Estilos personalizados
echo "<link rel=\"stylesheet\" href=\"../css/custom-styles.css\">";
?>