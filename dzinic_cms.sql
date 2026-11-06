-- =====================================================================
--  Džinić CMS — baza podataka, deo 1: PROIZVODI
--  MySQL 8 / MariaDB 10.4+
--  Tabele: kategorije, proizvodi
-- =====================================================================

--  Uvoz: u phpMyAdmin levo izaberite bazu rapicdevrs_data,
--  pa Import → ovaj fajl. Baza se ne pravi ovde, već u cPanelu.

USE rapicdevrs_data;

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
--  Kategorije (Sveže povrće, Zimnica i prerađevine)
-- ---------------------------------------------------------------------
CREATE TABLE kategorije (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  naziv       VARCHAR(100)  NOT NULL,
  slug        VARCHAR(100)  NOT NULL,
  slika       VARCHAR(255)  NULL,                -- putanja do slike, npr. 'uploads/paprika.jpg'
  redosled    SMALLINT      NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kategorije_slug (slug)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Proizvodi
-- ---------------------------------------------------------------------
CREATE TABLE proizvodi (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  kategorija_id INT UNSIGNED   NOT NULL,
  naziv         VARCHAR(150)   NOT NULL,
  opis          VARCHAR(255)   NULL,
  cena          DECIMAL(10,2)  NOT NULL DEFAULT 0,       -- RSD po jedinici
  jedinica      VARCHAR(10)    NOT NULL DEFAULT 'kg',    -- kg, kom, tegla, flaša
  kolicina      DECIMAL(10,2)  NOT NULL DEFAULT 0,       -- dostupna količina
  dostupan      TINYINT(1)     NOT NULL DEFAULT 1,       -- 1 = Dostupan, 0 = Nedostupan
  slika         VARCHAR(255)   NULL,
  redosled      SMALLINT       NOT NULL DEFAULT 0,
  kreirano      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  izmenjeno     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_proizvodi_kategorija (kategorija_id),
  CONSTRAINT fk_proizvodi_kategorija
    FOREIGN KEY (kategorija_id) REFERENCES kategorije (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_proizvodi_jedinica CHECK (jedinica IN ('kg','kom','tegla','flaša')),
  CONSTRAINT chk_proizvodi_cena     CHECK (cena >= 0),
  CONSTRAINT chk_proizvodi_kolicina CHECK (kolicina >= 0)
) ENGINE=InnoDB;


-- =====================================================================
--  POČETNI PODACI
--  Slike: otpremite fotografije u folder 'uploads/' na serveru.
-- =====================================================================

INSERT INTO kategorije (id, naziv, slug, slika, redosled) VALUES
  (1, 'Sveže povrće',          'sveze-povrce', 'uploads/paprika.jpg', 1),
  (2, 'Zimnica i prerađevine', 'zimnica',      'uploads/ajvar.jpg',   2);

INSERT INTO proizvodi (kategorija_id, naziv, opis, cena, jedinica, kolicina, dostupan, slika, redosled) VALUES
  (1, 'Paradajz',         'Volovsko srce i okrugli, zreo na stablu.',          150, 'kg',     120, 1, 'uploads/paradajz.jpg',     1),
  (1, 'Paprika',          'Babura, šilja i kapija za ajvar i pečenje.',         180, 'kg',      80, 1, 'uploads/paprika.jpg',      2),
  (1, 'Krastavac',        'Salatar i kornišon za kiseljenje.',                  120, 'kg',      75, 1, 'uploads/krastavac.jpg',    3),
  (1, 'Kupus',            'Glavičar za salatu i kiseljenje.',                    90, 'kg',     150, 1, 'uploads/kupus.jpg',        4),
  (1, 'Krompir',          'Domaći, pogodan za sva jela.',                       100, 'kg',     300, 1, 'uploads/krompir.jpg',      5),
  (1, 'Patlidžan',        'Za pečenje, pohovanje i zimnicu.',                   150, 'kg',      40, 1, 'uploads/patlidzan.jpg',    6),
  (1, 'Tikvica',          'Nežna, za pohovanje, čorbe i punjenje.',             120, 'kg',      60, 1, 'uploads/tikvica.jpg',      7),
  (1, 'Kukuruz šećerac',  'Sladak i sočan, za kuvanje i pečenje.',               60, 'kom',    200, 1, 'uploads/kukuruz.jpg',      8),
  (2, 'Ajvar',            'Pečena paprika, ručno ljuštena. Blagi i ljuti.',     600, 'tegla',   28, 1, 'uploads/ajvar.jpg',        1),
  (2, 'Turšija',          'Mešana: paprika, krastavčići, karfiol, šargarepa.',  500, 'tegla',   20, 1, 'uploads/tursija.jpg',      2),
  (2, 'Pinđur',           'Pečena paprika i paradajz sa belim lukom.',          550, 'tegla',    0, 0, 'uploads/pindjur.jpg',      3),
  (2, 'Kiseli kupus',     'Glavice kiseljene u buradima, na stari način.',      150, 'kg',      90, 1, 'uploads/kiseli-kupus.jpg', 4),
  (2, 'Sok od paradajza', 'Gusti sok za čorbe, sosove i pasulj.',               350, 'flaša',   35, 1, 'uploads/sok.jpg',          5),
  (2, 'Cvekla u tegli',   'Kuvana, rendana ili na kolutove, sa renom.',         300, 'tegla',   24, 1, 'uploads/cvekla.jpg',       6);


-- =====================================================================
--  UPITI KOJE CMS KORISTI  (? = vrednost koju šalje aplikacija)
--  U PHP-u ih uvek izvršavajte kao pripremljene upite (PDO prepare),
--  nikad spajanjem teksta — tako je baza zaštićena od SQL injekcije.
-- =====================================================================

-- Kartice kategorija sa brojem proizvoda
-- SELECT k.id, k.naziv, k.slika, COUNT(p.id) AS broj_proizvoda
-- FROM kategorije k LEFT JOIN proizvodi p ON p.kategorija_id = k.id
-- GROUP BY k.id ORDER BY k.redosled;

-- Lista proizvoda izabrane kategorije
-- SELECT id, naziv, cena, jedinica, kolicina, dostupan, slika
-- FROM proizvodi WHERE kategorija_id = ? ORDER BY redosled, naziv;

-- Izmena dostupnosti, cene i količine (polje koje se otvori klikom na proizvod)
-- UPDATE proizvodi SET dostupan = ?, cena = ?, kolicina = ? WHERE id = ?;

-- Dodavanje proizvoda (forma "Dodaj povrće / Dodaj zimnicu")
-- INSERT INTO proizvodi (kategorija_id, naziv, cena, jedinica, kolicina, dostupan, slika)
-- VALUES (?, ?, ?, ?, ?, ?, ?);

-- Brisanje proizvoda (posle potvrde "Da li ste sigurni?")
-- DELETE FROM proizvodi WHERE id = ?;
