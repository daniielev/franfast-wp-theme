# Deploy (GitHub → GoDaddy staging)

This repo contains **only the FranFast theme**. WordPress core, plugins, uploads (media) and the database are not in git;
content moves between environments with Duplicator.

## Flow
1. Change the theme locally (MAMP, http://franfast.local).
2. `git add -A && git commit -m "…" && git push` on `main`.
3. GitHub Actions (`.github/workflows/deploy-staging.yml`) checks JSON + PHP syntax, uploads the theme to staging with rsync over SSH
   and clears the WP Fastest Cache page cache. Watch it in the repo → **Actions**. A failed check uploads nothing.
4. You can also deploy by hand: Actions → *Deploy to staging* → **Run workflow**.

`rsync --delete` keeps the staging theme identical to the repo: a file deleted here is deleted on staging (only inside the theme folder).
Production is not connected; it will get its own workflow when we go live.

## GitHub secrets (repo → Settings → Secrets and variables → Actions; or environment "staging")
| Secret | Value |
|---|---|
| `STAGING_SSH_HOST` | SSH host from GoDaddy (cPanel → SSH Access), e.g. the server IP or domain |
| `STAGING_SSH_USER` | cPanel / SSH user |
| `STAGING_SSH_PORT` | `22` (leave empty for 22) |
| `STAGING_SSH_KEY` | **Private** key of a key pair created only for deploys (see below) |
| `STAGING_SSH_KNOWN_HOSTS` | Output of `ssh-keyscan -p 22 <host>` run from your Mac |
| `STAGING_PATH` | Absolute path to the theme on the server, ending in `/wp-content/themes/franfast` |

### Deploy key (once, on your Mac)
```bash
ssh-keygen -t ed25519 -C "github-deploy-franfast-staging" -f ~/.ssh/franfast_staging_deploy -N ""
cat ~/.ssh/franfast_staging_deploy.pub   # → GoDaddy cPanel → SSH Access → Import Key → Authorize
cat ~/.ssh/franfast_staging_deploy       # → GitHub secret STAGING_SSH_KEY (never commit or share it)
ssh-keyscan -p 22 <host>                 # → GitHub secret STAGING_SSH_KNOWN_HOSTS
```
Test it: `ssh -i ~/.ssh/franfast_staging_deploy <user>@<host> "ls <STAGING_PATH>"`.
