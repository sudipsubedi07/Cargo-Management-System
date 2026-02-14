<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'config.php';

// Fetch existing settings from database
$settings_query = "SELECT * FROM system_settings LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
$settings = mysqli_fetch_assoc($settings_result);

// Get all timezones
$timezones = DateTimeZone::listIdentifiers();
?>



<div class="settings-wrapper">
    <h2 class="settings-title">System Settings</h2>
    
    <form id="settingsForm" action="admin_save_settings.php" method="POST">
        <!-- General Settings -->
        <fieldset class="settings-card">
            <legend>General Settings</legend>
            <div class="form-group">
                <label for="systemName">System Name:</label>
                <input type="text" 
                       id="systemName" 
                       name="systemName" 
                       value="<?php echo htmlspecialchars($settings['system_name'] ?? 'CarGO'); ?>" 
                       required>
                <div class="error-message" id="systemNameError"></div>
            </div>
            <div class="form-group">
                <label for="timezone">Timezone:</label>
                <select id="timezone" name="timezone">
                    <?php foreach ($timezones as $tz): ?>
                        <option value="<?php echo htmlspecialchars($tz); ?>" 
                                <?php echo ($settings['timezone'] ?? 'UTC') === $tz ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tz); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </fieldset>
        
        <!-- User Management -->
        <fieldset class="settings-card">
            <legend>User Management</legend>
            <div class="form-group">
                <label for="maxUsers">Maximum Users Allowed:</label>
                <input type="number" 
                       id="maxUsers" 
                       name="maxUsers" 
                       min="1" 
                       value="<?php echo htmlspecialchars($settings['max_users'] ?? '100'); ?>" 
                       required>
                <div class="error-message" id="maxUsersError"></div>
            </div>
        </fieldset>

        <!-- Cargo Handling Settings -->
        <fieldset class="settings-card">
            <legend>Cargo Handling</legend>
            <div class="form-group">
                <label for="defaultWeightUnit">Default Weight Unit:</label>
                <select id="defaultWeightUnit" name="defaultWeightUnit">
                    <option value="kg" <?php echo ($settings['default_weight_unit'] ?? '') === 'kg' ? 'selected' : ''; ?>>
                        Kilograms (kg)
                    </option>
                    <option value="lbs" <?php echo ($settings['default_weight_unit'] ?? '') === 'lbs' ? 'selected' : ''; ?>>
                        Pounds (lbs)
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label for="maxCargoWeight">Maximum Cargo Weight (kg):</label>
                <input type="number" 
                       id="maxCargoWeight" 
                       name="maxCargoWeight" 
                       min="1" 
                       value="<?php echo htmlspecialchars($settings['max_cargo_weight'] ?? '1000'); ?>" 
                       required>
                <div class="error-message" id="maxCargoWeightError"></div>
            </div>
        </fieldset>

        
        <div class="form-actions">
            <button type="submit" class="save-button">Save Settings</button>
            <button type="reset" class="reset-button">Reset Changes</button>
        </div>
    </form>
</div>

<style>

.settings-wrapper {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.settings-title {
    color: #333;
    margin-bottom: 20px;
    font-size: 24px;
}

.settings-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.settings-card legend {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    padding: 0 10px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #555;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 10px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.save-button {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.reset-button {
    background-color: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.save-button:hover {
    background-color: #45a049;
}

.reset-button:hover {
    background-color: #5a6268;
}

.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
    display: none;
}

.settings-card.error {
    border-color: #dc3545;
}

@media (max-width: 768px) {
    .settings-wrapper {
        padding: 10px;
    }
    
    .settings-card {
        padding: 15px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .save-button,
    .reset-button {
        width: 100%;
    }
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 4px;
    color: white;
    font-size: 14px;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
}

.notification.success {
    background-color: #4CAF50;
}

.notification.error {
    background-color: #dc3545;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }  
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('settingsForm');
    let originalFormData = new FormData(form);

    // Form validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        
        // Reset error states
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.settings-card').forEach(el => el.classList.remove('error'));

        // Validate System Name
        const systemName = document.getElementById('systemName').value.trim();
        if (systemName.length < 3) {
            showError('systemName', 'System name must be at least 3 characters long');
            isValid = false;
        }

        // Validate Max Users
        const maxUsers = parseInt(document.getElementById('maxUsers').value);
        if (isNaN(maxUsers) || maxUsers < 1) {
            showError('maxUsers', 'Maximum users must be at least 1');
            isValid = false;
        }

        // Validate Max Cargo Weight
        const maxCargoWeight = parseInt(document.getElementById('maxCargoWeight').value);
        if (isNaN(maxCargoWeight) || maxCargoWeight < 1) {
            showError('maxCargoWeight', 'Maximum cargo weight must be at least 1 kg');
            isValid = false;
        }

        if (isValid) {
            // Create form data for submission
            const formData = new FormData(form);
            
            // Send AJAX request
            fetch('admin_save_settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Settings saved successfully!', 'success');
                    
                    // Update system name in all places it appears
                    updateSystemName(data.systemName);
                    
                    // Update the original form data to reflect the new state
                    originalFormData = new FormData(form);
                } else {
                    showNotification('Error saving settings: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to save settings. Please try again.', 'error');
            });
        }
    });

    // Reset form to original values
    document.querySelector('.reset-button').addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reset all changes?')) {
            form.reset();
            originalFormData.forEach((value, key) => {
                const element = form.elements[key];
                if (element) {
                    if (element.type === 'checkbox') {
                        element.checked = value === 'on';
                    } else {
                        element.value = value;
                    }
                }
            });
        }
    });

    // Helper functions
    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId + 'Error');
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        document.getElementById(fieldId).closest('.settings-card').classList.add('error');
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Function to update system name throughout the application
    function updateSystemName(newName) {
        // Store the new system name in localStorage for other pages to use
        localStorage.setItem('systemName', newName);
        
        // Update page title if it contains the old system name
        if (document.title.includes('CarGO') || document.title.includes('CargoPro')) {
            document.title = document.title.replace(/CarGO|CargoPro/, newName);
        }
        
        // Update all elements with class 'system-name'
        document.querySelectorAll('.system-name').forEach(element => {
            element.textContent = newName;
        });
        
        // If we're in an iframe or part of a larger application, notify the parent
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    type: 'systemNameChanged',
                    newName: newName
                }, '*');
            }
        } catch (e) {
            console.error('Error posting message to parent:', e);
        }
    }
});
</script>