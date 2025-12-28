/**
 * Chairperson View Grades Page JavaScript
 * Handles notes modal with AJAX saving
 */

export function initChairpersonViewGradesPage() {
  const notesTextarea = document.getElementById('notesTextarea');
  const studentNameDisplay = document.getElementById('studentNameDisplay');
  const saveNotesBtn = document.getElementById('saveNotesBtn');
  const charCount = document.getElementById('charCount');
  let currentFinalGradeId = null;
  let currentButton = null;

  if (!notesTextarea || !saveNotesBtn) return;

  // Update character count
  notesTextarea.addEventListener('input', function () {
    if (charCount) {
      charCount.textContent = this.value.length;
    }
  });

  // Handle open notes modal button click
  document.querySelectorAll('.open-notes-modal').forEach((button) => {
    button.addEventListener('click', function () {
      currentFinalGradeId = this.dataset.finalGradeId;
      currentButton = this;
      const studentName = this.dataset.studentName;
      const notes = this.dataset.notes || '';

      // Populate modal
      if (studentNameDisplay) {
        studentNameDisplay.textContent = studentName;
      }
      notesTextarea.value = notes;
      if (charCount) {
        charCount.textContent = notes.length;
      }

      // Show modal using global modal helper if available
      if (typeof modal !== 'undefined' && modal.open) {
        modal.open('notesModal', { finalGradeId: currentFinalGradeId, studentName, notes });
      }
    });
  });

  // Handle save notes button click
  saveNotesBtn.addEventListener('click', async function () {
    if (!currentFinalGradeId) return;

    const notes = notesTextarea.value.trim();

    // Start loading
    if (typeof loading !== 'undefined' && loading.start) {
      loading.start('saveNotes');
    }
    this.disabled = true;

    try {
      // Get CSRF token from meta tag
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

      const response = await fetch(window.chairpersonSaveNotesRoute || '/chairperson/grades/save-notes', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          final_grade_id: currentFinalGradeId,
          notes: notes,
        }),
      });

      const data = await response.json();

      if (data.status === 'success') {
        // Update button appearance
        if (currentButton) {
          const badgeExists = currentButton.querySelector('.badge');
          if (notes) {
            if (!badgeExists) {
              currentButton.innerHTML = `
                                <i class="bi bi-sticky"></i>
                                <span class="badge bg-success ms-1">Has Notes</span>
                            `;
            }
          } else {
            currentButton.innerHTML = `
                            <i class="bi bi-sticky"></i>
                            Add Notes
                        `;
          }
          // Update data attribute for next time
          currentButton.dataset.notes = notes;
        }

        // Show success notification
        if (typeof notify !== 'undefined' && notify.success) {
          notify.success(data.message);
        }

        // Close modal after short delay
        setTimeout(() => {
          if (typeof modal !== 'undefined' && modal.close) {
            modal.close('notesModal');
          }
          if (typeof loading !== 'undefined' && loading.stop) {
            loading.stop('saveNotes');
          }
          saveNotesBtn.disabled = false;
        }, 500);
      } else {
        throw new Error(data.message || 'Failed to save notes');
      }
    } catch (error) {
      console.error('Error saving notes:', error);
      if (typeof loading !== 'undefined' && loading.stop) {
        loading.stop('saveNotes');
      }
      saveNotesBtn.disabled = false;
      if (typeof notify !== 'undefined' && notify.error) {
        notify.error(error.message || 'Failed to save notes. Please try again.');
      }
    }
  });

  // Reset modal when closed
  const notesModal = document.getElementById('notesModal');
  if (notesModal) {
    notesModal.addEventListener('hidden.bs.modal', function () {
      currentFinalGradeId = null;
      currentButton = null;
      notesTextarea.value = '';
      if (charCount) {
        charCount.textContent = '0';
      }
      if (studentNameDisplay) {
        studentNameDisplay.textContent = '';
      }
    });
  }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (document.querySelector('[data-page="chairperson-view-grades"]') || document.querySelector('.open-notes-modal')) {
    initChairpersonViewGradesPage();
  }
});

window.initChairpersonViewGradesPage = initChairpersonViewGradesPage;
