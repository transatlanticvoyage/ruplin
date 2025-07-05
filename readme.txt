=== Snefuruplin ===
Contributors: yourname
Tags: analytics, monitoring, cloud, management, performance
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 4.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Snefuruplin WordPress plugin for handling image uploads and integration with Snefuru system.

== Description ==

Snefuruplin seamlessly integrates your WordPress website with the Snefuru platform, providing:

* **Real-time Site Monitoring** - Track performance, security, and content metrics
* **Automated Data Sync** - Hourly, daily, or custom sync intervals
* **Remote Management** - Receive and execute instructions from your cloud dashboard
* **Comprehensive Analytics** - Detailed insights into your site's health and performance
* **Security Monitoring** - Track login attempts, plugin updates, and security status
* **Performance Optimization** - Monitor memory usage, database performance, and more

= Key Features =

* Automatic data collection and synchronization
* Secure API communication with your Snefuru Cloud instance
* Real-time performance monitoring
* Security audit capabilities
* Remote maintenance task execution
* Comprehensive activity logging
* Easy configuration through WordPress admin

= Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* Active Snefuru Cloud account and API key
* cURL support (standard with most hosting)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/snefuruplin/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to 'Snefuru' in your WordPress admin menu
4. Enter your Snefuru Cloud API key and URL in Settings
5. Test the connection and start monitoring!

= Manual Installation =

1. Download the plugin zip file
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" and select the zip file
4. Install and activate the plugin
5. Configure your API settings under Snefuru > Settings

== Frequently Asked Questions ==

= Do I need a Snefuru Cloud account? =

Yes, this plugin requires an active Snefuru Cloud account and API key to function. The plugin connects to your cloud dashboard to send data and receive instructions.

= Is my data secure? =

Absolutely. All communication between your site and the Snefuru Cloud platform is encrypted using HTTPS. Your API key is stored securely and only transmitted over secure connections.

= How often does the plugin sync data? =

By default, the plugin syncs data hourly. You can adjust this to twice daily or daily based on your needs. You can also trigger manual syncs at any time.

= Will this slow down my website? =

No, the plugin is designed to run efficiently in the background. Data collection and API calls are optimized and won't impact your site's frontend performance.

= Can I customize what data is collected? =

Currently, the plugin collects standard site metrics including performance, security, content statistics, and system information. Future versions will include more granular control options.

== Screenshots ==

1. Main dashboard showing connection status and site overview
2. Settings page for API configuration
3. Activity logs showing sync history and status
4. Performance metrics and site statistics

== Changelog ==

= 4.1.0 =
* Initial release
* Core functionality for data collection and API communication
* WordPress admin interface
* Automated sync scheduling
* Activity logging system
* Security and performance monitoring

== Upgrade Notice ==

= 4.1.0 =
Updated release of Snefuruplin with consistent naming.

== Privacy Policy ==

This plugin collects and transmits site data to your configured Snefuru Cloud instance. Data collected includes:
- Site configuration and WordPress version
- Performance metrics (memory usage, database size)
- Security information (plugin status, failed login attempts)
- Content statistics (post counts, user counts)
- System information (PHP version, server details)

No personal user data or content is transmitted. All data is sent securely to your own Snefuru Cloud instance under your control. 