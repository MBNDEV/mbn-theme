# MBN Theme Unified Logging System

Centralized logging utility for consistent debugging and monitoring across the theme.

## Features

- **Multiple log levels**: DEBUG, INFO, WARNING, ERROR
- **Context support**: Add structured data to any log message
- **WordPress integration**: Works with WP_DEBUG, WP_DEBUG_LOG, WP_DEBUG_DISPLAY
- **Specialized loggers**: Block operations, HTTP requests, database queries, exceptions
- **Custom log files**: Optional separate log files with automatic cleanup
- **Easy API**: Simple helper functions for quick logging

## Configuration

Enable logging in `wp-config.php`:

```php
// Enable debug mode
define( 'WP_DEBUG', true );

// Write to debug.log file
define( 'WP_DEBUG_LOG', true );

// Hide errors from displaying on screen
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );
```

## Basic Usage

### Log Levels

```php
// Debug messages (detailed information for debugging)
MBN_Logger::debug( 'Variable value', array( 'value' => $variable ) );

// Info messages (general informational messages)
MBN_Logger::info( 'User action completed', array( 'user_id' => $user_id ) );

// Warning messages (something unexpected but not critical)
MBN_Logger::warning( 'Deprecated function used', array( 'function' => 'old_function()' ) );

// Error messages (critical errors that need attention)
MBN_Logger::error( 'Database connection failed', array( 'error' => $error_message ) );
```

### Quick Logging Helper

```php
// Using the helper function
mbn_log( 'Simple info message' );
mbn_log( 'Debug message', 'debug', array( 'data' => $data ) );
mbn_log( 'Error occurred', 'error', array( 'error' => $error ) );
```

## Specialized Logging

### Block Operations

```php
// Log block-specific events
MBN_Logger::block(
    'Block rendered',
    'mbn-theme/hero-section',
    array( 
        'attributes' => $attributes,
        'post_id' => get_the_ID()
    )
);
```

### Exception Handling

```php
try {
    // Your code here
} catch ( Exception $e ) {
    MBN_Logger::exception( $e, array( 'context' => 'Block initialization' ) );
}

// WordPress errors
$result = some_wp_function();
if ( is_wp_error( $result ) ) {
    MBN_Logger::exception( $result, array( 'function' => 'some_wp_function' ) );
}
```

### HTTP Requests

```php
$response = wp_remote_get( $url, $args );

MBN_Logger::http_request( 
    $url, 
    $args, 
    array( 
        'status_code' => wp_remote_retrieve_response_code( $response )
    )
);
```

### Database Queries

```php
global $wpdb;
$query = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $post_id );

MBN_Logger::query( 
    $query, 
    array( 
        'rows_affected' => $wpdb->num_rows 
    )
);
```

## Real-World Examples

### In Block Registration

```php
function register_custom_block( $block_path ) {
    MBN_Logger::debug( 'Attempting to register block', array( 'path' => $block_path ) );
    
    if ( ! file_exists( $block_path . '/block.json' ) ) {
        MBN_Logger::error( 'block.json not found', array( 'path' => $block_path ) );
        return false;
    }
    
    $result = register_block_type( $block_path );
    
    if ( $result ) {
        MBN_Logger::block( 'Block registered', $result->name );
    } else {
        MBN_Logger::error( 'Block registration failed', array( 'path' => $block_path ) );
    }
    
    return $result;
}
```

### In AJAX Handlers

```php
function handle_ajax_request() {
    MBN_Logger::info( 'AJAX request received', array(
        'action' => $_POST['action'],
        'user_id' => get_current_user_id(),
        'ip' => $_SERVER['REMOTE_ADDR']
    ));
    
    try {
        $result = process_request();
        
        MBN_Logger::info( 'AJAX request completed', array( 'result' => $result ) );
        
        wp_send_json_success( $result );
        
    } catch ( Exception $e ) {
        MBN_Logger::exception( $e, array( 'handler' => 'handle_ajax_request' ) );
        wp_send_json_error( array( 'message' => $e->getMessage() ) );
    }
}
```

### In Custom Post Type Registration

```php
function register_custom_post_type() {
    $args = array(
        'public' => true,
        'label'  => 'Projects'
    );
    
    $result = register_post_type( 'project', $args );
    
    if ( is_wp_error( $result ) ) {
        MBN_Logger::exception( $result, array( 'post_type' => 'project' ) );
        return false;
    }
    
    MBN_Logger::info( 'Custom post type registered', array( 'post_type' => 'project' ) );
    return true;
}
```

### In Theme Options Updates

```php
function save_theme_options( $options ) {
    MBN_Logger::debug( 'Saving theme options', array( 'options' => $options ) );
    
    $old_options = get_option( 'theme_options' );
    $updated = update_option( 'theme_options', $options );
    
    if ( $updated ) {
        MBN_Logger::info( 'Theme options updated', array(
            'changes' => array_diff_assoc( $options, $old_options )
        ));
    } else {
        MBN_Logger::warning( 'Theme options not updated', array( 
            'reason' => 'No changes detected'
        ));
    }
    
    return $updated;
}
```

### In Widget Registration

```php
function register_custom_widget() {
    try {
        register_widget( 'My_Custom_Widget' );
        MBN_Logger::info( 'Widget registered', array( 'widget' => 'My_Custom_Widget' ) );
    } catch ( Exception $e ) {
        MBN_Logger::exception( $e, array( 'widget' => 'My_Custom_Widget' ) );
    }
}
```

## Custom Log Files

### Writing to Custom Log File

```php
// Write to both debug.log and custom log file
MBN_Logger::write_custom_log( 
    MBN_Logger::LEVEL_ERROR, 
    'Critical error in payment processing',
    array( 'order_id' => $order_id )
);
```

### Managing Log Files

```php
// Get current log file path
$log_path = MBN_Logger::get_log_file_path();
// Returns: /wp-content/uploads/mbn-theme-logs/mbn-theme-2026-04-18.log

// Clear logs older than 7 days (runs automatically daily)
MBN_Logger::clear_old_logs( 7 );

// Clear logs older than 30 days
MBN_Logger::clear_old_logs( 30 );
```

## Log Format

All logs are formatted consistently:

```
[MBN-THEME] [2026-04-18 14:30:45] INFO: Block registered successfully | Context: {"block":"mbn-theme/hero-section","folder":"hero-section"}

[MBN-THEME] [2026-04-18 14:31:22] ERROR: Failed to load asset | Context: {"file":"style.css","block":"mbn-theme/cta-section"}

[MBN-THEME] [2026-04-18 14:32:10] DEBUG: Variable value | Context: {"var":"test_value","function":"process_data"}
```

## Best Practices

### 1. Use Appropriate Log Levels

- **DEBUG**: Detailed diagnostic information
- **INFO**: Confirmation that things are working as expected
- **WARNING**: Something unexpected happened, but the application continues
- **ERROR**: A serious problem that prevented a function from completing

### 2. Add Meaningful Context

```php
// Bad
MBN_Logger::error( 'Failed' );

// Good
MBN_Logger::error( 'Failed to upload file', array(
    'file_name' => $file_name,
    'file_size' => $file_size,
    'error' => $error_message,
    'user_id' => get_current_user_id()
));
```

### 3. Don't Log Sensitive Information

```php
// Bad - logs password
MBN_Logger::debug( 'User login', array( 'password' => $password ) );

// Good - logs only non-sensitive data
MBN_Logger::info( 'User login attempt', array( 'username' => $username ) );
```

### 4. Use Specialized Methods

```php
// Use block-specific logger
MBN_Logger::block( 'Block action', 'mbn-theme/hero' );

// Use exception logger for errors
MBN_Logger::exception( $error );

// Use query logger for database operations
MBN_Logger::query( $sql, array( 'execution_time' => $time ) );
```

## Viewing Logs

### WordPress Debug.log

Located at: `wp-content/debug.log`

```bash
# Tail the log in real-time (Linux/Mac)
tail -f wp-content/debug.log

# Windows PowerShell
Get-Content wp-content/debug.log -Wait -Tail 50
```

### Custom Theme Logs

Located at: `wp-content/uploads/mbn-theme-logs/mbn-theme-YYYY-MM-DD.log`

```bash
# View today's log
tail -f wp-content/uploads/mbn-theme-logs/mbn-theme-2026-04-18.log
```

### Filtering Logs

```bash
# Show only errors
grep "ERROR" wp-content/debug.log

# Show logs for specific block
grep "hero-section" wp-content/debug.log

# Show logs from specific time
grep "14:30" wp-content/debug.log
```

## Performance Considerations

- Logging only runs when `WP_DEBUG` is enabled
- Disable debugging in production to avoid performance overhead
- Custom log files are automatically cleaned up after 7 days
- Use context arrays instead of string concatenation for better performance

## Troubleshooting

### Logs Not Appearing

1. Check `WP_DEBUG` is enabled in `wp-config.php`
2. Verify `WP_DEBUG_LOG` is true
3. Check file permissions on `wp-content/debug.log`
4. Ensure `wp-content` directory is writable

### Too Many Logs

1. Use appropriate log levels (avoid excessive DEBUG logs)
2. Reduce context data in production
3. Consider using custom log files for specific features
4. Implement log rotation or cleanup

## Integration with Other Tools

### Query Monitor

MBN Logger works alongside Query Monitor plugin for comprehensive debugging.

### New Relic / Application Monitoring

Logs can be parsed by APM tools for centralized monitoring in production.

### Log Aggregation Services

Custom log files can be sent to services like Loggly, Papertrail, or Splunk.

## Support

For questions or issues with the logging system, check:
- [WordPress Debug Documentation](https://wordpress.org/support/article/debugging-in-wordpress/)
- Theme documentation in `/docs`
- `inc/includes-logger.php` source code
