// doctor.js

// Doctor Dashboard (PHP‐connected) with live search, chart & PDF export, and activity feed

// DOM helper
const el = id => document.getElementById(id);

// Core elements
// Map the detail‐panel elements by their IDs:
const patientDetailsElements = {
  avatar:  el('patientDetailAvatar'),
  name:    el('patientDetailName'),
  age:     el('patientDetailAge'),
  gender:  el('patientDetailGender'),
  blood:   el('patientDetailBlood'),
  id:      el('patientDetailId'),
  phone:   el('patientDetailPhone'),
  email:   el('patientDetailEmail'),
  address: el('patientDetailAddress'),
  status:  el('patientDetailStatus'),
  

};
// ——— Doctor profile elements —————————————————
const docProfileUsername   = el('docProfileUsername');
const docProfileName       = el('docProfileName');
const docProfileEmail      = el('docProfileEmail');
const docProfileId         = el('docProfileId');
const docProfileDept       = el('docProfileDept');
const docProfilePhone      = el('docProfilePhone');
const docProfileStatus     = el('docProfileStatus');

const recentPatientsList   = el('recentPatientsList');
const myPatientsCount      = el('myPatientsCount');
const appointmentsList     = el('appointmentsList');
const appointmentsTable   = el('appointmentsTable');
const activityFeed         = el('activityFeed');
const notesEditor          = el('notesEditor');
const saveNoteBtn          = el('saveNoteBtn');
const backToPatientsBtn    = el('backToPatients');
const patientSearchInput   = el('patientSearch');
const addDiagnosisForm     = el('addDiagnosisForm');
const uploadForm     = el('uploadDocForm');
const uploadFeedback = el('uploadFeedback');
const getDoctorProfile = () =>fetch('api_doctor/get_profile.php').then(r => r.json());


// ——— Load & render doctor profile —————————————————
async function loadDoctorProfile() {
  const d = await getDoctorProfile();
  if (!d) return;
  docProfileUsername.textContent = `Username: ${d.username}`;
  docProfileName.textContent     = d.name;
  docProfileEmail.textContent    = `Email: ${d.email || 'No email provided'}`;    // ← add "Email: "
  docProfileId.textContent       = `User ID: ${d.user_id}`;                      // ← add "User ID: "
  docProfileDept.textContent     = d.department;   // these spans get their labels via data-label
  docProfilePhone.textContent    = d.phone;
  docProfileStatus.textContent   = d.status;
}


let currentPatientId = null;

window.addEventListener('DOMContentLoaded', async () => {

 

  // 1) Load doctor profile
 
  await loadDoctorProfile();

  // 2) Sidebar tab navigation
    document.querySelectorAll('.sidebar-nav a[href^="#"]').forEach(link => {
    link.addEventListener('click', async e => {
      e.preventDefault();
      // deactivate sidebar & sections
      document.querySelectorAll('.sidebar-nav li').forEach(li => li.classList.remove('active'));
      document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
      // activate the one we clicked
      link.parentElement.classList.add('active');
      const secId = link.getAttribute('href').substring(1) + '-section';
      el(secId)?.classList.add('active');

      // if it's the Appointments tab, reload + default to Pending
      if (secId === 'appointments-section') {
        await loadDoctorAppointments();
      }
    });
  });

 
  // 3) Back to patients list
  backToPatientsBtn?.addEventListener('click', e => {
    e.preventDefault();
    el('patient-details-section').classList.remove('active');
    el('patients-section').classList.add('active');
    currentPatientId = null;
  });

  // 4) Live patient search
  patientSearchInput?.addEventListener('input', async function() {
    const term = this.value.toLowerCase();
    const allPatients = await getPatients();
    const filtered = allPatients.filter(p =>
      ['name','email','phone','blood_type','gender']
        .some(k => (p[k]||'').toLowerCase().includes(term))
    );
    renderMyPatientsList(filtered);
  });

  // 5) Save note
  saveNoteBtn?.addEventListener('click', async () => {
    const note = notesEditor.value.trim();
    if (!currentPatientId || !note) return;
    await addNote(currentPatientId, note);
    await loadNotes(currentPatientId);
    await loadActivityFeed();
    notesEditor.value = '';
  });

  // 6) Add diagnosis (with optional file upload)
const addDiagnosisForm = el('addDiagnosisForm');
addDiagnosisForm?.addEventListener('submit', async e => {
  e.preventDefault();
  if (!currentPatientId) return;

  // build FormData from the form
  const form = e.target;
  const data = new FormData(form);
  data.set('patient_id', currentPatientId);

  // send to PHP
  const res = await fetch('api_doctor/add_diagnosis.php', {
    method: 'POST',
    body: data
  });
  const json = await res.json();

  if (json.success) {
    // refresh list & close modal
    await loadDiagnoses(currentPatientId);
    await loadActivityFeed();
    el('addDiagnosisModal')?.classList.remove('show');
    form.reset();
  } else {
    alert('Error saving diagnosis: ' + json.error);
  }
});



  // 7) Initial data loads
  await loadMyPatients();
  await loadRecentPatients();

  // 8) Appointments & chart
  
 
  await loadDoctorAppointments();

  // 9) Activity feed
  await loadActivityFeed();

 // 10) Tab switching for the Patient Details section
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      // 1) Remove “active” from all buttons
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      // 2) Hide all tab‐content panels
      document.querySelectorAll('.tab-content').forEach(panel => panel.style.display = 'none');
      // 3) Mark this button active
      btn.classList.add('active');
      // 4) Show the panel whose id matches data-tab
      const tabId = btn.dataset.tab;                // e.g. "diagnosis"
      const panel = document.getElementById(tabId);
      if (panel) panel.style.display = 'block';
    });
  });

// Also, immediately show the first tab by default:
  // show “medical-records” on load
  document.querySelectorAll('.tab-content').forEach(p => p.style.display = 'none');
  document.getElementById('medical-records').style.display = 'block';
  document.querySelector('.tab-btn[data-tab="medical-records"]').classList.add('active');

  // 11) Wire up the “Add Diagnosis” button to show the modal
  const addDiagBtn = el('addDiagnosisBtn');
  const diagModal  = el('addDiagnosisModal');
  const closeModal = diagModal.querySelector('.close-modal');

  if (addDiagBtn && diagModal) {
    addDiagBtn.addEventListener('click', () => {
      diagModal.classList.add('show');
    });
  }

  // 12) Wire up the close “X” in the modal
  if (closeModal) {
    closeModal.addEventListener('click', () => {
      diagModal.classList.remove('show');
    });
  }


});



// —— Patient Lists —————————————————————————————————————————

// —— Render “My Patients” table —————————————————————————————————
function renderMyPatientsList(patients) {
  myPatientsList.innerHTML = '';
  if (!patients.length) {
    myPatientsList.innerHTML = '<tr><td colspan="7">No patients assigned.</td></tr>';
    return;
  }
  patients.forEach(p => {
    myPatientsList.innerHTML += `
      <tr>
        <td>${p.user_id}</td>
        <td>${p.name}</td>
        <td>${p.age}</td>
        <td><span class="gender-badge">
              <i data-lucide="${p.gender==='male'?'mars':'venus'}"></i> ${p.gender}
            </span></td>
        <td>${p.phone}</td>
        <td><span class="blood-badge">${p.blood_type}</span></td>
        <td>
          <button class="view-patient action-btn" data-id="${p.user_id}">
            <i data-lucide="eye"></i> View
          </button>
        </td>
      </tr>`;
  });
  lucide.createIcons();
  // wire up the View buttons
  myPatientsList.querySelectorAll('.view-patient').forEach(btn =>
    btn.addEventListener('click', () => viewPatientDetails(btn.dataset.id))
  );
}



// My Patients
async function loadMyPatients() {
  const patients = await getPatients();
  renderMyPatientsList(patients);
  if (myPatientsCount) myPatientsCount.textContent = patients.length;
}


// My patients
async function loadRecentPatients() {
  const patients = await getPatients();
  const activity = await getActivity();
  let recent = [];

  if (activity.length) {
    const recentIds = activity.map(a => a.patient_id);
    recent = patients.filter(p => recentIds.includes(p.user_id));
  }

  // fallback: if no activity (or fewer than 4), show the first 4 assigned
  if (recent.length < 4) {
    const assignedOnly = patients.filter(p => /* you can add any extra filter here */ true);
    // merge without dupes
    const ids = new Set(recent.map(p=>p.user_id));
    for (const p of assignedOnly) {
      if (recent.length >= 4) break;
      if (!ids.has(p.user_id)) {
        recent.push(p);
        ids.add(p.user_id);
      }
    }
  }

  recent = recent.slice(0,4);

  recentPatientsList.innerHTML = '';
  recent.forEach(p => {
    recentPatientsList.innerHTML += `
      <div class="patient-list-item">
        <div class="patient-avatar">
          <img src="https://randomuser.me/api/portraits/${p.gender==='male'?'men':'women'}/${Math.floor(Math.random()*70)}.jpg">
        </div>
        <div class="patient-list-info">
          <h4>${p.name}</h4>
          <div class="patient-meta">
            <span><i data-lucide="calendar"></i> ${p.age} yrs</span>
            <span><i data-lucide="${p.gender==='male'?'mars':'venus'}"></i> ${p.gender}</span>
            <span><i data-lucide="droplet"></i> ${p.blood_type}</span>
          </div>
        </div>
        <div class="patient-list-action">
          <button class="view-patient-btn" data-id="${p.user_id}">
            View <i data-lucide="chevron-right"></i>
          </button>
        </div>
      </div>`;
  });

  lucide.createIcons();

  // wire up the “dashboard” view buttons to behave exactly like the My-Patients ones:
  recentPatientsList.querySelectorAll('.view-patient-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const userId = btn.dataset.id;

      // 1) switch the sidebar to “My Patients”
      document.querySelector('.sidebar-nav a[href="#patients"]').click();

      // 2) re-render the table (to be safe)
      await loadMyPatients();

      // 3) find and click its corresponding “eye” button in the table
      const tableBtn = document.querySelector(
        `#myPatientsList button.view-patient[data-id="${userId}"]`
      );
      if (tableBtn) {
        tableBtn.click();
      } else {
        // fallback
        viewPatientDetails(userId);
      }
    });
  });
}




// —— My Appointments —————————————————————————————————————

async function loadMyAppointments() {
  const apps = await getAppointments();
  renderMyAppointmentsTable(apps);
}

function renderMyAppointmentsTable(appointments) {
  if (!appointmentsTable) return;
  appointmentsTable.innerHTML = '';
  if (appointments.length === 0) {
    appointmentsTable.innerHTML = '<tr><td colspan="5">No appointments found.</td></tr>';
    return;
  }
  appointments.forEach(a => {
    appointmentsTable.innerHTML += `
      <tr data-id="${a.id}">
        <td>${new Date(a.appointment_time).toLocaleString()}</td>
        <td>${a.patient_name}</td>
        <td>${a.purpose}</td>
        <td>${a.status || 'Upcoming'}</td>
        <td>
          <button class="delete-appointment btn btn-sm">Delete</button>
        </td>
      </tr>`;
  });

  // wire up deletes
  appointmentsTable.querySelectorAll('.delete-appointment').forEach(btn => {
    btn.addEventListener('click', async () => {
      const tr     = btn.closest('tr');
      const apptId = tr.dataset.id;
      if (!confirm('Delete this appointment?')) return;

      const res = await fetch('api_doctor/delete_appointment.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ appointment_id: apptId })
      });
      const jr = await res.json();
      if (jr.success) {
        // <-- Replace the old `tr.remove()` with this:
        await refreshAppointmentsUI();
        await loadActivityFeed();
      } else {
        alert('Error deleting: ' + jr.error);
      }
    });
  });
}




// —— My Patients table —————————————————————————————————————————
async function viewPatientDetails(userId) {
  // 1) fetch patient + latest diagnosis in parallel
  const [resP, diagnosis] = await Promise.all([
    fetch(`api_doctor/get_patient.php?id=${userId}`),
    getLatestDiagnosis(userId)
  ]);

  if (!resP.ok) {
    const err = await resP.json().catch(() => ({ error: resP.statusText }));
    return alert('Error loading patient: ' + (err.error || resP.statusText));
  }
  const p = await resP.json();

  // 2) stash for uploads/notes/etc.
  currentPatientId = userId;
  el('uploadPatientId').value = userId;

  // 3) avatar & name are always present
  patientDetailsElements.avatar.src = 
    `https://randomuser.me/api/portraits/${p.gender==='male'?'men':'women'}/${Math.floor(Math.random()*70)}.jpg`;
  patientDetailsElements.name.textContent = p.name;

  // 4) map of property → [element, formatterFn?]
  const mapping = {
    age:     [el('patientDetailAge'),    v => v + ' yrs'],
    gender:  [el('patientDetailGender')],
    blood_type: [el('patientDetailBlood')],
    id:      [patientDetailsElements.id, v => `Patient ID: ${v}`],
    phone:   [patientDetailsElements.phone],
    email:   [patientDetailsElements.email],
    address: [patientDetailsElements.address]
  };

  Object.entries(mapping).forEach(([prop, [element, fmt]]) => {
    if (p[prop] != null && element) {
      element.textContent = fmt ? fmt(p[prop]) : p[prop];
      element.parentElement?.classList.remove('hidden');
    } else {
      // if you want to hide the whole line when missing:
      element?.parentElement?.classList.add('hidden');
    }
  });

  // 5) combined attributes line: only include the bits you've got
  const attrs = [];
  if (p.age    != null) attrs.push(`Age: ${p.age} years`);
  if (p.gender != null) attrs.push(`Gender: ${p.gender}`);
  if (p.blood_type != null) attrs.push(`Blood Type: ${p.blood_type}`);
  el('patientAttributes').textContent = attrs.join('  |  ');

  // 6) status badge
  if (diagnosis) {
    patientDetailsElements.status.textContent = `${diagnosis.severity} Treatment`;
    patientDetailsElements.status.className   = `patient-status severity-${diagnosis.severity}`;
  } else {
    patientDetailsElements.status.textContent = 'Active Treatment';
    patientDetailsElements.status.className   = 'patient-status severity-mild';
  }

  // 7) load the other panels
  await loadDiagnoses(userId);
  await loadNotes(userId);
  await loadMedicalDocuments(userId);
  await loadActivityFeed();

  // 8) force show “Medical Records” tab
  document.querySelectorAll('#patient-details-section .tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('#patient-details-section .tab-content').forEach(c => c.style.display = 'none');
  const mrBtn = document.querySelector('#patient-details-section .tab-btn[data-tab="medical-records"]');
  const mrPanel = el('medical-records');
  mrBtn.classList.add('active');
  mrPanel.style.display = 'block';

  // 9) switch sections
  el('patients-section').classList.remove('active');
  el('patient-details-section').classList.add('active');
}


// —— Appointments Trend & PDF ——————————————————————————
async function getAllAppointments() {
  return fetch('api_doctor/get_appointments.php').then(res => res.json());
}

//
function renderAppointmentsList(appointments) {
  if (!appointmentsList) return;
  appointmentsList.innerHTML = '';
  if (appointments.length === 0) {
    appointmentsList.innerHTML = '<p>No appointments today.</p>';
    return;
  }
  appointments.forEach(a => {
    const item = document.createElement('div');
    item.className = 'appointment-item';
    item.innerHTML = `
      <div class="appointment-time">${a.appointment_time}</div>
      <div class="appointment-info">
        <h4>${a.patient_name}</h4>
        <p>${a.purpose}</p>
      </div>
      <div class="appointment-status upcoming">${a.status||'Upcoming'}</div>`;
    appointmentsList.appendChild(item);
  });
}

function renderAppointmentsChart(appointments) {
  const ctx = el('appointmentsChart')?.getContext('2d');
  if (!ctx) return;
  const counts = {};
  appointments.forEach(a => {
    const day = new Date(a.appointment_time).toLocaleDateString();
    counts[day] = (counts[day]||0) + 1;
  });
  const labels = Object.keys(counts), data = labels.map(d=>counts[d]);

  if (window._appChart) window._appChart.destroy();
  window._appChart = new Chart(ctx, {
    type: 'line',
    data: { labels, datasets:[{ label:'Appointments', data, fill:false, tension:0.2 }] },
    options:{ scales:{ y:{ beginAtZero:true } } }
  });
}

el('exportChartBtn')?.addEventListener('click', () => {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  const canvas = el('appointmentsChart');
  if (canvas) {
    doc.addImage(canvas.toDataURL('image/png'), 'PNG', 15, 20, 180, 80);
    doc.save('appointments_trend.pdf');
  }
}); 
//



// —— My Appointments —————————————————————————————————————

async function loadDoctorAppointments() {
  // 1) fetch all three lists
  const res = await fetch('api_doctor/get_appointments.php');
  if (!res.ok) {
    console.error('Failed to load appointments', await res.text());
    return;
  }
  const { pending, accepted, declined } = await res.json();

  // 2) render helpers
  const renderTable = (tbodyId, rows, columns, withActions=false) => {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    if (rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="${columns.length}">No ${tbodyId.replace('Table','')}.</td></tr>`;
      return;
    }
    tbody.innerHTML = rows.map(r => {
      const cells = columns.map(col => {
        if (col === 'time')    return `<td>${new Date(r.appointment_time).toLocaleString()}</td>`;
        if (col === 'patient') return `<td>${r.patient_name}</td>`;
        if (col === 'purpose') return `<td>${r.purpose}</td>`;
        if (col === 'actions') {
          return `
            <td>
              <button class="btn accept-btn" data-id="${r.id}">Accept</button>
              <button class="btn decline-btn" data-id="${r.id}">Decline</button>
            </td>`;
        }
        if (col === 'status')  return `<td>${r.status.charAt(0).toUpperCase()+r.status.slice(1)}</td>`;
      }).join('');
      return `<tr data-id="${r.id}">${cells}</tr>`;
    }).join('');
  };

  // 3) render each table
  renderTable('pendingTable',   pending,  ['time','patient','purpose','actions'], true);
  renderTable('acceptedTable',  accepted, ['time','patient','purpose','status']);
  renderTable('declinedTable',  declined, ['time','patient','purpose','status']);

  // 4) wire up accept/decline buttons in **Pending** only
  document.querySelectorAll('.accept-btn').forEach(btn =>
    btn.addEventListener('click', async () => {
      await fetch('api_doctor/update_appointment.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id:btn.dataset.id, status:'accepted' })
      });
      loadDoctorAppointments();
    })
  );
  document.querySelectorAll('.decline-btn').forEach(btn =>
    btn.addEventListener('click', async () => {
      await fetch('api_doctor/update_appointment.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id:btn.dataset.id, status:'declined' })
      });
      loadDoctorAppointments();
    })
  );

  // 5) tab‐switching
  const tabButtons = document.querySelectorAll('#appointments-section .tab-btn');
  const tabContents = document.querySelectorAll('#appointments-section .tab-content');
  tabButtons.forEach(btn => {
    btn.onclick = () => {
      // hide all
      tabButtons.forEach(b => b.classList.remove('active'));
      tabContents.forEach(c => c.style.display = 'none');
      // show this
      btn.classList.add('active');
      const panel = document.getElementById(btn.dataset.tab);
      if (panel) panel.style.display = 'block';
    };
  });

  // 6) ensure “Pending” is active on initial load
  const defaultBtn   = document.querySelector('.tab-btn[data-tab="pending-appointments"]');
  const defaultPanel = document.getElementById('pending-appointments');
  if (defaultBtn && defaultPanel) {
    // hide all, then show pending
    tabButtons.forEach(b => b.classList.remove('active'));
    tabContents.forEach(c => c.style.display = 'none');
    defaultBtn.classList.add('active');
    defaultPanel.style.display = 'block';
  }
}

// ⇢ Call it once on startup:
loadDoctorAppointments();






// —— Activity Feed —————————————————————————————————————————
async function loadActivityFeed() {
  const res = await fetch('api_doctor/get_activity.php');
  const logs = await res.json();
  const container = document.getElementById('activityFeed');
  container.innerHTML = ''; // clear old

  logs.forEach(l => {
    const item = document.createElement('div');
    item.className = 'activity-item';
    item.innerHTML = `
      <div class="activity-header">
        <div class="activity-description">${l.description}</div>
        <div class="activity-actions">
          <small class="activity-timestamp">${new Date(l.timestamp).toLocaleString()}</small>
          <button class="delete-activity" data-id="${l.id}" title="Delete">🗑️</button>
        </div>
      </div>
    `;
    container.appendChild(item);
  });

  // wire up delete buttons…
  container.querySelectorAll('.delete-activity').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      if (!confirm('Delete this activity?')) return;
      await fetch('api_doctor/delete_activity.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ activity_id: id })
      });
      loadActivityFeed();
    });
  });
}



// —— Notes & Diagnoses ———————————————————————————————————
async function loadNotes(patientId) {
  const notes = await fetch(`api_doctor/get_notes.php?patient_id=${patientId}`)
                      .then(r => r.json());

  el('notesHistory').innerHTML = notes.map(n => `
    <div class="note-item" data-id="${n.id}">
      <div class="note-header">
        <span class="note-date">${n.date}</span>
        <button class="delete-note-btn" title="Delete Note">🗑️</button>
      </div>
      <div class="note-content">${n.note}</div>
    </div>
  `).join('');

  // attach delete handlers
  document.querySelectorAll('.delete-note-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const item = btn.closest('.note-item');
      const noteId = item.dataset.id;
      if (!confirm('Delete this note?')) return;

      const res = await fetch('api_doctor/delete_note.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
          note_id: noteId,
          patient_id: patientId
        })
      });
      const json = await res.json();
      if (json.success) {
        // refresh notes and activity
        await loadNotes(patientId);
        await loadActivityFeed();
      } else {
        alert('Failed to delete note: ' + json.error);
      }
    });
  });
}

async function addNote(id, note) {
  await fetch('api_doctor/add_note.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ patient_id:id, note })
    
  });
  await loadActivityFeed();
}

// keep loadDiagnoses as-is, but render file link if exists:
async function loadDiagnoses(id) {
  const list = await fetch(`api_doctor/get_diagnoses.php?patient_id=${id}`)
                     .then(r => r.json());
  console.log('Diagnoses data:', list);

  const baseURL = window.location.origin 
                + '/hospital/Hospital-Management-Html-master/';

  // 1) Build the HTML, including a delete button
  const html = list.map(d => {
    const fileBlock = d.file_path
      ? `<div class="diagnosis-file">
            <a href="${baseURL + d.file_path}" target="_blank" rel="noopener">
              📎 ${d.attachment_type || 'View Attachment'}
            </a>
         </div>`
      : '';

    return `
      <div class="diagnosis-item" data-id="${d.id}">
        <div class="diagnosis-header">
          <h4>${d.title}</h4>
          <span class="diagnosis-date">${d.date}</span>
          <button class="delete-diagnosis" title="Delete">🗑️</button>
        </div>
        <div class="diagnosis-desc">${d.description}</div>
        <div class="diagnosis-severity severity-${d.severity}">
          ${d.severity}
        </div>
        ${fileBlock}
      </div>
    `;
  }).join('');

  el('diagnosisList').innerHTML = html;

  // 2) Attach click handlers to each delete button
  document.querySelectorAll('.delete-diagnosis').forEach(btn => {
    btn.addEventListener('click', async () => {
      const item = btn.closest('.diagnosis-item');
      const did  = item.dataset.id;
      if (!confirm('Are you sure you want to delete this diagnosis?')) return;

      const res = await fetch('api_doctor/delete_diagnosis.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ diagnosis_id: did })
      });
      const json = await res.json();
      if (json.success) {
        // refresh the list
        await loadDiagnoses(id);
        await loadActivityFeed();
      } else {
        alert('Delete failed: ' + json.error);
      }
    });
  });
}







async function refreshAppointmentsUI() {
  const apps = await getAppointments();
  // dashboard list & chart:
  renderAppointmentsList(apps);
  renderAppointmentsChart(apps);
  // table view:
  renderMyAppointmentsTable(apps);
  // today’s counter:
  const d = new Date();
  const todayStr = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
  const count = apps.filter(a => a.appointment_time.slice(0,10) === todayStr).length;
  const elCount = document.getElementById('todayAppointmentsCount');
  if (elCount) elCount.textContent = count;
}



// Medical records stub (implement as needed)
// After loadDiagnoses and loadNotes, also:


async function loadMedicalRecords(id) {
  const recs = await fetch(`api_doctor/get_medical_records.php?patient_id=${id}`)
                     .then(r => r.json());

  console.log('Received records count:', recs.length);
  console.log('Records data:', recs);

  // Build HTML as before
  const baseURL = window.location.origin + '/hospital/Hospital-Management-Html-master/';
  const html = recs.map(r => {
    const dt = new Date(r.record_date).toLocaleString();
    const fileBlock = r.file_path
      ? `<div class="record-file">
           <a href="${baseURL + r.file_path}" target="_blank">
             📎 ${r.attachment_type || 'Download'}
           </a>
         </div>`
      : '';
    return `
      <div class="record-item">
        <div class="record-header">${dt} — ${r.record_type}</div>
        <p>${r.description || ''}</p>
        ${fileBlock}
      </div>
    `;
  }).join('');

  console.log('Generated HTML:', html);

  const listEl = document.getElementById('medicalRecordsList');
  listEl.innerHTML = html;
  console.log('Rendered children count:', listEl.childElementCount);
}


// loadMedicalDocuments
async function loadMedicalDocuments(patientId) {
  if (!patientId) return;

  const docs = await fetch(
    `api_doctor/get_medical_documents.php?patient_id=${patientId}`
  ).then(r => r.json());

  const baseURL = window.location.origin
                + '/hospital/Hospital-Management-Html-master/';
  const html = docs.map(d => `
    <div class="record-item">
      <div class="record-header">
        <small>
          ${new Date(d.uploaded_at).toLocaleString()} — ${d.doc_type}
        </small>
        <div class="uploader-info">
          Uploaded by: ${d.uploader_name} (ID: ${d.uploader_id})
        </div>
      </div>
      <div class="record-desc">${d.description || ''}</div>
      <div class="record-file">
        <a href="${baseURL + d.file_path}"
           target="_blank" rel="noopener">
           📎 Download
        </a>
      </div>
    </div>
  `).join('');

  document.getElementById('medicalRecordsList').innerHTML = html;
}


// ➋ If the form exists, listen for its submit
if (uploadForm) {
  uploadForm.addEventListener('submit', async function(e) {
    e.preventDefault();             // stop the normal page reload
    uploadFeedback.textContent = ''; // clear old messages

    // ➌ Build a FormData object around the form fields + file
    const data = new FormData(uploadForm);

    try {
      // ➍ Send the POST to your new API endpoint
      const res = await fetch(uploadForm.action, {
        method: 'POST',
        body: data
      });
      const jr = await res.json();

      if (jr.success) {
        // ✅ On success, show a message...
        uploadFeedback.style.color = 'green';
        uploadFeedback.textContent = '✅ Uploaded successfully!';
        // …and reload the medical records below:
        if (currentPatientId) {
          await loadMedicalDocuments(currentPatientId);
          await loadActivityFeed();
        }
      } else {
        // ❌ On error, show the server’s message
        uploadFeedback.style.color = 'red';
        uploadFeedback.textContent = 'Error: ' + jr.error;
      }
    } catch (err) {
      uploadFeedback.style.color = 'red';
      uploadFeedback.textContent = 'Upload failed: ' + err.message;
    }
  });
}




// —— Profile, Patients, Activity API calls ————————————————————

const getPatients      = () => fetch('api_doctor/get_patients.php').then(r=>r.json());
const getPatient       = id => fetch(`api_doctor/get_patient.php?id=${id}`).then(r=>r.json());
const getActivity      = () => fetch('api_doctor/get_activity.php').then(r=>r.json());
const getLatestDiagnosis = async id => {
  const list = await fetch(`api_doctor/get_diagnoses.php?patient_id=${id}`)
                       .then(r=>r.json());
  return list.sort((a,b)=>new Date(b.date)-new Date(a.date))[0] || null;
};


