// Mock Database using localStorage
// This file provides functions to manage users, patients, doctors, and medical records

// Users
function getUsers() {
    const users = localStorage.getItem('users');
    return users ? JSON.parse(users) : initializeUsers();
}

function setUsers(users) {
    localStorage.setItem('users', JSON.stringify(users));
}

function getUserById(id) {
    const users = getUsers();
    return users.find(user => user.id === id);
}

function getUserByUsername(username) {
    const users = getUsers();
    return users.find(user => user.username === username);
}

// Doctors
function getDoctors() {
    const doctors = localStorage.getItem('doctors');
    return doctors ? JSON.parse(doctors) : initializeDoctors();
}

function setDoctors(doctors) {
    localStorage.setItem('doctors', JSON.stringify(doctors));
}

function getDoctorById(id) {
    const doctors = getDoctors();
    return doctors.find(doctor => doctor.id === id);
}

function addDoctor(doctor) {
    const doctors = getDoctors();
    const users = getUsers();
    
    // Generate ID
    const doctorId = 'D-' + Math.floor(Math.random() * 10000);
    doctor.id = doctorId;
    
    // Add doctor to doctors list
    doctors.push(doctor);
    
    // Add doctor to users list
    users.push({
        id: doctorId,
        username: doctor.username,
        password: doctor.password,
        role: 'doctor'
    });
    
    // Save changes
    setDoctors(doctors);
    setUsers(users);
    
    return doctorId;
}

function updateDoctor(doctorId, updates) {
    const doctors = getDoctors();
    const users = getUsers();
    
    // Find doctor index
    const doctorIndex = doctors.findIndex(doctor => doctor.id === doctorId);
    const userIndex = users.findIndex(user => user.id === doctorId);
    
    if (doctorIndex !== -1 && userIndex !== -1) {
        // Update doctor
        doctors[doctorIndex] = { ...doctors[doctorIndex], ...updates };
        
        // Update user if username or password changed
        if (updates.username || updates.password) {
            users[userIndex] = { 
                ...users[userIndex], 
                username: updates.username || users[userIndex].username,
                password: updates.password || users[userIndex].password
            };
        }
        
        // Save changes
        setDoctors(doctors);
        setUsers(users);
        
        return true;
    }
    
    return false;
}

function deleteDoctor(doctorId) {
    const doctors = getDoctors();
    const users = getUsers();
    const patients = getPatients();
    const assignments = getAssignments();
    
    // Remove doctor from doctors list
    const updatedDoctors = doctors.filter(doctor => doctor.id !== doctorId);
    
    // Remove doctor from users list
    const updatedUsers = users.filter(user => user.id !== doctorId);
    
    // Unassign patients from this doctor
    const updatedAssignments = assignments.filter(assignment => assignment.doctorId !== doctorId);
    
    // Save changes
    setDoctors(updatedDoctors);
    setUsers(updatedUsers);
    setAssignments(updatedAssignments);
    
    return true;
}

// Patients
function getPatients() {
    const patients = localStorage.getItem('patients');
    return patients ? JSON.parse(patients) : initializePatients();
}

function setPatients(patients) {
    localStorage.setItem('patients', JSON.stringify(patients));
}

function getPatientById(id) {
    const patients = getPatients();
    return patients.find(patient => patient.id === id);
}

function addPatient(patient) {
    const patients = getPatients();
    const users = getUsers();
    
    // Generate ID
    const patientId = 'P-' + Math.floor(Math.random() * 10000);
    patient.id = patientId;
    
    // Add patient to patients list
    patients.push(patient);
    
    // Add patient to users list
    users.push({
        id: patientId,
        username: patient.username,
        password: patient.password,
        role: 'patient'
    });
    
    // Save changes
    setPatients(patients);
    setUsers(users);
    
    return patientId;
}

function updatePatient(patientId, updates) {
    const patients = getPatients();
    const users = getUsers();
    
    // Find patient index
    const patientIndex = patients.findIndex(patient => patient.id === patientId);
    const userIndex = users.findIndex(user => user.id === patientId);
    
    if (patientIndex !== -1 && userIndex !== -1) {
        // Update patient
        patients[patientIndex] = { ...patients[patientIndex], ...updates };
        
        // Update user if username or password changed
        if (updates.username || updates.password) {
            users[userIndex] = { 
                ...users[userIndex], 
                username: updates.username || users[userIndex].username,
                password: updates.password || users[userIndex].password
            };
        }
        
        // Save changes
        setPatients(patients);
        setUsers(users);
        
        return true;
    }
    
    return false;
}

function deletePatient(patientId) {
    const patients = getPatients();
    const users = getUsers();
    const assignments = getAssignments();
    const medicalRecords = getMedicalRecords();
    
    // Remove patient from patients list
    const updatedPatients = patients.filter(patient => patient.id !== patientId);
    
    // Remove patient from users list
    const updatedUsers = users.filter(user => user.id !== patientId);
    
    // Remove patient assignments
    const updatedAssignments = assignments.filter(assignment => assignment.patientId !== patientId);
    
    // Remove patient medical records
    const updatedMedicalRecords = medicalRecords.filter(record => record.patientId !== patientId);
    
    // Save changes
    setPatients(updatedPatients);
    setUsers(updatedUsers);
    setAssignments(updatedAssignments);
    setMedicalRecords(updatedMedicalRecords);
    
    return true;
}

// Assignments (Doctor-Patient relationships)
function getAssignments() {
    const assignments = localStorage.getItem('assignments');
    return assignments ? JSON.parse(assignments) : initializeAssignments();
}

function setAssignments(assignments) {
    localStorage.setItem('assignments', JSON.stringify(assignments));
}

function assignPatientToDoctor(patientId, doctorId) {
    const assignments = getAssignments();
    
    // Check if assignment already exists
    const existingAssignment = assignments.find(a => 
        a.patientId === patientId && a.doctorId === doctorId
    );
    
    if (existingAssignment) {
        return false;
    }
    
    // Add new assignment
    assignments.push({
        id: 'A-' + Math.floor(Math.random() * 10000),
        patientId,
        doctorId,
        dateAssigned: new Date().toISOString()
    });
    
    // Save changes
    setAssignments(assignments);
    
    return true;
}

function removeAssignment(assignmentId) {
    const assignments = getAssignments();
    
    // Remove assignment
    const updatedAssignments = assignments.filter(a => a.id !== assignmentId);
    
    // Save changes
    setAssignments(updatedAssignments);
    
    return true;
}

function getPatientsByDoctorId(doctorId) {
    const assignments = getAssignments();
    const patients = getPatients();
    
    // Get patient IDs assigned to this doctor
    const patientIds = assignments
        .filter(a => a.doctorId === doctorId)
        .map(a => a.patientId);
    
    // Get patient details
    return patients.filter(patient => patientIds.includes(patient.id));
}

function getDoctorByPatientId(patientId) {
    const assignments = getAssignments();
    const doctors = getDoctors();
    
    // Find assignment for this patient
    const assignment = assignments.find(a => a.patientId === patientId);
    
    if (assignment) {
        // Get doctor details
        return doctors.find(doctor => doctor.id === assignment.doctorId);
    }
    
    return null;
}

// Medical Records
function getMedicalRecords() {
    const records = localStorage.getItem('medicalRecords');
    return records ? JSON.parse(records) : initializeMedicalRecords();
}

function setMedicalRecords(records) {
    localStorage.setItem('medicalRecords', JSON.stringify(records));
}

function getMedicalRecordsByPatientId(patientId) {
    const records = getMedicalRecords();
    return records.filter(record => record.patientId === patientId);
}

function addMedicalRecord(record) {
    const records = getMedicalRecords();
    
    // Generate ID
    record.id = 'MR-' + Math.floor(Math.random() * 10000);
    record.date = record.date || new Date().toISOString();
    
    // Add record
    records.push(record);
    
    // Save changes
    setMedicalRecords(records);
    
    return record.id;
}

function updateMedicalRecord(recordId, updates) {
    const records = getMedicalRecords();
    
    // Find record index
    const recordIndex = records.findIndex(record => record.id === recordId);
    
    if (recordIndex !== -1) {
        // Update record
        records[recordIndex] = { ...records[recordIndex], ...updates };
        
        // Save changes
        setMedicalRecords(records);
        
        return true;
    }
    
    return false;
}

function deleteMedicalRecord(recordId) {
    const records = getMedicalRecords();
    
    // Remove record
    const updatedRecords = records.filter(record => record.id !== recordId);
    
    // Save changes
    setMedicalRecords(updatedRecords);
    
    return true;
}

// Diagnosis
function addDiagnosis(patientId, diagnosis) {
    const record = {
        type: 'diagnosis',
        patientId,
        doctorId: getCurrentUser().id,
        ...diagnosis
    };
    
    return addMedicalRecord(record);
}

function getDiagnosesByPatientId(patientId) {
    const records = getMedicalRecords();
    return records.filter(record => 
        record.patientId === patientId && 
        record.type === 'diagnosis'
    );
}

// Doctor Notes
function addDoctorNote(patientId, note) {
    const record = {
        type: 'note',
        patientId,
        doctorId: getCurrentUser().id,
        content: note,
        date: new Date().toISOString()
    };
    
    return addMedicalRecord(record);
}

function getDoctorNotesByPatientId(patientId) {
    const records = getMedicalRecords();
    return records.filter(record => 
        record.patientId === patientId && 
        record.type === 'note'
    );
}

// Initialize sample data
function initializeUsers() {
    const users = [
        { id: 'A-1001', username: 'admin', password: 'admin123', role: 'admin' },
        { id: 'D-1001', username: 'doctor1', password: 'doctor123', role: 'doctor' },
        { id: 'D-1002', username: 'doctor2', password: 'doctor123', role: 'doctor' },
        { id: 'P-1001', username: 'patient1', password: 'patient123', role: 'patient' },
        { id: 'P-1002', username: 'patient2', password: 'patient123', role: 'patient' }
    ];
    
    localStorage.setItem('users', JSON.stringify(users));
    return users;
}

function initializeDoctors() {
    const doctors = [
        {
            id: 'D-1001',
            name: 'Dr. Sofiane Abbou',
            email: 'sofiane.abbou@medicare.dz',
            phone: '+213 550 123 456',
            department: 'cardiology',
            address: '123 Rue Didouche Mourad, Algiers',
            username: 'doctor1',
            status: 'active',
            patientCount: 15
        },
        {
            id: 'D-1002',
            name: 'Dr. Salima Rahmani',
            email: 'salima.rahmani@medicare.dz',
            phone: '+213 551 987 654',
            department: 'neurology',
            address: '45 Avenue Pasteur, Oran',
            username: 'doctor2',
            status: 'active',
            patientCount: 12
        }
    ];
    
    localStorage.setItem('doctors', JSON.stringify(doctors));
    return doctors;
}

function initializePatients() {
    const patients = [
        {
            id: 'P-1001',
            name: 'Yasmine Benali',
            email: 'yasmine.benali@gmail.com',
            phone: '+213 550 123 456',
            dob: '1997-12-05',
            gender: 'female',
            bloodType: 'A+',
            address: '123 Rue Didouche Mourad, Algiers',
            username: 'patient1',
            allergies: 'Penicillin, Peanuts',
            chronicConditions: 'Hypertension',
            status: 'active'
        },
        {
            id: 'P-1002',
            name: 'Ahmed Khelif',
            email: 'ahmed.khelif@gmail.com',
            phone: '+213 551 987 654',
            dob: '1985-08-17',
            gender: 'male',
            bloodType: 'O+',
            address: '78 Boulevard Mohamed Khemisti, Oran',
            username: 'patient2',
            allergies: 'None',
            chronicConditions: 'Diabetes Type 2',
            status: 'active'
        }
    ];
    
    localStorage.setItem('patients', JSON.stringify(patients));
    return patients;
}

function initializeAssignments() {
    const assignments = [
        {
            id: 'A-1001',
            patientId: 'P-1001',
            doctorId: 'D-1001',
            dateAssigned: '2025-01-15T10:30:00.000Z'
        },
        {
            id: 'A-1002',
            patientId: 'P-1002',
            doctorId: 'D-1002',
            dateAssigned: '2025-02-22T14:15:00.000Z'
        }
    ];
    
    localStorage.setItem('assignments', JSON.stringify(assignments));
    return assignments;
}

function initializeMedicalRecords() {
    const today = new Date();
    const lastWeek = new Date(today);
    lastWeek.setDate(today.getDate() - 7);
    const lastMonth = new Date(today);
    lastMonth.setMonth(today.getMonth() - 1);
    
    const records = [
        {
            id: 'MR-1001',
            type: 'diagnosis',
            patientId: 'P-1001',
            doctorId: 'D-1001',
            title: 'Hypertension Diagnosis',
            description: 'Patient presents with consistently elevated blood pressure readings over 140/90 mmHg on multiple occasions.',
            severity: 'moderate',
            treatmentPlan: 'Lifestyle modifications including reduced sodium diet, regular exercise, and stress management. Prescribed Lisinopril 10mg daily.',
            medications: 'Lisinopril 10mg daily, morning',
            followUpDate: '2025-05-30',
            date: lastMonth.toISOString()
        },
        {
            id: 'MR-1002',
            type: 'note',
            patientId: 'P-1001',
            doctorId: 'D-1001',
            content: 'Patient reports feeling better after starting medication. Blood pressure has improved to 135/85 mmHg. Will continue current treatment plan and monitor for any side effects.',
            date: lastWeek.toISOString()
        },
        {
            id: 'MR-1003',
            type: 'diagnosis',
            patientId: 'P-1002',
            doctorId: 'D-1002',
            title: 'Type 2 Diabetes Follow-up',
            description: 'Routine follow-up for diabetes management. Patient reports maintaining diet restrictions but struggling with regular exercise.',
            severity: 'moderate',
            treatmentPlan: 'Continue current medication. Referred to nutritionist for dietary planning. Emphasized importance of regular physical activity.',
            medications: 'Metformin 500mg twice daily, Glipizide 5mg once daily',
            followUpDate: '2025-06-15',
            date: '2025-03-10T09:45:00.000Z'
        }
    ];
    
    localStorage.setItem('medicalRecords', JSON.stringify(records));
    return records;
}

// Check if database needs to be initialized
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all data if needed
    if (!localStorage.getItem('users')) initializeUsers();
    if (!localStorage.getItem('doctors')) initializeDoctors();
    if (!localStorage.getItem('patients')) initializePatients();
    if (!localStorage.getItem('assignments')) initializeAssignments();
    if (!localStorage.getItem('medicalRecords')) initializeMedicalRecords();
});

// Calculate age from date of birth
function calculateAge(dob) {
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

// Format date for display
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}