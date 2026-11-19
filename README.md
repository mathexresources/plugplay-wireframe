# PlugPlay.cz – e-shop s autoelektronikou do starších aut

> **Školní projekt** do předmětu *Malé a střední podnikání*. E-shop je plně funkční včetně administrace,
> ale nejde o skutečný obchod – nic se neprodává ani neplatí. Platba kartou končí stránkou
> „Tady by byl paywall“ místo platební brány.

Postaveno na **Laravel 13** (PHP 8.3+, v Dockeru nginx + PHP-FPM), Blade + Bootstrap 5 (vše přibalené v `public/vendor`, žádný build ani CDN),
databáze **SQLite** (výchozí) nebo **MariaDB/MySQL**. Vychází z wireframu ve složce [`wireframe/`](wireframe/).

## Demo účty (po `db:seed`)

| Role | E-mail | Heslo |
|---|---|---|
| Správce (administrace `/admin`) | `admin@plugplay.cz` | `admin123` |
| Zákazník (garáž, objednávky…) | `petr@example.cz` | `heslo123` |

Slevové kódy: `SKOLA10` (−10 %), `MINUS200` (−200 Kč od 1 500 Kč), `FOTKY10`.

## Spuštění na Raspberry Pi (Docker)

```bash
git clone https://github.com/mathexresources/plugplay-wireframe.git plugplay && cd plugplay
docker compose up -d --build        # první build na RPi trvá pár minut
# → http://<ip-raspberry>:8080
```

* Databáze (SQLite) a nahrané obrázky jsou v Docker volumech `plugplay-db` a `plugplay-storage`, přežijí rebuild.
* Při prvním startu se spustí migrace a naplní se ukázková data. Reset dat:
  `docker compose exec app php artisan migrate:fresh --seed --force`.
* Za reverzní proxy (nginx, Cloudflare Tunnel…) nastav v `docker-compose.yml` `APP_URL` na veřejnou adresu.
* Varianta s MariaDB je v `docker-compose.yml` zakomentovaná.

## Spuštění lokálně (bez Dockeru)

```bash
composer run setup      # composer install, .env, klíč, SQLite, migrace + seed, storage:link
php artisan serve       # → http://localhost:8000
```

E-maily (potvrzení objednávky, změna stavu, zapomenuté heslo) se neodesílají, zapisují se do `storage/logs/laravel.log`
(`MAIL_MAILER=log`). Pro skutečné odesílání stačí nastavit SMTP v `.env`.

## Co umí

**Obchod (podle wireframu)**
* Výběr auta (značka → model → karoserie), celý web se mu přizpůsobí; průvodce v 5 krocích.
* Stránka modelu `/auto/{model}` – schéma auta s klikacími zónami, filtr „co od auta čekáš“ a náročnost montáže,
  řazení, hotové sady pro model, SEO text.
* Klasický katalog: strom kategorií, filtry (auto, cena, náročnost, výrobce, skladem, video, sleva), řazení, mřížka/seznam, stránkování.
* Detail produktu: kompatibilita s autem / ověření pro jiný model, náročnost, čas montáže, nářadí (lze rovnou přikoupit),
  varianty, test redakce, návod, parametry, recenze (filtr „jen moje auto“, psaní recenzí s moderací), cross-sell, Schema.org.
* Plán úprav (košík) jako časová osa montáže s tipy, souhrn nářadí, slevové kódy, doprava zdarma od limitu,
  uložení a sdílení plánu odkazem.
* Objednávka na jedné stránce: kontakt, doprava (výdejní místo / adresa / osobní odběr), platba (karta → paywall,
  QR platba s generovaným QR kódem, dobírka), založení účtu při objednávce, potvrzovací e-mail.
* Děkovná stránka se stavem objednávky, návody k zakoupeným věcem, nářadím a založením účtu pro hosty.
* Účet: garáž s více auty (fotka, výbava, VIN, aktivní auto, namontované upgrady a postup), objednávky,
  moje návody (pamatují si krok), uložené plány, údaje, adresy, změna hesla, zrušení účtu. Zapomenuté heslo.
* Návody krok za krokem, hledání, statické stránky, kontaktní formulář s přílohou, „Před a po montáži“ (nahrávání fotek).
* XML feed pro srovnávače (formát Heureka): `/feed/heureka.xml`.

**Administrace `/admin`**
* Přehled: tržby, průměrná objednávka, graf tržeb za 30 dní, stavy objednávek, nejprodávanější, docházející zásoby, úkoly k vyřízení.
* Objednávky: filtry, detail, změna stavu + e-mail zákazníkovi, platba, číslo zásilky, poznámka, tisk, export CSV.
* Produkty (vč. fotek, kompatibility s auty, nářadí, variant), kategorie, sady, modely aut, zóny schématu, nářadí,
  návody (editor kroků), stránky, slevové kódy, doprava, platby, nastavení obchodu.
* Zákazníci (detail, garáž, přidělení práv správce), moderace recenzí a fotek, zprávy z kontaktního formuláře.

## Struktura

* `app/Http/Controllers` – obchod, `Admin/` – administrace (`CrudController` = společný základ pro číselníky)
* `app/Support/Cart.php` – košík/plán v session, `CarContext.php` – aktuálně zvolené auto
* `database/seeders/DatabaseSeeder.php` – ukázková data
* `resources/views/shop`, `account`, `admin` – šablony; `public/css/app.css` – vzhled
