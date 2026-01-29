<?php
/**
 * Change Password Mar - Password Change Management
 * Part of the Ruplin plugin - Change password functionality for current user
 */

class Ruplin_Change_Password_Mar {
    
    public function __construct() {
        // AJAX handler for password change
        add_action('wp_ajax_ruplin_change_password', array($this, 'ajax_change_password'));
    }
    
    /**
     * Render the change password page
     */
    public function render_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;
        $user_email = $current_user->user_email;
        ?>
        
        <div class="wrap">
            <h1 style="display: flex; align-items: center; gap: 14px; margin: 0 0 20px 0;">
                🔐 Change Password Mar
            </h1>
            
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">
                <h3 style="color: #856404; margin-top: 0;">⚠️ Password Change Tool</h3>
                <p style="color: #856404; margin-bottom: 0;">
                    This tool allows you to change your WordPress password securely. Your new password will be applied immediately.
                </p>
            </div>

            <!-- Current User Information -->
            <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h2>Current User Information</h2>
                <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 15px; align-items: center; max-width: 600px;">
                    <strong>Username:</strong>
                    <input type="text" id="current-username" value="<?php echo esc_attr($username); ?>" readonly 
                           style="padding: 8px; border: 1px solid #ddd; border-radius: 3px; background: #f8f9fa; font-family: monospace;">
                    <button type="button" class="copy-btn button button-secondary" data-copy-target="current-username">Copy</button>
                    
                    <strong>Email:</strong>
                    <input type="text" id="current-email" value="<?php echo esc_attr($user_email); ?>" readonly 
                           style="padding: 8px; border: 1px solid #ddd; border-radius: 3px; background: #f8f9fa; font-family: monospace;">
                    <button type="button" class="copy-btn button button-secondary" data-copy-target="current-email">Copy</button>
                </div>
            </div>

            <!-- Password Generator -->
            <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h2>Generate New Password</h2>
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <input type="text" id="generated-password" placeholder="Generated password will appear here..." 
                           style="flex: 1; padding: 12px; border: 2px solid #007cba; border-radius: 5px; font-family: monospace; font-size: 16px; background: #f8fff8;">
                    <button type="button" id="generate-password" class="button" 
                            style="padding: 12px 20px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        🎲 Generate Password
                    </button>
                    <button type="button" class="copy-btn button button-secondary" data-copy-target="generated-password">📋 Copy</button>
                </div>
                
                <!-- Password strength indicator -->
                <div id="password-strength" style="margin-top: 10px; padding: 8px; border-radius: 3px; font-weight: bold; text-align: center; display: none;"></div>
            </div>

            <!-- Password Change Form -->
            <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
                <h2>Change Password</h2>
                <form id="change-password-form">
                    <div style="margin-bottom: 15px;">
                        <label for="new-password" style="display: block; font-weight: bold; margin-bottom: 5px;">New Password:</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="password" id="new-password" name="new_password" required 
                                   style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-family: monospace; font-size: 16px;"
                                   placeholder="Enter or paste your new password">
                            <button type="button" id="toggle-password" class="button button-secondary" style="padding: 12px;">👁️</button>
                            <button type="button" id="use-generated" class="button button-secondary" style="padding: 12px;">Use Generated</button>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label>
                            <input type="checkbox" id="confirm-password-change" style="margin-right: 8px;">
                            I understand this will change my password and I may need to log in again
                        </label>
                    </div>
                    
                    <button type="submit" id="submit-password-change" class="button button-primary" disabled 
                            style="background: #dc3545; border-color: #dc3545; padding: 12px 24px; font-size: 16px; font-weight: bold;">
                        🔐 Change Password
                    </button>
                </form>
            </div>

            <!-- Success/Error Messages -->
            <div id="password-change-result" style="margin-top: 20px; display: none;">
                <div id="result-content"></div>
            </div>

            <!-- Hidden form for LastPass detection -->
            <div style="position: absolute; left: -9999px; visibility: hidden;">
                <form id="lastpass-form" method="post">
                    <input type="text" name="username" id="lastpass-username" value="<?php echo esc_attr($username); ?>">
                    <input type="password" name="password" id="lastpass-password">
                    <input type="submit" value="Login">
                </form>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            
            // Password generator function
            function generatePassword(length = 12) {
                const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*";
                let password = "";
                
                // Ensure at least one of each type
                const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                const lower = "abcdefghijklmnopqrstuvwxyz"; 
                const numbers = "0123456789";
                const symbols = "!@#$%^&*";
                
                password += upper[Math.floor(Math.random() * upper.length)];
                password += lower[Math.floor(Math.random() * lower.length)];
                password += numbers[Math.floor(Math.random() * numbers.length)];
                password += symbols[Math.floor(Math.random() * symbols.length)];
                
                // Fill the rest randomly
                for (let i = 4; i < length; i++) {
                    password += charset[Math.floor(Math.random() * charset.length)];
                }
                
                // Shuffle the password
                return password.split('').sort(() => Math.random() - 0.5).join('');
            }
            
            // Password strength checker
            function checkPasswordStrength(password) {
                let strength = 0;
                let feedback = "";
                
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[a-z]/.test(password)) strength += 1;
                if (/\d/.test(password)) strength += 1;
                if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\?]/.test(password)) strength += 1;
                
                const $indicator = $('#password-strength');
                
                if (strength === 0) {
                    $indicator.hide();
                } else if (strength <= 2) {
                    $indicator.show().css('background', '#f8d7da').css('color', '#721c24').text('Weak Password');
                } else if (strength <= 4) {
                    $indicator.show().css('background', '#fff3cd').css('color', '#856404').text('Medium Password');
                } else {
                    $indicator.show().css('background', '#d1e7dd').css('color', '#0f5132').text('Strong Password');
                }
            }
            
            // Generate password button
            $('#generate-password').on('click', function() {
                const password = generatePassword(12);
                $('#generated-password').val(password);
                checkPasswordStrength(password);
            });
            
            // Use generated password button
            $('#use-generated').on('click', function() {
                const generatedPassword = $('#generated-password').val();
                if (generatedPassword) {
                    $('#new-password').val(generatedPassword);
                    checkPasswordStrength(generatedPassword);
                } else {
                    alert('Please generate a password first');
                }
            });
            
            // Password visibility toggle
            $('#toggle-password').on('click', function() {
                const $input = $('#new-password');
                const type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);
                $(this).text(type === 'password' ? '👁️' : '🙈');
            });
            
            // Check password strength on input
            $('#new-password').on('input', function() {
                checkPasswordStrength($(this).val());
            });
            
            // Enable submit button when checkbox is checked
            $('#confirm-password-change').on('change', function() {
                $('#submit-password-change').prop('disabled', !this.checked);
            });
            
            // Copy functionality
            $('.copy-btn').on('click', function() {
                const targetId = $(this).data('copy-target');
                const $target = $('#' + targetId);
                const text = $target.val();
                
                if (!text) {
                    alert('Nothing to copy');
                    return;
                }
                
                // Create temporary input for copying
                const $temp = $('<input>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                
                // Visual feedback
                const originalText = $(this).text();
                $(this).text('✓ Copied!').css('background', '#28a745').css('color', 'white');
                setTimeout(() => {
                    $(this).text(originalText).css('background', '').css('color', '');
                }, 2000);
            });
            
            // Form submission
            $('#change-password-form').on('submit', function(e) {
                e.preventDefault();
                
                const newPassword = $('#new-password').val();
                if (!newPassword) {
                    alert('Please enter a new password');
                    return;
                }
                
                if (newPassword.length < 6) {
                    alert('Password must be at least 6 characters long');
                    return;
                }
                
                const oldPassword = newPassword; // Store for success message
                
                // Show loading
                $('#submit-password-change').prop('disabled', true).text('Changing Password...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ruplin_change_password',
                        new_password: newPassword,
                        nonce: '<?php echo wp_create_nonce('ruplin_change_password'); ?>'
                    },
                    success: function(response) {
                        $('#submit-password-change').prop('disabled', false).text('🔐 Change Password');
                        
                        if (response.success) {
                            // Show success message with old and new passwords
                            const successHtml = `
                                <div style="background: #d1e7dd; border: 1px solid #a3cfbb; padding: 20px; border-radius: 5px;">
                                    <h3 style="color: #0f5132; margin-top: 0;">✅ Password Changed Successfully!</h3>
                                    <div style="margin: 15px 0;">
                                        <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 10px; align-items: center; max-width: 600px;">
                                            <strong>New Password:</strong>
                                            <input type="text" id="success-new-password" value="${newPassword}" readonly 
                                                   style="padding: 8px; border: 1px solid #a3cfbb; border-radius: 3px; background: #f8fff8; font-family: monospace;">
                                            <button type="button" class="copy-btn button button-secondary" data-copy-target="success-new-password">Copy</button>
                                        </div>
                                    </div>
                                    <p style="color: #0f5132; margin-bottom: 0;">
                                        <strong>Important:</strong> Please save this password to your password manager. You may need to log in again.
                                    </p>
                                </div>
                            `;
                            
                            $('#result-content').html(successHtml);
                            $('#password-change-result').show();
                            
                            // Trigger LastPass detection
                            $('#lastpass-password').val(newPassword);
                            
                            // Reset form
                            $('#new-password').val('');
                            $('#generated-password').val('');
                            $('#confirm-password-change').prop('checked', false);
                            $('#password-strength').hide();
                            
                            // Re-initialize copy buttons for new elements
                            $('.copy-btn').off('click').on('click', function() {
                                const targetId = $(this).data('copy-target');
                                const $target = $('#' + targetId);
                                const text = $target.val();
                                
                                const $temp = $('<input>');
                                $('body').append($temp);
                                $temp.val(text).select();
                                document.execCommand('copy');
                                $temp.remove();
                                
                                const originalText = $(this).text();
                                $(this).text('✓ Copied!').css('background', '#28a745').css('color', 'white');
                                setTimeout(() => {
                                    $(this).text(originalText).css('background', '').css('color', '');
                                }, 2000);
                            });
                            
                        } else {
                            const errorHtml = `
                                <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px;">
                                    <h3 style="color: #721c24; margin-top: 0;">❌ Password Change Failed</h3>
                                    <p style="color: #721c24; margin-bottom: 0;">${response.data}</p>
                                </div>
                            `;
                            $('#result-content').html(errorHtml);
                            $('#password-change-result').show();
                        }
                    },
                    error: function() {
                        $('#submit-password-change').prop('disabled', false).text('🔐 Change Password');
                        const errorHtml = `
                            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px;">
                                <h3 style="color: #721c24; margin-top: 0;">❌ Connection Error</h3>
                                <p style="color: #721c24; margin-bottom: 0;">Failed to communicate with server. Please try again.</p>
                            </div>
                        `;
                        $('#result-content').html(errorHtml);
                        $('#password-change-result').show();
                    }
                });
            });
        });
        </script>
        
        <style>
        .copy-btn {
            transition: all 0.2s ease;
        }
        
        .copy-btn:hover {
            transform: translateY(-1px);
        }
        
        #submit-password-change:disabled {
            background: #6c757d !important;
            border-color: #6c757d !important;
            cursor: not-allowed;
        }
        
        #submit-password-change:not(:disabled) {
            background: #dc3545 !important;
            border-color: #dc3545 !important;
        }
        
        #submit-password-change:not(:disabled):hover {
            background: #c82333 !important;
            border-color: #bd2130 !important;
        }
        </style>
        
        <?php
    }
    
    /**
     * AJAX handler for password change
     */
    public function ajax_change_password() {
        check_ajax_referer('ruplin_change_password', 'nonce');
        
        if (!current_user_can('read')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $new_password = sanitize_text_field($_POST['new_password']);
        
        if (empty($new_password)) {
            wp_send_json_error('New password is required');
        }
        
        if (strlen($new_password) < 6) {
            wp_send_json_error('Password must be at least 6 characters long');
        }
        
        $user_id = get_current_user_id();
        $current_user = wp_get_current_user();
        
        // Update user password
        wp_set_password($new_password, $user_id);
        
        // Log the user back in to prevent session loss
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        
        // Clear any cached user data
        clean_user_cache($user_id);
        
        wp_send_json_success('Password changed successfully for user: ' . $current_user->user_login);
    }
    
    /**
     * AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
     * Based on proven Snefuruplin/Grove implementation
     */
    private function suppress_all_admin_notices() {
        // Remove notices immediately - don't wait for hooks
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        
        // Remove user admin notices
        global $wp_filter;
        if (isset($wp_filter['user_admin_notices'])) {
            unset($wp_filter['user_admin_notices']);
        }
        
        // Add immediate CSS suppression
        add_action('admin_head', function() {
            echo '<style type="text/css">
                .notice, .notice-warning, .notice-error, .notice-success, .notice-info,
                .updated, .error, .update-nag, .admin-notice,
                .wrap > .notice, .wrap > .error, .wrap > .updated,
                div[class*="notice"], div[class*="updated"], div[class*="error"] {
                    display: none !important;
                }
            </style>';
        }, 1);
        
        // Additional hook-based removal
        add_action('admin_print_styles', function() {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
        }, 0);
        
        // Nuclear option - remove on admin_notices hook itself
        add_action('admin_notices', function() {
            remove_all_actions('admin_notices');
        }, -9999);
    }
}