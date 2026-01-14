<?php
session_start();
$conn = new mysqli("localhost", "root", "", "portfolio");
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

// --- 1. CONFIGURATION ---
$ADMIN_USER = "admin";
$ADMIN_PASS = "123456";

// --- 2. AUTHENTICATION LOGIC ---
if (isset($_POST['login'])) {
    if ($_POST['username'] === $ADMIN_USER && $_POST['password'] === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

if (!isset($_SESSION['admin_logged_in'])):
?>
    <!DOCTYPE html>
    <html lang="en" data-bs-theme="dark">

    <head>
        <meta charset="UTF-8">
        <title>Admin Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: #0a0a0a;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', sans-serif;
            }

            .login-card {
                background: #1a1a1a;
                border: 1px solid #333;
                width: 360px;
                border-radius: 16px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            }
        </style>
    </head>

    <body>
        <div class="card login-card p-4">
            <h2 class="text-center text-primary fw-bold mb-4">SMEY ADMIN</h2>
            <?php if (isset($error)) echo "<div class='alert alert-danger py-2 small'>$error</div>"; ?>
            <form method="POST">
                <div class="mb-3"><label class="small text-secondary mb-1">Username</label><input name="username" class="form-control bg-dark border-secondary text-white" required></div>
                <div class="mb-4"><label class="small text-secondary mb-1">Password</label><input type="password" name="password" class="form-control bg-dark border-secondary text-white" required></div>
                <button name="login" class="btn btn-primary w-100 fw-bold py-2">SIGN IN</button>
            </form>
        </div>
    </body>

    </html>
<?php exit;
endif;

// --- 3. DATABASE ACTIONS ---

// Handle Delete Message
if (isset($_GET['delete_msg'])) {
    $id = intval($_GET['delete_msg']);
    $conn->query("DELETE FROM messages WHERE id=$id");
    header("Location: admin.php#messenger");
    exit();
}

// Handle Add/Update Project
if (isset($_POST['save_project'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);
    $cat   = $_POST['category'];
    $link  = mysqli_real_escape_string($conn, $_POST['link']);
    $id    = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
    $img_name = !empty($_POST['existing_img']) ? $_POST['existing_img'] : 'default.png';

    if (!empty($_FILES['project_img']['name'])) {
        $file_ext = pathinfo($_FILES['project_img']['name'], PATHINFO_EXTENSION);
        $file_name = "pro_" . time() . "." . $file_ext;
        if (move_uploaded_file($_FILES['project_img']['tmp_name'], "uploads/" . $file_name)) {
            if ($img_name != 'default.png' && file_exists("uploads/" . $img_name)) unlink("uploads/" . $img_name);
            $img_name = $file_name;
        }
    }

    if ($id) {
        $stmt = $conn->prepare("UPDATE projects SET title=?, description=?, category=?, link=?, image=? WHERE id=?");
        $stmt->bind_param("sssssi", $title, $desc, $cat, $link, $img_name, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO projects (title, description, category, link, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $desc, $cat, $link, $img_name);
    }
    $stmt->execute();
    header("Location: admin.php?success=1");
    exit();
}

// Handle Delete Project
if (isset($_GET['delete_project'])) {
    $id = intval($_GET['delete_project']);
    $res = $conn->query("SELECT image FROM projects WHERE id=$id");
    if ($p = $res->fetch_assoc()) {
        if ($p['image'] != 'default.png' && file_exists("uploads/" . $p['image'])) unlink("uploads/" . $p['image']);
    }
    $conn->query("DELETE FROM projects WHERE id=$id");
    header("Location: admin.php");
    exit();
}

// --- 4. DATA FETCHING ---
$edit_p = null;
if (isset($_GET['edit_project'])) {
    $res = $conn->query("SELECT * FROM projects WHERE id=" . intval($_GET['edit_project']));
    $edit_p = $res->fetch_assoc();
}
$projects = $conn->query("SELECT * FROM projects ORDER BY id DESC");
$messages = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | SMEY Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --transition-speed: 0.3s;
        }

        body {
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        .navbar {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .project-img-preview {
            width: 70px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        .theme-btn {
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="bg-body-tertiary">

    <nav class="navbar navbar-expand-lg sticky-top bg-body shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="admin.php">SMEY DASHBOARD</a>
            <div class="d-flex align-items-center gap-2">
                <div class="theme-btn me-2" id="themeToggle"><i class="fas fa-moon"></i></div>
                <a href="index.php" class="btn btn-outline-secondary btn-sm" target="_blank">View Site</a>
                <a href="?logout=1" class="btn btn-danger btn-sm px-3 fw-bold">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        <div class="card shadow-sm border-0" id="projects">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><?= $edit_p ? "<i class='fas fa-edit me-2'></i>Edit Project" : "<i class='fas fa-plus me-2'></i>New Project" ?></h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="project_id" value="<?= $edit_p['id'] ?? '' ?>">
                    <input type="hidden" name="existing_img" value="<?= $edit_p['image'] ?? '' ?>">

                    <div class="col-md-4"><label class="small fw-bold mb-1">Project Name</label><input name="title" class="form-control" value="<?= $edit_p['title'] ?? '' ?>" required></div>
                    <div class="col-md-4"><label class="small fw-bold mb-1">Category</label>
                        <select name="category" class="form-select">
                            <option value="web" <?= ($edit_p['category'] ?? '') == 'web' ? 'selected' : '' ?>>Web App</option>
                            <option value="vlog" <?= ($edit_p['category'] ?? '') == 'vlog' ? 'selected' : '' ?>>Content/Vlog</option>
                            <option value="design" <?= ($edit_p['category'] ?? '') == 'design' ? 'selected' : '' ?>>UI/UX Design</option>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="small fw-bold mb-1">Cover Image</label><input type="file" name="project_img" class="form-control"></div>
                    <div class="col-12"><label class="small fw-bold mb-1">Short Description</label><textarea name="description" class="form-control" rows="2" required><?= $edit_p['description'] ?? '' ?></textarea></div>
                    <div class="col-md-9"><label class="small fw-bold mb-1">Direct URL</label><input name="link" class="form-control" placeholder="https://..." value="<?= $edit_p['link'] ?? '' ?>"></div>
                    <div class="col-md-3 d-flex align-items-end"><button name="save_project" class="btn btn-primary w-100 fw-bold py-2">SAVE DATA</button></div>
                </form>

                <hr class="my-4 opacity-25">

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $projects->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="uploads/<?= $p['image'] ?>" class="project-img-preview border shadow-sm"></td>
                                    <td class="fw-bold"><?= htmlspecialchars($p['title']) ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= strtoupper($p['category']) ?></span></td>
                                    <td class="text-end pe-3">
                                        <a href="?edit_project=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle"><i class="fas fa-edit"></i></a>
                                        <a href="?delete_project=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle ms-1" onclick="return confirm('Delete project?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" id="messenger">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-inbox me-2"></i>Messenger Inbox</h5>
                <span class="badge bg-danger rounded-pill"><?= $messages->num_rows ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Client</th>
                            <th>Subject & Message</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($messages->num_rows > 0): while ($m = $messages->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold"><?= htmlspecialchars($m['name']) ?></div>
                                        <small class="text-primary"><?= htmlspecialchars($m['email']) ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-primary"><?= htmlspecialchars($m['subject']) ?></div>
                                        <div class="small text-muted" style="max-width: 400px;"><?= nl2br(htmlspecialchars($m['message'])) ?></div>
                                    </td>
                                    <td class="small"><?= date('M d, H:i', strtotime($m['created_at'])) ?></td>
                                    <td class="text-end pe-3">
                                        <a href="mailto:<?= $m['email'] ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="btn btn-sm btn-primary rounded-pill px-3">Reply</a>
                                        <a href="?delete_msg=<?= $m['id'] ?>" class="btn btn-sm btn-link text-danger ms-2" onclick="return confirm('Delete message?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                        <?php endwhile;
                        else: echo "<tr><td colspan='4' class='text-center py-5 text-muted'>Your inbox is empty.</td></tr>";
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle Functionality
        const themeBtn = document.getElementById('themeToggle');
        const html = document.documentElement;
        const icon = themeBtn.querySelector('i');

        // Load saved preference
        const savedTheme = localStorage.getItem('smeyTheme') || 'dark';
        html.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);

        themeBtn.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('smeyTheme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
                themeBtn.classList.add('text-warning');
            } else {
                icon.className = 'fas fa-moon';
                themeBtn.classList.remove('text-warning');
            }
        }
    </script>

</body>

</html>