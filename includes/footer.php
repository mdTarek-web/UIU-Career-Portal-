<?php
/**
 * UIU Career Portal - Shared Page Footer
 */
require_once __DIR__ . '/functions.php';
$app_root = get_app_root();
?>
  <!-- Shared Footer -->
  <footer id="contact" style="background-color: var(--slate-950); color: #ffffff; padding: 4rem 1.25rem 2rem; margin-top: auto; border-top: 1px solid #1e293b;">
    <div style="max-width: 1280px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 3rem; margin-bottom: 3rem;">
      <!-- Col 1 -->
      <div>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
          <div class="brand-badge" style="width: 2.25rem; height: 2.25rem;">UIU</div>
          <span style="font-weight: 900; font-size: 1.125rem;">UIU CAREER PORTAL</span>
        </div>
        <p style="color: var(--slate-400); font-size: 0.875rem; line-height: 1.6;">
          Centre for Development of IT Professionals (CDIP) & Directorate of Career Services and Alumni Affairs, United International University.
        </p>
      </div>
      <!-- Col 2 -->
      <div>
        <h4 style="font-size: 0.875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary); margin-bottom: 1rem;">Quick Links</h4>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem; color: var(--slate-400);">
          <li><a href="<?= $app_root ?>/index.php#home">Portal Home</a></li>
          <li><a href="<?= $app_root ?>/index.php#jobs">Featured Jobs</a></li>
          <li><a href="<?= $app_root ?>/index.php#companies">Partner Companies</a></li>
          <li><a href="<?= $app_root ?>/student/dashboard.php">Student Dashboard</a></li>
          <li><a href="<?= $app_root ?>/company/dashboard.php">Employer Hub</a></li>
        </ul>
      </div>
      <!-- Col 3 -->
      <div>
        <h4 style="font-size: 0.875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary); margin-bottom: 1rem;">Campus Location</h4>
        <p style="color: var(--slate-400); font-size: 0.875rem; line-height: 1.6;">
          United City, Madani Avenue, Badda, Dhaka 1212, Bangladesh.<br>
          Email: career.services@uiu.ac.bd<br>
          Phone: +880 9604-848848
        </p>
      </div>
    </div>
    <div style="max-width: 1280px; margin: 0 auto; padding-top: 2rem; border-top: 1px solid #1e293b; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: 0.8125rem; color: var(--slate-500);">
      <p>&copy; <?= date('Y') ?> United International University (UIU). All rights reserved.</p>
      <p>Powered by UIU Career Services & CDIP</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="<?= $app_root ?>/assets/js/main.js"></script>
  <script src="<?= $app_root ?>/assets/js/jobs-filter.js"></script>
  <script src="<?= $app_root ?>/src/js/script.js"></script>
</body>
</html>
