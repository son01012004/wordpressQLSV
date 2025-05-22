# Điểm Danh Feature Fix Instructions

This document provides steps to fix the "404 Error" issue with the Điểm Danh (Attendance) feature.

## Installation

1. **Upload the updated files**:
   - Make sure all the updated plugin files have been uploaded to your WordPress installation.
   - The key files that have been modified are:
     - `modules/diemdanh/class-qlsv-diemdanh.php`
     - `templates/archive-diemdanh.php`
     - `templates/page-diemdanh.php` (new file)
     - `qlsv-plugin.php`

2. **Run the refresh script**:
   - Visit `http://your-site/wordpressQLSV/wp-content/plugins/qlsv-plugin-fixed/refresh-diemdanh.php` in your browser
   - You must be logged in as an administrator to run this script
   - This will refresh the rewrite rules and test URL generation

3. **Refresh WordPress permalinks**:
   - Go to WordPress admin → Settings → Permalinks
   - Don't make any changes, just click "Save Changes" button
   - This forces WordPress to flush and regenerate the rewrite rules

4. **Check configuration**:
   - Visit `http://your-site/wordpressQLSV/wp-content/plugins/qlsv-plugin-fixed/check-diemdanh-status.php`
   - This will analyze your current configuration and suggest any additional fixes

## How the Fix Works

The fix addresses several issues with the Điểm Danh feature:

1. **URL Parameter Handling**:
   - Added proper query variable registration for 'lop' and 'mon_hoc'
   - Enhanced the `handle_diemdanh_queries()` function to correctly handle URL parameters
   - Added custom rewrite rules for diemdanh URLs with parameters

2. **Template Improvements**:
   - Created a dedicated `page-diemdanh.php` template
   - Improved parameter passing and handling in the archive template

3. **Code Structure**:
   - Modified the `QLSV_DiemDanh` class constructor to accept null loader
   - Added helper function `get_diemdanh_url()` for consistent URL generation

## Testing the Fix

1. **Check the DiemDanh archive page**:
   - Visit `http://your-site/wordpressQLSV/diemdanh/`
   - You should see a list of classes and subjects

2. **Test with parameters**:
   - Use the links on the archive page to navigate to a specific class and subject
   - The URL should look like: `http://your-site/wordpressQLSV/diemdanh/?lop=123&mon_hoc=456`
   - The page should load correctly without 404 errors

3. **Check the DiemDanh page**:
   - Visit `http://your-site/wordpressQLSV/diemdanhh/`
   - You should see the dashboard for the Điểm Danh feature

## Troubleshooting

If you're still experiencing issues after following the installation steps:

1. **Clear browser cache**:
   - Try accessing the pages in an incognito/private browsing window
   - Clear your browser cache completely

2. **Check permalink structure**:
   - Make sure your WordPress site is using a permalink structure other than "Plain"
   - "Post name" structure is recommended (Settings → Permalinks)

3. **Review error logs**:
   - Check your server error logs for any PHP errors
   - Check the WordPress debug log if enabled

4. **Run the diagnostic script again**:
   - Visit `check-diemdanh-status.php` to see if there are any remaining issues

5. **Manual rewrite flush**:
   - If permalinks still aren't working, try deactivating and reactivating the plugin
   - This will trigger a complete flush of rewrite rules

## Technical Details

The core issue was that the URL parameters weren't being properly handled in the WordPress query system. The fix implements:

- Registration of custom query variables
- Custom rewrite rules for URL with parameters
- Proper handling of the parameters in the template files
- Forcing the archive template to be used when parameters are present

The `handle_diemdanh_queries()` function was enhanced to prevent WordPress from showing a 404 error when parameters are present in the URL. 