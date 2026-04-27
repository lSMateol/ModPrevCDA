CREATE TABLE `config` (
  `idcof` bigint(20) NOT NULL,
  `nomcof` varchar(150) DEFAULT NULL,
  `nitcof` varchar(12) DEFAULT NULL,
  `dircof` varchar(150) DEFAULT NULL,
  `telcof` varchar(12) DEFAULT NULL,
  `logcof` varchar(255) DEFAULT NULL,
  `emacof` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `diag` (
  `iddia` bigint(20) NOT NULL,
  `fecdia` datetime DEFAULT NULL,
  `idpun` bigint(20) DEFAULT NULL,
  `idveh` bigint(20) DEFAULT NULL,
  `aprobado` tinyint(1) DEFAULT NULL,
  `idper` bigint(20) DEFAULT NULL,
  `fecvig` datetime DEFAULT NULL,
  `idmaq` bigint(20) NOT NULL,
  `kilomt` bigint(20) DEFAULT NULL,
  `idinsp` bigint(20) DEFAULT NULL,
  `iding` bigint(20) DEFAULT NULL,
  `dpiddia` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `diapar` (
  `iddia` bigint(20) DEFAULT NULL,
  `idpar` bigint(20) DEFAULT NULL,
  `idper` bigint(20) DEFAULT NULL,
  `valor` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `dominio` (
  `iddom` bigint(20) NOT NULL,
  `nomdom` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `empresa` (
  `idemp` bigint(20) NOT NULL,
  `nonitem` varchar(12) DEFAULT NULL,
  `razsoem` varchar(150) DEFAULT NULL,
  `direm` varchar(150) DEFAULT NULL,
  `telem` varchar(10) DEFAULT NULL,
  `emaem` varchar(150) DEFAULT NULL,
  `nomger` varchar(70) DEFAULT NULL,
  `codcons` varchar(50) DEFAULT NULL,
  `codubi` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `foto` (
  `idfot` bigint(20) NOT NULL,
  `iddia` bigint(20) DEFAULT NULL,
  `rutafoto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `mantenimiento` (
  `idmant` int(11) NOT NULL,
  `idveh` bigint(20) NOT NULL,
  `idemp` bigint(20) NOT NULL,
  `descrip` longtext NOT NULL,
  `fechareg` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechamant` date NOT NULL,
  `fecnot` date DEFAULT NULL,
  `rutafact` varchar(200) NOT NULL,
  `valormant` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `maquina` (
  `idmaq` bigint(20) NOT NULL,
  `idpun` bigint(20) DEFAULT NULL,
  `ipmaq` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `marca` (
  `idmar` bigint(20) NOT NULL,
  `nommarlin` varchar(150) DEFAULT NULL,
  `depmar` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pagina` (
  `idpag` bigint(20) NOT NULL,
  `nompag` varchar(255) DEFAULT NULL,
  `rutpag` varchar(255) DEFAULT NULL,
  `mospag` tinyint(1) DEFAULT NULL,
  `ordpag` int(11) DEFAULT NULL,
  `icopag` varchar(255) DEFAULT NULL,
  `despag` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pagper` (
  `idpag` bigint(20) DEFAULT NULL,
  `idpef` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `param` (
  `idpar` bigint(20) NOT NULL,
  `nompar` varchar(100) DEFAULT NULL,
  `idtip` bigint(20) DEFAULT NULL,
  `rini` double(6,2) DEFAULT NULL,
  `rfin` double(6,2) DEFAULT NULL,
  `control` varchar(50) DEFAULT NULL,
  `nomcampo` varchar(30) DEFAULT NULL,
  `unipar` varchar(50) DEFAULT NULL,
  `colum` int(11) DEFAULT NULL,
  `actpar` tinyint(1) NOT NULL DEFAULT 1,
  `can` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `perfil` (
  `idpef` bigint(20) NOT NULL,
  `nompef` varchar(255) DEFAULT NULL,
  `pagpri` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `persona` (
  `idper` bigint(20) NOT NULL,
  `ndocper` bigint(20) DEFAULT NULL,
  `tdocper` bigint(20) NOT NULL,
  `nomper` varchar(50) NOT NULL,
  `apeper` varchar(50) NOT NULL,
  `dirper` varchar(150) DEFAULT NULL,
  `telper` varchar(10) NOT NULL,
  `codubi` bigint(20) NOT NULL,
  `idpef` bigint(20) NOT NULL,
  `pass` varchar(50) DEFAULT NULL,
  `emaper` varchar(60) NOT NULL,
  `idemp` bigint(20) DEFAULT NULL,
  `nliccon` varchar(20) DEFAULT NULL,
  `fvencon` date DEFAULT NULL,
  `catcon` bigint(20) DEFAULT NULL,
  `actper` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `proveh` (
  `idveh` bigint(20) DEFAULT NULL,
  `idper` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `punaten` (
  `idpun` bigint(20) NOT NULL,
  `nompa` varchar(150) DEFAULT NULL,
  `nitpa` varchar(12) DEFAULT NULL,
  `dirpa` varchar(150) DEFAULT NULL,
  `telpa` varchar(12) DEFAULT NULL,
  `encarpa` varchar(100) DEFAULT NULL,
  `firmapa` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tippar` (
  `idtip` bigint(20) NOT NULL,
  `nomtip` varchar(70) DEFAULT NULL,
  `tittip` varchar(150) DEFAULT NULL,
  `idpef` bigint(20) DEFAULT NULL,
  `acttip` tinyint(1) NOT NULL DEFAULT 1,
  `icotip` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ubica` (
  `codubi` bigint(20) NOT NULL,
  `nomubi` varchar(255) DEFAULT NULL,
  `depubi` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `valor` (
  `idval` bigint(20) NOT NULL,
  `iddom` bigint(20) DEFAULT NULL,
  `nomval` varchar(100) DEFAULT NULL,
  `parval` varchar(100) DEFAULT NULL,
  `actval` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `vehiculo` (
  `idveh` bigint(20) NOT NULL,
  `nordveh` varchar(30) DEFAULT NULL,
  `tipoveh` int(11) NOT NULL,
  `placaveh` varchar(6) NOT NULL,
  `linveh` bigint(20) NOT NULL,
  `modveh` int(11) NOT NULL,
  `paiveh` varchar(150) NOT NULL DEFAULT 'COLOMBIA',
  `fmatv` date DEFAULT NULL,
  `idemp` bigint(20) DEFAULT NULL,
  `capveh` int(11) DEFAULT NULL,
  `clveh` bigint(20) NOT NULL,
  `crgveh` bigint(20) DEFAULT 91,
  `combuveh` bigint(20) NOT NULL,
  `cilveh` int(11) DEFAULT NULL,
  `lictraveh` varchar(15) DEFAULT NULL,
  `colveh` varchar(30) DEFAULT NULL,
  `nmotveh` varchar(30) DEFAULT NULL,
  `tmotveh` bigint(20) DEFAULT 101,
  `nchaveh` varchar(30) DEFAULT NULL,
  `taroperveh` varchar(15) DEFAULT NULL,
  `radaccveh` varchar(255) DEFAULT NULL,
  `fecexpr` date DEFAULT NULL,
  `fecvenr` date DEFAULT NULL,
  `soat` varchar(15) DEFAULT NULL,
  `fecvens` date DEFAULT NULL,
  `extcontveh` varchar(15) DEFAULT NULL,
  `fecvene` date DEFAULT NULL,
  `cactveh` varchar(15) DEFAULT NULL,
  `fecvenc` date DEFAULT NULL,
  `tecmecveh` varchar(15) DEFAULT NULL,
  `fecvent` date DEFAULT NULL,
  `polaveh` tinyint(1) NOT NULL DEFAULT 1,
  `blinveh` tinyint(1) NOT NULL DEFAULT 2,
  `prop` bigint(20) DEFAULT NULL,
  `cond` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

