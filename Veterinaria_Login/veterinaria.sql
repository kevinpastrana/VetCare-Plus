-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-12-2025 a las 19:57:00
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `veterinaria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `id_cargo` int(10) UNSIGNED NOT NULL,
  `nombre_cargo` varchar(50) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`id_cargo`, `nombre_cargo`, `descripcion`, `estado`) VALUES
(1, 'Veterinario', 'Profesional encargado de la atención médica de animales', 'Activo'),
(2, 'Asistente Veterinario', 'Apoyo en procedimientos y cuidado de animales', 'Activo'),
(3, 'Recepcionista', 'Atención al cliente y gestión de citas', 'Activo'),
(4, 'Administrador', 'Gestión administrativa del sistema', 'Activo'),
(5, 'Guarda De Seguridad', 'Las responsabilidades comun y corrientes de un guardia de seguridad 24/7', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consulta`
--

CREATE TABLE `consulta` (
  `id_consulta` int(10) UNSIGNED NOT NULL,
  `fecha_consulta` date NOT NULL,
  `hora_consulta` time DEFAULT NULL,
  `motivo` varchar(200) NOT NULL,
  `sintomas` text DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `tratamiento` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `proxima_cita` date DEFAULT NULL,
  `id_mascota` int(10) UNSIGNED NOT NULL,
  `id_empleado` int(10) UNSIGNED NOT NULL,
  `estado` enum('Pendiente','En Proceso','Completada','Cancelada') NOT NULL DEFAULT 'Completada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consulta`
--

INSERT INTO `consulta` (`id_consulta`, `fecha_consulta`, `hora_consulta`, `motivo`, `sintomas`, `diagnostico`, `tratamiento`, `observaciones`, `proxima_cita`, `id_mascota`, `id_empleado`, `estado`) VALUES
(3, '2025-11-28', '18:00:00', 'Indigestion', 'Indigestion por comer', 'Comer poquito', 'No darle de jartar', 'Ninguna', '2025-12-12', 5, 1, 'Completada'),
(4, '2025-11-28', '20:50:00', 'Anemia', 'Anemia', 'Anemia', 'Darle agua', 'Ninguna', '2025-12-12', 8, 1, 'Pendiente'),
(5, '2025-11-28', '00:43:00', 'come mucho y se vomita', 'mucha barriga', 'comer poquito', 'comer a ciertas horas', '', NULL, 9, 1, 'Completada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_factura`
--

CREATE TABLE `detalle_factura` (
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `id_factura` int(10) UNSIGNED NOT NULL,
  `concepto` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_factura`
--

INSERT INTO `detalle_factura` (`id_detalle`, `id_factura`, `concepto`, `descripcion`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(27, 10, 'Consulta', '', 1, 15000.00, 15000.00),
(28, 11, 'Consulta', '', 1, 20000.00, 20000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dueno`
--

CREATE TABLE `dueno` (
  `id_dueno` int(10) UNSIGNED NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `cedula` varchar(20) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('M','F','Otro') NOT NULL,
  `ciudad` varchar(50) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `ocupacion` varchar(50) DEFAULT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo_electronico` varchar(100) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dueno`
--

INSERT INTO `dueno` (`id_dueno`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `cedula`, `fecha_nacimiento`, `genero`, `ciudad`, `direccion`, `ocupacion`, `telefono`, `correo_electronico`, `fecha_registro`) VALUES
(7, 'Kevin', '', 'Pastrana', '', '1076500116', '2003-10-30', 'M', 'Neiva', 'Amaranto', 'Estudiante', '3232848544', 'kevin.felipe@hotmail.com', '2025-11-26 20:02:03'),
(8, 'Leider', '', 'Fabian', '', '1087600227', '2005-06-15', 'M', 'Garzon', 'Santa Ines', 'Estudiante', '3011234567', 'leider.fabian@hotmail.com', '2025-11-26 20:10:11'),
(9, 'David', '', 'Jorda', '', '1057500126', '2000-06-05', 'M', 'España', 'Lloret de Mar', 'Estudiante', '3101234567', 'david.jorda@hotmail.com', '2025-11-27 16:11:08'),
(10, 'Pau', '', 'Molet', '', '1054158117', '1999-01-05', 'M', 'España', 'España', 'Ing', '3018481481', 'molet@hotmail.com', '2025-11-28 02:47:55'),
(11, 'Maria', '', 'Pastrana', '', '123456789', '1999-05-12', 'F', 'Neiva', 'Amaranto', 'Estudiante', '3101234567', 'maria-pas@hotmail.com', '2025-11-28 23:38:53'),
(12, 'Rubiela', '', 'Soto', '', '2041724812', '2003-10-30', 'F', 'Neiva', 'Amaranto', 'Pensionada', '3331248124', 'rubielasoto@hotmail.com', '2025-11-28 23:47:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `id_empleado` int(10) UNSIGNED NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo_electronico` varchar(100) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `fecha_contratacion` date NOT NULL,
  `id_cargo` int(10) UNSIGNED NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`id_empleado`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `cedula`, `telefono`, `correo_electronico`, `direccion`, `fecha_nacimiento`, `fecha_contratacion`, `id_cargo`, `password`, `estado`) VALUES
(1, 'Admin', NULL, 'Sistema', NULL, '12345', '3001234567', 'admin', 'Sistema', '1990-01-01', '2024-01-01', 1, '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lk.QdZNlX0Yi', 'activo'),
(7, 'Sara', '', 'Llanos', '', '2058400446', '3221234567', 'llanos.sara@hotmail.com', 'Manzanares 3', '2006-12-16', '0000-00-00', 3, '$2y$10$dCSZj/hfZv1ZVRh20k6EbeuT9ZRXX.VrTJPkL/1oM0UG4CMcITXSK', 'inactivo'),
(8, 'David', '', 'Molet', '', '1241241623', '3221864623', 'david.jorda@hotmail.com', 'España lloret de mar', '2003-05-24', '2025-11-27', 1, '$2y$10$Fo7CHN7c/XJXr/L/n8YoHuMQRyreGOdarcv.ADMSZm04Wd8GvtcuS', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `id_factura` int(10) UNSIGNED NOT NULL,
  `numero_factura` varchar(20) NOT NULL,
  `fecha_factura` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `metodo_pago` enum('Efectivo','Tarjeta','Transferencia','Otro') NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `impuesto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `pagado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_consulta` int(10) UNSIGNED DEFAULT NULL,
  `id_dueno` int(10) UNSIGNED NOT NULL,
  `descripcion` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('Pendiente','Pagada','Vencida','Anulada') NOT NULL DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`id_factura`, `numero_factura`, `fecha_factura`, `fecha_vencimiento`, `metodo_pago`, `subtotal`, `descuento`, `impuesto`, `total`, `pagado`, `saldo`, `id_consulta`, `id_dueno`, `descripcion`, `observaciones`, `estado`) VALUES
(10, 'FAC-0001', '2025-11-28', NULL, 'Efectivo', 15000.00, 0.00, 5000.00, 20000.00, 0.00, 20000.00, 3, 7, NULL, NULL, 'Vencida'),
(11, 'FAC-0002', '2025-11-28', NULL, 'Efectivo', 20000.00, 0.00, 0.00, 20000.00, 0.00, 20000.00, NULL, 12, NULL, NULL, 'Pagada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_medico`
--

CREATE TABLE `historial_medico` (
  `id_historial` int(10) UNSIGNED NOT NULL,
  `id_mascota` int(10) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('Consulta','Procedimiento','Vacuna','Observación') NOT NULL,
  `descripcion` text NOT NULL,
  `diagnostico` text DEFAULT NULL,
  `tratamiento` text DEFAULT NULL,
  `id_empleado` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascota`
--

CREATE TABLE `mascota` (
  `id_mascota` int(10) UNSIGNED NOT NULL,
  `id_dueno` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('Macho','Hembra','Otro') NOT NULL,
  `especie` varchar(50) NOT NULL,
  `raza` varchar(50) DEFAULT NULL,
  `peso` decimal(6,2) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `antecedentes` text DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_ultima_visita` date DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Fallecido') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mascota`
--

INSERT INTO `mascota` (`id_mascota`, `id_dueno`, `nombre`, `foto`, `fecha_nacimiento`, `genero`, `especie`, `raza`, `peso`, `color`, `diagnostico`, `antecedentes`, `fecha_ingreso`, `fecha_ultima_visita`, `estado`) VALUES
(5, 7, 'Timbikis', NULL, '2023-11-27', 'Hembra', 'Gata', 'Siamés', 6.00, 'Blanca con manchas grises', 'La gata se encuentra bien de salud', 'El unico antecedente que tiene es la esterilizacion', '2025-11-25', '2025-11-27', 'Activo'),
(6, 7, 'Fruna', NULL, '2011-05-15', 'Hembra', 'Perra', 'Pincher', 5.00, 'Negra con café', 'Se encuentra anemica, pero esta buena de salud', 'Ninguno', '2025-05-25', '2025-11-27', 'Fallecido'),
(7, 9, 'Rocky', NULL, '2023-04-25', 'Macho', 'Perro', 'Labrador', 12.00, 'Negro', 'Ninguno', 'Ninguno', '2025-11-27', '2025-11-27', 'Activo'),
(8, 8, 'Luna', NULL, '2024-10-05', 'Hembra', 'Gata', 'Siamés', 6.00, 'Blanca', 'Ninguno', 'Ninguno', '2025-11-25', '2025-11-27', 'Activo'),
(9, 11, 'Timo', NULL, '2025-10-03', 'Macho', 'Perro', 'Pug', 1.00, 'gris', '', '', '2025-11-28', '2025-11-28', 'Activo'),
(10, 12, 'Fico', NULL, '2025-06-25', 'Macho', 'Perro', 'Labrador', 3.00, 'Negro con cafe', '', '', '2025-11-28', '2025-11-28', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procedimiento`
--

CREATE TABLE `procedimiento` (
  `id_procedimiento` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('Cirugía','Tratamiento','Diagnóstico','Preventivo') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `patologia` text DEFAULT NULL,
  `historial_medico` text DEFAULT NULL,
  `esterilizacion` tinyint(1) NOT NULL DEFAULT 0,
  `diagnostico` text DEFAULT NULL,
  `antecedentes` text DEFAULT NULL,
  `fecha_procedimiento` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `id_mascota` int(10) UNSIGNED NOT NULL,
  `id_empleado` int(10) UNSIGNED NOT NULL,
  `estado` enum('Programado','En Proceso','Completado','Cancelado') NOT NULL DEFAULT 'Completado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `procedimiento`
--

INSERT INTO `procedimiento` (`id_procedimiento`, `nombre`, `tipo`, `descripcion`, `patologia`, `historial_medico`, `esterilizacion`, `diagnostico`, `antecedentes`, `fecha_procedimiento`, `hora_inicio`, `hora_fin`, `costo`, `id_mascota`, `id_empleado`, `estado`) VALUES
(1, 'Cirugia', 'Cirugía', 'Sacada de glandulas mamarias', 'Ninguna', 'Sacada de glandulas mamarias', 1, 'Normal, se saco las glandulas y ya', 'Ninguno', '2025-11-27', '10:00:00', '12:00:00', 50000.00, 5, 1, 'Completado'),
(2, 'Timbikis', 'Preventivo', 'Procedimineto preventivo', 'Procedimineto preventivo', 'Procedimineto preventivo', 0, 'Procedimineto preventivo', 'Procedimineto preventivo', '2025-11-28', '15:00:00', '17:00:00', 25000.00, 5, 1, 'En Proceso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacuna`
--

CREATE TABLE `vacuna` (
  `id_vacuna` int(10) UNSIGNED NOT NULL,
  `id_mascota` int(10) UNSIGNED NOT NULL,
  `nombre_vacuna` varchar(100) NOT NULL,
  `laboratorio` varchar(100) DEFAULT NULL,
  `lote` varchar(50) DEFAULT NULL,
  `dosis` varchar(50) NOT NULL,
  `via_administracion` enum('Subcutánea','Intramuscular','Oral','Intranasal') DEFAULT 'Subcutánea',
  `fecha_aplicacion` date NOT NULL,
  `proxima_aplicacion` date DEFAULT NULL,
  `id_empleado` int(10) UNSIGNED DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vacuna`
--

INSERT INTO `vacuna` (`id_vacuna`, `id_mascota`, `nombre_vacuna`, `laboratorio`, `lote`, `dosis`, `via_administracion`, `fecha_aplicacion`, `proxima_aplicacion`, `id_empleado`, `observaciones`) VALUES
(2, 5, 'Rabia', 'Humane', 'H148HAS1', '1ml', 'Intramuscular', '2025-11-28', NULL, 1, 'Ninguna reaccion, todo normal');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_empleados_completa`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_empleados_completa` (
`id_empleado` int(10) unsigned
,`nombre_completo` varchar(101)
,`cedula` varchar(20)
,`telefono` varchar(20)
,`correo_electronico` varchar(100)
,`fecha_contratacion` date
,`estado` enum('activo','inactivo')
,`nombre_cargo` varchar(50)
,`descripcion_cargo` varchar(200)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_mascotas_completa`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_mascotas_completa` (
`id_mascota` int(10) unsigned
,`nombre_mascota` varchar(50)
,`especie` varchar(50)
,`raza` varchar(50)
,`genero` enum('Macho','Hembra','Otro')
,`peso` decimal(6,2)
,`fecha_nacimiento` date
,`fecha_ingreso` date
,`fecha_ultima_visita` date
,`estado_mascota` enum('Activo','Inactivo','Fallecido')
,`edad_anos` bigint(21)
,`id_dueno` int(10) unsigned
,`nombre_dueno` varchar(101)
,`cedula_dueno` varchar(20)
,`telefono_dueno` varchar(20)
,`correo_dueno` varchar(100)
,`ciudad` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_empleados_completa`
--
DROP TABLE IF EXISTS `vista_empleados_completa`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_empleados_completa`  AS SELECT `e`.`id_empleado` AS `id_empleado`, concat(`e`.`primer_nombre`,' ',`e`.`primer_apellido`) AS `nombre_completo`, `e`.`cedula` AS `cedula`, `e`.`telefono` AS `telefono`, `e`.`correo_electronico` AS `correo_electronico`, `e`.`fecha_contratacion` AS `fecha_contratacion`, `e`.`estado` AS `estado`, `c`.`nombre_cargo` AS `nombre_cargo`, `c`.`descripcion` AS `descripcion_cargo` FROM (`empleado` `e` join `cargo` `c` on(`e`.`id_cargo` = `c`.`id_cargo`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_mascotas_completa`
--
DROP TABLE IF EXISTS `vista_mascotas_completa`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_mascotas_completa`  AS SELECT `m`.`id_mascota` AS `id_mascota`, `m`.`nombre` AS `nombre_mascota`, `m`.`especie` AS `especie`, `m`.`raza` AS `raza`, `m`.`genero` AS `genero`, `m`.`peso` AS `peso`, `m`.`fecha_nacimiento` AS `fecha_nacimiento`, `m`.`fecha_ingreso` AS `fecha_ingreso`, `m`.`fecha_ultima_visita` AS `fecha_ultima_visita`, `m`.`estado` AS `estado_mascota`, timestampdiff(YEAR,`m`.`fecha_nacimiento`,curdate()) AS `edad_anos`, `d`.`id_dueno` AS `id_dueno`, concat(`d`.`primer_nombre`,' ',`d`.`primer_apellido`) AS `nombre_dueno`, `d`.`cedula` AS `cedula_dueno`, `d`.`telefono` AS `telefono_dueno`, `d`.`correo_electronico` AS `correo_dueno`, `d`.`ciudad` AS `ciudad` FROM (`mascota` `m` join `dueno` `d` on(`m`.`id_dueno` = `d`.`id_dueno`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id_cargo`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `consulta`
--
ALTER TABLE `consulta`
  ADD PRIMARY KEY (`id_consulta`),
  ADD KEY `idx_mascota` (`id_mascota`),
  ADD KEY `idx_empleado` (`id_empleado`),
  ADD KEY `idx_fecha` (`fecha_consulta`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_factura` (`id_factura`);

--
-- Indices de la tabla `dueno`
--
ALTER TABLE `dueno`
  ADD PRIMARY KEY (`id_dueno`),
  ADD UNIQUE KEY `uk_cedula` (`cedula`),
  ADD UNIQUE KEY `uk_correo` (`correo_electronico`),
  ADD KEY `idx_nombres` (`primer_nombre`,`primer_apellido`),
  ADD KEY `idx_ciudad` (`ciudad`),
  ADD KEY `idx_fecha_registro` (`fecha_registro`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `uk_cedula` (`cedula`),
  ADD UNIQUE KEY `uk_correo` (`correo_electronico`),
  ADD KEY `idx_cargo` (`id_cargo`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_nombres` (`primer_nombre`,`primer_apellido`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id_factura`),
  ADD UNIQUE KEY `uk_numero_factura` (`numero_factura`),
  ADD KEY `idx_consulta` (`id_consulta`),
  ADD KEY `idx_dueno` (`id_dueno`),
  ADD KEY `idx_fecha` (`fecha_factura`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_mascota` (`id_mascota`),
  ADD KEY `idx_empleado` (`id_empleado`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `mascota`
--
ALTER TABLE `mascota`
  ADD PRIMARY KEY (`id_mascota`),
  ADD KEY `idx_dueno` (`id_dueno`),
  ADD KEY `idx_nombre` (`nombre`),
  ADD KEY `idx_especie` (`especie`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_ultima_visita` (`fecha_ultima_visita`);

--
-- Indices de la tabla `procedimiento`
--
ALTER TABLE `procedimiento`
  ADD PRIMARY KEY (`id_procedimiento`),
  ADD KEY `idx_mascota` (`id_mascota`),
  ADD KEY `idx_empleado` (`id_empleado`),
  ADD KEY `idx_fecha` (`fecha_procedimiento`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `vacuna`
--
ALTER TABLE `vacuna`
  ADD PRIMARY KEY (`id_vacuna`),
  ADD KEY `idx_mascota` (`id_mascota`),
  ADD KEY `idx_empleado` (`id_empleado`),
  ADD KEY `idx_fecha_aplicacion` (`fecha_aplicacion`),
  ADD KEY `idx_proxima_aplicacion` (`proxima_aplicacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id_cargo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `consulta`
--
ALTER TABLE `consulta`
  MODIFY `id_consulta` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `dueno`
--
ALTER TABLE `dueno`
  MODIFY `id_dueno` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id_empleado` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `id_factura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  MODIFY `id_historial` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mascota`
--
ALTER TABLE `mascota`
  MODIFY `id_mascota` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `procedimiento`
--
ALTER TABLE `procedimiento`
  MODIFY `id_procedimiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vacuna`
--
ALTER TABLE `vacuna`
  MODIFY `id_vacuna` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `consulta`
--
ALTER TABLE `consulta`
  ADD CONSTRAINT `fk_consulta_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consulta_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascota` (`id_mascota`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD CONSTRAINT `fk_detalle_factura` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD CONSTRAINT `fk_empleado_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_consulta` FOREIGN KEY (`id_consulta`) REFERENCES `consulta` (`id_consulta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_dueno` FOREIGN KEY (`id_dueno`) REFERENCES `dueno` (`id_dueno`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD CONSTRAINT `fk_historial_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascota` (`id_mascota`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `mascota`
--
ALTER TABLE `mascota`
  ADD CONSTRAINT `fk_mascota_dueno` FOREIGN KEY (`id_dueno`) REFERENCES `dueno` (`id_dueno`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `procedimiento`
--
ALTER TABLE `procedimiento`
  ADD CONSTRAINT `fk_procedimiento_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_procedimiento_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascota` (`id_mascota`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vacuna`
--
ALTER TABLE `vacuna`
  ADD CONSTRAINT `fk_vacuna_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vacuna_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascota` (`id_mascota`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
