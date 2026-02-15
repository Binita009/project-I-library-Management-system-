<footer style="background: #2c3e50; color: white; padding: 30px 0; margin-top: 50px;">
    <div class="container">
        <p style="text-align: center;">© <?php echo date('Y'); ?> Library Management System | 4th Semester Project</p>
    </div>
</footer>

<!-- Custom Alert HTML Container -->
<div id="custom-alert" class="custom-alert">
    <div class="alert-icon" id="alert-icon" style="font-size: 24px;"></div>
    <div class="alert-content">
        <h4 id="alert-title">Title</h4>
        <p id="alert-message">Message goes here...</p>
    </div>
</div>

<script>
    // Custom JS for Alerts (No Libraries)
    function showAlert(type, title, message) {
        const alertBox = document.getElementById('custom-alert');
        const alertTitle = document.getElementById('alert-title');
        const alertMsg = document.getElementById('alert-message');
        const alertIcon = document.getElementById('alert-icon');
        
        // Reset classes
        alertBox.className = 'custom-alert ' + type;
        
        // Set Content
        alertTitle.textContent = title;
        alertMsg.textContent = message;
        
        // Set Icon
        if(type === 'success') alertIcon.innerHTML = '✅';
        else if(type === 'error') alertIcon.innerHTML = '❌';
        else alertIcon.innerHTML = 'ℹ️';

        // Show
        setTimeout(() => { alertBox.classList.add('show'); }, 100);

        // Hide after 3 seconds
        setTimeout(() => {
            alertBox.classList.remove('show');
        }, 3000);
    }

    // Trigger Alert from PHP Session
    <?php if (isset($_SESSION['alert'])): ?>
        showAlert('<?php echo $_SESSION['alert']['type']; ?>', 
                  '<?php echo $_SESSION['alert']['title']; ?>', 
                  '<?php echo $_SESSION['alert']['message']; ?>');
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>
</script>
</body>
</html>