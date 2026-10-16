<?php
/**
 * UIU Career Portal - Global Helper Functions & Database Query Wrappers
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Determine the relative application root path dynamically
 * Supports root host (http://localhost:3000/) and subdirectories (/uiu-career-portal/)
 */
function get_app_root() {
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Strip trailing subfolders if accessed from inside admin, company, student, api, etc.
    $app_root = preg_replace('#/(student|company|admin|guest|api|includes|config|src)$#i', '', $script_dir);
    if ($app_root === '/' || $app_root === '') {
        return '';
    }
    return rtrim($app_root, '/');
}

/**
 * Sanitize string or array input against XSS
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

/**
 * Return formatted JSON response and terminate
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Render any flash notification message set in session
 */
function display_flash_messages() {
    $html = '';
    if (!empty($_SESSION['flash_success'])) {
        $html .= '<div class="alert alert-success">' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }
    if (!empty($_SESSION['flash_error'])) {
        $html .= '<div class="alert alert-error">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
    return $html;
}

/**
 * Fetch approved jobs and internships with multi-criteria search, filter, and sorting
 */
function get_approved_jobs($search = '', $dept = 'All', $type = 'All', $sort = 'newest', $limit = 0) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return [];
    }

    $sql = "SELECT j.*, c.name as company_name, c.logo as company_logo, c.sector as company_sector, c.location as company_location 
            FROM jobs j 
            JOIN companies c ON j.company_id = c.id 
            WHERE j.status = 'Approved'";
    
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (j.title LIKE ? OR c.name LIKE ? OR j.skills LIKE ? OR j.location LIKE ?)";
        $wildcard = "%" . $search . "%";
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
    }

    if ($dept !== 'All' && !empty($dept)) {
        $sql .= " AND j.department = ?";
        $params[] = $dept;
    }

    if ($type !== 'All' && !empty($type)) {
        $sql .= " AND j.type = ?";
        $params[] = $type;
    }

    switch ($sort) {
        case 'oldest':
            $sql .= " ORDER BY j.id ASC";
            break;
        case 'salary_high':
            $sql .= " ORDER BY j.stipend_numeric DESC";
            break;
        case 'salary_low':
            $sql .= " ORDER BY j.stipend_numeric ASC";
            break;
        case 'deadline':
            $sql .= " ORDER BY j.deadline ASC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY j.featured DESC, j.id DESC";
            break;
    }

    if ($limit > 0) {
        $sql .= " LIMIT " . (int)$limit;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Query error in get_approved_jobs: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch verified companies with search and sector filtering
 */
function get_verified_companies($search = '', $sector = 'All', $limit = 0) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return [];
    }

    $sql = "SELECT c.*, COUNT(j.id) as open_jobs_count 
            FROM companies c 
            LEFT JOIN jobs j ON c.id = j.company_id AND j.status = 'Approved' 
            WHERE c.status = 'Verified'";
    
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (c.name LIKE ? OR c.sector LIKE ? OR c.location LIKE ? OR c.industry LIKE ?)";
        $wildcard = "%" . $search . "%";
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
    }

    if ($sector !== 'All' && !empty($sector)) {
        $sql .= " AND c.sector = ?";
        $params[] = $sector;
    }

    $sql .= " GROUP BY c.id ORDER BY c.health_score DESC, c.name ASC";

    if ($limit > 0) {
        $sql .= " LIMIT " . (int)$limit;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Query error in get_verified_companies: " . $e->getMessage());
        return [];
    }
}
