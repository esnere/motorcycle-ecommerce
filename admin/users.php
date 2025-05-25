<?php
$page_title = 'Admin - Users';
require_once '../includes/header.php';
require_once '../classes/User.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../index.php');
}

$user_obj = new User($db);

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        switch ($action) {
            case 'toggle_admin':
                $user_id = (int)$_POST['user_id'];
                $is_admin = (int)$_POST['is_admin'];
                
                if ($user_id == $_SESSION['user_id']) {
                    $error = 'You cannot change your own admin status.';
                } else {
                    $old_data = $user_obj->getUserById($user_id);
                    
                    $query = "UPDATE users SET is_admin = :is_admin WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute([':is_admin' => $is_admin, ':id' => $user_id]);
                    
                    if ($result) {
                        logAdminAction($_SESSION['user_id'], 'UPDATE', 'users', $user_id, $old_data, ['is_admin' => $is_admin]);
                        $message = 'User admin status updated successfully!';
                    } else {
                        $error = 'Failed to update user admin status.';
                    }
                }
                break;
                
            case 'toggle_status':
                $user_id = (int)$_POST['user_id'];
                $is_active = (int)$_POST['is_active'];
                
                if ($user_id == $_SESSION['user_id']) {
                    $error = 'You cannot deactivate your own account.';
                } else {
                    $old_data = $user_obj->getUserById($user_id);
                    
                    $query = "UPDATE users SET is_active = :is_active WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute([':is_active' => $is_active, ':id' => $user_id]);
                    
                    if ($result) {
                        logAdminAction($_SESSION['user_id'], 'UPDATE', 'users', $user_id, $old_data, ['is_active' => $is_active]);
                        $message = 'User status updated successfully!';
                    } else {
                        $error = 'Failed to update user status.';
                    }
                }
                break;
        }
    }
}

// Get filters
$search = $_GET['search'] ?? '';
$admin_filter = $_GET['admin_filter'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($admin_filter !== '') {
    $where_conditions[] = "is_admin = :admin_filter";
    $params[':admin_filter'] = (int)$admin_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get users
$query = "SELECT id, username, email, first_name, last_name, phone, city, province, 
                 is_admin, is_active, created_at, updated_at 
          FROM users $where_clause 
          ORDER BY created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_users = $count_stmt->fetch()['total'];
$total_pages = ceil($total_users / $limit);

// Get user statistics
$stats_query = "SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN is_admin = 1 THEN 1 END) as admin_users,
    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_users,
    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_30_days
    FROM users";
$stats = $db->query($stats_query)->fetch();
?>

<div class="container-fluid py-4">
<div class="pt-5"> 
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="products.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-box me-2"></i>Products
                    </a>
                    <a href="categories.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tags me-2"></i>Categories
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart me-2"></i>Orders
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-users me-2"></i>Users
                    </a>
                    <a href="reports.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                    <hr>
                    <a href="../index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-home me-2"></i>Back to Site
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
        <div class="pt-5"> 
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>User Management</h2>
                <div class="text-muted">
                    <i class="fas fa-users me-2"></i><?php echo $total_users; ?> total users
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h4><?php echo $stats['total_users']; ?></h4>
                            <small>Total Users</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-user-check fa-2x mb-2"></i>
                            <h4><?php echo $stats['active_users']; ?></h4>
                            <small>Active Users</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fa-2x mb-2"></i>
                            <h4><?php echo $stats['admin_users']; ?></h4>
                            <small>Administrators</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <h4><?php echo $stats['new_users_30_days']; ?></h4>
                            <small>New (30 days)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search Users</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name, username, or email">
                        </div>
                        <div class="col-md-3">
                            <label for="admin_filter" class="form-label">User Type</label>
                            <select class="form-control" id="admin_filter" name="admin_filter">
                                <option value="">All Users</option>
                                <option value="1" <?php echo $admin_filter === '1' ? 'selected' : ''; ?>>Administrators</option>
                                <option value="0" <?php echo $admin_filter === '0' ? 'selected' : ''; ?>>Regular Users</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="users.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No users found</h5>
                            <p class="text-muted">No users match your current filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-info text-white ms-1">You</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($user['email']); ?></div>
                                            <?php if ($user['phone']): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['city'] || $user['province']): ?>
                                                <div><?php echo htmlspecialchars($user['city']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($user['province']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Not provided</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['is_admin']): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-user-shield me-1"></i>Administrator
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-user me-1"></i>Customer
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                                            <small class="text-muted"><?php echo date('g:i A', strtotime($user['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary dropdown-toggle" 
                                                            data-bs-toggle="dropdown" title="Actions">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <button class="dropdown-item" type="button" 
                                                                    onclick="toggleAdmin(<?php echo $user['id']; ?>, <?php echo $user['is_admin'] ? 0 : 1; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                                                                <i class="fas fa-user-shield me-2"></i>
                                                                <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" type="button" 
                                                                    onclick="toggleStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active'] ? 0 : 1; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                                                                <i class="fas fa-user-<?php echo $user['is_active'] ? 'slash' : 'check'; ?> me-2"></i>
                                                                <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="dropdown-item" type="button" onclick="viewUserDetails(<?php echo $user['id']; ?>)">
                                                                <i class="fas fa-eye me-2"></i>View Details
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Current User</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Users pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&admin_filter=<?php echo $admin_filter; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&admin_filter=<?php echo $admin_filter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&admin_filter=<?php echo $admin_filter; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Confirmation Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="actionModalMessage"></p>
                <p><strong id="actionUserName"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;" id="actionForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" id="actionUserId">
                    <input type="hidden" name="is_admin" id="actionIsAdmin">
                    <input type="hidden" name="is_active" id="actionIsActive">
                    <button type="submit" class="btn btn-primary" id="actionSubmitBtn">
                        Confirm
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAdmin(userId, isAdmin, userName) {
    document.getElementById('actionModalTitle').textContent = isAdmin ? 'Make Administrator' : 'Remove Administrator';
    document.getElementById('actionModalMessage').textContent = isAdmin ? 
        'Are you sure you want to grant administrator privileges to this user?' : 
        'Are you sure you want to remove administrator privileges from this user?';
    document.getElementById('actionUserName').textContent = userName;
    document.getElementById('actionUserId').value = userId;
    document.getElementById('actionIsAdmin').value = isAdmin;
    document.getElementById('actionForm').action = '?action=toggle_admin';
    document.getElementById('actionSubmitBtn').textContent = 'Confirm';
    
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function toggleStatus(userId, isActive, userName) {
    document.getElementById('actionModalTitle').textContent = isActive ? 'Activate User' : 'Deactivate User';
    document.getElementById('actionModalMessage').textContent = isActive ? 
        'Are you sure you want to activate this user account?' : 
        'Are you sure you want to deactivate this user account?';
    document.getElementById('actionUserName').textContent = userName;
    document.getElementById('actionUserId').value = userId;
    document.getElementById('actionIsActive').value = isActive;
    document.getElementById('actionForm').action = '?action=toggle_status';
    document.getElementById('actionSubmitBtn').textContent = 'Confirm';
    
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function viewUserDetails(userId) {
    // This could open a detailed user view modal or redirect to a user details page
    window.open('../profile.php?user=' + userId, '_blank');
}
</script>

<?php require_once '../includes/footer.php'; ?>
