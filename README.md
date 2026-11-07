<h1 align="center">💊 Pharmacy ERP</h1>

<p align="center">
  <b>Lightweight pharmacy management in plain PHP.</b><br>
  Billing · Inventory · Purchasing · Accounts — no framework, no build step.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/DB-JSON%20%7C%20MySQL-0ea5e9" alt="Storage">
  <img src="https://img.shields.io/badge/version-2.0.0-22c55e" alt="Version">
</p>

---

## 📑 Contents

1. [Why Pharmacy ERP](#-why-pharmacy-erp)
2. [Daily workflow](#-daily-workflow)
3. [Feature map](#-feature-map)
4. [Install](#-install)
5. [Configuration](#-configuration)
6. [Security](#-security)
7. [Code map](#-code-map)
8. [FAQ](#-faq)
9. [Author](#-author)

---

## 🎯 Why Pharmacy ERP

- **Zero dependencies** — drop the folder on any PHP host.
- **Runs anywhere** — XAMPP, shared hosting, even a phone via Termux.
- **Two storage modes** — flat JSON files to start, MySQL when you grow.
- **Pharmacy-aware** — batches, expiry, GST/HSN, rack locations built in.

---

## 🔄 Daily workflow

```text
 Order stock ──▶ Receive & batch ──▶ Sell at POS ──▶ Handle returns ──▶ Review reports
 (Purchase)      (Inventory)         (Billing)        (Returns)          (Ledger / Analytics)
```

---

## 🗺️ Feature map

### 🧾 Billing
| Feature | Details |
|---|---|
| POS | Cart, customer select, batch picker, live stock & MRP |
| Payments | Cash, UPI (ref no), Cheque (no, bank, date), Card, Credit |
| Discounts | Percentage or flat, per sale |
| Invoices | Search by date/customer, view and print |
| Returns | Restock and refund tracking |

### 📦 Inventory
| Feature | Details |
|---|---|
| Medicines | Dosage form, HSN, GST %, rack location, search |
| CSV import | Preview and duplicate detection |
| Batches | Expiry tracking with status filters |
| Adjustments | Add/remove with reason and audit trail |
| Expiry report | Expired · 30 days · 90 days · good |

### 🤝 Purchasing
| Feature | Details |
|---|---|
| Suppliers | CRUD, auto-created portal accounts, emailed credentials |
| Purchase orders | Create, email supplier, receive into stock |

### 📈 Accounts & Admin
| Feature | Details |
|---|---|
| Dashboard | Live stats, 7-day chart, alerts, top medicines |
| Ledger | Income / expense / return entries, date filter |
| Analytics | 12-month trend, top customers, payment breakdown |
| Customers | Purchase history, total spent |
| Users | Admin and staff roles, enable/disable |
| Settings | Store info, currency, thresholds, SMTP, password |

---

## 🚀 Install

**Requirements:** PHP 7.4+, a web server, write access to the data folder, `mail()` (optional).

1. Copy the project folder into your web root.
2. Open `http://localhost/<folder>/` in a browser.
3. Complete first-run setup: store info → admin account → storage.
4. Log in and add your first medicines.

---

## ⚙️ Configuration

| Setting | How |
|---|---|
| Storage mode | Pick JSON or MySQL during setup |
| Data folder | `PHARMACY_DATA_DIR` env var → `%APPDATA%\PharmacyERP` (Windows) → `./data` |
| Email | SMTP details in **Settings** (test page included) |

---

## 🔒 Security

| Protection | Detail |
|---|---|
| Passwords | BCrypt hashing |
| Forms | CSRF token on every form |
| Login | 5 failed attempts → 15 min lockout |
| Sessions | 2-hour timeout |
| Output | Escaped against XSS |
| Data | Stored outside the web root |

---

## 🧱 Code map

| Path | Purpose |
|---|---|
| `index.php` | Router — pages load via `?p=name` |
| `config/app.php` | Bootstrap, constants, DB selection |
| `helpers/` | `JsonDB`, `MySQLDB`, `Mailer`, shared functions |
| `views/` | Layout templates |
| `assets/` | CSS and JS |
| `public/admin/` | Admin pages and endpoints |

---

## ❓ FAQ

**JSON or MySQL?** JSON for a single counter; MySQL for several users or large catalogs.

**Emails not sending?** Configure SMTP in Settings and use the SMTP test page.

**Permission error on start?** Make the data folder writable: `chmod -R 755 <data-dir>`.

---

## 👤 Author

Built by **Shouvik** · Pharmacy ERP v2.0.0
