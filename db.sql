-- ============================================================
--  GESTIONE CENTRO SPORTIVO - Database Completo
--  Compatibile con MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

DROP DATABASE IF EXISTS centro_sportivo;
CREATE DATABASE centro_sportivo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE centro_sportivo;

-- -----------------------------------------------------------
-- TABELLA: CENTRI
-- -----------------------------------------------------------
CREATE TABLE CENTRI (
  idCentro     INT AUTO_INCREMENT PRIMARY KEY,
  nomeCentro   VARCHAR(100) NOT NULL,
  citta        VARCHAR(60)  NOT NULL,
  indirizzo    VARCHAR(150) NOT NULL,
  tipologia    ENUM('Palestra','Piscina','Polifunzionale','Wellness','CrossFit') NOT NULL,
  direttore    VARCHAR(100) NOT NULL,
  email        VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

INSERT INTO CENTRI (nomeCentro, citta, indirizzo, tipologia, direttore, email) VALUES
('AquaSport Arena',      'Milano',  'Via Mecenate 76',           'Polifunzionale', 'Roberto Ferretti',    'info@aquasport.it'),
('FitLife Torino',       'Torino',  'Corso Francia 215',         'Palestra',       'Giulia Marchetti',   'info@fitlife-to.it'),
('SportCenter Roma Est', 'Roma',    'Via Prenestina 890',        'Polifunzionale', 'Marco Conti',        'info@sportcenterroma.it'),
('BodyZen Napoli',       'Napoli',  'Via Caracciolo 14',         'Wellness',       'Francesca Esposito', 'info@bodyzen-na.it'),
('ActiveGym Firenze',    'Firenze', 'Viale dei Mille 55',        'CrossFit',       'Luca Fontana',       'info@activegym-fi.it'),
('AquaFun Bologna',      'Bologna', 'Via dell\'Arcoveggio 52',   'Piscina',        'Sara Lombardi',      'info@aquafun-bo.it');

-- -----------------------------------------------------------
-- TABELLA: CORSI
-- -----------------------------------------------------------
CREATE TABLE CORSI (
  idCorso          INT AUTO_INCREMENT PRIMARY KEY,
  idCentro         INT          NOT NULL,
  nomeCorso        VARCHAR(100) NOT NULL,
  istruttore       VARCHAR(100) NOT NULL,
  categoria        ENUM('Acqua','Fitness','Arti Marziali','Danza','Yoga','Forza','Outdoor') NOT NULL,
  quotaMensile     DECIMAL(8,2) NOT NULL,
  maxPartecipanti  INT          NOT NULL,
  attivo           TINYINT(1)   NOT NULL DEFAULT 1,
  FOREIGN KEY (idCentro) REFERENCES CENTRI(idCentro) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO CORSI (idCentro, nomeCorso, istruttore, categoria, quotaMensile, maxPartecipanti, attivo) VALUES
(1, 'Nuoto Adulti',         'Elena Mancini',     'Acqua',          55.00, 20, 1),
(1, 'Acquagym Mattutina',   'Davide Riva',        'Acqua',          45.00, 15, 1),
(1, 'Spinning Avanzato',    'Marco Bianchi',      'Fitness',        60.00, 18, 1),
(1, 'CrossFit Open',        'Cristian Negri',     'Forza',          75.00, 12, 1),
(2, 'Yoga Hatha',           'Marta Colombo',      'Yoga',           50.00, 15, 1),
(2, 'Pilates Mat',          'Alessia Ferrari',    'Fitness',        55.00, 14, 1),
(2, 'Zumba Fitness',        'Carmen Lopez',       'Danza',          40.00, 25, 1),
(2, 'Kettlebell Training',  'Filippo Galli',      'Forza',          65.00, 10, 1),
(3, 'Karate Shotokan',      'Sensei Tanaka',      'Arti Marziali',  50.00, 20, 1),
(3, 'Boxe Thai',            'Ahmed Bouazza',      'Arti Marziali',  60.00, 16, 1),
(3, 'Functional Training',  'Giorgia Palumbo',    'Fitness',        55.00, 18, 1),
(3, 'Hip-Hop Dance',        'Valentina Cruz',     'Danza',          45.00, 22, 1),
(4, 'Yoga Vinyasa',         'Priya Sharma',       'Yoga',           60.00, 12, 1),
(4, 'Stretching & Relax',   'Monica Gentile',     'Fitness',        35.00, 20, 1),
(4, 'Pilates Riformatore',  'Simona Vitale',      'Fitness',        70.00,  8, 1),
(5, 'CrossFit Level 1',     'Andrea Piras',       'Forza',          80.00, 15, 1),
(5, 'CrossFit Level 2',     'Andrea Piras',       'Forza',          90.00, 10, 1),
(5, 'Trail Running',        'Stefano Merlo',      'Outdoor',        45.00, 20, 1),
(6, 'Nuoto Bambini',        'Laura Grassi',       'Acqua',          50.00, 12, 1),
(6, 'Nuoto Agonistico',     'Riccardo Fabbri',    'Acqua',          65.00, 16, 0),
(6, 'Acquagym Serale',      'Giovanna Rizzi',     'Acqua',          45.00, 18, 1),
(2, 'Ginnastica Posturale', 'Francesca Valli',    'Fitness',        50.00, 15, 1);

-- -----------------------------------------------------------
-- TABELLA: ISCRITTI
-- -----------------------------------------------------------
CREATE TABLE ISCRITTI (
  idIscritto              INT AUTO_INCREMENT PRIMARY KEY,
  nome                    VARCHAR(60)  NOT NULL,
  cognome                 VARCHAR(60)  NOT NULL,
  dataNascita             DATE         NOT NULL,
  codiceFiscale           CHAR(16)     NOT NULL UNIQUE,
  telefono                VARCHAR(20)  NOT NULL,
  email                   VARCHAR(100) NOT NULL,
  dataCertificatoMedico   DATE         NOT NULL
) ENGINE=InnoDB;

INSERT INTO ISCRITTI (nome, cognome, dataNascita, codiceFiscale, telefono, email, dataCertificatoMedico) VALUES
('Luca',        'Bernardi',   '1990-03-15', 'BRNLCU90C15F205X', '333-1234567', 'luca.bernardi@email.it',    '2025-09-10'),
('Chiara',      'Moretti',    '1985-07-22', 'MRTCHR85L62F205Y', '347-2345678', 'chiara.moretti@email.it',   '2025-04-05'),
('Matteo',      'Russo',      '1993-11-08', 'RSSMTT93S08H501Z', '380-3456789', 'matteo.russo@email.it',     '2025-12-20'),
('Sofia',       'Romano',     '1988-04-30', 'RMNSFR88D70F205W', '391-4567890', 'sofia.romano@email.it',     '2024-11-15'),
('Alessandro',  'Colombo',    '1995-09-17', 'CLMLSS95P17F205V', '366-5678901', 'alex.colombo@email.it',     '2026-01-30'),
('Federica',    'Ricci',      '1992-06-03', 'RCCFDR92H43F205U', '349-6789012', 'fede.ricci@email.it',       '2025-07-22'),
('Davide',      'Esposito',   '1987-12-19', 'SPSDVD87T19F839T', '377-7890123', 'davide.esposito@email.it',  '2025-10-01'),
('Martina',     'Conti',      '1996-02-14', 'CNTMRT96B54F205S', '338-8901234', 'martina.conti@email.it',    '2026-03-15'),
('Simone',      'Ferretti',   '1991-08-25', 'FRRSMN91M25F205R', '348-9012345', 'simone.ferretti@email.it',  '2025-05-18'),
('Valentina',   'Bianchi',    '1989-05-11', 'BNCVNT89E51F205Q', '360-0123456', 'vale.bianchi@email.it',     '2024-12-31'),
('Francesco',   'Lombardi',   '1994-10-07', 'LMBFNC94R07F205P', '333-1122334', 'fra.lombardi@email.it',     '2025-08-09'),
('Elisa',       'Gallo',      '1986-01-28', 'GLLLSE86A68F205O', '347-2233445', 'elisa.gallo@email.it',      '2026-02-20'),
('Riccardo',    'Martini',    '1998-07-16', 'MRTRRD98L16F205N', '380-3344556', 'ric.martini@email.it',      '2025-11-11'),
('Giulia',      'Costa',      '1990-03-04', 'CSTGLI90C44F205M', '391-4455667', 'giulia.costa@email.it',     '2025-03-30'),
('Nicola',      'Mancini',    '1983-09-22', 'MNCNCL83P22F205L', '366-5566778', 'nicola.mancini@email.it',   '2024-10-05'),
('Paola',       'Greco',      '1997-04-13', 'GRCPLA97D53F205K', '349-6677889', 'paola.greco@email.it',      '2026-04-18'),
('Andrea',      'Fontana',    '1985-11-30', 'FNTNDR85S30F205J', '377-7788990', 'andrea.fontana@email.it',   '2025-06-07'),
('Cristina',    'Marchetti',  '1993-06-08', 'MRCCST93H48F205I', '338-8899001', 'cris.marchetti@email.it',   '2025-09-25'),
('Lorenzo',     'Palumbo',    '1999-02-21', 'PLMLNZ99B21F205H', '348-9900112', 'lore.palumbo@email.it',     '2026-01-12'),
('Giorgia',     'Rinaldi',    '1988-08-09', 'RNDGRG88M49F205G', '360-0011223', 'giorgia.rinaldi@email.it',  '2025-07-03'),
('Emanuele',    'Serra',      '1991-12-17', 'SRRMML91T17F205F', '333-1231231', 'emanuele.serra@email.it',   '2025-10-29'),
('Alessia',     'Vitale',     '1994-05-26', 'VTLLSS94E66F205E', '347-2342342', 'alessia.vitale@email.it',   '2024-09-14'),
('Daniele',     'Caruso',     '1987-10-03', 'CRSDNL87R03F205D', '380-3453453', 'daniele.caruso@email.it',   '2025-12-02'),
('Roberta',     'Ferrara',    '1995-03-19', 'FRRRRT95C59F205C', '391-4564564', 'roberta.ferrara@email.it',  '2026-02-08'),
('Marco',       'De Luca',    '1982-07-12', 'DLCMRC82L12F205B', '366-5675675', 'marco.deluca@email.it',     '2025-04-22');

-- -----------------------------------------------------------
-- TABELLA: ISCRIZIONI_CORSI
-- -----------------------------------------------------------
CREATE TABLE ISCRIZIONI_CORSI (
  idIscrizione         INT AUTO_INCREMENT PRIMARY KEY,
  idIscritto           INT         NOT NULL,
  idCorso              INT         NOT NULL,
  dataInizio           DATE        NOT NULL,
  dataScadenza         DATE        NOT NULL,
  pagamentoEffettuato  TINYINT(1)  NOT NULL DEFAULT 0,
  FOREIGN KEY (idIscritto) REFERENCES ISCRITTI(idIscritto) ON DELETE CASCADE,
  FOREIGN KEY (idCorso)    REFERENCES CORSI(idCorso)    ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO ISCRIZIONI_CORSI (idIscritto, idCorso, dataInizio, dataScadenza, pagamentoEffettuato) VALUES
(1,  1,  '2024-09-01', '2025-08-31', 1),
(1,  3,  '2025-01-15', '2026-01-14', 1),
(2,  5,  '2024-10-01', '2025-09-30', 1),
(2,  6,  '2025-02-01', '2026-01-31', 1),
(3,  9,  '2025-03-01', '2026-02-28', 1),
(3,  10, '2025-03-01', '2026-02-28', 1),
(4,  13, '2024-11-01', '2025-10-31', 1),
(4,  14, '2025-01-01', '2025-12-31', 1),
(5,  16, '2025-04-01', '2026-03-31', 1),
(5,  18, '2025-04-01', '2026-03-31', 1),
(6,  7,  '2025-01-10', '2026-01-09', 1),
(6,  22, '2025-03-15', '2026-03-14', 1),
(7,  2,  '2024-09-15', '2025-09-14', 1),
(7,  1,  '2025-02-01', '2026-01-31', 1),
(8,  5,  '2025-05-01', '2026-04-30', 1),
(8,  6,  '2025-05-01', '2026-04-30', 1),
(9,  4,  '2025-01-20', '2026-01-19', 1),
(9,  3,  '2025-01-20', '2026-01-19', 1),
(10, 13, '2024-12-01', '2025-11-30', 1),
(10, 15, '2025-03-01', '2026-02-28', 1),
(11, 11, '2025-02-15', '2026-02-14', 1),
(11, 12, '2025-02-15', '2026-02-14', 1),
(12, 19, '2025-04-10', '2026-04-09', 1),
(12, 21, '2025-04-10', '2026-04-09', 1),
(13, 16, '2025-06-01', '2026-05-31', 1),
(13, 17, '2025-06-01', '2026-05-31', 0),
(14, 8,  '2025-01-05', '2026-01-04', 1),
(14, 7,  '2025-01-05', '2026-01-04', 1),
(15, 9,  '2024-08-01', '2025-07-31', 1),
(16, 5,  '2025-03-20', '2026-03-19', 1),
(16, 14, '2025-03-20', '2026-03-19', 1),
(17, 4,  '2025-02-10', '2026-02-09', 1),
(18, 11, '2025-05-15', '2026-05-14', 1),
(19, 19, '2025-03-01', '2026-02-28', 1),
(20, 7,  '2025-04-20', '2026-04-19', 1),
(21, 3,  '2025-01-01', '2026-12-31', 1),
(22, 13, '2024-10-15', '2025-10-14', 1),
(23, 10, '2025-02-01', '2026-01-31', 1),
(24, 1,  '2025-04-01', '2026-03-31', 1),
(25, 16, '2025-05-01', '2026-04-30', 0),
(3,  4,  '2025-06-01', '2026-05-31', 1),
(6,  1,  '2025-03-01', '2026-02-28', 1),
(8,  7,  '2025-06-10', '2026-06-09', 1),
(10, 5,  '2025-05-01', '2026-04-30', 1),
(12, 5,  '2025-05-15', '2026-05-14', 1),
(14, 22, '2025-04-01', '2026-03-31', 1),
(20, 22, '2025-05-01', '2026-04-30', 1),
(1,  21, '2025-06-01', '2026-05-31', 1),
(9,  11, '2025-04-01', '2026-03-31', 1);

-- -----------------------------------------------------------
-- TABELLA: ACCESSI
-- -----------------------------------------------------------
CREATE TABLE ACCESSI (
  idAccesso          INT AUTO_INCREMENT PRIMARY KEY,
  idIscritto         INT          NOT NULL,
  dataOraIngresso    DATETIME     NOT NULL,
  temperaturaRilevata DECIMAL(4,1) NOT NULL,
  armadiettoAssegnato INT          NOT NULL,
  FOREIGN KEY (idIscritto) REFERENCES ISCRITTI(idIscritto) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Accessi distribuiti negli ultimi 60 giorni
INSERT INTO ACCESSI (idIscritto, dataOraIngresso, temperaturaRilevata, armadiettoAssegnato) VALUES
(1,  DATE_SUB(NOW(), INTERVAL 1  DAY) + INTERVAL '08:30:00' HOUR_SECOND, 36.4, 12),
(2,  DATE_SUB(NOW(), INTERVAL 1  DAY) + INTERVAL '09:15:00' HOUR_SECOND, 36.6, 34),
(3,  DATE_SUB(NOW(), INTERVAL 1  DAY) + INTERVAL '10:00:00' HOUR_SECOND, 36.3, 7),
(5,  DATE_SUB(NOW(), INTERVAL 1  DAY) + INTERVAL '17:45:00' HOUR_SECOND, 36.7, 22),
(6,  DATE_SUB(NOW(), INTERVAL 1  DAY) + INTERVAL '18:30:00' HOUR_SECOND, 36.5, 41),
(8,  DATE_SUB(NOW(), INTERVAL 2  DAY) + INTERVAL '07:00:00' HOUR_SECOND, 36.2, 15),
(9,  DATE_SUB(NOW(), INTERVAL 2  DAY) + INTERVAL '08:45:00' HOUR_SECOND, 36.8, 3),
(11, DATE_SUB(NOW(), INTERVAL 2  DAY) + INTERVAL '12:00:00' HOUR_SECOND, 36.4, 28),
(13, DATE_SUB(NOW(), INTERVAL 2  DAY) + INTERVAL '19:00:00' HOUR_SECOND, 36.6, 9),
(16, DATE_SUB(NOW(), INTERVAL 3  DAY) + INTERVAL '09:30:00' HOUR_SECOND, 36.3, 17),
(1,  DATE_SUB(NOW(), INTERVAL 3  DAY) + INTERVAL '11:00:00' HOUR_SECOND, 36.5, 12),
(4,  DATE_SUB(NOW(), INTERVAL 3  DAY) + INTERVAL '16:15:00' HOUR_SECOND, 36.4, 55),
(7,  DATE_SUB(NOW(), INTERVAL 4  DAY) + INTERVAL '08:00:00' HOUR_SECOND, 36.6, 8),
(10, DATE_SUB(NOW(), INTERVAL 4  DAY) + INTERVAL '10:30:00' HOUR_SECOND, 36.7, 30),
(12, DATE_SUB(NOW(), INTERVAL 4  DAY) + INTERVAL '17:00:00' HOUR_SECOND, 36.3, 44),
(14, DATE_SUB(NOW(), INTERVAL 4  DAY) + INTERVAL '19:30:00' HOUR_SECOND, 36.5, 6),
(18, DATE_SUB(NOW(), INTERVAL 5  DAY) + INTERVAL '09:00:00' HOUR_SECOND, 36.4, 23),
(20, DATE_SUB(NOW(), INTERVAL 5  DAY) + INTERVAL '11:45:00' HOUR_SECOND, 36.6, 37),
(2,  DATE_SUB(NOW(), INTERVAL 5  DAY) + INTERVAL '18:00:00' HOUR_SECOND, 36.5, 34),
(5,  DATE_SUB(NOW(), INTERVAL 6  DAY) + INTERVAL '07:30:00' HOUR_SECOND, 36.3, 22),
(3,  DATE_SUB(NOW(), INTERVAL 6  DAY) + INTERVAL '09:00:00' HOUR_SECOND, 36.7, 7),
(17, DATE_SUB(NOW(), INTERVAL 6  DAY) + INTERVAL '10:15:00' HOUR_SECOND, 36.4, 19),
(21, DATE_SUB(NOW(), INTERVAL 7  DAY) + INTERVAL '08:30:00' HOUR_SECOND, 36.6, 2),
(22, DATE_SUB(NOW(), INTERVAL 7  DAY) + INTERVAL '17:00:00' HOUR_SECOND, 36.5, 48),
(23, DATE_SUB(NOW(), INTERVAL 7  DAY) + INTERVAL '19:15:00' HOUR_SECOND, 36.3, 11),
(24, DATE_SUB(NOW(), INTERVAL 8  DAY) + INTERVAL '09:30:00' HOUR_SECOND, 36.7, 60),
(25, DATE_SUB(NOW(), INTERVAL 8  DAY) + INTERVAL '11:00:00' HOUR_SECOND, 36.4, 14),
(6,  DATE_SUB(NOW(), INTERVAL 8  DAY) + INTERVAL '18:30:00' HOUR_SECOND, 36.6, 41),
(8,  DATE_SUB(NOW(), INTERVAL 9  DAY) + INTERVAL '07:45:00' HOUR_SECOND, 36.3, 15),
(9,  DATE_SUB(NOW(), INTERVAL 9  DAY) + INTERVAL '09:00:00' HOUR_SECOND, 36.5, 3),
(1,  DATE_SUB(NOW(), INTERVAL 9  DAY) + INTERVAL '12:30:00' HOUR_SECOND, 36.4, 12),
(11, DATE_SUB(NOW(), INTERVAL 10 DAY) + INTERVAL '08:00:00' HOUR_SECOND, 36.6, 28),
(13, DATE_SUB(NOW(), INTERVAL 10 DAY) + INTERVAL '17:30:00' HOUR_SECOND, 36.7, 9),
(16, DATE_SUB(NOW(), INTERVAL 11 DAY) + INTERVAL '09:15:00' HOUR_SECOND, 36.3, 17),
(19, DATE_SUB(NOW(), INTERVAL 11 DAY) + INTERVAL '10:45:00' HOUR_SECOND, 36.5, 33),
(4,  DATE_SUB(NOW(), INTERVAL 12 DAY) + INTERVAL '08:00:00' HOUR_SECOND, 36.4, 55),
(7,  DATE_SUB(NOW(), INTERVAL 12 DAY) + INTERVAL '19:00:00' HOUR_SECOND, 36.6, 8),
(10, DATE_SUB(NOW(), INTERVAL 13 DAY) + INTERVAL '11:00:00' HOUR_SECOND, 36.5, 30),
(14, DATE_SUB(NOW(), INTERVAL 13 DAY) + INTERVAL '18:15:00' HOUR_SECOND, 36.3, 6),
(2,  DATE_SUB(NOW(), INTERVAL 14 DAY) + INTERVAL '09:30:00' HOUR_SECOND, 36.7, 34),
(5,  DATE_SUB(NOW(), INTERVAL 14 DAY) + INTERVAL '10:00:00' HOUR_SECOND, 36.4, 22),
(20, DATE_SUB(NOW(), INTERVAL 15 DAY) + INTERVAL '07:30:00' HOUR_SECOND, 36.6, 37),
(12, DATE_SUB(NOW(), INTERVAL 15 DAY) + INTERVAL '17:45:00' HOUR_SECOND, 36.3, 44),
(3,  DATE_SUB(NOW(), INTERVAL 16 DAY) + INTERVAL '08:30:00' HOUR_SECOND, 36.5, 7),
(18, DATE_SUB(NOW(), INTERVAL 16 DAY) + INTERVAL '11:15:00' HOUR_SECOND, 36.4, 23),
(21, DATE_SUB(NOW(), INTERVAL 17 DAY) + INTERVAL '09:00:00' HOUR_SECOND, 36.7, 2),
(23, DATE_SUB(NOW(), INTERVAL 17 DAY) + INTERVAL '18:30:00' HOUR_SECOND, 36.5, 11),
(24, DATE_SUB(NOW(), INTERVAL 18 DAY) + INTERVAL '10:30:00' HOUR_SECOND, 36.3, 60),
(25, DATE_SUB(NOW(), INTERVAL 18 DAY) + INTERVAL '19:15:00' HOUR_SECOND, 36.6, 14),
(1,  DATE_SUB(NOW(), INTERVAL 19 DAY) + INTERVAL '08:00:00' HOUR_SECOND, 36.4, 12),
(6,  DATE_SUB(NOW(), INTERVAL 19 DAY) + INTERVAL '12:00:00' HOUR_SECOND, 36.5, 41),
(9,  DATE_SUB(NOW(), INTERVAL 20 DAY) + INTERVAL '07:45:00' HOUR_SECOND, 36.6, 3),
(11, DATE_SUB(NOW(), INTERVAL 20 DAY) + INTERVAL '17:00:00' HOUR_SECOND, 36.3, 28),
(16, DATE_SUB(NOW(), INTERVAL 21 DAY) + INTERVAL '09:30:00' HOUR_SECOND, 36.5, 17),
(22, DATE_SUB(NOW(), INTERVAL 22 DAY) + INTERVAL '10:15:00' HOUR_SECOND, 36.7, 48),
(8,  DATE_SUB(NOW(), INTERVAL 22 DAY) + INTERVAL '18:00:00' HOUR_SECOND, 36.4, 15),
(17, DATE_SUB(NOW(), INTERVAL 23 DAY) + INTERVAL '08:30:00' HOUR_SECOND, 36.6, 19),
(13, DATE_SUB(NOW(), INTERVAL 23 DAY) + INTERVAL '19:30:00' HOUR_SECOND, 36.3, 9),
(5,  DATE_SUB(NOW(), INTERVAL 24 DAY) + INTERVAL '07:00:00' HOUR_SECOND, 36.5, 22),
(2,  DATE_SUB(NOW(), INTERVAL 24 DAY) + INTERVAL '09:15:00' HOUR_SECOND, 36.4, 34),
-- Iscritti inattivi (ultimi accessi >30 giorni fa)
(15, DATE_SUB(NOW(), INTERVAL 35 DAY) + INTERVAL '10:00:00' HOUR_SECOND, 36.6, 50),
(4,  DATE_SUB(NOW(), INTERVAL 40 DAY) + INTERVAL '17:30:00' HOUR_SECOND, 36.3, 55),
(7,  DATE_SUB(NOW(), INTERVAL 45 DAY) + INTERVAL '09:00:00' HOUR_SECOND, 36.5, 8),
(10, DATE_SUB(NOW(), INTERVAL 50 DAY) + INTERVAL '11:00:00' HOUR_SECOND, 36.4, 30),
(19, DATE_SUB(NOW(), INTERVAL 55 DAY) + INTERVAL '08:30:00' HOUR_SECOND, 36.6, 33);

SET foreign_key_checks = 1;
