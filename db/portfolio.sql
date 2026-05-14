-- ═══════════════════════════════════════════════════════════
-- portfolio.sql — Emine Kurucu Portfolio Database
-- Import:  phpMyAdmin > Import > select this file
--          or: mysql -u if0_41915907 -p if0_41915907_eminekurucu_portfolio < portfolio.sql
-- ═══════════════════════════════════════════════════════════

-- ── Table: projects ───────────────────────────────────────
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(255)  NOT NULL,
  `description` TEXT          NOT NULL,
  `tags`        VARCHAR(500)  DEFAULT '',
  `github_url`  VARCHAR(500)  DEFAULT '',
  `demo_url`    VARCHAR(500)  DEFAULT '',
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table: contacts ───────────────────────────────────────
DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(255) NOT NULL,
  `email`      VARCHAR(255) NOT NULL,
  `subject`    VARCHAR(255) NOT NULL,
  `message`    TEXT         NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Sample project data ───────────────────────────────────
INSERT INTO `projects` (`title`, `description`, `tags`, `github_url`, `demo_url`) VALUES
(
  'Classical Computer Vision vs. YOLOv8',
  'Comparative analysis of color/shape-based classical methods against a YOLOv8 deep learning model on live camera feed, evaluating accuracy, speed, and condition dependency.',
  'Python, OpenCV, YOLOv8',
  'https://github.com/EmineKurucu',
  ''
),
(
  'E-Commerce Product Listing Platform',
  'Dynamic product listing with JSON-based data and real-time pricing from an external API. Popularity and price-based sorting. Backend on Render, frontend on Vercel.',
  'React, JavaScript, Node.js, Express.js',
  'https://github.com/EmineKurucu',
  '#'
),
(
  'Creuf — Corporate Website',
  'Multi-page corporate website built during an internship, featuring a homepage, contact page, product listing, and product detail pages.',
  'HTML, CSS',
  'https://github.com/EmineKurucu',
  '#'
);
