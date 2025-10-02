</main>
<footer class="text-center p-4 bg-white dark:bg-gray-800 mt-8 shadow">
    &copy; <?php echo date('Y'); ?> Campus Navigator. All rights reserved.
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('avatarBtn');
    const dropdown = document.getElementById('avatarDropdown');
    if (btn && dropdown) {
        let open = false;
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            open = !open;
            dropdown.style.display = open ? 'block' : 'none';
        });
        document.addEventListener('click', function() {
            dropdown.style.display = 'none';
            open = false;
        });
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
</script>
</body>
</html> 