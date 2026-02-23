# Contributing to OpenGenetics

Thank you for considering contributing to OpenGenetics! This guide will help you get started.

## Development Setup

```bash
git clone https://github.com/open-genetics/framework.git
cd framework
composer install
cp .env.example .env
# Configure .env with your database credentials
php genetics mutate
```

## Branch Naming

- `feature/short-description` — New features
- `fix/short-description` — Bug fixes
- `docs/short-description` — Documentation updates

## Pull Request Process

1. Fork the repository
2. Create your feature branch from `main`
3. Write or update tests as needed
4. Ensure all tests pass: `composer test`
5. Follow the existing code style (PSR-12)
6. Submit a pull request with a clear description

## Code Style

- **PSR-12** coding standard
- **PSR-4** autoloading
- Static class pattern for API endpoints
- Type declarations on all method signatures
- `declare(strict_types=1)` in framework source files

## Reporting Bugs

Open an issue on GitHub with:
- PHP version and environment
- Steps to reproduce
- Expected vs actual behavior
- Error messages / stack traces

## Security Vulnerabilities

Please email security@opengenetics.io directly. Do **not** open a public issue.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
