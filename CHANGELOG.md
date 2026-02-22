# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-01

### Added
- File-based routing system (`Router`)
- JWT authentication with hybrid implementation (`JwtManager`)
- Genetic RBAC with 3 roles: ADMIN, HR, EMPLOYEE (`Guard`)
- PDO database singleton with prepared statements (`Database`)
- `queryOne()` helper for single-row fetches
- Environment loader with static caching (`Env`)
- Standardized JSON response helper (`Response`)
- i18n engine with Thai/English support (`I18n`)
- Non-blocking audit trail logging (`AuditLog`)
- Password reset with token expiration (`AuthService`)
- Vanilla JS SDK (`genetics.min.js`)
- React Hook SDK (`useGenetics`)
- CLI tool with commands: `mutate`, `seed`, `status`, `make:endpoint`, `make:middleware`, `new`, `serve`
- Multi-tenancy support via `tenant_id`
- OWASP-aligned security (prepared statements, bcrypt, JWT expiration)
- Dark/Light theme support in documentation
- Responsive documentation site
