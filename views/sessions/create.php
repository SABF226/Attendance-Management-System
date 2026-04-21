<?php
// Breadcrumbs are now set in the controller
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Create New Session</h2>
        <a href="?page=sessions" class="btn btn-secondary">Back to Sessions</a>
    </div>
    
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="?page=sessions&action=store">
        <div class="form-row">
            <div class="form-group">
                <label for="session_name" class="form-label">Session Name *</label>
                <input type="text" id="session_name" name="session_name" class="form-control" 
                       value="<?= htmlspecialchars($session['session_name'] ?? '') ?>" 
                       placeholder="e.g., English Club Mandora Team A - 1st Session of January 2026" required>
            </div>
            
            <div class="form-group">
                <label for="session_date" class="form-label">Session Date *</label>
                <input type="date" id="session_date" name="session_date" class="form-control" 
                       value="<?= htmlspecialchars($session['session_date'] ?? date('Y-m-d')) ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="session_time" class="form-label">Session Time</label>
                <input type="time" id="session_time" name="session_time" class="form-control" 
                       value="<?= htmlspecialchars($session['session_time'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="session_team" class="form-label">Session's Team</label>
                <input type="text" id="session_team" name="session_team" class="form-control" 
                       value="<?= htmlspecialchars($session['session_team'] ?? '') ?>" 
                       placeholder="e.g., Team Alpha">
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem; flex-wrap: wrap;">
            <a href="?page=sessions" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Session</button>
        </div>
    </form>
</div>

