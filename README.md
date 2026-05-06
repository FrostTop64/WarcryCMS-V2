# ![logo](https://i.ibb.co/RpgfR7Wd/Warcry-CMS-Png.png)

Warcry CMS is a modern content management system for World of Warcraft private servers (AzerothCore).
This version has been cleaned, fixed, and updated for better stability and performance.

![logo](https://raw.githubusercontent.com/azerothcore/azerothcore.github.io/master/images/logo-github.png)

[AzerothCore](https://github.com/azerothcore/azerothcore-wotlk)
---
![logo](https://cdn.prod.website-files.com/6257adef93867e50d84d30e2/67d00cf7266d2c75571aebde_Example.svg)

[Join our Discord](https://discord.gg/RcBduqxGG5)

## 🚀 Features

* WoW database integration (Auth, Characters, World)
* In-game store system
* Account management panel
* Store activity tracking
* Dynamic item icons (Wowhead integration)
* Clean Warcry UI theme

---

## 🆕 Improvements (2026)

### 🧹 Cleanup & Optimization

* Removed unused and legacy files
* Cleaned outdated 2017 code
* Improved structure and readability

### ⚙️ PHP Fixes

* Fixed PHP 8 warnings
* Fixed deprecated functions
* Improved stability across pages

### 🛒 Store Activity

* Fixed errors on activity page
* Removed broken clickable item links
* Improved display and data handling

### 🔒 Security

* Prevented access to invalid pages
* Fixed directory listing issues

---

## 🆕 New Content

### 👤 Profile Page

* Custom user profile system
* Displays player/account information
* Integrated with database

### ⚔️ Armory Page

* Custom Armory system
* Displays characters, items, and visuals
* Improved item icon handling

### 🛠️ Admin Panel (In Development)

* Administrative control panel
* Future management tools for:

  * Users
  * Store
  * Website content

---

## 📦 Installation (classic XAMPP/WAMP)

1. Place the CMS inside a folder named:

```
warcry
```

2. Import SQL files from:

```
/_SQL&Guide
```

3. Configure your database in:

```
config.php
```

4. Launch your website.

---

## 🐳 Docker (easy mode — no install needed)

Install [Docker Desktop](https://www.docker.com/products/docker-desktop/). That's it.

### Start the site

Open a terminal in this folder. Run:

```bash
docker compose up --build -d
```

Wait ~1 minute. Open: **http://localhost:8080**

That's the site. Done.

### Edit code

1. Edit any `.php` / CSS / JS file in your editor.
2. Refresh your browser.
3. Changes show up. No restart needed.

### Stop the site

```bash
docker compose down
```

### Start it again later

```bash
docker compose up -d
```

(no `--build` — only needed the very first time.)

### Look at the database

http://localhost:8081 → server `db`, user `warcry`, pass `warcry`

### Something broke?

```bash
docker compose logs web      # see error messages
```

### Start completely fresh (wipes DB)

```bash
docker compose down -v
docker compose up -d
```

More detail: [`docker/README.md`](docker/README.md).

---

## 🧾 Requirements

**Docker route (recommended):**

* Docker Desktop (Windows/macOS) or Docker Engine (Linux) with Compose v2

**Classic install:**

* PHP 8.0+
* MySQL / MariaDB
* Apache / Nginx
* AzerothCore server

---

## 🛠️ Customization

* Fully customizable via `/template/`
* Easy to modify UI and features

---

## 👤 Author
Warcry main project creator 
Chompi coded it, EvilSystem designed (2017)


Updated/Fix & Maintained by **Frost_Top** (2026)

---

## 📄 License

MIT License

---
