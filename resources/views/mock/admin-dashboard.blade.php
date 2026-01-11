{{-- Minimal mock admin dashboard for element picking (no real data) --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mock Admin Dashboard</title>
    <style>
        body { font-family: sans-serif; margin: 2em; background: #f8f9fa; }
        .mock-navbar { background: #198754; color: #fff; padding: 0.75em 1.5em; border-radius: 6px; margin-bottom: 1.5em; }
        .mock-sidebar { background: #e9ecef; width: 180px; float: left; min-height: 400px; border-radius: 6px; padding: 1em; margin-right: 2em; }
        .mock-content { margin-left: 200px; }
        .mock-card { background: #fff; border: 1px solid #dee2e6; border-radius: 6px; padding: 1em; margin-bottom: 1em; }
        .pick-highlight { outline: 3px solid #28a745 !important; background: #e6ffe6 !important; cursor: pointer !important; }
    </style>
</head>
<body>
    <div class="mock-navbar">Admin Navbar</div>
    <div class="mock-sidebar">
        <div>Dashboard</div>
        <div>Users</div>
        <div>Settings</div>
    </div>
    <div class="mock-content">
        <div class="mock-card">
            <h4>Welcome, Admin!</h4>
            <p>This is a minimal mock of the admin dashboard. No real data is shown.</p>
            <button class="btn btn-success">Add User</button>
            <button class="btn btn-secondary">Edit Profile</button>
        </div>
        <div class="mock-card">
            <h5>Recent Activity</h5>
            <ul>
                <li>User A logged in</li>
                <li>User B updated profile</li>
            </ul>
        </div>
    </div>
    <script>
    // Highlight on hover
    document.body.addEventListener('mouseover', function(e) {
        if (e.target === document.body) return;
        e.target.classList.add('pick-highlight');
    }, true);
    document.body.addEventListener('mouseout', function(e) {
        if (e.target === document.body) return;
        e.target.classList.remove('pick-highlight');
    }, true);
    // Get unique selector
    function getUniqueSelector(el) {
        if (!el) return '';
        if (el.id) return '#' + el.id;
        let path = '', parent;
        while (el && el.nodeType === 1 && el.tagName.toLowerCase() !== 'body') {
            let selector = el.tagName.toLowerCase();
            if (el.className) selector += '.' + Array.from(el.classList).join('.');
            parent = el.parentNode;
            if (parent) {
                let siblings = Array.from(parent.children).filter(child => child.tagName === el.tagName);
                if (siblings.length > 1) selector += `:nth-child(${Array.from(parent.children).indexOf(el) + 1})`;
            }
            path = selector + (path ? ' > ' + path : '');
            el = parent;
        }
        return path;
    }
    // On click, send selector to opener and close
    document.body.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (window.opener && e.target !== document.body) {
            const selector = getUniqueSelector(e.target);
            window.opener.postMessage({ type: 'tutorial-selector', selector }, '*');
            window.close();
        }
    }, true);
    </script>
</body>
</html>
