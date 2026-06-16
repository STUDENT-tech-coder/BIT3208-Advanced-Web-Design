<?php
require_once 'includes/config.php';
requireLogin();

$username = $_SESSION['user'];
$role     = $_SESSION['role'];

$access_error = isset($_GET['error']) && $_GET['error'] === 'access_denied';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=IBM+Plex+Mono:wght@400;500&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

<header>
  <div class="header-inner">
    <div class="brand">
      <div class="brand-name">Student<span>MS</span></div>
      <div class="brand-sub">Academic Records System</div>
    </div>
    <div style="display:flex; align-items:center; gap:1.5rem;">
      <span class="role-badge role-<?= $role ?>"><?= strtoupper($role) ?></span>
      <span class="header-user">👤 <?= $username ?></span>
      <?php if (isSuperAdmin()): ?>
        <a href="manage_users.php" class="header-nav-link">Manage Users</a>
      <?php endif; ?>
      <a href="logout.php" class="header-nav-link">Logout</a>
    </div>
  </div>
</header>

<main>

  <?php if ($access_error): ?>
    <div style="max-width:1100px;margin:0 auto 1rem;padding:0 1.5rem;">
      <div class="alert alert-error">⛔ Access denied. Only Superadmin can access that page.</div>
    </div>
  <?php endif; ?>

  <?php if (isStudent()): ?>
  <!-- -->
  <?php
    $u_email  = $_SESSION['user'] . '@example.com';
    $u_name   = $_SESSION['user'];

    // Try matching by email first, then by name
    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? OR LOWER(name) LIKE ?");
    $like = '%' . strtolower($u_name) . '%';
    $stmt->bind_param('ss', $u_email, $like);
    $stmt->execute();
    $my_record = $stmt->get_result()->fetch_assoc();
  ?>

  <div style="max-width:700px; margin:0 auto; padding:0 1.5rem;">

    <div style="margin-bottom:1.5rem;">
      <div style="font-family:'Playfair Display',serif; font-size:1.6rem; font-weight:900;">
        Welcome, <?= htmlspecialchars(ucfirst($username)) ?>
      </div>
      <div style="font-family:'IBM Plex Mono',monospace; font-size:0.7rem; color:var(--muted); letter-spacing:2px; text-transform:uppercase; margin-top:4px;">
        Your Academic Record
      </div>
    </div>

    <?php if ($my_record): ?>

      <div style="background:var(--ink); color:var(--paper); padding:2rem; margin-bottom:1.5rem; border-left:6px solid var(--accent);">
        <div style="font-family:'IBM Plex Mono',monospace; font-size:0.65rem; letter-spacing:3px; text-transform:uppercase; color:rgba(245,240,232,0.5); margin-bottom:0.5rem;">Current GPA</div>
        <div style="font-family:'Playfair Display',serif; font-size:4rem; font-weight:900; line-height:1;
          color:<?= $my_record['gpa'] >= 3.5 ? '#86efac' : ($my_record['gpa'] >= 2.5 ? '#fcd34d' : '#fca5a5') ?>">
          <?= $my_record['gpa'] != null ? number_format($my_record['gpa'], 2) : '—' ?>
        </div>
        <div style="font-family:'IBM Plex Mono',monospace; font-size:0.75rem; color:rgba(245,240,232,0.5); margin-top:0.5rem;">
          <?php
            if ($my_record['gpa'] >= 3.5)      echo 'Distinction — Excellent performance';
            elseif ($my_record['gpa'] >= 3.0)  echo 'Credit — Good performance';
            elseif ($my_record['gpa'] >= 2.5)  echo 'Pass — Average performance';
            elseif ($my_record['gpa'] != null) echo 'Below average — Needs improvement';
            else echo 'GPA not yet assigned';
          ?>
        </div>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
        <div class="stat-card" style="border-color:var(--accent)">
          <div style="font-family:'IBM Plex Mono',monospace; font-size:0.62rem; letter-spacing:2px; text-transform:uppercase; color:var(--muted); margin-bottom:0.4rem;">Full Name</div>
          <div style="font-weight:600; font-size:0.95rem;"><?= htmlspecialchars($my_record['name']) ?></div>
        </div>
        <div class="stat-card" style="border-color:var(--accent)">
          <div style="font-family:'IBM Plex Mono',monospace; font-size:0.62rem; letter-spacing:2px; text-transform:uppercase; color:var(--muted); margin-bottom:0.4rem;">Email</div>
          <div style="font-family:'IBM Plex Mono',monospace; font-size:0.82rem; color:var(--muted);"><?= htmlspecialchars($my_record['email']) ?></div>
        </div>
        <div class="stat-card" style="border-color:#2f9e44">
          <div style="font-family:'IBM Plex Mono',monospace; font-size:0.62rem; letter-spacing:2px; text-transform:uppercase; color:var(--muted); margin-bottom:0.4rem;">Course</div>
          <div style="font-weight:600; font-size:0.95rem;"><?= htmlspecialchars($my_record['course']) ?></div>
        </div>
        <div class="stat-card" style="border-color:#b8860b">
          <div style="font-family:'IBM Plex Mono',monospace; font-size:0.62rem; letter-spacing:2px; text-transform:uppercase; color:var(--muted); margin-bottom:0.4rem;">Year of Study</div>
          <div style="font-weight:600; font-size:0.95rem;">Year <?= $my_record['year'] ?></div>
        </div>
      </div>

      <div class="alert alert-info">
        📋 This is a read-only view. Contact your administrator to update your records.
      </div>

    <?php else: ?>
      <div class="alert alert-error">
        ⚠️ No academic record found for your account. Please contact your administrator.
      </div>
    <?php endif; ?>

  </div>

  <?php else: ?>
  <!-- -->

  <div class="stats">
    <div class="stat-card">
      <div class="num" id="stat-total">—</div>
      <div class="lbl">Total Students</div>
    </div>
    <div class="stat-card" style="border-color:#2f9e44">
      <div class="num" id="stat-avg" style="color:#2f9e44">—</div>
      <div class="lbl">Average GPA</div>
    </div>
    <div class="stat-card" style="border-color:#b8860b">
      <div class="num" id="stat-courses" style="color:#b8860b">—</div>
      <div class="lbl">Courses</div>
    </div>
  </div>

  <div class="card">
    <div class="form-title" id="form-title">New Record</div>
    <div class="form-subtitle" id="form-sub">Add Student Entry</div>

    <input type="hidden" id="edit-id"/>

    <div class="form-grid">
      <div class="field">
        <label>Full Name *</label>
        <input type="text" id="name" placeholder="e.g. Alice Wanjiku"/>
      </div>
      <div class="field">
        <label>Email Address *</label>
        <input type="email" id="email" placeholder="alice@example.com"/>
      </div>
      <div class="field">
        <label>Course *</label>
        <input type="text" id="course" placeholder="e.g. Computer Science"/>
      </div>
      <div class="field">
        <label>Year of Study *</label>
        <select id="year">
          <option value="">— Select Year —</option>
          <option value="1">Year 1</option>
          <option value="2">Year 2</option>
          <option value="3">Year 3</option>
          <option value="4">Year 4</option>
        </select>
      </div>
      <div class="field">
        <label>GPA (optional)</label>
        <input type="number" id="gpa" placeholder="e.g. 3.75" min="0" max="4" step="0.01"/>
      </div>
    </div>

    <div class="btn-row">
      <button class="btn-auth" onclick="submitForm()">
        <span id="btn-label">Add Student</span>
      </button>
      <button class="btn-cancel" onclick="resetForm()">Cancel</button>
    </div>
  </div>

  <div class="card">
    <div class="table-toolbar">
      <div class="form-title" style="margin:0">Student Records</div>
      <input type="text" id="search" placeholder="Search records..." oninput="filterTable()"/>
    </div>

    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Email</th>
            <th>Course</th><th>Year</th><th>GPA</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="7"><div class="spinner"></div></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const USER_ROLE = '<?= $role ?>';
    const API       = 'api.php';
    let all = [], editingId = null;

    document.addEventListener('DOMContentLoaded', load);

    async function load() {
      try {
        const res  = await fetch(API);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        all = json.data;
        render(all);
        stats(all);
      } catch (e) {
        toast(e.message, 'error');
        document.getElementById('tbody').innerHTML =
          `<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--muted)">${e.message}</td></tr>`;
      }
    }

    function render(data) {
      const tb = document.getElementById('tbody');
      if (!data.length) {
        tb.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--muted)">No records found</td></tr>`;
        return;
      }
      tb.innerHTML = data.map((s, i) => `
        <tr style="animation-delay:${i*35}ms">
          <td class="mono">${String(i+1).padStart(2,'0')}</td>
          <td style="font-weight:600">${esc(s.name)}</td>
          <td class="mono">${esc(s.email)}</td>
          <td><span class="course-tag">${esc(s.course)}</span></td>
          <td class="mono">Y${s.year}</td>
          <td class="${gpaClass(s.gpa)}">${s.gpa != null ? parseFloat(s.gpa).toFixed(2) : '—'}</td>
          <td>
            <div style="display:flex;gap:0.4rem;">
              <button class="act-btn act-edit" onclick="startEdit(${s.id})">Edit</button>
              ${USER_ROLE === 'superadmin'
                ? `<button class="act-btn act-del" onclick="del(${s.id},'${esc(s.name)}')">Del</button>`
                : `<button class="act-btn" style="opacity:0.3;cursor:not-allowed" disabled title="Only Superadmin can delete">Del</button>`
              }
            </div>
          </td>
        </tr>`).join('');
    }

    function stats(data) {
      document.getElementById('stat-total').textContent = data.length;
      const gpas = data.filter(s => s.gpa != null).map(s => parseFloat(s.gpa));
      document.getElementById('stat-avg').textContent = gpas.length
        ? (gpas.reduce((a,b)=>a+b,0)/gpas.length).toFixed(2) : '—';
      document.getElementById('stat-courses').textContent = new Set(data.map(s=>s.course)).size;
    }

    function filterTable() {
      const q = document.getElementById('search').value.toLowerCase();
      render(all.filter(s =>
        s.name.toLowerCase().includes(q) ||
        s.email.toLowerCase().includes(q) ||
        s.course.toLowerCase().includes(q)
      ));
    }

    async function submitForm() {
      const name   = document.getElementById('name').value.trim();
      const email  = document.getElementById('email').value.trim();
      const course = document.getElementById('course').value.trim();
      const year   = document.getElementById('year').value;
      const gpa    = document.getElementById('gpa').value;

      let ok = true;
      ['name','email','course','year'].forEach(id => {
        const el = document.getElementById(id);
        if (!el.value.trim()) { el.style.borderColor='#c0392b'; ok=false; }
        else el.style.borderColor='';
      });
      if (!ok) return toast('Please fill in all required fields.', 'error');
      if (gpa && (parseFloat(gpa) < 0 || parseFloat(gpa) > 4))
        return toast('GPA must be 0.00 – 4.00.', 'error');

      const payload = { name, email, course, year: parseInt(year), gpa: gpa ? parseFloat(gpa) : '' };
      if (editingId) payload.id = editingId;

      try {
        const res  = await fetch(API, {
          method: editingId ? 'PUT' : 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        toast(json.message, 'success');
        resetForm();
        load();
      } catch (e) { toast(e.message, 'error'); }
    }

    async function startEdit(id) {
      try {
        const res  = await fetch(`${API}?id=${id}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        const s = json.data;
        editingId = id;
        document.getElementById('name').value   = s.name;
        document.getElementById('email').value  = s.email;
        document.getElementById('course').value = s.course;
        document.getElementById('year').value   = s.year;
        document.getElementById('gpa').value    = s.gpa ?? '';
        document.getElementById('form-title').textContent = '✏️ Edit Record';
        document.getElementById('form-sub').textContent   = 'Update Student Entry';
        document.getElementById('btn-label').textContent  = 'Update Student';
        document.querySelector('.card').scrollIntoView({ behavior:'smooth' });
      } catch (e) { toast(e.message, 'error'); }
    }

    async function del(id, name) {
      if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;
      try {
        const res  = await fetch(`${API}?id=${id}`, { method:'DELETE' });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        toast(json.message, 'success');
        load();
      } catch (e) { toast(e.message, 'error'); }
    }

    function resetForm() {
      editingId = null;
      ['name','email','course','gpa'].forEach(id => {
        document.getElementById(id).value = '';
        document.getElementById(id).style.borderColor = '';
      });
      document.getElementById('year').value = '';
      document.getElementById('form-title').textContent = 'New Record';
      document.getElementById('form-sub').textContent   = 'Add Student Entry';
      document.getElementById('btn-label').textContent  = 'Add Student';
    }

    function toast(msg, type='success') {
      const t = document.getElementById('toast');
      t.textContent = (type==='success'?'✓ ':'✗ ') + msg;
      t.className   = `show ${type}`;
      setTimeout(() => t.className = '', 3500);
    }

    function gpaClass(g) {
      if (g == null) return 'mono';
      return parseFloat(g) >= 3.5 ? 'gpa-high' : parseFloat(g) >= 2.5 ? 'gpa-mid' : 'gpa-low';
    }

    function esc(s) {
      return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
  </script>

  <?php endif; ?>

</main>

<div id="toast"></div>

</body>
</html>