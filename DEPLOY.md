# Deploy from Git with Dockhand

Local change → `git push` → Dockhand pulls this repo, **rebuilds** `webmmx:latest`, and recreates the container. SQLite, settings, and attachments stay on the NAS.

```
laptop  --push-->  GitHub  --webhook or poll-->  Dockhand on OMV  --build-->  webmmx:9080
```

## 1. One-time data move (keep your existing transactions)

The old stack mounted the **whole** web root:

```yaml
volumes:
  - /data/webmmx:/var/www/html
```

That hid image updates. Persistent files now live in a **data-only** folder.

On the OMV host:

```bash
sudo mkdir -p /data/webmmx-data/attachments

# Copy live data from the current bind mount (adjust if your files differ)
sudo cp -a /data/webmmx/configuration_user.php /data/webmmx-data/
sudo cp -a /data/webmmx/MMEX_New_Transaction.db /data/webmmx-data/
sudo cp -a /data/webmmx/attachments/. /data/webmmx-data/attachments/

sudo chown -R 33:33 /data/webmmx-data   # www-data inside the official PHP image
```

Leave `/data/webmmx` as a backup until you have confirmed a login and a pending transaction still appear.

## 2. Stop the old stack

In Dockhand, **Stop** then **Down** the current `webmmx` stack (or remove it). Port `9080` and the name `webmmx` must be free. Do not delete `/data/webmmx-data`.

## 3. Add the Git repository

The fork is **public**: `https://github.com/pierceppdh/web-money-manager-ex.git`

1. Dockhand → **Git** → **Repositories** → **Add Repository**
2. URL: `https://github.com/pierceppdh/web-money-manager-ex.git`
3. Branch: **`master`** (not `main`)
4. Auth: none (public). If you later make the repo private, use a GitHub PAT or deploy key.

## 4. Create the Git stack

1. **Stacks** → **From Git**
2. Repository: the one above
3. Stack name: `webmmx`
4. Branch: `master`
5. Compose path: **`compose.yaml`**
6. Stack variables (same as your old compose):

   | Key | Value |
   |---|---|
   | `WEBMMX_PORT` | `9080` |
   | `WEBMMX_DATA` | `/data/webmmx-data` |
   | `WEBMMX_DNS` | `192.168.0.25` |

7. Enable **Build on deploy** (required: the image is built from this repo, not pulled from Docker Hub)
8. Enable **Force redeploy** / **Re-pull** if your Dockhand version has it, so a PHP-only commit still rebuilds
9. Deploy and confirm `http://omv.home:9080/`

## 5. Updates: webhook vs poll

GitHub cannot call `http://omv.home:9011/` from the internet. Pick one:

### A. Poll (fits a home VPN; recommended)

In the Git stack, enable **auto-update** with a short cron, for example every 5 minutes:

```
*/5 * * * *
```

Push to `master`; Dockhand pulls, sees a new commit, builds, deploys. No inbound port.

### B. GitHub webhook (instant, needs a public URL)

Dockhand must be reachable from GitHub (Cloudflare Tunnel, Tailscale Funnel, or a reverse proxy). Then:

1. Stack settings → **Webhook enabled** → **Generate secret** → copy URL  
   Typical form: `https://<public-dockhand>/api/git/stacks/<id>/webhook`
2. GitHub repo → **Settings** → **Webhooks** → **Add webhook**
   - Payload URL: that Dockhand URL
   - Content type: `application/json`
   - Secret: the Dockhand secret
   - Events: **Just the push event**
3. Push to `master` and check GitHub → webhook **Recent Deliveries** (green) and Dockhand → stack **Deployments**

If GitHub shows `failed` / timeout, the payload never reached OMV; use poll (A) instead.

### C. Manual

After `git push`, in Dockhand open the stack and **Redeploy** / **Sync**.

## 6. Day-to-day

```bash
# on the laptop
cd web-money-manager-ex
# edit, test locally if you want:
docker compose up --build
git add -A && git commit -m "..." && git push origin master
```

Wait for Dockhand (webhook or next poll). The phone app at port 9080 should serve the new code; pending transactions remain in `/data/webmmx-data/MMEX_New_Transaction.db`.

## 7. If something goes wrong

| Symptom | Likely cause |
|---|---|
| Site still looks old | **Build on deploy** off, or old stack still bound to `/data/webmmx:/var/www/html` |
| Empty settings / no transactions | Data dir empty; copy files as in step 1 |
| `WEBMMX_DATA` is a file or empty dir | Host path did not exist; Docker created a directory. Create the folder and copy the DB/config in |
| Webhook never fires | GitHub cannot reach `omv.home`; use poll |
| Deploy skipped | Push was not to `master`, or Dockhand did not rebuild because compose.yaml was unchanged — enable build-on-deploy / force redeploy |
| Permission denied on save | `chown 33:33 /data/webmmx-data` |

Desktop MMEX sync is unchanged (`services.php` + GUID in settings).
