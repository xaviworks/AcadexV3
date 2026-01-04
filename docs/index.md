---
layout: default
title: Home
nav_order: 1
description: "ACADEX - Academic Excellence Management System documentation"
permalink: /
---

# ACADEX Documentation
{: .fs-9 }

Academic Excellence Management System - A comprehensive Laravel 12 based student information and grading system.
{: .fs-6 .fw-300 }

[Get Started](installation){: .btn .btn-primary .fs-5 .mb-4 .mb-md-0 .mr-2 }
[View on GitHub](https://github.com/xaviworks/AcadexV3){: .btn .fs-5 .mb-4 .mb-md-0 }

---

## Quick Links

| Documentation | Description |
|:--------------|:------------|
| [Installation Guide](installation) | Complete setup instructions |
| [CLI Commands](commands) | ACADEX command line tools |
| [Configuration](configuration) | Environment and settings |
| [Database Setup](database) | Database configuration |

---

## Overview

ACADEX is built with modern technologies:

- **Laravel 12** - PHP Framework
- **Laravel Breeze** - Authentication scaffolding
- **Alpine.js** - Lightweight JavaScript framework
- **Tailwind CSS** - Utility-first CSS framework
- **Maatwebsite Excel** - Excel import/export
- **Google 2FA** - Two-factor authentication

---

## System Requirements

| Requirement | Minimum Version |
|:------------|:----------------|
| PHP | 8.2+ |
| Node.js | 18.x+ |
| Composer | 2.x |
| npm | 9.x+ |
| MySQL/MariaDB | 8.0+ / 10.4+ |

---

## Quick Start

```bash
# Clone the repository
git clone https://github.com/xaviworks/AcadexV3.git
cd AcadexV3

# Run first-time setup
./acadex setup

# Start development server
acadex dev
```

Visit [http://localhost:8000](http://localhost:8000) to access the application.

---

## Key Features

### Multi-role Authentication
Support for Admin, Chairperson, Faculty, and Student roles with granular permissions.

### Two-Factor Authentication
Enhanced security with Google Authenticator 2FA support for all users.

### Additional Features
- Student grade management
- Excel import/export for student data
- Real-time session management
- Device tracking and security
- Automated backup system

---

## Support

For issues and feature requests, visit the [GitHub Issues](https://github.com/xaviworks/AcadexV3/issues) page.

---

**ACADEX** - Academic Excellence Management System  
Built with Laravel 12
