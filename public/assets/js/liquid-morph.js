/**
 * KakYo Lesson - Liquid Glass & Dynamic Island Morphing Controller
 * Handles mobile drawer behavior, Dynamic Island live clock/status morphing,
 * and Scroll-to-Bottom verification gates for Tata Tertib.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Mobile Sidebar Default Closed on Small Viewports (< 768px)
        var sidebar = document.getElementById('accordionSidebar');
        var backdrop = document.getElementById('sidebarMobileBackdrop');

        function syncMobileSidebarInit() {
            if (!sidebar) return;
            if (window.innerWidth < 768) {
                document.body.classList.add('sidebar-toggled');
                sidebar.classList.add('toggled');
                if (backdrop) backdrop.classList.add('is-mobile-ready');
            }
        }
        syncMobileSidebarInit();

        if (backdrop && sidebar) {
            backdrop.addEventListener('click', function () {
                document.body.classList.add('sidebar-toggled');
                sidebar.classList.add('toggled');
            });
        }

        // 2. Dynamic Island Live Clock & Tactile Expansion
        var island = document.getElementById('dynamicIslandPill');
        var islandClock = document.getElementById('dynamicIslandClock');

        function updateIslandClock() {
            if (!islandClock) return;
            var now = new Date();
            var hh = String(now.getHours()).padStart(2, '0');
            var mm = String(now.getMinutes()).padStart(2, '0');
            islandClock.textContent = hh + ':' + mm + ' WIB';
        }
        updateIslandClock();
        setInterval(updateIslandClock, 30000);

        if (island) {
            island.addEventListener('click', function () {
                island.classList.toggle('is-expanded');
            });
        }

        // 3. Scroll-to-Bottom Gate for Mandatory Tata Tertib Reader
        document.querySelectorAll('[data-tatib-gate]').forEach(function (scrollBox) {
            var targetCheckboxId = scrollBox.getAttribute('data-tatib-checkbox');
            var targetHiddenId   = scrollBox.getAttribute('data-tatib-hidden');
            var targetHintId     = scrollBox.getAttribute('data-tatib-hint');
            var targetSubmitId   = scrollBox.getAttribute('data-tatib-submit');

            var checkbox = targetCheckboxId ? document.getElementById(targetCheckboxId) : null;
            var hidden   = targetHiddenId ? document.getElementById(targetHiddenId) : null;
            var hint     = targetHintId ? document.getElementById(targetHintId) : null;
            var submitBtn = targetSubmitId ? document.getElementById(targetSubmitId) : null;

            function unlockGate() {
                if (hidden) hidden.value = '1';
                if (checkbox && checkbox.disabled) {
                    checkbox.disabled = false;
                }
                if (hint) {
                    hint.textContent = 'Dokumen telah dibaca hingga akhir. Silakan centang kotak persetujuan.';
                    hint.style.color = '#34D399';
                }
            }

            function checkScrollPosition() {
                var remaining = scrollBox.scrollHeight - scrollBox.scrollTop - scrollBox.clientHeight;
                if (remaining <= 24) {
                    unlockGate();
                }
            }

            scrollBox.addEventListener('scroll', checkScrollPosition, { passive: true });
            setTimeout(checkScrollPosition, 250);

            if (checkbox && submitBtn) {
                checkbox.addEventListener('change', function () {
                    submitBtn.disabled = !checkbox.checked;
                });
            }
        });

        // 4. Ensure third-party modal placeholders & DataTables controls meet WCAG 4.1.2 / 1.1.1
        function patchThirdPartyA11y() {
            document.querySelectorAll('img.swal2-image:not([alt])').forEach(function (img) {
                img.setAttribute('alt', '');
            });
            document.querySelectorAll('.dataTables_length select:not([aria-label])').forEach(function (sel) {
                sel.setAttribute('aria-label', 'Jumlah baris data per halaman');
            });
            document.querySelectorAll('.page-item.disabled .page-link:not([aria-disabled])').forEach(function (lnk) {
                lnk.setAttribute('aria-disabled', 'true');
            });
        }
        patchThirdPartyA11y();
        window.addEventListener('load', patchThirdPartyA11y);
        if (window.MutationObserver && document.body) {
            new MutationObserver(patchThirdPartyA11y).observe(document.body, { childList: true, subtree: true });
        }
    });
})();
