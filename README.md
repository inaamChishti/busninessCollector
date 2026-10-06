# Business Email Collector

A standalone Laravel 13 application for discovering businesses through authorised provider APIs, identifying likely official websites, and collecting business email addresses that are publicly published on those websites.

The application does **not** scrape Yelp HTML, guess email addresses, submit contact forms, bypass access controls, or send marketing email. Yelp discovery uses the official Yelp Places API. Website discovery is provider-based; the included implementation uses Google Programmable Search JSON API.

## What is included

- Secure administrator login and one-time first-admin setup.
- Large postcode/city CSV imports with validation and duplicate prevention.
- Provider-independent `BusinessDiscoveryProviderInterface` and `WebsiteSearchProviderInterface`.
- Yelp category searches with pagination, exact-postcode option, source tracking and deduplication.
- Google Programmable Search website candidates, weighted evidence, confidence scores and manual approval/rejection.
- Restrained same-domain crawler with configurable depth/page limits, delays, `robots.txt` checks, timeout/retry handling and redirect validation.
- SSRF protection for loopback, private/reserved networks, metadata addresses, internal hostnames, credentials and non-HTTP protocols.
- Normal, `mailto:` and common `[at]` / `(at)` / spaced-`@` email extraction without address guessing.
- Syntax/domain checks, obvious-fake filtering, contact-form detection and source URL retention.
- Database queues, resumable/idempotent jobs, pause/resume, failed-job controls and 1–5 scheduler-managed workers.
- Search/filter/detail/edit pages, duplicate review, activity history and streamed CSV/XLSX exports.
- Authenticated read-only future-integration API under `/api/v1`.

## 1. Server requirements

- Linux web server with Nginx or Apache.
- PHP **8.3–8.5** (Laravel 13 requires PHP 8.3 or newer).
- MySQL 8.0+ or a compatible current MariaDB release.
- Composer 2.7+.
- PHP extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `intl`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `xmlwriter`, and `zip` (XLSX exports require `zip`).
- Cron and permission to run PHP CLI.
- HTTPS is strongly recommended and expected by the production cookie defaults.

The included lock file pins Laravel 13.1.1 and its reviewed dependency set. The application has local CSS/JavaScript assets and does not need an npm build for deployment.

## 2. Upload and extract the ZIP

Upload `business-email-collector.zip` to a directory outside the public web root, then extract it. Example final location:

```text
/var/www/business-email-collector
```

Configure the site's document root as:

```text
/var/www/business-email-collector/public
```

Do not expose the project root, `.env`, `storage`, or `vendor` directly through the web server.

Install the locked dependencies once:

```bash
cd /var/www/business-email-collector
composer install --no-dev --optimize-autoloader
```

## 3. Create the MySQL database

Create an empty database and a least-privilege application user. Use `utf8mb4` and a Unicode collation. Record the database name, username and password for `.env`.

## 4. Import the supplied SQL

Import `business_email_collector.sql` using phpMyAdmin or MySQL CLI. It creates framework tables, collector tables, foreign keys, indexes, default settings and the initial category list. It does not contain an administrator, demo businesses, credentials or API keys.

Example:

```bash
mysql -u YOUR_DATABASE_USER -p YOUR_DATABASE_NAME < business_email_collector.sql
```

Do not also run fresh migrations after importing this file; the SQL marks the included migrations as applied. Future releases can use normal `php artisan migrate --force` upgrades.

## 5. Configure `.env`

Copy `.env.example` to `.env`, then set at minimum:

- `APP_URL` to the final HTTPS URL.
- `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`.
- `MAIL_*` only if you later add email-based password reset delivery.
- `INSTALLER_TOKEN` to a long, random one-time secret.

Generate the Laravel encryption key once:

```bash
php artisan key:generate
```

Then cache production configuration:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

API credentials can be placed in `.env`, but entering them through **Settings → API integrations** is recommended; database values are encrypted using `APP_KEY`.

## 6. Permissions

The web-server/CLI user must be able to write to `storage` and `bootstrap/cache`:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

Use the correct web-server user for your host. Do not make the whole application world-writable.

## 7. Configure the one permanent cron job

Add one cron entry, replacing the PHP and project paths as required:

```cron
* * * * * cd /var/www/business-email-collector && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

This is the only permanent process configuration required. The scheduler starts short-lived database queue workers every minute and stops them when the queue is empty. **Settings → Concurrent background workers** controls 1–5 worker slots from the browser. You do not need to run `php artisan queue:work` manually during normal use.

## 8. Create and log in to the first administrator

After setting `INSTALLER_TOKEN`, open:

```text
https://your-domain.example/setup?token=YOUR_INSTALLER_TOKEN
```

Create the first administrator with a password of at least 12 characters. The setup endpoint returns 404 after any user exists. Remove `INSTALLER_TOKEN` from `.env` afterward and run `php artisan config:cache` once more.

Sign in at `/login`.

## 9. Configure the Yelp API

1. Obtain an authorised Yelp Places API key under Yelp's current developer terms.
2. Open **Settings → API integrations**.
3. Paste the Yelp API key and save.
4. Adjust business API requests per minute below the limit assigned to your account.

If the key is missing, jobs pause with “Provider not configured” rather than crashing. HTTP 429/quota responses pause the relevant collection so the error can be corrected safely.

## 10. Configure website search

The included provider uses Google Programmable Search JSON API:

1. Create/configure a Programmable Search Engine suitable for the websites you are permitted to search.
2. Enable the Custom Search JSON API in the associated Google project.
3. Enter the API key and Search Engine ID in **Settings → API integrations**.
4. Set a conservative request limit.

No Google result-page HTML is scraped. A future provider can implement `WebsiteSearchProviderInterface` and be bound in `AppServiceProvider` without changing crawler or business logic.

## 11. Upload postcode/city CSV

Open **Imports**, upload a UTF-8 CSV, and use these exact headers:

```csv
postcode,city
E6 1AA,London
IG1 1AA,Ilford
B1 1AA,Birmingham
```

The import runs in the background, processes rows incrementally and ignores duplicate postcode/city/country combinations.

## 12. Start collection

1. Review **Categories**; enable only the category searches you require and use official Yelp aliases.
2. Configure API credentials and limits in **Settings**.
3. Open **Dashboard** and choose **Start collection**, or select rows on **Locations** and choose **Process**.

The application remembers each location/category/page search. A failed page does not restart the full collection. Businesses are primarily deduplicated by provider ID; uncertain secondary matches go to **Duplicate review** and are never automatically merged.

## 13. Pause and resume

Use **Pause collection** on the Dashboard to stop new work while preserving queued/database state. In-flight HTTP work finishes safely. **Resume collection** continues pending work. Closing the browser or restarting PHP does not erase progress.

## 14. Retry failures and recheck records

- **Failed Jobs** shows the exception, queue and failure time. Retry selected/all or ignore a record.
- A business detail page can re-run website verification or email discovery.
- **Find missing websites** and **Find missing emails** queue incomplete records.
- Recheck intervals are configured in **Settings**; successful records can be excluded from automatic rechecks.

## 15. Export results

Open **Exports**, select CSV or XLSX, optionally filter by city, postcode prefix and email availability, then create the export. Files are written row by row so large exports do not load the entire business table into memory. Download links require an authenticated administrator session.

## 16. Troubleshooting

**Queue remains at the same size**

- Confirm the cron entry runs as a user that can read the application and write `storage`.
- Run `php artisan schedule:list` once through SSH to confirm scheduling.
- Confirm collection is not paused and **Concurrent background workers** is at least 1.
- Check `storage/logs/laravel.log` and **Failed Jobs**.

**Provider not configured**

- Add the required key/engine ID in Settings. If configuration was changed in `.env`, run `php artisan config:cache`.

**429 / quota exceeded**

- Lower the matching requests-per-minute setting, confirm the provider quota, then resume/retry.

**403 or robots blocked**

- This is a legitimate website refusal. Do not bypass it. The business is stored as `crawl_blocked` or `request_failed` for review.

**XLSX export fails**

- Enable PHP `zip`; CSV exports do not require it.

**Session/login loop**

- Confirm `APP_URL`, HTTPS, `SESSION_SECURE_COOKIE`, proxy HTTPS headers and database permissions.

**SQL import succeeds but tables appear missing**

- Confirm phpMyAdmin imported into the same database named by `DB_DATABASE`.

## Security and operating notes

- All web administration routes require authentication and an admin account. CSRF protection, session regeneration, password hashing, output escaping and parameterised Eloquent queries are used.
- Crawler destinations and every redirect are validated and DNS-resolved before the request. Private, reserved, loopback, local, cloud-metadata and unsafe-protocol destinations are rejected.
- Manual website verification is protected from later automatic replacement.
- API keys and the optional read-only API bearer token are encrypted in the database. Back up `APP_KEY`; losing it makes those values unreadable.
- The read-only API is disabled in practice until a token is generated in Settings. Use `Authorization: Bearer TOKEN` with `/api/v1/businesses`.
- Keep Laravel/PHP dependencies patched and review `composer audit` during maintenance.

## Compliance responsibility

This software is a collection and management tool, not a marketing sender. The administrator is responsible for confirming a lawful purpose and complying with API-provider terms, website terms, applicable `robots.txt` rules, UK GDPR, PECR, data accuracy/retention duties, opt-out requirements, and any other applicable privacy or direct-marketing law. Collect only genuinely public business contact information. Do not bypass logins, CAPTCHAs, paywalls, authentication, access controls or technical restrictions.

## Verification performed for this release

- Composer manifest and lock validation passed.
- Laravel package discovery and optimised autoload generation passed.
- All application PHP files passed syntax validation under PHP 8.5.
- All 57 application routes loaded successfully.
- Fresh schema migration and default seeding passed using SQLite.
- Scheduler registration passed, including five browser-configurable worker slots.
- Automated suite: **11 tests, 39 assertions, all passed**. Coverage includes every primary admin page rendering, authentication/permissions, provider identity deduplication, queue idempotency, email and obfuscation extraction, URL/SSRF rules, website scoring, Yelp 429 handling, and streamed CSV/XLSX generation.
- Live Yelp/Google calls are deliberately excluded from tests; provider responses are faked.
