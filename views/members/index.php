<div class="card">
    <div class="card-header">
        <h2 class="card-title">Members List</h2>
        <a href="?page=members&action=create" class="btn btn-primary">+ Add New Member</a>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?? 'success' ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
    
    <div style="margin-bottom: 1.5rem;">
        <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="page" value="members">
            <input type="text" name="search" placeholder="Search by name or email..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                   class="form-control" style="flex: 1; min-width: 200px;">
            <button type="submit" class="btn btn-secondary">Search</button>
            <?php if (isset($_GET['search'])): ?>
                <a href="?page=members" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (empty($members)): ?>
        <div class="empty-state">
            <div class="empty-state-title">No Members Found</div>
            <div class="empty-state-description">
                <?= isset($_GET['search']) ? 'No members match your search criteria.' : 'Get started by adding your first member to the club.' ?>
            </div>
            <div class="empty-state-action">
                <a href="?page=members&action=create" class="btn btn-primary">Add First Member</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Field</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?= htmlspecialchars($member['id'] ?? '') ?></td>
                            <td><?= htmlspecialchars($member['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($member['field'] ?? '') ?></td>
                            <td><?= htmlspecialchars($member['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($member['email'] ?? '') ?></td>
                            <td class="actions">
                                <a href="?page=members&action=edit&id=<?= $member['id'] ?? 0 ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="?page=members&action=delete&id=<?= $member['id'] ?? 0 ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this member?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <strong>Total Members: <?= count($members) ?></strong>
        </div>
    <?php endif; ?>
</div>

