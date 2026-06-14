# CBT System - Computer Based Testing Platform

[![Laravel Version](https://shields.io)](https://laravel.com)
[![PHP Version](https://shields.io)](https://php.net)
[![License](https://shields.io)](LICENSE)

A comprehensive, enterprise-grade Computer Based Testing (CBT) system for educational institutions to conduct online examinations, manage question banks, track student performance, and provide real-time exam monitoring.

---

## 🚀 Features

### For Students

- **Online Exam Engine:** Exam taking with real-time timers, auto-save functionality, and auto-submit on expiry.
- **Navigation Palette:** Interactive tracking panel with 5 status indicators (Not Visited, Visited, Answered, Marked, Answered & Marked).
- **Review System:** Quick "Mark for review" functionality during active sessions.
- **Proctoring Security:** Fullscreen mode enforcement and browser tab-switch detection logs.
- **Detailed Analytics:** Performance charts, topic-wise analysis, score distributions, and rank/percentile calculators.
- **Engagement Tools:** Class feedback submissions and study streak tracking.

### For Instructors

- **Management Hub:** Complete control over courses, modules, and student batches.
- **Quick Deploy:** Swift quiz creation and instant assessment launching.
- **Live Dashboard:** Real-time exam monitoring with active tab-switch warnings and forced exam termination.
- **Analytics Panel:** Question-wise performance metrics and student feedback response tracking.

### For Administrators

- **Access Control:** User management mapped using granular roles and permissions.
- **Resource Scaling:** Core question bank supporting bulk spreadsheet import/export, categorisation, and difficulty scaling.
- **Global Systems:** Exam scheduling engines, system theme adjustments, and comprehensive rank list generations.

---

## 🛠️ Built With

- **Backend:** Laravel 12.x & PHP 8.2+
- **Frontend:** Bootstrap 5, jQuery, Chart.js, Select2
- **Database:** MySQL 8.0+
- **Authentication:** Laravel UI Bootstrap Auth
- **Authorization:** Spatie Laravel Permission
- **APIs:** Laravel Sanctum

---

## 📦 Installation & Setup

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js >= 18.x

### Steps

1. **Clone the Repository:**

    ```bash
    git clone https://github.com/your-repo/cbt-system.git
    cd cbt-system
    ```

2. **Install Dependencies:**

    ```bash
    composer install
    npm install
    ```

3. **Configure Environment:**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    > _Note: Open `.env` and configure your database credentials before proceeding._

4. **Setup Database & Seeds:**

    ```bash
    php artisan migrate
    php artisan db:seed --class=RolePermissionSeeder
    php artisan db:seed --class=SettingsSeeder
    ```

5. **Link Assets & Build:**

    ```bash
    php artisan storage:link
    npm run build
    ```

6. **Start Application Server:**
    ```bash
    php artisan serve
    ```

---

## 🔑 Default Credentials

| Role           | Email                | Password   |
| :------------- | :------------------- | :--------- |
| **Admin**      | `admin@cbt.com`      | `password` |
| **Instructor** | `instructor@cbt.com` | `password` |
| **Student**    | `student@cbt.com`    | `password` |

---

## 🌐 API Endpoints

The platform exposes rest endpoints optimized for mobile applications and external system integrations.

| Method | Endpoint                                       | Description                                           |
| :----- | :--------------------------------------------- | :---------------------------------------------------- |
| `POST` | `/api/login`                                   | User authentication                                   |
| `GET`  | `/api/student/exams`                           | List available student assessments                    |
| `POST` | `/api/student/exams/{exam}/start`              | Initialize an exam instance                           |
| `POST` | `/api/student/exams/{studentExam}/save-answer` | Payload capture for auto-saving answers               |
| `POST` | `/api/student/exams/{studentExam}/submit`      | Manual finalized exam submission                      |
| `GET`  | `/api/student/results`                         | Fetch complete student performance historical metrics |
| `GET`  | `/api/student/analytics`                       | Deep metrics analytics extraction                     |

---

## 📁 Project Structure

```text
├── app/
│   ├── Http/Controllers/     # Admin, Instructor, Student logic handlers
│   ├── Models/               # Core Eloquent models
│   ├── Services/             # Dedicated business logic layer
│   ├── Repositories/         # Structural data repository pattern
│   └── Traits/               # Shared modular traits
├── database/
│   ├── migrations/           # Structural database schemas
│   └── seeders/              # User roles, configurations, and baseline testing seeds
├── resources/
│   └── views/                # Modular Bootstrap Blade front-end templates
└── routes/
    ├── web.php               # Standard web application navigation routing
    └── api.php               # Secure Sanctum REST API endpoints
```

---

## ⚙️ Automated Commands

```bash
# Auto-submit expired exams (Run via scheduler every minute)
php artisan exams:auto-submit

# Wipe all compiled application caches
php artisan cache:clear && php artisan view:clear && php artisan config:clear && php artisan route:clear
```

---

## 🛠️ Troubleshooting

| Common Issue                                            | Immediate Solution                                                                                     |
| :------------------------------------------------------ | :----------------------------------------------------------------------------------------------------- |
| White screen rendering right after user login           | Clear old state bindings: `php artisan config:clear` and `php artisan view:clear`                      |
| Uploaded file elements or missing proctoring images     | Re-link public visibility paths: `php artisan storage:link`                                            |
| Token mismatch (CSRF expiration) warnings               | Empty individual browser cookie tracking storage; check sessions configuration details                 |
| Stale entity properties or migration compilation errors | Clear loaders and refresh database entirely: `composer dump-autoload` then `php artisan migrate:fresh` |

---

## 🌐 Browser Support

The application is rigorously optimized for standardized evergreen rendering engines:

- Google Chrome _(Recommended for best proctoring stability)_
- Mozilla Firefox
- Apple Safari
- Microsoft Edge

---

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.
