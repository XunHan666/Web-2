# LibraryOS

Internal library management system with features:
- Book search
- Borrow/return books online and at the counter
- Account and user management
- Approve borrow/return requests
- Calculate late fees

## User Roles
- **Admin**: System administration (accounts, settings, system requests)
- **Librarian**: Book operations, borrow/return, reader management
- **Reader**: Self-service (borrow, track, request return)
- **Guest**: View book catalog only

## Technologies
- PHP
- MySQL
- Vanilla CSS

## Installation
1. Configure database in `env/config.php`
2. Import `env/database.sql` into MySQL
3. Run on XAMPP or a similar PHP environment
