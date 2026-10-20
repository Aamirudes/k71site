# K71 Platform Backup & Disaster Recovery Runbook

**Target Platform:** K71 Multi-Vendor Super-Marketplace (`k71.in` + subdomains)  
**Document Version:** 1.0 (Production-Ready)  
**Last Updated:** October 2026  
**Audience:** Platform Site Reliability Engineers (SRE), Master Administrators, DevOps  

---

## 1. Architecture Overview & Critical Data Assets

The K71 platform consists of three core stateful assets requiring systematic, scheduled backups:

| Asset Type | Location / Source | Retention Policy | Recovery Objective |
| :--- | :--- | :--- | :--- |
| **Relational Database** | MySQL 8.x database (`marketplace` / `k71`) | Daily full + hourly incremental (binary logs) | RPO: < 15 min, RTO: < 30 min |
| **Media & Static Storage** | `storage/app/public/` (Images, documents, banners) | Daily incremental, 30-day snapshot | RPO: < 24 hrs, RTO: < 1 hr |
| **Shorts Video Assets** | `storage/app/public/shorts/` & CDN video buckets | Daily incremental, 60-day snapshot | RPO: < 24 hrs, RTO: < 2 hrs |
| **Environment Configuration** | `.env` and cryptographic master keys (`APP_KEY`) | Encrypted offline vault / secrets manager | Permanent cold storage |

---

## 2. Automated MySQL Database Backup Runbook

### 2.1 Production Dump Command (Transactional & Zero-Lock)

To ensure zero downtime and consistency without locking active shopping carts or auction bids, use `--single-transaction --quick`:

```bash
#!/usr/bin/env bash
# /opt/scripts/backup_k71_mysql.sh
set -euo pipefail

BACKUP_DIR="/var/backups/k71/mysql"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="k71_production"
DB_USER="k71_backup"
DUMP_FILE="${BACKUP_DIR}/k71_db_${TIMESTAMP}.sql.gz"

mkdir -p "${BACKUP_DIR}"

# Perform transactional dump with gzip compression
mysqldump \
  --user="${DB_USER}" \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  --events \
  --set-gtid-purged=OFF \
  "${DB_NAME}" | gzip -9 > "${DUMP_FILE}"

# Set restricted permissions
chmod 600 "${DUMP_FILE}"

# Prune dumps older than 30 days
find "${BACKUP_DIR}" -type f -name "k71_db_*.sql.gz" -mtime +30 -delete

echo "[$(date)] Backup completed successfully: ${DUMP_FILE}"
```

### 2.2 Cron Schedule Configuration

Install in crontab (`crontab -e`):

```cron
# Daily database backup at 02:30 AM IST (low traffic window)
30 2 * * * /opt/scripts/backup_k71_mysql.sh >> /var/log/k71_db_backup.log 2>&1

# Hourly binary log flush for Point-In-Time-Recovery (PITR)
0 * * * * mysqladmin -u k71_backup flush-logs
```

---

## 3. Media & Uploads Storage Backup Runbook

### 3.1 Rsync Synchronization to Secondary Off-Site Storage

```bash
#!/usr/bin/env bash
# /opt/scripts/backup_k71_media.sh
set -euo pipefail

SOURCE_DIR="/var/www/k71/storage/app/public"
REMOTE_TARGET="s3://k71-cold-backups/storage/"

# Sync media directory excluding cache and temp files
aws s3 sync "${SOURCE_DIR}" "${REMOTE_TARGET}" \
  --exclude "cache/*" \
  --exclude ".tempmediaStorage/*" \
  --delete

echo "[$(date)] S3 media synchronization finished."
```

### 3.2 Tar-Gzip Local Emergency Snapshot

```bash
tar -czf /var/backups/k71/media/storage_snapshot_$(date +"%Y%m%d").tar.gz \
  --exclude="storage/app/public/cache" \
  -C /var/www/k71 storage/app/public
```

---

## 4. Disaster Recovery & Restoration Checklist

### 4.1 Restoring Database from Gzip Dump

In the event of database failure or corrupted records:

1. **Put Platform in Maintenance Mode:**
   ```bash
   cd /var/www/k71
   php artisan down --secret="k71-emergency-bypass" --render="errors.503"
   ```

2. **Verify Backup File Integrity:**
   ```bash
   gzip -t /var/backups/k71/mysql/k71_db_YYYYMMDD_HHMMSS.sql.gz
   ```

3. **Restore Database:**
   ```bash
   gunzip < /var/backups/k71/mysql/k71_db_YYYYMMDD_HHMMSS.sql.gz | mysql -u k71_admin -p k71_production
   ```

4. **Run Pending Migrations:**
   ```bash
   php artisan migrate --force
   ```

5. **Clear Application Caches:**
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

6. **Bring Platform Online:**
   ```bash
   php artisan up
   ```

### 4.2 Storage Symlink & Permissions Recovery

If moving servers or rebuilding filesystem:

```bash
cd /var/www/k71
php artisan storage:link
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 5. Security & Verification Drill

1. **Restoration Drill Cadence:** Perform automated test restores to a staging/shadow database on the 1st of every month.
2. **Encryption at Rest:** Ensure S3 buckets or remote storage targets use AES-256 or AWS-KMS encryption.
3. **Environment Security:** Never commit `.env` or dump files into git repositories.
