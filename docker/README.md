# Docker setup

Run the whole site (PHP + Apache + MySQL) without installing anything on your
computer. Only Docker Desktop required.

---

## Just want to run it?

```bash
docker compose up --build -d
```

Wait ~1 minute. Open **http://localhost:8080**.

That's it.

---

## The 5 commands you actually need

| What you want                  | Command                          |
|--------------------------------|----------------------------------|
| Start the site                 | `docker compose up -d`           |
| Stop the site                  | `docker compose down`            |
| See errors                     | `docker compose logs web`        |
| Start fresh (wipes database)   | `docker compose down -v` then `docker compose up -d` |
| Check it's running             | `docker compose ps`              |

That's the whole daily flow. Skip the rest unless you hit a problem.

---

## Editing the site

1. Open any file in your editor (`.php`, CSS, JS, templates).
2. Save.
3. Refresh the browser.

You're done. The container reads files live from disk — no restart, no rebuild.

**Exception:** if you edit one of these, you need to rebuild:
- `Dockerfile`
- `docker/php/php.ini`
- `docker/apache/000-default.conf`

To rebuild:
```bash
docker compose up --build -d
```

---

## What's running

| Service  | URL                                  | What it is                |
|----------|--------------------------------------|---------------------------|
| Site     | http://localhost:8080                | The CMS                   |
| Admin    | http://localhost:8080/admin          | Admin panel               |
| Adminer  | http://localhost:8081                | Web tool to browse the DB |

For Adminer:
- System: `MySQL`
- Server: `db`
- User: `warcry`
- Password: `warcry`
- Database: `warcry`

---

## What got loaded into the database

First time the database boots, it auto-imports:

1. `_SQL&Guide/Warcry_Database.sql` — the full site DB
2. A small fake `auth` DB so the site doesn't crash (real one comes from your TrinityCore server)

These only run **once**. To re-run them, you have to delete the database first:

```bash
docker compose down -v       # the -v wipes the data
docker compose up -d         # boots fresh, re-imports SQL
```

---

## Common problems

### "Port is already allocated"

Something else on your computer uses port 8080, 8081, or 3307.

Fix: copy `.env.example` to `.env`, change the port number, run `docker compose up -d`.

### "White page" / 500 error

Check the logs:
```bash
docker compose logs web --tail 50
```

### Database empty / tables missing

The init scripts only run on a fresh DB. Reset it:
```bash
docker compose down -v
docker compose up -d
```

### I closed the terminal and the site went down

You ran without `-d` (foreground). Always use:
```bash
docker compose up -d
```

---

## Power-user stuff

Skip unless you need it.

```bash
docker compose logs -f web                                       # live tail logs
docker compose exec web bash                                     # shell into PHP container
docker compose exec db mysql -uroot -prootpass warcry            # MySQL prompt
docker compose exec db mysqldump -uroot -prootpass warcry > backup.sql   # backup
docker compose restart web                                       # restart just web
```

### Environment overrides

`configuration/database.php`, `authentication.php`, and `basic.php` read these
env vars (with sensible fallbacks):

| Variable             | Default     | Purpose                          |
|----------------------|-------------|----------------------------------|
| `WARCRY_DB_HOST`     | `localhost` | Site DB host                     |
| `WARCRY_DB_USER`     | `Ghost`     | Site DB user                     |
| `WARCRY_DB_PASS`     | `ascent`    | Site DB password                 |
| `WARCRY_DB_NAME`     | `warcry`    | Site DB name                     |
| `WARCRY_AUTH_HOST`   | `localhost` | Auth DB host                     |
| `WARCRY_AUTH_USER`   | `Ghost`     | Auth DB user                     |
| `WARCRY_AUTH_PASS`   | `ascent`    | Auth DB password                 |
| `WARCRY_AUTH_NAME`   | `auth`      | Auth DB name                     |
| `WARCRY_BASE_URL`    | autodetect  | Forces `$config['BaseURL']`      |

Compose already sets these for the container. Override in `.env` only if you
need to change them.

### Don't run this in production

Dev defaults: `display_errors = On`, root MySQL pass is `rootpass`, no HTTPS.
Fine for local demo. Not fine for the public internet.
