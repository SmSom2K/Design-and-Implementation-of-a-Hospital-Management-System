
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "hospital_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$doctor_result = mysqli_query($conn, "SELECT COUNT(*) AS total_doctors FROM users WHERE role = 'doctor'");
$doctor_count = mysqli_fetch_assoc($doctor_result)['total_doctors'];

$patient_result = mysqli_query($conn, "SELECT COUNT(*) AS total_patients FROM patients");
$patient_count = mysqli_fetch_assoc($patient_result)['total_patients'];

$department_result = mysqli_query($conn, "SELECT COUNT(DISTINCT department) AS total_departments FROM users WHERE role = 'doctor' AND department IS NOT NULL AND department != ''");
$department_count = mysqli_fetch_assoc($department_result)['total_departments'];

$appointment_result = mysqli_query($conn, "SELECT COUNT(*) AS total_appointments FROM appointments");
$appointment_count = mysqli_fetch_assoc($appointment_result)['total_appointments'];

$recent_activity = mysqli_query($conn, "
    SELECT * FROM activity_log 
    ORDER BY timestamp DESC 
    LIMIT 5
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard</title>


    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="assets/css2/style.css" />
    <link rel="stylesheet" href="assets/css2/dashboard.css" />
    <link rel="stylesheet" href="assets/css2/admin.css" />
   
  
    


</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i data-lucide="heart-pulse" class="logo-icon"></i>
                <h1>MediCare</h1>
            </div>
        </div>
        <div class="sidebar-content">
            <div class="user-info">
                <div class="user-avatar">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Admin" />
                </div>
                <div class="user-details">
                    <h3 id="adminName"><?= $_SESSION['user'] ?></h3>
                    <p>Administrator</p>
                </div>
            </div>



            <nav class="sidebar-nav">
    <ul>
        <li><a href="#dashboard-section"><i data-lucide="layout-dashboard" class="nav-icon"></i>Dashboard</a></li>
        <li><a href="#doctors-section"><i data-lucide="stethoscope" class="nav-icon"></i>Doctors</a></li>
        <li><a href="#patients-section"><i data-lucide="users" class="nav-icon"></i>Patients</a></li>
        <li><a href="#assignments-section"><i data-lucide="clipboard-list" class="nav-icon"></i>Assignments</a></li>
        <li><a href="#profile-section"><i data-lucide="user" class="nav-icon"></i>Profile</a></li>
        



    </ul>
</nav>

<li>
    <a href="logout.php" id="logoutBtn">
        <i data-lucide="log-out" class="nav-icon"></i>Logout
    </a>
</li>

        </div>

        

    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Dashboard</h2>
        </header>

        <!-- Dashboard Section -->
        <section id="dashboard-section" class="content-section active">
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon"><i data-lucide="stethoscope"></i></div>
                    <div class="stat-details">
                        <h3>Total Doctors</h3>
                        <p class="stat-value"><?= $doctor_count ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i data-lucide="users"></i></div>
                    <div class="stat-details">
                        <h3>Total Patients</h3>
                        <p class="stat-value"><?= $patient_count ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i data-lucide="calendar"></i></div>
                    <div class="stat-details">
                        <h3>Appointments</h3>
                        <p class="stat-value"><?= $appointment_count ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i data-lucide="building"></i></div>
                    <div class="stat-details">
                        <h3>Departments</h3>
                        <p class="stat-value"><?= $department_count ?></p>
                    </div>
                </div>
            </div>
            <div class="recent-activity">
    <h3>Recent Activity</h3>
    <?php while($row = mysqli_fetch_assoc($recent_activity)): ?>
        <div class="activity-item">
            <div class="activity-icon"><i data-lucide="activity"></i></div>
            <div class="activity-details">
                <p><?= htmlspecialchars($row['description']) ?></p>
                <small><?= date("F j, Y, g:i A", strtotime($row['timestamp'])) ?></small>
            </div>
        </div>
    <?php endwhile; ?>
</div>

        </section>

        <!-- Doctors Section -->
        <section id="doctors-section" class="content-section">
  <div class="section-header">
    <h2>Doctors Management</h2>
    <button id="addDoctorBtn" class="btn btn-primary">+ Add Doctor</button>
  </div>

  <div class="table-container">
    <table class="data-table">
    <thead>
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Username</th>
    <th>Email</th>
    <th>Department</th>
    <th>Phone</th>
    <th>Status</th>
    <th>Actions</th>
  </tr>
</thead>

      <tbody id="doctorsList"></tbody> <!-- JS inserts rows here -->
    </table>
  </div>
</section>





        <!-- Patients Section -->
        <section id="patients-section" class="content-section">
  <div class="section-header">
    <h2>Patients Management</h2>
    <button id="addPatientBtn" class="btn btn-primary">+ Add Patient</button>
  </div>

  <div class="table-container">
    <table class="data-table">
      <thead>
        <tr>
        <th>ID</th>
<th>Name</th>
<th>Username</th>
<th>Email</th>
<th>Gender</th>
<th>Phone</th>
<th>Blood Type</th>
<th>Actions</th>

        </tr>
      </thead>
      <tbody id="patientsList"></tbody>
    </table>
  </div>
</section>


        <section id="assignments-section" class="content-section">
    <div class="section-header">
        <h2>Assign Patient to Doctor</h2>
    </div>

    <form id="assignmentForm" class="form-inline">
        <select id="assignDoctor" required></select>
        <select id="assignPatient" required></select>
        <button id="assignBtn" type="submit" class="btn btn-primary">Assign</button>

    </form>

    <h3>Current Assignments</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Patient</th>
                <th>Date Assigned</th>
            </tr>
        </thead>
        <tbody id="assignmentsList"></tbody>
    </table>
</section>


        <!-- Profile Section -->
        <section id="profile-section" class="content-section">
  <h2>Your Profile</h2>
  <p><strong>Name:</strong> <span id="profileName"></span></p>
  <p><strong>Username:</strong> <span id="profileUsername"></span></p>
  <p><strong>Role:</strong> <span id="profileRole"></span></p>
  <p><strong>Department:</strong> <span id="profileDepartment"></span></p>
</section>







    </main>
</div>







<div id="addDoctorModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Add New Doctor</h3>
      <button class="close-modal" onclick="document.getElementById('addDoctorModal').classList.remove('show')">✕</button>
    </div>
    <div class="modal-body">
      <form id="addDoctorForm">
        <input type="text" id="doctorName" placeholder="Full Name" required>
        <input type="text" id="doctorUsername" placeholder="Username" required>
        <input type="email" id="doctorEmail" placeholder="Email" required>
        <input type="password" id="doctorPassword" placeholder="Password" required>
        <input type="text" id="doctorDepartment" placeholder="Department" required>
        <input type="text" id="doctorPhone" placeholder="Phone">
        <input type="text" id="doctorStatus" placeholder="Status (e.g. active)" required>
        <button type="submit" class="btn btn-primary">Save</button>
      </form>
    </div>
  </div>
</div>



<!-- Add Patient Modal (admin_dashboard.php) -->
<div id="addPatientModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Add New Patient</h3>
      <button class="close-modal" onclick="document.getElementById('addPatientModal').classList.remove('show')">✕</button>
    </div>
    <div class="modal-body">
      <form id="addPatientForm" method="POST" action="api_admin/add_patient.php">
        <input name="name"        placeholder="Full Name" required>
        <input name="username"    placeholder="Username" required>
        <input name="password"    type="password" placeholder="Password" required>
        <input name="email"       type="email"    placeholder="Email"    required>
        <input name="phone"       placeholder="Phone">
        <input name="age"         type="number"     placeholder="Age">
        <select name="gender">
          <option value="">Select Gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
        <input name="blood_type"  placeholder="Blood Type">
        <input name="status"      placeholder="Status (e.g. active)">

        <label for="assignDoctorSelect">Assign to Doctor</label>
        <select id="assignDoctorSelect" name="doctor_id" >
          <option value="">Select Doctor…</option>
        </select>

        <button type="submit" class="btn btn-primary">Save</button>
      </form>
    </div>
  </div>
</div>





<script src="assets/js/admin.js"></script>
            <script>lucide.createIcons();</script>

</body>
</html>
