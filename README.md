# 🎓 ACADEX - An Outcome-Based Grading System

**Version 3.0** | Academic Records & Grading Management System for Brokenshire College

## 📋 About ACADEX

ACADEX is a comprehensive academic management system designed specifically for Brokenshire College to streamline grade management, student records, and course outcome tracking across multiple departments and programs.

### Key Features

- **Multi-Role Portal System**
  - 👨‍🏫 Instructor Portal - Grade entry, student management, course outcome tracking
  - 👔 Chairperson Portal - Department oversight, instructor management, grade approval
  - 🎖️ Dean Portal - College-wide academic monitoring and reporting
  - 🌐 GE Coordinator Portal - General Education subject management
  - 📊 VPAA Portal - Institutional academic oversight and analytics
  - 🔧 Admin Portal - System configuration, user management, academic period setup

- **Grade Management**
  - Configurable grading formulas (Quiz 40%, OCR 20%, Exam 40%)
  - Term-based grade entry (Prelim, Midterm, Prefinal, Final)
  - Automated final grade calculation
  - Grade notification system for chairpersons
  - Bulk Excel import/export for student grades

- **Course Outcome Tracking**
  - Course Outcome (CO) compliance monitoring
  - Multi-level CO attainment reporting (Subject → Course → Department → Institution)
  - Wildcard CO management for GE subjects
  - Performance analytics and visualization

- **Student Management**
  - Student enrollment and subject assignment
  - Excel-based bulk student import
  - Student records with year level and course tracking
  - Soft delete system for data retention

- **Academic Period Management**
  - Semester-based academic period configuration
  - Auto-generation of academic years
  - Period-specific data isolation

## 🛠️ Technology Stack

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Blade templating, Alpine.js 3.x, Bootstrap 5.3
- **Build Tool**: Vite 6.4
- **Database**: MySQL/MariaDB
- **Styling**: Custom CSS architecture with PostCSS
- **Icons**: Bootstrap Icons

## 📁 Project Structure

```bash
AcadexV3/
├── app/
│   ├── Http/Controllers/     # Role-based controllers
│   ├── Models/                # Eloquent models
│   ├── Traits/                # Reusable traits (GradeCalculation, ActivityManagement)
│   └── Imports/               # Excel import handlers
├── resources/
│   ├── views/                 # Blade templates by portal
│   │   ├── admin/
│   │   ├── chairperson/
│   │   ├── dean/
│   │   ├── gecoordinator/
│   │   ├── instructor/
│   │   └── vpaa/
│   ├── css/                   # Organized CSS by portal
│   │   ├── layout/
│   │   ├── admin/
│   │   ├── instructor/
│   │   └── vpaa/
│   └── js/                    # Alpine.js stores and utilities
├── database/
│   ├── migrations/            # Database schema
│   └── seeders/               # Initial data seeders
└── routes/
    └── web.php                # All application routes
```

## 🚀 Installation

### Prerequisites

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- MySQL/MariaDB
- Git

### Setup Steps

1. **Clone the repository**

   ```bash
   git clone https://github.com/B0GARTT00/AcadexV3.git
   cd AcadexV3
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**

   ```bash
   npm install
   ```

4. **Environment configuration**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database** (edit `.env`)

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=acadex
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run migrations and seeders**

   ```bash
   php artisan migrate --seed
   ```

7. **Build assets**

   ```bash
   npm run build
   ```

8. **Start development server**

   ```bash
   # Option 1: Use the dev script (runs all services)
   composer run dev
   
   # Option 2: Run individually
   php artisan serve
   php artisan queue:listen
   npm run dev
   ```

## 👥 User Roles

| Role | ID | Access Level | Key Permissions |
| ------ | ----- | -------------- | ----------------- |
| Instructor | 0 | Subject-level | Grade entry, student management, CO tracking |
| Chairperson | 1 | Department-level | Instructor oversight, grade approval, department reports |
| Dean | 2 | College-level | Cross-department monitoring, academic reporting |
| Admin | 3 | System-level | User management, system configuration |
| GE Coordinator | 4 | GE Subject-level | General Education subject management |
| VPAA | 5 | Institution-level | Institutional oversight, comprehensive analytics |

## 🔑 Default Credentials

After seeding, you can log in with:

**Admin Account:**

- Email: `admin@brokenshire.edu.ph`
- Password: (set during seeding)

**Note:** All user emails use the `@brokenshire.edu.ph` domain.

## 📊 Database Schema

### Core Tables

- `users` - System users with role-based access
- `students` - Student records with soft delete
- `subjects` - Course/subject catalog
- `academic_periods` - Semester configuration
- `term_grades` - Individual term grades (Prelim, Midterm, Prefinal, Final)
- `final_grades` - Computed final grades
- `activities` - Activity/assessment records
- `course_outcomes` - Course outcome definitions
- `course_outcome_attainments` - CO performance tracking

## 🎨 CSS Architecture

Organized modular CSS structure:

- `layout/` - App layout, navigation, guest pages
- `admin/` - Admin portal styles
- `instructor/` - Instructor-specific styles
- `chairperson/` - Chairperson portal styles
- `dean/`, `gecoordinator/`, `vpaa/` - Role-specific styles

**Build output:** ~124 kB (gzip: ~22 kB)

## 🧪 Testing

```bash
# Run tests
composer test

# Run specific test suite
php artisan test --filter=GradeCalculationTest
```

## 📝 Development Workflow

1. **Branch naming**: `feature/feature-name` or `fix/bug-name`
2. **Commit messages**: Follow conventional commits
3. **CSS changes**: Always rebuild with `npm run build`
4. **Database changes**: Create migrations, never modify existing ones
5. **Testing**: Write tests for new features

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and ensure build succeeds
5. Submit a pull request

## 📄 License

Proprietary software © 2025 Brokenshire College. All rights reserved.

## 🆘 Support

For issues and questions:

- Create an issue in the GitHub repository
- Contact the development team

## 🔄 Version History

- **v3.0** - Current version with modular CSS architecture
- **v2.0** - Enhanced multi-role portal system
- **v1.0** - Initial release

---

## Built with ❤️ for Brokenshire College
