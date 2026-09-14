/**
 * autocomplete.js — Job search autocomplete
 *
 * HOW TO WIRE THIS UP:
 * 1. Include this file on any page with your job search input:
 *      <script src="/assets/js/autocomplete.js"></script>
 * 2. Make sure your search input and a suggestion container exist in the HTML:
 *      <div class="search-wrapper" style="position:relative;">
 *        <input type="text" id="jobSearchInput" name="q" autocomplete="off" placeholder="Search jobs...">
 *        <div id="suggestBox" class="suggest-box"></div>
 *      </div>
 * 3. Update SUGGEST_ENDPOINT below to match your actual route (e.g. "/search/suggest").
 */

(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('jobSearchInput');
        const box = document.getElementById('suggestBox');
        if (!input || !box) return;

        const SUGGEST_ENDPOINT = input.dataset.suggestEndpoint || '/jobs/suggest';

        let debounceTimer;
        let activeIndex = -1;

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const q = input.value.trim();
            activeIndex = -1;

            if (q.length < 2) {
                box.innerHTML = '';
                box.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => fetchSuggestions(q), 250);
        });

        // Keyboard navigation (up/down/enter)
        input.addEventListener('keydown', function (e) {
            const items = box.querySelectorAll('.suggest-item');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                highlight(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                highlight(items);
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                selectSuggestion(items[activeIndex].dataset.value);
            } else if (e.key === 'Escape') {
                box.style.display = 'none';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!box.contains(e.target) && e.target !== input) {
                box.style.display = 'none';
            }
        });

        function fetchSuggestions(q) {
            fetch(SUGGEST_ENDPOINT + '?q=' + encodeURIComponent(q))
                .then(res => res.json())
                .then(data => renderSuggestions(data))
                .catch(() => { box.innerHTML = ''; box.style.display = 'none'; });
        }

        function renderSuggestions(data) {
            if (!data || !data.length) {
                box.innerHTML = '';
                box.style.display = 'none';
                return;
            }

            box.innerHTML = data.map(item => {
                const icon = item.type === 'location' ? '📍' : '🔍';
                return `<div class="suggest-item" data-value="${escapeHtml(item.suggestion)}">
                            <span class="suggest-icon">${icon}</span>${escapeHtml(item.suggestion)}
                        </div>`;
            }).join('');

            box.style.display = 'block';

            box.querySelectorAll('.suggest-item').forEach(el => {
                el.addEventListener('click', () => selectSuggestion(el.dataset.value));
            });
        }

        function highlight(items) {
            items.forEach(el => el.classList.remove('active'));
            if (activeIndex >= 0) {
                items[activeIndex].classList.add('active');
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        function selectSuggestion(value) {
            input.value = value;
            box.innerHTML = '';
            box.style.display = 'none';
            input.closest('form') ? input.closest('form').submit() : null;
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    });
})();
