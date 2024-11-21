<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Admin Dashboard</title>
    <link rel="stylesheet" href="#" />
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f9f9fc; 
            color: #333;
            display: flex;
        }
        /* Sidebar Styling */
        .sidebar {
            background-color: #6a5acd;
            width: 250px;
            height: 100vh;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
        }
        .sidebar h2 {
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: background 0.3s;
            cursor: pointer;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: #5d55b1;
        }
        /* Dropdown Menu Styling */
        .dropdown {
            display: none;
            flex-direction: column;
            margin-left: 20px;
        }
        .top {
            display: none;
            flex-direction: column;
           
        }
        .dropdown a {
            background: #5d55b1;
            padding-left: 30px;
            transition: background 0.3s;
        }
        .dropdown a:hover {
            background: #4b45a0;
        }
        /* Show Dropdown Menu on Click */
        .show-dropdown {
            display: flex !important;
        }
        
        /* Main Content Styling */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .dashboard-stats {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            color: #6a5acd;
            font-size: 2em;
            margin-bottom: 5px;
        }
        .stat-card p {
            color: #555;
        }
        /* Hot Topics, Comment Moderation, and Analytics */
        .section {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .section h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .topic, .comment {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .topic:last-child, .comment:last-child {
            border-bottom: none;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .badge.active {
            background: #6a5acd;
            color: #fff;
        }
        .badge.locked {
            background: #e74c3c;
            color: #fff;
        }
        /* Buttons for Comment Moderation */
        .comment .btn {
            padding: 8px 12px;
            margin-left: 5px;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn.approve {
            background: #28a745;
        }
        .btn.remove {
            background: #e74c3c;
        }
        .btn.approve:hover {
            background: #218838;
        }
        .btn.remove:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><i class="fas fa-comments"></i> Forum Admin</h2>
        <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="#"><i class="fas fa-book"></i> Etudiants</a>
        <a href="#"><i class="fas fa-flag"></i>Enseignants</a>
        <a href="#"><i class="fas fa-users"></i> Entreprises</a>
        <a href="#"><i class="fas fa-layer-group"></i> Personnel</a>
        <a href="#"><i class="fas fa-ban"></i> boîte de réception</a>
        <!-- Settings Dropdown Menu -->
        <a href="#" id="settings-toggle"><i class="fas fa-cog"></i> Parametre <i class="fas fa-chevron-down"></i></a>
        <div class="dropdown" id="settings-dropdown">
            <a href="#">Profile Settings</a>
            <a href="#">Account Security</a>
            <a href="#">Notification Preferences</a>
            <a href="#">Privacy Options</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>2,456</h3>
                <p>Active Topics</p>
            </div>
            <div class="stat-card">
                <h3>12,789</h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h3>23</h3>
                <p>Reported Posts</p>
            </div>
            <div class="stat-card">
                <h3>856</h3>
                <p>Today's Posts</p>
            </div>
        </div>

        <!-- Hot Topics Section -->
        <div class="section">
            <h3>Hot Topics</h3>
            <div class="topic">
                <span>Technology Discussion</span>
                <span class="badge active">Active</span>
            </div>
            <div class="topic">
                <span>Gaming Community</span>
                <span class="badge active">Active</span>
            </div>
            <div class="topic">
                <span>Movie Reviews</span>
                <span class="badge locked">Locked</span>
            </div>
        </div>

        <!-- Comment Moderation Section -->
        <div class="section">
            <h3>Comment Moderation</h3>
            <div class="comment">
                <span>User123: This comment has been reported</span>
                <div>
                    <button class="btn approve">Approve</button>
                    <button class="btn remove">Remove</button>
                </div>
            </div>
            <div class="comment">
                <span>User456: This comment has been flagged</span>
                <div>
                    <button class="btn approve">Approve</button>
                    <button class="btn remove">Remove</button>
                </div>
            </div>
        </div>
    </div>

    <div class="top">
        <H1>SALUT</H1>
    </div>

    <!-- JavaScript for Toggle Dropdown Menu -->
    <script>
        document.getElementById('settings-toggle').addEventListener('click', function (event) {
            event.preventDefault();
            const dropdown = document.getElementById('settings-dropdown');
            dropdown.classList.toggle('show-dropdown');
        });
    </script>
</body>
</html>