</main>
<footer class="text-center p-4 bg-white dark:bg-gray-800 mt-8 shadow">
    &copy; <?php echo date('Y'); ?> Spotyfind. All rights reserved.
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('avatarBtn');
    const dropdown = document.getElementById('avatarDropdown');
    if (btn && dropdown) {
        let open = false;
        const originalParent = dropdown.parentElement;

        function positionDropdown() {
            const rect = btn.getBoundingClientRect();
            // Temporarily show to measure
            dropdown.style.visibility = 'hidden';
            dropdown.style.display = 'block';
            dropdown.style.position = 'fixed';
            const dw = dropdown.offsetWidth;
            const dh = dropdown.offsetHeight;
            const marginTop = 8; // 8px below button
            const left = Math.max(8, Math.min(rect.right - dw, window.innerWidth - dw - 8));
            const top = Math.min(rect.bottom + marginTop, window.innerHeight - dh - 8);
            dropdown.style.left = left + 'px';
            dropdown.style.top = top + 'px';
            dropdown.style.visibility = '';
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            open = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                if (dropdown.parentElement !== document.body) {
                    document.body.appendChild(dropdown);
                }
                positionDropdown();
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
                if (dropdown.parentElement !== originalParent) {
                    originalParent.appendChild(dropdown);
                }
            }
        });
        document.addEventListener('click', function() {
            if (!open) return;
            dropdown.style.display = 'none';
            open = false;
            btn.setAttribute('aria-expanded', 'false');
            if (dropdown.parentElement !== originalParent) {
                originalParent.appendChild(dropdown);
            }
        });
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        window.addEventListener('resize', function() {
            if (open) positionDropdown();
        });
    }
});
</script>
</body>
</html> 