# Webshop Installatiehandleiding

## Vereisten
- XAMPP (Apache, MySQL, PHP)

## Installatiestappen

### 1. XAMPP Installatie
1. Download XAMPP vanaf: https://www.apachefriends.org/
2. Tijdens installatie: selecteer enkel **phpMyAdmin**, **PHP** (default) en **MySQL**
3. Kies een installatiefolder en noteer deze voor latere stappen
4. Voltooi de installatie

### 2. Webshop Klonen
1. Start XAMPP Control Panel
2. Navigeer naar: `xampp\htdocs`
3. Voer uit( in de map htdocs):  
   `git clone https://github.com/loveti84/Webshop.git`
4. De map Webshop staat nu in de map htdocs(waar ook dashboard staat)

### 3. Database Configuratie
1. Start **Apache** en **MySQL** via XAMPP Control Panel
2. Klik bij MySQL op **Admin** (opent phpMyAdmin)
3. Klik op het huisje-icoontje (eerste icoon onder phpMyAdmin)
4. Klik op "importeren"
5. selecteer het WebshopDump.sql  [webshopDump](/WebshopDump.sql)
6. druk op importeren,


### 4. Webshop Starten
1. Ga naar: http://localhost/webshop/
2. Fouten worden gelogd in xampp\apache\logs\error.log
3. Zou moeten werken, heb het zelf op 2 aparte pc getest

**Poortconfiguratie indien nodig**

1. Navigeer naar: `xampp/mysql/bin/my.ini`
2. Zoek en wijzig de `[client]` en `[mysqld]` secties
3. Herstart MySQL
4. Pas de poort aan in `.env` bestand in de root van webshop

Standaard .env instellingen (staan zo in de repositorie (mag eigenlijk niet)):
```
DB_HOST=localhost
DB_NAME=webshop
DB_USER=root
DB_PASS=
```