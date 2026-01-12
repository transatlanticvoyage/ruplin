<?php

if (!defined('ABSPATH')) {
    exit;
}

function snefuru_schema_mar_page() {
    // Get current setting
    $inject_enabled = get_option('ruplin_inject_schema_enabled', false);
    ?>
    <div class="wrap">
        <h1><strong>schema_mar - Schema Management</strong></h1>
        <p>Manage structured data and schema markup for your website</p>
        
        <!-- Automatic injection checkbox with save button -->
        <div style="margin: 20px 0; padding: 15px; background: #f1f1f1; border-radius: 5px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <label style="display: flex; align-items: center; font-size: 14px; font-weight: 500;">
                    <input type="checkbox" id="auto-inject-schema" <?php checked($inject_enabled, true); ?> style="margin-right: 8px;">
                    inject schema into page automatically
                </label>
                <button id="save-schema-settings" class="button button-primary" style="padding: 5px 15px;">
                    Save Settings
                </button>
            </div>
            <div id="save-feedback" style="display: none; margin-top: 10px; padding: 8px; background: #d4edda; 
                                           color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
                ✓ Settings saved successfully!
            </div>
        </div>
        
        <!-- Tab system -->
        <div class="schema-tabs" style="margin: 20px 0;">
            <!-- Tab navigation -->
            <div class="tab-nav" style="border-bottom: 1px solid #ccc; margin-bottom: 20px;">
                <button class="tab-btn active" data-tab="example-code" 
                        style="background: #0073aa; color: white; border: none; padding: 10px 20px; 
                               margin-right: 5px; cursor: pointer; border-radius: 5px 5px 0 0;">
                    example code
                </button>
                <button class="tab-btn" data-tab="tab2" 
                        style="background: #f1f1f1; color: #333; border: none; padding: 10px 20px; 
                               margin-right: 5px; cursor: pointer; border-radius: 5px 5px 0 0;">
                    tab 2
                </button>
            </div>
            
            <!-- Tab content -->
            <div class="tab-content">
                <!-- Example Code Tab -->
                <div id="tab-example-code" class="tab-panel active" style="display: block;">
                    <div class="schema-container" style="max-width: 100%;">
                        <h2>Local Business Schema Markup</h2>
                        <p>Copy the structured data below and paste it into your website's head section:</p>
                        
                        <div style="position: relative; margin-bottom: 20px;">
                            <button id="copy-schema-btn" 
                                    style="position: absolute; top: 10px; right: 10px; z-index: 10; 
                                           background: #0073aa; color: white; border: none; padding: 8px 16px; 
                                           border-radius: 4px; cursor: pointer; font-size: 12px;">
                                📋 Copy Schema
                            </button>
                            
                            <textarea id="schema-markup" readonly 
                                      style="width: 100%; height: 600px; font-family: 'Courier New', monospace; 
                                             font-size: 12px; padding: 15px; border: 2px solid #ddd; 
                                             border-radius: 4px; background: #f9f9f9; resize: vertical; 
                                             box-sizing: border-box;"><?php echo esc_textarea(get_schema_markup_content()); ?></textarea>
                        </div>
                        
                        <div id="copy-feedback" style="display: none; padding: 10px; background: #d4edda; 
                                                       color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; 
                                                       margin-top: 10px;">
                            ✓ Schema markup copied to clipboard!
                        </div>
                    </div>
                </div>
                
                <!-- Tab 2 -->
                <div id="tab-tab2" class="tab-panel" style="display: none;">
                    <div style="padding: 20px; text-align: center; color: #666;">
                        <h2>Tab 2</h2>
                        <p>Content coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Save settings functionality
    document.getElementById('save-schema-settings').addEventListener('click', function() {
        var checkbox = document.getElementById('auto-inject-schema');
        var feedback = document.getElementById('save-feedback');
        var button = this;
        
        // Disable button during save
        button.disabled = true;
        button.textContent = 'Saving...';
        
        // Prepare data
        var data = new FormData();
        data.append('action', 'save_schema_injection_setting');
        data.append('inject_enabled', checkbox.checked ? '1' : '0');
        data.append('nonce', '<?php echo wp_create_nonce("schema_injection_nonce"); ?>');
        
        // Send AJAX request
        fetch(ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Show success feedback
                feedback.style.display = 'block';
                setTimeout(function() {
                    feedback.style.display = 'none';
                }, 3000);
            } else {
                alert('Failed to save settings. Please try again.');
            }
        })
        .catch(error => {
            alert('Error saving settings: ' + error);
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
            button.textContent = 'Save Settings';
        });
    });
    
    // Copy button functionality
    document.getElementById('copy-schema-btn').addEventListener('click', function() {
        var schemaTextarea = document.getElementById('schema-markup');
        var feedback = document.getElementById('copy-feedback');
        
        // Select and copy the text
        schemaTextarea.select();
        schemaTextarea.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            document.execCommand('copy');
            
            // Show feedback
            feedback.style.display = 'block';
            
            // Hide feedback after 3 seconds
            setTimeout(function() {
                feedback.style.display = 'none';
            }, 3000);
            
            // Change button text temporarily
            var originalText = this.innerHTML;
            this.innerHTML = '✓ Copied!';
            this.style.background = '#28a745';
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.background = '#0073aa';
            }, 2000);
            
        } catch (err) {
            alert('Failed to copy. Please select the text manually and copy it.');
        }
        
        // Deselect the text
        window.getSelection().removeAllRanges();
    });
    
    // Tab switching functionality
    document.addEventListener('DOMContentLoaded', function() {
        var tabButtons = document.querySelectorAll('.tab-btn');
        var tabPanels = document.querySelectorAll('.tab-panel');
        
        tabButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var targetTab = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and panels
                tabButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.background = '#f1f1f1';
                    btn.style.color = '#333';
                });
                tabPanels.forEach(panel => {
                    panel.classList.remove('active');
                    panel.style.display = 'none';
                });
                
                // Add active class to clicked button and corresponding panel
                this.classList.add('active');
                this.style.background = '#0073aa';
                this.style.color = 'white';
                
                var targetPanel = document.getElementById('tab-' + targetTab);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                    targetPanel.style.display = 'block';
                }
            });
        });
    });
    </script>
    <?php
}

function get_schema_markup_content() {
    return '<!-- Structured Data for Local Business -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Buckeye Pest Control Pros",
  "description": "Expert pest control in Buckeye, AZ 85326. Local pest removal specialists serving Verrado & Sundance. Call (602) 346-6683 for same-day service near you!",
  "telephone": "(602) 346-6683",
  "priceRange": "$",
  "openingHours": "Mo-Fr 08:00-18:00, Sa 09:00-17:00",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326",
    "addressLocality": "Buckeye",
    "addressCountry": "US"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
  },
  "serviceArea": {
    "@type": "GeoCircle",
    "geoRadius": "50000",
    "geoMidpoint": {
      "@type": "GeoCoordinates",
      "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
    }
  },
  "areaServed": [
    {
      "@type": "City",
      "name": "Sundance",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Verrado",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Tartesso",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Sun City Festival",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Windmill Village",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Sienna Hills",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Blue Horizons",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Festival Foothills",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Westpark",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Vista De Montana",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Sundance Towne Center",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Buckeye Town Center",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Dove Cove Estates",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Palo Verde",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }, {
      "@type": "City",
      "name": "Cantabria",
      "containedInPlace": {
        "@type": "City", 
        "name": "Buckeye"
      }
    }
  ],
  "hasOfferCatalog": {
    "@type": "OfferCatalog",
    "name": "Services",
    "itemListElement": [
      {
        "@type": "Offer",
        "name": "Ant Control",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }, {
        "@type": "Offer",
        "name": "Bed Bug Treatment",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }, {
        "@type": "Offer",
        "name": "Cockroach Control",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }, {
        "@type": "Offer",
        "name": "Flea Control",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }, {
        "@type": "Offer",
        "name": "Mice Control",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }, {
        "@type": "Offer",
        "name": "Rodent Control",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }, {
        "@type": "Offer",
        "name": "Spider Control",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }, {
        "@type": "Offer",
        "name": "Termite Control",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }, {
        "@type": "Offer",
        "name": "Tick Control",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }, {
        "@type": "Offer",
        "name": "Wildlife Removal",
        "category": "pest control",
        "areaServed": "Buckeye",
        "availableAtOrFrom": {
          "@type": "Place",
          "name": "Buckeye Pest Control Pros",
          "address": "6213 S Miller Rd Suite 150, Buckeye, AZ 85326"
        }
      }
    ]
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": 83
  }
}
</script>';
}