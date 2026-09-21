<div align="center">

# 🌐 VmCoreCentral

### Central Licensing, Fleet Management & Distribution Hub for Nimbus Control Panel

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.x-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

<br>

**VmCoreCentral** is the unified central management platform that powers the **Nimbus Control Panel** ecosystem. It serves as the master authority for software licensing, release packaging, fleet telemetry, server node orchestration, and client billing/invoicing.

<br>

[Architecture](#-architecture) •
[Core Capabilities](#-core-capabilities) •
[Installer Distribution](#-dynamic-installer-distribution) •
[Node Fleet Management](#-fleet-orchestration--telemetry) •
[Installation & Setup](#-installation--setup)

</div>

---

## 🏛️ Architecture

```
                               ┌────────────────────────────────┐
                               │         VmCoreCentral          │
                               │  Licensing & Distribution Hub  │
                               └────────────────────────────────┘
                                  │            │            │
             Dynamic Installer    │            │ Heartbeat  │ Release Zips
             & License Keys       │            │ Telemetry  │ & Auto-Updates
                                  ▼            ▼            ▼
                   ┌────────────────────────────────────────────────┐
                   │               CONNECTED SERVERS                │
                   ├────────────────┬───────────────┬───────────────┤
                   │  Nimbus Node A │ Nimbus Node B │ Nimbus Node C │
                   │ (Production)   │ (Staging)     │ (Client VPS)  │
                   └────────────────┴───────────────┴───────────────┘
```

VmCoreCentral acts as the central brain:
1. **Client & Admin Portal:** Clients register, order plans, manage their hosting accounts, and view assigned licenses.
2. **Release Engine:** Packages, validates, and serves `/install.sh` and `/uninstall.sh` with environment-specific token replacements.
3. **Telemetry Ingestion:** Receives periodic health checks, disk consumption, and service statuses from all active Nimbus instances.
4. **Entitlement Authority:** Validates node capabilities, domain limits, and premium security modules (Nimbus Shield, Cloud Backups).

---

## 🚀 Core Capabilities

### 🔑 Cryptographic License Engine
- **Public/Private Key Cryptography:** Issues tamper-proof, RSA-signed license tokens verifiable both online and offline.
- **Plan Entitlement Control:** Granular feature flags governing maximum domains, databases, multi-PHP pools, Nimbus Shield modules, and custom branding.
- **Node Binding:** Enforces server IP and hardware identifier verification to prevent unauthorized key sharing, with automated IP re-binding policies.
- **Lifecycle Management:** Real-time license status transitions (Active, Trial, Suspended, Expired, Revoked).

### 📦 Dynamic Installer Distribution
- **One-Line Provisioning:** Serves dynamic installer endpoints (`/install.sh`) that automatically inject the active central hub URL and custom configuration parameters.
- **Release Versioning:** Release manager allows administrators to upload validated production ZIP packages, automatically inspecting and updating `/install.sh` and `/uninstall.sh` artifacts.
- **Headless One-Click Command:** Generates customized deployment commands for clients:
  ```bash
  curl -sSL https://central.vmcore.in/install.sh | sudo bash -s -- --license=VMCORE-KEY-XXXX
  ```

### 🖥️ Fleet Orchestration & Telemetry
- **Central Node Registry:** Real-time visibility into all deployed server nodes running Nimbus.
- **Health & Resource Monitoring:** Live tracking of server load, CPU cores, RAM consumption, storage capacity, and OS release versions.
- **Active Node Telemetry:** Receive automated heartbeats from remote nodes, reporting domain counts, active FPM pools, and security threat logs.
- **One-Click Node SSO:** Seamlessly launch authenticated administrative sessions directly into any managed server node.

### 💼 Hosting & Billing Administration
- **Subscription Management:** Plan assignment, quota limits, invoice generation, and order processing.
- **Managed Hosting Provisioning:** Automated server provisioning requests and client hosting account onboarding.
- **Built-in Bug Reporting:** Centralized bug report ingestion from connected Nimbus nodes for rapid triage and patching.

---

## 📡 Dynamic Installer Distribution

When a node requests an installer, VmCoreCentral dynamically processes the script through `ReleaseController`:

- **Path:** `GET /install.sh`
- **Path:** `GET /uninstall.sh`
- **Dynamic Replacements:**
  - Injects the server's public base URL (`{{VMCORE_URL}}`)
  - Configures installer mode (`INSTALL_MODE="zip"` or `INSTALL_MODE="git"`)
  - Serves directly with `text/plain` headers for seamless `curl | bash` execution.

---

## 🛠️ Installation & Setup

### Requirements
- **PHP:** 8.3 or higher with extensions (`bcmath`, `curl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`)
- **Web Server:** Nginx or Apache
- **Database:** MariaDB 10.6+ or MySQL 8.0+
- **Composer & Node.js:** Composer 2.x and Node.js 20+

### Step-by-Step Setup

```bash
# 1. Clone the repository
git clone https://github.com/sudhirrajai/VmCoreCentral.git
cd VmCoreCentral

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 3. Environment Configuration
cp .env.example .env
php artisan key:generate

# 4. Generate Licensing Keys (RSA Keypair)
# Generates private and public keys for signing license tokens
php artisan license:generate-keys

# 5. Database Migration & Seeding
php artisan migrate --seed

# 6. Set Up Storage & Symlinks
php artisan storage:link

# 7. Start Queue Worker & Scheduler
php artisan queue:work --daemon
```

---

## 🔒 Security Best Practices

1. **Protect Licensing Private Keys:** Ensure `storage/app/keys/license_private.key` has strict `600` file permissions and is never checked into source control.
2. **Reverse Proxy & SSL:** Always terminate TLS using Let's Encrypt or Cloudflare SSL to encrypt telemetry data and license token handshakes.
3. **Webhook Signatures:** Central API endpoints authenticate remote nodes via bearer tokens and signed HMAC headers.

---

## 📄 License

VmCoreCentral is proprietary software developed by VmCore. All rights reserved.
