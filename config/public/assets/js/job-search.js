'use strict';
/* ── AJAX Live Job Search ──────────────────────────────────────────────── */
(function () {
  const form       = document.getElementById('jobSearchForm');
  const resultsBox = document.getElementById('jobResults');
  const countBox   = document.getElementById('resultsCount');
  const spinner    = document.getElementById('searchSpinner');
  if (!form || !resultsBox) return;

  let debounceTimer;

  // watch every input/select in the form
  form.querySelectorAll('input,select').forEach(el => {
    el.addEventListener('input',  () => scheduleSearch());
    el.addEventListener('change', () => scheduleSearch());
  });

  function scheduleSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(doSearch, 350);
  }

  async function doSearch() {
    spinner && (spinner.style.display = 'inline-block');

    const params = new URLSearchParams(new FormData(form));
    params.set('ajax', '1');

    try {
      const res  = await fetch('/job-portal/public/jobs?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();

      renderResults(data.jobs);
      if (countBox) countBox.textContent = data.total + ' job' + (data.total !== 1 ? 's' : '') + ' found';

      // update browser URL without reload
      const url = new URL(window.location);
      params.delete('ajax');
      url.search = params.toString();
      history.replaceState({}, '', url);
    } catch (e) {
      console.error('Search error', e);
    } finally {
      spinner && (spinner.style.display = 'none');
    }
  }

  function renderResults(jobs) {
    if (!jobs || jobs.length === 0) {
      resultsBox.innerHTML = `
        <div class="card border-0 shadow-sm text-center py-5">
          <div class="card-body">
            <i class="bi bi-search text-muted" style="font-size:3rem;"></i>
            <h5 class="fw-700 mt-3">No jobs found</h5>
            <p class="text-muted">Try different keywords or remove some filters.</p>
            <a href="/job-portal/public/jobs" class="btn btn-primary px-4">Clear Filters</a>
          </div>
        </div>`;
      return;
    }

    resultsBox.innerHTML = jobs.map(job => `
      <div class="card border-0 shadow-sm job-card mb-3">
        <div class="card-body p-4">
          <div class="row align-items-center g-3">
            <div class="col-12 col-md-7 d-flex gap-3">
              <div class="logo-placeholder flex-shrink-0"
                   style="width:52px;height:52px;font-size:20px;border-radius:12px;">
                ${job.company_logo
                  ? `<img src="/job-portal/public/file?path=${encodeURIComponent(job.company_logo)}"
                         style="width:52px;height:52px;border-radius:12px;object-fit:contain;
                                border:1px solid #e5e7eb;padding:4px;background:#fff;" alt="">`
                  : job.company_name.charAt(0).toUpperCase()}
              </div>
              <div class="min-w-0">
                <h5 class="fw-700 mb-0 fs-6">
                  <a href="/job-portal/public/jobs/${job.slug}" class="text-dark text-decoration-none">
                    ${escHtml(job.title)}
                  </a>
                </h5>
                <div class="text-muted small mb-1">${escHtml(job.company_name)}</div>
                <div class="d-flex flex-wrap gap-2">
                  <span class="badge bg-light text-dark fw-500" style="font-size:11.5px;">
                    <i class="bi bi-geo-alt me-1"></i>
                    ${job.is_remote ? '<span class="text-success fw-600">Remote</span>' : escHtml(job.location_city || 'N/A')}
                  </span>
                  <span class="badge bg-light text-dark fw-500" style="font-size:11.5px;">
                    ${escHtml(job.job_type_label)}
                  </span>
                  <span class="badge bg-light text-dark fw-500" style="font-size:11.5px;">
                    ${escHtml(job.experience_label)}
                  </span>
                  ${job.salary_display ? `<span class="badge fw-500"
                    style="background:#F0FDF4;color:#057A55;font-size:11.5px;">${escHtml(job.salary_display)}</span>` : ''}
                </div>
              </div>
            </div>
            <div class="col-12 col-md-5 d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
              <span class="text-muted small">${escHtml(job.time_ago)}</span>
              <a href="/job-portal/public/jobs/${job.slug}"
                 class="btn btn-primary btn-sm px-3">View Job</a>
            </div>
          </div>
        </div>
      </div>`).join('');
  }

  function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  }
})();
