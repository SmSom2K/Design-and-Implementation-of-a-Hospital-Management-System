<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Patient Portal – MediCare</title>
  <link rel="stylesheet" href="assets/css2/style.css">
  <link rel="stylesheet" href="assets/css2/dashboard.css">
  <link rel="stylesheet" href="assets/css2/patient.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="logo">
          <i data-lucide="heart-pulse" class="logo-icon"></i>
          <h1>MediCare</h1>
        </div>
        <button id="closeSidebar" class="sidebar-close"><i data-lucide="x"></i></button>
      </div>
      <div class="sidebar-content">
        <div class="user-info">
          <div class="user-avatar">
            <img id="sidebarAvatar" src="https://randomuser.me/api/portraits/men/62.jpg" alt="Patient">
          </div>
          <div class="user-details">
            <h3 id="patientName">—</h3>
            <p>Patient</p>
          </div>
        </div>
        <nav class="sidebar-nav">
          <ul>
            <li class="active"><a href="#dashboard"><i data-lucide="layout-dashboard"></i>Dashboard</a></li>
            <li><a href="#medical-history"><i data-lucide="clipboard-list"></i>Medical History</a></li>
            <li><a href="#appointments"><i data-lucide="calendar"></i>Appointments</a></li>
            <li><a href="#profile"><i data-lucide="user"></i>Profile</a></li>
            <li><a href="#" id="logoutBtn"><i data-lucide="log-out"></i>Logout</a></li>
          </ul>
        </nav>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="main-header">
        <button id="openSidebar" class="sidebar-toggle"><i data-lucide="menu"></i></button>
        
      </header>

      <!-- Dashboard -->
      <section id="dashboard-section" class="content-section active">
        <div class="section-header">
          <h2>Patient Dashboard</h2>
          <p class="date">Today: <span id="currentDate"></span></p>
        </div>
        <div class="stats-container">
          <div class="stat-card">
            <i data-lucide="calendar-check" class="stat-icon"></i>
            <div class="stat-details">
              <h3>Upcoming Appointments</h3>
              <p class="stat-value">—</p>
            </div>
          </div>
          <div class="stat-card">
            <i data-lucide="pill" class="stat-icon"></i>
            <div class="stat-details">
              <h3>Active Prescriptions</h3>
              <p class="stat-value">—</p>
            </div>
          </div>
          <div class="stat-card">
            <i data-lucide="clipboard-check" class="stat-icon"></i>
            <div class="stat-details">
              <h3>Test Results</h3>
              <p class="stat-value">—</p>
            </div>
          </div>
          <div class="stat-card">
            <i data-lucide="stethoscope" class="stat-icon"></i>
            <div class="stat-details">
              <h3>My Doctor</h3>
              <p id="myDoctorName">—</p>
            </div>
          </div>
        </div>
        <div class="grid-card recent-activity">
          <div class="card-header"><h3>Recent Medical Updates</h3></div>
          <div class="timeline" id="recentActivityList">
            <!-- loaded by JS -->
          </div>
        </div>
      </section>

      <!-- Medical History -->
      <section id="medical-history-section" class="content-section">
  <div class="section-header"><h2>Medical History</h2></div>
  <div class="tabs">
    <button class="tab-btn active" data-tab="diagnoses">Diagnoses</button>
    <!-- 1️⃣ add this: -->
    <button class="tab-btn"       data-tab="medical-records">Records</button>
  </div>

  <div id="diagnoses" class="tab-content">
    <div class="diagnoses-list" id="diagnosesList"></div>
  </div>

  <!-- 2️⃣ add this below -->
  <div id="medical-records" class="tab-content" style="display:none">
    <div class="medical-records-list" id="medicalRecordsList"></div>
  </div>
</section>


      <!-- Appointments -->
<!-- Appointments -->
<section id="appointments-section" class="content-section">
  <div class="section-header">
    <h2>Appointments</h2>
    <button id="openRequestAppt" class="btn btn-primary">Request Appointment</button>
  </div>

  <div class="tabs">
    <button class="tab-btn active" data-tab="pending-appointments">Pending</button>
    <button class="tab-btn"       data-tab="accepted-appointments">Accepted</button>
    <button class="tab-btn"       data-tab="declined-appointments">Declined</button>
  </div>

  <div id="pending-appointments" class="tab-content">
    <table class="data-table">
      <thead><tr><th>Time</th><th>Doctor</th><th>Purpose</th><th>Actions</th></tr></thead>
      <tbody id="pendingTable"></tbody>
    </table>
  </div>

  <div id="accepted-appointments" class="tab-content" style="display:none">
    <table class="data-table">
      <thead><tr><th>Time</th><th>Doctor</th><th>Purpose</th><th>Status</th></tr></thead>
      <tbody id="acceptedTable"></tbody>
    </table>
  </div>

  <div id="declined-appointments" class="tab-content" style="display:none">
    <table class="data-table">
      <thead><tr><th>Time</th><th>Doctor</th><th>Purpose</th><th>Status</th></tr></thead>
      <tbody id="declinedTable"></tbody>
    </table>
  </div>
</section>


<!-- Request Appointment Modal -->
<!-- in patient_dashboard.php, inside #appointments-section -->


<!-- REQUEST APPOINTMENT MODAL (place this right below your #appointments-section) -->
<div id="requestApptModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h3>Request an Appointment</h3>
    <form id="requestApptForm">
      <div class="form-group">
        <label for="doctorSelect">Doctor</label>
        <select id="doctorSelect" required>
          <option value="">Loading doctors…</option>
        </select>
      </div>
      <div class="form-group">
        <label for="apptDateTime">Date &amp; Time</label>
        <input type="datetime-local" id="apptDateTime" required>
      </div>
      <div class="form-group">
        <label for="apptPurpose">Purpose</label>
        <textarea id="apptPurpose" rows="2" required></textarea>
      </div>
      <button type="submit" class="btn btn-success">Send Request</button>
      <div id="requestApptFeedback" style="margin-top:0.5rem;font-size:0.9rem;"></div>
    </form>
  </div>
</div>



   <!-- Profile -->
<section id="profile-section" class="content-section">
  <div class="section-header">
    <h2>Patient Profile</h2>
  </div>
  <div class="profile-card">
    <div class="profile-header">
      <img id="profileAvatar" src="https://randomuser.me/api/portraits/men/62.jpg" alt="Avatar">
      <div class="profile-info">
        <!-- New: show username -->
        <p id="profileUsername">Username: —</p>
        <!-- Name -->
        <h3 id="profileName">—</h3>
        <!-- New: show email -->
        <p id="profileEmail">Email: —</p>
        <!-- Patient ID -->
        <p id="profileId">Patient ID: —</p>
        <!-- Attributes grid -->
        <div class="profile-attributes">
  <span id="profileAge"    data-label="Age"></span>
  <span id="profileGender" data-label="Gender"></span>
  <span id="profileBlood"  data-label="Blood Type"></span>
  <span id="profilePhone"  data-label="Phone"></span>
  <span id="profileStatus" data-label="Status"></span>
</div>

      </div>
    </div>
  </div>
</section>


  <div id="notification" class="notification"></div>

  <script src="assets/js/patient.js"></script>
  <script>
    lucide.createIcons();
    // Protect route if needed
  </script>
</body>
</html>
