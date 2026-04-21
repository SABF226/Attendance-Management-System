<?php
// Breadcrumbs are now set in the controller
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= isset($member) ? 'Edit Member' : 'Add New Member' ?></h2>
        <a href="?page=members" class="btn btn-secondary">Back to List</a>
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
    
    <form method="POST" action="?page=members<?= isset($member) ? '&action=update&id=' . $member['id'] : '&action=store' ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="name" class="form-label">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?= htmlspecialchars($member['name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="field" class="form-label">Field of Study *</label>
                <select id="field" name="field" class="form-control" required>
                    <option value="">-- Select Field --</option>
                    <option value="Computer Science" <?= ($member['field'] ?? '') === 'Computer Science' ? 'selected' : '' ?>>Computer Science</option>
                    <option value="Electrical Engineering" <?= ($member['field'] ?? '') === 'Electrical Engineering' ? 'selected' : '' ?>>Electrical Engineering</option>
                    <option value="Mechanical Engineering" <?= ($member['field'] ?? '') === 'Mechanical Engineering' ? 'selected' : '' ?>>Mechanical Engineering</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="phone" class="form-label">Phone Number *</label>
                <input type="tel" id="phone" name="phone" class="form-control" 
                       value="<?= htmlspecialchars($member['phone'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($member['email'] ?? '') ?>" required>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem; flex-wrap: wrap;">
            <a href="?page=members" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <?= isset($member) ? 'Update Member' : 'Add Member' ?>
            </button>
        </div>
    </form>
</div>

