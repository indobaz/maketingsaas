<script>
    (function () {
        var form = document.getElementById('brandKitForm');
        if (!form) return;

        function normalizeHex(input) {
            if (!input) return '';
            var v = String(input).trim();
            if (v[0] !== '#') v = '#' + v;
            return v.toUpperCase();
        }

        function isValidHex(v) {
            return /^#([A-F0-9]{6})$/.test(v);
        }

        var primaryHex = document.getElementById('skPrimaryHex');
        var secondaryHex = document.getElementById('skSecondaryHex');
        var primaryPicker = document.getElementById('skPrimaryPicker');
        var secondaryPicker = document.getElementById('skSecondaryPicker');
        var primarySwatch = document.getElementById('skPrimarySwatch');
        var secondarySwatch = document.getElementById('skSecondarySwatch');
        var previewSidebar = document.getElementById('skPreviewSidebar');
        var previewActive = document.getElementById('skPreviewActive');
        var previewBadge = document.getElementById('skPreviewBadge');

        if (!primaryHex || !secondaryHex) return;

        function applyColors() {
            var p = normalizeHex(primaryHex.value);
            var s = normalizeHex(secondaryHex.value);

            if (isValidHex(p)) {
                primaryHex.value = p;
                primaryPicker.value = p;
                primarySwatch.style.background = p;
                previewActive.style.background = p;
                previewBadge.style.background = 'transparent';
                previewBadge.style.color = p;
                previewBadge.style.border = '1px solid rgba(39,43,65,0.10)';
            }

            if (isValidHex(s)) {
                secondaryHex.value = s;
                secondaryPicker.value = s;
                secondarySwatch.style.background = s;
                previewSidebar.style.background = s;
            }
        }

        function setBoth(p, s) {
            primaryHex.value = normalizeHex(p);
            secondaryHex.value = normalizeHex(s);
            applyColors();
        }

        primaryHex.addEventListener('input', applyColors);
        secondaryHex.addEventListener('input', applyColors);
        primaryPicker.addEventListener('input', function () {
            primaryHex.value = normalizeHex(primaryPicker.value);
            applyColors();
        });
        secondaryPicker.addEventListener('input', function () {
            secondaryHex.value = normalizeHex(secondaryPicker.value);
            applyColors();
        });

        document.querySelectorAll('.sk-preset').forEach(function (el) {
            function activate() {
                setBoth(el.getAttribute('data-primary'), el.getAttribute('data-secondary'));
            }
            el.addEventListener('click', activate);
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    activate();
                }
            });
        });

        setBoth(primaryHex.value, secondaryHex.value);
    })();
</script>
