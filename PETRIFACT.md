# Petrifact - Data Persistence Layer

Petrifact is a persistence system that keeps your zen shortcodes and database tables functional even after the main Snefuruplin plugin is removed or deactivated.

## Overview

Petrifact works by creating a lightweight Must-Use (MU) plugin that contains only the essential shortcode handlers and database queries needed to display your content. This "survival layer" ensures your website content remains accessible during plugin updates, maintenance, or site migrations.

## Features

- **Persistent Shortcodes**: All zen shortcodes continue working after main plugin removal
- **Data Preservation**: Database tables remain intact with configurable cleanup options
- **Automatic Updates**: MU-plugin regenerates when new shortcodes are added
- **Security**: Sensitive fields are automatically filtered from public display
- **Easy Management**: Admin interface for controlling persistence settings

## Persistent Shortcodes

The following shortcodes will remain functional after plugin deletion:

### Services
- `[zen_services]` - Display list of services
- `[zen_service id="1" field="service_name"]` - Display single service field
- `[zen_service id="1"]` - Display full service details
- `[zen_service_image id="1"]` - Display service image
- `[zen_pinned_services]` - Display only pinned services

### Locations
- `[zen_locations]` - Display list of locations
- `[zen_location id="1" field="location_name"]` - Display single location field
- `[zen_location id="1"]` - Display full location details
- `[zen_location_image id="1"]` - Display location image
- `[zen_pinned_locations]` - Display only pinned locations

### Site Management
- `[sitespren id="uuid" field="true_root_domain"]` - Display sitespren field
- `[zen_sitespren_list type="wp_sites"]` - Display list of sites
- `[zen_sitespren_count type="connected"]` - Display count of sites

## Database Tables

These tables will persist after plugin deletion (unless explicitly removed):

- `wp_zen_services` - Business services data
- `wp_zen_locations` - Location information  
- `wp_zen_sitespren` - Site configuration and metadata
- `wp_zen_orbitposts` - Post relationships and metadata

## Installation

Petrifact is automatically installed when the Snefuruplin plugin is activated. It creates:

1. **MU-Plugin File**: `wp-content/mu-plugins/petrifact.php`
2. **Registry Data**: Shortcode and table definitions
3. **Settings Options**: Persistence configuration

## Configuration

### Admin Interface

Access Petrifact settings at: **Settings → Petrifact Settings**

Available options:
- **Enable Petrifact**: Install and maintain the MU-plugin
- **Preserve Data on Uninstall**: Keep database tables when plugin is removed
- **Auto-update Petrifact**: Regenerate MU-plugin when shortcodes change

### Manual Operations

- **Rebuild MU-Plugin**: Regenerates the persistence layer with latest shortcodes
- **Remove MU-Plugin**: Removes the MU-plugin (data remains)
- **Status Check**: View installation status and table health

## How It Works

### 1. Plugin Activation
```php
// Creates database tables
Ruplin_WP_Database_Horse_Class::create_tables();

// Installs Petrifact MU-plugin
Petrifact_Generator::generate_and_install();
```

### 2. MU-Plugin Generation
The generator extracts minimal code from the main plugin classes:
- Shortcode registration and handlers
- Basic database query methods
- Security filtering for sensitive fields
- Simple HTML output generation

### 3. Runtime Operation
When WordPress loads, the MU-plugin:
- Registers all persistent shortcodes
- Provides lightweight database access
- Renders content using simplified templates
- Applies security filters to protect sensitive data

## Security Features

### Field Filtering
Sensitive fields are automatically blocked from shortcode output:
- `wpuser1`, `wppass1` - WordPress credentials
- `api_key`, `api_secret` - API authentication
- `auth_token`, `private_key` - Security tokens

### Access Control
Admin-only fields require proper capabilities:
- User relationships (`fk_users_id`)
- Account associations (`fk_domreg_hostaccount`)
- Installation flags (`wp_plugin_installed1`)

## Development

### Adding New Shortcodes

1. **Register in Registry**:
```php
// In Petrifact_Registry::get_persistent_shortcodes()
'my_new_shortcode' => array(
    'description' => 'My new shortcode',
    'callback' => 'render_my_shortcode',
    'requires_tables' => array('zen_services'),
    'required_fields' => array('service_id', 'service_name'),
    'supports_attributes' => array('id', 'field')
)
```

2. **Update Version**:
```php
// In Petrifact_Registry
const PETRIFACT_VERSION = '1.1.0'; // Bump version
```

3. **Rebuild MU-Plugin**:
The plugin will automatically detect the version change and regenerate the MU-plugin on the next admin page load.

### Testing

Use the included test script:
```bash
# Basic syntax and class loading test
node test-petrifact.php
```

For WordPress environment testing:
1. Activate the plugin in a test environment
2. Check that `wp-content/mu-plugins/petrifact.php` exists
3. Test shortcodes in posts/pages
4. Deactivate main plugin and verify shortcodes still work

## Troubleshooting

### Common Issues

**MU-Plugin Not Created**
- Check directory permissions for `wp-content/mu-plugins/`
- Verify plugin activation completed successfully
- Check error logs for PHP errors

**Shortcodes Not Working**
- Ensure MU-plugin file exists and is readable
- Check that required database tables exist
- Verify shortcode syntax matches registry definitions

**Version Mismatch**
- Go to Settings → Petrifact Settings
- Click "Rebuild Petrifact MU-Plugin"
- Check that version numbers match

### Debug Information

Check the following for diagnostics:
- WordPress debug log
- Petrifact settings page status section
- Database table existence
- MU-plugin file permissions

## File Structure

```
snefuruplin/
├── includes/
│   └── petrifact/
│       ├── class-petrifact-registry.php    # Shortcode definitions
│       ├── class-petrifact-generator.php   # MU-plugin generator
│       └── class-petrifact-settings.php    # Admin interface
└── uninstall.php                           # Handles cleanup options

wp-content/
└── mu-plugins/
    └── petrifact.php                       # Generated persistence layer
```

## Compatibility

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **Elementor**: Compatible with Shortcode widget
- **Page Builders**: Works with any builder supporting shortcodes

## Best Practices

1. **Test Before Production**: Always test persistence in staging environment
2. **Regular Backups**: Backup database before major plugin updates
3. **Monitor Status**: Check Petrifact status page after plugin updates
4. **Document Dependencies**: Keep track of which content uses persistent shortcodes
5. **Plan Migrations**: Consider persistence needs when moving sites

## Support

For issues related to Petrifact:
1. Check this documentation
2. Review WordPress debug logs  
3. Test in clean WordPress environment
4. Report bugs through the plugin's issue tracker