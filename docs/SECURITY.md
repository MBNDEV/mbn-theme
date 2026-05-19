# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 1.0.x   | Yes       |

## Reporting a Vulnerability

If you discover a security vulnerability in this theme, please report it responsibly.

**Do not open a public GitHub issue for security vulnerabilities.**

Instead, email **web@mybizniche.com** with:

- A description of the vulnerability.
- Steps to reproduce.
- Affected version(s).
- Potential impact.

We will acknowledge your report within 48 hours and provide an estimated timeline for a fix.

## Security Standards

This theme enforces the following security practices through automated linting (`composer run lint`) and code review:

### Input Sanitization

All user input is sanitized before processing.

```php
sanitize_text_field( $_POST['field'] );
absint( $_GET['id'] );
sanitize_email( $_POST['email'] );
```

### Output Escaping

All dynamic output is escaped before rendering.

```php
esc_html( $value );
esc_url( $url );
esc_attr( $attribute );
wp_kses( $html, $allowed_tags );
```

### Nonce Verification

All form submissions and AJAX requests verify a nonce.

```php
wp_verify_nonce( $_POST['_wpnonce'], 'my_action' );
check_ajax_referer( 'my_action', 'nonce' );
```

### Capability Checks

Privileged actions verify user capabilities.

```php
if ( ! current_user_can( 'manage_options' ) ) {
  wp_die( 'Unauthorized', 403 );
}
```

### Prepared SQL

All database queries use parameterized placeholders.

```php
$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}table WHERE id = %d", $id );
```

### No Hardcoded Secrets

API keys, tokens, and credentials are never committed to the repository. Use `wp-config.php` constants or environment variables.

## Enforced By

These standards are enforced by PHPCS with the WordPress Coding Standards ruleset. The following sniffs are active:

- `WordPress.Security.EscapeOutput`
- `WordPress.Security.ValidatedSanitizedInput`
- `WordPress.Security.NonceVerification`
- `WordPress.DB.PreparedSQL`
- `WordPress.DB.PreparedSQLPlaceholders`
- `WordPress.DB.DirectDatabaseQuery`

Run `composer run lint` to verify compliance.
