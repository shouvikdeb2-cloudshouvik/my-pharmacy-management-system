# Contributing to Pharmacy ERP

Thanks for helping out. Contributions are released under the project's [MIT License](LICENSE).

## Quick start

1. Fork the repo and clone your fork.
2. Run it locally: `php -S localhost:8080 -t .` then open `http://localhost:8080/index.php`.
3. Create a branch: `git checkout -b fix/short-description`.
4. Make your change, test it by hand in the browser, then open a pull request against `main`.

Requires PHP 7.4+. Data is stored outside the web root (`PHARMACY_DATA_DIR`, `%APPDATA%\PharmacyERP` on Windows, or `./data`).

## Reporting bugs

Open an issue with:
- what you did, what you expected, what happened;
- PHP version and OS;
- storage mode (JSON or MySQL).

**Security problems:** don't open a public issue. Contact the maintainer privately through the email on their GitHub profile.

## Pull request guidelines

- One change per PR, kept small and focused.
- Match the existing style: plain PHP, no new dependencies unless clearly needed.
- Escape all output (`htmlspecialchars`), keep CSRF tokens on forms, and validate input on the server.
- Never commit real data, passwords, or `db_config.json`.
- Describe what changed and how you tested it.

## Good first areas

- Returns and refund flow polish
- Report and export improvements
- Input validation and error messages
- Docs and translations
