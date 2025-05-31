// Admin Dashboard JS (Cleaned & Merged)

document.addEventListener('DOMContentLoaded', () => {
    const doctorsList      = document.getElementById('doctorsList');
    const patientsList     = document.getElementById('patientsList');
    const assignmentsList  = document.getElementById('assignmentsList');
    const addDoctorForm    = document.getElementById('addDoctorForm');
    const addPatientForm   = document.getElementById('addPatientForm');
    const assignDoctor     = document.getElementById('assignDoctor');
    const assignPatient    = document.getElementById('assignPatient');
    const doctorCount      = document.getElementById('doctorCount');
    const patientCount     = document.getElementById('patientCount');

    initNavigation();
    loadInitialData();
    loadProfile();

    // ASSIGN PATIENT
    const assignmentForm = document.getElementById('assignmentForm');
    if (assignmentForm && assignDoctor && assignPatient) {
        assignmentForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const doctorId  = assignDoctor.value;
            const patientId = assignPatient.value;
            if (!doctorId || !patientId) {
                return alert("Select both doctor and patient.");
            }
            const result = await assignPatientToDoctor({ doctorId, patientId });
            if (!result.success) {
                return alert("❌ " + result.error);
            }
            await loadInitialData();
        });
    }

    // ADD DOCTOR
    const addDoctorBtn = document.getElementById('addDoctorBtn');
    const doctorModal  = document.getElementById('addDoctorModal');
    if (addDoctorBtn && addDoctorForm && doctorModal) {
        addDoctorBtn.addEventListener('click', () => {
            doctorModal.classList.add('show');
        });

        addDoctorForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const doctor = {
                name:       document.getElementById('doctorName').value,
                username:   document.getElementById('doctorUsername').value,
                email:      document.getElementById('doctorEmail').value,
                password:   document.getElementById('doctorPassword').value,
                department: document.getElementById('doctorDepartment').value,
                phone:      document.getElementById('doctorPhone').value,
                status:     document.getElementById('doctorStatus').value,
            };
            await addDoctor(doctor);
            await loadInitialData();
            addDoctorForm.reset();
            doctorModal.classList.remove('show');
        });
    }

    // ADD PATIENT
    const addPatientBtn = document.getElementById('addPatientBtn');
    const patientModal  = document.getElementById('addPatientModal');
    if (addPatientBtn && addPatientForm && patientModal) {
        addPatientBtn.addEventListener('click', () => {
            patientModal.classList.add('show');
        });

        addPatientForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const response = await fetch('api/add_patient.php', {
                method: 'POST',
                body:   formData
            });
            const result = await response.json();
            console.log("Server response:", result);

            if (result.success) {
                alert("✅ Patient added successfully!");
                this.reset();
                loadInitialData();
                patientModal.classList.remove('show');
            } else {
                alert("❌ Error adding patient: " + result.error);
            }
        });
    }
});

// API Helper
async function fetchJSON(url, method = 'GET', body = null) {
    const options = { method };
    if (body) {
        options.headers = { 'Content-Type': 'application/json' };
        options.body    = JSON.stringify(body);
    }
    const response = await fetch(url, options);
    return await response.json();
}

// API Calls
const getDoctors            = ()   => fetchJSON('api/get_doctors.php');
const getPatients           = ()   => fetchJSON('api/get_patients.php');
const getAssignments        = ()   => fetchJSON('api/get_assignments.php');
const addDoctor             = doc  => fetch('api/add_doctor.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(doc)
}).then(r => r.json());
const assignPatientToDoctor = data => fetchJSON('api/assign_patient.php', 'POST', data);
const deleteDoctor          = id   => fetchJSON(`api/delete_doctor.php?id=${id}`, 'DELETE');
const deletePatient         = id   => fetchJSON(`api/delete_patient.php?id=${id}`, 'DELETE');
const getActivityLog        = ()   => fetchJSON('api/get_activity.php');

// Init Functions
function initNavigation() {
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    const sections = document.querySelectorAll('.content-section');
    navLinks.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            navLinks.forEach(l => l.parentElement.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            const sectionId = link.getAttribute('href').substring(1);
            const section   = document.getElementById(sectionId);
            if (section) {
                section.classList.add('active');
                link.parentElement.classList.add('active');
            }
        });
    });
    const initialHash = window.location.hash.substring(1);
    const initialSection = document.getElementById(initialHash);
    if (initialSection) {
        sections.forEach(s => s.classList.remove('active'));
        initialSection.classList.add('active');
    }
}

async function loadInitialData() {
    try {
        const doctors     = await getDoctors();
        const patients    = await getPatients();
        const assignments = await getAssignments();

        renderDoctorsList(doctors);
        renderPatientsList(patients);
        renderAssignments(assignments);

        // assignment form dropdowns
        populateSelect(
          document.getElementById('assignDoctor'),
          doctors
        );
        populateSelect(
          document.getElementById('assignPatient'),
          patients.map(p => ({ id: p.user_id, name: p.name }))
        );

        // **Add Patient** modal doctor dropdown
        const modalDocSel = document.getElementById('assignDoctorSelect');
        if (modalDocSel) {
          populateSelect(modalDocSel, doctors);
        }

        // update your “My Patients” / “Today’s Appointments” stats
        updateCounters(patients, assignments);

        await loadActivityLog();
    } catch (err) {
        console.error("❌ Error in loadInitialData():", err);
    }
}

function populateSelect(selectEl, items) {
    if (!selectEl) return;
    selectEl.innerHTML = '<option value="">Select...</option>';
    items.forEach(item => {
        const opt = document.createElement('option');
        opt.value       = item.id;
        opt.textContent = item.name;
        selectEl.appendChild(opt);
    });
}

// replace your old updateCounters entirely with this:
function updateCounters(patients, assignments) {
  const myPatientsEl        = document.getElementById('myPatientsCount');
  const todayApptsEl        = document.getElementById('todayAppointmentsCount');
  if (myPatientsEl)     myPatientsEl.textContent = patients.length;
  if (todayApptsEl)     todayApptsEl.textContent = assignments.length;
  // …and similarly for pendingTestsCount, completedTreatmentsCount if you track those…
}


async function loadProfile() {
    const res     = await fetch('api/get_profile.php');
    const profile = await res.json();
    document.getElementById('profileName')    .textContent = profile.name;
    document.getElementById('profileUsername').textContent = profile.username;
    document.getElementById('profileRole')    .textContent = profile.role;
}

function renderDoctorsList(doctors) {
    const tbl = document.getElementById('doctorsList');
    if (!tbl) return;
    if (doctors.length === 0) {
        tbl.innerHTML = '<tr><td colspan="8">No doctors found.</td></tr>';
        return;
    }
    tbl.innerHTML = '';
    doctors.forEach(d => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${d.id}</td>
            <td>${d.name}</td>
            <td>${d.username || '-'}</td>
            <td>${d.email    || '-'}</td>
            <td>${d.department|| '-'}</td>
            <td>${d.phone   || '-'}</td>
            <td>${d.status  || '-'}</td>
            <td>
                <button onclick="deleteDoctor(${d.id}).then(() => loadInitialData())">
                    Delete
                </button>
            </td>
        `;
        tbl.appendChild(row);
    });
}

function renderPatientsList(patients) {
    const tbl = document.getElementById('patientsList');
    if (!tbl) return;
    tbl.innerHTML = '';
    patients.forEach(p => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${p.user_id}</td>
            <td>${p.name}</td>
            <td>${p.username}</td>        <!-- added -->
            <td>${p.email || '-'}</td>    <!-- added -->
            <td>${p.gender}</td>
            <td>${p.phone}</td>
            <td>${p.blood_type || '-'}</td>
            <td>
                <button onclick="deletePatient(${p.user_id}).then(() => loadInitialData())">
                    Delete
                </button>
            </td>
        `;
        tbl.appendChild(row);
    });
}


function renderAssignments(assignments) {
    const tbl = document.getElementById('assignmentsList');
    if (!tbl) return;
    tbl.innerHTML = '';
    assignments.forEach(a => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${a.doctorName}</td>
            <td>${a.patientName}</td>
            <td>${a.dateAssigned}</td>
        `;
        tbl.appendChild(row);
    });
}

async function loadActivityLog() {
    const logs = await getActivityLog();
    const container = document.querySelector('.recent-activity');
    if (!container) return;
    container.innerHTML = '<h3>Recent Activity</h3>';
    logs.forEach(log => {
        const div = document.createElement('div');
        div.className = 'activity-item';
        div.innerHTML = `
            <div class="activity-details">
                <p>${log.description}</p>
                <small>${new Date(log.timestamp).toLocaleString()}</small>
            </div>
        `;
        container.appendChild(div);
    });
}
