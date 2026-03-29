/**
 * Spearhead Authorization Page Enhancement
 * 
 * Captures the application password when created and sends it back
 * to the calling application via the success URL.
 */

(function($) {
    'use strict';
    
    // Wait for DOM ready
    $(document).ready(function() {
        console.log('Spearhead Auth Connector: Initializing on authorization page');
        
        // Store original success URL
        const successUrl = spearhead_auth.success_url;
        const appName = spearhead_auth.app_name;
        
        if (!successUrl) {
            console.log('Spearhead Auth: No success URL provided');
            return;
        }
        
        console.log('Spearhead Auth: Success URL:', successUrl);
        console.log('Spearhead Auth: App Name:', appName);
        
        // Monitor for the password display
        observePasswordCreation();
        
        /**
         * Observe DOM for password creation
         */
        function observePasswordCreation() {
            // Look for the application password when it's displayed
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    // Check if password was added to the page
                    checkForPassword();
                });
            });
            
            // Start observing the document body for changes
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Also check immediately in case password is already there
            checkForPassword();
        }
        
        /**
         * Check if password is displayed and capture it
         */
        function checkForPassword() {
            // WordPress displays the password in a code element with specific markup
            // Look for the new application password display
            const passwordElements = document.querySelectorAll('code.application-password');
            
            if (passwordElements.length > 0) {
                console.log('Spearhead Auth: Password element found');
                
                const passwordElement = passwordElements[0];
                const password = passwordElement.textContent.trim();
                
                if (password && password.length > 0) {
                    console.log('Spearhead Auth: Password captured, length:', password.length);
                    
                    // Get the username (should be displayed nearby)
                    const username = captureUsername();
                    
                    // Send credentials back to success URL
                    sendCredentialsToSuccessUrl(username, password);
                }
            }
            
            // Alternative: Check for the password in the success notice
            const successNotice = document.querySelector('.notice-success');
            if (successNotice) {
                const codeElements = successNotice.querySelectorAll('code');
                codeElements.forEach(function(elem) {
                    const text = elem.textContent.trim();
                    // Application passwords are typically 24 characters with spaces
                    if (text.length >= 24 && text.indexOf(' ') > -1) {
                        console.log('Spearhead Auth: Found potential password in success notice');
                        const username = captureUsername();
                        sendCredentialsToSuccessUrl(username, text);
                    }
                });
            }
        }
        
        /**
         * Capture the username from the page
         */
        function captureUsername() {
            // Try to get username from the logged-in user display
            const userDisplay = document.querySelector('#wp-admin-bar-my-account .display-name');
            if (userDisplay) {
                return userDisplay.textContent.trim();
            }
            
            // Try to get from user login field if visible
            const userLoginField = document.querySelector('input[name="user_login"]');
            if (userLoginField && userLoginField.value) {
                return userLoginField.value;
            }
            
            // Default to admin if can't find
            console.log('Spearhead Auth: Could not capture username, using default');
            return 'admin';
        }
        
        /**
         * Send credentials back to the success URL
         */
        function sendCredentialsToSuccessUrl(username, password) {
            if (!successUrl) {
                console.error('Spearhead Auth: No success URL to send credentials to');
                return;
            }
            
            // Build the callback URL with credentials
            const url = new URL(successUrl);
            url.searchParams.append('spearhead_auth', 'true');
            url.searchParams.append('username', username);
            url.searchParams.append('password', password);
            url.searchParams.append('app_name', appName);
            url.searchParams.append('auth_success', 'true');
            
            console.log('Spearhead Auth: Redirecting to success URL with credentials');
            
            // Add a visible message for the user
            showRedirectMessage();
            
            // Redirect after a short delay to allow user to see the message
            setTimeout(function() {
                window.location.href = url.toString();
            }, 2000);
        }
        
        /**
         * Show redirect message to user
         */
        function showRedirectMessage() {
            const message = $('<div>')
                .addClass('notice notice-success spearhead-redirect-notice')
                .css({
                    'position': 'fixed',
                    'top': '50%',
                    'left': '50%',
                    'transform': 'translate(-50%, -50%)',
                    'z-index': '100000',
                    'padding': '20px',
                    'background': '#4CAF50',
                    'color': 'white',
                    'border-radius': '5px',
                    'box-shadow': '0 4px 6px rgba(0,0,0,0.1)',
                    'font-size': '16px',
                    'text-align': 'center'
                })
                .html('<strong>Spearhead System:</strong><br>Application password created successfully!<br>Redirecting back to your application...');
            
            $('body').append(message);
        }
        
        /**
         * Also intercept the form submission for approve button
         */
        const approveForm = document.querySelector('form#approve');
        if (approveForm) {
            console.log('Spearhead Auth: Found approve form, adding submit handler');
            
            approveForm.addEventListener('submit', function(e) {
                console.log('Spearhead Auth: Approve form submitted');
                
                // Let the form submit naturally, but set up monitoring
                // for the response page
                sessionStorage.setItem('spearhead_waiting_for_password', 'true');
                sessionStorage.setItem('spearhead_success_url', successUrl);
                sessionStorage.setItem('spearhead_app_name', appName);
            });
        }
        
        // Check if we're on the page after approval
        if (sessionStorage.getItem('spearhead_waiting_for_password') === 'true') {
            console.log('Spearhead Auth: Checking for password after approval');
            
            // Clear the flag
            sessionStorage.removeItem('spearhead_waiting_for_password');
            
            // Restore URLs from session
            const storedSuccessUrl = sessionStorage.getItem('spearhead_success_url');
            const storedAppName = sessionStorage.getItem('spearhead_app_name');
            
            if (storedSuccessUrl) {
                // Override with stored values
                spearhead_auth.success_url = storedSuccessUrl;
                spearhead_auth.app_name = storedAppName;
                
                // Check for password immediately
                setTimeout(function() {
                    checkForPassword();
                }, 500);
            }
            
            // Clean up session storage
            sessionStorage.removeItem('spearhead_success_url');
            sessionStorage.removeItem('spearhead_app_name');
        }
    });
    
})(jQuery);