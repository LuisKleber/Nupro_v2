-- NUPRO+ V2 — Schema MySQL 8.0
-- Charset: utf8mb4 | Collation: utf8mb4_unicode_ci

SET NAMES utf8mb4;
SET time_zone = '-03:00';

CREATE DATABASE IF NOT EXISTS nupro_v2
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE nupro_v2;

-- ─────────────────────────────────────────
--  USERS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  email      VARCHAR(180) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       ENUM('admin','coordenacao','pm','gm','saude','psicologia','assistencia','diretoria') NOT NULL,
  active     TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
--  VICTIMS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS victims (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  full_name         VARCHAR(200) NOT NULL,
  cpf               VARCHAR(14),
  birth_date        DATE,
  address_encrypted TEXT,
  phone_encrypted   TEXT,
  is_confidential   TINYINT(1) DEFAULT 0,
  created_by        INT,
  created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
--  OCCURRENCES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS occurrences (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  victim_id    INT NOT NULL,
  title        VARCHAR(255) NOT NULL,
  description  TEXT,
  risk_level   ENUM('A','B','C') NOT NULL DEFAULT 'B',
  region       VARCHAR(120),
  incident_date DATE,
  latitude     DECIMAL(10,7),
  longitude    DECIMAL(10,7),
  created_by   INT,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (victim_id)  REFERENCES victims(id)  ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
--  SUPPORT FOLLOWUPS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS support_followups (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  occurrence_id  INT NOT NULL,
  followup_date  DATE,
  organization   VARCHAR(200),
  notes          TEXT,
  created_by     INT,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (occurrence_id) REFERENCES occurrences(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by)    REFERENCES users(id)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
--  HEALTH VISITS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS health_visits (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  occurrence_id  INT NOT NULL,
  visit_date     DATE,
  unit_name      VARCHAR(200),
  report         TEXT,
  created_by     INT,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (occurrence_id) REFERENCES occurrences(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by)    REFERENCES users(id)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
--  PROTECTIVE MEASURES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS protective_measures (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  occurrence_id    INT NOT NULL,
  measure_type     VARCHAR(200),
  issued_date      DATE,
  expiry_date      DATE,
  status           ENUM('ativa','expirada','revogada') DEFAULT 'ativa',
  breach_reported  TINYINT(1) DEFAULT 0,
  notes            TEXT,
  created_by       INT,
  created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (occurrence_id) REFERENCES occurrences(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by)    REFERENCES users(id)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
--  PANIC ALERTS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS panic_alerts (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  victim_id   INT NOT NULL,
  location    VARCHAR(255),
  latitude    DECIMAL(10,7),
  longitude   DECIMAL(10,7),
  resolved    TINYINT(1) DEFAULT 0,
  triggered_by INT,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (victim_id)    REFERENCES victims(id) ON DELETE CASCADE,
  FOREIGN KEY (triggered_by) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
--  AUDIT LOGS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_logs (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT,
  action      ENUM('LOGIN','LOGOUT','CREATE','UPDATE','DELETE','VIEW') NOT NULL,
  module_name VARCHAR(80),
  description TEXT,
  ip_address  VARCHAR(45),
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
--  SEED — Usuários de demonstração
--  Senha de todos: nupro123
-- ─────────────────────────────────────────
INSERT INTO users (name, email, password, role) VALUES
('Admin Sistema',        'admin@nupro.local',       '$2y$12$KOVKXBnj0pP0uPVL7cAaxODiGKdFBupBPAWH.yx.K1mN4nMuBb0yy', 'admin'),
('Coordenadora Ana',     'coord@nupro.local',        '$2y$12$KOVKXBnj0pP0uPVL7cAaxODiGKdFBupBPAWH.yx.K1mN4nMuBb0yy', 'coordenacao'),
('Agente PM Silva',      'pm@nupro.local',           '$2y$12$KOVKXBnj0pP0uPVL7cAaxODiGKdFBupBPAWH.yx.K1mN4nMuBb0yy', 'pm'),
('Dra. Beatriz Saúde',   'saude@nupro.local',        '$2y$12$KOVKXBnj0pP0uPVL7cAaxODiGKdFBupBPAWH.yx.K1mN4nMuBb0yy', 'saude'),
('Psi. Carla Mendes',    'psico@nupro.local',        '$2y$12$KOVKXBnj0pP0uPVL7cAaxODiGKdFBupBPAWH.yx.K1mN4nMuBb0yy', 'psicologia'),
('Assist. Social Diana', 'assistencia@nupro.local',  '$2y$12$KOVKXBnj0pP0uPVL7cAaxODiGKdFBupBPAWH.yx.K1mN4nMuBb0yy', 'assistencia'),
('Diretor Eduardo',      'diretoria@nupro.local',    '$2y$12$KOVKXBnj0pP0uPVL7cAaxODiGKdFBupBPAWH.yx.K1mN4nMuBb0yy', 'diretoria');

-- SEED — Vítimas de demonstração
INSERT INTO victims (full_name, cpf, birth_date, is_confidential, created_by) VALUES
('Maria Souza',    '111.111.111-11', '1990-03-15', 0, 1),
('Ana Lima',       '222.222.222-22', '1985-07-22', 1, 1),
('Joana Ferreira', '333.333.333-33', '1998-11-01', 0, 2);

-- SEED — Ocorrências
INSERT INTO occurrences (victim_id, title, description, risk_level, region, incident_date, latitude, longitude, created_by) VALUES
(1, 'Agressão física na residência',    'Vítima relatou agressão pelo companheiro.', 'A', 'Centro',       '2025-06-10', -22.4350, -46.9520, 3),
(2, 'Ameaças por mensagens',            'Recebeu mensagens ameaçadoras do ex.',       'B', 'Zona Norte',   '2025-06-12', -22.4200, -46.9400, 3),
(3, 'Violência psicológica continuada', 'Situação de isolamento forçado.',             'B', 'Zona Sul',     '2025-06-14', -22.4450, -46.9650, 3),
(1, 'Descumprimento de medida protetiva','Agressor se aproximou da vítima.',           'A', 'Centro',       '2025-06-18', -22.4355, -46.9525, 3);

-- SEED — Medidas protetivas
INSERT INTO protective_measures (occurrence_id, measure_type, issued_date, expiry_date, status, breach_reported, created_by) VALUES
(1, 'Afastamento do lar / proibição de contato', '2025-06-11', '2025-12-11', 'ativa', 1, 1),
(2, 'Proibição de aproximação (300m)',            '2025-06-13', '2025-12-13', 'ativa', 0, 1);

-- SEED — Alertas de pânico
INSERT INTO panic_alerts (victim_id, location, latitude, longitude, resolved, triggered_by) VALUES
(1, 'Rua das Flores, 123 — Centro, Mogi Mirim', -22.4350, -46.9520, 0, 3);
