-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 02-09-2018 a las 16:35:23
-- Versión del servidor: 10.1.26-MariaDB-0+deb9u1
-- Versión de PHP: 7.0.30-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `panoraudio`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_area`
--

CREATE TABLE `at_area` (
  `idArea` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
  `area` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
  `latitudIzquierdaInferior` decimal(9,6) NOT NULL,
  `longitudIzquierdaInferior` decimal(9,6) NOT NULL,
  `latitudDerechaSuperior` decimal(9,6) NOT NULL,
  `longitudDerechaSuperior` decimal(9,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_audio`
--

CREATE TABLE `at_audio` (
  `idAudio` int(10) UNSIGNED NOT NULL,
  `idCategoria` int(10) UNSIGNED NOT NULL,
  `idUser` int(10) UNSIGNED DEFAULT NULL,
  `archivo` varchar(255) COLLATE utf8_spanish2_ci NOT NULL,
  `idIdiomaAudio` int(10) UNSIGNED NOT NULL,
  `latitud` decimal(9,6) NOT NULL,
  `longitud` decimal(9,6) NOT NULL,
  `bloqueado` tinyint(1) NOT NULL DEFAULT '0',
  `marca` tinyint(1) NOT NULL DEFAULT '0',
  `descripcion` varchar(255) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `descargas` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `idArea` varchar(255) COLLATE utf8_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_cachePuntos`
--

CREATE TABLE `at_cachePuntos` (
  `idAudio` int(10) UNSIGNED NOT NULL,
  `objeto` tinyint(1) NOT NULL DEFAULT '0',
  `resultado` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_categoria`
--

CREATE TABLE `at_categoria` (
  `idCategoria` int(10) UNSIGNED NOT NULL,
  `categoria` varchar(255) COLLATE utf8_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_comentario`
--

CREATE TABLE `at_comentario` (
  `idComentario` int(10) UNSIGNED NOT NULL,
  `idUser` int(10) UNSIGNED NOT NULL,
  `idAudio` int(10) UNSIGNED NOT NULL,
  `texto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_descargas`
--

CREATE TABLE `at_descargas` (
  `idAudio` int(10) UNSIGNED NOT NULL,
  `ip` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_idiomaAudio`
--

CREATE TABLE `at_idiomaAudio` (
  `idIdiomaAudio` int(10) UNSIGNED NOT NULL,
  `idioma` varchar(255) COLLATE utf8_spanish2_ci NOT NULL,
  `siglasIdioma` char(2) COLLATE utf8_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_inapropiado`
--

CREATE TABLE `at_inapropiado` (
  `idAudio` int(10) UNSIGNED NOT NULL,
  `idUser` int(10) UNSIGNED NOT NULL,
  `tipoDenuncia` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_prefCat`
--

CREATE TABLE `at_prefCat` (
  `idPreferencia` int(10) UNSIGNED NOT NULL,
  `idCategoria` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_preferencia`
--

CREATE TABLE `at_preferencia` (
  `idPreferencia` int(10) UNSIGNED NOT NULL,
  `puntuacionMinima` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `idUser` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_prefIdioma`
--

CREATE TABLE `at_prefIdioma` (
  `idIdiomaAudio` int(10) UNSIGNED NOT NULL,
  `idPreferencia` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_punto`
--

CREATE TABLE `at_punto` (
  `idPunto` int(10) UNSIGNED NOT NULL,
  `idRuta` int(10) UNSIGNED NOT NULL,
  `idAudio` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_puntuacion`
--

CREATE TABLE `at_puntuacion` (
  `idAudio` int(10) UNSIGNED NOT NULL,
  `idUser` int(10) UNSIGNED NOT NULL,
  `puntuacion` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_puntuacionRAltas`
--

CREATE TABLE `at_puntuacionRAltas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `puntuacion` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_puntuacionRBaixas`
--

CREATE TABLE `at_puntuacionRBaixas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `puntuacion` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_ruta`
--

CREATE TABLE `at_ruta` (
  `idRuta` int(10) UNSIGNED NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `idUser` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_seguimiento`
--

CREATE TABLE `at_seguimiento` (
  `idUserSeguido` int(10) UNSIGNED NOT NULL,
  `idUserSeguidor` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `at_user`
--

CREATE TABLE `at_user` (
  `idUser` int(10) UNSIGNED NOT NULL,
  `usuario` varchar(255) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `pass` char(104) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `activated` tinyint(1) NOT NULL,
  `activationCode` char(13) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `idFacebook` bigint(20) UNSIGNED DEFAULT NULL,
  `firebase` varchar(500) COLLATE utf8_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8_spanish_ci DEFAULT NULL,
  `body` text COLLATE utf8_spanish_ci NOT NULL,
  `url` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `class` varchar(45) COLLATE utf8_spanish_ci NOT NULL DEFAULT 'event-important',
  `start` varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  `end` varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  `inicio_normal` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `final_normal` varchar(50) COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `at_area`
--
ALTER TABLE `at_area`
  ADD PRIMARY KEY (`idArea`);

--
-- Indices de la tabla `at_audio`
--
ALTER TABLE `at_audio`
  ADD PRIMARY KEY (`idAudio`),
  ADD KEY `at_audio_ibfk_3` (`idIdiomaAudio`),
  ADD KEY `at_audio_ibfk_1` (`idCategoria`),
  ADD KEY `at_audio_ibfk_2` (`idUser`),
  ADD KEY `idArea` (`idArea`);

--
-- Indices de la tabla `at_cachePuntos`
--
ALTER TABLE `at_cachePuntos`
  ADD PRIMARY KEY (`idAudio`,`objeto`);

--
-- Indices de la tabla `at_categoria`
--
ALTER TABLE `at_categoria`
  ADD PRIMARY KEY (`idCategoria`);

--
-- Indices de la tabla `at_comentario`
--
ALTER TABLE `at_comentario`
  ADD PRIMARY KEY (`idComentario`),
  ADD KEY `idUser` (`idUser`),
  ADD KEY `idAudio` (`idAudio`);

--
-- Indices de la tabla `at_descargas`
--
ALTER TABLE `at_descargas`
  ADD KEY `idAudio` (`idAudio`);

--
-- Indices de la tabla `at_idiomaAudio`
--
ALTER TABLE `at_idiomaAudio`
  ADD PRIMARY KEY (`idIdiomaAudio`);

--
-- Indices de la tabla `at_inapropiado`
--
ALTER TABLE `at_inapropiado`
  ADD PRIMARY KEY (`idAudio`,`idUser`),
  ADD KEY `idUser` (`idUser`);

--
-- Indices de la tabla `at_prefCat`
--
ALTER TABLE `at_prefCat`
  ADD PRIMARY KEY (`idPreferencia`,`idCategoria`),
  ADD KEY `at_prefCat_ibfk_2` (`idCategoria`);

--
-- Indices de la tabla `at_preferencia`
--
ALTER TABLE `at_preferencia`
  ADD PRIMARY KEY (`idPreferencia`),
  ADD KEY `at_preferencia_ibfk_1` (`idUser`);

--
-- Indices de la tabla `at_prefIdioma`
--
ALTER TABLE `at_prefIdioma`
  ADD PRIMARY KEY (`idIdiomaAudio`,`idPreferencia`),
  ADD KEY `idPreferencia` (`idPreferencia`);

--
-- Indices de la tabla `at_punto`
--
ALTER TABLE `at_punto`
  ADD PRIMARY KEY (`idPunto`),
  ADD KEY `idRuta` (`idRuta`),
  ADD KEY `idAudio` (`idAudio`);

--
-- Indices de la tabla `at_puntuacion`
--
ALTER TABLE `at_puntuacion`
  ADD PRIMARY KEY (`idAudio`,`idUser`),
  ADD KEY `at_puntuacion_ibfk_2` (`idUser`);

--
-- Indices de la tabla `at_puntuacionRAltas`
--
ALTER TABLE `at_puntuacionRAltas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `at_puntuacionRBaixas`
--
ALTER TABLE `at_puntuacionRBaixas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `at_ruta`
--
ALTER TABLE `at_ruta`
  ADD PRIMARY KEY (`idRuta`),
  ADD KEY `idUser` (`idUser`);

--
-- Indices de la tabla `at_seguimiento`
--
ALTER TABLE `at_seguimiento`
  ADD PRIMARY KEY (`idUserSeguido`,`idUserSeguidor`),
  ADD KEY `idUserSeguidor` (`idUserSeguidor`);

--
-- Indices de la tabla `at_user`
--
ALTER TABLE `at_user`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `idFacebook` (`idFacebook`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `at_audio`
--
ALTER TABLE `at_audio`
  MODIFY `idAudio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15168;
--
-- AUTO_INCREMENT de la tabla `at_categoria`
--
ALTER TABLE `at_categoria`
  MODIFY `idCategoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT de la tabla `at_comentario`
--
ALTER TABLE `at_comentario`
  MODIFY `idComentario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
--
-- AUTO_INCREMENT de la tabla `at_idiomaAudio`
--
ALTER TABLE `at_idiomaAudio`
  MODIFY `idIdiomaAudio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT de la tabla `at_preferencia`
--
ALTER TABLE `at_preferencia`
  MODIFY `idPreferencia` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;
--
-- AUTO_INCREMENT de la tabla `at_punto`
--
ALTER TABLE `at_punto`
  MODIFY `idPunto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;
--
-- AUTO_INCREMENT de la tabla `at_ruta`
--
ALTER TABLE `at_ruta`
  MODIFY `idRuta` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT de la tabla `at_user`
--
ALTER TABLE `at_user`
  MODIFY `idUser` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=506;
--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `at_audio`
--
ALTER TABLE `at_audio`
  ADD CONSTRAINT `at_audio_ibfk_3` FOREIGN KEY (`idIdiomaAudio`) REFERENCES `at_idiomaAudio` (`idIdiomaAudio`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `at_audio_ibfk_4` FOREIGN KEY (`idUser`) REFERENCES `at_user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `at_audio_ibfk_5` FOREIGN KEY (`idArea`) REFERENCES `at_area` (`idArea`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_cachePuntos`
--
ALTER TABLE `at_cachePuntos`
  ADD CONSTRAINT `at_cachePuntos_ibfk_1` FOREIGN KEY (`idAudio`) REFERENCES `at_audio` (`idAudio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_comentario`
--
ALTER TABLE `at_comentario`
  ADD CONSTRAINT `at_comentario_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `at_user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `at_comentario_ibfk_2` FOREIGN KEY (`idAudio`) REFERENCES `at_audio` (`idAudio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_descargas`
--
ALTER TABLE `at_descargas`
  ADD CONSTRAINT `at_descargas_ibfk_1` FOREIGN KEY (`idAudio`) REFERENCES `at_audio` (`idAudio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_inapropiado`
--
ALTER TABLE `at_inapropiado`
  ADD CONSTRAINT `at_inapropiado_ibfk_1` FOREIGN KEY (`idAudio`) REFERENCES `at_audio` (`idAudio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `at_inapropiado_ibfk_2` FOREIGN KEY (`idUser`) REFERENCES `at_user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_prefCat`
--
ALTER TABLE `at_prefCat`
  ADD CONSTRAINT `at_prefCat_ibfk_1` FOREIGN KEY (`idPreferencia`) REFERENCES `at_preferencia` (`idPreferencia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `at_prefCat_ibfk_2` FOREIGN KEY (`idCategoria`) REFERENCES `at_categoria` (`idCategoria`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_preferencia`
--
ALTER TABLE `at_preferencia`
  ADD CONSTRAINT `at_preferencia_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `at_user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_prefIdioma`
--
ALTER TABLE `at_prefIdioma`
  ADD CONSTRAINT `at_prefIdioma_ibfk_1` FOREIGN KEY (`idIdiomaAudio`) REFERENCES `at_idiomaAudio` (`idIdiomaAudio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `at_prefIdioma_ibfk_2` FOREIGN KEY (`idPreferencia`) REFERENCES `at_preferencia` (`idPreferencia`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_punto`
--
ALTER TABLE `at_punto`
  ADD CONSTRAINT `at_punto_ibfk_1` FOREIGN KEY (`idRuta`) REFERENCES `at_ruta` (`idRuta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `at_punto_ibfk_2` FOREIGN KEY (`idAudio`) REFERENCES `at_audio` (`idAudio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_puntuacion`
--
ALTER TABLE `at_puntuacion`
  ADD CONSTRAINT `at_puntuacion_ibfk_1` FOREIGN KEY (`idAudio`) REFERENCES `at_audio` (`idAudio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `at_puntuacion_ibfk_2` FOREIGN KEY (`idUser`) REFERENCES `at_user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_ruta`
--
ALTER TABLE `at_ruta`
  ADD CONSTRAINT `at_ruta_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `at_user` (`idUser`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `at_seguimiento`
--
ALTER TABLE `at_seguimiento`
  ADD CONSTRAINT `at_seguimiento_ibfk_1` FOREIGN KEY (`idUserSeguido`) REFERENCES `at_user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `at_seguimiento_ibfk_2` FOREIGN KEY (`idUserSeguidor`) REFERENCES `at_user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
