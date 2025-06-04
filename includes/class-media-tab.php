<?php
/**
 * Snefuru Media Tab Handler
 * 
 * Handles custom media library tab functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Snefuru_Media_Tab {
    
    public function __construct() {
        add_filter('media_upload_tabs', array($this, 'add_narplichtab'));
        add_action('media_upload_narplichtab', array($this, 'narplichtab_handler'));
        add_filter('media_upload_default_tab', array($this, 'set_default_tab'));
    }
    
    /**
     * Add the narplichtab to media upload tabs
     */
    public function add_narplichtab($tabs) {
        $tabs['narplichtab'] = __('Narplichtab', 'snefuru-plugin');
        return $tabs;
    }
    
    /**
     * Set narplichtab as the default active tab
     */
    public function set_default_tab($default_tab) {
        error_log('Snefuru: Default tab called, returning narplichtab instead of: ' . $default_tab);
        return 'narplichtab';
    }
    
    /**
     * Handle the narplichtab content and functionality
     */
    public function narplichtab_handler() {
        wp_iframe(array($this, 'narplichtab_content'));
    }
    
    /**
     * Display the content of the narplichtab
     */
    public function narplichtab_content() {
        // Handle form submissions
        if (isset($_POST['send']) && !empty($_POST['attachments'])) {
            $this->handle_media_send();
            return;
        }
        
        // Display the tab content
        $this->render_tab_interface();
    }
    
    /**
     * Render the main interface for the narplichtab
     */
    private function render_tab_interface() {
        // Get images from Snefuru API or database
        $images = $this->get_snefuru_images();
        
        ?>
        <style>
            .snefuru-media-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
                padding: 20px;
            }
            .snefuru-media-item {
                border: 2px solid #ddd;
                border-radius: 4px;
                padding: 10px;
                text-align: center;
                cursor: pointer;
                transition: border-color 0.3s ease;
            }
            .snefuru-media-item:hover {
                border-color: #0073aa;
            }
            .snefuru-media-item.selected {
                border-color: #0073aa;
                background-color: #f0f8ff;
            }
            .snefuru-media-item img {
                max-width: 100%;
                height: auto;
                max-height: 120px;
                object-fit: cover;
            }
            .snefuru-media-info {
                margin-top: 8px;
                font-size: 12px;
                color: #666;
            }
            .snefuru-toolbar {
                padding: 10px 20px;
                border-bottom: 1px solid #ddd;
                background: #f9f9f9;
            }
            .snefuru-search {
                width: 300px;
                padding: 5px 10px;
                margin-right: 10px;
            }
            .snefuru-submit {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #0073aa;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
            }
            .snefuru-submit:disabled {
                background: #ccc;
                cursor: not-allowed;
            }
        </style>
        
        <div class="snefuru-toolbar">
            <input type="text" class="snefuru-search" placeholder="Search Snefuru Images..." id="snefuru-search">
            <button type="button" onclick="refreshSnefuruImages()">Refresh</button>
        </div>
        
        <form method="post" enctype="multipart/form-data">
            <div class="snefuru-media-grid" id="snefuru-media-grid">
                <?php if (empty($images)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                        <p>No Snefuru images found.</p>
                        <p><a href="<?php echo admin_url('admin.php?page=snefuru-admin'); ?>" target="_blank">Generate some images first</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($images as $index => $image): ?>
                        <div class="snefuru-media-item" onclick="selectSnefuruImage(this, <?php echo $index; ?>)">
                            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['prompt'] ?? 'Snefuru Image'); ?>">
                            <div class="snefuru-media-info">
                                <div><strong><?php echo esc_html($image['prompt'] ?? 'Generated Image'); ?></strong></div>
                                <div>Size: <?php echo esc_html($image['width'] ?? 'Unknown'); ?>x<?php echo esc_html($image['height'] ?? 'Unknown'); ?></div>
                                <?php if (!empty($image['created_at'])): ?>
                                    <div>Created: <?php echo esc_html(date('M j, Y', strtotime($image['created_at']))); ?></div>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="attachments[<?php echo $index; ?>][url]" value="<?php echo esc_url($image['url']); ?>">
                            <input type="hidden" name="attachments[<?php echo $index; ?>][title]" value="<?php echo esc_attr($image['prompt'] ?? 'Snefuru Image'); ?>">
                            <input type="hidden" name="attachments[<?php echo $index; ?>][width]" value="<?php echo esc_attr($image['width'] ?? ''); ?>">
                            <input type="hidden" name="attachments[<?php echo $index; ?>][height]" value="<?php echo esc_attr($image['height'] ?? ''); ?>">
                            <input type="checkbox" name="send[]" value="<?php echo $index; ?>" style="display: none;" class="snefuru-checkbox">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($images)): ?>
                <button type="submit" name="send" class="snefuru-submit" id="snefuru-submit" disabled>
                    Insert Selected Image(s)
                </button>
            <?php endif; ?>
        </form>
        
        <script>
            let selectedImages = [];
            
            function selectSnefuruImage(element, index) {
                const checkbox = element.querySelector('.snefuru-checkbox');
                const isSelected = element.classList.contains('selected');
                
                if (isSelected) {
                    element.classList.remove('selected');
                    checkbox.checked = false;
                    selectedImages = selectedImages.filter(i => i !== index);
                } else {
                    element.classList.add('selected');
                    checkbox.checked = true;
                    selectedImages.push(index);
                }
                
                updateSubmitButton();
            }
            
            function updateSubmitButton() {
                const submitButton = document.getElementById('snefuru-submit');
                if (submitButton) {
                    submitButton.disabled = selectedImages.length === 0;
                    submitButton.textContent = selectedImages.length === 1 ? 
                        'Insert Selected Image' : 
                        `Insert ${selectedImages.length} Selected Images`;
                }
            }
            
            function refreshSnefuruImages() {
                window.location.reload();
            }
            
            // Search functionality
            document.getElementById('snefuru-search').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const mediaItems = document.querySelectorAll('.snefuru-media-item');
                
                mediaItems.forEach(item => {
                    const prompt = item.querySelector('.snefuru-media-info strong').textContent.toLowerCase();
                    if (prompt.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        </script>
        <?php
    }
    
    /**
     * Get Snefuru images from API or database
     */
    private function get_snefuru_images() {
        // Try to get images from API first
        $api_client = new Snefuru_API_Client();
        $images = $api_client->get_generated_images();
        
        // If API fails, return sample data for demonstration
        if (empty($images)) {
            $images = array(
                array(
                    'url' => 'https://via.placeholder.com/300x200/0073aa/ffffff?text=Sample+Snefuru+Image+1',
                    'prompt' => 'Sample generated image 1',
                    'width' => 300,
                    'height' => 200,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                ),
                array(
                    'url' => 'https://via.placeholder.com/400x300/ff6b35/ffffff?text=Sample+Snefuru+Image+2',
                    'prompt' => 'Sample generated image 2',
                    'width' => 400,
                    'height' => 300,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                ),
                array(
                    'url' => 'https://via.placeholder.com/350x250/28a745/ffffff?text=Sample+Snefuru+Image+3',
                    'prompt' => 'Sample generated image 3',
                    'width' => 350,
                    'height' => 250,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
                )
            );
        }
        
        return $images;
    }
    
    /**
     * Handle sending selected media to the editor
     */
    private function handle_media_send() {
        if (empty($_POST['send']) || !is_array($_POST['send'])) {
            return;
        }
        
        $selected_indices = $_POST['send'];
        $attachments = $_POST['attachments'] ?? array();
        
        foreach ($selected_indices as $index) {
            if (!isset($attachments[$index])) {
                continue;
            }
            
            $attachment = $attachments[$index];
            $url = $attachment['url'];
            $title = $attachment['title'];
            $width = $attachment['width'] ?? '';
            $height = $attachment['height'] ?? '';
            
            // Create the HTML for insertion
            $html = sprintf(
                '<img src="%s" alt="%s" title="%s"%s%s class="snefuru-generated-image">',
                esc_url($url),
                esc_attr($title),
                esc_attr($title),
                $width ? ' width="' . esc_attr($width) . '"' : '',
                $height ? ' height="' . esc_attr($height) . '"' : ''
            );
            
            // Send to editor
            ?>
            <script type="text/javascript">
                var win = window.dialogArguments || opener || parent || top;
                win.send_to_editor('<?php echo addslashes($html); ?>');
            </script>
            <?php
        }
    }
} 