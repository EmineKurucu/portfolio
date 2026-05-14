-- ═══════════════════════════════════════════════════════════
-- portfolio_db.sql — Emine Kurucu Portfolio Veritabanı
-- phpMyAdmin veya MySQL CLI ile içe aktar:
--   mysql -u root -p < portfolio_db.sql
-- ═══════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS `portfolio_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `portfolio_db`;

-- ── Tablo: projects ───────────────────────────────────────
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

-- ── Tablo: contacts ───────────────────────────────────────
DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(255) NOT NULL,
  `email`      VARCHAR(255) NOT NULL,
  `subject`    VARCHAR(255) NOT NULL,
  `message`    TEXT         NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Örnek Proje Verileri ──────────────────────────────────
INSERT INTO `projects` (`title`, `description`, `tags`, `github_url`, `demo_url`) VALUES
(
  'Klasik Görüntü İşleme vs. YOLOv8',
  'Gerçek zamanlı kamera görüntüsü üzerinde renk ve şekil tabanlı klasik görüntü işleme teknikleri ile YOLOv8 tabanlı derin öğrenme modelini karşılaştırmalı olarak analiz ettim. İki yaklaşımın doğruluk, hız ve koşul bağımlılığı açısından performansını değerlendirdim.',
  'Python, OpenCV, YOLOv8',
  'https://github.com/EmineKurucu',
  ''
),
(
  'E-Ticaret Ürün Listeleme Platformu',
  'JSON tabanlı ürün verisi ve harici API\'den çekilen fiyat bilgisiyle dinamik ürün listeleme sayfası geliştirdim. Popülerlik ve fiyat bazlı sıralama özelliği ile kullanıcı deneyimini iyileştirdim. Backend\'i Render, frontend\'i Vercel üzerinde deploy ettim.',
  'React, JavaScript, Node.js, Express.js',
  'https://github.com/EmineKurucu',
  '#'
),
(
  'Creuf — Kurumsal Web Sitesi',
  'Staj sürecinde şirket için homepage, iletişim, ürün listeleme ve ürün detay sayfalarından oluşan çok sayfalı kurumsal web sitesi geliştirdim.',
  'HTML, CSS',
  'https://github.com/EmineKurucu',
  '#'
);