# Deploy Starlink Billing to Render + TiDB Cloud

This repository is Laravel 13 and uses PHP 8.4 in the included Dockerfile. The database connection is Laravel's `mysql` driver because TiDB speaks the MySQL protocol.

## 1. Create your TiDB database

1. Create a TiDB Cloud Starter/Essential cluster and a database.
2. In TiDB Cloud, open **Connect** and choose the public connection details.
3. Copy the host, database name, username, and password. TiDB Cloud Starter/Essential usernames include the cluster's required prefix; copy the username exactly as shown by TiDB.
4. Keep the TiDB connection on TLS. The Docker image includes Debian's trusted CA bundle and configures PDO to use it. If your TiDB cluster gives you a custom CA certificate (for example, a Dedicated cluster), upload it to Render as a secret file and set `MYSQL_ATTR_SSL_CA` to that file's mounted path.

TiDB Cloud Starter/Essential uses port `4000` and requires TLS. See the official TiDB connection and TLS docs:
- https://docs.pingcap.com/tidbcloud/connect-via-standard-connection-serverless/
- https://docs.pingcap.com/tidbcloud/secure-connections-to-serverless-clusters/

## 2. Push this project to GitHub

Upload the contents of this folder to your GitHub repository (the `Dockerfile`, `docker/`, `.dockerignore`, `render.yaml`, and `DEPLOY_RENDER.md` must be in the repository root / included folder). Do not commit a real `.env` file or database credentials.

## 3. Create the Render Web Service

1. In Render, choose **New + → Blueprint** and select this repository if you want Render to read `render.yaml`. Alternatively choose **New + → Web Service**, connect the repository, and choose **Docker**.
2. The Dockerfile must be `./Dockerfile`.
3. Set the environment variables below in the Render service's **Environment** page. If you use the Blueprint, Render will prompt for values marked `sync: false`.

| Key | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | A unique Laravel key (see below) |
| `APP_URL` | Your Render URL, beginning with `https://` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | Host shown in TiDB Connect |
| `DB_PORT` | `4000` |
| `DB_DATABASE` | Your TiDB database name |
| `DB_USERNAME` | Exact TiDB username from Connect |
| `DB_PASSWORD` | TiDB password |
| `MYSQL_ATTR_SSL_CA` | `/etc/ssl/certs/ca-certificates.crt` for Starter/Essential |
| `ADMIN_NAME` | Administrator name |
| `ADMIN_EMAIL` | Login email for the first admin |
| `ADMIN_PASSWORD` | Strong password of at least 12 characters |

Generate `APP_KEY` on a computer with PHP using:
`php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"`

Keep `APP_DEBUG=false`. Do not paste your database password or `APP_KEY` into GitHub or public chat.

## 4. Deploy and sign in

The container start script:
- caches Laravel configuration,
- runs `php artisan migrate --force`,
- creates the initial administrator once (if `ADMIN_EMAIL` is set),
- starts Apache on Render's `PORT`.

Open your Render URL and sign in with the `ADMIN_EMAIL` and `ADMIN_PASSWORD` values you configured. If you leave `ADMIN_PASSWORD` empty, the app generates a one-time password and prints it in the Render logs; setting your own secret is preferable.

## Important notes

- The app stores billing records in TiDB. Keep your TiDB cluster active and retain its credentials.
- Render's free web services can sleep while idle. The first request after sleeping may take time to wake.
- This package has been inspected and its PHP files were syntax-checked, but a full Docker build and live TiDB connection test still need to be run from Render.
