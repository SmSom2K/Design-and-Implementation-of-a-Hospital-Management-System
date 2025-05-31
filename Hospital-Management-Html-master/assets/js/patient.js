// assets/js/patient.js

// ——— DOM helpers ——————————————————
const el = id => document.getElementById(id);


// ——— Elements by ID —————————————————
const profileName       = el('profileName');
const profileUsername   = el('profileUsername');
const profileId         = el('profileId');
const profileAge        = el('profileAge');
const profileGender     = el('profileGender');
const profileBlood      = el('profileBlood');

const recentActivityList = el('recentActivityList');
const diagnosesList      = el('diagnosesList');

const pendingTable       = el('pendingTable');
const acceptedTable      = el('acceptedTable');


// ——— Simple API calls —————————————————
const getProfileData    = () => fetch('api_patient/get_profile.php').then(r => r.json());
const getActivityData   = () => fetch('api_patient/get_activity.php').then(r => r.json());
const getDiagnosesData  = () => fetch('api_patient/get_diagnoses.php').then(r => r.json());
const getMyAppointments = () => fetch('api_patient/get_my_appointments.php').then(r => r.json());
const getMedicalRecordsData = () => fetch('api_patient/get_medical_records.php').then(r=>r.json());


window.addEventListener('DOMContentLoaded', async () => {
   console.log('▶️ modal:', el('requestApptModal'));
  console.log('▶️ openBtn:', el('openRequestAppt'));
  console.log('▶️ form:',   el('requestApptForm'));
  // ── 1) Sidebar nav ─────────────────────────────────
  document.querySelectorAll('.sidebar-nav a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      // deactivate all
      document.querySelectorAll('.sidebar-nav li').forEach(li => li.classList.remove('active'));
      document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));

      // activate this
      link.parentElement.classList.add('active');
      const name = link.getAttribute('href').substring(1);
      // try both "name-section" and "name"
      const target = el(name + '-section') || el(name);
      if (target) target.classList.add('active');
    });
  });

  // ── 2) Mobile sidebar toggle ─────────────────────────
  const sidebar = document.querySelector('.sidebar');
  el('openSidebar')?.addEventListener('click',  () => sidebar.classList.add('show'));
  el('closeSidebar')?.addEventListener('click', () => sidebar.classList.remove('show'));

  // ── 3) Medical-history tab switching ─────────────────
  document.querySelectorAll('#medical-history-section .tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#medical-history-section .tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('#medical-history-section .tab-content').forEach(c => c.style.display = 'none');
      btn.classList.add('active');
      el(btn.dataset.tab).style.display = 'block';
    });
  });
  // show first medical-history tab
  document.querySelectorAll('#medical-history-section .tab-content').forEach(c => c.style.display = 'none');
  const firstMedBtn = document.querySelector('#medical-history-section .tab-btn');
  if (firstMedBtn) {
    firstMedBtn.classList.add('active');
    el(firstMedBtn.dataset.tab).style.display = 'block';
  }

  // ── 4) Appointments tab switching ───────────────────
  document.querySelectorAll('#appointments-section .tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#appointments-section .tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('#appointments-section .tab-content').forEach(p => p.style.display = 'none');
      btn.classList.add('active');
      el(btn.dataset.tab).style.display = 'block';
    });
  });
  // show first appointments tab
  document.querySelectorAll('#appointments-section .tab-content').forEach(p => p.style.display = 'none');
  const firstAppBtn = document.querySelector('#appointments-section .tab-btn');
  if (firstAppBtn) {
    firstAppBtn.classList.add('active');
    el(firstAppBtn.dataset.tab).style.display = 'block';
  }

  // ── 5) Display current date (if you have an element with id="currentDate") ─────────────────
  if (el('currentDate')) {
    el('currentDate').textContent = new Date().toLocaleDateString();
  }

  // ── 6) Initial data loads ────────────────────────────
  await loadProfile();
  await loadRecentActivity();
  await loadDiagnoses();
  await loadAppointments();
  await loadMedicalRecords();


    // ── 7) Request Appointment modal logic ──────────────────────────

  // grab the elements
  const modal    = el('requestApptModal');
  const btnOpen  = el('openRequestAppt');
  const btnClose = modal.querySelector('.close');

  // helper to fill doctor <select>
  async function loadDoctorOptions() {
    const sel = el('doctorSelect');
    const res = await fetch('api_patient/get_doctors.php');
    const list = res.ok ? await res.json() : [];
    sel.innerHTML = list.length
      ? list.map(d =>
          `<option value="${d.id}">
             Dr. ${d.name} — Dept: ${d.department} (ID: ${d.id})
           </option>`
        ).join('\n')
      : '<option value="">No doctors available</option>';
  }

  // open the modal
  btnOpen.addEventListener('click', async () => {
    await loadDoctorOptions();
    modal.style.display = 'flex';
  });

  // close by “×”
  btnClose.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // close by clicking outside the box
  window.addEventListener('click', e => {
    if (e.target === modal) modal.style.display = 'none';
  });

  // handle the form submit
  el('requestApptForm').addEventListener('submit', async e => {
    e.preventDefault();
    const fb   = el('requestApptFeedback');
    const data = new FormData(e.target);
    data.set('doctor_id',        el('doctorSelect').value);
    data.set('appointment_time', el('apptDateTime').value);
    data.set('purpose',          el('apptPurpose').value);

    const res = await fetch('api_patient/request_appointment.php', {
      method: 'POST', body: data
    });
    const jr = await res.json();

    if (jr.success) {
      fb.style.color = 'green';
      fb.textContent = '✔ Request sent!';
      await loadAppointments();
      setTimeout(() => modal.style.display = 'none', 800);
    } else {
      fb.style.color = 'red';
      fb.textContent = 'Error: ' + jr.error;
    }
  });
});

// inside your DOMContentLoaded handler, or just after you define el():
const logoutBtn = el('logoutBtn');
if (logoutBtn) {
  logoutBtn.addEventListener('click', e => {
    e.preventDefault();                // stop the “#” from jumping
    window.location.href = 'logout.php';  // go to our logout script
  });
}

// ——— Load & render patient profile —————————————
async function loadProfile() {
  const p = await getProfileData();
  if (!p) return;
   // new: grab the sidebar name element
  const patientNameEl = el('patientName');

  // set it
  patientNameEl.textContent = p.name;

  profileUsername.textContent = `Username: ${p.username}`;
  profileName.textContent     = p.name;
  profileEmail.textContent = p.email
  ? `Email: ${p.email}` 
  : 'Email: No email provided';

  profileId.textContent       = `Patient ID: ${p.user_id}`;
  profileAge.textContent      = `${p.age} years`;
  profileGender.textContent   = capitalize(p.gender);
  profileBlood.textContent    = p.blood_type;
  profilePhone.textContent    = p.phone;
  profileStatus.textContent   = p.status;
}


// ——— Load & render recent activity ————————————
async function loadRecentActivity() {
  const logs = await getActivityData();
  recentActivityList.innerHTML = logs.length
    ? logs.map(l => `
        <div class="timeline-item">
          <small>${new Date(l.timestamp).toLocaleString()}</small>
          <p>${l.description}</p>
        </div>
      `).join('')
    : '<p>No recent activity found.</p>';
}



// ——— Load & render diagnoses (with attachments) —————
async function loadDiagnoses() {
  const list = await getDiagnosesData();
  diagnosesList.innerHTML = '';

  for (let d of list) {
    const card = document.createElement('div');
    card.className = 'medical-record-item';
    card.innerHTML = `
      <div class="medical-record-header">
        <div class="field">
          <strong>Title:</strong> <span>${d.title}</span>
        </div>
        <div class="field">
          <strong>Date:</strong> <span>${new Date(d.date).toLocaleDateString()}</span>
        </div>
      </div>

      <div class="field">
        <strong>Time Recorded:</strong>
        <span>${new Date(d.created_at).toLocaleString()}</span>
      </div>

      <div class="field">
        <strong>Sent By:</strong>
        <span>${d.doctor_name} (ID ${d.doctor_id})</span>
      </div>

      <div class="field">
        <strong>Description:</strong>
        <span>${d.description}</span>
      </div>

      <div class="field">
        <strong>Severity:</strong>
        <span class="diagnosis-severity severity-${d.severity}">
          ${capitalize(d.severity)}
        </span>
      </div>

      <div class="field">
        <strong>Attachments:</strong>
        <div class="doc-list" id="diag-docs-${d.id}">
          <em>Loading attachments…</em>
        </div>
      </div>
    `;
    diagnosesList.append(card);

    // fetch & render attachments
    const docs = await getDocuments('diagnosis', d.id);
    const container = document.getElementById(`diag-docs-${d.id}`);
    if (docs.length) {
      container.innerHTML = docs.map(doc => {
        const filename = doc.file_path.split('/').pop();
        const label    = doc.doc_type || filename;
        const date     = new Date(doc.uploaded_at).toLocaleString();
        const href     = doc.file_path.startsWith('http')
          ? doc.file_path
          : `/${doc.file_path.replace(/^\/+/, '')}`;
        return `<a href="${href}" target="_blank" title="${filename}">
                  ${label} — ${date}
                </a>`;
      }).join('<br>');
    } else {
      container.innerHTML = '<span>No attachments</span>';
    }
  }
}


// ——— Load & render medical records (with attachments) —————
async function loadMedicalRecords() {
  const recs = await getMedicalRecordsData();
  const container = el('medicalRecordsList');
  container.innerHTML = '';

  for (let r of recs) {
    const card = document.createElement('div');
    card.className = 'medical-record-item';
    card.innerHTML = `
      <div class="medical-record-header">
        <div class="field">
          <strong>Type:</strong> <span>${r.record_type}</span>
        </div>
        <div class="field">
          <strong>Date:</strong> <span>${new Date(r.record_date).toLocaleDateString()}</span>
        </div>
      </div>

      <div class="field">
        <strong>Exact Time:</strong>
        <span>${new Date(r.record_date).toLocaleString()}</span>
      </div>

      <div class="field">
        <strong>Description:</strong>
        <span>${r.record_desc}</span>
      </div>

      <div class="field">
        <strong>Attachments:</strong>
        <div class="doc-list" id="rec-docs-${r.id}">
          <em>Loading attachments…</em>
        </div>
      </div>
    `;
    container.append(card);

    // fetch & render attachments
    const docs = await getDocuments('medical_record', r.id);
    const docContainer = document.getElementById(`rec-docs-${r.id}`);
    if (docs.length) {
      docContainer.innerHTML = docs.map(doc => {
        const filename = doc.file_path.split('/').pop();
        const label    = doc.doc_type || filename;
        const date     = new Date(doc.uploaded_at).toLocaleString();
        const href     = doc.file_path.startsWith('http')
          ? doc.file_path
          : `/${doc.file_path.replace(/^\/+/, '')}`;
        return `<a href="${href}" target="_blank" title="${filename}">
                  ${label} — ${date}
                </a>`;
      }).join('<br>');
    } else {
      docContainer.innerHTML = '<span>No attachments</span>';
    }
  }
}





// ——— Load & render medical records (new) —————
async function getDocuments(type, id) {
  try {
    const res = await fetch(`api_patient/get_documents.php?type=${type}&id=${id}`);
    if (!res.ok) return [];
    const text = await res.text();
    return text ? JSON.parse(text) : [];
  } catch (e) {
    console.error('Error fetching documents', e);
    return [];
  }
}




// ——— Load & render My Appointments ————————————
async function loadAppointments() {
  const apps = await getMyAppointments();

  const pending  = apps.filter(a => a.status === 'pending');
  const accepted = apps.filter(a => a.status === 'accepted');
  const declined = apps.filter(a => a.status === 'declined');

  // Pending
  pendingTable.innerHTML = pending.length
    ? pending.map(a => `
        <tr>
          <td>${new Date(a.appointment_time).toLocaleString()}</td>
          <td>${a.doctor_name}</td>
          <td>${a.purpose}</td>
          <td><!-- maybe a “Cancel” or “Reschedule” button --></td>
        </tr>
      `).join('')
    : '<tr><td colspan="4">No pending appointments.</td></tr>';

  // Accepted
  acceptedTable.innerHTML = accepted.length
    ? accepted.map(a => `
        <tr>
          <td>${new Date(a.appointment_time).toLocaleString()}</td>
          <td>${a.doctor_name}</td>
          <td>${a.purpose}</td>
          <td>Accepted</td>
        </tr>
      `).join('')
    : '<tr><td colspan="4">No confirmed appointments.</td></tr>';

  // Declined
  const declinedTable = el('declinedTable');
  declinedTable.innerHTML = declined.length
    ? declined.map(a => `
        <tr>
          <td>${new Date(a.appointment_time).toLocaleString()}</td>
          <td>${a.doctor_name}</td>
          <td>${a.purpose}</td>
          <td>Declined</td>
        </tr>
      `).join('')
    : '<tr><td colspan="4">No declined appointments.</td></tr>';
}




// ——— Utility —————————————————————
function capitalize(str = '') {
  return str.charAt(0).toUpperCase() + str.slice(1);
}
