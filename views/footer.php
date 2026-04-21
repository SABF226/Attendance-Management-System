    </div>
    </div>
    <footer style="text-align: center; padding: 2rem; color: #1D1F5A; margin-top: 3rem;">
        <p>&copy; <?= date('Y') ?> <a href="https://sabfcode-portfolio.netlify.app" target="_blank">SaBf GraphiTech</a>. All rights reserved.</p>
    </footer>
    
    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Confirm Action</h3>
            </div>
            <div class="modal-body">
                <p class="modal-message" id="modalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="button" class="modal-btn modal-btn-danger" id="modalConfirmBtn" onclick="confirmModalAction()">Delete</button>
            </div>
        </div>
    </div>
</body>
</html>

