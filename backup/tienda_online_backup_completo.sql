-- =============================================
-- Respaldo completo de base de datos: tienda_online
-- Generado el: 2025-06-25 13:45:00
-- Servidor: localhost:3307
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =============================================
-- Crear base de datos si no existe
-- =============================================
CREATE DATABASE IF NOT EXISTS `tienda_online` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tienda_online`;

-- =============================================
-- Estructura de tabla: administradores
-- =============================================
DROP TABLE IF EXISTS `administradores`;
CREATE TABLE `administradores` (
  `id_administrador` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `nivel_admin` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id_administrador`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Datos de tabla: administradores
-- =============================================
INSERT INTO `administradores` (`id_administrador`, `nombre_usuario`, `correo`, `contrasena`, `fecha_creacion`, `ultimo_acceso`, `activo`, `nivel_admin`) VALUES
(1, 'johan', 'becerrajohan6@gmail.com', 'johan123', '2025-05-31 00:15:49', '2025-06-25 22:00:05', 1, 2);

-- =============================================
-- Estructura de tabla: categorias
-- =============================================
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion_categoria` varchar(255) DEFAULT NULL,
  `icono_categoria` varchar(10) DEFAULT NULL,
  `color_categoria` varchar(7) DEFAULT '#059669',
  `activa` tinyint(1) DEFAULT 1,
  `orden_visualizacion` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre_categoria` (`nombre_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Datos de tabla: categorias
-- =============================================
INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`, `descripcion_categoria`, `icono_categoria`, `color_categoria`, `activa`, `orden_visualizacion`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'monitores', 'Monitores gaming, profesionales y para oficina', '🖥️', '#059669', 1, 1, '2025-06-25 21:53:08', '2025-06-25 21:53:08'),
(2, 'teclados', 'Teclados mecánicos, inalámbricos y gaming', '⌨️', '#7C3AED', 1, 2, '2025-06-25 21:53:08', '2025-06-25 21:53:08'),
(3, 'ordenadores', 'Computadoras completas, laptops y equipos', '💻', '#DC2626', 1, 3, '2025-06-25 21:53:08', '2025-06-25 21:53:08'),
(4, 'accesorios', 'Mouse, audífonos, sillas y otros accesorios', '🎧', '#F59E0B', 1, 4, '2025-06-25 21:53:08', '2025-06-25 21:53:08'),
(5, 'gamer', 'Consolas, videojuegos y equipos gaming', '🎮', '#EC4899', 1, 5, '2025-06-25 21:53:08', '2025-06-25 21:53:08');

-- =============================================
-- Estructura de tabla: usuario
-- =============================================
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `contrasena` varchar(100) DEFAULT NULL,
  `numero_tarjeta` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `super_usuario` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Datos de tabla: usuario
-- =============================================
INSERT INTO `usuario` (`id_usuario`, `nombre_usuario`, `fecha_nacimiento`, `correo`, `contrasena`, `numero_tarjeta`, `direccion`, `super_usuario`) VALUES
(1, 'franciscousuario', '2001-06-04', 'francgutierrezlopez@gmail.com', '12345', '1234567890123456', 'universidad Anahuac facultad de ingeniería', 0),
(2, 'Leandro', '2001-06-04', 'PruebaLeandro@gmail.com', '$2y$10$IRRd5lcIN0PRU8b5i9sWcOEdNlC3mCwhfYnyACGBAouhr7ga78meq', '123456789123', 'Av. Sipan', 2),
(5, 'franciscootrousuario', '2001-06-04', 'francogl@gmail.com', '12345', '2222222222222222', 'uni anahuac facultad ingenieria 2', 0),
(24, 'Pantoja', '2004-12-15', 'Pantoja@gmail.com', 'Pantoja', '3210 9876 5432', 'Patapo', 0),
(25, 'Flores', '1969-12-24', 'Flores@gmail.com', 'Flores', '1234567890123', 'Pimentel', 0),
(26, 'admin@example.com', '2003-08-23', 'usuario@example.com', 'admin123', '1', 'Lambayeque', 0),
(27, 'Fabricio', '1969-12-24', 'harry.acost.20.20@gmail.com', '12345', '1237 4748 8484 8484', 'Sipan', 0);

-- =============================================
-- Estructura de tabla: producto
-- =============================================
DROP TABLE IF EXISTS `producto`;
CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_producto` varchar(100) DEFAULT NULL,
  `descripcion_producto` varchar(255) DEFAULT NULL,
  `cantidad_disponible` int(11) DEFAULT NULL,
  `precio_producto` double DEFAULT NULL,
  `fabricante` varchar(100) DEFAULT NULL,
  `origen` varchar(100) DEFAULT 'China',
  `categoria` varchar(100) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_producto`),
  KEY `fk_producto_categoria` (`id_categoria`),
  CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Datos de tabla: producto
-- =============================================
INSERT INTO `producto` (`id_producto`, `nombre_producto`, `descripcion_producto`, `cantidad_disponible`, `precio_producto`, `fabricante`, `origen`, `categoria`, `id_categoria`) VALUES
(1, 'Monitor gamer curvo Samsung C32R500', 'Este monitor de 32 pulgadas te dará comodidad para estudiar, trabajar o ver una película, su resolución de 1920 x 1080 te permitirá disfrutar de momentos únicos gracias a una imagen de alta fidelidad.', 2, 5999, 'Samsung', 'China', 'monitores', 1),
(2, 'Teclado inalámbrico Logitech K400 Plus QWERTY español', 'Color negro, con su touchpad incorporado puedes controlar el cursor de manera sencilla y mantener una cómoda navegación en cualquier interfaz.', 2, 800, 'Logitech', 'China', 'teclados', 2),
(3, 'Mouse de juego Glorious Model O', 'El mouse de juego te ofrecerá la posibilidad de marcar la diferencia y sacar ventajas en tus partidas. Su conectividad y sensor suave ayudará a que te desplaces rápido por la pantalla.', 30, 2300, 'Glorious', 'China', 'accesorios', 4),
(4, 'Asus Zenbook Pro Duo 15 Ux582', 'ASUS - ZenBook Pro Duo 15 UX582 Laptop con pantalla táctil de 15.6 - Intel Core i9 - Memoria de 32 GB - NVIDIA GeForce RTX 3060 - SSD de 1 TB - Celestial Blue', 1, 94000, 'Asus', 'China', 'ordenadores', 3),
(5, 'Audífonos gamer HyperX Cloud Alpha S blue', '¡Experimenta la adrenalina de sumergirte en la escena de otra manera! Tener auriculares específicos para jugar cambia completamente tu experiencia en cada partida.', 14, 2046, 'HP', 'China', 'accesorios', 4),
(6, 'Xtreme Pc Geforce Rtx 3060 Ryzen 5 360O 16gb Ssd 480gb 2tb', 'Gráficos NVIDIA GeForce RTX 3060 12GB GDDR6, Memory Bus 192-bit, Engine ClockBoost 1882 MHz, Memory Clock 14 Gbps lo que proporciona un rendimiento rápido, sin interrupciones y fluido en los juegos que te apasiona.', 4, 27026, 'Xtreme PC gamer', 'Mexico', 'ordenadores', 3),
(7, 'Silla de escritorio Seats And Stools giratoria reclinable reposa pies ergonómica', 'Con esta silla Seats And Stools, tendrás la comodidad y el bienestar que necesitas a lo largo de tu jornada. Además, puedes ubicarla en cualquier parte de tu casa u oficina ya que su diseño se adapta a múltiples entornos.', 8, 3521, 'Seats And Stools', 'China', 'accesorios', 4),
(8, 'Micrófono Maono AU-PM421 condensador cardioide', 'Con este producto lograrás que la reproducción obtenida sea lo más parecida a la original. Excelente para grabar voces debido a su sensibilidad y amplio rango de frecuencia.', 5, 2015, 'Maono', 'China', 'accesorios', 4),
(9, 'Microsoft Xbox Series X 1TB', 'Con tu consola Xbox Series tendrás entretenimiento asegurado todos los días. Su tecnología fue creada para poner nuevos retos tanto a jugadores principiantes como expertos.', 12, 20845, 'Microsoft', 'China', 'gamer', 5),
(10, 'Sony PlayStation 5 825GB', 'Con tu consola PlayStation 5 tendrás entretenimiento asegurado todos los días. Su tecnología fue creada para poner nuevos retos tanto a jugadores principiantes como expertos.', 48, 20895, 'Sony', 'China', 'gamer', 5),
(11, 'Nintendo Switch 32GB', 'Con tu consola Switch tendrás entretenimiento asegurado todos los días. Su tecnología fue creada para poner nuevos retos tanto a jugadores principiantes como expertos.', 14, 7000, 'Nintendo', 'China', 'gamer', 5),
(12, 'Audífonos in-ear inalámbricos Samsung Galaxy Buds Live mystic black', 'Cuenta con tecnología True Wireless, La batería dura 6 h, Modo manos libres incluido, Asistente de voz integrado: Bixby, Con cancelación de ruido.', 54, 1920, 'Samsung', 'China', 'accesorios', 4),
(13, 'Rog Zephyrus 14 Amd Ryzen 9-5900hs 16gb Nvidia Rtx 3060 1tb', 'ASUS - ROG Zephyrus 14 Gaming Laptop - AMD Ryzen 9 - 16GB Memory - NVIDIA GeForce RTX 3060 - 1TB SSD - Moonlight White - Moonlight White, Modelo:GA401QM-211.ZG14', 62, 45500, 'Asus', 'China', 'ordenadores', 3),
(14, 'Audífonos gamer Redragon Zeus black', 'Con micrófono incorporado.\r\nTipo de conector: Jack 3.5 mm/USB.\r\nSonido superior y sin límites.\r\nCómodos y prácticos.', 25, 1327, 'Redragon', 'China', 'accesorios', 4),
(15, 'Mouse de juego Game Factor MOG601 rosa', 'Utiliza cable. posee rueda de desplazamiento. cuenta con 7 botones para un mayor control.\r\nCon luces para mejorar la experiencia de uso.\r\nCon sensor óptico.\r\nResolución de 32000dpi.', 24, 631, 'Game Factor', 'China', 'accesorios', 4),
(16, 'Monitor gamer curvo Huawei Sound Edition MateView GT LCD 34 negro', 'Pantalla LCD de 34 . Curvo. Tiene una resolución de 3440px-1440px. Relación de aspecto de 21:9. Panel VA. Su brillo es de 350cd/m.', 15, 12499, 'Huawei', 'China', 'monitores', 1),
(17, 'T50 Full - Silla Ergonómica - Oficina - Alta Tecnología', 'La hermosa forma del respaldo diseñado con la inspiración de la estructura proporcionada del ser humano, contribuye a una mayor comodidad ergonómica y estabilidad en su espalda.', 9, 8500, 'T50', 'Corea', 'accesorios', 4),
(18, 'Escritorio Para Videojuegos Gamer Con Librero Para Home', 'ESCRITORIO GAMER MODERNO IDEAL PARA TU HOGAR FACIL DE ARMAR INCLUYE ENVIO GRATUITO A TODA EL PAIS MEXICO, (APLICA RESTRICCIONES)', 47, 2500, 'GNN', 'Chiapas', 'accesorios', 4),
(19, 'The Walking Dead Collection Xbox One Físico Sellado 5 Juegos', 'Videojuego THE WALKING DEAD COLLECTION Para Xbox One Totalmente nuevo (Sellado) ¡Listo para envío!', 10, 2000, 'Telltale Games', 'China', 'gamer', 5),
(20, 'Halo Infinite Físico', 'CONVIÉRTETE. La legendaria saga Halo regresa con la campaña de Master Chief más amplia hasta la fecha y una experiencia multijugador gratuita revolucionaria.', 15, 1500, 'Xbox One', 'China', 'gamer', 5),
(21, 'Control joystick ACCO Brands PowerA Enhanced Wired Controller for Xbox One black', 'Compatible con: Xbox One y Televisores. Incluye un control. Con sistema de vibración incorporado. Cuenta con 1 cable usb de 3 m y 1 manual.', 45, 700, 'Slang', 'China', 'gamer', 5),
(22, 'Xtreme Pc Amd Radeon Vega Ryzen 5 4650g 16gb Ssd 3tb Wifi', 'Gráficos AMD Radeon 7 Renoir con frecuencia de 1900MHz y 7 núcleos lo que proporciona un rendimiento rápido, sin interrupciones y fluido en los juegos que te apasionan, más potente de lo que crees.\r\n', 36, 18542, 'Xtreme Pc Gamer', 'China', 'ordenadores', 3),
(23, 'Mesa Gamer Balam Rush Olympus Rgb, 2*usb, portavasos, soportes', 'Estilo: Forma en Z Accesorios: Soporte para control, soporte para headset y portavasos Puertos USB: 2 * 2.0 (carga) Iluminación: RGB Dimensiones: 100 * 64 * 77 cm', 21, 5200, 'Balam Rush', 'China', 'gamer', 5),
(24, 'Hp Pavilion 17 Gamer Laptop Gtx 1660ti 16gb Ram 1tb', 'La laptop HP Pavilion Gaming 15-dk0005la es una solución tanto para trabajar y estudiar como para entretenerte.', 100, 34999, 'HP', 'China', 'ordenadores', 3),
(25, 'Tarjeta de video Nvidia GeForce\r\nRTX 30 Series RTX 3090 24GB', 'Interfaz PCI-Express 4.0.\r\nBus de memoria: 384bit.\r\nCantidad de núcleos: 10496.\r\nFrecuencia boost del núcleo de 1.7GHz y base de 1.4GHz.\r\nResolución máxima: 7680x4320.\r\nCompatible con directX y openGL.', 10, 63999, 'Nvidia', 'China', 'gamer', 5),
(26, 'Procesador gamer Intel Core i9- 10850K BX8070110850K de 10 núcleos y 5.2GHZ de frecuencia con gráfic', 'Ejecuta con rapidez y eficiencia cualquier tipo de programa sin afectar el funcionamiento total del dispositivo. Memoria caché de 20 MB, rápida y volátil.\r\nProcesador gráfico Intel UHD Graphics 630. Soporta memoria RAM DDR4. Su potencia es de 125 W.', 26, 11046, 'Intel', 'China', 'gamer', 5),
(27, 'Disco duro externo Seagate\r\nExpansion STEB1200040O 12TB\r\nnegro', 'Útil para guardar programas y documentos con su capacidad de 12 TB. Es compatible con Windows. Disco externo de escritorio. Interfaz de conexión: USB 3.0. Apto para PC y Laptop.', 14, 8389, 'Seagate', 'China', 'accesorios', 4),
(28, 'Monitor Gamer 23.8 Pulgadas 165hz 1080p Led Slim Curvo Xzeal', 'El monitor LED de XZEAL proporciona imágenes claras, nítidas y colores más vivos para una experiencia visual extraordinaria, además de ser una de las pocas líneas ultra slim del mercado.', 25, 4999, 'Xzeal', 'China', 'monitores', 1),
(29, 'Xtreme Pc Amd Radeon Renoir Ryzen 5 4650g 8gb Ssd 240gb Wifi', 'Gráficos AMD Radeon 7 Renoir con frecuencia de 1900MHz y 7 núcleos lo que proporciona un rendimiento rápido, sin interrupciones y fluido en los juegos que te apasionan, más potente de lo que crees.\r\n', 12, 8200, 'xtreme pc gamer', 'China', 'ordenadores', 3),
(30, 'Control joystick inalámbrico Sony PlayStation DualSense CFI-ZCT1 cosmic red', 'Cuenta con Bluetooth. Pantalla táctil. Mando inalámbrico. Compatible con: PlayStation 5. Incluye un control.', 8, 1549, 'Sony', 'China', 'gamer', 5);

-- =============================================
-- Estructura de tabla: carrito
-- =============================================
DROP TABLE IF EXISTS `carrito`;
CREATE TABLE `carrito` (
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL AUTO_INCREMENT,
  `cantidad_seleccionada` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_carrito`),
  KEY `carrito_FK_1` (`id_producto`),
  KEY `carrito_FK` (`id_usuario`),
  CONSTRAINT `carrito_FK` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carrito_FK_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Datos de tabla: carrito
-- =============================================
INSERT INTO `carrito` (`id_usuario`, `id_producto`, `id_carrito`, `cantidad_seleccionada`) VALUES
(27, 1, 49, 1);

-- =============================================
-- Estructura de tabla: historial_compras
-- =============================================
DROP TABLE IF EXISTS `historial_compras`;
CREATE TABLE `historial_compras` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad_comprada` int(11) DEFAULT NULL,
  `fecha_compra` date DEFAULT NULL,
  PRIMARY KEY (`id_historial`),
  KEY `historial_compras_FK_1` (`id_producto`),
  KEY `historial_compras_FK` (`id_usuario`),
  CONSTRAINT `historial_compras_FK` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `historial_compras_FK_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Datos de tabla: historial_compras
-- =============================================
INSERT INTO `historial_compras` (`id_historial`, `id_usuario`, `id_producto`, `cantidad_comprada`, `fecha_compra`) VALUES
(57, 1, 1, 1, '2022-05-17'),
(58, 1, 2, 3, '2022-05-17'),
(59, 1, 3, 2, '2022-05-17'),
(60, 1, 4, 1, '2022-05-17'),
(61, 1, 5, 25, '2022-05-17'),
(62, 2, 1, 1, '2022-05-17'),
(63, 2, 2, 1, '2022-05-17'),
(64, 24, 1, 2, '2024-12-14'),
(65, 24, 5, 1, '2024-12-14'),
(66, 26, 3, 1, '2025-05-27'),
(67, 26, 3, 1, '2025-05-30'),
(68, 26, 2, 1, '2025-05-30'),
(69, 27, 3, 1, '2025-05-30'),
(70, 26, 3, 1, '2025-06-25');

-- =============================================
-- Configuración de AUTO_INCREMENT
-- =============================================
ALTER TABLE `administradores` AUTO_INCREMENT = 2;
ALTER TABLE `carrito` AUTO_INCREMENT = 50;
ALTER TABLE `categorias` AUTO_INCREMENT = 6;
ALTER TABLE `historial_compras` AUTO_INCREMENT = 71;
ALTER TABLE `producto` AUTO_INCREMENT = 31;
ALTER TABLE `usuario` AUTO_INCREMENT = 28;

-- =============================================
-- Restaurar configuración SQL
-- =============================================
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =============================================
-- FIN DEL RESPALDO
-- =============================================