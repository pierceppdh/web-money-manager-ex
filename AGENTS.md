# Agent notes

This laptop talks to Dockhand on the home LAN. GitHub cannot reach `omv.home`.

After **every** code change that should go live:

1. `git commit` on `master`
2. `git push origin master`
3. `scripts/dockhand-webhook.sh`

Do not skip step 3. The webhook secret lives in `.env.local` (gitignored), never in committed files.

Stack: `webmmxapp` at `http://omv.home:${WEBMMX_PORT}/` (default 9080)  
Data: `/data/webmmxapp`  
Dockhand variable name is `WEBMMX_PORT` (not `WEBMM_PORT`).
