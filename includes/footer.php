    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Global configuration
        window.BicarantaConfig = {
            materials: <?php echo json_encode(array_values($materials ?? [])); ?>,
            materialKeys: <?php echo json_encode(array_keys($materials ?? [])); ?>,
            user: <?php echo isUserLoggedIn() ? json_encode(getCurrentUser()) : 'null'; ?>,
            isLoggedIn: <?php echo json_encode(isUserLoggedIn()); ?>,
            userRole: <?php echo json_encode(getUserRole()); ?>
        };
    </script>
    
    <script src="assets/js/script.js"></script>
</body>
</html>