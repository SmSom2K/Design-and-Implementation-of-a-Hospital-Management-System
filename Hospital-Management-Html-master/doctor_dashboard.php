<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.html");
    exit;
}
$doctor_email = $_SESSION['email'];
$doctor_name  = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Doctor Dashboard</title>
  <link rel="stylesheet" href="assets/css2/style.css">
  <link rel="stylesheet" href="assets/css2/dashboard.css">
  <link rel="stylesheet" href="assets/css2/doctor.css">

  <!-- Icons + Charts + PDF -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
<div class="layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo">
        <i data-lucide="heart-pulse"></i>
        <h1>MediCare</h1>
      </div>
      <button id="closeSidebar" class="sidebar-close">
        <i data-lucide="x"></i>
      </button>
    </div>
    <div class="sidebar-content">
      <div class="user-info">
        <div class="user-avatar">
          <img src="https://randomuser.me/api/portraits/men/36.jpg" alt="Doctor">
        </div>
        <div class="user-details">
          <h3 id="doctorName">Dr. <?= htmlspecialchars($doctor_name) ?></h3>
          <p id="doctorDepartment">Cardiology</p>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li class="active"><a href="#dashboard"><i data-lucide="layout-dashboard"></i>Dashboard</a></li>
          <li><a href="#patients"><i data-lucide="users"></i>My Patients</a></li>
          <li><a href="#trend"><i data-lucide="bar-chart-2"></i>Trend</a></li>
          <li><a href="#appointments"><i data-lucide="calendar"></i>My Appointments</a></li>
          <li><a href="#activity"><i data-lucide="list"></i>Activity</a></li>
          <li><a href="#profile"><i data-lucide="user"></i>Profile</a></li>
          <li><a href="logout.php"><i data-lucide="log-out"></i>Logout</a></li>
        </ul>
      </nav>
    </div>
  </aside>
  <!-- MAIN CONTENT -->
  <main class="main-content">
    <!-- DASHBOARD -->
    <section id="dashboard-section" class="content-section active">
      <div class="section-header">
        <h2>Doctor Dashboard</h2>
        <p class="date">Today: <span id="currentDate"><?= date("F j, Y") ?></span></p>
      </div>
      <div class="stats-container">
        <div class="stat-card">
          <div class="stat-icon patient-icon"><i data-lucide="users"></i></div>
          <div class="stat-details">
            <h3>My Patients</h3>
            <p id="myPatientsCount" class="stat-value">0</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon appointment-icon"><i data-lucide="calendar"></i></div>
          <div class="stat-details">
            <h3>Today's Appointments</h3>
            <p id="todayAppointmentsCount" class="stat-value">0</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pending-icon"><i data-lucide="clock"></i></div>
          <div class="stat-details">
            <h3>Pending Tests</h3>
            <p id="pendingTestsCount" class="stat-value">—</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon completed-icon"><i data-lucide="check-circle"></i></div>
          <div class="stat-details">
            <h3>Completed Treatments</h3>
            <p id="completedTreatmentsCount" class="stat-value">—</p>
          </div>
        </div>
      </div>
      <div class="dashboard-grid">
        <div class="grid-card today-appointments">
          <div class="card-header"><h3>Today's Appointments</h3></div>
          <div class="appointment-list" id="appointmentsList"></div>
        </div>
        <div class="grid-card recent-patients">
          <div class="card-header"><h3>Recent Patients</h3></div>
          <div id="recentPatientsList" class="recent-patients-list"></div>
        </div>
      </div>
    </section>

    <!-- MY PATIENTS -->
    <section id="patients-section" class="content-section">
      <div class="section-header"><h2>My Patients</h2></div>
      <div class="search-bar patients-only">
         <i data-lucide="search"></i>
         <input type="text" id="patientSearch" placeholder="Search patients...">
       </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th><th>Name</th><th>Age</th><th>Gender</th>
              <th>Phone</th><th>Blood Type</th><th>Actions</th>
            </tr>
          </thead>
          <tbody id="myPatientsList"></tbody>
        </table>
      </div>
    </section>
  


<!-- MY APPOINTMENTS -->
<section id="appointments-section" class="content-section">
  <div class="section-header">
    <h2>My Appointments</h2>
  </div>

  <!-- Tabs for pending vs accepted -->
  <div class="tabs">
  <button class="tab-btn active" data-tab="pending-appointments">Pending</button>
  <button class="tab-btn" data-tab="accepted-appointments">Accepted</button>
  <button class="tab-btn" data-tab="declined-appointments">Declined</button>
</div>


  <!-- Pending table -->
  <div id="pending-appointments" class="tab-content">
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Time</th><th>Patient</th><th>Purpose</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="pendingTable"></tbody>
      </table>
    </div>
  </div>

  <!-- Accepted table -->
  <div id="accepted-appointments" class="tab-content" style="display:none;">
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Time</th><th>Patient</th><th>Purpose</th><th>Status</th>
          </tr>
        </thead>
        <tbody id="acceptedTable"></tbody>
      </table>
    </div>
  </div>

  <!-- Declined table -->
  <div id="declined-appointments" class="tab-content" style="display:none;">
  <div class="table-container">
    <table class="data-table">
      <thead>
        <tr><th>Time</th><th>Patient</th><th>Purpose</th><th>Status</th></tr>
      </thead>
      <tbody id="declinedTable"></tbody>
    </table>
  </div>
</div>

</section>




    <!-- TREND -->
    <section id="trend-section" class="content-section">
      <div class="section-header"><h2>Appointments Trend</h2></div>
      <div class="chart-container">
        <canvas id="appointmentsChart" width="400" height="200"></canvas>
        <button id="exportChartBtn" class="btn btn-sm">Export Chart</button>
      </div>
    </section>

    <!-- ACTIVITY -->
<section id="activity-section" class="content-section">
  <div class="section-header">
    <h2>Recent Activity</h2>
  </div>
  <div id="activityFeed" class="activity-list">
    <!-- entries will be injected here -->
  </div>
</section>



    <!-- PATIENT DETAILS -->
    <section id="patient-details-section" class="content-section">
      <div class="section-header">
        <button class="btn btn-back" id="backToPatients">
          <i data-lucide="arrow-left"></i> Back
        </button>
        <h2>Patient Details</h2>
      </div>
      <div class="patient-profile">
        <div class="patient-header">
          <div class="patient-avatar">
            <img id="patientDetailAvatar" src="#" alt="Avatar">
          </div>
          <div class="patient-basic-info">
            <h3 id="patientDetailName"></h3>
            <div class="patient-attributes">
              <span id="patientDetailAge"></span>
<span id="patientDetailGender"></span>
<span id="patientDetailBlood"></span>

          <span id="patientAttributes"></span>
            </div>

            <p id="patientDetailId"></p>
          </div>
          <div class="patient-status" id="patientDetailStatus"></div>
        </div>
        <div class="patient-contact">
          <p><strong>Phone:</strong> <span id="patientDetailPhone"></span></p>
          <p><strong>Email:</strong> <span id="patientDetailEmail"></span></p>
          <p><strong>Address:</strong> <span id="patientDetailAddress"></span></p>
        </div>
        <!-- Tabs for Records, Diagnosis, Notes -->
        <div class="tabs">
          <button class="tab-btn active" data-tab="medical-records">Medical Records</button>
          <button class="tab-btn" data-tab="diagnosis">Diagnosis</button>
          <button class="tab-btn" data-tab="notes">Notes</button>
        </div>


               <div id="medical-records" class="tab-content">
  <!-- Upload form container -->
  <div class="upload-form-container">
    <h3>Upload New Document</h3>
    <form
      id="uploadDocForm"
      action="./api_server/upload_document.php"
      method="POST"
      enctype="multipart/form-data"
    >
      <input
        type="hidden"
        name="patient_id"
        id="uploadPatientId"
        value=""
      >
      <div class="form-row">
        <div class="form-group">
          <label for="docType">Type</label>
          <input id="docType" name="doc_type" required placeholder="e.g. Lab Result">
        </div>
        <div class="form-group">
          <label for="docDesc">Description</label>
          <input id="docDesc" name="description" placeholder="Short note">
        </div>
      </div>
      <div class="form-group">
        <label for="docFile">File</label>
        <input id="docFile" name="file" type="file" accept=".pdf,image/*" required>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn">Upload Document</button>
        <span id="uploadFeedback"></span>
      </div>
    </form>
  </div>

  <!-- Title for the records list -->
  <h3 class="records-list-title">Medical Records List</h3>

  <!-- Then the existing records list -->
  <div class="timeline" id="medicalRecordsList"></div>
</div>


        <!-- ===== Diagnosis panel ===== -->

        <div id="diagnosis" class="tab-content" style="display:none;">
          <div class="tab-header">
            <h3>Diagnosis & Treatment</h3>
            <button id="addDiagnosisBtn" class="btn">Add Diagnosis</button>
          </div>

          <div id="diagnosisList"></div>
  
        </div>
        <div id="notes" class="tab-content" style="display:none;">
          <div class="tab-header"><h3>Doctor's Notes</h3></div>
          <textarea id="notesEditor" placeholder="Enter notes..."></textarea>
          <button id="saveNoteBtn" class="btn">Save Note</button>
          <div id="notesHistory"></div>
        </div>

      </div>
    </section>

    <!-- PROFILE -->
    <section id="profile-section" class="content-section">
  <div class="section-header"><h2>Doctor Profile</h2></div>
  <div class="profile-card">
    <div class="profile-header">
      <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Avatar">
      <div class="profile-info">
        <p id="docProfileUsername">Username: —</p>
        <h3 id="docProfileName">—</h3>
        <p id="docProfileEmail">Email: —</p>
        <p id="docProfileId">User ID: —</p>
      </div>
    </div>
    <div class="profile-attributes">
      <span id="docProfileDept" data-label="Department"></span>
      <span id="docProfilePhone" data-label="Phone"></span>
      <span id="docProfileStatus" data-label="Status"></span>
    </div>
  </div>
</section>


  </main>
</div>

<!-- Diagnosis Modal -->
<!-- Diagnosis Modal -->
<div id="addDiagnosisModal" class="modal">
  <div class="modal-content p-6 rounded-lg shadow-lg" style="max-width:500px;margin:auto">
    <div class="modal-header flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold">Add Diagnosis</h3>
      <button class="close-modal text-gray-600 hover:text-gray-900">
        <i data-lucide="x"></i>
      </button>
    </div>
    <form id="addDiagnosisForm" enctype="multipart/form-data" class="grid gap-4">
      <input type="hidden" name="patient_id" id="diagPatientId" value="">
      
      <div class="form-group">
        <label for="diagnosisTitle" class="block mb-1 text-sm">Title</label>
        <input type="text" id="diagnosisTitle" name="title" required
               class="w-full p-2 border rounded" placeholder="e.g. Acute Bronchitis">
      </div>
      
      <div class="form-group">
        <label for="diagnosisDescription" class="block mb-1 text-sm">Description</label>
        <textarea id="diagnosisDescription" name="description" required
                  class="w-full p-2 border rounded" rows="3"
                  placeholder="Detailed notes…"></textarea>
      </div>
      
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group">
          <label for="diagnosisDate" class="block mb-1 text-sm">Date</label>
          <input type="date" id="diagnosisDate" name="date" required
                 class="w-full p-2 border rounded">
        </div>
        <div class="form-group">
          <label for="diagnosisSeverity" class="block mb-1 text-sm">Severity</label>
          <select id="diagnosisSeverity" name="severity" required
                  class="w-full p-2 border rounded">
            <option value="mild">Mild</option>
            <option value="moderate">Moderate</option>
            <option value="severe">Severe</option>
            <option value="critical">Critical</option>
          </select>
        </div>
      </div>
      
      <div class="form-group">
        <label for="treatmentPlan" class="block mb-1 text-sm">Treatment Plan</label>
        <textarea id="treatmentPlan" name="treatment_plan" required
                  class="w-full p-2 border rounded" rows="2"
                  placeholder="Rx, dosing, follow-up…"></textarea>
      </div>
      
      <div class="form-group">
        <label for="medications" class="block mb-1 text-sm">Medications</label>
        <textarea id="medications" name="medications"
                  class="w-full p-2 border rounded" rows="2"
                  placeholder="List of meds…"></textarea>
      </div>
      
      <div class="form-group">
        <label for="followUpDate" class="block mb-1 text-sm">Follow-Up Date</label>
        <input type="date" id="followUpDate" name="follow_up_date"
               class="w-full p-2 border rounded">
      </div>
      
      <div class="form-group">
        <label for="diagnosisFile" class="block mb-1 text-sm">Attach File (optional)</label>
        <input type="file" id="diagnosisFile" name="diagnosis_file"
               class="w-full p-2 border rounded" accept=".pdf,image/*">
      </div>
      
      <div class="text-right mt-2">
        <button type="submit" class="btn btn-primary px-4 py-2 rounded">
          Save Diagnosis
        </button>
      </div>
    </form>
  </div>
</div>
</main>

<div id="notification" class="notification"></div>

<script src="assets/js/doctor.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
