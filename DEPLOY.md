# Deploy from Git with Dockhand

Local change → `git push` → Dockhand pulls this repo, **rebuilds** `webmmxapp:latest`, and recreates the container. SQLite, settings, and attachments stay on the NAS.

The stack is **`webmmxapp`**. Host port comes from the Dockhand variable **`WEBMMX_PORT`** (default **9080**). The name is `WEBMMX_PORT`, not `WEBMM_PORT`.

```
laptop  --push-->  GitHub  --webhook or poll-->  Dockhand on OMV  --build-->  webmmxapp:${WEBMMX_PORT}
```

| | New stack |
|---|---|
| Stack / container | `webmmxapp` |
| URL | `http://omv.home:${WEBMMX_PORT}/` (default 9080) |
| Files | `/data/webmmxapp` (data only) |

## 1. Copy data (do not move the live app)

On the OMV host:

```bash
sudo mkdir -p /data/webmmxapp/attachments

sudo cp -a /data/webmmx/configuration_user.php /data/webmmxapp/
sudo cp -a /data/webmmx/MMEX_New_Transaction.db /data/webmmxapp/
sudo cp -a /data/webmmx/attachments/. /data/webmmxapp/attachments/

sudo chown -R 33:33 /data/webmmxapp   # www-data inside the official PHP image
```

The old `/data/webmmx` tree stays in place so 9080 keeps working.

## 2. Keep the old stack

Do **not** stop `webmmx`. The new compose uses a different container name, image name, port, and data path, so both can run.

## 3. Add the Git repository

The fork is **public**: `https://github.com/pierceppdh/web-money-manager-ex.git`

1. Dockhand → **Git** → **Repositories** → **Add Repository**
2. URL: `https://github.com/pierceppdh/web-money-manager-ex.git`
3. Branch: **`master`** (not `main`)
4. Auth: none (public). If you later make the repo private, use a GitHub PAT or deploy key.

## 4. Create the Git stack

1. **Stacks** → **From Git**
2. Repository: the one above
3. Stack name: `webmmxapp`
4. Branch: `master`
5. Compose path: **`compose.yaml`**
6. Stack variables (exact names, no leading/trailing spaces):

   | Key | Value |
   |---|---|
   | `WEBMMX_PORT` | `9080` |
   | `WEBMMX_DATA` | `/data/webmmxapp` |
   | `WEBMMX_DNS` | `192.168.0.25` |

   `WEBMM_PORT` is not read. If the app stays on 9081, `WEBMMX_PORT` is still set to 9081 — change it to 9080 and redeploy.
7. Enable **Build on deploy** (required: the image is built from this repo)
8. Enable **Force redeploy** / **Re-pull** if your Dockhand version has it, so a PHP-only commit still rebuilds
9. Deploy and confirm `http://omv.home:9080/`

Point desktop MMEX **Options → Network → WebApp** at `http://omv.home:9080/` (same GUID if you copied `configuration_user.php`).

## 5. Updates: webhook vs poll

GitHub cannot call `http://omv.home:9011/` from the internet. After `git push`, trigger Dockhand from the LAN:

```bash
scripts/dockhand-webhook.sh
```

(credentials in `.env.local`, not in Git). That is the path used from this workspace.

Alternatively:

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

Wait for Dockhand (webhook or next poll). The phone app is at `http://omv.home:${WEBMMX_PORT}/` (default 9080). Pending transactions remain in `/data/webmmxapp/MMEX_New_Transaction.db`.

## 8. If something goes wrong

| Symptom | Likely cause |
|---|---|
| Site still looks old | **Build on deploy** off, or browser cache |
| Empty settings / no transactions | Data dir empty; copy files as in step 1 |
| Still on 9081 / invalid port `" 9080"` | Dockhand puts a **leading space** in `WEBMMX_PORT`, which Docker rejects. Host port is therefore hardcoded as `9080:80` in `compose.yaml`. `WEBMM_PORT` (no X) is never read. |
| Port already allocated | Another container still bound to 9080 |
| `WEBMMX_DATA` is a file or empty dir | Host path did not exist; Docker created a directory. Create the folder and copy the DB/config in |
| Webhook never fires | GitHub cannot reach `omv.home`; use poll |
| Deploy skipped | Push was not to `master`, or Dockhand did not rebuild because compose.yaml was unchanged — enable build-on-deploy / force redeploy |
| Permission denied on save | `chown 33:33 /data/webmmxapp` |

Desktop MMEX sync is unchanged (`services.php` + GUID in settings).
