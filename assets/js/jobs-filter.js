/**
 * UIU Career Portal - jobs-filter.js
 * Instant client-side search and filtering for job listings across pages
 */
function filterJobs(searchInputId, deptFilterId, typeFilterId, containerSelector, itemSelector) {
  const searchInput = document.getElementById(searchInputId);
  const deptSelect = document.getElementById(deptFilterId);
  const typeSelect = document.getElementById(typeFilterId);
  const container = document.querySelector(containerSelector);

  if (!container) return;
  const items = container.querySelectorAll(itemSelector);

  const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
  const dept = deptSelect ? deptSelect.value : 'All';
  const type = typeSelect ? typeSelect.value : 'All';

  let matchCount = 0;
  items.forEach(item => {
    const text = item.textContent.toLowerCase();
    const itemDept = item.getAttribute('data-department') || '';
    const itemType = item.getAttribute('data-type') || '';

    const matchesQuery = !query || text.includes(query);
    const matchesDept = dept === 'All' || itemDept === dept || text.includes(dept.toLowerCase());
    const matchesType = type === 'All' || itemType === type || text.includes(type.toLowerCase());

    if (matchesQuery && matchesDept && matchesType) {
      item.style.display = '';
      matchCount++;
    } else {
      item.style.display = 'none';
    }
  });

  const countBadge = document.getElementById('jobs-match-count');
  if (countBadge) {
    countBadge.textContent = `${matchCount} open position${matchCount === 1 ? '' : 's'} available`;
  }
}

// Bind live search listeners
document.addEventListener('DOMContentLoaded', function() {
  const sInput = document.getElementById('job-search-input');
  const dFilter = document.getElementById('job-dept-filter');
  const tFilter = document.getElementById('job-type-filter');

  const triggerFilter = () => filterJobs('job-search-input', 'job-dept-filter', 'job-type-filter', '#jobs-container', '.job-card, .data-row');

  if (sInput) sInput.addEventListener('input', triggerFilter);
  if (dFilter) dFilter.addEventListener('change', triggerFilter);
  if (tFilter) tFilter.addEventListener('change', triggerFilter);
});
